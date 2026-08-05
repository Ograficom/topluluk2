<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Translates a Post's title/excerpt/content on demand using MyMemory
 * (https://mymemory.translated.net), a free, key-less translation API, so the
 * reader sees the article in whichever locale they picked with the language
 * switcher. Every published post is written in Turkish; there is no stored
 * translation, so this always runs at read time - the result is cached
 * (keyed by post id + a hash of the source fields + target locale) so a given
 * version of a post is only ever sent to the API once, not on every page view.
 *
 * Fails open: if the API is unreachable, rate-limited, or returns something
 * unusable, the original Turkish text is shown instead of an error.
 */
class PostTranslationService
{
    private const SOURCE_LOCALE = 'tr';

    /**
     * MyMemory's public free tier caps each `q` request around 500 bytes.
     * Turkish text is multi-byte (ı, ğ, ü, ş, ö, ç), so chunks are split by
     * character count with a safety margin rather than assuming 1 char = 1 byte.
     */
    private const CHUNK_CHAR_LIMIT = 400;

    public function translatePost(Post $post, string $locale): array
    {
        $original = [
            'title' => (string) $post->title,
            'excerpt' => (string) $post->excerpt,
            'content' => (string) $post->content,
        ];

        if (!$this->shouldTranslate($locale)) {
            return $original;
        }

        $contentHash = md5($original['title'] . '|' . $original['excerpt'] . '|' . $original['content']);
        $cacheKey = "post-translation:{$post->id}:{$locale}:{$contentHash}";

        return Cache::rememberForever($cacheKey, function () use ($original, $locale, $post) {
            try {
                return [
                    'title' => $this->translateText($original['title'], $locale),
                    'excerpt' => $this->translateText($original['excerpt'], $locale),
                    'content' => $this->translateHtml($original['content'], $locale),
                ];
            } catch (\Throwable $e) {
                Log::warning('Post cevirisi basarisiz, orijinal Turkce icerik gosteriliyor', [
                    'post_id' => $post->id,
                    'locale' => $locale,
                    'error' => $e->getMessage(),
                ]);

                return $original;
            }
        });
    }

    private function shouldTranslate(string $locale): bool
    {
        if ($locale === self::SOURCE_LOCALE) {
            return false;
        }

        return in_array($locale, array_keys(config('app.available_locales', [])), true);
    }

    private function translateText(string $text, string $locale): string
    {
        $text = trim($text);
        if ($text === '') {
            return $text;
        }

        $chunks = $this->splitIntoChunks($text);
        $translated = $this->translateChunks($chunks, $locale);

        return trim(implode(' ', array_map(
            fn (string $chunk) => $translated[$chunk] ?? $chunk,
            $chunks
        )));
    }

    /**
     * Translates HTML content by walking every text node with DOMDocument and
     * replacing only its text, leaving every tag/attribute untouched - safer
     * than sending raw HTML through the translation API, which could mangle
     * markup (tags, attributes, entities) it was never meant to parse.
     */
    private function translateHtml(string $html, string $locale): string
    {
        $html = trim($html);
        if ($html === '') {
            return $html;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        if (!$loaded) {
            return $html;
        }

        $xpath = new \DOMXPath($dom);
        $textNodes = [];
        foreach ($xpath->query('//text()') as $node) {
            if ($node instanceof \DOMText && trim($node->nodeValue ?? '') !== '') {
                $textNodes[] = $node;
            }
        }

        if ($textNodes === []) {
            return $html;
        }

        $nodeChunks = [];
        $allChunks = [];
        foreach ($textNodes as $index => $node) {
            $chunks = $this->splitIntoChunks((string) $node->nodeValue);
            $nodeChunks[$index] = $chunks;
            array_push($allChunks, ...$chunks);
        }

        $translated = $this->translateChunks(array_values(array_unique($allChunks)), $locale);

        foreach ($textNodes as $index => $node) {
            $pieces = array_map(
                fn (string $chunk) => $translated[$chunk] ?? $chunk,
                $nodeChunks[$index]
            );
            $node->nodeValue = trim(implode(' ', $pieces));
        }

        $root = $dom->getElementsByTagName('div')->item(0);
        if (!$root) {
            return $html;
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return trim($result);
    }

    /**
     * @return array<int, string>
     */
    private function splitIntoChunks(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        if (mb_strlen($text) <= self::CHUNK_CHAR_LIMIT) {
            return [$text];
        }

        $words = preg_split('/\s+/u', $text) ?: [$text];
        $chunks = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if (mb_strlen($candidate) > self::CHUNK_CHAR_LIMIT && $current !== '') {
                $chunks[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /**
     * Translates every chunk concurrently (Http::pool) instead of one request
     * at a time - an article can easily have 15-20 paragraphs/text nodes, and
     * sequential round-trips to a third-party API would make the first (only
     * the first, thanks to caching) view of a translated post noticeably slow.
     *
     * @param  array<int, string>  $chunks
     * @return array<string, string> original chunk text => translated text
     */
    private function translateChunks(array $chunks, string $locale): array
    {
        if ($chunks === []) {
            return [];
        }

        $langpair = self::SOURCE_LOCALE . '|' . $locale;
        $url = (string) config('services.mymemory.url', 'https://api.mymemory.translated.net/get');
        $email = (string) config('services.mymemory.email', '');
        $timeout = (int) config('services.mymemory.timeout', 10);

        $responses = Http::pool(function (Pool $pool) use ($chunks, $langpair, $email, $url, $timeout) {
            return array_map(function (string $chunk) use ($pool, $langpair, $email, $url, $timeout) {
                $params = ['q' => $chunk, 'langpair' => $langpair];
                if ($email !== '') {
                    $params['de'] = $email;
                }

                return $pool->withoutVerifying()->timeout($timeout)->get($url, $params);
            }, $chunks);
        });

        $result = [];
        foreach ($chunks as $index => $chunk) {
            $result[$chunk] = $this->extractTranslatedText($responses[$index] ?? null, $chunk);
        }

        return $result;
    }

    private function extractTranslatedText(mixed $response, string $fallback): string
    {
        if (!$response instanceof Response || !$response->successful()) {
            return $fallback;
        }

        $status = $response->json('responseStatus');
        $translated = $response->json('responseData.translatedText');

        if ((string) $status !== '200' || !is_string($translated) || trim($translated) === '') {
            return $fallback;
        }

        // MyMemory returns HTTP 200 with a warning sentence embedded in
        // translatedText once the free daily quota is exhausted, rather than
        // an error status - treat that as a failure so the warning text never
        // gets cached/shown as if it were a real translation.
        if (stripos($translated, 'MYMEMORY WARNING') !== false) {
            return $fallback;
        }

        return trim(html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

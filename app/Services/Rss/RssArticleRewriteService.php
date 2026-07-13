<?php

namespace App\Services\Rss;

use App\Models\RssItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RssArticleRewriteService
{
    public function rewrite(RssItem $item, ?string $model = null): array
    {
        $sourceHash = $item->hash ?: hash('sha256', (string) $item->content);

        if (
            $item->ai_source_hash === $sourceHash
            && filled($item->ai_title)
            && filled($item->ai_content)
        ) {
            return $this->resultFromItem($item);
        }

        try {
            $sourceText = $this->sourceText($item);

            if (mb_strlen($sourceText) < 120) {
                throw new \RuntimeException('AI yeniden yazimi icin kaynak metin cok kisa.');
            }

            $apiKey = (string) config('services.ollama.api_key');
            $baseUrl = rtrim((string) config('services.ollama.url', 'https://ollama.com'), '/');
            $selectedModel = $model ?: config('services.ollama.model', 'gpt-oss:20b');
            $generateEndpoint = str_ends_with($baseUrl, '/api')
                ? $baseUrl . '/generate'
                : $baseUrl . '/api/generate';

            if ($apiKey === '') {
                throw new \RuntimeException('Ollama API key eksik. .env icine OLLAMA_API_KEY ekleyin.');
            }

            $response = Http::withoutVerifying()
                ->timeout((int) config('services.ollama.timeout', 120))
                ->acceptJson()
                ->asJson()
                ->withToken($apiKey)
                ->post($generateEndpoint, [
                    'model' => $selectedModel,
                    'stream' => false,
                    'format' => 'json',
                    'prompt' => $this->prompt($item, $sourceText),
                    'options' => [
                        'temperature' => 0.7,
                    ],
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException("Ollama HTTP {$response->status()}: " . $response->body());
            }

            $rawResponse = $response->json('response');

            if (! is_string($rawResponse) || trim($rawResponse) === '') {
                throw new \RuntimeException('Ollama bos cevap dondurdu.');
            }

            $payload = json_decode($rawResponse, true);

            if (! is_array($payload)) {
                throw new \RuntimeException('Ollama gecerli JSON dondurmedi: ' . Str::limit($rawResponse, 500, ''));
            }

            $title = Str::limit(trim(strip_tags((string) ($payload['title'] ?? ''))), 500, '');
            $summary = Str::limit($this->plainText((string) ($payload['summary'] ?? '')), 500, '');
            $content = $this->sanitizeGeneratedHtml((string) ($payload['content_html'] ?? ''));
            $tags = $this->normalizeTags((array) ($payload['tags'] ?? []));

            if ($title === '' || $summary === '' || mb_strlen($this->plainText($content)) < 120) {
                throw new \RuntimeException('Ollama eksik veya cok kisa icerik dondurdu.');
            }

            $content = $this->appendSourceLink($content, $item->link);

            $item->forceFill([
                'ai_source_hash' => $sourceHash,
                'ai_title' => $title,
                'ai_summary' => $summary,
                'ai_content' => $content,
                'ai_tags' => $tags,
                'ai_rewritten_at' => now(),
                'ai_rewrite_error' => null,
            ])->save();

            return $this->resultFromItem($item);
        } catch (\Throwable $e) {
            $item->forceFill([
                'ai_rewrite_error' => Str::limit($e->getMessage(), 2000, ''),
            ])->save();

            throw new \RuntimeException('AI yeniden yazimi basarisiz: ' . $e->getMessage(), 0, $e);
        }
    }

    private function resultFromItem(RssItem $item): array
    {
        return [
            'title' => (string) $item->ai_title,
            'summary' => (string) $item->ai_summary,
            'content' => (string) $item->ai_content,
            'tags' => $this->normalizeTags((array) ($item->ai_tags ?? [])),
        ];
    }

    private function sourceText(RssItem $item): string
    {
        $text = implode("\n\n", array_filter([
            trim((string) $item->title),
            trim((string) $item->summary),
            trim((string) $item->content),
        ]));

        return Str::limit($this->plainText($text), 12000, '');
    }

    private function plainText(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    private function sanitizeGeneratedHtml(string $html): string
    {
        $html = trim($html);

        $html = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', $html) ?? $html;

        $html = strip_tags($html, '<p><h2><h3><ul><ol><li><strong><em><blockquote>');

        $html = preg_replace(
            '/<(p|h2|h3|ul|ol|li|strong|em|blockquote)\b[^>]*>/i',
            '<$1>',
            $html
        ) ?? $html;

        if (! str_contains($html, '<')) {
            $paragraphs = array_filter(array_map('trim', preg_split('/\R{2,}/u', $html) ?: []));

            $html = implode("\n", array_map(
                fn (string $paragraph) => '<p>' . e($paragraph) . '</p>',
                $paragraphs
            ));
        }

        return trim($html);
    }

    private function appendSourceLink(string $html, ?string $url): string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return $html;
        }

        return trim(
            $html .
            "\n<p>Kaynak: <a href=\"" .
            e($url) .
            "\" rel=\"nofollow noopener\" target=\"_blank\">" .
            e($url) .
            '</a></p>'
        );
    }

    private function normalizeTags(array $tags): array
    {
        return collect($tags)
            ->map(fn ($tag) => Str::limit(trim($this->plainText((string) $tag)), 80, ''))
            ->filter()
            ->unique(fn ($tag) => Str::lower($tag))
            ->take(8)
            ->values()
            ->all();
    }

    private function prompt(RssItem $item, string $sourceText): string
    {
        return <<<PROMPT
Asagidaki RSS kaynagindan Turkce, ozgun ve okunabilir yeni bir tanitim yazisi uret.
Metni kelime kelime degistirme. Bilgileri yeniden organize ederek yeni bir anlatim kur.
Kaynakta bulunmayan bilgi, alinti, tarih veya iddia ekleme.
Tarafsiz ve bilgilendirici bir dil kullan.
JSON disinda hicbir sey dondurme.

JSON semasi:
{"title":"benzersiz baslik","summary":"en fazla 2 cumlelik ozet","content_html":"yalnizca p, h2, h3, ul, ol, li, strong, em ve blockquote etiketleriyle HTML","tags":["3-8 kisa etiket"]}

Kaynak URL: {$item->link}
Kaynak metin:
{$sourceText}
PROMPT;
    }
}

<?php

namespace App\Services\Rss;

use App\Models\RssItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RssArticleRewriteService
{
    /** Incrementing this intentionally invalidates previously cached rewrites. */
    public const PROMPT_VERSION = 'editorial-2026-09-v6';

    private const MAX_ATTEMPTS = 2;
    private const MAX_CONTENT_SIMILARITY = 0.24;
    private const MAX_TITLE_SIMILARITY = 0.62;
    private const MAX_SHARED_WORD_RUN = 10;

    public static function expectedSourceHash(string $itemHash): string
    {
        return hash('sha256', self::PROMPT_VERSION.'|'.$itemHash);
    }

    /**
     * @return array{title: string, summary: string, content: string, tags: array<int, string>}
     */
    public function rewrite(RssItem $item, ?string $model = null): array
    {
        $itemHash = $item->hash ?: hash('sha256', (string) $item->content);
        $sourceHash = self::expectedSourceHash($itemHash);

        if (
            $item->ai_source_hash === $sourceHash
            && filled($item->ai_title)
            && filled($item->ai_content)
        ) {
            return $this->resultFromItem($item);
        }

        $item->forceFill([
            'ai_rewrite_attempts' => ((int) $item->ai_rewrite_attempts) + 1,
            'ai_last_attempted_at' => now(),
        ])->save();

        try {
            $sourceText = $this->sourceText($item);
            if (mb_strlen($sourceText) < 120) {
                throw new \RuntimeException('AI yeniden yazimi icin kaynak metin cok kisa.');
            }

            $selectedModel = $this->normalizeOllamaModel($model);
            $feedback = null;
            $lastError = null;

            for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
                try {
                    $draft = $this->generateDraft(
                        item: $item,
                        sourceText: $sourceText,
                        model: $selectedModel,
                        feedback: $feedback,
                        attempt: $attempt,
                    );

                    $contentSimilarity = $this->shingleJaccardSimilarity($sourceText, $draft['content'], 4);
                    $titleSimilarity = $this->wordSetJaccardSimilarity((string) $item->title, $draft['title']);
                    $sharedWordRun = $this->longestSharedWordRun($sourceText, $draft['content']);
                    $unsupportedNumbers = $this->unsupportedNumbers($sourceText, $draft['content']);

                    $passes = $contentSimilarity <= self::MAX_CONTENT_SIMILARITY
                        && $titleSimilarity <= self::MAX_TITLE_SIMILARITY
                        && $sharedWordRun <= self::MAX_SHARED_WORD_RUN
                        && $unsupportedNumbers === [];

                    if ($passes) {
                        $item->forceFill([
                            'ai_source_hash' => $sourceHash,
                            'ai_title' => $draft['title'],
                            'ai_summary' => $draft['summary'],
                            'ai_content' => $draft['content'],
                            'ai_tags' => $draft['tags'],
                            'ai_rewritten_at' => now(),
                            'ai_rewrite_error' => null,
                            'ai_rewrite_attempts' => 0,
                        ])->save();

                        return $this->resultFromItem($item);
                    }

                    $feedback = trim(implode(' ', array_filter([
                        $contentSimilarity > self::MAX_CONTENT_SIMILARITY
                            ? 'Kaynak metnin cumle yapisini fazla takip ettin. Paragraf sirasi ve cumle yapisini bastan kur.'
                            : null,
                        $titleSimilarity > self::MAX_TITLE_SIMILARITY
                            ? 'Baslik kaynak basliga fazla benziyor. Farkli vurgu ve kelime sirasi kullan.'
                            : null,
                        $sharedWordRun > self::MAX_SHARED_WORD_RUN
                            ? 'Kaynak metinden uzun bir ifade aynen tasinmis. Bu ifadeyi tamamen yeniden kur.'
                            : null,
                        $unsupportedNumbers !== []
                            ? 'Kaynakta olmayan sayilari kaldir: '.implode(', ', $unsupportedNumbers).'.'
                            : null,
                    ])));
                } catch (\Throwable $e) {
                    $lastError = $e;
                    $feedback = 'Onceki deneme teknik veya kalite kontrolu nedeniyle reddedildi. Haberi kaynak olgulara sadik kalarak bastan yaz.';
                }
            }

            throw $lastError ?? new \RuntimeException('Ollama gecerli ve ozgun bir taslak uretemedi.');
        } catch (\Throwable $e) {
            $item->forceFill([
                'ai_rewrite_error' => Str::limit($e->getMessage(), 2000, ''),
            ])->save();

            Log::warning('RSS AI yeniden yazimi basarisiz', [
                'rss_item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('AI yeniden yazimi basarisiz: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * @return array{title: string, summary: string, content: string, tags: array<int, string>}
     */
    private function generateDraft(
        RssItem $item,
        string $sourceText,
        string $model,
        ?string $feedback,
        int $attempt,
    ): array {
        $schema = [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'summary' => ['type' => 'string'],
                'content_html' => ['type' => 'string'],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'minItems' => 3,
                    'maxItems' => 8,
                ],
            ],
            'required' => ['title', 'summary', 'content_html', 'tags'],
            'additionalProperties' => false,
        ];

        $feedbackBlock = filled($feedback)
            ? "\n\nONCEKI DENEME ICIN DUZELTME:\n{$feedback}\n"
            : '';

        $prompt = <<<PROMPT
Sen Ografi'nin Turkce haber editorusun. Asagidaki kaynak haberi tamamen yeni bir anlatimla yeniden yaz.

ZORUNLU KURALLAR:
- Kaynakta olmayan bilgi, isim, tarih, sayi, alinti veya yorum EKLEME.
- Kaynak cumlelerini sirayla takip edip kelime degistirerek spin yapma.
- Paragraf sirasi, cumle yapisi ve anlatim akisini bastan kur.
- Dogrudan alintilarin sozlerini degistirme; alinti varsa <blockquote> kullan.
- Baslik kaynak basligin kopyasi veya ufak degistirilmis hali olmasin.
- Tarafsiz, aciklayici, tik tuzagi olmayan Turkce kullan.
- content_html en az 3 anlamli paragraf icersin.
- Yalnizca p, h2, h3, ul, ol, li, strong, em, blockquote, table, thead, tbody, tr, th, td HTML etiketlerini kullan.
- Kaynak URL'sini, "Kaynak:" satirini veya etiket listesini content_html sonuna ekleme.
- Etiketleri yalnizca tags alaninda dondur.
- SADECE istenen JSON nesnesini dondur. Markdown kod blogu, ```json veya aciklama ekleme.
{$feedbackBlock}

KAYNAK BASLIK:
{$item->title}

KAYNAK METIN:
{$sourceText}
PROMPT;

        $payload = $this->generateStructured(
            model: $model,
            prompt: $prompt,
            schema: $schema,
            temperature: min(0.45 + (($attempt - 1) * 0.15), 0.65),
        );

        $title = Str::limit(trim(strip_tags((string) ($payload['title'] ?? ''))), 500, '');
        $summary = Str::limit($this->plainText((string) ($payload['summary'] ?? '')), 500, '');
        $content = $this->sanitizeGeneratedHtml((string) ($payload['content_html'] ?? ''));
        $tags = $this->normalizeTags((array) ($payload['tags'] ?? []));

        if ($title === '' || $summary === '' || mb_strlen($this->plainText($content)) < 180 || count($tags) < 3) {
            throw new \RuntimeException('Ollama eksik, cok kisa veya etiketsiz icerik dondurdu.');
        }

        return compact('title', 'summary', 'content', 'tags');
    }

    /** @param array<string, mixed> $schema */
    private function generateStructured(string $model, string $prompt, array $schema, float $temperature): array
    {
        $apiKey = trim((string) config('services.ollama.api_key'));
        $baseUrl = rtrim((string) config('services.ollama.url', 'https://ollama.com'), '/');
        $endpoint = str_ends_with($baseUrl, '/api') ? $baseUrl.'/generate' : $baseUrl.'/api/generate';

        if ($apiKey === '') {
            throw new \RuntimeException('Ollama API key eksik. .env icine OLLAMA_API_KEY ekleyin.');
        }

        $response = Http::withoutVerifying()
            ->timeout(max(20, (int) config('services.ollama.timeout', 120)))
            ->retry(1, 500, throw: false)
            ->acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->post($endpoint, [
                'model' => $model,
                'stream' => false,
                'format' => $schema,
                'prompt' => $prompt,
                'options' => ['temperature' => $temperature],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Ollama HTTP {$response->status()}: ".$response->body());
        }

        $raw = $response->json('response');
        if (is_array($raw)) {
            return $raw;
        }
        if (! is_string($raw) || trim($raw) === '') {
            throw new \RuntimeException('Ollama bos cevap dondurdu.');
        }

        $payload = $this->decodeStructuredJson($raw);
        if (! is_array($payload)) {
            throw new \RuntimeException('Ollama gecerli JSON dondurmedi: '.Str::limit($raw, 500, ''));
        }

        return $payload;
    }

    /** @return array<string, mixed>|null */
    private function decodeStructuredJson(string $raw): ?array
    {
        $clean = trim($raw, "\xEF\xBB\xBF \t\n\r\0\x0B");

        // Some Ollama cloud models still wrap otherwise valid structured JSON in
        // Markdown fences. Accept that transport noise rather than rejecting a
        // perfectly usable rewrite.
        $clean = preg_replace('/^```(?:json)?\s*/iu', '', $clean) ?? $clean;
        $clean = preg_replace('/\s*```\s*$/u', '', $clean) ?? $clean;
        $clean = trim($clean);

        $decoded = json_decode($clean, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Also tolerate a short explanatory prefix/suffix while keeping parsing
        // strict about the JSON object itself.
        $firstBrace = strpos($clean, '{');
        $lastBrace = strrpos($clean, '}');
        if ($firstBrace === false || $lastBrace === false || $lastBrace <= $firstBrace) {
            return null;
        }

        $candidate = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
        $decoded = json_decode($candidate, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function normalizeOllamaModel(?string $model): string
    {
        $fallback = trim((string) config('services.ollama.model', 'gpt-oss:20b'));
        if ($fallback === '' || $this->looksLikeOpenAiModel($fallback)) {
            $fallback = 'gpt-oss:20b';
        }

        $candidate = trim((string) $model);
        if ($candidate === '' || $this->looksLikeOpenAiModel($candidate)) {
            return $fallback;
        }

        return $candidate;
    }

    private function looksLikeOpenAiModel(string $model): bool
    {
        $model = Str::lower(trim($model));

        return Str::startsWith($model, ['gpt-5', 'gpt-6', 'o1', 'o3', 'o4']);
    }

    /** @return array{title: string, summary: string, content: string, tags: array<int, string>} */
    private function resultFromItem(RssItem $item): array
    {
        return [
            'title' => (string) $item->ai_title,
            'summary' => (string) $item->ai_summary,
            'content' => $this->sanitizeGeneratedHtml((string) $item->ai_content),
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

        return Str::limit($this->plainText($text), 14000, '');
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
        $html = strip_tags($html, '<p><h2><h3><ul><ol><li><strong><em><blockquote><table><thead><tbody><tr><th><td>');
        $html = preg_replace(
            '/<(p|h2|h3|ul|ol|li|strong|em|blockquote|table|thead|tbody|tr|th|td)\b[^>]*>/i',
            '<$1>',
            $html,
        ) ?? $html;

        $html = preg_replace('#<p>\s*(?:Kaynak|Source)\s*:.*?</p>\s*$#isu', '', $html) ?? $html;
        $html = preg_replace('#<p>\s*(?:Etiketler|Anahtar\s*Kelimeler|Tags|Keywords)\s*:.*?</p>\s*$#isu', '', $html) ?? $html;

        if (! str_contains($html, '<')) {
            $paragraphs = array_filter(array_map('trim', preg_split('/\R{2,}/u', $html) ?: []));
            $html = implode("\n", array_map(fn (string $p) => '<p>'.e($p).'</p>', $paragraphs));
        }

        return trim($html);
    }

    /** @return array<int, string> */
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

    private function shingleJaccardSimilarity(string $a, string $b, int $size): float
    {
        $setA = array_unique($this->wordShingles($a, $size));
        $setB = array_unique($this->wordShingles($b, $size));

        if ($setA === [] || $setB === []) {
            return 0.0;
        }

        $union = count(array_unique(array_merge($setA, $setB)));

        return $union > 0 ? count(array_intersect($setA, $setB)) / $union : 0.0;
    }

    private function wordSetJaccardSimilarity(string $a, string $b): float
    {
        return $this->shingleJaccardSimilarity($a, $b, 1);
    }

    /** @return array<int, string> */
    private function wordShingles(string $text, int $size): array
    {
        $normalized = mb_strtolower($this->plainText($text), 'UTF-8');
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized) ?? $normalized;
        $words = array_values(array_filter(preg_split('/\s+/u', trim($normalized)) ?: []));

        if ($size <= 1) {
            return $words;
        }
        if (count($words) < $size) {
            return $words === [] ? [] : [implode(' ', $words)];
        }

        $result = [];
        for ($i = 0, $max = count($words) - $size; $i <= $max; $i++) {
            $result[] = implode(' ', array_slice($words, $i, $size));
        }

        return $result;
    }

    private function longestSharedWordRun(string $source, string $draft): int
    {
        $sourceWords = $this->wordShingles($source, 1);
        $draftWords = $this->wordShingles($draft, 1);
        $previous = array_fill(0, count($draftWords) + 1, 0);
        $longest = 0;

        foreach ($sourceWords as $sourceWord) {
            $current = array_fill(0, count($draftWords) + 1, 0);
            foreach ($draftWords as $index => $draftWord) {
                if ($sourceWord === $draftWord) {
                    $current[$index + 1] = $previous[$index] + 1;
                    $longest = max($longest, $current[$index + 1]);
                }
            }
            $previous = $current;
        }

        return $longest;
    }

    /** @return array<int, string> */
    private function unsupportedNumbers(string $source, string $draft): array
    {
        preg_match_all('/(?<![\p{L}\p{N}])\d+(?:[.,]\d+)*(?![\p{L}\p{N}])/u', $source, $sourceMatches);
        preg_match_all('/(?<![\p{L}\p{N}])\d+(?:[.,]\d+)*(?![\p{L}\p{N}])/u', $draft, $draftMatches);

        $normalize = fn ($number) => str_replace(',', '.', (string) $number);
        $sourceNumbers = array_unique(array_map($normalize, $sourceMatches[0] ?? []));

        return array_values(array_unique(array_filter(
            array_map($normalize, $draftMatches[0] ?? []),
            fn ($number) => ! in_array($number, $sourceNumbers, true),
        )));
    }
}

<?php

namespace App\Services\Rss;

use App\Models\RssItem;
use App\Services\AI\OpenAiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OpenAiRssArticleRewriteService extends RssArticleRewriteService
{
    private const MAX_ATTEMPTS = 2;
    private const MAX_CONTENT_SIMILARITY = 0.24;
    private const MAX_TITLE_SIMILARITY = 0.62;
    private const MAX_SHARED_WORD_RUN = 10;

    public function __construct(private readonly OpenAiService $openAi)
    {
    }

    public function rewrite(RssItem $item, ?string $model = null): array
    {
        $itemHash = $item->hash ?: hash('sha256', (string) $item->content);
        $sourceHash = parent::expectedSourceHash($itemHash);

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

            $selectedModel = $this->openAi->normalizeModel($model);
            $feedback = null;
            $best = null;
            $lastError = null;

            for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
                try {
                    $draft = $this->generateDraft(
                        $item,
                        $sourceText,
                        $selectedModel,
                        $feedback,
                        $attempt,
                    );

                    $contentSimilarity = $this->shingleJaccardSimilarity($sourceText, $draft['content'], 4);
                    $titleSimilarity = $this->shingleJaccardSimilarity((string) $item->title, $draft['title'], 1);
                    $sharedWordRun = $this->longestSharedWordRun($sourceText, $draft['content']);
                    $unsupportedNumbers = $this->unsupportedNumbers($sourceText, $draft['content']);

                    $passes = $contentSimilarity <= self::MAX_CONTENT_SIMILARITY
                        && $titleSimilarity <= self::MAX_TITLE_SIMILARITY
                        && $sharedWordRun <= self::MAX_SHARED_WORD_RUN
                        && $unsupportedNumbers === [];

                    if ($passes) {
                        $best = $draft;
                        break;
                    }

                    $feedback = trim(implode(' ', array_filter([
                        $contentSimilarity > self::MAX_CONTENT_SIMILARITY
                            ? 'Kaynak cumle yapilarina fazla benzedin. Haberi farkli paragraf sirasi ve tamamen yeni cumlelerle bastan kur.'
                            : null,
                        $titleSimilarity > self::MAX_TITLE_SIMILARITY
                            ? 'Baslik kaynak basliga fazla benziyor. Farkli vurgu ve kelime sirasi kullan.'
                            : null,
                        $sharedWordRun > self::MAX_SHARED_WORD_RUN
                            ? 'Kaynak metinden uzun bir ifade aynen tasinmis. Bu ifadeyi yeniden kur.'
                            : null,
                        $unsupportedNumbers !== []
                            ? 'Kaynakta olmayan sayilari kaldir: ' . implode(', ', $unsupportedNumbers) . '.'
                            : null,
                    ])));
                } catch (\Throwable $e) {
                    $lastError = $e;
                    $feedback = 'Onceki deneme teknik veya kalite kontrolu nedeniyle reddedildi. Haberi kaynak olgulara sadik kalarak bastan yaz.';
                }
            }

            if ($best === null) {
                throw $lastError ?? new \RuntimeException('OpenAI gecerli ve ozgun bir taslak uretemedi.');
            }

            $item->forceFill([
                'ai_source_hash' => $sourceHash,
                'ai_title' => $best['title'],
                'ai_summary' => $best['summary'],
                'ai_content' => $best['content'],
                'ai_tags' => $best['tags'],
                'ai_rewritten_at' => now(),
                'ai_rewrite_error' => null,
                'ai_rewrite_attempts' => 0,
            ])->save();

            return $this->resultFromItem($item);
        } catch (\Throwable $e) {
            $item->forceFill([
                'ai_rewrite_error' => Str::limit($e->getMessage(), 2000, ''),
            ])->save();

            Log::warning('OpenAI RSS yeniden yazimi basarisiz', [
                'rss_item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('AI yeniden yazimi basarisiz: ' . $e->getMessage(), 0, $e);
        }
    }

    /** @return array{title: string, summary: string, content: string, tags: array<int, string>} */
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

KURALLAR:
- Kaynakta olmayan bilgi, isim, tarih, sayi, alinti veya yorum EKLEME.
- Kaynak cumlelerini sirayla takip edip kelime degistirerek spin yapma. Paragraf sirasi ve cumle yapisini bastan kur.
- Dogrudan alinti varsa anlamini ve sozlerini degistirme; <blockquote> kullan.
- Baslik kaynak baslikla ayni kelime sirasi ve ayni kalipta olmasin.
- Baslik tercihen 45-65 karakter, summary tercihen 120-160 karakter olsun.
- Tarafsiz, aciklayici, tik tuzagi olmayan Turkce kullan.
- Icerikte yalnizca p, h2, h3, ul, ol, li, strong, em, blockquote, table, thead, tbody, tr, th, td HTML etiketlerini kullan.
- Kaynak URL'sini veya "Kaynak:" satirini metne ekleme.
- Etiketleri content_html sonuna yazma; yalnizca tags alaninda dondur.
{$feedbackBlock}

KAYNAK BASLIK:
{$item->title}

KAYNAK METIN:
{$sourceText}
PROMPT;

        $payload = $this->openAi->structured(
            prompt: $prompt,
            schema: $schema,
            model: $model,
            temperature: min(0.45 + (($attempt - 1) * 0.15), 0.70),
            schemaName: 'ografi_rss_rewrite',
            developerInstruction: 'Kaynak sadakati kritik. Uydurma yapma. Structured output semasina tam uy ve Turkce yaz.',
        );

        $title = Str::limit(trim(strip_tags((string) ($payload['title'] ?? ''))), 500, '');
        $summary = Str::limit($this->plainText((string) ($payload['summary'] ?? '')), 500, '');
        $content = $this->sanitizeGeneratedHtml((string) ($payload['content_html'] ?? ''));
        $tags = $this->normalizeTags((array) ($payload['tags'] ?? []));

        if ($title === '' || $summary === '' || mb_strlen($this->plainText($content)) < 180 || count($tags) < 3) {
            throw new \RuntimeException('OpenAI eksik, cok kisa veya etiketsiz icerik dondurdu.');
        }

        return compact('title', 'summary', 'content', 'tags');
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
            $html
        ) ?? $html;

        if (! str_contains($html, '<')) {
            $paragraphs = array_filter(array_map('trim', preg_split('/\R{2,}/u', $html) ?: []));
            $html = implode("\n", array_map(fn (string $p) => '<p>' . e($p) . '</p>', $paragraphs));
        }

        $html = preg_replace('#<p>\s*(?:Kaynak|Source)\s*:.*?</p>\s*$#isu', '', $html) ?? $html;
        $html = preg_replace('#<p>\s*(?:Etiketler|Anahtar\s*Kelimeler|Tags|Keywords)\s*:.*?</p>\s*$#isu', '', $html) ?? $html;

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

    /** @return array{title: string, summary: string, content: string, tags: array<int, string>} */
    private function resultFromItem(RssItem $item): array
    {
        return [
            'title' => (string) $item->ai_title,
            'summary' => (string) $item->ai_summary,
            'content' => (string) $item->ai_content,
            'tags' => $this->normalizeTags((array) ($item->ai_tags ?? [])),
        ];
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

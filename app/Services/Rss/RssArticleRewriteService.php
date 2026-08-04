<?php

namespace App\Services\Rss;

use App\Models\RssItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RssArticleRewriteService
{
    /** Incrementing this value intentionally refreshes previously cached AI rewrites. */
    public const PROMPT_VERSION = 'seo-2026-08-v4';

    /**
     * How many drafts to request from the model when a previous draft is judged
     * too textually close to the source (see similarity checks below). Kept low
     * because every extra attempt is another Ollama round-trip inside the
     * rate-limited AI queue (rss:ai-process handles a handful of items per
     * minute across every ai_rewrite_enabled feed).
     */
    private const MAX_ATTEMPTS = 3;

    /**
     * Above this 4-word-shingle Jaccard overlap between the source body text and
     * the generated body text, the draft is treated as a near-paraphrase rather
     * than a genuine rewrite and triggers a retry. This mirrors standard
     * near-duplicate document detection (shingling + Jaccard similarity, the
     * technique behind tools like MinHash/SimHash dedup): two independently
     * worded texts covering the same facts typically land well under ~0.30
     * overlap on 4-grams, while carrying whole source phrases over verbatim
     * pushes it much higher.
     */
    private const MAX_CONTENT_SIMILARITY = 0.30;

    /**
     * Single-word Jaccard on the title. Titles legitimately reuse a handful of
     * keywords (names, places, product names) so the bar is looser than for the
     * full article body.
     */
    private const MAX_TITLE_SIMILARITY = 0.55;

    public static function expectedSourceHash(string $itemHash): string
    {
        return hash('sha256', self::PROMPT_VERSION . '|' . $itemHash);
    }

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

        try {
            $sourceText = $this->sourceText($item);

            if (mb_strlen($sourceText) < 120) {
                throw new \RuntimeException('AI yeniden yazimi icin kaynak metin cok kisa.');
            }

            $selectedModel = $model ?: config('services.ollama.model', 'gpt-oss:20b');

            $best = null;
            $bestScore = null;
            $bestSimilarity = null;
            $feedback = null;
            $lastError = null;

            for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
                try {
                    $draft = $this->generateDraft($item, $sourceText, $selectedModel, $feedback, $attempt);
                } catch (\Throwable $e) {
                    $lastError = $e;
                    continue;
                }

                $contentSimilarity = $this->shingleJaccardSimilarity($sourceText, $draft['content'], 4);
                $titleSimilarity = $this->wordSetJaccardSimilarity((string) $item->title, $draft['title']);

                // Normalized against each metric's own threshold so the "best of N"
                // comparison is meaningful even though the two scores use different scales.
                $score = max(
                    $contentSimilarity / self::MAX_CONTENT_SIMILARITY,
                    $titleSimilarity / self::MAX_TITLE_SIMILARITY
                );

                if ($best === null || $score < $bestScore) {
                    $best = $draft;
                    $bestScore = $score;
                    $bestSimilarity = ['content' => $contentSimilarity, 'title' => $titleSimilarity];
                }

                if ($contentSimilarity <= self::MAX_CONTENT_SIMILARITY && $titleSimilarity <= self::MAX_TITLE_SIMILARITY) {
                    break;
                }

                $feedback = $this->similarityFeedback($contentSimilarity, $titleSimilarity);
            }

            if ($best === null) {
                throw $lastError ?? new \RuntimeException('Ollama gecerli bir taslak uretemedi.');
            }

            if ($bestScore !== null && $bestScore > 1) {
                Log::warning('RSS AI rewrite maksimum deneme sonrasinda da kaynakla beklenenden fazla benzer kaldi, en iyi taslak kullanildi', [
                    'rss_item_id' => $item->id,
                    'attempts' => self::MAX_ATTEMPTS,
                    'similarity' => $bestSimilarity,
                ]);
            }

            $item->forceFill([
                'ai_source_hash' => $sourceHash,
                'ai_title' => $best['title'],
                'ai_summary' => $best['summary'],
                'ai_content' => $best['content'],
                'ai_tags' => $best['tags'],
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

    /**
     * @return array{title: string, summary: string, content: string, tags: array<int, string>}
     */
    private function generateDraft(RssItem $item, string $sourceText, string $model, ?string $feedback, int $attempt): array
    {
        $apiKey = (string) config('services.ollama.api_key');
        $baseUrl = rtrim((string) config('services.ollama.url', 'https://ollama.com'), '/');
        $generateEndpoint = str_ends_with($baseUrl, '/api')
            ? $baseUrl . '/generate'
            : $baseUrl . '/api/generate';

        if ($apiKey === '') {
            throw new \RuntimeException('Ollama API key eksik. .env icine OLLAMA_API_KEY ekleyin.');
        }

        // Each retry nudges the temperature up a little so the model diverges
        // further from its previous (too-similar) attempt instead of regenerating
        // near-identical text.
        $temperature = min(0.35 + (($attempt - 1) * 0.2), 0.75);

        $response = Http::withoutVerifying()
            ->timeout((int) config('services.ollama.timeout', 120))
            ->acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->post($generateEndpoint, [
                'model' => $model,
                'stream' => false,
                'format' => 'json',
                'prompt' => $this->prompt($item, $sourceText, $feedback),
                'options' => [
                    'temperature' => $temperature,
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
        $content = $this->stripTrailingTagList(
            $this->stripSourceAttribution(
                $this->sanitizeGeneratedHtml((string) ($payload['content_html'] ?? ''))
            )
        );
        $tags = $this->normalizeTags((array) ($payload['tags'] ?? []));

        if ($title === '' || $summary === '' || mb_strlen($this->plainText($content)) < 120) {
            throw new \RuntimeException('Ollama eksik veya cok kisa icerik dondurdu.');
        }

        return compact('title', 'summary', 'content', 'tags');
    }

    private function similarityFeedback(float $contentSimilarity, float $titleSimilarity): string
    {
        $notes = [];

        if ($contentSimilarity > self::MAX_CONTENT_SIMILARITY) {
            $notes[] = 'Onceki taslagin govde metni kaynakla kelimesi kelimesine ortusen ifadeler iceriyordu. Bu sefer paragraflarin sirasini, cumle yapisini ve kelime secimini bastan degistirerek yaz; ayni cumleyi sadece birkac kelime degistirerek tekrar etme.';
        }

        if ($titleSimilarity > self::MAX_TITLE_SIMILARITY) {
            $notes[] = 'Onceki taslagin basligi kaynak baslikla neredeyse ayniydi. Farkli bir vurgu, farkli kelimelerle, kaynak basligin kelime sirasini takip etmeyen tamamen yeni bir baslik kur.';
        }

        return implode(' ', $notes);
    }

    private function resultFromItem(RssItem $item): array
    {
        return [
            'title' => (string) $item->ai_title,
            'summary' => (string) $item->ai_summary,
            'content' => $this->stripTrailingTagList($this->stripSourceAttribution((string) $item->ai_content)),
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

    /**
     * Word-shingle Jaccard similarity between two already-plain-text strings.
     * Standard near-duplicate-detection measure: split each text into overlapping
     * $shingleSize-word phrases, then compare the two phrase sets. 0 = no shared
     * phrasing at all, 1 = identical. Used to catch the model just translating/
     * paraphrasing the source sentence-by-sentence instead of truly rewriting it.
     */
    private function shingleJaccardSimilarity(string $a, string $b, int $shingleSize): float
    {
        $setA = array_unique($this->wordShingles($this->plainText($a), $shingleSize));
        $setB = array_unique($this->wordShingles($this->plainText($b), $shingleSize));

        if ($setA === [] || $setB === []) {
            return 0.0;
        }

        $union = count(array_unique(array_merge($setA, $setB)));

        return $union > 0 ? count(array_intersect($setA, $setB)) / $union : 0.0;
    }

    /** Single-word Jaccard similarity, used for short strings like titles. */
    private function wordSetJaccardSimilarity(string $a, string $b): float
    {
        return $this->shingleJaccardSimilarity($a, $b, 1);
    }

    private function wordShingles(string $plainText, int $size): array
    {
        $normalized = $this->normalizeForComparison($plainText);
        $words = array_values(array_filter(preg_split('/\s+/u', $normalized) ?: []));

        if ($size <= 1) {
            return $words;
        }

        if (count($words) < $size) {
            return $words === [] ? [] : [implode(' ', $words)];
        }

        $shingles = [];
        $limit = count($words) - $size;
        for ($i = 0; $i <= $limit; $i++) {
            $shingles[] = implode(' ', array_slice($words, $i, $size));
        }

        return $shingles;
    }

    private function normalizeForComparison(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
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

            $html = implode("\n", array_map(
                fn (string $paragraph) => '<p>' . e($paragraph) . '</p>',
                $paragraphs
            ));
        }

        return trim($html);
    }

    private function stripSourceAttribution(string $html): string
    {
        $html = preg_replace(
            '#<p>\s*(?:Kaynak|Source)\s*:\s*(?:<a\b[^>]*>.*?</a>|https?://\S+)\s*</p>#isu',
            '',
            $html
        ) ?? $html;

        return trim($html);
    }

    /**
     * The rewrite prompt explicitly asks the model to keep tags in the separate
     * `tags` JSON field, but LLMs occasionally ignore this and append a trailing
     * "Etiketler: #x #y" style line (or a paragraph made up almost entirely of
     * hashtags) inside content_html anyway. Strip it defensively so tags never
     * end up duplicated inside the published article body.
     */
    private function stripTrailingTagList(string $html): string
    {
        // A trailing paragraph explicitly labelled as a tag/keyword list.
        $html = preg_replace(
            '#<p>\s*(?:Etiketler|Anahtar\s*Kelimeler|Tags|Keywords)\s*:.*?</p>\s*$#isu',
            '',
            $html
        ) ?? $html;

        // A trailing paragraph that is (almost) nothing but hashtags, e.g.
        // "<p>#deprem #istanbul #afad #sondakika</p>".
        $html = preg_replace_callback(
            '#<p>([^<]*)</p>\s*$#isu',
            function (array $matches): string {
                $text = trim($matches[1]);
                $withoutHashtags = trim(preg_replace('/#[\p{L}\p{N}_]{2,80}/u', '', $text) ?? $text);

                return $withoutHashtags === '' && $text !== '' ? '' : $matches[0];
            },
            $html
        ) ?? $html;

        return trim($html);
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

    private function prompt(RssItem $item, string $sourceText, ?string $feedback = null): string
    {
        $feedbackBlock = $feedback !== null && $feedback !== ''
            ? "\n\nONEMLI DUZELTME (onceki taslak reddedildi, dikkatle uygula): {$feedback}\n"
            : '';

        return <<<PROMPT
Sen "Ografi" haber/tanitim sitesinin editorusun. Asagidaki RSS kaynagindan Turkce, tamamen ozgun, insan
odakli ve arama motorlarinda anlasilir yeni bir yazi uret. Amac ceviri ya da parafraz degil, ayni olay/bilgiyi
sifirdan, kendi kelimelerinle, Ografi'nin kendi editoryal sesiyle anlatan bagimsiz bir metin yazmak.

OZGUNLUK KURALLARI (en onemli kisim):
- Kaynagin cumlelerini sirayla takip ederek, sadece birkac kelime degistirerek yazma (bu bir "spin" olur, kabul edilmez). Once haberin ana noktalarini kendi zihninde ozetle, sonra bu noktalari tamamen farkli bir cumle sirasi, farkli paragraf yapisi ve farkli kelime dagarciyla yeniden anlat.
- Kaynak metnin ilk cumlesini/girisini aynen ya da hafif degistirerek acilis cumlesi yapma; yaziya farkli bir acidan (ornegin sonuc, etki veya baglamdan) baslayabilirsin.
- Cumle uzunluklarini cesitlendir: bazi cumleler kisa ve vurgulu, bazilari daha aciklayici olsun. Kaynaktaki cumle kaliplarini birebir tasima.
- Baslik ve ozet, kaynagin baslik/ozetiyle kelime kelime ortusmesin; ayni bilgiyi farkli bir ifadeyle, farkli kelime sirasiyla ver.
- Etiketler kaynaktaki hashtag'lerin birebir kopyasi olmasin; konuyu/kategoriyi yansitan kisa, dogal Turkce anahtar kelimeler uret.

DOGRULUK KURALLARI (ayni derecede onemli):
- Kaynakta bulunmayan bilgi, alinti, tarih, sayi veya iddia ekleme; hicbir gercegi degistirme veya abartma.
- Kaynaktaki kisi, kurum, yer, urun, tarih ve sayi gibi somut bilgileri dogru ve degismeden koru; sadece bunlarin etrafindaki anlatimi/cumle yapisini degistiriyorsun.
- Bir kisiye (yetkili, tanik, uzman) ait dogrudan alinti varsa, alintinin kendi ifadesini degistirme (fikir/lafizi bozma), sadece <blockquote> ile sun.
- Gorsel veya video hakkinda kaynakta bulunmayan aciklama uydurma.

USLUP (Ografi editoryal sesi):
- Tarafsiz, guncel, aciklayici ve okur odakli bir haber dili kullan; abartili/tik tuzagi ifadelerden kacin.
- Baslik 45-65 karakter civarinda olsun; ana konuyu ilk bolumde acikca anlatsin, tik tuzagi ve anahtar kelime yigini olmasin.
- Summary 120-160 karakter civarinda, tek basina anlamli bir meta aciklamasi olsun; basligi ya da birbirini gereksiz tekrarlamasin.
- Ilk paragraf haberin temel sorusunu dogrudan cevaplasin. Devaminda anlamli h2/h3 basliklari, kisa paragraflar ve gerekiyorsa listeler kullan.
- Metnin icine kaynak, kaynak URL, internet adresi veya baglanti ekleme. Kaynak ayri bir kutuda gosterilecek.
- Yaziyi tek duzden, alt alta siralanmis ayni bicimde paragraflar halinde yazma. Icerigin yapisini konuya gore cesitlendir:
  - Kaynakta karsilastirilabilir sayisal veri, fiyat, tarih/program, siralama veya liste halinde durum varsa (ornegin birden fazla kurum/urun/rakam karsilastirmasi) bunu <table><tr><th>...</th></tr><tr><td>...</td></tr></table> ile duzenli bir tabloda goster, ayni bilgiyi ayrica duz paragrafta tekrarlama.
  - Kaynakta bir kisiye ait dogrudan alinti/aciklama varsa bunu <blockquote> ile ayri ve belirgin goster.
  - Sadece kaynakta gercekten var olan veriler icin tablo/alinti kullan; veri veya alinti yoksa uydurma, duz paragraf ve basliklarla devam et.
{$feedbackBlock}
JSON disinda hicbir sey dondurme.

JSON semasi:
{"title":"benzersiz baslik","summary":"en fazla 2 cumlelik ozet","content_html":"yalnizca p, h2, h3, ul, ol, li, strong, em, blockquote, table, thead, tbody, tr, th, td etiketleriyle HTML - uygun oldugunda tablo ve alinti kullan, sadece duz paragraflar yigma","tags":["3-8 kisa etiket"]}

Kaynak metin:
{$sourceText}
PROMPT;
    }
}

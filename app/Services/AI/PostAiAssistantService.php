<?php

namespace App\Services\AI;

use App\Models\Post;
use Illuminate\Support\Str;

class PostAiAssistantService
{
    private const CONTENT_OPERATIONS = ['rewrite', 'proofread', 'shorten', 'expand', 'custom'];

    public function __construct(private readonly OpenAiService $openAi)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function editAndSave(
        Post $post,
        string $operation,
        ?string $instruction = null,
        ?string $model = null,
    ): array {
        $operation = trim($operation);
        $instruction = trim((string) $instruction);

        if (! in_array($operation, ['rewrite', 'proofread', 'shorten', 'expand', 'title', 'seo', 'custom'], true)) {
            throw new \InvalidArgumentException('Gecersiz AI duzenleme islemi.');
        }

        if ($operation === 'custom' && $instruction === '') {
            throw new \InvalidArgumentException('Ozel AI islemi icin talimat yazmalisiniz.');
        }

        if (in_array($operation, self::CONTENT_OPERATIONS, true) && $this->hasUnsupportedEditorBlocks($post)) {
            throw new \RuntimeException(
                'Bu gonderide gorsel, video, embed veya ozel EditorJS bloklari var. '
                . 'Medya bloklarini bozmamak icin icerik yeniden yazma engellendi. Baslik veya SEO islemini kullanabilirsiniz.'
            );
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'excerpt' => ['type' => 'string'],
                'content_html' => ['type' => 'string'],
                'meta_title' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'meta_keywords' => ['type' => 'string'],
                'change_summary' => ['type' => 'string'],
            ],
            'required' => [
                'title',
                'excerpt',
                'content_html',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'change_summary',
            ],
            'additionalProperties' => false,
        ];

        $payload = $this->openAi->structured(
            prompt: $this->prompt($post, $operation, $instruction),
            schema: $schema,
            model: $model,
            temperature: $this->temperatureFor($operation),
            schemaName: 'ografi_post_editor',
            developerInstruction: 'Ografi icin Turkce editorluk yap. Mevcut olgulari koru, uydurma bilgi ekleme, structured output semasina tam uy.',
        );

        $result = $this->normalizeResult($post, $payload, $operation);

        $updates = [
            'edited_at' => now(),
            'edited_reason' => Str::limit('OpenAI: ' . $result['change_summary'], 1000, ''),
        ];

        if ($operation === 'title') {
            $updates['title'] = $result['title'];
            $updates['meta_title'] = $result['meta_title'];
        } elseif ($operation === 'seo') {
            $updates['excerpt'] = $result['excerpt'];
            $updates['meta_title'] = $result['meta_title'];
            $updates['meta_description'] = $result['meta_description'];
            $updates['meta_keywords'] = $result['meta_keywords'];
        } else {
            $updates['title'] = $result['title'];
            $updates['excerpt'] = $result['excerpt'];
            $updates['meta_title'] = $result['meta_title'];
            $updates['meta_description'] = $result['meta_description'];
            $updates['meta_keywords'] = $result['meta_keywords'];

            if (in_array($operation, self::CONTENT_OPERATIONS, true)) {
                $updates['content'] = $result['content'];
                $updates['content_json'] = $this->htmlToEditorJs($result['content']);
            }
        }

        // Slug deliberately stays unchanged so existing post URLs never break.
        $post->fill($updates);
        $post->save();

        return $result;
    }

    private function prompt(Post $post, string $operation, string $instruction): string
    {
        $task = match ($operation) {
            'rewrite' => 'Baslik, ozet ve govdeyi ayni olgulari koruyarak bastan ve daha akici sekilde yeniden yaz. SEO alanlarini yeni metinle uyumlu guncelle.',
            'proofread' => 'Yazim, noktalama, anlatim bozuklugu ve gereksiz tekrarlarini duzelt. Anlami ve olgulari degistirme. Basligi gerekmedikce degistirme.',
            'shorten' => 'Govdeyi daha kisa ve yogun hale getir. Ana olgulari, tarihleri, sayilari ve kritik ayrintilari koru. Gereksiz tekrar ve dolgu cumlelerini cikar.',
            'expand' => 'Metni daha aciklayici ve duzenli hale getir ancak YENI BILGI UYDURMA. Yalnizca mevcut metindeki olgulari daha iyi acikla ve yapilandir.',
            'title' => 'Yalnizca title ve meta_title alanlarini iyilestir. Diger alanlari mevcut haliyle aynen dondur. Tik tuzagi kullanma ve slug degisikligi onerme.',
            'seo' => 'Yalnizca excerpt, meta_title, meta_description ve meta_keywords alanlarini SEO icin iyilestir. Title ve content_html alanlarini mevcut haliyle aynen dondur.',
            'custom' => 'Kullanicinin ozel talimatini uygula. Talimat disinda gereksiz degisiklik yapma: ' . $instruction,
        };

        $extraInstructionBlock = $operation !== 'custom' && $instruction !== ''
            ? "\n\nEK KULLANICI TALIMATI:\n{$instruction}\n"
            : '';

        $title = (string) $post->title;
        $excerpt = (string) $post->excerpt;
        $content = (string) $post->content;
        $metaTitle = (string) $post->meta_title;
        $metaDescription = (string) $post->meta_description;
        $metaKeywords = (string) $post->meta_keywords;

        return <<<PROMPT
GOREV:
{$task}{$extraInstructionBlock}

ZORUNLU KURALLAR:
- Turkce yaz.
- Mevcut metinde bulunmayan kisi, kurum, olay, tarih, sayi, alinti veya iddia ekleme.
- title en fazla 255 karakter olsun.
- excerpt tercihen 120-200 karakter araliginda olsun.
- meta_title en fazla 65 karakter olsun.
- meta_description en fazla 160 karakter olsun.
- meta_keywords virgulle ayrilmis kisa anahtar kelimeler olsun.
- content_html icin yalnizca p, h2, h3, ul, ol, li, strong, em, blockquote, table, thead, tbody, tr, th, td etiketlerini kullan.
- script, style, iframe, form, class, style veya event attribute uretme.
- Degistirilmemesi istenen alanlari ASAGIDAKI MEVCUT DEGERLERLE aynen dondur.

MEVCUT TITLE:
{$title}

MEVCUT EXCERPT:
{$excerpt}

MEVCUT CONTENT_HTML:
{$content}

MEVCUT META_TITLE:
{$metaTitle}

MEVCUT META_DESCRIPTION:
{$metaDescription}

MEVCUT META_KEYWORDS:
{$metaKeywords}
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{title: string, excerpt: string, content: string, meta_title: string, meta_description: string, meta_keywords: string, change_summary: string}
     */
    private function normalizeResult(Post $post, array $payload, string $operation): array
    {
        $title = Str::limit(trim(strip_tags((string) ($payload['title'] ?? $post->title))), 255, '');
        $excerpt = Str::limit($this->plainText((string) ($payload['excerpt'] ?? $post->excerpt)), 500, '');
        $metaTitle = Str::limit(trim(strip_tags((string) ($payload['meta_title'] ?? $post->meta_title))), 65, '');
        $metaDescription = Str::limit($this->plainText((string) ($payload['meta_description'] ?? $post->meta_description)), 160, '');
        $metaKeywords = Str::limit($this->plainText((string) ($payload['meta_keywords'] ?? $post->meta_keywords)), 255, '');
        $changeSummary = Str::limit($this->plainText((string) ($payload['change_summary'] ?? $this->operationLabel($operation))), 900, '');

        if ($title === '') {
            $title = (string) $post->title;
        }

        $content = (string) $post->content;
        if (in_array($operation, self::CONTENT_OPERATIONS, true)) {
            $content = $this->sanitizeGeneratedHtml((string) ($payload['content_html'] ?? ''));
            if (mb_strlen($this->plainText($content)) < 40) {
                throw new \RuntimeException('OpenAI cok kisa veya bos icerik dondurdu; gonderi degistirilmedi.');
            }
        }

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_keywords' => $metaKeywords,
            'change_summary' => $changeSummary !== '' ? $changeSummary : $this->operationLabel($operation),
        ];
    }

    private function hasUnsupportedEditorBlocks(Post $post): bool
    {
        if (preg_match('/<(?:img|picture|figure|video|audio|iframe|embed|source)\b/i', (string) $post->content)) {
            return true;
        }

        if (! is_array($post->content_json)) {
            return false;
        }

        $allowed = ['paragraph', 'header', 'list', 'quote', 'table'];

        foreach ((array) ($post->content_json['blocks'] ?? []) as $block) {
            $type = is_array($block) ? (string) ($block['type'] ?? '') : '';
            if ($type !== '' && ! in_array($type, $allowed, true)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, mixed> */
    private function htmlToEditorJs(string $html): array
    {
        $blocks = [];

        preg_match_all(
            '#<(p|h2|h3|ul|ol|blockquote|table)\b[^>]*>(.*?)</\1>#is',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $tag = strtolower((string) ($match[1] ?? ''));
            $inner = trim((string) ($match[2] ?? ''));

            if ($tag === 'p') {
                $text = $this->sanitizeInlineHtml($inner);
                if ($this->plainText($text) !== '') {
                    $blocks[] = ['type' => 'paragraph', 'data' => ['text' => $text]];
                }
                continue;
            }

            if (in_array($tag, ['h2', 'h3'], true)) {
                $text = $this->sanitizeInlineHtml($inner);
                if ($this->plainText($text) !== '') {
                    $blocks[] = [
                        'type' => 'header',
                        'data' => ['text' => $text, 'level' => $tag === 'h2' ? 2 : 3],
                    ];
                }
                continue;
            }

            if (in_array($tag, ['ul', 'ol'], true)) {
                preg_match_all('#<li\b[^>]*>(.*?)</li>#is', $inner, $itemMatches);
                $items = collect($itemMatches[1] ?? [])
                    ->map(fn ($item) => $this->sanitizeInlineHtml((string) $item))
                    ->filter(fn ($item) => $this->plainText((string) $item) !== '')
                    ->values()
                    ->all();

                if ($items !== []) {
                    $blocks[] = [
                        'type' => 'list',
                        'data' => [
                            'style' => $tag === 'ol' ? 'ordered' : 'unordered',
                            'items' => $items,
                        ],
                    ];
                }
                continue;
            }

            if ($tag === 'blockquote') {
                $text = $this->sanitizeInlineHtml($inner);
                if ($this->plainText($text) !== '') {
                    $blocks[] = [
                        'type' => 'quote',
                        'data' => ['text' => $text, 'caption' => '', 'alignment' => 'left'],
                    ];
                }
                continue;
            }

            if ($tag === 'table') {
                $rows = [];
                preg_match_all('#<tr\b[^>]*>(.*?)</tr>#is', $inner, $rowMatches);
                foreach ($rowMatches[1] ?? [] as $rowHtml) {
                    preg_match_all('#<(?:th|td)\b[^>]*>(.*?)</(?:th|td)>#is', (string) $rowHtml, $cellMatches);
                    $cells = array_map(
                        fn ($cell) => $this->sanitizeInlineHtml((string) $cell),
                        $cellMatches[1] ?? []
                    );
                    if ($cells !== []) {
                        $rows[] = $cells;
                    }
                }

                if ($rows !== []) {
                    $blocks[] = ['type' => 'table', 'data' => ['content' => $rows]];
                }
            }
        }

        if ($blocks === []) {
            $text = e($this->plainText($html));
            if ($text !== '') {
                $blocks[] = ['type' => 'paragraph', 'data' => ['text' => $text]];
            }
        }

        return [
            'time' => (int) round(microtime(true) * 1000),
            'blocks' => $blocks,
        ];
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

        return trim($html);
    }

    private function sanitizeInlineHtml(string $html): string
    {
        $html = strip_tags($html, '<strong><em>');
        $html = preg_replace('/<(strong|em)\b[^>]*>/i', '<$1>', $html) ?? $html;
        return trim($html);
    }

    private function plainText(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    private function temperatureFor(string $operation): float
    {
        return match ($operation) {
            'proofread', 'seo', 'title' => 0.2,
            'rewrite', 'shorten' => 0.45,
            'expand', 'custom' => 0.55,
            default => 0.3,
        };
    }

    private function operationLabel(string $operation): string
    {
        return match ($operation) {
            'rewrite' => 'Gonderi yeniden yazildi',
            'proofread' => 'Yazim ve anlatim duzeltildi',
            'shorten' => 'Gonderi kisaltildi',
            'expand' => 'Gonderi mevcut bilgilerle genisletildi',
            'title' => 'Baslik iyilestirildi',
            'seo' => 'SEO alanlari iyilestirildi',
            'custom' => 'Ozel AI talimati uygulandi',
            default => 'AI duzenlemesi uygulandi',
        };
    }
}

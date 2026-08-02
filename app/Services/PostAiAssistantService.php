<?php

namespace App\Services;

use Illuminate\Support\Str;

class PostAiAssistantService
{
    /**
     * Reads the in-progress post draft and returns SEO metadata plus short
     * writing suggestions. Used by the AI assistant button on /blog/create.
     *
     * @return array{meta_title: string, meta_description: string, meta_keywords: array<int, string>, excerpt: string, suggestions: string}
     */
    public function assist(string $title, string $content): array
    {
        $title = trim($title);
        $plainContent = Str::limit(
            trim(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            6000,
            '',
        );

        if ($title === '' && $plainContent === '') {
            throw new \InvalidArgumentException('Baslik veya icerik bos olamaz.');
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'meta_title' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'meta_keywords' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'excerpt' => ['type' => 'string'],
                'suggestions' => ['type' => 'string'],
            ],
            'required' => ['meta_title', 'meta_description', 'meta_keywords', 'excerpt', 'suggestions'],
        ];

        $prompt = <<<PROMPT
Bir haber/blog yazarina gonderisi icin yardim ediyorsun. Asagidaki taslak baslik ve icerigi
oku, anla ve JSON semasina gore cevap ver. Yazarin kendi metnini degistirme, sadece SEO
alanlarini doldur ve kisa gelistirme onerisi ver.

- meta_title: 50-60 karakter civarinda, arama motoru icin optimize edilmis baslik.
- meta_description: 140-160 karakter civarinda, tikalamayi tesvik eden ozet.
- meta_keywords: icerikle alakali 3-6 kisa anahtar kelime.
- excerpt: 1-2 cumlelik, akista gorunecek kisa ozet (140 karakteri gecmesin).
- suggestions: yazarin basligini/icerigini gelistirmesi icin 2-4 cumlelik somut, yapici Turkce
  oneri (eksik bilgi, yapi, netlik gibi). Elestirel ama destekleyici bir dil kullan.

JSON disinda hicbir sey dondurme.

Baslik: {$title}
Icerik: {$plainContent}
PROMPT;

        $result = app(OllamaService::class)->chatStructured(
            messages: [['role' => 'user', 'content' => $prompt]],
            schema: $schema,
            temperature: 0.4,
        );

        $keywords = collect((array) ($result['meta_keywords'] ?? []))
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter()
            ->unique(fn ($keyword) => Str::lower($keyword))
            ->take(6)
            ->values()
            ->all();

        return [
            'meta_title' => Str::limit(trim((string) ($result['meta_title'] ?? '')), 65, ''),
            'meta_description' => Str::limit(trim((string) ($result['meta_description'] ?? '')), 160, ''),
            'meta_keywords' => $keywords,
            'excerpt' => Str::limit(trim((string) ($result['excerpt'] ?? '')), 200, ''),
            'suggestions' => Str::limit(trim((string) ($result['suggestions'] ?? '')), 800, ''),
        ];
    }
}

<?php

namespace App\Services\Rss;

use App\Models\RssItem;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RssContentModerationService
{
    /**
     * Fast, dependency-free rejection of rhetorical "is X happening?" clickbait
     * headlines ("Deprem mi oldu?", "... öldü mü?", "... var mı?") - the pattern
     * this news site should never publish as-is, regardless of AI availability.
     */
    private const CLICKBAIT_PATTERNS = [
        '/\b(mi|mı|mu|mü)\s+oldu\s*\?/iu',
        '/\b(mi|mı|mu|mü)\s+öldü\s*\?/iu',
        '/\bvar\s*m[ıi]\s*\?/iu',
        '/\bgerçek\s*mi\s*\?/iu',
        '/\bdoğru\s*mu\s*\?/iu',
        '/\bnerede\s+oldu\s*\?/iu',
        '/\bne\s+zaman\s+oldu\s*\?/iu',
    ];

    public function looksLikeClickbaitQuestion(string $title): bool
    {
        $title = trim($title);

        if ($title === '' || ! str_ends_with($title, '?')) {
            return false;
        }

        foreach (self::CLICKBAIT_PATTERNS as $pattern) {
            if (preg_match($pattern, $title) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{title: string, summary: string, content: string}  $rewritten
     * @return array{reject: bool, reject_reason: ?string, is_nsfw: bool, nsfw_reason: ?string}
     */
    public function moderate(RssItem $item, array $rewritten, ?string $imageUrl = null): array
    {
        // Check the ORIGINAL source title, not the rewritten one - the AI rewrite step
        // deliberately polishes clickbait-question titles into clean-sounding prose
        // ("Deprem mi oldu?" -> "Sosyal Medyada Deprem İddiası"), which would otherwise
        // let a fundamentally speculative/rumor-based story slip through moderation.
        $originalTitle = (string) ($item->title ?? '');
        $rewrittenTitle = (string) ($rewritten['title'] ?? '');

        if ($this->looksLikeClickbaitQuestion($originalTitle) || $this->looksLikeClickbaitQuestion($rewrittenTitle)) {
            return [
                'reject' => true,
                'reject_reason' => 'Baslik tik tuzagi sorusu formatinda ("... mi oldu?" vb.)',
                'is_nsfw' => false,
                'nsfw_reason' => null,
            ];
        }

        try {
            return $this->moderateWithAi($item, $rewritten, $imageUrl);
        } catch (\Throwable $e) {
            Log::warning('RSS icerik moderasyonu basarisiz, icerik yayinlanacak (fail-open)', [
                'rss_item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'reject' => false,
                'reject_reason' => null,
                'is_nsfw' => false,
                'nsfw_reason' => null,
            ];
        }
    }

    /**
     * @param  array{title: string, summary: string, content: string}  $rewritten
     * @return array{reject: bool, reject_reason: ?string, is_nsfw: bool, nsfw_reason: ?string}
     */
    private function moderateWithAi(RssItem $item, array $rewritten, ?string $imageUrl): array
    {
        $plainContent = Str::limit(
            trim(html_entity_decode(strip_tags((string) ($rewritten['content'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            2500,
            '',
        );

        $images = [];
        if ($imageUrl) {
            $encoded = $this->downloadAsBase64($imageUrl);
            if ($encoded !== null) {
                $images[] = $encoded;
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'is_clickbait_question' => ['type' => 'boolean'],
                'is_nsfw' => ['type' => 'boolean'],
                'nsfw_reason' => ['type' => 'string'],
            ],
            'required' => ['is_clickbait_question', 'is_nsfw'],
        ];

        $originalTitle = (string) ($item->title ?? '');

        $prompt = <<<PROMPT
Bu bir haber sitesi icerik denetim gorevidir. Bu site sadece dogrulanmis, guncel haberler yayinlar - dogrulanmamis
söylenti, "sosyal medyada iddia edildi" turu spekulatif hikayeler veya tik tuzagi sorular icin bir yer degildir.
Asagidaki haberi (hem orijinal kaynak basligini hem de yeniden yazilmis halini) degerlendir ve JSON semasina gore cevap ver.

1) is_clickbait_question: ASIL soru: bu haberin KONUSU dogrulanmamis bir soylenti/iddia mi (orn. "sosyal medyada X oldugu iddia edildi ama resmi aciklama yok"), yoksa orijinal kaynak basligi "oldu mu?", "var mi?" gibi retorik bir soru formatinda mi? Yeniden yazilmis baslik duzgun bir cumle haline getirilmis olsa bile, ALTINDAKI HIKAYE dogrulanmamis bir iddiaysa veya orijinal kaynak basligi tik tuzagi sorusuysa, bunu true olarak isaretle. Gercek, dogrulanmis, bilgi iceren haberler bu kapsamda DEGILDIR.
2) is_nsfw: Metin veya varsa ekteki gorsel +18 icerik, ciplaklik, cinsellik, agir siddet, kan veya vahset iceriyor mu? Bir haberin siddet OLAYINDAN bahsetmesi tek basina yeterli degildir (orn. "trafik kazasinda 2 kisi oldu" NSFW degildir) - sadece grafik/rahatsiz edici gorsel/metinsel detay varsa isaretle.
3) NSFW ise nsfw_reason alanina kisa Turkce aciklama yaz.

Orijinal kaynak basligi: {$originalTitle}
Yeniden yazilmis baslik: {$rewritten['title']}
Ozet: {$rewritten['summary']}
Icerik: {$plainContent}
PROMPT;

        $result = app(OllamaService::class)->chatStructured(
            messages: [['role' => 'user', 'content' => $prompt]],
            schema: $schema,
            images: $images,
        );

        $isClickbait = (bool) ($result['is_clickbait_question'] ?? false);
        $isNsfw = (bool) ($result['is_nsfw'] ?? false);

        return [
            'reject' => $isClickbait,
            'reject_reason' => $isClickbait ? 'AI: baslik tik tuzagi sorusu olarak degerlendirildi' : null,
            'is_nsfw' => $isNsfw,
            'nsfw_reason' => $isNsfw ? Str::limit((string) ($result['nsfw_reason'] ?? ''), 255, '') : null,
        ];
    }

    private function downloadAsBase64(string $url): ?string
    {
        try {
            $response = Http::withoutVerifying()->timeout(8)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();

            if (strlen($body) > 8 * 1024 * 1024) {
                return null;
            }

            return base64_encode($body);
        } catch (\Throwable) {
            return null;
        }
    }
}

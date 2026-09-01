<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostShowTypographyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->routeIs('blog.post')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html)
            || $html === ''
            || ! str_contains($html, 'ps-post-body')
            || str_contains($html, 'data-ografi-post-show-typography-final')
        ) {
            return $response;
        }

        // Haber/RSS kaynaklari bazen ara basliklari <h2> yerine duz <p> olarak verir.
        // Tamami buyuk harfli, kisa ve en az iki kelimelik bu satirlari tarayiciya
        // gonderilmeden once gercek H2 yap. CSS/JS ile baslik taklidi yapilmaz.
        $html = $this->normalizeSemanticSubheadings($html);

        $style = <<<'HTML'
<style data-ografi-post-show-typography-final>
/*
 * Post-show makale tipografisi.
 * Tek semantik sistem:
 * - Govde: 17px / 1.65
 * - Kalin: 700
 * - Basliklar: H2 > H3 > H4 > H5 > H6
 */
html body.alma-app .post-show-shell .ps-post-title:not(#comments):not(#comments *) {
    font-size: 30px !important;
    line-height: 1.24 !important;
    font-weight: 700 !important;
    letter-spacing: -0.018em !important;
}

html body.alma-app .post-show-shell .ps-post-body:not(#comments):not(#comments *) {
    font-size: 17px !important;
    line-height: 1.65 !important;
    font-weight: 400 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(
    p,
    li,
    blockquote,
    .ce-paragraph,
    .cdx-list__item
):not(#comments):not(#comments *) {
    font-size: 17px !important;
    line-height: 1.65 !important;
    font-weight: 400 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(
    p,
    li,
    blockquote,
    .ce-paragraph,
    .cdx-list__item
) :where(span, font, a, em, i, u, mark):not(#comments):not(#comments *) {
    font-size: inherit !important;
    line-height: inherit !important;
    font-weight: inherit !important;
}

/* Kalin metin gercek kalindir; boyutu degismez. */
html body.alma-app .post-show-shell .ps-post-body :where(strong, b):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(strong, b) :where(span, font, a, em, i, u, mark):not(#comments):not(#comments *) {
    font-size: inherit !important;
    line-height: inherit !important;
    font-weight: 700 !important;
}

/* H2 */
html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]) *:not(#comments):not(#comments *) {
    font-size: 24px !important;
    line-height: 1.3 !important;
    font-weight: 700 !important;
    letter-spacing: -0.012em !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *) {
    margin: 30px 0 12px !important;
}

/* H3 */
html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]) *:not(#comments):not(#comments *) {
    font-size: 21px !important;
    line-height: 1.34 !important;
    font-weight: 700 !important;
    letter-spacing: -0.008em !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *) {
    margin: 26px 0 10px !important;
}

/* H4 */
html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]) *:not(#comments):not(#comments *) {
    font-size: 19px !important;
    line-height: 1.38 !important;
    font-weight: 700 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *) {
    margin: 23px 0 9px !important;
}

/* H5 / H6 */
html body.alma-app .post-show-shell .ps-post-body h5:not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body h5 *:not(#comments):not(#comments *) {
    font-size: 18px !important;
    line-height: 1.4 !important;
    font-weight: 700 !important;
}

html body.alma-app .post-show-shell .ps-post-body h5:not(#comments):not(#comments *) {
    margin: 21px 0 8px !important;
}

html body.alma-app .post-show-shell .ps-post-body h6:not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body h6 *:not(#comments):not(#comments *) {
    font-size: 17px !important;
    line-height: 1.42 !important;
    font-weight: 700 !important;
}

html body.alma-app .post-show-shell .ps-post-body h6:not(#comments):not(#comments *) {
    margin: 20px 0 8px !important;
}

@media (max-width: 640px) {
    html body.alma-app .post-show-shell .ps-post-title:not(#comments):not(#comments *) {
        font-size: 25px !important;
        line-height: 1.28 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body:not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(
        p,
        li,
        blockquote,
        .ce-paragraph,
        .cdx-list__item
    ):not(#comments):not(#comments *) {
        font-size: 16px !important;
        line-height: 1.62 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]) *:not(#comments):not(#comments *) {
        font-size: 22px !important;
        line-height: 1.32 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]) *:not(#comments):not(#comments *) {
        font-size: 20px !important;
        line-height: 1.36 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]) *:not(#comments):not(#comments *) {
        font-size: 18px !important;
        line-height: 1.4 !important;
    }
}
</style>
HTML;

        $html = preg_replace('/<\/body>/i', $style . "\n</body>", $html, 1) ?? ($html . $style);

        $response->setContent($html);
        $response->headers->set('X-Ografi-Post-Typography', 'v7');

        return $response;
    }

    private function normalizeSemanticSubheadings(string $html): string
    {
        return preg_replace_callback(
            '/<p(?P<attrs>\s[^>]*)?>(?P<inner>.*?)<\/p>/isu',
            static function (array $match): string {
                $attrs = (string) ($match['attrs'] ?? '');
                $inner = (string) ($match['inner'] ?? '');

                // Sinifli/ozel paragraflara dokunma; sadece ham haber metni satirlari.
                if (preg_match('/\b(?:class|id|style)\s*=/iu', $attrs)) {
                    return $match[0];
                }

                $text = html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
                $length = mb_strlen($text);

                if ($length < 5 || $length > 80) {
                    return $match[0];
                }

                $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if (count($words) < 2) {
                    return $match[0];
                }

                $lettersOnly = preg_replace('/[^\p{L}]+/u', '', $text) ?? '';
                if (mb_strlen($lettersOnly) < 5) {
                    return $match[0];
                }

                $upper = mb_strtoupper($text, 'UTF-8');
                $lower = mb_strtolower($text, 'UTF-8');

                if ($text !== $upper || $text === $lower) {
                    return $match[0];
                }

                // Uzun cumleleri/normal metinleri basliga cevirmemek icin cumle sonu noktalamasini ele.
                if (preg_match('/[.!?…]["\'”’)]?$/u', $text)) {
                    return $match[0];
                }

                return '<h2' . $attrs . '>' . $inner . '</h2>';
            },
            $html
        ) ?? $html;
    }
}

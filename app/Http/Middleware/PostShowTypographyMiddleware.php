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

        $html = $this->normalizeSemanticSubheadings($html);

        $style = <<<'HTML'
<style data-ografi-post-show-typography-final>
/*
 * Post-show tipografisi.
 * Sadece yazı detay sayfasını etkiler; akış/kartlar/header bu katmandan etkilenmez.
 * Dar içerik sütununda daha sıkı, okunaklı ve dengeli bir ölçek kullanılır.
 */
html body.alma-app .post-show-shell .ps-post-title:not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-title *:not(#comments):not(#comments *) {
    font-family: Inter, Arial, Helvetica, sans-serif !important;
    font-size: 22px !important;
    line-height: 1.3 !important;
    font-weight: 700 !important;
    letter-spacing: -0.012em !important;
}

html body.alma-app .post-show-shell .ps-post-body:not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(
    p,
    div,
    span,
    font,
    a,
    li,
    td,
    th,
    blockquote,
    em,
    i,
    u,
    mark,
    small,
    .ce-paragraph,
    .cdx-block,
    .cdx-list__item
):not(#comments):not(#comments *) {
    font-family: Inter, Arial, Helvetica, sans-serif !important;
    font-size: 15px !important;
    line-height: 1.58 !important;
    font-weight: 400 !important;
}

/* Kalın metin: boyut değişmez, yalnızca ağırlık değişir. */
html body.alma-app .post-show-shell .ps-post-body :where(strong, b):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(strong, b) *:not(#comments):not(#comments *) {
    font-family: Inter, Arial, Helvetica, sans-serif !important;
    font-size: inherit !important;
    line-height: inherit !important;
    font-weight: 700 !important;
}

/* H2 */
html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]) *:not(#comments):not(#comments *) {
    font-family: Inter, Arial, Helvetica, sans-serif !important;
    font-size: 18px !important;
    line-height: 1.36 !important;
    font-weight: 700 !important;
    letter-spacing: -0.004em !important;
}
html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *) {
    margin: 20px 0 7px !important;
}

/* H3 */
html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]) *:not(#comments):not(#comments *) {
    font-family: Inter, Arial, Helvetica, sans-serif !important;
    font-size: 17px !important;
    line-height: 1.38 !important;
    font-weight: 700 !important;
    letter-spacing: -0.003em !important;
}
html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *) {
    margin: 18px 0 7px !important;
}

/* H4 */
html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]) *:not(#comments):not(#comments *) {
    font-family: Inter, Arial, Helvetica, sans-serif !important;
    font-size: 16px !important;
    line-height: 1.42 !important;
    font-weight: 700 !important;
}
html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *) {
    margin: 17px 0 6px !important;
}

/* H5 */
html body.alma-app .post-show-shell .ps-post-body h5:not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body h5 *:not(#comments):not(#comments *) {
    font-family: Inter, Arial, Helvetica, sans-serif !important;
    font-size: 16px !important;
    line-height: 1.42 !important;
    font-weight: 700 !important;
}
html body.alma-app .post-show-shell .ps-post-body h5:not(#comments):not(#comments *) {
    margin: 17px 0 6px !important;
}

/* H6 */
html body.alma-app .post-show-shell .ps-post-body h6:not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body h6 *:not(#comments):not(#comments *) {
    font-family: Inter, Arial, Helvetica, sans-serif !important;
    font-size: 15px !important;
    line-height: 1.45 !important;
    font-weight: 700 !important;
}
html body.alma-app .post-show-shell .ps-post-body h6:not(#comments):not(#comments *) {
    margin: 16px 0 6px !important;
}

/*
 * Post-show blok ritmi.
 * Resim, tablo ve bilgi/uyarı gibi kutuların ardından metin hemen yapışmasın.
 * EditorJS, RSS/HTML ve doğrudan figure/img çıktılarını birlikte kapsar.
 */
html body.alma-app .post-show-shell .ps-post-body .ce-block:has(
    .image-tool,
    figure,
    table,
    .tc-wrap,
    .ce-quote,
    .cdx-quote,
    .cdx-warning,
    .cdx-alert,
    .ce-code,
    .alert,
    .callout,
    .info-box,
    .warning-box
):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body > :where(
    figure,
    .image-tool,
    p:has(> img),
    table,
    .tc-wrap,
    .ce-quote,
    .cdx-quote,
    .cdx-warning,
    .cdx-alert,
    .ce-code,
    .alert,
    .callout,
    .info-box,
    .warning-box
):not(#comments):not(#comments *) {
    margin-bottom: 18px !important;
}

/* Resim doğrudan bir içerik kapsayıcısında ise sonraki bloğa yine nefes bırak. */
html body.alma-app .post-show-shell .ps-post-body :where(
    .ce-block__content > .image-tool,
    .ce-block__content > figure,
    .ce-block__content > table,
    .ce-block__content > .tc-wrap,
    .ce-block__content > .cdx-warning,
    .ce-block__content > .cdx-alert
):not(#comments):not(#comments *) {
    margin-bottom: 18px !important;
}

/* Son blokta gereksiz ekstra kuyruk boşluğu oluşturma. */
html body.alma-app .post-show-shell .ps-post-body > :where(
    figure,
    .image-tool,
    table,
    .tc-wrap,
    .ce-quote,
    .cdx-quote,
    .cdx-warning,
    .cdx-alert,
    .ce-code,
    .alert,
    .callout,
    .info-box,
    .warning-box
):last-child:not(#comments):not(#comments *) {
    margin-bottom: 0 !important;
}

/* Tablet */
@media (min-width: 641px) and (max-width: 1024px) {
    html body.alma-app .post-show-shell .ps-post-title:not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-title *:not(#comments):not(#comments *) {
        font-size: 21px !important;
        line-height: 1.31 !important;
    }
}

/* Mobil */
@media (max-width: 640px) {
    html body.alma-app .post-show-shell .ps-post-title:not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-title *:not(#comments):not(#comments *) {
        font-size: 20px !important;
        line-height: 1.32 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body:not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(
        p,
        div,
        span,
        font,
        a,
        li,
        td,
        th,
        blockquote,
        em,
        i,
        u,
        mark,
        small,
        .ce-paragraph,
        .cdx-block,
        .cdx-list__item
    ):not(#comments):not(#comments *) {
        font-size: 15px !important;
        line-height: 1.56 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]) *:not(#comments):not(#comments *) {
        font-size: 18px !important;
        line-height: 1.38 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]) *:not(#comments):not(#comments *) {
        font-size: 17px !important;
        line-height: 1.4 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]) *:not(#comments):not(#comments *) {
        font-size: 16px !important;
        line-height: 1.42 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body .ce-block:has(
        .image-tool,
        figure,
        table,
        .tc-wrap,
        .ce-quote,
        .cdx-quote,
        .cdx-warning,
        .cdx-alert,
        .ce-code,
        .alert,
        .callout,
        .info-box,
        .warning-box
    ):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body > :where(
        figure,
        .image-tool,
        p:has(> img),
        table,
        .tc-wrap,
        .ce-quote,
        .cdx-quote,
        .cdx-warning,
        .cdx-alert,
        .ce-code,
        .alert,
        .callout,
        .info-box,
        .warning-box
    ):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(
        .ce-block__content > .image-tool,
        .ce-block__content > figure,
        .ce-block__content > table,
        .ce-block__content > .tc-wrap,
        .ce-block__content > .cdx-warning,
        .ce-block__content > .cdx-alert
    ):not(#comments):not(#comments *) {
        margin-bottom: 16px !important;
    }
}
</style>
HTML;

        $html = preg_replace('/<\/body>/i', $style . "\n</body>", $html, 1) ?? ($html . $style);

        $response->setContent($html);
        $response->headers->set('X-Ografi-Post-Typography', 'v12');

        return $response;
    }

    private function normalizeSemanticSubheadings(string $html): string
    {
        // Ham RSS/HTML paragraflari.
        $html = preg_replace_callback(
            '/<p(?P<attrs>\s[^>]*)?>(?P<inner>.*?)<\/p>/isu',
            function (array $match): string {
                return $this->convertTextContainerToHeading($match, 'p');
            },
            $html
        ) ?? $html;

        // EditorJS'in tek satirlik paragraph bloklari genellikle div.ce-paragraph / div.cdx-block'tur.
        $html = preg_replace_callback(
            '/<div(?P<attrs>\s[^>]*)?>(?P<inner>(?:(?!<div\b|<\/div>).)*)<\/div>/isu',
            function (array $match): string {
                $attrs = (string) ($match['attrs'] ?? '');

                if (! preg_match('/\bclass\s*=\s*(["\'])[^"\']*\b(?:ce-paragraph|cdx-block)\b[^"\']*\1/iu', $attrs)) {
                    return $match[0];
                }

                return $this->convertTextContainerToHeading($match, 'div');
            },
            $html
        ) ?? $html;

        return $html;
    }

    private function convertTextContainerToHeading(array $match, string $tag): string
    {
        $attrs = (string) ($match['attrs'] ?? '');
        $inner = (string) ($match['inner'] ?? '');

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

        if (preg_match('/[.!?…]["\'”’)]?$/u', $text)) {
            return $match[0];
        }

        // Eski inline font-size/font-weight baslik stilini ezmesin.
        $attrs = preg_replace('/\sstyle\s*=\s*(["\']).*?\1/isu', '', $attrs) ?? $attrs;

        return '<h2' . $attrs . '>' . $inner . '</h2>';
    }
}

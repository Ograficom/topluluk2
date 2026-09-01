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

        $style = <<<'HTML'
<style data-ografi-post-show-typography-final>
/*
 * Post-show makale tipografisi.
 * 640-700px civari okuma kolonunda yaygin blog olcegi:
 * govde 17px, ana baslik 30px, H2 26px, H3 22px, H4 19px.
 */
html body.alma-app .post-show-shell .ps-post-title:not(#comments):not(#comments *) {
    font-size: 30px !important;
    line-height: 1.22 !important;
    font-weight: 700 !important;
    letter-spacing: -0.02em !important;
}

html body.alma-app .post-show-shell .ps-post-body:not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(
    p,
    div,
    li,
    td,
    th,
    blockquote,
    span,
    font,
    a,
    em,
    i,
    u,
    mark,
    .ce-paragraph,
    .cdx-block
):not(#comments):not(#comments *) {
    font-size: 17px !important;
    line-height: 1.68 !important;
    font-weight: 400 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(strong, b):not(#comments):not(#comments *),
html body.alma-app .post-show-shell .ps-post-body :where(
    p,
    div,
    li,
    td,
    th,
    blockquote,
    span,
    font,
    a,
    em,
    i,
    u,
    mark,
    .ce-paragraph,
    .cdx-block
) :where(strong, b):not(#comments):not(#comments *) {
    font-size: inherit !important;
    line-height: inherit !important;
    font-weight: 700 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *) {
    margin: 28px 0 12px !important;
    font-size: 26px !important;
    line-height: 1.28 !important;
    font-weight: 700 !important;
    letter-spacing: -0.015em !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *) {
    margin: 24px 0 10px !important;
    font-size: 22px !important;
    line-height: 1.32 !important;
    font-weight: 700 !important;
    letter-spacing: -0.01em !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *) {
    margin: 22px 0 9px !important;
    font-size: 19px !important;
    line-height: 1.36 !important;
    font-weight: 700 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h5, h6):not(#comments):not(#comments *) {
    margin: 20px 0 8px !important;
    font-size: 18px !important;
    line-height: 1.4 !important;
    font-weight: 600 !important;
}

/* RSS kaynaklarinda H2 yerine duz BUYUK HARFLI paragraf olarak gelen ara basliklar. */
html body.alma-app .post-show-shell .ps-post-body .ografi-post-subheading:not(#comments):not(#comments *) {
    display: block !important;
    margin: 24px 0 8px !important;
    font-size: 18px !important;
    line-height: 1.4 !important;
    font-weight: 700 !important;
    letter-spacing: 0 !important;
}

@media (max-width: 640px) {
    html body.alma-app .post-show-shell .ps-post-title:not(#comments):not(#comments *) {
        font-size: 25px !important;
        line-height: 1.26 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body:not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(
        p,
        div,
        li,
        td,
        th,
        blockquote,
        span,
        font,
        a,
        em,
        i,
        u,
        mark,
        .ce-paragraph,
        .cdx-block
    ):not(#comments):not(#comments *) {
        font-size: 16px !important;
        line-height: 1.65 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *) {
        font-size: 23px !important;
        line-height: 1.3 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *) {
        font-size: 20px !important;
        line-height: 1.34 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *) {
        font-size: 18px !important;
        line-height: 1.38 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body .ografi-post-subheading:not(#comments):not(#comments *) {
        font-size: 17px !important;
        line-height: 1.4 !important;
    }
}
</style>
<script data-ografi-post-show-typography-script>
(() => {
    const body = document.querySelector('.post-show-shell .ps-post-body');
    if (!body) return;

    const candidates = body.querySelectorAll('p, div.ce-paragraph, .cdx-block > p');

    candidates.forEach((element) => {
        if (element.closest('blockquote, li, td, th, figure, .ps-notice, .ps-stats, .ps-cta, .ps-faq, .ps-steps, .ps-proscons')) {
            return;
        }

        const text = (element.textContent || '').replace(/\s+/g, ' ').trim();
        if (text.length < 4 || text.length > 72) return;

        const letters = text.match(/[A-Za-zÇĞİÖŞÜçğıöşü]/g) || [];
        if (letters.length < 4) return;

        const upper = text.toLocaleUpperCase('tr-TR');
        const lower = text.toLocaleLowerCase('tr-TR');
        if (text === upper && text !== lower) {
            element.classList.add('ografi-post-subheading');
        }
    });
})();
</script>
HTML;

        $html = preg_replace('/<\/body>/i', $style . "\n</body>", $html, 1) ?? ($html . $style);

        $response->setContent($html);
        $response->headers->set('X-Ografi-Post-Typography', 'v4');

        return $response;
    }
}

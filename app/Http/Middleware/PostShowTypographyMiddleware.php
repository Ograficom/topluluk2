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
/* Post-show govde ve inline metinleri ayni okunabilir olcekte tut. */
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
    font-size: 17px !important;
    line-height: 1.68 !important;
    font-weight: 800 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *) {
    font-size: 28px !important;
    line-height: 1.3 !important;
    font-weight: 800 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *) {
    font-size: 24px !important;
    line-height: 1.34 !important;
    font-weight: 800 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *) {
    font-size: 21px !important;
    line-height: 1.38 !important;
    font-weight: 800 !important;
}

html body.alma-app .post-show-shell .ps-post-body :where(h5, h6):not(#comments):not(#comments *) {
    font-size: 19px !important;
    line-height: 1.42 !important;
    font-weight: 800 !important;
}

@media (max-width: 640px) {
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
        font-size: 16px !important;
        line-height: 1.65 !important;
        font-weight: 800 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *) {
        font-size: 25px !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *) {
        font-size: 22px !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *) {
        font-size: 20px !important;
    }
}
</style>
HTML;

        $html = preg_replace('/<\/body>/i', $style . "\n</body>", $html, 1) ?? ($html . $style);

        $response->setContent($html);
        $response->headers->set('X-Ografi-Post-Typography', 'v3');

        return $response;
    }
}

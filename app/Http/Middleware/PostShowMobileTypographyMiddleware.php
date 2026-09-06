<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostShowMobileTypographyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->routeIs('blog.post')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html)
            || $html === ''
            || ! str_contains($html, 'post-show-shell')
            || str_contains($html, 'data-ografi-post-show-mobile-typography')
        ) {
            return $response;
        }

        $style = <<<'HTML'
<style data-ografi-post-show-mobile-typography>
@media (max-width: 640px) {
    html body.alma-app .post-show-shell {
        -webkit-text-size-adjust: 100% !important;
        text-size-adjust: 100% !important;
    }

    html body.alma-app .post-show-shell .ps-post-title:not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-title *:not(#comments):not(#comments *) {
        font-family: Inter, Arial, Helvetica, sans-serif !important;
        font-size: 19px !important;
        line-height: 1.3 !important;
        font-weight: 700 !important;
        letter-spacing: -0.012em !important;
        overflow-wrap: anywhere !important;
    }

    html body.alma-app .post-show-shell .ps-post-body:not(#comments):not(#comments *) {
        font-family: Inter, Arial, Helvetica, sans-serif !important;
        font-size: 16px !important;
        line-height: 1.68 !important;
        font-weight: 400 !important;
        letter-spacing: -0.003em !important;
        text-rendering: optimizeLegibility !important;
        overflow-wrap: anywhere !important;
        word-break: normal !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(
        p,
        .ce-paragraph,
        .cdx-block
    ):not(#comments):not(#comments *) {
        font-family: Inter, Arial, Helvetica, sans-serif !important;
        font-size: 16px !important;
        line-height: 1.68 !important;
        font-weight: 400 !important;
        letter-spacing: -0.003em !important;
        overflow-wrap: anywhere !important;
        word-break: normal !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(li, .cdx-list__item):not(#comments):not(#comments *) {
        font-size: 16px !important;
        line-height: 1.62 !important;
        font-weight: 400 !important;
        overflow-wrap: anywhere !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(ul, ol):not(#comments):not(#comments *) {
        padding-left: 1.25rem !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(strong, b):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(strong, b) *:not(#comments):not(#comments *) {
        font-size: inherit !important;
        line-height: inherit !important;
        font-weight: 700 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]) *:not(#comments):not(#comments *) {
        font-size: 20px !important;
        line-height: 1.32 !important;
        font-weight: 700 !important;
        letter-spacing: -0.008em !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h2, .ce-header[data-level="2"]):not(#comments):not(#comments *) {
        margin: 22px 0 8px !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]) *:not(#comments):not(#comments *) {
        font-size: 18px !important;
        line-height: 1.36 !important;
        font-weight: 700 !important;
        letter-spacing: -0.006em !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h3, .ce-header[data-level="3"]):not(#comments):not(#comments *) {
        margin: 20px 0 7px !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h4, .ce-header[data-level="4"]) *:not(#comments):not(#comments *) {
        font-size: 17px !important;
        line-height: 1.4 !important;
        font-weight: 700 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(h5, h6):not(#comments):not(#comments *),
    html body.alma-app .post-show-shell .ps-post-body :where(h5, h6) *:not(#comments):not(#comments *) {
        font-size: 16px !important;
        line-height: 1.42 !important;
        font-weight: 700 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(blockquote, .ce-quote, .cdx-quote):not(#comments):not(#comments *) {
        font-size: 15.5px !important;
        line-height: 1.62 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(table, .tc-wrap):not(#comments):not(#comments *) {
        max-width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(td, th):not(#comments):not(#comments *) {
        font-size: 14px !important;
        line-height: 1.45 !important;
        overflow-wrap: normal !important;
        word-break: normal !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(pre, code, .ce-code):not(#comments):not(#comments *) {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace !important;
        font-size: 13.5px !important;
        line-height: 1.55 !important;
    }

    html body.alma-app .post-show-shell .ps-post-body pre:not(#comments):not(#comments *) {
        max-width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    html body.alma-app .post-show-shell .ps-post-body :where(figcaption, small):not(#comments):not(#comments *) {
        font-size: 13px !important;
        line-height: 1.45 !important;
    }
}
</style>
HTML;

        $html = preg_replace('/<\/body>/i', $style . "\n</body>", $html, 1) ?? ($html . $style);
        $response->setContent($html);
        $response->headers->remove('Content-Length');
        $response->headers->set('X-Ografi-Post-Mobile-Typography', 'v1');

        return $response;
    }
}

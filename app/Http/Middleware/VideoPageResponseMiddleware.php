<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoPageResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->is('video') || $request->method() !== 'GET') {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (! str_contains($contentType, 'text/html') || ! method_exists($response, 'getContent')) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return $response;
        }

        // Safari/PWA tarafında eski video-tv.js kopyasının tutulmasını engelle.
        $content = str_replace('video-tv.js?v=2', 'video-tv.js?v=410', $content);

        if (! str_contains($content, 'id="video-tv-mobile-header-critical"')) {
            $style = <<<'HTML'
<style id="video-tv-mobile-header-critical">
@media (max-width: 767px) {
    html body header.site-header[data-site-header].site-header {
        --site-header-height: 118px !important;
        position: fixed !important;
        inset: 0 0 auto 0 !important;
        width: 100% !important;
        height: 118px !important;
        min-height: 118px !important;
        border: 0 !important;
        border-bottom: 0 !important;
        background: #fff !important;
        background-color: #fff !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    html body header.site-header[data-site-header].site-header > .site-header-shell {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        max-width: none !important;
        height: 118px !important;
        min-height: 118px !important;
        margin: 0 !important;
        padding: 18px 18px 0 !important;
        gap: 18px !important;
        background: transparent !important;
        box-sizing: border-box !important;
    }

    html body header.site-header[data-site-header].site-header > .site-header-shell > div:first-child {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        width: 64px !important;
        min-width: 64px !important;
        max-width: 64px !important;
        height: 64px !important;
        min-height: 64px !important;
        max-height: 64px !important;
        margin: 0 !important;
        padding: 0 !important;
        gap: 0 !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-logo {
        display: none !important;
    }

    html body header.site-header[data-site-header].site-header button[data-mobile-sidebar-toggle] {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 64px !important;
        min-width: 64px !important;
        max-width: 64px !important;
        height: 64px !important;
        min-height: 64px !important;
        max-height: 64px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: #fff !important;
        background-color: #fff !important;
        color: #111 !important;
        box-shadow: 0 15px 42px rgba(15, 23, 42, .08) !important;
        transform: none !important;
    }

    html body header.site-header[data-site-header].site-header button[data-mobile-sidebar-toggle] > :is(svg, iconify-icon) {
        display: block !important;
        width: 30px !important;
        min-width: 30px !important;
        height: 30px !important;
        min-height: 30px !important;
        font-size: 30px !important;
        color: currentColor !important;
        margin: 0 !important;
        transform: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 146px !important;
        min-width: 146px !important;
        max-width: 146px !important;
        height: 64px !important;
        min-height: 64px !important;
        max-height: 64px !important;
        margin: 0 0 0 auto !important;
        padding: 0 8px !important;
        gap: 2px !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: #fff !important;
        background-color: #fff !important;
        box-shadow: 0 15px 42px rgba(15, 23, 42, .08) !important;
        white-space: nowrap !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions > :not(.site-header-write-btn):not([data-user-menu]) {
        display: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions > .site-header-write-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 60px !important;
        min-width: 60px !important;
        max-width: 60px !important;
        height: 54px !important;
        min-height: 54px !important;
        max-height: 54px !important;
        margin: 0 !important;
        padding: 0 !important;
        gap: 0 !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: transparent !important;
        background-color: transparent !important;
        color: #050505 !important;
        box-shadow: none !important;
        font-size: 0 !important;
        transform: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions > .site-header-write-btn > span {
        display: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions > .site-header-write-btn > :is(svg, iconify-icon) {
        display: block !important;
        width: 31px !important;
        min-width: 31px !important;
        height: 31px !important;
        min-height: 31px !important;
        font-size: 31px !important;
        color: #050505 !important;
        margin: 0 !important;
        transform: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions > [data-user-menu] {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 60px !important;
        min-width: 60px !important;
        max-width: 60px !important;
        height: 54px !important;
        min-height: 54px !important;
        max-height: 54px !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    html body header.site-header[data-site-header].site-header button[data-user-menu-btn] {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 60px !important;
        min-width: 60px !important;
        max-width: 60px !important;
        height: 54px !important;
        min-height: 54px !important;
        max-height: 54px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: transparent !important;
        background-color: transparent !important;
        color: #111 !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    html body header.site-header[data-site-header].site-header button[data-user-menu-btn] > :is(img, .site-avatar-fallback, svg, iconify-icon) {
        display: none !important;
    }

    html body header.site-header[data-site-header].site-header button[data-user-menu-btn]::before {
        content: '' !important;
        display: block !important;
        width: 6px !important;
        height: 6px !important;
        border-radius: 9999px !important;
        background: #111 !important;
        box-shadow: 11px 0 0 #111, 22px 0 0 #111 !important;
        transform: translateX(-11px) !important;
    }

    html body header.site-header[data-site-header].site-header :is(
        button[data-mobile-sidebar-toggle],
        .site-header-write-btn,
        button[data-user-menu-btn]
    ):active {
        background: #f3f4f6 !important;
        background-color: #f3f4f6 !important;
    }

    html body [data-video-tv-root].video-tv-page {
        padding-top: 80px !important;
    }
}
</style>
HTML;

            $content = str_replace('</head>', $style . "\n</head>", $content);
        }

        $response->setContent($content);

        return $response;
    }
}

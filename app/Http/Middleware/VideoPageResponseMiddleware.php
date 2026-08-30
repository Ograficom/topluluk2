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

        // Mobil Safari/PWA/Chrome eski player ve header JS kopyasını tutmasın.
        $content = preg_replace(
            '/video-tv\.js\?v=\d+/i',
            'video-tv.js?v=412',
            $content,
        ) ?? $content;

        if (! str_contains($content, 'id="video-reference-mobile-header-style"')) {
            $style = <<<'HTML'
<style id="video-reference-mobile-header-style">
@media (max-width: 767px) {
    /*
     * /video kendi mobil ust barini kullanir. Genel site headerinin gorunen
     * kontrolleri saklanir; kullanici menusu paneli ise mevcut JS ile calismaya
     * devam etsin diye DOM'da tutulur.
     */
    html body header.site-header[data-site-header].site-header {
        position: fixed !important;
        inset: 0 0 auto 0 !important;
        z-index: 10020 !important;
        width: 100% !important;
        height: 0 !important;
        min-height: 0 !important;
        border: 0 !important;
        border-bottom: 0 !important;
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
        pointer-events: none !important;
    }

    html body header.site-header[data-site-header].site-header > .site-header-shell {
        position: static !important;
        display: block !important;
        width: 100% !important;
        max-width: none !important;
        height: 0 !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        overflow: visible !important;
        pointer-events: none !important;
    }

    html body header.site-header[data-site-header].site-header > .site-header-shell > div:first-child {
        display: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions {
        position: static !important;
        display: block !important;
        width: 0 !important;
        min-width: 0 !important;
        max-width: 0 !important;
        height: 0 !important;
        min-height: 0 !important;
        max-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
        overflow: visible !important;
        pointer-events: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions > :not([data-user-menu]) {
        display: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions > [data-user-menu] {
        position: fixed !important;
        top: calc(24px + env(safe-area-inset-top, 0px)) !important;
        right: 20px !important;
        z-index: 10030 !important;
        display: block !important;
        width: 72px !important;
        min-width: 72px !important;
        max-width: 72px !important;
        height: 72px !important;
        min-height: 72px !important;
        max-height: 72px !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        pointer-events: none !important;
    }

    html body header.site-header[data-site-header].site-header button[data-user-menu-btn] {
        position: absolute !important;
        inset: 0 !important;
        display: block !important;
        width: 72px !important;
        min-width: 72px !important;
        max-width: 72px !important;
        height: 72px !important;
        min-height: 72px !important;
        max-height: 72px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-menu-panel[data-user-menu-panel] {
        top: 82px !important;
        right: 0 !important;
        pointer-events: auto !important;
    }

    #video-reference-mobile-header {
        position: fixed !important;
        top: calc(24px + env(safe-area-inset-top, 0px)) !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 10025 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        height: 72px !important;
        margin: 0 !important;
        padding: 0 20px !important;
        box-sizing: border-box !important;
        pointer-events: none !important;
    }

    #video-reference-mobile-header .video-reference-menu,
    #video-reference-mobile-header .video-reference-compose,
    #video-reference-mobile-header .video-reference-more {
        -webkit-appearance: none !important;
        appearance: none !important;
        outline: none !important;
        -webkit-tap-highlight-color: transparent !important;
        touch-action: manipulation !important;
    }

    #video-reference-mobile-header .video-reference-menu {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 72px !important;
        min-width: 72px !important;
        height: 72px !important;
        min-height: 72px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        color: #080808 !important;
        box-shadow: 0 18px 52px rgba(15, 23, 42, .10) !important;
        pointer-events: auto !important;
        cursor: pointer !important;
    }

    #video-reference-mobile-header .video-reference-menu svg {
        display: block !important;
        width: 34px !important;
        height: 34px !important;
        margin: 0 !important;
        color: currentColor !important;
    }

    #video-reference-mobile-header .video-reference-actions {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        align-items: center !important;
        width: 166px !important;
        min-width: 166px !important;
        height: 72px !important;
        margin: 0 !important;
        padding: 5px 7px !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        box-shadow: 0 18px 52px rgba(15, 23, 42, .10) !important;
        box-sizing: border-box !important;
        pointer-events: auto !important;
    }

    #video-reference-mobile-header .video-reference-compose,
    #video-reference-mobile-header .video-reference-more {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 62px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: transparent !important;
        background-color: transparent !important;
        color: #080808 !important;
        box-shadow: none !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    #video-reference-mobile-header .video-reference-compose svg {
        display: block !important;
        width: 35px !important;
        height: 35px !important;
        margin: 0 !important;
        color: currentColor !important;
    }

    #video-reference-mobile-header .video-reference-more-dots {
        position: relative !important;
        display: block !important;
        width: 34px !important;
        height: 8px !important;
    }

    #video-reference-mobile-header .video-reference-more-dots::before,
    #video-reference-mobile-header .video-reference-more-dots::after,
    #video-reference-mobile-header .video-reference-more-dots span {
        content: '' !important;
        position: absolute !important;
        top: 1px !important;
        width: 7px !important;
        height: 7px !important;
        border-radius: 9999px !important;
        background: #080808 !important;
    }

    #video-reference-mobile-header .video-reference-more-dots::before {
        left: 0 !important;
    }

    #video-reference-mobile-header .video-reference-more-dots span {
        left: 13.5px !important;
    }

    #video-reference-mobile-header .video-reference-more-dots::after {
        right: 0 !important;
    }

    #video-reference-mobile-header :is(.video-reference-menu, .video-reference-compose, .video-reference-more):active {
        background: #f3f4f6 !important;
        background-color: #f3f4f6 !important;
    }

    html body [data-video-tv-root].video-tv-page {
        padding-top: 150px !important;
    }

    @media (max-width: 390px) {
        #video-reference-mobile-header {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        #video-reference-mobile-header .video-reference-menu {
            width: 66px !important;
            min-width: 66px !important;
            height: 66px !important;
            min-height: 66px !important;
        }

        #video-reference-mobile-header .video-reference-actions {
            width: 154px !important;
            min-width: 154px !important;
            height: 66px !important;
        }

        #video-reference-mobile-header .video-reference-compose,
        #video-reference-mobile-header .video-reference-more {
            height: 56px !important;
        }
    }
}

@media (min-width: 768px) {
    #video-reference-mobile-header {
        display: none !important;
    }
}
</style>
HTML;

            $content = str_replace('</head>', $style . "\n</head>", $content);
        }

        if (! str_contains($content, 'id="video-reference-mobile-header"')) {
            $header = <<<'HTML'
<div id="video-reference-mobile-header" aria-label="Video mobil gezinme">
    <button type="button" class="video-reference-menu" data-video-reference-menu aria-label="Menüyü aç">
        <svg viewBox="0 0 36 36" fill="none" aria-hidden="true">
            <path d="M7 12H29" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"></path>
            <path d="M7 24H22" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"></path>
        </svg>
    </button>

    <div class="video-reference-actions">
        <a href="/blog/create" class="video-reference-compose" aria-label="Yeni gönderi oluştur">
            <svg viewBox="0 0 36 36" fill="none" aria-hidden="true">
                <path d="M20.8 7.2H9.8A3.8 3.8 0 0 0 6 11v15.2A3.8 3.8 0 0 0 9.8 30h15.2a3.8 3.8 0 0 0 3.8-3.8V15.4" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M16 20 28.1 7.9a3.25 3.25 0 0 1 4.6 4.6L20.6 24.6 14.4 26.2 16 20Z" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </a>

        <button type="button" class="video-reference-more" data-video-reference-more aria-label="Diğer seçenekler">
            <span class="video-reference-more-dots" aria-hidden="true"><span></span></span>
        </button>
    </div>
</div>
HTML;

            $content = preg_replace('/(<body\b[^>]*>)/i', '$1' . "\n" . $header, $content, 1) ?? $content;
        }

        if (! str_contains($content, 'id="video-reference-mobile-header-script"')) {
            $script = <<<'HTML'
<script id="video-reference-mobile-header-script">
(() => {
    const menu = document.querySelector('[data-video-reference-menu]');
    const more = document.querySelector('[data-video-reference-more]');

    menu?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const original = document.querySelector('header.site-header[data-site-header] [data-mobile-sidebar-toggle]');
        original?.click();
    });

    more?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const original = document.querySelector('header.site-header[data-site-header] [data-user-menu-btn]');
        if (original) {
            original.click();
            return;
        }
        window.location.href = '/login';
    });
})();
</script>
HTML;

            $content = str_replace('</body>', $script . "\n</body>", $content);
        }

        $response->setContent($content);

        return $response;
    }
}

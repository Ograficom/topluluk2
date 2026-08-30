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

        // Mobil Safari/PWA/Chrome eski player kopyasini tutmasin.
        $content = preg_replace(
            '/video-tv\.js\?v=\d+/i',
            'video-tv.js?v=419',
            $content,
        ) ?? $content;

        $currentUser = $request->user();
        $currentLocale = app()->getLocale();
        $languageUrl = e(route('locale.switch', [
            'locale' => $currentLocale === 'en' ? 'tr' : 'en',
        ]));
        $loginUrl = e(route('login'));
        $homeUrl = e(url('/'));
        $composeUrl = e(url('/blog/create'));

        $avatarUrl = $currentUser?->profile_photo_url
            ?? $currentUser?->avatar
            ?? $currentUser?->photo
            ?? null;

        if ($avatarUrl && str_contains((string) $avatarUrl, 'ui-avatars.com')) {
            $avatarUrl = null;
        }

        $userName = e((string) ($currentUser?->name ?? 'Kullanıcı'));
        $initial = e(strtoupper(mb_substr((string) ($currentUser?->name ?? 'U'), 0, 1)));
        $languageLabel = $currentLocale === 'en' ? 'English' : 'Türkçe';

        $avatarMarkup = $avatarUrl
            ? '<img class="video-reference-avatar-image" src="'.e((string) $avatarUrl).'" alt="'.$userName.'">'
            : '<span class="video-reference-avatar-fallback" aria-hidden="true">'.$initial.'</span>';

        $rightControl = $currentUser
            ? <<<HTML
<button type="button" class="video-reference-account" data-video-reference-account aria-label="Hesap menüsü">
    {$avatarMarkup}
</button>
HTML
            : <<<'HTML'
<button type="button" class="video-reference-more" data-video-reference-more aria-label="Diğer seçenekler" aria-expanded="false">
    <span class="video-reference-more-dots" aria-hidden="true"><span></span></span>
</button>
HTML;

        $guestMenu = $currentUser
            ? ''
            : <<<HTML
<div class="video-mobile-menu-panel" data-video-mobile-menu-panel hidden>
    <button
        type="button"
        class="video-mobile-menu-row"
        data-user-menu-theme-toggle
        data-label-dark="Karanlık Mod"
        data-label-light="Aydınlık Mod"
        aria-pressed="false"
    >
        <span class="video-mobile-menu-icon">
            <iconify-icon icon="lucide:moon" data-user-menu-theme-icon></iconify-icon>
        </span>
        <span>Karanlık Mod</span>
        <span class="video-mobile-theme-switch" aria-hidden="true">
            <span class="video-mobile-theme-knob"></span>
        </span>
    </button>

    <a href="{$languageUrl}" class="video-mobile-menu-row">
        <span class="video-mobile-menu-icon"><iconify-icon icon="lucide:languages"></iconify-icon></span>
        <span>Dil: {$languageLabel}</span>
    </a>

    <a href="{$loginUrl}" class="video-mobile-menu-row video-mobile-menu-login">
        <span class="video-mobile-menu-icon"><iconify-icon icon="lucide:log-in"></iconify-icon></span>
        <span>Giriş yap</span>
    </a>
</div>
HTML;

        if (! str_contains($content, 'id="video-reference-mobile-header-style"')) {
            $style = <<<'HTML'
<style id="video-reference-mobile-header-style">
@media (max-width: 767px) {
    html body {
        padding-top: 0 !important;
    }

    /* Genel header gizli kalir; mevcut sidebar ve hesap paneli DOM'da kullanilir. */
    html body header.site-header[data-site-header].site-header {
        position: fixed !important;
        inset: 0 0 auto 0 !important;
        z-index: 10020 !important;
        width: 100% !important;
        height: 0 !important;
        min-height: 0 !important;
        border: 0 !important;
        background: transparent !important;
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
        overflow: visible !important;
        background: transparent !important;
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
        overflow: visible !important;
        background: transparent !important;
        pointer-events: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-header-actions > :not([data-user-menu]) {
        display: none !important;
    }

    /* Giris yapildiginda custom avatar mevcut hesap panelini tetikler. */
    html body header.site-header[data-site-header].site-header .site-header-actions > [data-user-menu] {
        position: fixed !important;
        top: calc(10px + env(safe-area-inset-top, 0px)) !important;
        right: 12px !important;
        z-index: 10030 !important;
        display: block !important;
        width: 36px !important;
        height: 36px !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        pointer-events: none !important;
    }

    html body header.site-header[data-site-header].site-header button[data-user-menu-btn] {
        position: absolute !important;
        inset: 0 !important;
        display: block !important;
        width: 36px !important;
        height: 36px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    html body header.site-header[data-site-header].site-header .site-menu-panel[data-user-menu-panel] {
        top: 46px !important;
        right: 0 !important;
        z-index: 10080 !important;
        width: 292px !important;
        max-width: calc(100vw - 24px) !important;
        pointer-events: auto !important;
    }

    #video-reference-mobile-header {
        position: fixed !important;
        top: calc(10px + env(safe-area-inset-top, 0px)) !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 10025 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        height: 38px !important;
        margin: 0 !important;
        padding: 0 12px !important;
        box-sizing: border-box !important;
        pointer-events: none !important;
    }

    #video-reference-mobile-header * {
        box-sizing: border-box !important;
    }

    #video-reference-mobile-header .video-mobile-left {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        min-width: 0 !important;
        pointer-events: auto !important;
    }

    #video-reference-mobile-header .video-mobile-sidebar-button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 38px !important;
        min-width: 38px !important;
        height: 38px !important;
        min-height: 38px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: #ffffff !important;
        color: #090909 !important;
        box-shadow: 0 7px 20px rgba(15, 23, 42, .055) !important;
        cursor: pointer !important;
        -webkit-appearance: none !important;
        appearance: none !important;
        outline: none !important;
        -webkit-tap-highlight-color: transparent !important;
    }

    #video-reference-mobile-header .video-mobile-sidebar-button svg {
        display: block !important;
        width: 19px !important;
        height: 19px !important;
        margin: 0 !important;
    }

    #video-reference-mobile-header .video-mobile-brand {
        display: inline-flex !important;
        align-items: center !important;
        width: 100px !important;
        min-width: 38px !important;
        height: 38px !important;
        margin: 0 !important;
        padding: 0 8px 0 6px !important;
        gap: 6px !important;
        overflow: hidden !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: #ffffff !important;
        color: #111827 !important;
        text-decoration: none !important;
        box-shadow: 0 7px 20px rgba(15, 23, 42, .055) !important;
        transition: width .28s ease, padding .28s ease, gap .28s ease !important;
        white-space: nowrap !important;
    }

    #video-reference-mobile-header .video-mobile-brand.is-collapsed {
        width: 38px !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
        gap: 0 !important;
    }

    #video-reference-mobile-header .video-mobile-brand-logo {
        display: block !important;
        width: 26px !important;
        min-width: 26px !important;
        height: 26px !important;
        object-fit: contain !important;
        flex: 0 0 26px !important;
    }

    #video-reference-mobile-header .video-mobile-brand-word {
        display: block !important;
        max-width: 56px !important;
        overflow: hidden !important;
        opacity: 1 !important;
        color: #111827 !important;
        font-size: 14px !important;
        line-height: 1 !important;
        font-weight: 600 !important;
        letter-spacing: -0.02em !important;
        transition: max-width .28s ease, opacity .20s ease !important;
    }

    #video-reference-mobile-header .video-mobile-brand.is-collapsed .video-mobile-brand-word {
        max-width: 0 !important;
        opacity: 0 !important;
    }

    #video-reference-mobile-header .video-reference-actions {
        position: relative !important;
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        align-items: center !important;
        width: 90px !important;
        min-width: 90px !important;
        height: 38px !important;
        margin: 0 !important;
        padding: 2px 4px !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: #ffffff !important;
        box-shadow: 0 7px 20px rgba(15, 23, 42, .055) !important;
        pointer-events: auto !important;
    }

    #video-reference-mobile-header .video-reference-compose,
    #video-reference-mobile-header .video-reference-more,
    #video-reference-mobile-header .video-reference-account {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 34px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 9999px !important;
        background: transparent !important;
        color: #090909 !important;
        box-shadow: none !important;
        text-decoration: none !important;
        cursor: pointer !important;
        -webkit-appearance: none !important;
        appearance: none !important;
        outline: none !important;
        -webkit-tap-highlight-color: transparent !important;
    }

    #video-reference-mobile-header .video-reference-compose svg {
        display: block !important;
        width: 20px !important;
        height: 20px !important;
        margin: 0 !important;
    }

    #video-reference-mobile-header .video-reference-more-dots {
        position: relative !important;
        display: block !important;
        width: 21px !important;
        height: 5px !important;
    }

    #video-reference-mobile-header .video-reference-more-dots::before,
    #video-reference-mobile-header .video-reference-more-dots::after,
    #video-reference-mobile-header .video-reference-more-dots span {
        content: '' !important;
        position: absolute !important;
        top: .5px !important;
        width: 4px !important;
        height: 4px !important;
        border-radius: 9999px !important;
        background: #090909 !important;
    }

    #video-reference-mobile-header .video-reference-more-dots::before { left: 0 !important; }
    #video-reference-mobile-header .video-reference-more-dots span { left: 8.5px !important; }
    #video-reference-mobile-header .video-reference-more-dots::after { right: 0 !important; }

    #video-reference-mobile-header .video-reference-avatar-image,
    #video-reference-mobile-header .video-reference-avatar-fallback {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 29px !important;
        min-width: 29px !important;
        height: 29px !important;
        min-height: 29px !important;
        border-radius: 9999px !important;
        object-fit: cover !important;
    }

    #video-reference-mobile-header .video-reference-avatar-fallback {
        background: #2563eb !important;
        color: #ffffff !important;
        font-size: 12px !important;
        line-height: 29px !important;
        font-weight: 600 !important;
    }

    #video-reference-mobile-header .video-reference-more[aria-expanded="true"],
    #video-reference-mobile-header :is(.video-mobile-sidebar-button, .video-reference-compose, .video-reference-more, .video-reference-account):active {
        background: #f1f5f9 !important;
    }

    /* Misafir ayar kutusu artik sagdaki uc noktanin altinda, sag kenara hizali. */
    #video-reference-mobile-header .video-mobile-menu-panel {
        position: absolute !important;
        top: 46px !important;
        right: 0 !important;
        left: auto !important;
        z-index: 10070 !important;
        width: 218px !important;
        max-width: calc(100vw - 24px) !important;
        margin: 0 !important;
        padding: 7px !important;
        transform: none !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 15px !important;
        background: #ffffff !important;
        color: #0f172a !important;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .09) !important;
        pointer-events: auto !important;
    }

    #video-reference-mobile-header .video-mobile-menu-panel[hidden] {
        display: none !important;
    }

    #video-reference-mobile-header .video-mobile-menu-row {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        min-height: 40px !important;
        margin: 0 !important;
        padding: 7px 9px !important;
        gap: 10px !important;
        border: 0 !important;
        border-radius: 10px !important;
        background: transparent !important;
        color: #1e293b !important;
        font: inherit !important;
        font-size: 13px !important;
        line-height: 1.25 !important;
        font-weight: 400 !important;
        text-align: left !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    #video-reference-mobile-header .video-mobile-menu-row:hover,
    #video-reference-mobile-header .video-mobile-menu-row:focus-visible,
    #video-reference-mobile-header .video-mobile-menu-row:active {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        outline: none !important;
    }

    #video-reference-mobile-header .video-mobile-menu-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 19px !important;
        min-width: 19px !important;
        height: 19px !important;
        color: #475569 !important;
    }

    #video-reference-mobile-header .video-mobile-menu-icon iconify-icon {
        display: block !important;
        width: 18px !important;
        height: 18px !important;
        font-size: 18px !important;
        color: currentColor !important;
    }

    #video-reference-mobile-header .video-mobile-theme-switch {
        position: relative !important;
        width: 38px !important;
        min-width: 38px !important;
        height: 22px !important;
        margin-left: auto !important;
        border-radius: 9999px !important;
        background: #cbd5e1 !important;
    }

    #video-reference-mobile-header .video-mobile-theme-knob {
        position: absolute !important;
        top: 3px !important;
        left: 3px !important;
        width: 16px !important;
        height: 16px !important;
        border-radius: 9999px !important;
        background: #ffffff !important;
        transition: left .16s ease !important;
    }

    html.dark #video-reference-mobile-header .video-mobile-theme-switch {
        background: #2563eb !important;
    }

    html.dark #video-reference-mobile-header .video-mobile-theme-knob {
        left: 19px !important;
    }

    #video-reference-mobile-header .video-mobile-menu-login {
        margin-top: 3px !important;
        border-top: 1px solid #eef2f7 !important;
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
        color: #2563eb !important;
    }

    html body [data-video-tv-root].video-tv-page {
        padding-top: 66px !important;
    }

    html.dark #video-reference-mobile-header .video-mobile-sidebar-button,
    html.dark #video-reference-mobile-header .video-mobile-brand,
    html.dark #video-reference-mobile-header .video-reference-actions,
    html.dark #video-reference-mobile-header .video-mobile-menu-panel {
        background: #111827 !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    html.dark #video-reference-mobile-header .video-mobile-brand-word,
    html.dark #video-reference-mobile-header :is(.video-reference-compose, .video-reference-more, .video-reference-account) {
        color: #f8fafc !important;
    }

    html.dark #video-reference-mobile-header .video-reference-more-dots::before,
    html.dark #video-reference-mobile-header .video-reference-more-dots::after,
    html.dark #video-reference-mobile-header .video-reference-more-dots span {
        background: #f8fafc !important;
    }

    html.dark #video-reference-mobile-header .video-mobile-menu-row {
        color: #e2e8f0 !important;
    }

    html.dark #video-reference-mobile-header .video-mobile-menu-row:hover,
    html.dark #video-reference-mobile-header .video-mobile-menu-row:focus-visible,
    html.dark #video-reference-mobile-header .video-mobile-menu-row:active {
        background: #1e293b !important;
        color: #ffffff !important;
    }

    html.dark #video-reference-mobile-header .video-mobile-menu-icon {
        color: #cbd5e1 !important;
    }

    html.dark #video-reference-mobile-header .video-mobile-menu-login {
        border-top-color: #334155 !important;
        color: #93c5fd !important;
    }

    @media (max-width: 360px) {
        #video-reference-mobile-header .video-mobile-brand {
            width: 86px !important;
        }

        #video-reference-mobile-header .video-mobile-brand.is-collapsed {
            width: 38px !important;
        }

        #video-reference-mobile-header .video-mobile-brand-word {
            font-size: 13px !important;
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

            $content = str_replace('</head>', $style."\n</head>", $content);
        }

        if (! str_contains($content, 'id="video-reference-mobile-header"')) {
            $header = <<<HTML
<div id="video-reference-mobile-header" aria-label="Video mobil gezinme">
    <div class="video-mobile-left">
        <button type="button" class="video-mobile-sidebar-button" data-video-mobile-sidebar aria-label="Mobil yan menüyü aç">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 8.5H19" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"></path>
                <path d="M5 15.5H15" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"></path>
            </svg>
        </button>

        <a href="{$homeUrl}" class="video-mobile-brand" data-video-mobile-brand aria-label="Ografi ana sayfa">
            <img class="video-mobile-brand-logo" src="https://ografi.com/images/ografi-logo.png?v=20260714a" alt="Ografi">
            <span class="video-mobile-brand-word">Ografi</span>
        </a>
    </div>

    <div class="video-reference-actions">
        <a href="{$composeUrl}" class="video-reference-compose" aria-label="Yeni gönderi oluştur">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M13.8 5H6.7A2.7 2.7 0 0 0 4 7.7v9.6A2.7 2.7 0 0 0 6.7 20h9.6a2.7 2.7 0 0 0 2.7-2.7v-6.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="m11 13.4 7.1-7.1a2.25 2.25 0 0 1 3.2 3.2l-7.1 7.1-4.1 1.1 1-4.3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </a>

        {$rightControl}
        {$guestMenu}
    </div>
</div>
HTML;

            $content = preg_replace('/(<body\b[^>]*>)/i', '$1'."\n".$header, $content, 1) ?? $content;
        }

        if (! str_contains($content, 'id="video-reference-mobile-header-script"')) {
            $script = <<<'HTML'
<script id="video-reference-mobile-header-script">
(() => {
    const header = document.getElementById('video-reference-mobile-header');
    if (!header) return;

    const sidebar = header.querySelector('[data-video-mobile-sidebar]');
    const brand = header.querySelector('[data-video-mobile-brand]');
    const more = header.querySelector('[data-video-reference-more]');
    const account = header.querySelector('[data-video-reference-account]');
    const menuPanel = header.querySelector('[data-video-mobile-menu-panel]');

    const closeMenu = () => {
        if (!menuPanel) return;
        menuPanel.hidden = true;
        more?.setAttribute('aria-expanded', 'false');
    };

    const toggleMenu = () => {
        if (!menuPanel || !more) return;
        const opening = menuPanel.hidden;
        menuPanel.hidden = !opening;
        more.setAttribute('aria-expanded', opening ? 'true' : 'false');
    };

    window.setTimeout(() => {
        brand?.classList.add('is-collapsed');
    }, 5000);

    // Soldaki iki cizgili buton mevcut mobil yan menuyu acar.
    sidebar?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        closeMenu();
        document.querySelector('header.site-header[data-site-header] [data-mobile-sidebar-toggle]')?.click();
    });

    // Misafirde sagdaki uc nokta kompakt ayar kutusunu kendi altinda acar.
    more?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        toggleMenu();
    });

    // Giris yapildiginda uc nokta yerine avatar vardir; mevcut hesap panelini acar.
    account?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        closeMenu();
        document.querySelector('header.site-header[data-site-header] [data-user-menu-btn]')?.click();
    });

    menuPanel?.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    document.addEventListener('click', (event) => {
        if (!header.contains(event.target)) closeMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMenu();
    });
})();
</script>
HTML;

            $content = str_replace('</body>', $script."\n</body>", $content);
        }

        $response->setContent($content);

        return $response;
    }
}

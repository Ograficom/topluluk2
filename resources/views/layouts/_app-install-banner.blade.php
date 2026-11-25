<style>
    .ografi-install-banner[hidden], .ografi-install-banner [hidden] { display: none !important; }
    .ografi-install-banner { display: none; }
    @media screen and (max-width: 920px) and (display-mode: browser) {
        .ografi-install-banner:not([hidden]) {
            display: block;
            padding: 8px 12px 10px;
        }
    }
    .ografi-install-banner__card {
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 480px;
        margin-inline: auto;
        padding: 10px;
        border-radius: 16px;
        background: var(--card-bg, #fff);
        color: var(--text-color, #212529);
        box-shadow: 0 5px 18px rgb(32 56 96 / 12%);
        border: 1px solid var(--border-color, #e7ebef);
    }
    .ografi-install-banner__close {
        display: grid;
        place-items: center;
        flex: 0 0 24px;
        width: 24px;
        height: 32px;
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--gray-500, #64748b);
        cursor: pointer;
    }
    .ografi-install-banner__logo {
        display: block;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 12px;
        object-fit: contain;
        background: var(--gray-200, #e7ebef);
    }
    .ografi-install-banner__copy { flex: 1; min-width: 0; }
    .ografi-install-banner__title {
        display: block;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
        color: var(--text-color, #212529);
        overflow-wrap: anywhere;
    }
    .ografi-install-banner__subtitle {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        line-height: 1.35;
        color: var(--gray-500, #64748b);
    }
    .ografi-install-banner__action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: none;
        min-height: 32px;
        padding: 0 14px;
        border: 0;
        border-radius: 999px;
        background: var(--color-primary, #0d6efd);
        color: #fff !important;
        text-decoration: none !important;
        line-height: 1;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: transform .18s ease, opacity .18s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .ografi-install-banner__action:active { transform: scale(.94); opacity: .86; }
    .ografi-install-banner__close:focus-visible,
    .ografi-install-banner__action:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
    .ografi-install-banner__help {
        max-width: 480px;
        margin: 6px auto 0;
        padding: 10px 12px;
        border-radius: 12px;
        background: var(--card-bg, #fff);
        color: var(--text-color, #212529);
        font-size: 12px;
        line-height: 1.5;
    }
    @media (max-width: 340px) {
        .ografi-install-banner__card { gap: 6px; padding: 8px; }
        .ografi-install-banner__action { padding-inline: 10px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .ografi-install-banner__action { transition: none; }
        .ografi-install-banner__action:active { transform: none; }
    }
</style>
<section class="ografi-install-banner" data-ografi-install-banner hidden aria-label="OgraFi uygulamasını yükle">
    <div class="ografi-install-banner__card">
        <button type="button" class="ografi-install-banner__close" data-ografi-install-close aria-label="Uygulama önerisini kapat">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
        <img class="ografi-install-banner__logo" src="{{ \App\Http\Controllers\PwaIconController::manifestIcons($pwaSettings)[0]['src'] }}" alt="" width="44" height="44" onerror="this.onerror=null;this.src='{{ asset('assets/img/meta/pwa-icon-192.png') }}';">
        <div class="ografi-install-banner__copy">
            <strong class="ografi-install-banner__title">OgraFi</strong>
            <span class="ografi-install-banner__subtitle">Gerçekleşmiş Hayaller</span>
        </div>
        <a class="ografi-install-banner__action" data-ografi-install-android href="https://play.google.com/store/apps/details?id=com.ografi.twa" target="_blank" rel="noopener noreferrer" hidden>Yükle</a>
        <button class="ografi-install-banner__action" type="button" data-ografi-install-ios aria-controls="ografiInstallHelp" aria-expanded="false" hidden>Yükle</button>
    </div>
    <p class="ografi-install-banner__help" id="ografiInstallHelp" hidden>Safari'de Paylaş menüsünü aç, ardından “Ana Ekrana Ekle” seçeneğine dokun.</p>
</section>
<script>
    (() => {
        const banner = document.querySelector('[data-ografi-install-banner]');
        if (!banner) return;
        const key = 'ografi-install-banner-dismissed-until';
        const android = /Android/i.test(navigator.userAgent);
        const ios = /iPhone|iPad|iPod/i.test(navigator.userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        const installed = () => navigator.standalone === true
            || ['standalone', 'fullscreen', 'minimal-ui', 'window-controls-overlay', 'tabbed'].some(mode => window.matchMedia('(display-mode: ' + mode + ')').matches)
            || document.referrer.startsWith('android-app://');
        const androidLink = banner.querySelector('[data-ografi-install-android]');
        const iosButton = banner.querySelector('[data-ografi-install-ios]');
        const help = banner.querySelector('#ografiInstallHelp');
        const dismiss = () => {
            banner.hidden = true;
            try { localStorage.setItem(key, String(Date.now() + 7 * 24 * 60 * 60 * 1000)); } catch (_) {}
        };
        let dismissed = false;
        try { dismissed = Number(localStorage.getItem(key) || 0) > Date.now(); } catch (_) {}
        if ((!android && !ios) || installed() || dismissed) return;
        androidLink.hidden = !android;
        iosButton.hidden = !ios || android;
        banner.hidden = false;
        banner.querySelector('[data-ografi-install-close]').addEventListener('click', dismiss);
        iosButton.addEventListener('click', () => {
            help.hidden = !help.hidden;
            iosButton.setAttribute('aria-expanded', String(!help.hidden));
        });
        window.addEventListener('appinstalled', () => { banner.hidden = true; });
        window.addEventListener('pageshow', () => { if (installed()) banner.hidden = true; });
    })();
</script>

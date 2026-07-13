@php($pwa = \App\Models\PwaSetting::currentOrNull())
@if ($pwa && $pwa->is_enabled && !request()->routeIs('video'))
<script>
    (function () {
        const basePath = @json(rtrim(request()->getBaseUrl(), '/') . '/');

        // PWA registration is intentionally disabled. Older service workers caused
        // a navigation loop, so remove every registration and stale cache once.
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations?.().then((registrations) => {
                registrations.forEach((registration) => registration.unregister());
            }).catch(() => {});

            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data?.type === 'OGRAFI_SERVICE_WORKER_REMOVED') {
                    console.info('Ografi service worker removed.');
                }
            });
        }

        if ('caches' in window) {
            caches.keys().then((keys) => {
                keys.forEach((key) => caches.delete(key));
            }).catch(() => {});
        }

        const banner = document.querySelector('[data-pwa-install-banner]');
        if (!banner) return;

        const installButton = banner.querySelector('[data-pwa-install]');
        const closeButton = banner.querySelector('[data-pwa-install-close]');
        const helper = banner.querySelector('[data-pwa-install-helper]');
        const title = banner.querySelector('[data-pwa-install-title]');
        const description = banner.querySelector('[data-pwa-install-description]');
        const buttonLabel = banner.querySelector('[data-pwa-install-button-label]');
        const dismissKey = 'ografi-pwa-install-dismissed-v3';
        let deferredPrompt = null;

        const isStandalone = () =>
            window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        const isIosSafari = () => {
            const ua = window.navigator.userAgent || '';
            const isIos = /iPad|iPhone|iPod/.test(ua);
            const isWebkit = /WebKit/.test(ua);
            const isCriOS = /CriOS/.test(ua);

            return isIos && isWebkit && !isCriOS;
        };

        const isDismissed = () => {
            try {
                return window.localStorage.getItem(dismissKey) === '1';
            } catch (error) {
                return false;
            }
        };

        const setDismissed = () => {
            try {
                window.localStorage.setItem(dismissKey, '1');
            } catch (error) {
                // ignore storage failures
            }
        };

        const showBanner = () => {
            if (isStandalone() || isDismissed()) return;
            banner.classList.remove('hidden');
        };

        const hideBanner = () => {
            banner.classList.add('hidden');
        };

        const applyIosMode = () => {
            if (title) title.textContent = @json($pwa->install_banner_title ?? ($pwa->app_name ?? __('site.pwa.install_title')));
            if (description) description.textContent = 'Safari menusunda Paylas tusuna basip Ana Ekrana Ekle secenegini kullan.';
            if (helper) {
                helper.textContent = 'iPhone ve iPad tarafinda yukleme butonu otomatik acilmaz.';
                helper.classList.remove('hidden');
            }
            if (buttonLabel) buttonLabel.textContent = 'Nasil yuklenir';
        };

        const applyInstallMode = () => {
            if (description) description.textContent = @json($pwa->install_banner_description ?? __('site.pwa.install_description'));
            if (helper) {
                helper.textContent = '';
                helper.classList.add('hidden');
            }
            if (buttonLabel) buttonLabel.textContent = @json($pwa->install_banner_button_label ?? __('site.pwa.install_button'));
        };

        closeButton?.addEventListener('click', () => {
            setDismissed();
            hideBanner();
        });

        installButton?.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                try {
                    await deferredPrompt.userChoice;
                } catch (error) {
                    // ignore prompt errors
                }
                deferredPrompt = null;
                hideBanner();
                return;
            }

            if (isIosSafari()) {
                applyIosMode();
                showBanner();
            }
        });

        window.addEventListener('beforeinstallprompt', (event) => {
            if (isStandalone() || isDismissed() || !installButton) {
                return;
            }

            event.preventDefault();
            deferredPrompt = event;
            applyInstallMode();
            showBanner();
        });

        window.addEventListener('appinstalled', () => {
            deferredPrompt = null;
            hideBanner();
            setDismissed();
        });

        if (!isStandalone() && !isDismissed() && isIosSafari()) {
            applyIosMode();
            showBanner();
        }
    })();
</script>
@endif

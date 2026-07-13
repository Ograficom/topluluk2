@php
    $googleOneTapOptIn = trim((string) $__env->yieldContent('enable_google_one_tap', '')) === 'true';
    $shouldShowGoogleOneTap = false;
    $googleOneTapClientId = '';

    if (
        $googleOneTapOptIn
        && auth()->guest()
        && request()->routeIs('login')
        && !request()->routeIs('filament.*')
        && !request()->is('admin*')
    ) {
        $googleOneTapSettings = \App\Models\SocialLoginSetting::current();
        $googleOneTapConfig = $googleOneTapSettings->providerConfig('google');
        $googleOneTapClientId = (string) ($googleOneTapConfig['client_id'] ?? '');
        $googleOneTapRedirect = (string) ($googleOneTapConfig['redirect'] ?? '');
        $googleOneTapEnabled = (bool) config('services.google.one_tap_enabled', false);
        $appUrl = (string) config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $appScheme = parse_url($appUrl, PHP_URL_SCHEME);
        $redirectHost = parse_url($googleOneTapRedirect, PHP_URL_HOST);
        $redirectScheme = parse_url($googleOneTapRedirect, PHP_URL_SCHEME);
        $googleOneTapOriginMatches = !$appHost || !$redirectHost || ($appHost === $redirectHost && $appScheme === $redirectScheme);

        $shouldShowGoogleOneTap = $googleOneTapEnabled
            && $googleOneTapSettings->isProviderEnabled('google')
            && filled($googleOneTapClientId)
            && $googleOneTapOriginMatches;
    }
@endphp

@if ($shouldShowGoogleOneTap)
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        window.addEventListener('load', function () {
            if (!window.google || !google.accounts || !google.accounts.id) {
                return;
            }

            if (document.visibilityState && document.visibilityState !== 'visible') {
                return;
            }

            google.accounts.id.initialize({
                client_id: @js($googleOneTapClientId),
                login_uri: @js(route('social.google.one_tap')),
                auto_select: false,
                cancel_on_tap_outside: true,
                context: 'signin',
                use_fedcm_for_prompt: false,
                itp_support: true
            });

            window.setTimeout(function () {
                if (document.visibilityState && document.visibilityState !== 'visible') {
                    return;
                }

                google.accounts.id.prompt();
            }, 250);
        });
    </script>
@endif

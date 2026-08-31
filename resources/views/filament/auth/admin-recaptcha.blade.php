@php
    $recaptchaSettings = \App\Models\RecaptchaSetting::currentOrNull();
    $adminRecaptchaEnabled = $recaptchaSettings?->isEnabledFor('admin') ?? false;
    $adminRecaptchaSiteKey = $adminRecaptchaEnabled
        ? trim((string) $recaptchaSettings?->resolvedSiteKey())
        : '';
@endphp

@if ($adminRecaptchaEnabled && $adminRecaptchaSiteKey !== '')
    <div
        x-data
        x-init="
            const refreshAdminRecaptcha = () => {
                if (!window.grecaptcha) {
                    window.setTimeout(refreshAdminRecaptcha, 250);
                    return;
                }

                window.grecaptcha.ready(() => {
                    window.grecaptcha.execute(@js($adminRecaptchaSiteKey), { action: 'admin_login' })
                        .then((token) => $wire.set('data.recaptcha_token', token));
                });
            };

            window.ografiRefreshAdminRecaptcha = refreshAdminRecaptcha;
            refreshAdminRecaptcha();
            window.setInterval(refreshAdminRecaptcha, 90000);
        "
        x-on:admin-recaptcha-refresh.window="window.ografiRefreshAdminRecaptcha?.()"
        class="text-xs text-gray-500 dark:text-gray-400"
    >
        Robot ve otomasyon kontrolü etkin.
    </div>

    @once
        <script src="https://www.google.com/recaptcha/api.js?render={{ rawurlencode($adminRecaptchaSiteKey) }}" async defer></script>
    @endonce
@endif

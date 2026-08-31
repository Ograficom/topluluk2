<?php

namespace App\Http\Middleware;

use App\Models\RecaptchaSetting;
use App\Services\LoginSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginPageSecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->is('login') || ! $request->isMethod('GET')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html') || ! method_exists($response, 'getContent')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '' || str_contains($content, 'data-login-security-injected')) {
            return $response;
        }

        $settings = RecaptchaSetting::currentOrNull();
        $security = app(LoginSecurityService::class);
        $challengePending = $security->hasPendingDeviceChallenge($request);
        $recaptchaEnabled = $settings?->isEnabledFor('login') ?? false;
        $siteKey = $recaptchaEnabled ? trim((string) $settings?->resolvedSiteKey()) : '';

        $challengeMarkup = $challengePending ? <<<'HTML'
<div class="simple-auth-field" data-login-device-challenge>
    <label class="simple-auth-label" for="device_verification_code">Yeni cihaz doğrulama kodu</label>
    <input
        class="simple-auth-input"
        id="device_verification_code"
        name="device_verification_code"
        type="text"
        inputmode="numeric"
        pattern="[0-9]*"
        maxlength="6"
        autocomplete="one-time-code"
        placeholder="6 haneli kod"
        autofocus
    >
    <p class="login-security-note">Bu cihaz daha önce doğrulanmadı. E-postana gönderilen kodu gir.</p>
</div>
HTML : '';

        $recaptchaInput = $recaptchaEnabled && $siteKey !== ''
            ? '<input type="hidden" name="recaptcha_token" value="" data-login-recaptcha-token>'
            : '';

        $securityMarkup = <<<HTML
<div data-login-security-injected>
    <div class="login-security-honeypot" aria-hidden="true">
        <label for="login_website">Website</label>
        <input id="login_website" name="website" type="text" tabindex="-1" autocomplete="off">
    </div>
    {$recaptchaInput}
    {$challengeMarkup}
</div>
HTML;

        $submitNeedle = '<button class="simple-auth-submit" type="submit">Giriş yapmak</button>';
        if (str_contains($content, $submitNeedle)) {
            $content = preg_replace(
                '/<button class="simple-auth-submit" type="submit">Giriş yapmak<\/button>/',
                $securityMarkup."\n                ".$submitNeedle,
                $content,
                1,
            ) ?? $content;
        }

        $style = <<<'HTML'
<style data-login-security-style>
    .login-security-honeypot {
        position: fixed !important;
        left: -10000px !important;
        top: -10000px !important;
        width: 1px !important;
        height: 1px !important;
        overflow: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    .login-security-note {
        margin: 7px 0 0;
        color: #71717a;
        font-size: 11px;
        line-height: 1.4;
    }
    html.dark .login-security-note {
        color: var(--alma-muted, #94a3b8);
    }
</style>
HTML;

        if (str_contains($content, '</head>')) {
            $content = str_replace('</head>', $style."\n</head>", $content);
        }

        if ($recaptchaEnabled && $siteKey !== '') {
            $encodedSiteKey = json_encode($siteKey, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            $scriptSiteKey = rawurlencode($siteKey);
            $script = <<<HTML
<script src="https://www.google.com/recaptcha/api.js?render={$scriptSiteKey}" defer></script>
<script data-login-security-recaptcha>
(function () {
    const form = document.querySelector('form[action$="/login"]');
    const tokenInput = document.querySelector('[data-login-recaptcha-token]');
    if (!form || !tokenInput) return;

    form.addEventListener('submit', function (event) {
        if (form.dataset.recaptchaVerified === '1') return;
        event.preventDefault();

        const submit = form.querySelector('.simple-auth-submit');
        if (submit) submit.disabled = true;

        if (!window.grecaptcha) {
            if (submit) submit.disabled = false;
            window.alert('Robot doğrulaması henüz yüklenmedi. Lütfen tekrar dene.');
            return;
        }

        grecaptcha.ready(function () {
            grecaptcha.execute({$encodedSiteKey}, { action: 'login' }).then(function (token) {
                tokenInput.value = token;
                form.dataset.recaptchaVerified = '1';
                form.submit();
            }).catch(function () {
                if (submit) submit.disabled = false;
                window.alert('Robot doğrulaması tamamlanamadı. Lütfen tekrar dene.');
            });
        });
    });
})();
</script>
HTML;

            if (str_contains($content, '</body>')) {
                $content = str_replace('</body>', $script."\n</body>", $content);
            }
        }

        $response->setContent($content);

        return $response;
    }
}

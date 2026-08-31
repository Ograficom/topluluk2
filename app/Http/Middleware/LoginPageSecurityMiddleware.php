<?php

namespace App\Http\Middleware;

use App\Models\RecaptchaSetting;
use App\Services\LoginSecurityService;
use App\Services\RecaptchaV3Verifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LoginPageSecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = RecaptchaSetting::currentOrNull();
        $context = $this->contextFor($request);

        if ($context !== null && $request->isMethod('POST')) {
            app(LoginSecurityService::class)->assertRequestAllowed($request, $settings);

            if ($context === 'forgot_password' && $request->is('forgot-password')) {
                $this->throttlePasswordReset($request);
            }

            if ($this->shouldVerifyRecaptcha($request, $context, $settings)) {
                $this->verifyRecaptcha($request, $context);
            }
        }

        /** @var Response $response */
        $response = $next($request);

        if ($context === null || ! $request->isMethod('GET')) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html') || ! method_exists($response, 'getContent')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '' || str_contains($content, 'data-auth-security-injected')) {
            return $response;
        }

        $security = app(LoginSecurityService::class);
        $challengePending = $context === 'login' && $security->hasPendingDeviceChallenge($request);
        $recaptchaEnabled = $this->shouldRenderRecaptcha($request, $context, $settings);
        $siteKey = $recaptchaEnabled ? trim((string) $settings?->resolvedSiteKey()) : '';

        $recaptchaInput = $recaptchaEnabled && $siteKey !== ''
            ? '<input type="hidden" name="recaptcha_token" value="" data-auth-recaptcha-token>'
            : '';

        $securityMarkup = <<<HTML
<div data-auth-security-injected>
    <div class="login-security-honeypot" aria-hidden="true">
        <label for="auth_website">Website</label>
        <input id="auth_website" name="website" type="text" tabindex="-1" autocomplete="off">
    </div>
    {$recaptchaInput}
</div>
HTML;

        $content = preg_replace(
            '/(<form\b[^>]*method=["\']POST["\'][^>]*>)/i',
            '$1'."\n                ".$securityMarkup,
            $content,
            1,
        ) ?? $content;

        if ($challengePending) {
            $challengeMarkup = <<<'HTML'
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
HTML;

            $submitNeedle = '<button class="simple-auth-submit" type="submit">Giriş yapmak</button>';
            if (str_contains($content, $submitNeedle)) {
                $content = str_replace(
                    $submitNeedle,
                    $challengeMarkup."\n                ".$submitNeedle,
                    $content,
                );
            }
        }

        $style = <<<'HTML'
<style data-auth-security-style>
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
            $action = $this->recaptchaAction($context);
            $encodedSiteKey = json_encode($siteKey, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            $encodedAction = json_encode($action, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            $scriptSiteKey = rawurlencode($siteKey);
            $script = <<<HTML
<script src="https://www.google.com/recaptcha/api.js?render={$scriptSiteKey}" defer></script>
<script data-auth-security-recaptcha>
(function () {
    const tokenInput = document.querySelector('[data-auth-recaptcha-token]');
    const form = tokenInput ? tokenInput.closest('form') : null;
    if (!form || !tokenInput) return;

    form.addEventListener('submit', function (event) {
        if (form.dataset.recaptchaVerified === '1') return;
        event.preventDefault();

        const submit = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submit) submit.disabled = true;

        if (!window.grecaptcha) {
            if (submit) submit.disabled = false;
            window.alert('Robot doğrulaması henüz yüklenmedi. Lütfen tekrar dene.');
            return;
        }

        grecaptcha.ready(function () {
            grecaptcha.execute({$encodedSiteKey}, { action: {$encodedAction} }).then(function (token) {
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

    private function contextFor(Request $request): ?string
    {
        if ($request->is('login')) {
            return 'login';
        }

        if ($request->is('register') || $request->is('register/*')) {
            return 'register';
        }

        if ($request->is('forgot-password')) {
            return 'forgot_password';
        }

        return null;
    }

    private function shouldRenderRecaptcha(Request $request, string $context, ?RecaptchaSetting $settings): bool
    {
        if (! ($settings?->isEnabledFor($context) ?? false)) {
            return false;
        }

        return match ($context) {
            'login' => $request->is('login'),
            'register' => $request->is('register'),
            'forgot_password' => $request->is('forgot-password'),
            default => false,
        };
    }

    private function shouldVerifyRecaptcha(Request $request, string $context, ?RecaptchaSetting $settings): bool
    {
        if (! ($settings?->isEnabledFor($context) ?? false)) {
            return false;
        }

        return match ($context) {
            // Login reCAPTCHA is verified inside FortifyServiceProvider so the same
            // one-time token is never sent to Google twice.
            'login' => false,
            'register' => $request->is('register/email'),
            'forgot_password' => $request->is('forgot-password'),
            default => false,
        };
    }

    private function verifyRecaptcha(Request $request, string $context): void
    {
        $token = trim((string) $request->input('recaptcha_token', ''));
        if ($token === '') {
            throw ValidationException::withMessages([
                'email' => 'Robot doğrulaması gerekli.',
            ]);
        }

        $result = app(RecaptchaV3Verifier::class)->verify(
            $token,
            $this->recaptchaAction($context),
            $request->ip(),
        );

        if (! ($result['success'] ?? false)) {
            throw ValidationException::withMessages([
                'email' => 'Robot doğrulaması başarısız. Lütfen tekrar dene.',
            ]);
        }
    }

    private function recaptchaAction(string $context): string
    {
        return match ($context) {
            'register' => 'register',
            'forgot_password' => 'forgot_password',
            default => 'login',
        };
    }

    private function throttlePasswordReset(Request $request): void
    {
        $email = mb_strtolower(trim((string) $request->input('email', '')));
        $ip = (string) $request->ip();
        $specificKey = 'auth-security:forgot-password:'.hash('sha256', $email.'|'.$ip);
        $ipKey = 'auth-security:forgot-password-ip:'.hash('sha256', $ip);

        if (RateLimiter::tooManyAttempts($specificKey, 5) || RateLimiter::tooManyAttempts($ipKey, 20)) {
            throw ValidationException::withMessages([
                'email' => 'Çok fazla şifre sıfırlama isteği gönderildi. Lütfen biraz sonra tekrar dene.',
            ]);
        }

        RateLimiter::hit($specificKey, 60);
        RateLimiter::hit($ipKey, 600);
    }
}

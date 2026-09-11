<?php

namespace App\Http\Controllers;

use App\Models\RecaptchaSetting;
use App\Models\SocialLoginSetting;
use App\Models\User;
use App\Services\LoginSecurityService;
use App\Services\StrictLoginNetworkGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialLoginController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        $provider = strtolower($provider);
        $settings = SocialLoginSetting::current();

        if (! $settings->isProviderEnabled($provider)) {
            abort(404);
        }

        $config = $settings->providerConfig($provider);

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            abort(403);
        }

        config(["services.{$provider}" => $config]);

        return $this->driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $provider = strtolower($provider);
        $settings = SocialLoginSetting::current();
        $securitySettings = RecaptchaSetting::currentOrNull();
        $loginSecurity = app(LoginSecurityService::class);

        try {
            app(StrictLoginNetworkGuard::class)->assertAllowed($request, $securitySettings);
            $loginSecurity->assertRequestAllowed($request, $securitySettings);
        } catch (ValidationException $exception) {
            return redirect()->route('login')->withErrors($exception->errors());
        }

        if (! $settings->isProviderEnabled($provider)) {
            abort(404);
        }

        $config = $settings->providerConfig($provider);

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            abort(403);
        }

        config(["services.{$provider}" => $config]);

        try {
            $socialUser = $this->driver($provider)->user();
        } catch (Throwable $e) {
            Log::warning('Social login callback failed.', [
                'provider' => $provider,
                'message' => $e->getMessage(),
                'redirect' => $config['redirect'] ?? null,
            ]);

            return redirect()
                ->route('login')
                ->with('error', ucfirst($provider).' girişi başarısız oldu.');
        }

        $providerId = $socialUser->getId();
        $email = $socialUser->getEmail();

        $user = $this->resolveSocialUser(
            provider: $provider,
            providerId: (string) $providerId,
            email: $email,
            name: $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
            avatar: $socialUser->getAvatar(),
        );

        return $this->completeLoginAfterDeviceCheck(
            user: $user,
            request: $request,
            loginSecurity: $loginSecurity,
            securitySettings: $securitySettings,
        );
    }

    public function oneTap(Request $request): RedirectResponse
    {
        $settings = SocialLoginSetting::current();
        $securitySettings = RecaptchaSetting::currentOrNull();
        $loginSecurity = app(LoginSecurityService::class);

        try {
            app(StrictLoginNetworkGuard::class)->assertAllowed($request, $securitySettings);
            $loginSecurity->assertRequestAllowed($request, $securitySettings);
        } catch (ValidationException $exception) {
            return redirect()->route('login')->withErrors($exception->errors());
        }

        if (! $settings->isProviderEnabled('google')) {
            abort(404);
        }

        $config = $settings->providerConfig('google');
        $clientId = (string) ($config['client_id'] ?? '');
        $credential = (string) $request->input('credential', '');

        if ($clientId === '' || $credential === '') {
            return redirect()->route('login')->with('error', 'Google oturum açma başarısız oldu.');
        }

        $cookieToken = (string) $request->cookie('g_csrf_token', '');
        $bodyToken = (string) $request->input('g_csrf_token', '');

        if ($cookieToken === '' || $bodyToken === '' || ! hash_equals($cookieToken, $bodyToken)) {
            return redirect()->route('login')->with('error', 'Google oturum açma isteği doğrulanamadı.');
        }

        $response = Http::asForm()
            ->timeout(10)
            ->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $credential,
            ]);

        if (! $response->ok()) {
            return redirect()->route('login')->with('error', 'Google kimlik doğrulaması başarısız oldu.');
        }

        $payload = $response->json();
        $audience = (string) ($payload['aud'] ?? '');
        $issuer = (string) ($payload['iss'] ?? '');
        $subject = (string) ($payload['sub'] ?? '');
        $email = (string) ($payload['email'] ?? '');
        $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (
            $audience !== $clientId ||
            ! in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true) ||
            $subject === '' ||
            $email === '' ||
            ! $emailVerified
        ) {
            return redirect()->route('login')->with('error', 'Google hesabı doğrulanamadı.');
        }

        $user = $this->resolveSocialUser(
            provider: 'google',
            providerId: $subject,
            email: $email,
            name: (string) ($payload['name'] ?? Str::before($email, '@') ?: 'User'),
            avatar: (string) ($payload['picture'] ?? ''),
        );

        return $this->completeLoginAfterDeviceCheck(
            user: $user,
            request: $request,
            loginSecurity: $loginSecurity,
            securitySettings: $securitySettings,
        );
    }

    public function showDeviceVerification(Request $request): View|RedirectResponse
    {
        $loginSecurity = app(LoginSecurityService::class);

        if (! $loginSecurity->hasPendingDeviceChallenge($request)) {
            return redirect()->route('login');
        }

        return view('auth.social-device-verification');
    }

    public function verifyDevice(Request $request): RedirectResponse
    {
        $securitySettings = RecaptchaSetting::currentOrNull();
        $loginSecurity = app(LoginSecurityService::class);

        try {
            app(StrictLoginNetworkGuard::class)->assertAllowed($request, $securitySettings);
            $loginSecurity->assertRequestAllowed($request, $securitySettings);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        $request->validate([
            'device_verification_code' => ['required', 'digits:6'],
        ], [
            'device_verification_code.required' => '6 haneli cihaz doğrulama kodunu gir.',
            'device_verification_code.digits' => 'Cihaz doğrulama kodu 6 haneli olmalı.',
        ]);

        $userId = $loginSecurity->pendingDeviceUserId($request);
        $user = $userId ? User::query()->find($userId) : null;

        if (! $user) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Cihaz doğrulama oturumunun süresi doldu. Tekrar giriş yap.']);
        }

        try {
            $loginSecurity->verifyOrChallenge($user, $request, $securitySettings);
        } catch (ValidationException $exception) {
            if ($loginSecurity->hasPendingDeviceChallenge($request)) {
                return back()->withErrors($exception->errors());
            }

            return redirect()->route('login')->withErrors($exception->errors());
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    private function completeLoginAfterDeviceCheck(
        User $user,
        Request $request,
        LoginSecurityService $loginSecurity,
        ?RecaptchaSetting $securitySettings,
    ): RedirectResponse {
        try {
            $loginSecurity->verifyOrChallenge($user, $request, $securitySettings);
        } catch (ValidationException $exception) {
            if ($loginSecurity->hasPendingDeviceChallenge($request)) {
                $message = collect($exception->errors())->flatten()->first();

                return redirect()
                    ->route('social.device.verify')
                    ->with('status', $message ?: 'E-postana gönderilen cihaz doğrulama kodunu gir.');
            }

            return redirect()->route('login')->withErrors($exception->errors());
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    private function driver(string $provider)
    {
        return Socialite::driver($provider)->stateless();
    }

    private function resolveSocialUser(
        string $provider,
        string $providerId,
        ?string $email,
        string $name,
        ?string $avatar = null,
    ): User {
        $user = User::query()
            ->where('social_provider', $provider)
            ->where('social_provider_id', $providerId)
            ->first();

        if (! $user && $email) {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            $isFirstUser = ! User::query()->exists();

            $user = User::create([
                'name' => $name !== '' ? $name : 'User',
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'profile_photo_path' => $avatar ?: null,
                'social_provider' => $provider,
                'social_provider_id' => $providerId,
                'role' => $isFirstUser ? User::ROLE_ADMIN : User::ROLE_WRITER,
            ]);
        } else {
            $user->forceFill([
                'social_provider' => $provider,
                'social_provider_id' => $providerId,
                'profile_photo_path' => $avatar ?: $user->profile_photo_path,
            ])->save();
        }

        if (! $user->email_verified_at && $email) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }
}

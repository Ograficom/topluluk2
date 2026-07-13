<?php

namespace App\Http\Controllers;

use App\Models\SocialLoginSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialLoginController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        $provider = strtolower($provider);
        $settings = SocialLoginSetting::current();

        if (!$settings->isProviderEnabled($provider)) {
            abort(404);
        }

        $config = $settings->providerConfig($provider);

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            abort(403);
        }

        config(["services.{$provider}" => $config]);

        return $this->driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $provider = strtolower($provider);
        $settings = SocialLoginSetting::current();

        if (!$settings->isProviderEnabled($provider)) {
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
                ->with('error', ucfirst($provider) . ' girisi basarisiz oldu.');
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

        Auth::login($user, true);

        return redirect()->intended('/');
    }

    public function oneTap(Request $request): RedirectResponse
    {
        $settings = SocialLoginSetting::current();

        if (!$settings->isProviderEnabled('google')) {
            abort(404);
        }

        $config = $settings->providerConfig('google');
        $clientId = (string) ($config['client_id'] ?? '');
        $credential = (string) $request->input('credential', '');

        if ($clientId === '' || $credential === '') {
            return redirect()->route('login')->with('error', 'Google oturum acma basarisiz oldu.');
        }

        $cookieToken = (string) $request->cookie('g_csrf_token', '');
        $bodyToken = (string) $request->input('g_csrf_token', '');

        if ($cookieToken === '' || $bodyToken === '' || !hash_equals($cookieToken, $bodyToken)) {
            return redirect()->route('login')->with('error', 'Google oturum acma istegi dogrulanamadi.');
        }

        $response = Http::asForm()
            ->timeout(10)
            ->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $credential,
            ]);

        if (!$response->ok()) {
            return redirect()->route('login')->with('error', 'Google kimlik dogrulamasi basarisiz oldu.');
        }

        $payload = $response->json();
        $audience = (string) ($payload['aud'] ?? '');
        $issuer = (string) ($payload['iss'] ?? '');
        $subject = (string) ($payload['sub'] ?? '');
        $email = (string) ($payload['email'] ?? '');
        $emailVerified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (
            $audience !== $clientId ||
            !in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true) ||
            $subject === '' ||
            $email === '' ||
            !$emailVerified
        ) {
            return redirect()->route('login')->with('error', 'Google hesabi dogrulanamadi.');
        }

        $user = $this->resolveSocialUser(
            provider: 'google',
            providerId: $subject,
            email: $email,
            name: (string) ($payload['name'] ?? Str::before($email, '@') ?: 'User'),
            avatar: (string) ($payload['picture'] ?? ''),
        );

        Auth::login($user, true);

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

        if (!$user && $email) {
            $user = User::query()->where('email', $email)->first();
        }

        if (!$user) {
            $isFirstUser = !User::query()->exists();

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

        if (!$user->email_verified_at && $email) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }
}

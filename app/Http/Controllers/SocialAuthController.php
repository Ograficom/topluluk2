<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'yandex'], true), 404);

        $settings = SiteSetting::current();
        $enabled = $provider === 'google'
            ? $settings?->isGoogleLoginEnabled()
            : $settings?->isYandexLoginEnabled();

        if (! $enabled) {
            return redirect()->route('login')->withErrors(['social' => 'Bu giriş yöntemi şu anda kullanılamıyor.']);
        }

        $state = Str::random(48);
        $request->session()->put('oauth_state_'.$provider, $state);
        $query = [
            'client_id' => $provider === 'google' ? $settings->google_client_id : $settings->yandex_client_id,
            'redirect_uri' => route('oauth.callback', ['provider' => $provider]),
            'response_type' => 'code',
            'state' => $state,
        ];

        if ($provider === 'google') {
            $query['scope'] = 'openid email profile';
            $query['prompt'] = 'select_account';

            return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($query));
        }

        $query['scope'] = 'login:email login:info';

        return redirect()->away('https://oauth.yandex.com/authorize?'.http_build_query($query));
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'yandex'], true), 404);

        $expectedState = (string) $request->session()->pull('oauth_state_'.$provider);
        $receivedState = (string) $request->query('state');

        if ($expectedState === '' || $receivedState === '' || ! hash_equals($expectedState, $receivedState)) {
            return redirect()->route('login')->withErrors(['social' => 'Güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.']);
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('login')->withErrors(['social' => 'Giriş işlemi iptal edildi veya sağlayıcı tarafından reddedildi.']);
        }

        $settings = SiteSetting::current();
        $enabled = $provider === 'google'
            ? $settings?->isGoogleLoginEnabled()
            : $settings?->isYandexLoginEnabled();

        if (! $enabled) {
            return redirect()->route('login')->withErrors(['social' => 'Bu giriş yöntemi şu anda devre dışı.']);
        }

        $clientId = $provider === 'google' ? $settings->google_client_id : $settings->yandex_client_id;
        $clientSecret = $provider === 'google' ? $settings->google_client_secret : $settings->yandex_client_secret;

        try {
            $token = Http::asForm()->acceptJson()->post(
                $provider === 'google' ? 'https://oauth2.googleapis.com/token' : 'https://oauth.yandex.com/token',
                [
                    'grant_type' => 'authorization_code',
                    'code' => $request->query('code'),
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => route('oauth.callback', ['provider' => $provider]),
                ],
            )->throw()->json();

            $profileRequest = $provider === 'google'
                ? Http::withToken($token['access_token'] ?? '')
                : Http::withHeaders(['Authorization' => 'OAuth '.($token['access_token'] ?? '')]);

            $profile = $profileRequest
                ->acceptJson()
                ->get($provider === 'google'
                    ? 'https://openidconnect.googleapis.com/v1/userinfo'
                    : 'https://login.yandex.ru/info?format=json')
                ->throw()
                ->json();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors(['social' => 'Kimlik sağlayıcısından kullanıcı bilgileri alınamadı. Daha sonra tekrar deneyin.']);
        }

        $identity = $provider === 'google'
            ? [
                'id' => $profile['sub'] ?? null,
                'email' => ($profile['email_verified'] ?? false) ? ($profile['email'] ?? null) : null,
                'name' => $profile['name'] ?? $profile['given_name'] ?? null,
                'username' => $profile['email'] ?? null,
            ]
            : [
                'id' => $profile['id'] ?? null,
                'email' => $profile['default_email'] ?? null,
                'name' => $profile['display_name'] ?? $profile['real_name'] ?? $profile['login'] ?? null,
                'username' => $profile['login'] ?? null,
            ];

        if (! filled($identity['id']) || ! filled($identity['email']) || ! filter_var($identity['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('login')->withErrors(['social' => 'Sağlayıcı doğrulanmış bir e-posta adresi döndürmedi. E-posta ile kayıt olup tekrar deneyin.']);
        }

        $social = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', (string) $identity['id'])
            ->first();

        if ($social) {
            $user = $social->user;
        } else {
            $user = User::query()->where('email', $identity['email'])->first();

            if (! $user) {
                $base = Str::slug((string) ($identity['username'] ?: Str::before($identity['email'], '@')));
                $base = $base !== '' ? Str::limit($base, 24, '') : 'user';
                $username = $base;
                $suffix = 1;

                while (User::query()->where('username', $username)->exists()) {
                    $username = Str::limit($base, 24, '').$suffix++;
                }

                $user = User::create([
                    'name' => filled($identity['name']) ? $identity['name'] : $username,
                    'username' => $username,
                    'email' => $identity['email'],
                    'email_verified_at' => now(),
                    'password' => Str::random(64),
                    'verification_status' => 'unverified',
                ]);
            }

            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => (string) $identity['id'],
                'provider_email' => $identity['email'],
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('posts.index'));
    }
}

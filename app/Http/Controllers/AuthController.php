<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View { return view('auth.register'); }

    public function checkUsername(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:30'],
        ]);

        $available = ! User::query()
            ->where('username', $validated['username'])
            ->exists();

        return response()->json([
            'available' => $available,
            'message' => $available ? 'Kullanıcı adı kullanılabilir.' : 'Bu kullanıcı adı zaten alınmış.',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:30', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
            'agree_rules' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['username'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'verification_status' => 'unverified',
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('status', 'Kayıt tamamlandı. Hesabını etkinleştirmek için e-posta adresine gönderdiğimiz bağlantıya tıkla.');
    }

    public function showLogin(): View { return view('auth.login'); }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $key = mb_strtolower($validated['login_name']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts('login:'.$key, 5)) {
            return back()->withErrors(['login_name' => 'Çok fazla giriş denemesi yapıldı. Lütfen bir dakika sonra tekrar deneyin.'])->onlyInput('login_name');
        }

        $user = User::query()->where('email', $validated['login_name'])->orWhere('username', $validated['login_name'])->first();
        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $validated['password']], $request->boolean('remember'))) {
            RateLimiter::hit('login:'.$key, 60);
            return back()->withErrors(['login_name' => 'Giriş bilgileri eşleşmiyor.'])->onlyInput('login_name');
        }

        RateLimiter::clear('login:'.$key);
        if ($user->two_factor_confirmed_at) {
            Auth::logout();
            $request->session()->regenerate();
            $request->session()->put([
                'two_factor_login_user_id' => $user->id,
                'two_factor_login_remember' => $request->boolean('remember'),
                'two_factor_login_started_at' => now()->timestamp,
            ]);
            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('status', 'Hesabın açık. E-posta adresini doğrulayana veya yönetici onaylayana kadar doğrulama ekranında kalacaksın.');
        }

        return redirect()->intended(route('posts.index'));
    }

    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor_login_user_id')) return redirect()->route('login');
        if (now()->timestamp - (int) $request->session()->get('two_factor_login_started_at', 0) > 600) {
            $request->session()->forget(['two_factor_login_user_id', 'two_factor_login_remember', 'two_factor_login_started_at']);
            return redirect()->route('login')->withErrors(['login_name' => 'Doğrulama oturumunun süresi doldu. Yeniden giriş yapın.']);
        }
        return view('auth.two-factor-challenge');
    }

    public function verifyTwoFactorChallenge(Request $request): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'string', 'max:20']]);
        $userId = $request->session()->get('two_factor_login_user_id');
        if (! $userId || now()->timestamp - (int) $request->session()->get('two_factor_login_started_at', 0) > 600) {
            $request->session()->forget(['two_factor_login_user_id', 'two_factor_login_remember', 'two_factor_login_started_at']);
            return redirect()->route('login')->withErrors(['login_name' => 'Doğrulama oturumunun süresi doldu. Yeniden giriş yapın.']);
        }

        $user = User::query()->find($userId);
        if (! $user || ! $user->two_factor_confirmed_at || ! $this->consumeTwoFactorCode($user, $validated['code'])) {
            return back()->withErrors(['code' => 'Doğrulama kodu geçersiz.']);
        }

        $remember = (bool) $request->session()->get('two_factor_login_remember');
        $request->session()->forget(['two_factor_login_user_id', 'two_factor_login_remember', 'two_factor_login_started_at']);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        return redirect()->intended(route('posts.index'));
    }

    private function consumeTwoFactorCode(User $user, string $code): bool
    {
        if ($user->two_factor_secret && \App\Support\Totp::verify($user->two_factor_secret, $code)) return true;
        $codes = $user->two_factor_recovery_codes ?? [];
        $normalized = strtoupper(str_replace([' ', '-'], '', $code));
        foreach ($codes as $index => $recoveryCode) {
            if (hash_equals($recoveryCode, $normalized)) {
                unset($codes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();
                return true;
            }
        }
        return false;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('posts.index');
    }
}

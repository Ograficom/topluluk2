<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\UsernameFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:30', new UsernameFormat, 'unique:users,username'],
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

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('posts.index'))
            ->with('status', 'Hesabınız oluşturuldu.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $key = mb_strtolower($validated['login_name']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts('login:'.$key, 5)) {
            return back()
                ->withErrors(['login_name' => 'Çok fazla giriş denemesi yapıldı. Lütfen bir dakika sonra tekrar deneyin.'])
                ->onlyInput('login_name');
        }

        $user = User::query()
            ->where('email', $validated['login_name'])
            ->orWhere('username', $validated['login_name'])
            ->first();

        if (! $user || ! Auth::attempt([
            'email' => $user->email,
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            RateLimiter::hit('login:'.$key, 60);

            return back()
                ->withErrors(['login_name' => 'Giriş bilgileri eşleşmiyor.'])
                ->onlyInput('login_name');
        }

        RateLimiter::clear('login:'.$key);
        $request->session()->regenerate();

        return redirect()->intended(route('posts.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('posts.index');
    }
}

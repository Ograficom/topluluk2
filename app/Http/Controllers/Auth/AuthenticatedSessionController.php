<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (Auth::check() && auth()->user()->google2fa_status && ! $request->session()->has('user_2fa')) {
            return redirect()->route('login.2fa');
        } else {
            $request->session()->regenerate();

            return redirect()->intended(route('feed.home', absolute: false));
        }
    }

    public function twoFactorAuthentication(Request $request): Response
    {
        return Inertia::render('Auth/TwoFactorAuth');
    }

    /**
     * Handle an incoming 2FA authentication request.
     */
    public function twoFactorAuthenticationStore(Request $request): RedirectResponse|Response
    {
        $request->validate([
            'otp_code' => 'required|numeric',
        ]);

        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey(auth()->user()->google2fa_secret, $request->otp_code);

        $user = auth()->user();

        if ($valid) {
            session()->put('user_2fa', hash_encode($user->id));

            $request->session()->regenerate();

            return redirect()->intended(route('feed.home', absolute: false));
        } else {
            toast_error(__('Invalid OTP code'));

            return redirect()->route('login.2fa');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

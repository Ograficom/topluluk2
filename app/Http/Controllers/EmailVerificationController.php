<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(): View|RedirectResponse
    {
        return request()->user()->hasVerifiedEmail()
            ? redirect()->intended(route('posts.index'))
            : view('auth.verify-email');
    }

    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = $request->user();

        if ((string) $user->getKey() !== $id || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('posts.index')->with('status', 'E-posta adresin zaten doğrulanmış.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('login')->with('status', 'E-posta adresin doğrulandı. Artık giriş yapabilirsin.');
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('posts.index');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Doğrulama bağlantısı e-posta adresine yeniden gönderildi.');
    }
}

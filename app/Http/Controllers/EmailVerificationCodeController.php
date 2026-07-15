<?php

namespace App\Http\Controllers;

use App\Models\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmailVerificationCodeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'E-postanıza gönderilen kodu girin.',
            'code.digits' => 'Doğrulama kodu 6 haneli olmalıdır.',
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(config('fortify.home', '/'));
        }

        $result = DB::transaction(function () use ($user, $validated): string {
            $verification = EmailVerificationCode::query()
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->first();

            if (!$verification || $verification->expires_at->isPast()) {
                return 'expired';
            }

            if ($verification->attempts >= 5) {
                return 'locked';
            }

            if (!Hash::check($validated['code'], $verification->code_hash)) {
                $verification->increment('attempts');
                return 'invalid';
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            $verification->delete();

            return 'verified';
        });

        if ($result !== 'verified') {
            $messages = [
                'expired' => 'Kodun süresi dolmuş. Yeni kod gönderin.',
                'locked' => 'Çok fazla hatalı deneme yapıldı. Yeni kod gönderin.',
                'invalid' => 'Girdiğiniz doğrulama kodu hatalı.',
            ];

            throw ValidationException::withMessages([
                'code' => $messages[$result] ?? 'Doğrulama kodu kullanılamadı.',
            ]);
        }

        return redirect()->intended(config('fortify.home', '/'))
            ->with('status', 'E-posta adresiniz doğrulandı.');
    }
}

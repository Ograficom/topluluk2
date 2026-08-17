<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\VerifyDailyDigestEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class DashboardNotificationSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'comments' => ['nullable', 'boolean'],
            'replies' => ['nullable', 'boolean'],
            'likes' => ['nullable', 'boolean'],
            'followers' => ['nullable', 'boolean'],
            'mentions' => ['nullable', 'boolean'],
            'daily_digest_enabled' => ['nullable', 'boolean'],
        ]);

        $preferences = [
            'comments' => (bool) ($validated['comments'] ?? false),
            'replies' => (bool) ($validated['replies'] ?? false),
            'likes' => (bool) ($validated['likes'] ?? false),
            'followers' => (bool) ($validated['followers'] ?? false),
            'mentions' => (bool) ($validated['mentions'] ?? false),
        ];

        $request->session()->put('dashboard_notifications', $preferences);

        $user = $request->user();
        $digestEnabled = (bool) ($validated['daily_digest_enabled'] ?? false);
        $changes = ['daily_digest_enabled' => $digestEnabled];

        if ($digestEnabled && blank($user->daily_digest_email)) {
            $changes['daily_digest_email'] = mb_strtolower(trim((string) $user->email));
            $changes['daily_digest_email_verified_at'] = $user->hasVerifiedEmail() ? now() : null;
        }

        $user->forceFill($changes)->save();

        return back()->with('status', 'notifications-updated');
    }

    public function updateDigestEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'daily_digest_email' => ['required', 'string', 'email:rfc', 'max:255'],
        ]);

        $user = $request->user();
        $email = mb_strtolower(trim($validated['daily_digest_email']));
        $accountEmail = mb_strtolower(trim((string) $user->email));
        $verifiedAt = $email === $accountEmail && $user->hasVerifiedEmail() ? now() : null;

        $user->forceFill([
            'daily_digest_email' => $email,
            'daily_digest_email_verified_at' => $verifiedAt,
            'daily_digest_last_sent_at' => null,
        ])->save();

        if ($verifiedAt === null) {
            Notification::route('mail', $email)
                ->notify(new VerifyDailyDigestEmail($user, $email));

            return back()->with('status', 'digest-verification-sent');
        }

        return back()->with('status', 'digest-email-updated');
    }

    public function verifyDigestEmail(Request $request, string $userId, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($userId);
        $email = mb_strtolower(trim((string) $user->daily_digest_email));

        abort_unless(
            $email !== '' && hash_equals(sha1($email), $hash),
            403,
            'Geçersiz e-posta doğrulama bağlantısı.'
        );

        $user->forceFill(['daily_digest_email_verified_at' => now()])->save();

        if ($request->user()?->is($user)) {
            return redirect()->route('dashboard.notifications')
                ->with('status', 'digest-email-verified');
        }

        return redirect()->route('login')
            ->with('status', 'Günlük özet e-posta adresiniz doğrulandı.');
    }

    public function unsubscribe(Request $request, string $userId, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($userId);
        $email = mb_strtolower(trim((string) $user->daily_digest_email));

        abort_unless(
            $email !== '' && hash_equals(sha1($email), $hash),
            403,
            'Geçersiz abonelik bağlantısı.'
        );

        $user->forceFill(['daily_digest_enabled' => false])->save();

        if ($request->user()?->is($user)) {
            return redirect()->route('dashboard.notifications')
                ->with('status', 'digest-unsubscribed');
        }

        return redirect()->route('login')
            ->with('status', 'Günlük e-posta özeti kapatıldı.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const TABS = ['profile', 'site', 'security', 'blacklist', 'verify', 'delete'];

    public function edit(Request $request): View
    {
        $user = $request->user();
        $activeTab = old(
            'activeTab',
            $request->session()->get('activeTab', $request->query('tab', 'profile')),
        );

        return view('settings.index', [
            'user' => $user,
            'blockedUsers' => $user->blockedUsers()->orderBy('name')->get(),
            'activeTab' => in_array($activeTab, self::TABS, true) ? $activeTab : 'profile',
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:30', 'alpha_dash:ascii', Rule::unique('users', 'username')->ignore($user->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'signature' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:2048'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'telegram' => ['nullable', 'string', 'max:100'],
            'threads' => ['nullable', 'string', 'max:100'],
            'youtube' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'delete_avatar' => ['sometimes', 'boolean'],
        ]);

        $newAvatar = $request->file('avatar')?->store('avatars', 'public');

        if ($request->hasFile('avatar') && ! $newAvatar) {
            throw new \RuntimeException('The profile avatar could not be stored.');
        }

        $oldAvatar = $user->avatar_path;
        $user->fill(collect($validated)->except(['avatar', 'delete_avatar'])->all());

        if ($newAvatar) {
            $user->avatar_path = $newAvatar;
        } elseif ($request->boolean('delete_avatar')) {
            $user->avatar_path = null;
        }

        $user->save();

        if ($oldAvatar && $oldAvatar !== $user->avatar_path) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return back()->with('activeTab', 'profile')->with('status', 'Profil bilgileriniz kaydedildi.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'timezone' => ['required', Rule::in(timezone_identifiers_list())],
            'show_adult_content' => ['sometimes', 'boolean'],
            'notify_replies' => ['sometimes', 'boolean'],
            'notify_posts' => ['sometimes', 'boolean'],
            'notify_announcements' => ['sometimes', 'boolean'],
        ]);

        $request->user()->update([
            'timezone' => $validated['timezone'],
            'show_adult_content' => $request->boolean('show_adult_content'),
            'notify_replies' => $request->boolean('notify_replies'),
            'notify_posts' => $request->boolean('notify_posts'),
            'notify_announcements' => $request->boolean('notify_announcements'),
        ]);

        return back()->with('activeTab', 'site')->with('status', 'Tercihleriniz kaydedildi.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('activeTab', 'security')->with('status', 'Parolanız güncellendi.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
        ]);

        $user = $request->user();
        $user->email = $validated['email'];
        $user->email_verified_at = null;
        $user->save();

        return back()->with('activeTab', 'security')->with('status', 'E-posta adresiniz güncellendi.');
    }

    public function requestVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedBadge()) {
            return back()->withInput(['activeTab' => 'verify'])
                ->withErrors(['verification_reason' => 'Hesabınız zaten doğrulanmış.']);
        }

        if ($user->verification_status === 'pending') {
            return back()->withInput(['activeTab' => 'verify'])
                ->withErrors(['verification_reason' => 'Doğrulama başvurunuz zaten inceleniyor.']);
        }

        $validated = $request->validate([
            'verification_reason' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $user->update([
            'verification_status' => 'pending',
            'verification_reason' => $validated['verification_reason'],
            'verification_requested_at' => now(),
        ]);

        return back()
            ->with('activeTab', 'verify')
            ->with('status', 'Mavi tik başvurunuz yönetici incelemesine gönderildi.');
    }

    public function cancelVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->verification_status === 'pending') {
            $user->update([
                'verification_status' => 'unverified',
                'verification_reason' => null,
                'verification_requested_at' => null,
            ]);
        } else {
            return back()->withInput(['activeTab' => 'verify'])
                ->withErrors(['verification_reason' => 'İptal edilecek bir doğrulama başvurusu bulunamadı.']);
        }

        return back()
            ->with('activeTab', 'verify')
            ->with('status', 'Doğrulama başvurunuz iptal edildi.');
    }

    public function blockUser(Request $request, User $blockedUser): RedirectResponse
    {
        abort_if($request->user()->is($blockedUser), 422, 'Kendi hesabınızı engelleyemezsiniz.');

        $request->user()->blockedUsers()->syncWithoutDetaching([$blockedUser->id]);

        return redirect()->route('settings.edit', ['tab' => 'blacklist'])
            ->with('activeTab', 'blacklist')
            ->with('status', 'Kullanıcı kara listeye eklendi.');
    }

    public function unblockUser(Request $request, User $blockedUser): RedirectResponse
    {
        $request->user()->blockedUsers()->detach($blockedUser->id);

        return back()->with('activeTab', 'blacklist')->with('status', 'Kullanıcı kara listeden çıkarıldı.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('posts.index');
    }
}

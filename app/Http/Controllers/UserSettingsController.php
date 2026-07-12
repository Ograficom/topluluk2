<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserBadgeResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserSimpleResource;
use App\Models\User;
use App\Services\SeoGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserSettingsController extends Controller
{
    // Display the user's settings page
    public function showUserSettings(User $user): Response
    {
        $seo = new SeoGeneratorService();

        return Inertia::render('User/Settings/Index', [
            'user' => UserProfileResource::make($user),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    // Display the user account form
    public function showAccountSettings(User $user): Response
    {
        $seo = new SeoGeneratorService();

        return Inertia::render('User/Settings/Account', [
            'user' => UserProfileResource::make($user),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    // Update the user's account information
    public function updateAccountSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:100'],
            'username' => ['string', 'min:2', 'max:50', Rule::unique(User::class)->ignore($request->user()->id)],
            'email' => ['email', 'max:100', Rule::unique(User::class)->ignore($request->user()->id)],
        ]);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;

            // TODO: Send verification email
        }

        $request->user()->save();

        toast_success(__('Updated successfully'));

        return to_route('user.settings.show', $request->user());
    }

    // Display the user profile form
    public function showProfileSettings(User $user): Response
    {
        $seo = new SeoGeneratorService();

        $user->load('profile');

        return Inertia::render('User/Settings/Profile', [
            'user' => UserProfileResource::make($user),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    // Update the user's profile information
    public function updateProfileSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'min:2', 'max:100'],
            'education' => ['nullable', 'string', 'min:2', 'max:100'],
            'company' => ['nullable', 'string', 'min:2', 'max:100'],
            'website' => ['nullable', 'url', 'min:2', 'max:100'],
            'facebook' => ['nullable', 'url', 'min:2', 'max:100'],
            'twitter' => ['nullable', 'url', 'min:2', 'max:100'],
            'instagram' => ['nullable', 'url', 'min:2', 'max:100'],
            'tiktok' => ['nullable', 'url', 'min:10', 'max:100'],
            'youtube' => ['nullable', 'url', 'min:10', 'max:100'],
        ]);

        $request->user()->profile->fill($validated);
        $request->user()->profile->save();

        toast_success(__('Updated successfully'));

        return to_route('user.settings.show', $request->user()->username);
    }

    // Display the user password form
    public function showPasswordSettings(User $user): Response
    {
        $seo = new SeoGeneratorService();

        return Inertia::render('User/Settings/Password', [
            'user' => UserProfileResource::make($user),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    // Show blocked users
    public function showBlockedUsers(User $user): Response
    {
        $seo = new SeoGeneratorService();

        $blockedUsers = $user->getBlocking()->pluck('blocking');

        return Inertia::render('User/Settings/BlockedUsers', [
            'user' => UserProfileResource::make($user),
            'blockedUsers' => UserSimpleResource::collection($blockedUsers),
        ])->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    // Show user badges
    public function showBadges(User $user): Response
    {
        $seo = new SeoGeneratorService();

        $badges = $user->badges()->orderBy('sort_id')->get();

        return Inertia::render('User/Settings/Badges', [
            'user' => UserProfileResource::make($user),
            'badges' => UserBadgeResource::collection($badges),
        ])->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function badgesSortable(Request $request, User $user)
    {
        if (! $request->has('ids') || is_null($request->ids)) {
            return response()->json(['success' => false, 'message' => __('Failed to sort the badges')]);
        }

        foreach ($request->ids as $sortOrder => $id) {
            $userBadge = $user->badges->where('id', $id)->first();
            $userBadge->sort_id = $sortOrder + 1;
            $userBadge->update();
        }

        return response()->json(['success' => true, 'message' => __('Badges reordered successfully')]);
    }

    /**
     * Display a listing of the resource.
     */
    public function showTFA(User $user, Request $request): Response
    {
        $seo = new SeoGeneratorService();

        $qr_code = null;
        $user = auth()->user();
        $secretKey = '';

        if (! $user->google2fa_status) {
            $google2fa = app('pragmarx.google2fa');
            $secretKey = $google2fa->generateSecretKey();

            $user->update(['google2fa_secret' => $secretKey]);

            $qr_code = $google2fa->getQRCodeInline(
                settings()->group('general')->get('site_name', false),
                $user->email,
                $user->google2fa_secret
            );
        }

        return Inertia::render('User/Settings/TwoFactorAuth', [
            'user' => UserProfileResource::make($user),
            'qr_code' => $qr_code,
            'secretKey' => $secretKey,
        ])->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    /**
     * Activate Two factor authentication.
     */
    public function activateTFA(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'otp_code' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toast_error($error);
            }

            return back();
        }

        $user = auth()->user();

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp_code);

        if ($valid == false) {
            toast_error(__('Invalid OTP code'));

            return back();
        }

        $update2FaStatus = $user->update(['google2fa_status' => true]);

        if ($update2FaStatus) {
            session()->put('user_2fa', hash_encode($user->id));
            toast_success(__('Two factor authentication has been activated'));
        }

        return back();
    }

    /**
     * Deactivate Two factor authentication.
     */
    public function deactivateTFA(Request $request, User $user): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'otp_code' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toast_error($error);
            }

            return back();
        }

        $user = auth()->user();

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp_code);

        if ($valid == false) {
            toast_error(__('Invalid OTP code'));

            return back();
        }

        $update2FaStatus = $user->update(['google2fa_status' => false]);

        if ($update2FaStatus) {
            if ($request->session()->has('user_2fa')) {
                session()->forget('user_2fa');
            }

            toast_success(__('Two factor authentication has been disabled'));
        }

        return back();
    }

    public function showNotificationsSettings(User $user): Response
    {
        $seo = new SeoGeneratorService();

        $userNotificationSettings = User::where('id', $user->id)->first('notify_settings');

        return Inertia::render('User/Settings/Notifications', [
            'user' => UserProfileResource::make($user),
            'userNotificationSettings' => $userNotificationSettings,
        ])->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function updateNotificationsSettings(Request $request, User $user): RedirectResponse
    {
        $user->notify_settings = [
            'new_comments' => $request->new_comments,
            'replies_comments' => $request->replies_comments,
            'liked' => $request->liked,
            'new_follower' => $request->new_follower,
            'mentions' => $request->mentions,
        ];
        $user->save();

        toast_success(__('Updated successfully'));

        return back();
    }

    public function showPreferenceSettings(User $user): Response
    {
        $seo = new SeoGeneratorService();

        $userPreferenceSettings = User::where('id', $user->id)->first('preference_settings');

        return Inertia::render('User/Settings/Preference', [
            'user' => UserProfileResource::make($user),
            'userPreferenceSettings' => $userPreferenceSettings,
        ])->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function updatePreferenceSettings(Request $request, User $user): RedirectResponse
    {
        $user->preference_settings = [
            'show_nsfw' => $request->show_nsfw,
            'blur_nsfw' => $request->blur_nsfw,
            'open_posts_new_tab' => $request->open_posts_new_tab,
        ];
        $user->save();

        toast_success(__('Updated successfully'));

        return back();
    }

    // Display the user delete account form
    public function showDestroySettings(User $user): Response
    {
        $seo = new SeoGeneratorService();

        return Inertia::render('User/Settings/Delete', [
            'user' => UserProfileResource::make($user),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    // Delete the user's account.
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->hasRole('administrator')) {
            toast_warning(__('You cannot delete an administrator account'));

            return back();
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('feed.home');
    }
}

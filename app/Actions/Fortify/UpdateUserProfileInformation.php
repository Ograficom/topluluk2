<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\BadgePointService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Illuminate\Support\Facades\Storage;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function __construct(
        protected BadgePointService $badgePointService,
    ) {
    }

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'cover_photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:2048'],
            'profile_type' => ['nullable', 'string', Rule::in(['person', 'organization'])],
            'bio' => ['nullable', 'string', 'max:2000'],
            'social_x' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_whatsapp' => ['nullable', 'string', 'max:255'],
            'social_tiktok' => ['nullable', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'joined_at' => ['nullable', 'date'],
            'is_verified' => ['sometimes', 'boolean'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if (isset($input['cover_photo'])) {
            $this->updateCoverPhoto($user, $input['cover_photo']);
        }

        $profileData = [
            'name' => $input['name'],
            'email' => $input['email'],
            'profile_type' => $input['profile_type'] ?? $user->profile_type ?? 'person',
            'bio' => $input['bio'] ?? null,
            'social_x' => $input['social_x'] ?? null,
            'social_instagram' => $input['social_instagram'] ?? null,
            'social_whatsapp' => $input['social_whatsapp'] ?? null,
            'social_tiktok' => $input['social_tiktok'] ?? null,
            'social_facebook' => $input['social_facebook'] ?? null,
            'website_url' => $input['website_url'] ?? null,
            'joined_at' => $input['joined_at'] ?? $user->joined_at ?? $user->created_at,
            'is_verified' => (bool) ($input['is_verified'] ?? $user->is_verified),
        ];

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $profileData);
        } else {
            $user->forceFill($profileData)->save();
            $user->refresh();
            $this->badgePointService->awardProfileCompletion($user);
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'profile_type' => $input['profile_type'] ?? $user->profile_type ?? 'person',
            'bio' => $input['bio'] ?? null,
            'social_x' => $input['social_x'] ?? null,
            'social_instagram' => $input['social_instagram'] ?? null,
            'social_whatsapp' => $input['social_whatsapp'] ?? null,
            'social_tiktok' => $input['social_tiktok'] ?? null,
            'social_facebook' => $input['social_facebook'] ?? null,
            'website_url' => $input['website_url'] ?? null,
            'joined_at' => $input['joined_at'] ?? $user->joined_at ?? $user->created_at,
            'is_verified' => (bool) ($input['is_verified'] ?? $user->is_verified),
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
        $user->refresh();
        $this->badgePointService->awardProfileCompletion($user);
    }

    protected function updateCoverPhoto(User $user, mixed $file): void
    {
        $path = $file->store('cover-photos', 'public');

        if ($user->cover_photo_path) {
            Storage::disk('public')->delete($user->cover_photo_path);
        }

        $user->forceFill(['cover_photo_path' => $path])->save();
    }
}

<?php

namespace App\Services;

use App\Models\User;

class BadgePointService
{
    public function award(User $user, string $action, int $multiplier = 1): int
    {
        $points = $this->pointsFor($action) * max(1, $multiplier);

        if ($points <= 0) {
            return 0;
        }

        $user->increment('badge_points', $points);
        $user->refresh();
        app(BadgeAwardSyncService::class)->syncForUser($user);

        return $points;
    }

    public function awardProfileCompletion(User $user): int
    {
        if (!$this->hasCompletedProfile($user) || $user->profile_completed_rewarded_at !== null) {
            return 0;
        }

        $points = $this->pointsFor('profile_completed');
        if ($points <= 0) {
            return 0;
        }

        $user->forceFill([
            'badge_points' => (int) $user->badge_points + $points,
            'profile_completed_rewarded_at' => now(),
        ])->save();

        $user->refresh();
        app(BadgeAwardSyncService::class)->syncForUser($user);

        return $points;
    }

    public function pointsFor(string $action): int
    {
        return (int) config("badge_points.actions.{$action}", 0);
    }

    public function hasCompletedProfile(User $user): bool
    {
        if (blank($user->profile_photo_path) || blank($user->cover_photo_path)) {
            return false;
        }

        foreach ([
            'bio',
            'social_x',
            'social_instagram',
            'social_whatsapp',
            'social_tiktok',
            'social_facebook',
            'website_url',
            'joined_at',
        ] as $field) {
            if (blank($user->{$field})) {
                return false;
            }
        }

        return true;
    }
}

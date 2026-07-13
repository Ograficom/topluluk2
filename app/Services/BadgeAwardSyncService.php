<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class BadgeAwardSyncService
{
    public function syncForUser(User $user): void
    {
        if (!$user->exists || !Schema::hasTable('badge_user')) {
            return;
        }

        $points = max(0, (int) $user->badge_points);
        $eligibleBadges = Badge::query()
            ->active()
            ->eligibleForUser($user)
            ->where('min_points', '<=', $points)
            ->get(['id', 'min_points']);

        if ($eligibleBadges->isEmpty()) {
            return;
        }

        $existingBadgeIds = $user->awardedBadges()
            ->pluck('badges.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $timestamp = now();
        $attach = [];

        foreach ($eligibleBadges as $badge) {
            if (in_array((int) $badge->id, $existingBadgeIds, true)) {
                continue;
            }

            $attach[$badge->id] = [
                'awarded_points' => $points,
                'awarded_at' => $timestamp,
            ];
        }

        if ($attach !== []) {
            $user->awardedBadges()->syncWithoutDetaching($attach);
        }
    }

    public function syncForBadge(Badge $badge): void
    {
        if (!$badge->exists || !$badge->is_active || !Schema::hasTable('badge_user')) {
            return;
        }

        User::query()
            ->when(filled($badge->eligible_profile_type ?? null), fn ($query) => $query->where('profile_type', $badge->eligible_profile_type))
            ->when((bool) ($badge->requires_verified ?? false), fn ($query) => $query->where('is_verified', true))
            ->where('badge_points', '>=', (int) $badge->min_points)
            ->select(['id', 'badge_points'])
            ->chunkById(200, function ($users): void {
                foreach ($users as $user) {
                    $this->syncForUser($user);
                }
            });
    }
}

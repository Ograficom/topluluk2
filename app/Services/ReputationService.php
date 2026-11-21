<?php

namespace App\Services;

use App\Models\ReputationSetting;
use App\Models\ReputationTransaction;
use App\Models\User;

class ReputationService
{
    public const POST_PUBLISHED = 10;
    public const COMMENT_PUBLISHED = 2;
    public const POST_LIKED = 3;
    public const COMMENT_LIKED = 2;

    public function award(User $user, int $points, string $eventKey, string $description, ?string $sourceType = null, ?int $sourceId = null): void
    {
        $settings = ReputationSetting::current();
        if (! $settings->enabled || ($settings->exclude_company_accounts && $user->is_company)) {
            return;
        }

        $points = match (true) {
            str_starts_with($eventKey, 'post:') => (int) $settings->post_points,
            str_starts_with($eventKey, 'comment:') => (int) $settings->comment_points,
            str_starts_with($eventKey, 'post-like:') => (int) $settings->post_like_points,
            str_starts_with($eventKey, 'comment-like:') => (int) $settings->comment_like_points,
            default => $points,
        };

        if ($points <= 0) {
            return;
        }

        ReputationTransaction::query()->firstOrCreate(
            ['event_key' => $eventKey],
            ['user_id' => $user->id, 'points' => $points, 'description' => $description, 'source_type' => $sourceType, 'source_id' => $sourceId],
        );
    }

    public function revoke(User $user, int $points, string $eventKey, string $description, ?string $sourceType = null, ?int $sourceId = null): void
    {
        $original = ReputationTransaction::query()->where('event_key', $eventKey)->first();
        if (! $original || $original->user_id !== $user->id) {
            return;
        }

        ReputationTransaction::query()->firstOrCreate(
            ['event_key' => 'revoke:'.$eventKey],
            ['user_id' => $user->id, 'points' => -abs((int) $original->points), 'description' => $description, 'source_type' => $sourceType, 'source_id' => $sourceId],
        );
    }

    public function score(User $user): int
    {
        if (ReputationSetting::current()->exclude_company_accounts && $user->is_company) {
            return 0;
        }

        return (int) $user->reputationTransactions()->sum('points');
    }

    public function badgeFor(int $score): array
    {
        $customBadge = collect(ReputationSetting::current()->reward_badges ?? [])
            ->filter(fn (array $badge): bool => filled($badge['name'] ?? null) && (int) ($badge['required_points'] ?? 0) <= $score)
            ->sortByDesc(fn (array $badge): int => (int) ($badge['required_points'] ?? 0))
            ->first();

        if ($customBadge) {
            return [
                'name' => $customBadge['name'],
                'next' => null,
                'minimum' => (int) ($customBadge['required_points'] ?? 0),
                'description' => $customBadge['description'] ?? null,
                'icon' => $customBadge['icon'] ?? 'trophy',
                'custom' => true,
            ];
        }

        return match (true) {
            $score >= 1000 => ['name' => 'Efsane', 'next' => null, 'minimum' => 1000],
            $score >= 500 => ['name' => 'Usta', 'next' => 1000, 'minimum' => 500],
            $score >= 200 => ['name' => 'Uzman', 'next' => 500, 'minimum' => 200],
            $score >= 50 => ['name' => 'Katılımcı', 'next' => 200, 'minimum' => 50],
            default => ['name' => 'Yeni Üye', 'next' => 50, 'minimum' => 0],
        };
    }
}

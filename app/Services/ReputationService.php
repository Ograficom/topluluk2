<?php

namespace App\Services;

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
            ['user_id' => $user->id, 'points' => -$points, 'description' => $description, 'source_type' => $sourceType, 'source_id' => $sourceId],
        );
    }

    public function score(User $user): int
    {
        return (int) $user->reputationTransactions()->sum('points');
    }

    public function badgeFor(int $score): array
    {
        return match (true) {
            $score >= 1000 => ['name' => 'Efsane', 'next' => null, 'minimum' => 1000],
            $score >= 500 => ['name' => 'Usta', 'next' => 1000, 'minimum' => 500],
            $score >= 200 => ['name' => 'Uzman', 'next' => 500, 'minimum' => 200],
            $score >= 50 => ['name' => 'Katılımcı', 'next' => 200, 'minimum' => 50],
            default => ['name' => 'Yeni Üye', 'next' => 50, 'minimum' => 0],
        };
    }
}

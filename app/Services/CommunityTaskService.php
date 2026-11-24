<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\ReputationSetting;
use App\Models\ReputationTransaction;
use App\Models\TaskCompletion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommunityTaskService
{
    public const ACTIVITIES = [
        'comments' => 'Yorum yap',
        'posts' => 'Yazı yayınla',
        'follow_members' => 'Üye takip et',
        'login_streak' => 'Ardışık günlerde giriş yap',
        'registration' => 'Üyelik oluştur',
        'profile_complete' => 'Profilini tamamla',
        'two_factor' => 'İki aşamalı doğrulamayı etkinleştir',
        'avatar_added' => 'Profil resmi ekle',
        'avatar_changed' => 'Profil resmini değiştir',
        'social_follow' => 'Sosyal medyada takip et (onaylı)',
        'event' => 'Etkinlik / yönetici onaylı görev',
    ];

    public function recordDay(User $user, string $activity): void
    {
        DB::table('task_activity_days')->insertOrIgnore([
            'user_id' => $user->id,
            'activity' => $activity,
            'day' => now('Europe/Istanbul')->toDateString(),
            'created_at' => now(),
        ]);
    }

    public function observeUser(User $user, ?string $previousAvatar): void
    {
        $this->recordDay($user, 'login_streak');
        if (filled($user->avatar_path)) {
            $this->recordDay($user, 'avatar_added');
            if (filled($previousAvatar) && $previousAvatar !== $user->avatar_path) {
                $this->recordDay($user, 'avatar_changed');
            }
        }
        if (filled($user->name) && filled($user->username) && filled($user->avatar_path) && filled($user->bio)) {
            $this->recordDay($user, 'profile_complete');
        }
        if ($user->two_factor_confirmed_at) {
            $this->recordDay($user, 'two_factor');
        }
        $this->completeAvailableTasks($user);
    }

    private function activityProgress(User $user, array $task): int
    {
        $days = DB::table('task_activity_days')->where('user_id', $user->id)
            ->where('activity', $task['activity'])
            ->when(filled($task['starts_at'] ?? null), fn ($query) => $query->where('created_at', '>=', $task['starts_at']))
            ->when(filled($task['ends_at'] ?? null), fn ($query) => $query->where('created_at', '<=', $task['ends_at']))
            ->orderBy('day')->pluck('day');
        if ($task['activity'] !== 'login_streak') {
            return $days->isNotEmpty() ? 1 : 0;
        }
        $best = 0;
        $streak = 0;
        $previous = null;
        foreach ($days as $day) {
            $date = \Illuminate\Support\Carbon::parse($day);
            $streak = $previous && $previous->copy()->addDay()->equalTo($date) ? $streak + 1 : 1;
            $best = max($best, $streak);
            $previous = $date;
        }
        return $best;
    }

    public function tasks(): array
    {
        return collect(ReputationSetting::current()->tasks ?? [])
            ->filter(fn (array $task): bool => ($task['active'] ?? true) && filled($task['title'] ?? null))
            ->values()
            ->all();
    }

    public function progress(User $user, array $task): int
    {
        if (filled($task['starts_at'] ?? null) && now()->lt($task['starts_at'])) {
            return 0;
        }
        return match ($task['activity'] ?? 'comments') {
            'follow_members' => $user->following()
                ->where('users.id', '!=', $user->id)
                ->when(filled($task['starts_at'] ?? null), fn ($query) => $query->where('user_follows.created_at', '>=', $task['starts_at']))
                ->when(filled($task['ends_at'] ?? null), fn ($query) => $query->where('user_follows.created_at', '<=', $task['ends_at']))->count(),
            'registration' => $user->created_at
                && (! filled($task['starts_at'] ?? null) || $user->created_at->gte($task['starts_at']))
                && (! filled($task['ends_at'] ?? null) || $user->created_at->lte($task['ends_at'])) ? 1 : 0,
            'login_streak', 'profile_complete', 'two_factor', 'avatar_added', 'avatar_changed' => $this->activityProgress($user, $task),
            'event', 'social_follow' => (! filled($task['ends_at'] ?? null) || now()->lte($task['ends_at'])) && in_array((string) $user->id, array_map('strval', $task['approved_users'] ?? []), true) ? 1 : 0,
            'posts' => Post::query()->where('user_id', $user->id)->published()
                ->whereNull('advertisement_id')
                ->when(filled($task['starts_at'] ?? null), fn ($query) => $query->where('created_at', '>=', $task['starts_at']))
                ->when(filled($task['ends_at'] ?? null), fn ($query) => $query->where('created_at', '<=', $task['ends_at']))
                ->count(),
            'comments' => Comment::query()->where('user_id', $user->id)
                ->where('is_spam', false)
                ->when(filled($task['starts_at'] ?? null), fn ($query) => $query->where('created_at', '>=', $task['starts_at']))
                ->when(filled($task['ends_at'] ?? null), fn ($query) => $query->where('created_at', '<=', $task['ends_at']))
                ->count(),
            default => 0,
        };
    }

    public function completeAvailableTasks(User $user): void
    {
        $settings = ReputationSetting::current();
        if (! $settings->enabled || ($settings->exclude_company_accounts && $user->is_company)) {
            return;
        }

        foreach ($this->tasks() as $index => $task) {
            $target = max(1, (int) ($task['target_count'] ?? 1));
            if ($this->progress($user, $task) < $target) {
                continue;
            }

            $key = (string) ($task['key'] ?? Str::slug(($task['title'] ?? 'gorev').'-'.$index));
            $selectedBadge = collect($settings->reward_badges ?? [])
                ->first(fn (array $badge): bool => filled($task['reward_badge'] ?? null)
                    && ($badge['name'] ?? null) === $task['reward_badge']);

            DB::transaction(function () use ($user, $task, $key, $selectedBadge): void {
                User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
                $completion = TaskCompletion::query()->firstOrCreate(
                    ['user_id' => $user->id, 'task_key' => $key],
                    [
                        'task_name' => (string) $task['title'],
                        'reward_points' => max(0, (int) ($task['reward_points'] ?? 0)),
                        'badge' => $selectedBadge,
                        'completed_at' => now(),
                    ],
                );

                if (! $completion->wasRecentlyCreated) {
                    return;
                }

                if ($completion->reward_points > 0) {
                    ReputationTransaction::query()->firstOrCreate(
                        ['event_key' => 'task:'.$key.':user:'.$user->id],
                        [
                            'user_id' => $user->id,
                            'points' => $completion->reward_points,
                            'description' => 'Görev tamamlandı: '.$completion->task_name,
                            'source_type' => TaskCompletion::class,
                            'source_id' => $completion->id,
                        ],
                    );
                }
            });
        }
    }
}
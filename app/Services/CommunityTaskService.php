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
    public function tasks(): array
    {
        return collect(ReputationSetting::current()->tasks ?? [])
            ->filter(fn (array $task): bool => ($task['active'] ?? true) && filled($task['title'] ?? null))
            ->values()
            ->all();
    }

    public function progress(User $user, array $task): int
    {
        return match ($task['activity'] ?? 'comments') {
            'posts' => Post::query()->where('user_id', $user->id)->published()
                ->whereNull('advertisement_id')
                ->when(filled($task['starts_at'] ?? null), fn ($query) => $query->where('created_at', '>=', $task['starts_at']))
                ->count(),
            default => Comment::query()->where('user_id', $user->id)
                ->where('is_spam', false)
                ->when(filled($task['starts_at'] ?? null), fn ($query) => $query->where('created_at', '>=', $task['starts_at']))
                ->count(),
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
<?php

namespace App\Http\Controllers;

use App\Models\ReputationSetting;
use App\Models\TaskCompletion;
use App\Models\TaskParticipation;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Services\CommunityTaskService;
use App\Services\ReputationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function submit(Request $request, string $taskKey, CommunityTaskService $taskService): RedirectResponse
    {
        $settings = ReputationSetting::current();
        $user = $request->user();
        abort_unless($settings->enabled && ! ($settings->exclude_company_accounts && $user->is_company), 403);
        $task = collect($taskService->tasks())->first(fn (array $item): bool => ($item['key'] ?? '') === $taskKey);
        abort_unless($task && in_array($task['activity'] ?? '', ['event', 'social_follow'], true), 404);
        abort_if(filled($task['starts_at'] ?? null) && now()->lt($task['starts_at']), 422);
        abort_if(filled($task['ends_at'] ?? null) && now()->gt($task['ends_at']), 422);
        $validated = $request->validate([
            'external_username' => [$task['activity'] === 'social_follow' ? 'required' : 'nullable', 'string', 'max:100'],
        ]);
        if (TaskCompletion::query()->where('user_id', $user->id)->where('task_key', $taskKey)->exists()) {
            return back()->with('task_status', 'Bu görevi zaten tamamladın.');
        }
        $participation = TaskParticipation::query()->firstOrCreate(
            ['user_id' => $user->id, 'task_key' => $taskKey],
            ['task_name' => $task['title'], 'external_username' => $validated['external_username'] ?? null, 'status' => 'pending']
        );
        return back()->with('task_status', $participation->wasRecentlyCreated
            ? 'Başvurun yöneticiye iletildi.'
            : 'Bu görev için daha önce başvurdun.');
    }

    public function index(Request $request, CommunityTaskService $taskService, ReputationService $reputationService): View
    {
        $viewer = $request->user();
        $settings = ReputationSetting::current();
        if ($viewer) {
            $taskService->completeAvailableTasks($viewer);
        }
        $period = in_array($request->query('period'), ['week', 'month'], true) ? $request->query('period') : 'all';
        $since = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };
        $participations = $viewer
            ? TaskParticipation::query()->where('user_id', $viewer->id)->get()->keyBy('task_key')
            : collect();
        $tasks = collect($taskService->tasks())->map(function (array $task) use ($viewer, $taskService, $participations): array {
            $progress = $viewer ? $taskService->progress($viewer, $task) : 0;
            $target = max(1, (int) ($task['target_count'] ?? 1));
            $key = (string) ($task['key'] ?? '');
            $completed = $viewer && $key !== ''
                ? TaskCompletion::query()->where('user_id', $viewer->id)->where('task_key', $key)->exists()
                : false;

            return [
                'task' => $task,
                'participation' => $participations->get($key),
                'progress' => min($progress, $target),
                'target' => $target,
                'completed' => $completed,
                'percent' => min(100, (int) floor(($progress / $target) * 100)),
            ];
        });

        $leaderboard = User::query()
            ->select('users.*')
            ->withSum(['reputationTransactions as reputation_score' => fn ($query) => $query->when($since, fn ($query) => $query->where('created_at', '>=', $since))], 'points')
            ->when($settings->exclude_company_accounts, fn (Builder $query) => $query->where('is_company', false))
            ->when($viewer, fn (Builder $query) => $query
                ->whereNotIn('users.id', $viewer->blockedUsers()->select('users.id'))
                ->whereNotIn('users.id', $viewer->blockedByUsers()->select('users.id')))
            ->orderByDesc('reputation_score')
            ->orderBy('users.id')
            ->limit(50)
            ->get();

        $myCompletions = $viewer
            ? TaskCompletion::query()->where('user_id', $viewer->id)->latest('completed_at')->get()
            : collect();

        return view('tasks.index', [
            'tasks' => $tasks,
            'period' => $period,
            'leaderboard' => $leaderboard,
            'myCompletions' => $myCompletions,
            'myScore' => $viewer ? $reputationService->score($viewer) : 0,
            'myBadge' => $viewer ? $reputationService->badgeFor($reputationService->score($viewer)) : null,
        ]);
    }
}
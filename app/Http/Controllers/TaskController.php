<?php

namespace App\Http\Controllers;

use App\Models\ReputationSetting;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\CommunityTaskService;
use App\Services\ReputationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
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
        $tasks = collect($taskService->tasks())->map(function (array $task) use ($viewer, $taskService): array {
            $progress = $viewer ? $taskService->progress($viewer, $task) : 0;
            $target = max(1, (int) ($task['target_count'] ?? 1));
            $key = (string) ($task['key'] ?? '');
            $completed = $viewer && $key !== ''
                ? TaskCompletion::query()->where('user_id', $viewer->id)->where('task_key', $key)->exists()
                : false;

            return [
                'task' => $task,
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
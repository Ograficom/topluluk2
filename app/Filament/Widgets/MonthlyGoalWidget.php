<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use LaBoiteACode\FilamentDashboardWidgets\Data\Goal;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\GoalProgressWidget;

class MonthlyGoalWidget extends GoalProgressWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected function getGoal(): Goal
    {
        $current = Post::query()
            ->published()
            ->whereYear('published_at', now()->year)
            ->whereMonth('published_at', now()->month)
            ->count();

        $lastMonthCount = Post::query()
            ->published()
            ->whereYear('published_at', now()->subMonthNoOverflow()->year)
            ->whereMonth('published_at', now()->subMonthNoOverflow()->month)
            ->count();

        $target = max(20, (int) ceil($lastMonthCount * 1.1));

        return Goal::make('Bu Ayın Yayın Hedefi', $current, $target)
            ->description('Geçen ay: ' . $lastMonthCount . ' gönderi')
            ->icon('heroicon-o-flag')
            ->color('primary')
            ->deadline(now()->endOfMonth())
            ->showRemaining();
    }
}

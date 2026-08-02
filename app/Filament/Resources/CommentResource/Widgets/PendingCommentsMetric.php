<?php

namespace App\Filament\Resources\CommentResource\Widgets;

use App\Models\Comment;
use LaBoiteACode\FilamentDashboardWidgets\Data\Metric;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\MetricWidget;

class PendingCommentsMetric extends MetricWidget
{
    protected int|string|array $columnSpan = 1;

    protected function getMetric(): Metric
    {
        $pending = Comment::query()->where('is_approved', false)->count();

        $currentPeriod = Comment::query()
            ->where('is_approved', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $previousPeriod = Comment::query()
            ->where('is_approved', false)
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->count();

        $trend = $previousPeriod > 0
            ? (($currentPeriod - $previousPeriod) / $previousPeriod)
            : ($currentPeriod > 0 ? 1.0 : 0.0);

        $sparkline = collect(range(13, 0))
            ->map(fn (int $daysAgo) => Comment::query()
                ->where('is_approved', false)
                ->whereBetween('created_at', [
                    now()->subDays($daysAgo)->startOfDay(),
                    now()->subDays($daysAgo)->endOfDay(),
                ])
                ->count())
            ->all();

        return Metric::make('Onay Bekleyen Yorumlar', $pending)
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color($pending > 0 ? 'warning' : 'success')
            ->trend($trend)
            ->lowerIsBetter()
            ->sparkline($sparkline);
    }
}

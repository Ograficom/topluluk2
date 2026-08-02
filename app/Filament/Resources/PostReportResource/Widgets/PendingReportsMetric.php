<?php

namespace App\Filament\Resources\PostReportResource\Widgets;

use App\Models\PostReport;
use LaBoiteACode\FilamentDashboardWidgets\Data\Metric;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\MetricWidget;

class PendingReportsMetric extends MetricWidget
{
    protected int|string|array $columnSpan = 1;

    protected function getMetric(): Metric
    {
        $pending = PostReport::query()->where('status', 'pending')->count();

        $currentPeriod = PostReport::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $previousPeriod = PostReport::query()
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->count();

        $trend = $previousPeriod > 0
            ? (($currentPeriod - $previousPeriod) / $previousPeriod)
            : ($currentPeriod > 0 ? 1.0 : 0.0);

        $sparkline = collect(range(13, 0))
            ->map(fn (int $daysAgo) => PostReport::query()
                ->whereBetween('created_at', [
                    now()->subDays($daysAgo)->startOfDay(),
                    now()->subDays($daysAgo)->endOfDay(),
                ])
                ->count())
            ->all();

        return Metric::make('Bekleyen Şikayetler', $pending)
            ->icon('heroicon-o-flag')
            ->color($pending > 0 ? 'danger' : 'success')
            ->trend($trend)
            ->lowerIsBetter()
            ->sparkline($sparkline);
    }
}

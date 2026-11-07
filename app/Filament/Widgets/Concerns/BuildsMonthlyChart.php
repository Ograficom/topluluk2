<?php

namespace App\Filament\Widgets\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

trait BuildsMonthlyChart
{
    protected function monthlyChartData(string $model, string $label, string $color): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $end = now()->endOfMonth();

        $counts = $model::query()
            ->whereBetween('created_at', [$start, $end])
            ->pluck('created_at')
            ->map(fn ($createdAt): string => Carbon::parse($createdAt)->format('Y-m'))
            ->countBy();

        $months = collect(range(0, 11));

        return [
            'datasets' => [[
                'label' => $label,
                'data' => $months->map(fn (int $month): int => (int) ($counts[$start->copy()->addMonths($month)->format('Y-m')] ?? 0))->all(),
                'borderColor' => $color,
                'backgroundColor' => $color,
                'tension' => 0.35,
                'fill' => false,
            ]],
            'labels' => $months->map(fn (int $month): string => $start->copy()->addMonths($month)->translatedFormat('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}

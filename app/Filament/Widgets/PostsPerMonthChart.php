<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;

class PostsPerMonthChart extends ChartWidget
{
    protected ?string $heading = 'Aylık Gönderiler';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo));

        $counts = $months->map(function ($month) {
            return Post::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Gönderiler',
                    'data' => $counts->all(),
                    'backgroundColor' => '#f97316',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->translatedFormat('M'))->all(),
        ];
    }

    protected function getOptions(): array | \Filament\Support\RawJs | null
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}

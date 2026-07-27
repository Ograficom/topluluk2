<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class UsersGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Kullanıcı Büyümesi';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo));

        $counts = $months->map(function ($month) {
            return User::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Yeni Kullanıcılar',
                    'data' => $counts->all(),
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
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

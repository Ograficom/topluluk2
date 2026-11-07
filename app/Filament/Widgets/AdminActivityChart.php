<?php

namespace App\Filament\Widgets;

use App\Models\Channel;
use App\Models\Post;
use App\Models\SupportTicket;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AdminActivityChart extends ChartWidget
{
    protected ?string $heading = 'OgraFi büyüme grafiği';

    protected ?string $description = 'Son 12 ayda oluşturulan yazı, kanal, üye ve iletişim talepleri.';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $end = now()->endOfMonth();

        $labels = collect(range(0, 11))
            ->map(fn (int $month): string => $start->copy()->addMonths($month)->translatedFormat('M Y'));

        $models = [
            'Yazılar' => [Post::class, '#f97316'],
            'Kanallar' => [Channel::class, '#16a34a'],
            'Üyeler' => [User::class, '#2563eb'],
            'İletişim formları' => [SupportTicket::class, '#a855f7'],
        ];

        $datasets = [];

        foreach ($models as $label => [$model, $color]) {
            $counts = $model::query()
                ->whereBetween('created_at', [$start, $end])
                ->pluck('created_at')
                ->map(fn ($createdAt) => Carbon::parse($createdAt)->format('Y-m'))
                ->countBy();

            $datasets[] = [
                'label' => $label,
                'data' => collect(range(0, 11))
                    ->map(fn (int $month): int => (int) ($counts[$start->copy()->addMonths($month)->format('Y-m')] ?? 0))
                    ->all(),
                'borderColor' => $color,
                'backgroundColor' => $color,
                'tension' => 0.35,
                'fill' => false,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels->all(),
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
            'interaction' => ['intersect' => false, 'mode' => 'index'],
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}

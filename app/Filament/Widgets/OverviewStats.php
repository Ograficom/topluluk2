<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Gsferro\FilamentStatPlusEasy\Widgets\StatPlus;

class OverviewStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            $this->buildStat('Gönderiler', Post::query(), 'heroicon-m-document-text'),
            $this->buildStat('Kullanıcılar', User::query(), 'heroicon-m-users'),
            $this->buildStat('Reaksiyonlar (emoji)', Reaction::query(), 'heroicon-m-face-smile'),
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    private function buildStat(string $label, $query, string $icon): StatPlus
    {
        $total = (clone $query)->count();

        $currentPeriod = (clone $query)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $previousPeriod = (clone $query)
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->count();

        $change = $previousPeriod > 0
            ? round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 1)
            : ($currentPeriod > 0 ? 100.0 : 0.0);

        $chart = collect(range(6, 0))
            ->map(fn (int $daysAgo) => (clone $query)
                ->whereBetween('created_at', [
                    now()->subDays($daysAgo)->startOfDay(),
                    now()->subDays($daysAgo)->endOfDay(),
                ])
                ->count())
            ->all();

        $isUp = $change >= 0;

        return StatPlus::make($label, number_format($total))
            ->description(($isUp ? '+' : '') . $change . '% (son 7 gün)')
            ->descriptionIcon($isUp ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->descriptionColor($isUp ? 'success' : 'danger')
            ->color($isUp ? 'success' : 'danger')
            ->icon($icon)
            ->chart($chart);
    }
}

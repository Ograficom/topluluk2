<?php

namespace App\Filament\Resources\PostResource\Widgets;

use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Post::query()->count();
        $published = Post::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->count();
        $drafts = Post::query()->where('is_published', false)->count();
        $scheduled = Post::query()
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '>', now())
            ->count();
        $averageViews = (int) round(Post::query()->avg('views_count') ?? 0);

        $dailyCounts = collect(range(13, 0))
            ->map(fn (int $daysAgo) => Post::query()
                ->whereBetween('created_at', [
                    now()->subDays($daysAgo)->startOfDay(),
                    now()->subDays($daysAgo)->endOfDay(),
                ])
                ->count())
            ->all();

        return [
            Stat::make('Gönderiler', number_format($total))
                ->description('Tüm kayıtlar')
                ->chart($dailyCounts)
                ->color('gray'),

            Stat::make('Yayınlanan', number_format($published))
                ->description($total > 0 ? round(($published / $total) * 100) . '% yayında' : '0% yayında')
                ->color('success'),

            Stat::make('Taslaklar', number_format($drafts))
                ->description($scheduled > 0 ? number_format($scheduled) . ' zamanlanmış gönderi' : 'Yayınlanmamış gönderiler')
                ->color('warning'),

            Stat::make('Ortalama Görüntülenme', number_format($averageViews))
                ->color('info'),
        ];
    }
}

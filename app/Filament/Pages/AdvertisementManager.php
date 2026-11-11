<?php

namespace App\Filament\Pages;

use App\Models\Advertisement;
use App\Models\AdvertisementEvent;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;

class AdvertisementManager extends Page
{
    protected static ?string $slug = 'advertisement-manager';

    protected static ?string $navigationLabel = 'Reklam yöneticisi';

    protected static ?string $title = 'Reklam yöneticisi';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 9998;

    protected string $view = 'filament.pages.advertisement-manager';

    protected function getViewData(): array
    {
        $publishedAds = Advertisement::query()
            ->where('status', 'approved')
            ->with(['user:id,name'])
            ->latest('approved_at');

        if (Schema::hasTable('advertisement_events')) {
            $publishedAds->withCount([
                'events as detail_views_count' => fn ($query) => $query->where('event_type', 'detail'),
                'events as card_clicks_count' => fn ($query) => $query->where('event_type', 'card_click'),
                'events as reads_count' => fn ($query) => $query->where('event_type', 'read'),
            ]);
        }

        $ads = $publishedAds->get();

        $totals = [
            'ads' => $ads->count(),
            'active' => $ads->filter(fn ($ad) => $ad->starts_at && $ad->ends_at && $ad->starts_at->lte(now()) && $ad->ends_at->gt(now()))->count(),
            'impressions' => (int) $ads->sum('impressions'),
            'clicks' => (int) $ads->sum('clicks') + (int) $ads->sum('card_clicks_count'),
            'detail_views' => (int) $ads->sum('detail_views_count'),
            'reads' => (int) $ads->sum('reads_count'),
            'spent' => (float) $ads->sum('spent_amount'),
        ];
        $totals['ctr'] = $totals['impressions'] > 0
            ? round(($totals['clicks'] / $totals['impressions']) * 100, 2)
            : 0;

        $events = collect();
        if (Schema::hasTable('advertisement_events')) {
            $events = AdvertisementEvent::query()
                ->join('advertisements', 'advertisements.id', '=', 'advertisement_events.advertisement_id')
                ->where('advertisements.status', 'approved')
                ->where('advertisement_events.created_at', '>=', now()->subDays(13)->startOfDay())
                ->whereIn('advertisement_events.event_type', ['impression', 'click', 'card_click'])
                ->selectRaw('DATE(advertisement_events.created_at) as event_date, advertisement_events.event_type, COUNT(*) as total')
                ->groupBy('event_date', 'advertisement_events.event_type')
                ->get();
        }

        $daily = collect(range(13, 0))->map(function (int $daysAgo) use ($events): array {
            $date = now()->subDays($daysAgo);

            return [
                'date' => $date->toDateString(),
                'label' => $date->translatedFormat('d M'),
                'impressions' => (int) $events->where('event_date', $date->toDateString())->where('event_type', 'impression')->sum('total'),
                'clicks' => (int) $events->where('event_date', $date->toDateString())->whereIn('event_type', ['click', 'card_click'])->sum('total'),
            ];
        })->values();

        $maxDaily = max(1, (int) $daily->max(fn ($day) => max($day['impressions'], $day['clicks'])));

        return compact('ads', 'totals', 'daily', 'maxDaily');
    }
}

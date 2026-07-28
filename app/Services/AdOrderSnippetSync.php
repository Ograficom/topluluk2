<?php

namespace App\Services;

use App\Models\AdOrder;
use App\Models\Snippet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * "Reklam ver" formundan gelip Filament'ta onaylanan (status=active) siparişleri,
 * ilgili yerleşimin Snippet kaydına yazar - böylece bir sipariş aktif olduğu anda
 * sitede otomatik yayınlanır, admin ayrıca elle HTML kopyalamak zorunda kalmaz.
 * Süresi geçmiş siparişleri de "expired" olarak işaretler.
 */
class AdOrderSnippetSync
{
    /**
     * AdOrder->placement değerini Snippet->key değerine eşler.
     */
    public const PLACEMENT_TO_SNIPPET_KEY = [
        'sidebar_top' => 'ads_sidebar_top',
        'feed_inline' => 'ads_feed_inline',
        'mobile_inline' => 'ads_mobile_inline',
    ];

    public function syncPlacement(string $placement): void
    {
        $snippetKey = self::PLACEMENT_TO_SNIPPET_KEY[$placement] ?? null;
        if (!$snippetKey) {
            return;
        }

        $orders = AdOrder::query()
            ->where('placement', $placement)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderByDesc('created_at')
            ->get();

        $html = trim(implode("\n", $orders->map(fn (AdOrder $order) => $this->renderOrderHtml($order))->all()));

        Snippet::query()->updateOrCreate(
            ['key' => $snippetKey],
            [
                'title' => Snippet::query()->where('key', $snippetKey)->value('title')
                    ?? ('Reklam - ' . $placement . ' (reklam ver otomatik)'),
                'content' => $html,
                'is_active' => $html !== '',
            ]
        );
    }

    public function syncAll(): void
    {
        $this->expireOverdueOrders();

        foreach (array_keys(self::PLACEMENT_TO_SNIPPET_KEY) as $placement) {
            $this->syncPlacement($placement);
        }
    }

    private function expireOverdueOrders(): void
    {
        AdOrder::query()
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);
    }

    private function renderOrderHtml(AdOrder $order): string
    {
        $imageUrl = $order->image_path ? Storage::disk('public')->url($order->image_path) : null;
        if (!$imageUrl) {
            return '';
        }

        $alt = e((string) ($order->title ?: 'Reklam'));

        // Onemli: width/height HTML ozniteligi BILEREK eklenmiyor. Siparisin
        // width/height alanlari (formda secilen yerlesimin nominal boyutu)
        // yuklenen gorselin GERCEK piksel oranindan farkli olabiliyor - bu
        // durumda tarayici, resim yuklenmeden once yanlis bir alan ayirip
        // resim yuklendikten sonra da bosluk birakiyordu (kutu "sarkiyor"
        // gibi gorunuyordu). width:100%;height:auto ile tarayici resmin
        // GERCEK oranini kullaniyor, hicbir zaman yanlis olmuyor.
        $img = sprintf(
            '<img src="%s" alt="%s" style="display:block;width:100%%;height:auto;border-radius:12px;">',
            e($imageUrl),
            $alt
        );

        $targetUrl = trim((string) ($order->target_url ?? ''));
        if ($targetUrl === '' || !Str::startsWith($targetUrl, ['http://', 'https://'])) {
            return $img;
        }

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener sponsored" style="display:block;">%s</a>',
            e($targetUrl),
            $img
        );
    }
}

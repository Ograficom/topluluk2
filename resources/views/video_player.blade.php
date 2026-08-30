@extends('layouts.app')

@section('title', 'Canlı TV')
@section('hide_feed_header')
@endsection

@section('content')
@php
    $channels = collect();

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('live_channels')) {
            $channels = \App\Models\LiveChannel::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (\App\Models\LiveChannel $channel) => [
                    'id' => $channel->id,
                    'name' => $channel->name,
                    'category' => $channel->category ?: 'Genel',
                    'url' => $channel->stream_url,
                    'image' => $channel->featured_image_url,
                    'type' => str_contains(strtolower((string) parse_url($channel->stream_url, PHP_URL_PATH)), '.m3u8') ? 'hls' : 'video',
                ])
                ->values();
        }
    } catch (\Throwable $exception) {
        $channels = collect();
    }

    if ($channels->isEmpty()) {
        $channels = collect(
            app(\App\Services\LiveChannelPlaylistService::class)
                ->parseFile(public_path('streams/turkiye.m3u'))
        )->map(fn (array $channel, int $index) => [
            'id' => 'm3u-' . ($index + 1),
            'name' => $channel['name'] ?? ('Kanal ' . ($index + 1)),
            'category' => $channel['category'] ?? 'Genel',
            'url' => $channel['stream_url'] ?? '',
            'image' => null,
            'type' => str_contains(strtolower((string) parse_url($channel['stream_url'] ?? '', PHP_URL_PATH)), '.m3u8') ? 'hls' : 'video',
        ])->filter(fn (array $channel) => filled($channel['url']))->values();
    }

    $categories = collect(['Tümü'])
        ->concat($channels->pluck('category')->filter()->unique()->sort()->values())
        ->values();

    $channelsJson = json_encode(
        $channels->values()->all(),
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
@endphp

<style>
    .video-tv-page {
        width: 100%;
        max-width: 1040px;
        margin: 0 auto;
        padding: 22px 14px 96px;
        color: #0f172a;
    }

    .video-tv-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 18px;
    }

    .video-tv-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -0.035em;
        color: #0f172a;
    }

    .video-tv-subtitle {
        margin: 7px 0 0;
        font-size: 14px;
        line-height: 1.45;
        color: #64748b;
    }

    .video-tv-summary {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .video-tv-summary-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 34px;
        padding: 0 11px;
        border-radius: 10px;
        background: #f1f5f9;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .video-tv-summary-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #22c55e;
        flex: 0 0 auto;
    }

    .video-tv-player-card {
        overflow: hidden;
        border-radius: 20px;
        background: #0b1120;
    }

    .video-tv-player-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        background: #050914;
    }

    .video-tv-player-wrap video {
        display: block;
        width: 100%;
        height: 100%;
        background: #050914;
        object-fit: contain;
    }

    .video-tv-player-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 9px;
        background: rgba(15, 23, 42, .92);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
    }

    .video-tv-player-badge::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #ef4444;
    }

    .video-tv-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(2, 6, 23, .58);
        color: #fff;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
    }

    .video-tv-error {
        background: rgba(2, 6, 23, .94);
    }

    .video-tv-error strong {
        display: block;
        margin-bottom: 7px;
        font-size: 18px;
        line-height: 1.2;
    }

    .video-tv-error span {
        display: block;
        max-width: 480px;
        color: #cbd5e1;
        font-size: 13px;
        line-height: 1.5;
        font-weight: 500;
    }

    .video-tv-now {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 15px 16px 16px;
        background: #0f172a;
    }

    .video-tv-now-copy {
        min-width: 0;
    }

    .video-tv-now-label {
        margin-bottom: 4px;
        color: #94a3b8;
        font-size: 10px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .video-tv-now-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #fff;
        font-size: 16px;
        line-height: 1.35;
        font-weight: 800;
    }

    .video-tv-now-meta {
        margin-top: 3px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.4;
    }

    .video-tv-next {
        flex: 0 0 auto;
        min-height: 38px;
        padding: 0 14px;
        border: 0;
        border-radius: 10px;
        background: #fff;
        color: #0f172a;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .video-tv-toolbar {
        margin-top: 22px;
        padding: 16px;
        border-radius: 18px;
        background: #f8fafc;
    }

    .video-tv-toolbar-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .video-tv-section-title {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .video-tv-section-count {
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .video-tv-search {
        position: relative;
        width: min(330px, 100%);
        margin-left: auto;
    }

    .video-tv-search svg {
        position: absolute;
        top: 50%;
        left: 12px;
        width: 17px;
        height: 17px;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }

    .video-tv-search input {
        width: 100%;
        height: 40px;
        padding: 0 12px 0 38px;
        border: 0;
        outline: 0;
        border-radius: 10px;
        background: #e2e8f0;
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
    }

    .video-tv-search input::placeholder {
        color: #64748b;
        font-weight: 500;
    }

    .video-tv-filters {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: none;
    }

    .video-tv-filters::-webkit-scrollbar {
        display: none;
    }

    .video-tv-filter {
        flex: 0 0 auto;
        min-height: 34px;
        padding: 0 12px;
        border: 0;
        border-radius: 9px;
        background: #e2e8f0;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .video-tv-filter.is-active {
        background: #0f172a;
        color: #fff;
    }

    .video-tv-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 24px 0 12px;
    }

    .video-tv-list-stats {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    .video-tv-list-stats-separator {
        color: #cbd5e1;
    }

    .video-tv-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .video-tv-card {
        min-width: 0;
        padding: 0;
        overflow: hidden;
        border: 0;
        border-radius: 16px;
        background: #f8fafc;
        color: inherit;
        text-align: left;
        cursor: pointer;
    }

    .video-tv-card.is-active {
        background: #e2e8f0;
    }

    .video-tv-card.is-bad {
        opacity: .52;
    }

    .video-tv-card:disabled {
        cursor: not-allowed;
    }

    .video-tv-thumb {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        background: #e2e8f0;
    }

    .video-tv-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .video-tv-thumb-fallback {
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        background: #dbe3ec;
        color: #334155;
        font-size: clamp(24px, 4vw, 36px);
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.05em;
    }

    .video-tv-live-tag {
        position: absolute;
        right: 8px;
        bottom: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 26px;
        padding: 0 8px;
        border-radius: 8px;
        background: rgba(15, 23, 42, .92);
        color: #fff;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .06em;
    }

    .video-tv-live-tag::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #ef4444;
    }

    .video-tv-card-body {
        padding: 11px 12px 12px;
    }

    .video-tv-card-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #0f172a;
        font-size: 14px;
        line-height: 1.35;
        font-weight: 800;
    }

    .video-tv-card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 6px;
        color: #64748b;
        font-size: 11px;
        line-height: 1.3;
        font-weight: 650;
    }

    .video-tv-card-category {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .video-tv-health {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .video-tv-health::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #94a3b8;
    }

    .video-tv-health[data-state='ok'] {
        color: #15803d;
    }

    .video-tv-health[data-state='ok']::before {
        background: #22c55e;
    }

    .video-tv-health[data-state='bad'] {
        color: #b91c1c;
    }

    .video-tv-health[data-state='bad']::before {
        background: #ef4444;
    }

    .video-tv-empty {
        margin-top: 12px;
        padding: 34px 16px;
        border-radius: 16px;
        background: #f8fafc;
        color: #64748b;
        text-align: center;
        font-size: 13px;
        font-weight: 650;
    }

    [data-video-tv-root] [hidden] {
        display: none !important;
    }

    @media (max-width: 840px) {
        .video-tv-page {
            padding-left: 0;
            padding-right: 0;
        }

        .video-tv-header,
        .video-tv-toolbar,
        .video-tv-list-head,
        .video-tv-grid,
        .video-tv-empty {
            margin-left: 12px;
            margin-right: 12px;
        }

        .video-tv-header {
            align-items: center;
        }

        .video-tv-title {
            font-size: 24px;
        }

        .video-tv-player-card {
            border-radius: 0;
        }

        .video-tv-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
    }

    @media (max-width: 620px) {
        .video-tv-header {
            display: block;
        }

        .video-tv-summary {
            justify-content: flex-start;
            margin-top: 12px;
        }

        .video-tv-summary-item {
            min-height: 30px;
            padding: 0 9px;
            font-size: 11px;
        }

        .video-tv-now {
            padding: 13px 12px 14px;
        }

        .video-tv-now-title {
            font-size: 14px;
        }

        .video-tv-next {
            min-height: 36px;
            padding: 0 11px;
        }

        .video-tv-toolbar {
            padding: 12px;
        }

        .video-tv-toolbar-top {
            display: block;
        }

        .video-tv-search {
            width: 100%;
            margin: 11px 0 0;
        }

        .video-tv-list-head {
            align-items: flex-end;
        }

        .video-tv-card-body {
            padding: 9px 10px 10px;
        }

        .video-tv-card-name {
            font-size: 13px;
        }

        .video-tv-card-meta {
            display: block;
            margin-top: 4px;
        }

        .video-tv-health {
            margin-top: 5px;
        }
    }
</style>

<div class="video-tv-page" data-video-tv-root>
    <script type="application/json" data-video-tv-data>{!! $channelsJson !!}</script>

    <header class="video-tv-header">
        <div>
            <h1 class="video-tv-title">Canlı TV</h1>
            <p class="video-tv-subtitle">Türkiye'den canlı yayın yapan kanalları tek ekrandan izle.</p>
        </div>

        <div class="video-tv-summary" aria-label="Kanal durumu">
            <div class="video-tv-summary-item">
                <span class="video-tv-summary-dot" aria-hidden="true"></span>
                <span data-video-health-status>{{ $channels->count() }} kanal kullanılabilir</span>
            </div>
            <div class="video-tv-summary-item" data-video-source-count>0 kontrol edildi</div>
        </div>
    </header>

    <section class="video-tv-player-card" aria-label="Canlı yayın oynatıcı">
        <div class="video-tv-player-wrap">
            <video
                data-video-player
                controls
                autoplay
                muted
                playsinline
                preload="metadata"
            ></video>

            <div class="video-tv-player-badge">CANLI YAYIN</div>

            <div class="video-tv-overlay" data-video-loading>
                İlk kanal açılıyor…
            </div>

            <div class="video-tv-overlay video-tv-error" data-video-error hidden>
                <div>
                    <strong>Yayın açılamadı</strong>
                    <span data-video-error-text>Sıradaki çalışan kanala geçiliyor.</span>
                </div>
            </div>
        </div>

        <div class="video-tv-now">
            <div class="video-tv-now-copy">
                <div class="video-tv-now-label">Şimdi izleniyor</div>
                <div class="video-tv-now-title" data-video-current-title>Kanal hazırlanıyor</div>
                <div class="video-tv-now-meta" data-video-current-meta>Canlı yayın bağlantısı kontrol ediliyor.</div>
            </div>

            <button type="button" class="video-tv-next" data-video-next>Sonraki kanal</button>
        </div>
    </section>

    <section class="video-tv-toolbar" aria-label="Kanal filtreleri">
        <div class="video-tv-toolbar-top">
            <div>
                <h2 class="video-tv-section-title">Kanal bul</h2>
            </div>

            <label class="video-tv-search">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"></circle>
                    <path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                </svg>
                <input type="search" data-video-search placeholder="Kanal ara..." autocomplete="off">
            </label>
        </div>

        <div class="video-tv-filters">
            @foreach ($categories as $filter)
                <button
                    type="button"
                    class="video-tv-filter {{ $filter === 'Tümü' ? 'is-active' : '' }}"
                    data-video-filter="{{ $filter }}"
                >{{ $filter }}</button>
            @endforeach
        </div>
    </section>

    <div class="video-tv-list-head">
        <div>
            <h2 class="video-tv-section-title">Kanallar</h2>
            <div class="video-tv-section-count" data-video-visible-count>{{ $channels->count() }} kanal</div>
        </div>

        <div class="video-tv-list-stats">
            <span data-video-checked-count>0 kontrol edildi</span>
            <span class="video-tv-list-stats-separator">•</span>
            <span data-video-failed-count>0 bozuk</span>
        </div>
    </div>

    <section class="video-tv-grid" aria-label="Kanal listesi">
        @foreach ($channels as $index => $channel)
            @php
                $channelName = trim((string) ($channel['name'] ?? ('Kanal ' . ($index + 1))));
                $channelCategory = trim((string) ($channel['category'] ?? 'Genel')) ?: 'Genel';
                $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($channelName ?: 'TV', 0, 2));
            @endphp

            <button
                type="button"
                class="video-tv-card"
                data-video-card
                data-index="{{ $index }}"
                aria-label="{{ $channelName }} kanalını aç"
            >
                <div class="video-tv-thumb">
                    @if (filled($channel['image'] ?? null))
                        <img src="{{ $channel['image'] }}" alt="{{ $channelName }}" loading="lazy">
                    @else
                        <div class="video-tv-thumb-fallback" aria-hidden="true">{{ $initials }}</div>
                    @endif
                    <span class="video-tv-live-tag">CANLI</span>
                </div>

                <div class="video-tv-card-body">
                    <div class="video-tv-card-name">{{ $channelName }}</div>
                    <div class="video-tv-card-meta">
                        <span class="video-tv-card-category">{{ $channelCategory }}</span>
                        <span class="video-tv-health" data-video-card-health data-state="idle">Hazır</span>
                    </div>
                </div>
            </button>
        @endforeach
    </section>

    <div class="video-tv-empty" data-video-empty {{ $channels->isNotEmpty() ? 'hidden' : '' }}>
        Bu filtrede gösterilecek kanal bulunamadı.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js" defer></script>
<script src="{{ asset('js/video-tv.js') }}?v=2" defer></script>
@endsection

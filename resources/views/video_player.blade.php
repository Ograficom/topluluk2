@extends('layouts.app')

@section('title', 'Video')
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

    // Veritabanı boş kalırsa /video sayfasını boş bırakma. Yerel M3U listesini
    // anında yedek kaynak olarak kullan. Filament tarafı düzeldiğinde DB kayıtları
    // otomatik olarak öncelikli olmaya devam eder.
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
@endphp

<style>
    .video-tv-shell{width:100%;max-width:760px;margin:0 auto;background:#fff;color:#0f172a}
    .video-tv-player{width:100%;overflow:hidden;background:#020617;border-radius:16px}
    .video-tv-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .video-tv-card{min-width:0;overflow:hidden;border:1px solid #e5e7eb;border-radius:13px;background:#fff;text-align:left}
    .video-tv-card.is-active{border-color:#64748b}
    .video-tv-card.is-bad{opacity:.58}
    .video-tv-card:disabled{cursor:not-allowed}
    .video-tv-thumb{position:relative;aspect-ratio:16/9;overflow:hidden;background:#0f172a}
    .video-tv-thumb img{display:block;width:100%;height:100%;object-fit:cover}
    .video-tv-fallback{display:flex;width:100%;height:100%;align-items:center;justify-content:center;color:#94a3b8}
    .video-tv-live{position:absolute;right:7px;bottom:7px;border-radius:6px;background:rgba(2,6,23,.9);padding:4px 6px;color:#fff;font-size:10px;font-weight:700}
    .video-tv-body{padding:9px 10px 10px}
    .video-tv-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#111827;font-size:14px;font-weight:650;line-height:20px}
    .video-tv-meta{margin-top:3px;display:flex;align-items:center;justify-content:space-between;gap:5px;color:#64748b;font-size:11px;line-height:16px}
    .video-tv-health{display:inline-flex;align-items:center;gap:4px;white-space:nowrap}
    .video-tv-health:before{content:"";width:6px;height:6px;border-radius:999px;background:#94a3b8}
    .video-tv-health[data-health="ok"]{color:#15803d}.video-tv-health[data-health="ok"]:before{background:#22c55e}
    .video-tv-health[data-health="bad"]{color:#b91c1c}.video-tv-health[data-health="bad"]:before{background:#ef4444}
    .video-tv-filters{display:flex;flex-wrap:wrap;gap:7px}
    .video-tv-filter{border:1px solid #e2e8f0;border-radius:999px;background:#fff;padding:7px 11px;color:#475569;font-size:12px;font-weight:600}
    .video-tv-filter.is-active{border-color:#0f172a;background:#0f172a;color:#fff}

    @media(max-width:767px){
        .video-tv-shell{width:100vw;max-width:none;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw)}
        .video-tv-player{border-radius:0}
        .video-tv-pad{padding-left:9px;padding-right:9px}
        .video-tv-grid{gap:8px}
        .video-tv-card{border-radius:10px}
        .video-tv-body{padding:8px}
        .video-tv-name{font-size:13px}
    }
</style>

<div class="video-tv-shell py-3 md:py-5">
    <div class="video-tv-pad mb-3 flex items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-lg font-bold tracking-tight text-slate-950 md:text-xl">Canlı TV</h1>
            <p class="mt-0.5 text-xs text-slate-500">Kanalı seç, yayın üstte açılsın.</p>
        </div>
        <div class="shrink-0 text-right text-[11px] font-medium text-slate-500">
            <div id="video-health-status">{{ $channels->count() }} kanal listede</div>
            <div id="video-source-count" class="mt-0.5">0 kontrol edildi</div>
        </div>
    </div>

    <section class="video-tv-player" aria-label="Canlı video oynatıcı">
        <div class="relative aspect-video w-full bg-slate-950">
            <video id="ografi-video-player" class="h-full w-full bg-slate-950 object-contain" controls autoplay muted playsinline preload="metadata"></video>
            <div id="video-player-loading" class="pointer-events-none absolute inset-0 flex items-center justify-center bg-slate-950/55 px-6 text-center text-sm font-medium text-white">İlk kanal açılıyor…</div>
            <div id="video-player-error" class="absolute inset-0 hidden items-center justify-center bg-slate-950 px-6 text-center">
                <div>
                    <div class="text-base font-semibold text-white">Yayın açılamadı</div>
                    <p id="video-player-error-text" class="mt-2 text-sm text-white/70">Sıradaki kanala geçiliyor.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="video-tv-pad mt-3 flex min-h-10 items-center justify-between gap-3">
        <div class="min-w-0">
            <div id="video-current-title" class="truncate text-sm font-semibold text-slate-900">Kanal hazırlanıyor</div>
            <div id="video-current-meta" class="mt-0.5 truncate text-xs text-slate-500"></div>
        </div>
        <button id="video-next-button" type="button" class="shrink-0 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 active:bg-slate-300">Sonraki</button>
    </div>

    <div class="video-tv-pad mt-4">
        <div id="video-category-filters" class="video-tv-filters"></div>
    </div>

    <section class="video-tv-pad mt-4 pb-24 md:pb-8" aria-label="Kanal listesi">
        <div class="mb-2 flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-slate-900">Kanallar</h2>
            <div class="text-[11px] text-slate-500"><span id="video-checked-count">0 kontrol edildi</span><span class="px-1 text-slate-300">•</span><span id="video-failed-count">0 bozuk</span></div>
        </div>
        <div id="video-playlist-grid" class="video-tv-grid"></div>
        <div id="video-empty-state" class="hidden py-10 text-center text-sm text-slate-500">Bu türde kanal bulunamadı.</div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js"></script>
<script>
(() => {
    const items = @json($channels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    const player = document.getElementById('ografi-video-player');
    const grid = document.getElementById('video-playlist-grid');
    const filters = document.getElementById('video-category-filters');
    const loading = document.getElementById('video-player-loading');
    const errorBox = document.getElementById('video-player-error');
    const errorText = document.getElementById('video-player-error-text');
    const title = document.getElementById('video-current-title');
    const meta = document.getElementById('video-current-meta');
    const sourceCount = document.getElementById('video-source-count');
    const checkedCount = document.getElementById('video-checked-count');
    const failedCount = document.getElementById('video-failed-count');
    const healthStatus = document.getElementById('video-health-status');
    const empty = document.getElementById('video-empty-state');
    const nextButton = document.getElementById('video-next-button');

    if (!player || !grid || !Array.isArray(items)) return;

    items.forEach(item => item.health = 'idle');

    let active = -1;
    let category = 'Tümü';
    let hls = null;
    let timer = null;
    let checked = 0;
    let failed = 0;
    let switchingAfterFailure = false;
    let ignorePlayerErrorsUntil = 0;

    const categories = ['Tümü', ...new Set(items.map(item => item.category || 'Genel'))];

    const safePoster = value => {
        try {
            const url = new URL(String(value || ''), location.href);
            return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
        } catch {
            return '';
        }
    };

    const clearTimer = () => {
        if (timer) window.clearTimeout(timer);
        timer = null;
    };

    const stop = () => {
        clearTimer();
        if (hls) hls.destroy();
        hls = null;
    };

    const counters = () => {
        if (checkedCount) checkedCount.textContent = `${checked} kontrol edildi`;
        if (failedCount) failedCount.textContent = `${failed} bozuk`;
        if (sourceCount) sourceCount.textContent = `${checked} kontrol edildi`;
        if (healthStatus) healthStatus.textContent = `${items.length} kanal listede`;
    };

    const renderFilters = () => {
        filters.innerHTML = '';

        categories.forEach(value => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `video-tv-filter${category === value ? ' is-active' : ''}`;
            button.textContent = value;
            button.addEventListener('click', () => {
                category = value;
                renderFilters();
                renderGrid();
            });
            filters.appendChild(button);
        });
    };

    const renderGrid = () => {
        grid.innerHTML = '';
        let visible = 0;

        items.forEach((item, index) => {
            if (category !== 'Tümü' && (item.category || 'Genel') !== category) return;

            visible++;

            const card = document.createElement('button');
            card.type = 'button';
            card.className = `video-tv-card${index === active ? ' is-active' : ''}${item.health === 'bad' ? ' is-bad' : ''}`;
            card.disabled = item.health === 'bad';

            const thumb = document.createElement('div');
            thumb.className = 'video-tv-thumb';

            const imageUrl = safePoster(item.image);
            if (imageUrl) {
                const image = document.createElement('img');
                image.src = imageUrl;
                image.alt = '';
                image.loading = 'lazy';
                thumb.appendChild(image);
            } else {
                const fallback = document.createElement('div');
                fallback.className = 'video-tv-fallback';
                fallback.innerHTML = '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="m9 9 6 3-6 3V9Z" fill="currentColor"/></svg>';
                thumb.appendChild(fallback);
            }

            const liveBadge = document.createElement('span');
            liveBadge.className = 'video-tv-live';
            liveBadge.textContent = 'CANLI';
            thumb.appendChild(liveBadge);

            const body = document.createElement('div');
            body.className = 'video-tv-body';

            const name = document.createElement('div');
            name.className = 'video-tv-name';
            name.textContent = item.name || `Kanal ${index + 1}`;
            body.appendChild(name);

            const metaRow = document.createElement('div');
            metaRow.className = 'video-tv-meta';

            const type = document.createElement('span');
            type.className = 'truncate';
            type.textContent = item.category || 'Genel';
            metaRow.appendChild(type);

            const health = document.createElement('span');
            health.className = 'video-tv-health';
            health.dataset.health = item.health === 'bad' ? 'bad' : (item.health === 'ok' ? 'ok' : 'idle');
            health.textContent = item.health === 'bad' ? 'Bozuk' : (item.health === 'ok' ? 'Çalışıyor' : 'Hazır');
            metaRow.appendChild(health);

            body.appendChild(metaRow);
            card.appendChild(thumb);
            card.appendChild(body);
            card.addEventListener('click', () => play(index));
            grid.appendChild(card);
        });

        empty.classList.toggle('hidden', visible > 0);
        counters();
    };

    const markOk = index => {
        const item = items[index];
        if (!item || item.health === 'ok') return;

        if (item.health === 'idle') checked++;
        if (item.health === 'bad' && failed > 0) failed--;

        item.health = 'ok';
        renderGrid();
    };

    const markBad = index => {
        const item = items[index];
        if (!item || item.health === 'bad') return;

        if (item.health === 'idle') checked++;
        item.health = 'bad';
        failed++;
        renderGrid();
    };

    const nextIndex = from => {
        if (!items.length) return -1;

        for (let step = 1; step <= items.length; step++) {
            const index = (from + step + items.length) % items.length;
            if (items[index]?.health === 'bad') continue;
            if (category !== 'Tümü' && (items[index].category || 'Genel') !== category) continue;
            return index;
        }

        return -1;
    };

    const hideError = () => {
        errorBox.classList.add('hidden');
        errorBox.classList.remove('flex');
    };

    const showError = message => {
        loading.classList.add('hidden');
        errorText.textContent = message;
        errorBox.classList.remove('hidden');
        errorBox.classList.add('flex');
    };

    const fail = message => {
        if (switchingAfterFailure || active < 0) return;

        switchingAfterFailure = true;
        const failedIndex = active;
        stop();
        markBad(failedIndex);
        showError(`${message} Sıradaki çalışan kanala geçiliyor.`);

        const next = nextIndex(failedIndex);
        if (next >= 0) {
            window.setTimeout(() => {
                switchingAfterFailure = false;
                play(next);
            }, 450);
        } else {
            switchingAfterFailure = false;
            title.textContent = 'Çalışan kanal bulunamadı';
        }
    };

    async function play(index) {
        const item = items[index];
        if (!item || item.health === 'bad') return;

        switchingAfterFailure = false;
        active = index;
        stop();
        hideError();

        loading.textContent = `${item.name || 'Kanal'} açılıyor…`;
        loading.classList.remove('hidden');
        title.textContent = item.name || 'Canlı yayın';
        meta.textContent = `${item.category || 'Genel'} · Canlı yayın`;

        ignorePlayerErrorsUntil = performance.now() + 650;
        player.pause();
        player.removeAttribute('src');
        player.removeAttribute('poster');

        const imageUrl = safePoster(item.image);
        if (imageUrl) player.poster = imageUrl;

        renderGrid();

        timer = window.setTimeout(() => {
            if (player.readyState === 0) {
                fail('Yayın sunucusundan yanıt alınamadı.');
            }
        }, 15000);

        if (item.type === 'hls') {
            if (player.canPlayType('application/vnd.apple.mpegurl')) {
                player.src = item.url;
                player.load();
                try { await player.play(); } catch {}
                return;
            }

            if (window.Hls?.isSupported()) {
                hls = new window.Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                    manifestLoadingTimeOut: 12000,
                    levelLoadingTimeOut: 12000,
                });
                hls.loadSource(item.url);
                hls.attachMedia(player);
                hls.on(window.Hls.Events.MANIFEST_PARSED, () => {
                    markOk(index);
                    player.play().catch(() => {});
                });
                hls.on(window.Hls.Events.ERROR, (_event, data) => {
                    if (data?.fatal) fail('HLS kaynağı yanıt vermedi.');
                });
                return;
            }

            fail('Bu tarayıcı HLS oynatmayı desteklemiyor.');
            return;
        }

        player.src = item.url;
        player.load();
        try { await player.play(); } catch {}
    }

    player.addEventListener('loadedmetadata', () => {
        clearTimer();
        markOk(active);
    });
    player.addEventListener('canplay', () => {
        clearTimer();
        loading.classList.add('hidden');
        markOk(active);
    });
    player.addEventListener('playing', () => {
        clearTimer();
        loading.classList.add('hidden');
        markOk(active);
    });
    player.addEventListener('waiting', () => loading.classList.remove('hidden'));
    player.addEventListener('error', () => {
        if (performance.now() < ignorePlayerErrorsUntil) return;
        if (active >= 0) fail('Video kaynağı yüklenemedi.');
    });
    player.addEventListener('ended', () => {
        const next = nextIndex(active);
        if (next >= 0) play(next);
    });

    nextButton?.addEventListener('click', () => {
        const next = nextIndex(active);
        if (next >= 0) play(next);
    });

    renderFilters();
    renderGrid();

    if (items.length) {
        play(0);
    } else {
        loading.classList.add('hidden');
        showError('Kanal listesi yüklenemedi. Filament panelinden kanal ekleyebilirsin.');
    }
})();
</script>
@endsection

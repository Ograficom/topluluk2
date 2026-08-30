@extends('layouts.app')

@section('title', 'Video')
@section('hide_feed_header')
@endsection

@section('content')
@php
    $mediaItems = [];
    $seenMediaUrls = [];

    $defaultPlaylistUrl = asset('streams/turkiye.m3u');
    $seenMediaUrls[$defaultPlaylistUrl] = true;
    $mediaItems[] = [
        'url' => $defaultPlaylistUrl,
        'type' => 'm3u',
        'title' => 'Türkiye Canlı TV',
        'poster' => null,
        'post_id' => 0,
    ];

    $normaliseMediaUrl = static function (?string $value): ?string {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace('\\/', '/', $value);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if (str_starts_with($value, '//')) {
            return request()->getScheme() . ':' . $value;
        }

        if (preg_match('~^https?://~i', $value) === 1) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return url($value);
        }

        if (preg_match('~^(?:storage|uploads|media)/~i', $value) === 1) {
            return asset($value);
        }

        return null;
    };

    $mediaType = static function (string $url): ?string {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        return match (true) {
            str_ends_with($path, '.m3u') => 'm3u',
            str_ends_with($path, '.m3u8') => 'hls',
            str_ends_with($path, '.mp4'),
            str_ends_with($path, '.webm'),
            str_ends_with($path, '.m4v'),
            str_ends_with($path, '.mov'),
            str_ends_with($path, '.ogv'),
            str_ends_with($path, '.ogg') => 'video',
            default => null,
        };
    };

    $extractCandidates = static function ($root): array {
        $found = [];
        $stack = [$root];

        while ($stack !== []) {
            $value = array_pop($stack);

            if (is_object($value)) {
                $value = get_object_vars($value);
            }

            if (is_array($value)) {
                foreach ($value as $child) {
                    $stack[] = $child;
                }
                continue;
            }

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $text = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = str_replace('\\/', '/', $text);
            $trimmed = trim($text, " \t\n\r\0\x0B\"'");

            if (preg_match('~(?:\.m3u8?|\.mp4|\.webm|\.m4v|\.mov|\.ogv|\.ogg)(?:\?[^\s<>\"\']*)?$~i', $trimmed) === 1) {
                $found[] = $trimmed;
            }

            if (preg_match_all('~(?:https?:)?//[^\s<>\"\']+|/(?:storage|uploads|media)/[^\s<>\"\']+~i', $text, $matches)) {
                foreach ($matches[0] as $candidate) {
                    $candidate = rtrim($candidate, "\"'.,;)>]} ");
                    if (preg_match('~\.(?:m3u8?|mp4|webm|m4v|mov|ogv|ogg)(?:\?|$)~i', $candidate) === 1) {
                        $found[] = $candidate;
                    }
                }
            }
        }

        return array_values(array_unique($found));
    };

    foreach ($videoPosts ?? collect() as $post) {
        $postTitle = trim((string) ($post->title ?? '')) ?: ('Video #' . $post->id);

        $poster = $post->featured_image_url ?? $post->featured_image ?? null;
        if (is_string($poster) && $poster !== '') {
            if (! preg_match('~^https?://~i', $poster) && ! str_starts_with($poster, '//')) {
                $poster = str_starts_with($poster, '/') ? url($poster) : asset($poster);
            }
        } else {
            $poster = null;
        }

        $sources = [
            $post->content_json ?? null,
            $post->content ?? null,
            $post->video_url ?? null,
            $post->media_url ?? null,
            $post->source_url ?? null,
            $post->url ?? null,
        ];

        foreach ($sources as $source) {
            if (is_string($source)) {
                $decoded = json_decode($source, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $source = $decoded;
                }
            }

            foreach ($extractCandidates($source) as $candidate) {
                $url = $normaliseMediaUrl($candidate);
                if (! $url || isset($seenMediaUrls[$url])) {
                    continue;
                }

                $type = $mediaType($url);
                if (! $type) {
                    continue;
                }

                $seenMediaUrls[$url] = true;
                $mediaItems[] = [
                    'url' => $url,
                    'type' => $type,
                    'title' => $postTitle,
                    'poster' => $poster,
                    'post_id' => (int) $post->id,
                ];
            }
        }
    }
@endphp

<div class="min-h-screen bg-white text-slate-950">
    <div class="mx-auto w-full max-w-7xl px-3 py-4 sm:px-5 sm:py-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">Video</h1>
                <p class="mt-1 text-sm text-slate-500">Çalışan yayınlar otomatik kontrol edilir; bozulan kanal atlanır.</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                <span id="video-health-status">Liste hazırlanıyor</span>
                <span class="text-slate-300">•</span>
                <span id="video-source-count">0 kanal</span>
            </div>
        </div>

        <section class="overflow-hidden rounded-2xl bg-black" aria-label="Video oynatıcı">
            <div class="relative aspect-video w-full bg-black">
                <video
                    id="ografi-video-player"
                    class="h-full w-full bg-black object-contain"
                    controls
                    autoplay
                    muted
                    playsinline
                    preload="metadata"
                ></video>

                <div id="video-player-loading" class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/35 px-6 text-center text-sm font-medium text-white">
                    Yayın hazırlanıyor…
                </div>

                <div id="video-player-error" class="absolute inset-0 hidden items-center justify-center bg-black px-6 text-center">
                    <div>
                        <div class="text-base font-semibold text-white">Yayın açılamadı</div>
                        <p id="video-player-error-text" class="mt-2 max-w-xl text-sm text-white/70">Sıradaki çalışan kanala geçiliyor.</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-3 flex min-h-10 items-center justify-between gap-4">
            <div class="min-w-0">
                <div id="video-current-title" class="truncate text-sm font-semibold text-slate-900">Çalışan kanal aranıyor</div>
                <div id="video-current-meta" class="mt-0.5 truncate text-xs text-slate-500"></div>
            </div>
            <button
                id="video-next-button"
                type="button"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 active:bg-slate-300"
            >
                Sonraki çalışan kanal
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" aria-hidden="true">
                    <path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <section class="mt-6" aria-label="Kanal listesi">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Kanallar</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Yatay kaydırma yok. Kanallar aşağı doğru normal liste halinde dizilir.</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span id="video-checked-count">0 kontrol edildi</span>
                    <span class="text-slate-300">•</span>
                    <span id="video-failed-count">0 bozuk</span>
                </div>
            </div>

            <div
                id="video-playlist-grid"
                class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            ></div>

            <div id="video-empty-state" class="hidden rounded-xl bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
                Çalışan yayın bulunamadı.
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js"></script>
<script>
(() => {
    const seedItems = @json($mediaItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    const player = document.getElementById('ografi-video-player');
    const grid = document.getElementById('video-playlist-grid');
    const loading = document.getElementById('video-player-loading');
    const errorBox = document.getElementById('video-player-error');
    const errorText = document.getElementById('video-player-error-text');
    const title = document.getElementById('video-current-title');
    const meta = document.getElementById('video-current-meta');
    const count = document.getElementById('video-source-count');
    const checkedCount = document.getElementById('video-checked-count');
    const failedCount = document.getElementById('video-failed-count');
    const healthStatus = document.getElementById('video-health-status');
    const emptyState = document.getElementById('video-empty-state');
    const nextButton = document.getElementById('video-next-button');

    if (!player || !grid) return;

    let items = [];
    let activeIndex = -1;
    let hls = null;
    let playbackTimer = null;
    let healthRunId = 0;
    let checkedTotal = 0;
    let failedTotal = 0;
    let healthyTotal = 0;
    const failedUrls = new Set();

    try {
        const cached = JSON.parse(sessionStorage.getItem('ografi_video_failed_urls') || '[]');
        if (Array.isArray(cached)) cached.forEach((url) => failedUrls.add(String(url)));
    } catch {
        // Session storage may be unavailable; runtime blacklist still works.
    }

    const saveFailedUrls = () => {
        try {
            sessionStorage.setItem('ografi_video_failed_urls', JSON.stringify([...failedUrls].slice(-100)));
        } catch {
            // Ignore storage errors.
        }
    };

    const cleanUrl = (value) => String(value || '').trim();

    const extensionType = (url) => {
        try {
            const pathname = new URL(url, window.location.href).pathname.toLowerCase();
            if (pathname.endsWith('.m3u')) return 'm3u';
            if (pathname.endsWith('.m3u8')) return 'hls';
            return 'video';
        } catch {
            return 'video';
        }
    };

    const safePoster = (value) => {
        const poster = cleanUrl(value);
        if (!poster) return '';

        try {
            const parsed = new URL(poster, window.location.href);
            return ['http:', 'https:'].includes(parsed.protocol) ? parsed.href : '';
        } catch {
            return '';
        }
    };

    const parseExtInf = (line) => {
        const result = { title: '', logo: '', group: '' };
        const comma = line.indexOf(',');
        if (comma >= 0) result.title = line.slice(comma + 1).trim();

        const logo = line.match(/tvg-logo=(?:"([^"]*)"|'([^']*)'|([^\s,]+))/i);
        if (logo) result.logo = logo[1] || logo[2] || logo[3] || '';

        const group = line.match(/group-title=(?:"([^"]*)"|'([^']*)'|([^\s,]+))/i);
        if (group) result.group = group[1] || group[2] || group[3] || '';

        return result;
    };

    const parseM3u = (text, playlistUrl, inheritedTitle = '') => {
        const lines = String(text || '')
            .replace(/^\uFEFF/, '')
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter(Boolean);

        const result = [];
        let info = { title: '', logo: '', group: '' };

        for (const line of lines) {
            if (line.startsWith('#EXTINF:')) {
                info = parseExtInf(line);
                continue;
            }

            if (line.startsWith('#')) continue;

            try {
                const url = new URL(line, playlistUrl).href;
                result.push({
                    url,
                    type: extensionType(url),
                    title: info.title || inheritedTitle || 'Video',
                    poster: info.logo || '',
                    group: info.group || '',
                    fromPlaylist: playlistUrl,
                    health: 'pending',
                });
            } catch {
                // Invalid row; skip it.
            }

            info = { title: '', logo: '', group: '' };
        }

        return result;
    };

    const fetchWithTimeout = async (url, options = {}, timeoutMs = 7000) => {
        const controller = new AbortController();
        const timer = window.setTimeout(() => controller.abort(), timeoutMs);

        try {
            return await fetch(url, { ...options, signal: controller.signal });
        } finally {
            window.clearTimeout(timer);
        }
    };

    const expandPlaylist = async (item) => {
        try {
            const response = await fetchWithTimeout(item.url, {
                method: 'GET',
                credentials: 'omit',
                cache: 'no-store',
                headers: { 'Accept': 'audio/x-mpegurl, application/vnd.apple.mpegurl, text/plain, */*' },
            }, 8000);

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const text = await response.text();
            return parseM3u(text, item.url, item.title);
        } catch (error) {
            console.warn('M3U listesi okunamadı:', item.url, error);
            return [];
        }
    };

    const uniqueItems = (list) => {
        const seen = new Set();

        return list.filter((item) => {
            const url = cleanUrl(item?.url);
            if (!url || seen.has(url)) return false;

            seen.add(url);
            item.url = url;
            item.type = item.type || extensionType(url);
            item.health = failedUrls.has(url) ? 'failed' : (item.health || 'pending');
            return true;
        });
    };

    const destroyHls = () => {
        if (!hls) return;
        hls.destroy();
        hls = null;
    };

    const clearPlaybackTimer = () => {
        if (!playbackTimer) return;
        window.clearTimeout(playbackTimer);
        playbackTimer = null;
    };

    const armPlaybackTimer = () => {
        clearPlaybackTimer();
        playbackTimer = window.setTimeout(() => {
            failActive('Yayın 12 saniye içinde başlamadı.');
        }, 12000);
    };

    const showLoading = (show, message = '') => {
        if (message && loading) loading.textContent = message;
        loading?.classList.toggle('hidden', !show);
    };

    const showError = (message = '') => {
        showLoading(false);
        if (errorText && message) errorText.textContent = message;
        errorBox?.classList.remove('hidden');
        errorBox?.classList.add('flex');
    };

    const hideError = () => {
        errorBox?.classList.add('hidden');
        errorBox?.classList.remove('flex');
    };

    const tryAutoplay = async () => {
        try {
            player.muted = true;
            await player.play();
        } catch {
            // Controls remain available when autoplay requires a user gesture.
        }
    };

    const updateCounters = () => {
        const visibleHealthy = items.filter((item) => item.health === 'healthy').length;
        const pending = items.filter((item) => item.health === 'pending' || item.health === 'checking').length;

        if (count) count.textContent = `${visibleHealthy} çalışan kanal`;
        if (checkedCount) checkedCount.textContent = `${checkedTotal} kontrol edildi`;
        if (failedCount) failedCount.textContent = `${failedTotal} bozuk`;

        if (healthStatus) {
            healthStatus.textContent = pending > 0
                ? `${pending} kanal kontrol ediliyor`
                : 'Kontrol tamamlandı';
        }
    };

    const updateActiveCard = () => {
        grid.querySelectorAll('[data-playlist-index]').forEach((card) => {
            const current = Number(card.dataset.playlistIndex) === activeIndex;
            card.classList.toggle('ring-2', current);
            card.classList.toggle('ring-slate-900', current);
            card.classList.toggle('bg-slate-100', current);
            card.setAttribute('aria-current', current ? 'true' : 'false');
        });
    };

    const updateMeta = (item) => {
        if (title) title.textContent = item?.title || 'Video';

        if (meta) {
            const bits = [];
            if (item?.group) bits.push(item.group);
            bits.push(item?.type === 'hls' ? 'HLS canlı yayın' : 'Video');
            meta.textContent = bits.join(' · ');
        }
    };

    const statusMarkup = (item) => {
        if (item.health === 'healthy') {
            return '<span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Çalışıyor</span>';
        }

        if (item.health === 'failed') {
            return '<span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-rose-600"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Bozuk</span>';
        }

        return '<span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Kontrol ediliyor</span>';
    };

    const renderGrid = () => {
        grid.innerHTML = '';

        items.forEach((item, index) => {
            if (item.health === 'failed') return;

            const card = document.createElement('button');
            card.type = 'button';
            card.dataset.playlistIndex = String(index);
            card.className = 'group overflow-hidden rounded-xl bg-slate-50 text-left focus:outline-none disabled:cursor-wait disabled:opacity-70';
            card.disabled = item.health !== 'healthy';

            const thumb = document.createElement('div');
            thumb.className = 'relative aspect-video overflow-hidden bg-slate-200';

            const poster = safePoster(item.poster);
            if (poster) {
                const image = document.createElement('img');
                image.src = poster;
                image.alt = '';
                image.loading = 'lazy';
                image.className = 'h-full w-full object-cover';
                thumb.appendChild(image);
            } else {
                const fallback = document.createElement('div');
                fallback.className = 'flex h-full w-full items-center justify-center bg-slate-900 text-white/70';
                fallback.innerHTML = '<svg viewBox="0 0 24 24" class="h-9 w-9" fill="none" aria-hidden="true"><path d="M9 7.8v8.4L16 12 9 7.8Z" fill="currentColor"/></svg>';
                thumb.appendChild(fallback);
            }

            const badge = document.createElement('span');
            badge.className = 'absolute bottom-2 right-2 rounded-md bg-black/75 px-1.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white';
            badge.textContent = item.type === 'hls' ? 'CANLI' : 'VIDEO';
            thumb.appendChild(badge);

            const body = document.createElement('div');
            body.className = 'px-3 py-3';

            const top = document.createElement('div');
            top.className = 'flex items-start justify-between gap-2';

            const cardTitle = document.createElement('div');
            cardTitle.className = 'line-clamp-2 min-w-0 text-sm font-semibold leading-5 text-slate-900';
            cardTitle.textContent = item.title || `Kanal ${index + 1}`;
            top.appendChild(cardTitle);

            const status = document.createElement('div');
            status.className = 'shrink-0 pt-0.5';
            status.innerHTML = statusMarkup(item);
            top.appendChild(status);
            body.appendChild(top);

            if (item.group) {
                const group = document.createElement('div');
                group.className = 'mt-1 truncate text-xs text-slate-500';
                group.textContent = item.group;
                body.appendChild(group);
            }

            card.appendChild(thumb);
            card.appendChild(body);
            card.addEventListener('click', () => loadItem(index));
            grid.appendChild(card);
        });

        updateActiveCard();
        updateCounters();

        const anyVisible = items.some((item) => item.health !== 'failed');
        emptyState?.classList.toggle('hidden', anyVisible);
    };

    const nextHealthyIndex = (fromIndex) => {
        if (!items.length) return -1;

        for (let offset = 1; offset <= items.length; offset += 1) {
            const index = (fromIndex + offset + items.length) % items.length;
            if (items[index]?.health === 'healthy' && !failedUrls.has(items[index].url)) {
                return index;
            }
        }

        return -1;
    };

    const markFailed = (index) => {
        const item = items[index];
        if (!item || item.health === 'failed') return;

        if (item.health === 'healthy' && healthyTotal > 0) healthyTotal -= 1;
        item.health = 'failed';
        failedUrls.add(item.url);
        failedTotal += 1;
        saveFailedUrls();
        renderGrid();
    };

    const failActive = (message = 'Yayın açılamadı.') => {
        clearPlaybackTimer();
        destroyHls();

        const failedIndex = activeIndex;
        if (failedIndex >= 0) markFailed(failedIndex);

        showError(`${message} Sıradaki çalışan kanala geçiliyor.`);

        const nextIndex = nextHealthyIndex(failedIndex);
        if (nextIndex >= 0) {
            window.setTimeout(() => loadItem(nextIndex), 450);
        } else {
            showLoading(false);
            if (title) title.textContent = 'Çalışan kanal kalmadı';
        }
    };

    const loadItem = async (index, shouldScroll = true) => {
        const item = items[index];
        if (!item || item.health !== 'healthy' || failedUrls.has(item.url)) {
            const nextIndex = nextHealthyIndex(index);
            if (nextIndex >= 0 && nextIndex !== index) return loadItem(nextIndex, shouldScroll);
            return;
        }

        activeIndex = index;
        hideError();
        showLoading(true, 'Yayın açılıyor…');
        destroyHls();
        clearPlaybackTimer();

        player.pause();
        player.removeAttribute('src');
        player.removeAttribute('poster');
        player.load();

        const poster = safePoster(item.poster);
        if (poster) player.poster = poster;

        updateMeta(item);
        updateActiveCard();

        if (shouldScroll) {
            grid.querySelector(`[data-playlist-index="${activeIndex}"]`)?.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
            });
        }

        armPlaybackTimer();

        if (item.type === 'hls' || extensionType(item.url) === 'hls') {
            if (player.canPlayType('application/vnd.apple.mpegurl')) {
                player.src = item.url;
                player.load();
                await tryAutoplay();
                return;
            }

            if (window.Hls?.isSupported()) {
                hls = new window.Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 60,
                    manifestLoadingTimeOut: 8000,
                    levelLoadingTimeOut: 8000,
                    fragLoadingTimeOut: 10000,
                });

                hls.loadSource(item.url);
                hls.attachMedia(player);
                hls.on(window.Hls.Events.MANIFEST_PARSED, () => tryAutoplay());
                hls.on(window.Hls.Events.ERROR, (_event, data) => {
                    if (!data?.fatal) return;
                    failActive('HLS yayını bağlantı hatası verdi.');
                });
                return;
            }

            failActive('Bu tarayıcı HLS oynatmayı desteklemiyor.');
            return;
        }

        player.src = item.url;
        player.load();
        await tryAutoplay();
    };

    const healthCheckHls = async (item) => {
        if (player.canPlayType('application/vnd.apple.mpegurl')) {
            return true;
        }

        try {
            const response = await fetchWithTimeout(item.url, {
                method: 'GET',
                credentials: 'omit',
                cache: 'no-store',
                headers: { 'Accept': 'application/vnd.apple.mpegurl, application/x-mpegURL, */*' },
            }, 6500);

            if (!response.ok) return false;
            const text = await response.text();
            return text.includes('#EXTM3U') || text.includes('#EXT-X-');
        } catch {
            return false;
        }
    };

    const healthCheckVideo = (item) => new Promise((resolve) => {
        const probe = document.createElement('video');
        let settled = false;

        const finish = (value) => {
            if (settled) return;
            settled = true;
            window.clearTimeout(timer);
            probe.removeAttribute('src');
            probe.load();
            resolve(value);
        };

        const timer = window.setTimeout(() => finish(false), 6500);
        probe.preload = 'metadata';
        probe.muted = true;
        probe.playsInline = true;
        probe.addEventListener('loadedmetadata', () => finish(true), { once: true });
        probe.addEventListener('canplay', () => finish(true), { once: true });
        probe.addEventListener('error', () => finish(false), { once: true });
        probe.src = item.url;
        probe.load();
    });

    const healthCheckItem = async (item) => {
        if (failedUrls.has(item.url)) return false;
        if (item.type === 'hls' || extensionType(item.url) === 'hls') return healthCheckHls(item);
        return healthCheckVideo(item);
    };

    const checkHealthPool = async () => {
        const runId = ++healthRunId;
        let cursor = 0;
        let firstHealthyStarted = false;
        const workerCount = Math.min(5, Math.max(1, items.length));

        const worker = async () => {
            while (cursor < items.length && runId === healthRunId) {
                const index = cursor;
                cursor += 1;
                const item = items[index];

                if (!item) continue;

                if (failedUrls.has(item.url)) {
                    item.health = 'failed';
                    checkedTotal += 1;
                    failedTotal += 1;
                    renderGrid();
                    continue;
                }

                item.health = 'checking';
                renderGrid();

                const ok = await healthCheckItem(item);
                checkedTotal += 1;

                if (ok) {
                    item.health = 'healthy';
                    healthyTotal += 1;
                    renderGrid();

                    if (!firstHealthyStarted && activeIndex < 0) {
                        firstHealthyStarted = true;
                        await loadItem(index, false);
                    }
                } else {
                    item.health = 'failed';
                    failedUrls.add(item.url);
                    failedTotal += 1;
                    saveFailedUrls();
                    renderGrid();
                }
            }
        };

        await Promise.all(Array.from({ length: workerCount }, () => worker()));

        if (runId !== healthRunId) return;
        updateCounters();

        if (!items.some((item) => item.health === 'healthy')) {
            showLoading(false);
            showError('Kontrol edilen kaynakların hiçbiri şu anda çalışmıyor.');
            if (title) title.textContent = 'Çalışan kanal bulunamadı';
            emptyState?.classList.remove('hidden');
        }
    };

    const goNext = () => {
        const nextIndex = nextHealthyIndex(activeIndex);
        if (nextIndex >= 0) loadItem(nextIndex);
    };

    player.addEventListener('playing', () => {
        clearPlaybackTimer();
        hideError();
        showLoading(false);
    });

    player.addEventListener('canplay', () => {
        clearPlaybackTimer();
        showLoading(false);
    });

    player.addEventListener('waiting', () => showLoading(true, 'Yayın tamponlanıyor…'));
    player.addEventListener('ended', goNext);
    player.addEventListener('error', () => {
        if (player.error && activeIndex >= 0) {
            failActive('Video kaynağı yüklenemedi.');
        }
    });

    nextButton?.addEventListener('click', goNext);

    const boot = async () => {
        showLoading(true, 'Kanal listesi hazırlanıyor…');

        const playlistSeeds = seedItems.filter((item) => item.type === 'm3u' || extensionType(item.url) === 'm3u');
        const directSeeds = seedItems.filter((item) => item.type !== 'm3u' && extensionType(item.url) !== 'm3u');

        const expanded = [];
        for (const playlist of playlistSeeds) {
            expanded.push(...await expandPlaylist(playlist));
        }

        items = uniqueItems([...expanded, ...directSeeds]);

        if (!items.length) {
            showLoading(false);
            showError('M3U listesinde oynatılabilir kaynak bulunamadı.');
            emptyState?.classList.remove('hidden');
            return;
        }

        renderGrid();
        await checkHealthPool();
    };

    boot();
})();
</script>
@endsection

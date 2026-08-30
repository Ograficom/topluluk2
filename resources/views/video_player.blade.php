@extends('layouts.app')

@section('title', 'Video')
@section('hide_feed_header')
@endsection

@section('content')
@php
    $mediaItems = [];
    $seenMediaUrls = [];

    $defaultPlaylistUrl = 'https://iptv-org.github.io/iptv/countries/tr.m3u';
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
        <div class="mb-4 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">Video</h1>
                <p class="mt-1 text-sm text-slate-500">M3U, HLS ve doğrudan video kaynakları otomatik oynatılır.</p>
            </div>
            <div id="video-source-count" class="shrink-0 text-xs font-medium text-slate-500"></div>
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

                <div id="video-player-loading" class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/35 text-sm font-medium text-white">
                    Video listesi hazırlanıyor…
                </div>

                <div id="video-player-error" class="absolute inset-0 hidden items-center justify-center bg-black px-6 text-center">
                    <div>
                        <div class="text-base font-semibold text-white">Video oynatılamadı</div>
                        <p id="video-player-error-text" class="mt-2 max-w-xl text-sm text-white/70">Kaynak geçersiz veya uzak sunucu bağlantıyı engelliyor.</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-3 flex min-h-7 items-center justify-between gap-4">
            <div class="min-w-0">
                <div id="video-current-title" class="truncate text-sm font-semibold text-slate-900"></div>
                <div id="video-current-meta" class="mt-0.5 truncate text-xs text-slate-500"></div>
            </div>
            <button
                id="video-next-button"
                type="button"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 active:bg-slate-300"
            >
                Sonraki
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" aria-hidden="true">
                    <path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <section class="mt-5" aria-label="Video listesi">
            <div class="mb-2 flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-slate-900">Oynatma listesi</h2>
                <span class="text-xs text-slate-400">Yatay kaydır</span>
            </div>

            <div
                id="video-playlist-rail"
                class="flex snap-x snap-mandatory gap-3 overflow-x-auto pb-3 [scrollbar-width:thin]"
            ></div>

            <div id="video-empty-state" class="hidden rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                Video kaynağı bulunamadı.
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js"></script>
<script>
(() => {
    const seedItems = @json($mediaItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    const player = document.getElementById('ografi-video-player');
    const rail = document.getElementById('video-playlist-rail');
    const loading = document.getElementById('video-player-loading');
    const errorBox = document.getElementById('video-player-error');
    const errorText = document.getElementById('video-player-error-text');
    const title = document.getElementById('video-current-title');
    const meta = document.getElementById('video-current-meta');
    const count = document.getElementById('video-source-count');
    const emptyState = document.getElementById('video-empty-state');
    const nextButton = document.getElementById('video-next-button');

    if (!player || !rail) return;

    let items = [];
    let activeIndex = -1;
    let hls = null;

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
                });
            } catch {
                // Invalid playlist row; skip it.
            }

            info = { title: '', logo: '', group: '' };
        }

        return result;
    };

    const expandPlaylist = async (item) => {
        try {
            const response = await fetch(item.url, {
                method: 'GET',
                credentials: 'omit',
                cache: 'no-store',
                headers: { 'Accept': 'audio/x-mpegurl, application/vnd.apple.mpegurl, text/plain, */*' },
            });

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
            return true;
        });
    };

    const destroyHls = () => {
        if (!hls) return;
        hls.destroy();
        hls = null;
    };

    const showLoading = (show) => {
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
            // Browser can still require a user gesture; controls remain available.
        }
    };

    const updateActiveCard = () => {
        rail.querySelectorAll('[data-playlist-index]').forEach((card) => {
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
            bits.push(item?.type === 'hls' ? 'HLS' : 'Video');
            meta.textContent = bits.join(' · ');
        }
    };

    const loadItem = async (index, shouldScroll = true) => {
        if (!items.length) return;

        activeIndex = ((index % items.length) + items.length) % items.length;
        const item = items[activeIndex];

        hideError();
        showLoading(true);
        destroyHls();
        player.pause();
        player.removeAttribute('src');
        player.removeAttribute('poster');
        player.load();

        const poster = safePoster(item.poster);
        if (poster) player.poster = poster;

        updateMeta(item);
        updateActiveCard();

        if (shouldScroll) {
            rail.querySelector(`[data-playlist-index="${activeIndex}"]`)?.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center',
            });
        }

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
                });

                hls.loadSource(item.url);
                hls.attachMedia(player);
                hls.on(window.Hls.Events.MANIFEST_PARSED, () => tryAutoplay());
                hls.on(window.Hls.Events.ERROR, (_event, data) => {
                    if (!data?.fatal) return;
                    showError('HLS yayını açılamadı. Kaynak sunucu bağlantıyı veya CORS erişimini engelliyor olabilir.');
                    destroyHls();
                });
                return;
            }

            showError('Bu tarayıcı HLS oynatmayı desteklemiyor.');
            return;
        }

        player.src = item.url;
        player.load();
        await tryAutoplay();
    };

    const renderRail = () => {
        rail.innerHTML = '';

        if (!items.length) {
            emptyState?.classList.remove('hidden');
            count.textContent = '0 video';
            return;
        }

        emptyState?.classList.add('hidden');
        count.textContent = `${items.length} video`;

        items.forEach((item, index) => {
            const card = document.createElement('button');
            card.type = 'button';
            card.dataset.playlistIndex = String(index);
            card.className = 'group w-[250px] shrink-0 snap-start overflow-hidden rounded-xl bg-slate-50 text-left focus:outline-none sm:w-[290px]';

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
            badge.textContent = item.type === 'hls' ? 'HLS' : 'VIDEO';
            thumb.appendChild(badge);

            const body = document.createElement('div');
            body.className = 'px-3 py-2.5';

            const cardTitle = document.createElement('div');
            cardTitle.className = 'line-clamp-2 text-sm font-semibold leading-5 text-slate-900';
            cardTitle.textContent = item.title || `Video ${index + 1}`;
            body.appendChild(cardTitle);

            if (item.group) {
                const group = document.createElement('div');
                group.className = 'mt-1 truncate text-xs text-slate-500';
                group.textContent = item.group;
                body.appendChild(group);
            }

            card.appendChild(thumb);
            card.appendChild(body);
            card.addEventListener('click', () => loadItem(index));
            rail.appendChild(card);
        });
    };

    const goNext = () => {
        if (!items.length) return;
        loadItem(activeIndex + 1);
    };

    player.addEventListener('playing', () => showLoading(false));
    player.addEventListener('canplay', () => showLoading(false));
    player.addEventListener('waiting', () => showLoading(true));
    player.addEventListener('ended', goNext);
    player.addEventListener('error', () => {
        if (player.error) {
            showError('Video kaynağı yüklenemedi veya yayın sunucusu erişimi reddetti.');
        }
    });
    nextButton?.addEventListener('click', goNext);

    const boot = async () => {
        showLoading(true);

        const playlistSeeds = seedItems.filter((item) => item.type === 'm3u' || extensionType(item.url) === 'm3u');
        const directSeeds = seedItems.filter((item) => item.type !== 'm3u' && extensionType(item.url) !== 'm3u');

        const expanded = [];
        for (const playlist of playlistSeeds) {
            expanded.push(...await expandPlaylist(playlist));
        }

        items = uniqueItems([...expanded, ...directSeeds]);
        renderRail();

        if (!items.length) {
            showLoading(false);
            showError('M3U listesinde veya gönderilerde oynatılabilir bir video kaynağı bulunamadı.');
            return;
        }

        await loadItem(0, false);
    };

    boot();
})();
</script>
@endsection
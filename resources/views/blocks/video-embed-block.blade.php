@php
    $url = trim((string) data_get($block->data, 'url'));
    $parts = $url !== '' ? parse_url($url) : false;
    $host = is_array($parts) ? strtolower($parts['host'] ?? '') : '';
    $path = is_array($parts) ? trim($parts['path'] ?? '', '/') : '';
    $videoId = null;
    if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
        parse_str($parts['query'] ?? '', $query);
        $videoId = is_string($query['v'] ?? null) ? $query['v'] : null;
        if (str_starts_with($path, 'embed/')) $videoId = substr($path, 6);
    } elseif ($host === 'youtu.be') {
        $videoId = explode('/', $path)[0] ?? null;
    } elseif (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)) {
        $videoId = explode('/', $path)[0] ?? null;
    }
    $isYoutube = in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'], true);
    $isVimeo = in_array($host, ['vimeo.com', 'www.vimeo.com'], true);
    $validId = is_string($videoId) && preg_match('/^[A-Za-z0-9_-]{6,20}$/', $videoId);
    $embedUrl = $validId ? ($isYoutube ? 'https://www.youtube-nocookie.com/embed/' . $videoId : ($isVimeo ? 'https://player.vimeo.com/video/' . $videoId : null)) : null;
@endphp
@if ($embedUrl)
    <figure class="my-6">
        <div class="aspect-video overflow-hidden rounded-lg bg-slate-100">
            <iframe src="{{ $embedUrl }}" title="{{ data_get($block->data, 'caption') ?: 'Gömülü video' }}"
                    loading="lazy" referrerpolicy="strict-origin-when-cross-origin"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen class="h-full w-full border-0"></iframe>
        </div>
        @if (filled(data_get($block->data, 'caption')))
            <figcaption class="mt-2 text-sm text-slate-500">{{ data_get($block->data, 'caption') }}</figcaption>
        @endif
    </figure>
@endif

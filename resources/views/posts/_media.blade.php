@php
    $videoEmbedUrl = null;
    $videoSourceUrl = trim((string) $post->video_url);
    if ($videoSourceUrl !== '' && parse_url($videoSourceUrl, PHP_URL_SCHEME) === 'https') {
        $host = strtolower((string) parse_url($videoSourceUrl, PHP_URL_HOST));
        $path = (string) parse_url($videoSourceUrl, PHP_URL_PATH);
        $query = [];
        parse_str((string) parse_url($videoSourceUrl, PHP_URL_QUERY), $query);
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            $id = $host === 'youtu.be' || $host === 'www.youtu.be' ? trim($path, '/') : ($query['v'] ?? (preg_match('~/(?:embed|shorts|live)/([A-Za-z0-9_-]{6,})~', $path, $m) ? $m[1] : ''));
            if (preg_match('/^[A-Za-z0-9_-]{6,}$/', $id)) $videoEmbedUrl = 'https://www.youtube-nocookie.com/embed/'.$id;
        } elseif (in_array($host, ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'fb.watch', 'www.fb.watch'], true)) {
            $videoEmbedUrl = 'https://www.facebook.com/plugins/video.php?href='.urlencode($videoSourceUrl).'&show_text=false';
        } elseif (in_array($host, ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com'], true) && preg_match('~/(?:[^/]+/status|i/web/status)/(\d+)~', $path, $m)) {
            $videoEmbedUrl = 'https://platform.twitter.com/embed/Tweet.html?dnt=true&theme=light&conversation=none&id='.$m[1];
        } elseif (in_array($host, ['reddit.com', 'www.reddit.com', 'old.reddit.com', 'redd.it', 'www.redd.it'], true)) {
            $videoEmbedUrl = 'https://www.redditmedia.com'.$path.'?ref_source=embed&ref=share&embed=true';
        } elseif (in_array($host, ['instagram.com', 'www.instagram.com'], true) && preg_match('~/(?:p|reel|tv)/([A-Za-z0-9_-]+)~', $path, $m)) {
            $videoEmbedUrl = 'https://www.instagram.com/'.$m[0].'/embed/';
        } elseif (in_array($host, ['tiktok.com', 'www.tiktok.com', 'm.tiktok.com'], true) && preg_match('~/video/(\d+)~', $path, $m)) {
            $videoEmbedUrl = 'https://www.tiktok.com/embed/v2/'.$m[1];
        }
    }
    $uploadedVideoUrl = $post->video_path ? asset('storage/'.ltrim($post->video_path, '/')) : null;
    $mediaUrls = collect(array_merge(
        array_filter([$post->coverImageUrl()]),
        $post->galleryImageUrls(),
    ))->unique()->values();
    $mediaGroup = 'post-images-'.$post->id;
    $censored = $post->is_adult && ! auth()->user()?->show_adult_content;
    $videoCensoredKey = 'post-'.$post->id.'-video';
    $imageCensoredKey = 'post-'.$post->id.'-images';
@endphp

@if ($uploadedVideoUrl || $videoEmbedUrl)
    <div class="post__media post-video-frame">
        @if ($censored)
            <div class="post__censored" data-censored="{{ $videoCensoredKey }}">
                <div class="text-center post__censored-content">
                    <span class="post__censored-info">18+ içerik</span>
                    <button type="button" class="btn btn-white btn-sm" data-censored-toggle="{{ $videoCensoredKey }}">
                        <x-repost-icon name="eye" size="18" /> Göster
                    </button>
                </div>
            </div>
        @endif
        @if ($uploadedVideoUrl)
            <video class="post-video-frame__video" controls playsinline preload="metadata">
                <source src="{{ $uploadedVideoUrl }}">
                Tarayıcınız video oynatmayı desteklemiyor.
            </video>
        @else
            <iframe
                src="{{ $videoEmbedUrl }}"
                title="{{ $post->title }} videosu"
                loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                referrerpolicy="strict-origin-when-cross-origin"
            ></iframe>
        @endif
    </div>
@endif

@if ($mediaUrls->count() > 1)
    <div class="post__media f-carousel is-media-loading" data-fancy-carousel>
        @if ($censored)
            <div class="post__censored" data-censored="{{ $imageCensoredKey }}">
                <div class="text-center post__censored-content">
                    <span class="post__censored-info">18+ content</span>
                    <button type="button" class="btn btn-white btn-sm" data-censored-toggle="{{ $imageCensoredKey }}">
                        <x-repost-icon name="eye" size="18" />
                        Show
                    </button>
                </div>
            </div>
        @endif
        @foreach ($mediaUrls as $mediaUrl)
            <div class="f-carousel__slide">
                <a href="{{ $mediaUrl }}" data-src="{{ $mediaUrl }}" data-fancybox="{{ $mediaGroup }}">
                    <img
                        src="{{ $mediaUrl }}"
                        alt="{{ $post->title }}"
                        class="post__media-image"
                        decoding="async"
                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                        draggable="false"
                    >
                </a>
            </div>
        @endforeach
        <button class="post__media-nav post__media-nav--prev" type="button" data-carousel-go-prev aria-label="Önceki görsel">
            <x-repost-icon name="chevron-left" size="18" />
        </button>
        <button class="post__media-nav post__media-nav--next" type="button" data-carousel-go-next aria-label="Sonraki görsel">
            <x-repost-icon name="chevron-right" size="18" />
        </button>
        <div class="post__media-loader" aria-hidden="true">
            <span class="loader"></span>
        </div>
    </div>
@elseif ($mediaUrls->isNotEmpty())
    <div class="post__media">
        @if ($censored)
            <div class="post__censored" data-censored="{{ $imageCensoredKey }}">
                <div class="text-center post__censored-content">
                    <span class="post__censored-info">18+ content</span>
                    <button type="button" class="btn btn-white btn-sm" data-censored-toggle="{{ $imageCensoredKey }}">
                        <x-repost-icon name="eye" size="18" />
                        Show
                    </button>
                </div>
            </div>
        @endif
        <a href="{{ $mediaUrls->first() }}" data-fancybox="{{ $mediaGroup }}">
            <img src="{{ $mediaUrls->first() }}" alt="{{ $post->title }}" class="post__media-image" loading="lazy">
        </a>
    </div>
@endif

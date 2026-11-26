@php
    $mediaUrls = collect(array_merge(
        array_filter([$post->coverImageUrl()]),
        ($coverOnly ?? false) ? [] : $post->galleryImageUrls(),
    ))->unique()->values();
    $mediaGroup = 'post-images-'.$post->id;
    $censored = $post->is_adult && ! auth()->user()?->show_adult_content;
    $videoCensoredKey = 'post-'.$post->id.'-video';
    $imageCensoredKey = 'post-'.$post->id.'-images';
@endphp

@if (filled($post->video_path))
    <div class="post__media post__media-video post-uploaded-video">
        <video controls playsinline preload="metadata" @if ($post->coverImageUrl()) poster="{{ $post->coverImageUrl() }}" @endif>
            <source src="{{ asset('storage/'.$post->video_path) }}" type="{{ str_ends_with(strtolower($post->video_path), '.webm') ? 'video/webm' : (str_ends_with(strtolower($post->video_path), '.mov') ? 'video/quicktime' : (str_ends_with(strtolower($post->video_path), '.mkv') ? 'video/x-matroska' : 'video/mp4')) }}">
            Tarayıcınız video oynatmayı desteklemiyor.
        </video>
    </div>
@endif

@if (filled($post->video_url))
    <div
        class="post__media post__media-video is-media-loading"
        data-player-url="{{ $post->video_url }}"
        data-player-script="{{ asset('assets/libs/playerjs/playerjs.js') }}"
    >
        @if ($censored)
            <div class="post__censored" data-censored="{{ $videoCensoredKey }}">
                <div class="text-center post__censored-content">
                    <span class="post__censored-info">18+ content</span>
                    <button type="button" class="btn btn-white btn-sm" data-censored-toggle="{{ $videoCensoredKey }}">
                        <x-repost-icon name="eye" size="18" />
                        Show
                    </button>
                </div>
            </div>
        @endif
        <div class="post__media-player-slide"></div>
        <div class="post__media-loader" aria-hidden="true">
            <span class="loader"></span>
        </div>
    </div>
@endif

@if ($mediaUrls->count() > 1)
    <div class="post__media f-carousel post__media-paired-carousel post__media-images" data-fancy-carousel data-paired-carousel aria-label="Gönderi görsel galerisi">
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
                <a href="{{ $mediaUrl }}" data-fancybox="{{ $mediaGroup }}" aria-label="Görseli büyüt">
                    <img src="{{ $mediaUrl }}" alt="{{ $post->title }} - {{ $loop->iteration }}" class="post__media-image" decoding="async" loading="{{ $loop->first ? 'eager' : 'lazy' }}" draggable="false">
                </a>
            </div>
        @endforeach
        <button class="post__media-nav post__media-nav--prev" type="button" data-carousel-go-prev aria-label="Önceki görsel"><x-repost-icon name="chevron-left" size="18" /></button>
        <button class="post__media-nav post__media-nav--next" type="button" data-carousel-go-next aria-label="Sonraki görsel"><x-repost-icon name="chevron-right" size="18" /></button>
    </div>
@elseif ($mediaUrls->count() === 1)
    <div class="post__media f-carousel post__media-images" data-fancy-carousel>
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

@if ($post->is_panorama && $post->coverImageUrl() && ! $censored)
    <div class="ografi-panorama-action">
        <a class="btn btn-secondary btn-sm ografi-panorama-trigger" href="{{ $post->coverImageUrl() }}" data-ografi-panorama aria-haspopup="dialog">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><ellipse cx="12" cy="12" rx="10" ry="5"/><path d="M2 12v4c0 2.8 4.5 5 10 5s10-2.2 10-5v-4M12 7V3m-3 3 3-3 3 3"/></svg>
            <span>280° Keşfet</span>
        </a>
    </div>
@endif

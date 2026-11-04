@php
    $mediaUrls = collect(array_merge(
        array_filter([$post->coverImageUrl()]),
        $post->galleryImageUrls(),
    ))->unique()->values();
    $mediaGroup = 'post-images-'.$post->id;
    $censored = $post->is_adult && ! auth()->user()?->show_adult_content;
    $censoredKey = 'post-'.$post->id.'-media';
@endphp

@if (filled($post->video_url))
    <div
        class="post__media post__media-video is-media-loading"
        data-player-url="{{ $post->video_url }}"
        data-player-script="{{ asset('assets/libs/playerjs/playerjs.js') }}"
    >
        @if ($censored)
            <div class="post__censored" data-censored="{{ $censoredKey }}">
                <div class="text-center post__censored-content">
                    <span class="post__censored-info">18+ content</span>
                    <button type="button" class="btn btn-white btn-sm" data-censored-toggle="{{ $censoredKey }}">
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
    <div class="post__media f-carousel is-media-loading" data-fancy-carousel>
        @if ($censored)
            <div class="post__censored" data-censored="{{ $censoredKey }}">
                <div class="text-center post__censored-content">
                    <span class="post__censored-info">18+ content</span>
                    <button type="button" class="btn btn-white btn-sm" data-censored-toggle="{{ $censoredKey }}">
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
            <div class="post__censored" data-censored="{{ $censoredKey }}">
                <div class="text-center post__censored-content">
                    <span class="post__censored-info">18+ content</span>
                    <button type="button" class="btn btn-white btn-sm" data-censored-toggle="{{ $censoredKey }}">
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

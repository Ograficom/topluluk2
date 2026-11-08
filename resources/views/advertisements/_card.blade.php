@php
    $preview = $preview ?? false;
    $post = $ad->post;
    $detailUrl = $preview ? null : route('advertisements.show', $ad);
@endphp

<article class="card post post--sponsored">
    <section class="card-body">
        <header class="post__header">
            <div class="user-box">
                @if ($ad->user)
                    <a href="{{ $preview ? '#' : route('profiles.show', $ad->user) }}" class="avatar">
                        <img src="{{ $ad->user->avatarUrl() }}" alt="" width="40" height="40" loading="lazy">
                    </a>
                @else
                    <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                @endif
                <div class="user-box-content">
                    <span class="name">
                        @if ($preview)
                            <span class="name-text">{{ $ad->user?->displayName() ?? 'OgraFi reklamvereni' }}</span>
                        @else
                            <a class="name-text" href="{{ $ad->user ? route('profiles.show', $ad->user) : route('posts.index') }}">{{ $ad->user?->displayName() ?? 'OgraFi reklamvereni' }}</a>
                        @endif
                    </span>
                    <div class="meta">
                        <span>Sponsorlu içerik</span>
                        <time datetime="{{ $ad->created_at?->toAtomString() }}">{{ $ad->created_at?->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
        </header>
        <div class="post__short">
            <h2 class="post__short-title h3">
                @if ($preview)
                    <span class="color-title"><span class="post__short-title-text">{{ $ad->title }}</span></span>
                @else
                    <a href="{{ $detailUrl }}" class="color-title"><span class="post__short-title-text">{{ $ad->title }}</span></a>
                @endif
            </h2>
            <p class="text-clamp-3 color-text">{{ $ad->description }}</p>
        </div>
    </section>

    @if ($post && ($post->coverImageUrl() || count($post->galleryImageUrls()) > 0 || filled($post->video_url)))
        <section class="card-body post__short-media">
            @include('posts._media', ['post' => $post])
        </section>
    @elseif ($ad->imageUrl())
        <section class="card-body post__short-media">
            <div class="post__media">
                @if ($preview)
                    <img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" class="post__media-image" loading="lazy">
                @else
                    <a href="{{ $detailUrl }}"><img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" class="post__media-image" loading="lazy"></a>
                @endif
            </div>
        </section>
    @endif

    <footer class="post__footer card-body">
        <div class="post__footer-stats small color-gray">
            @if ($post)
                <span>{{ number_format($post->likes_count ?? 0) }} beğeni</span>
                <span>{{ number_format($post->views ?? 0) }} görüntülenme</span>
            @else
                <span>Sponsorlu içerik</span>
            @endif
        </div>
        @if ($preview)
            <span class="small color-gray">Reklam önizlemesi</span>
        @else
            <a class="btn btn-primary btn-sm" href="{{ $detailUrl }}">Ayrıntıları görüntüle</a>
        @endif
    </footer>
</article>

@php($preview = $preview ?? false)

<article class="card post post--sponsored">
    <section class="card-body">
        <header class="post__header">
            <div class="user-box">
                <a href="{{ $preview ? '#' : ($ad->user ? route('profiles.show', $ad->user) : route('posts.index')) }}" class="avatar" @if($preview) aria-label="Reklam önizlemesi" @endif>
                    <img src="{{ $ad->user?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="" width="40" height="40" loading="lazy">
                </a>
                <div class="user-box-content">
                    <span class="name">
                        @if ($preview)
                            <span class="name-text">{{ $ad->user?->displayName() ?? 'OgraFi reklamvereni' }}</span>
                        @else
                            <a class="name-text" href="{{ $ad->user ? route('profiles.show', $ad->user) : route('posts.index') }}">{{ $ad->user?->displayName() ?? 'OgraFi reklamvereni' }}</a>
                        @endif
                    </span>
                    <div class="meta">
                        <span>Sponsorlu</span>
                        <time datetime="{{ $ad->starts_at?->toAtomString() }}">{{ $ad->starts_at?->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
        </header>

        <div class="post__short">
            <h2 class="post__short-title h3">
                @if ($preview)
                    <span class="color-title"><span class="post__short-title-text">{{ $ad->title }}</span></span>
                @else
                    <a href="{{ route('advertisements.click', $ad) }}" class="color-title" rel="sponsored nofollow">
                        <span class="post__short-title-text">{{ $ad->title }}</span>
                    </a>
                @endif
            </h2>
            <p class="text-clamp-3 color-text">{{ $ad->description }}</p>
        </div>
    </section>

    @if ($ad->imageUrl())
        <section class="card-body post__short-media">
            <div class="post__media">
                @if ($preview)
                    <img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" class="post__media-image" loading="lazy">
                @else
                    <a href="{{ route('advertisements.click', $ad) }}" rel="sponsored nofollow">
                        <img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" class="post__media-image" loading="lazy">
                    </a>
                @endif
            </div>
        </section>
    @elseif ($ad->video_url)
        <section class="card-body post__short-media">
            <div class="post__media">
                @if ($preview)
                    <span class="color-gray">{{ $ad->video_url }}</span>
                @else
                    <a href="{{ route('advertisements.click', $ad) }}" rel="sponsored nofollow">{{ $ad->video_url }}</a>
                @endif
            </div>
        </section>
    @endif

    <footer class="post__footer card-body">
        <span class="small color-gray">Sponsorlu içerik</span>
        @if ($preview)
            <span class="small color-gray">Reklam önizlemesi</span>
        @else
            <a class="btn btn-primary btn-sm" href="{{ route('advertisements.click', $ad) }}" rel="sponsored nofollow">{{ $ad->button_text }}</a>
        @endif
    </footer>
</article>

<article class="card post">
    <section class="card-body">
        <header class="post__header">
            <div class="user-box">
                <a href="{{ $ad->user ? route('profiles.show', $ad->user) : route('posts.index') }}" class="avatar">
                    <img src="{{ $ad->user?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="" width="40" height="40" loading="lazy">
                </a>
                <div class="user-box-content">
                    <span class="name"><a class="name-text" href="{{ $ad->user ? route('profiles.show', $ad->user) : route('posts.index') }}">{{ $ad->user?->displayName() ?? 'OgraFi reklamvereni' }}</a></span>
                    <div class="meta"><span>Sponsorlu</span><time>{{ $ad->starts_at->diffForHumans() }}</time></div>
                </div>
            </div>
        </header>
        <div class="post__short">
            <h2 class="post__short-title h3"><span class="color-title">{{ $ad->title }}</span></h2>
            <p class="color-text">{{ $ad->description }}</p>
        </div>
    </section>
    @if ($ad->imageUrl())
        <section class="card-body post__short-media">
            <a href="{{ route('advertisements.click', $ad) }}" rel="sponsored nofollow">
                <img src="{{ $ad->imageUrl() }}" alt="{{ $ad->title }}" loading="lazy" style="display:block;width:100%;height:auto;">
            </a>
        </section>
    @elseif ($ad->video_url)
        <section class="card-body post__short-media"><a href="{{ route('advertisements.click', $ad) }}" rel="sponsored nofollow">{{ $ad->video_url }}</a></section>
    @endif
    <footer class="card-body d-flex justify-content-between align-items-center">
        <span class="small color-gray">Sponsorlu içerik</span>
        <a class="btn btn-primary btn-sm" href="{{ route('advertisements.click', $ad) }}" rel="sponsored nofollow">{{ $ad->button_text }}</a>
    </footer>
</article>

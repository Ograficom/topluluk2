<article class="card post">
    <section class="card-body">
        <header class="post__header">
            <div class="user-box">
                @if ($post->user)
                    <a href="{{ route('profiles.show', $post->user) }}" class="avatar"><img src="{{ $post->user->avatarUrl() }}" alt="" width="40" height="40" loading="lazy"></a>
                @else
                    <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                @endif
                <div class="user-box-content">
                    <a href="{{ $post->user ? route('profiles.show', $post->user) : route('posts.index') }}" class="name">
                        <span class="name-text">{{ $post->user?->displayName() ?? 'OgraFi yazarı' }}</span>
                        @if ($post->user?->hasVerifiedCompanyBadge())
                    <x-company-verified-badge size="14" />
                @endif
                @if ($post->user?->hasVerifiedBadge())<span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>@endif
                    </a>
                    <div class="meta">
                        @if ($post->channel)<a href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>@endif
                        <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
        </header>
        <div class="post__short">
            <h2 class="post__short-title h3"><a href="{{ route('posts.show', $post) }}" class="color-title">{{ $post->title }}</a></h2>
            <p class="text-clamp-3 color-text">{{ $post->excerpt }}</p>
            @if ($post->tagRecords->isNotEmpty())
                <div class="post__tags">
                    @foreach ($post->tagRecords as $tag)
                        <a href="{{ route('tags.show', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @elseif ($post->tags)
                <div class="post__tags">
                    @foreach ($post->tags as $tag)<a href="{{ route('tags.show', ['tag' => \Illuminate\Support\Str::slug($tag)]) }}">{{ $tag }}</a>@endforeach
                </div>
            @endif
        </div>
    </section>
    @if ($post->coverImageUrl() || count($post->galleryImageUrls()) > 0 || filled($post->video_url))
        <section class="card-body post__short-media">
            @include('posts._media', ['post' => $post])
        </section>
    @endif
    <footer class="post__footer card-body">
        <div class="post__footer-stats small color-gray">
            <span>{{ number_format($post->likes_count ?? 0) }} beğeni</span>
            <span>{{ number_format($post->comments_count ?? 0) }} yorum</span>
            <span>{{ number_format($post->views) }} görüntülenme</span>
        </div>
        <a class="small color-gray" href="{{ route('posts.show', $post) }}">Yazıyı görüntüle</a>
    </footer>
    @include('posts._interactions', ['post' => $post])
</article>

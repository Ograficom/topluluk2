@extends('layouts.app')

@section('title', $channel->name)

@section('content')
    <div class="channel-page">
        <section class="channel card card-body">
            <div class="channel__icon">
                @if ($channel->image_path)
                    <img src="{{ asset('storage/'.$channel->image_path) }}" alt="{{ $channel->name }}" width="64" height="64">
                @else
                    <x-repost-icon name="image" size="24" />
                @endif
            </div>
            <div class="channel__info">
                <div class="channel__overview">
                    <div class="channel__content">
                        <h1 class="channel__title h3">{{ $channel->name }}</h1>
                        <div class="channel__description trim" data-trim data-trim-limit="2">
                            <div class="channel__description-text trim__content" data-trim-content>{{ $channel->description }}</div>
                            <button class="trim__toggle" data-trim-toggle type="button" hidden>
                                <span data-trim-label>Daha fazla göster</span>
                                <x-repost-icon name="chevron-down" size="14" />
                            </button>
                        </div>
                    </div>
                    <aside class="channel__side">
                        @auth
                            <form action="{{ route('channels.subscribe', $channel) }}" method="POST">
                                @csrf
                                <button class="btn {{ $isSubscribed ? 'btn-secondary' : 'btn-primary' }}" type="submit">
                                    {{ $isSubscribed ? 'Abonelikten çık' : 'Abone ol' }}
                                </button>
                            </form>
                        @else
                            <a class="btn btn-primary" href="{{ route('login') }}">Abone ol</a>
                        @endauth
                        <div class="channel__authors">
                            <span class="channel__authors-title">Aktif yazarlar:</span>
                            <ul class="user-set">
                                @foreach ($posts->getCollection()->pluck('user')->filter()->unique('id')->take(5) as $author)
                                    <li>
                                        <a class="avatar" href="{{ route('profiles.show', $author) }}" aria-label="{{ $author->displayName() }}">
                                            <img src="{{ $author->avatarUrl() }}" alt="" width="30" height="30" loading="lazy">
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </aside>
                </div>
                <div class="channel__meta small color-gray">
                    <a href="{{ route('channels.index') }}">Tüm kanallar</a>
                    <span> · {{ number_format($channel->subscribers_count) }} abone</span>
                </div>
                <div class="channel__tabs">
                    <nav class="tabs-nav" data-tabs-nav="channel" role="tablist" aria-label="Kanal bölümleri">
                        <button class="tabs-nav__button is-active" id="channel-tab-posts" type="button" role="tab" data-tab="posts" aria-controls="channel-panel-posts" aria-selected="true">
                            Yazılar <span>{{ number_format($posts->total()) }}</span>
                        </button>
                        <button class="tabs-nav__button" id="channel-tab-comments" type="button" role="tab" data-tab="comments" aria-controls="channel-panel-comments" aria-selected="false" tabindex="-1">
                            Yorumlar <span>{{ number_format($comments->total()) }}</span>
                        </button>
                    </nav>
                </div>
            </div>
        </section>
    </div>

    @php($channelTags = $posts->getCollection()->flatMap(fn ($post) => $post->tags ?? [])->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')->unique()->take(18))
    <div class="channel-page__content tabs-content" data-tabs-container="channel">
        <section class="tabs-panel is-active" id="channel-panel-posts" role="tabpanel" aria-labelledby="channel-tab-posts" data-tab-panel="posts" tabindex="0">
        <div class="channel__posts">
            <div class="channel__hero">
                <div class="channel__hero-start">
                    <span class="channel__hero-count">
                        <x-repost-icon name="copy" size="17" />
                        {{ number_format($posts->total()) }} yazı
                    </span>
                </div>
                @if ($channelTags->isNotEmpty())
                    <div class="channel__tags">
                        <div class="channel__tags-list">
                            @foreach ($channelTags as $tag)
                                <a href="{{ route('tags.show', ['tag' => $tag]) }}">{{ $tag }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="feed">
        @forelse ($posts as $post)
            <article class="card post">
            <section class="card-body">
                <header class="post__header">
                    <div class="user-box">
                        @if ($post->user)
                            <a href="{{ route('profiles.show', $post->user) }}" class="avatar"><img src="{{ $post->user->avatarUrl() }}" alt="" width="40" height="40"></a>
                        @else
                            <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                        @endif
                        <div class="user-box-content">
                            <span class="name">
                                @if ($post->user)<a class="name-text" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->displayName() }}</a>@else<span class="name-text">Repost yazarı</span>@endif
                                @if ($post->user?->hasVerifiedBadge())<span class="verify" role="img" aria-label="Verified profile"><x-repost-icon name="verify" size="14" /></span>@endif
                            </span>
                            <div class="meta"><time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}</time></div>
                        </div>
                    </div>
                </header>
                <div class="post__short">
                    <h2 class="post__short-title h3"><a href="{{ route('posts.show', $post) }}" class="color-title"><span class="post__short-title-text">{{ $post->title }}</span></a></h2>
                    <p class="text-clamp-3 color-text">{{ $post->excerpt }}</p>
                </div>
            </section>
            @if ($post->coverImageUrl() || count($post->galleryImageUrls()) > 0 || filled($post->video_url))
                <section class="card-body post__short-media">
                    @include('posts._media', ['post' => $post])
                </section>
            @endif
            <footer class="post__footer card-body">
                <div class="post__footer-stats small color-gray"><span>{{ number_format($post->likes_count) }} beğeni</span><span>{{ number_format($post->views) }} görüntülenme</span></div>
                @if ($post->user?->publicUsername())
                    <span class="small color-gray">{{ '@'.$post->user->publicUsername() }}</span>
                @endif
            </footer>
            @include('posts._interactions', ['post' => $post])
            </article>
        @empty
            <section class="card card-body empty-state">
                <h2 class="h4">Bu kanalda henüz yazı yok</h2>
                <p class="color-gray">İlk yazılar yayınlandığında burada görünecek.</p>
            </section>
        @endforelse
        @include('layouts._pagination', ['paginator' => $posts])
            </div>
        </div>
        </section>
        <section class="tabs-panel" id="channel-panel-comments" role="tabpanel" aria-labelledby="channel-tab-comments" data-tab-panel="comments" tabindex="0">
            <div class="comments-tree-list">
                @forelse ($comments as $comment)
                    @include('posts._comment', ['comment' => $comment, 'post' => $comment->post, 'allowModeration' => false])
                @empty
                    <section class="card card-body empty-state">
                        <h2 class="h4">Henüz yorum yok</h2>
                        <p class="color-gray">Bu kanaldaki yazılara yapılan yorumlar burada görünecek.</p>
                    </section>
                @endforelse
            </div>
            @include('layouts._pagination', ['paginator' => $comments])
        </section>
    </div>
@endsection

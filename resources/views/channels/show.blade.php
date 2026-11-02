@extends('layouts.app')

@section('title', $channel->name)

@section('content')
    <div class="channel-page">
        <section class="channel card card-body">
            <div class="channel__icon"><x-repost-icon name="image" size="24" /></div>
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
                </div>
                <div class="channel__meta small color-gray"><a href="{{ route('channels.index') }}">Tüm kanallar</a></div>
            </div>
        </section>
    </div>

    <div class="channel-page__content">
        <div class="channel__posts">
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
            @if ($post->coverImageUrl())
                <section class="card-body post__short-media">
                    <div class="post__media">
                        <a href="{{ route('posts.show', $post) }}">
                            <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->title }}" class="post__media-image" loading="lazy">
                        </a>
                    </div>
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
@endsection

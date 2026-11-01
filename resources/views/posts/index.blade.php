@extends('layouts.app')

@section('title', filled($search) ? 'Arama sonuçları' : 'Akış')

@section('content')
    <div class="hero__header-main">
        <div>
            <h1 class="h3 feed-title">{{ filled($search) ? 'Arama sonuçları' : 'Keşfet' }}</h1>
            @if (filled($search))
                <p class="color-gray">“{{ $search }}” için bulunan yazılar</p>
            @else
                <p class="color-gray">Topluluktan yeni hikâyeler ve fikirler</p>
            @endif
        </div>
        @if (filled($search))
            <a class="btn btn-secondary-ghost btn-sm" href="{{ route('posts.index') }}">Aramayı temizle</a>
        @endif
    </div>

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
                                @if ($post->user)<a class="name-text" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->full_name ?: $post->user->name }}</a>@else<span class="name-text">Repost yazarı</span>@endif
                                @if ($post->user?->hasVerifiedBadge())<span class="verify" role="img" aria-label="Verified profile"><x-repost-icon name="verify" size="14" /></span>@endif
                            </span>
                            <div class="meta">
                                @if ($post->channel)
                                    <a href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>
                                @endif
                                <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}</time>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="post__short">
                    <h2 class="post__short-title h3">
                        <a href="{{ route('posts.show', $post) }}" class="color-title">{{ $post->title }}</a>
                    </h2>
                    <p class="text-clamp-3 color-text">{{ $post->excerpt }}</p>
                    @if (filled($post->tags))
                        <div class="post__tags">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('posts.index', ['q' => $tag]) }}">{{ $tag }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            @if ($post->coverImageUrl())
                <section class="card-body post__short-media">
                    <div class="post__media">
                        <a href="{{ route('posts.show', $post) }}">
                            <img
                                src="{{ $post->coverImageUrl() }}"
                                alt="{{ $post->title }}"
                                class="post__media-image"
                                loading="lazy"
                            >
                        </a>
                    </div>
                </section>
            @endif

            <footer class="post__footer card-body">
                <div class="post__footer-stats small color-gray">
                    <span>{{ number_format($post->views) }} görüntülenme</span>
                    <span>{{ number_format($post->likes_count) }} beğeni</span>
                </div>
                <a class="small color-gray" href="{{ route('posts.show', $post) }}">Yazıyı oku</a>
            </footer>
            @include('posts._interactions', ['post' => $post])
        </article>
    @empty
        <section class="card card-body empty-state">
            <h2 class="h4">Henüz yayınlanmış yazı yok</h2>
            <p class="color-gray">
                {{ filled($search) ? 'Aramanızla eşleşen bir yazı bulunamadı.' : 'İlk yazılar yayınlandığında burada görünecek.' }}
            </p>
        </section>
    @endforelse

    {{ $posts->links() }}
@endsection

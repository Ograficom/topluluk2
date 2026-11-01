@extends('layouts.app')

@section('title', filled($search) ? 'Arama sonuçları' : 'Akış')

@section('content')
    <section aria-label="Akış ayarları">
        <div class="hero__header-main">
            <div>
                <div class="dropdown dropdown-md">
                    <button class="hero__toggle" type="button" data-dropdown-toggle aria-expanded="false">
                        <x-repost-icon name="feed" size="16" />
                        <span class="hero__toggle-text">{{ filled($search) ? 'Arama sonuçları' : 'Son yazılar' }}</span>
                        <x-repost-icon name="chevron-down" size="12" />
                    </button>
                    <ul class="dropdown-menu">
                        <li class="dropdown-menu-item dropdown-menu-item-sm is-active"><a href="{{ route('posts.index') }}">Son yazılar</a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['sort' => 'popular']) }}">Popüler</a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['sort' => 'views']) }}">En çok okunan</a></li>
                    </ul>
                </div>
                <div class="dropdown dropdown-sm">
                    <button class="hero__toggle" type="button" data-dropdown-toggle aria-expanded="false">
                        <x-repost-icon name="calendar" size="16" />
                        <span class="hero__toggle-text">Tüm zamanlar</span>
                        <x-repost-icon name="chevron-down" size="12" />
                    </button>
                    <ul class="dropdown-menu">
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['period' => 'day']) }}">Son 24 saat</a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['period' => 'week']) }}">Bu hafta</a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['period' => 'month']) }}">Bu ay</a></li>
                    </ul>
                </div>
            </div>
            <div class="dropdown">
                <button class="hero__toggle" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Sıralama seçenekleri">
                    <x-repost-icon name="sort" size="17" />
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['sort' => 'latest']) }}">En yeni</a></li>
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['sort' => 'popular']) }}">Popüler</a></li>
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index', ['sort' => 'views']) }}">Görüntülenme</a></li>
                </ul>
            </div>
        </div>
    </section>
    <div class="hero__digest card card-body">
        @forelse ($posts->take(4) as $digestPost)
            <div class="digest">
                <a class="digest__title" href="{{ route('posts.show', $digestPost) }}">{{ $digestPost->title }}</a>
                <span class="digest__comm">
                    <span class="digest__comm-icon"><x-repost-icon name="comment" size="12" /></span>
                    {{ number_format($digestPost->comments_count ?? 0) }}
                </span>
            </div>
        @empty
            <div class="digest"><span class="color-gray">Henüz öne çıkan yazı yok.</span></div>
        @endforelse
    </div>
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
                                @if ($post->user)<a class="name-text" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->displayName() }}</a>@else<span class="name-text">Repost yazarı</span>@endif
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

@extends('layouts.app')

@section('title', $user->displayName())

@section('content')
    <article>
        <section class="profile card card-body">
            @auth
                <div class="edit">
                    <div class="dropdown dropdown-md">
                        <button class="btn btn-secondary btn-xs btn-rounded" type="button" data-dropdown-toggle aria-expanded="false" aria-controls="profile-edit-menu" aria-label="Profil menüsünü aç">
                            <x-repost-icon name="more" size="18" class="color-gray" />
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="profile-edit-menu">
                            @if (auth()->user()->is($user))
                                <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('settings.edit') }}">Ayarlar</a></li>
                                <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.create') }}">Yazı oluştur</a></li>
                            @elseif (! $isBlocked)
                                <li class="dropdown-menu-item dropdown-menu-item-sm">
                                    <form action="{{ route('users.block', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit">Kullanıcıyı engelle</button>
                                    </form>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endauth

            <div class="profile__avatar {{ $user->isOnline() ? 'profile__online' : '' }}">
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->displayName() }}" width="90" height="90">
            </div>
            <div class="profile__content">
                <div class="profile__info">
                    <div class="profile__name">
                        <h1 class="h3">{{ $user->displayName() }}</h1>
                        @if ($user->hasVerifiedBadge())
                            <span class="verify" data-tooltip="Doğrulanmış profil" role="img" aria-label="Doğrulanmış profil">
                                <x-repost-icon name="verify" size="14" class="color-primary" />
                            </span>
                        @endif
                    </div>
                    <div class="profile__meta">
                        @if ($topPosition)
                            <a href="{{ route('members.index') }}" class="profile__top">
                                <x-repost-icon name="bolt" size="20" />
                                Top {{ $topPosition }}
                            </a>
                        @endif
                        @if ($publicUsername = $user->publicUsername())
                            <span class="small color-gray">{{ '@'.$publicUsername }}</span>
                        @endif
                        <span class="small color-gray">Üyelik tarihi {{ $user->created_at->format('d.m.Y') }}</span>
                    </div>
                    <div class="profile__meta">
                        <span class="small color-gray">{{ number_format($posts->total()) }} yazı</span>
                        <span class="small color-gray">{{ number_format($followersCount) }} takipçi</span>
                        <span class="small color-gray">{{ number_format($followingCount) }} takip</span>
                    </div>
                    @if (auth()->check() && ! auth()->user()->is($user))
                        <div class="profile__actions">
                            @unless ($isBlocked)
                                <form action="{{ route('users.follow', $user) }}" method="POST">
                                    @csrf
                                    <button class="btn {{ $isFollowing ? 'btn-secondary' : 'btn-primary' }} btn-md" type="submit">
                                        {{ $isFollowing ? 'Takibi bırak' : 'Takip et' }}
                                    </button>
                                </form>
                                <form action="{{ route('messenger.start', $user) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-secondary btn-md" type="submit"><x-repost-icon name="message" size="16" /> Mesaj gönder</button>
                                </form>
                            @endunless
                        </div>
                    @elseif (! auth()->check())
                        <div class="profile__actions"><a class="btn btn-primary btn-md" href="{{ route('login') }}">Takip etmek için giriş yap</a></div>
                    @endif
                    @if ($user->signature)
                        <div class="profile__signature">{{ $user->signature }}</div>
                    @endif
                </div>
            </div>
            <nav class="tabs-nav profile__nav" aria-label="Profil bölümleri" data-tabs-nav="profile">
                <button class="tabs-nav__button is-active" id="profile-tab-posts" type="button" data-tab="posts" aria-selected="true" aria-controls="profile-panel-posts">
                    <span>Yazılar ({{ $posts->total() }})</span>
                </button>
                <button class="tabs-nav__button" id="profile-tab-comments" type="button" data-tab="comments" aria-selected="false" aria-controls="profile-panel-comments" tabindex="-1">
                    <span>Yorumlar ({{ $comments->total() }})</span>
                </button>
            </nav>
        </section>

        <div class="main main__grid-feed">
            <div>
                <div class="tabs-content" data-tabs-container="profile">
                <section class="tabs-panel is-active" id="profile-panel-posts" role="tabpanel" aria-labelledby="profile-tab-posts" data-tab-panel="posts" tabindex="0">
                    <div class="feed">
                    @forelse ($posts as $post)
                        <article class="card post">
                            <section class="card-body">
                                <header class="post__header">
                                    <div class="user-box">
                                        <a href="{{ route('profiles.show', $user) }}" class="avatar">
                                            <img src="{{ $user->avatarUrl() }}" alt="" width="40" height="40">
                                        </a>
                                        <div class="user-box-content">
                                            <a href="{{ route('profiles.show', $user) }}" class="name">
                                                <span class="name-text">{{ $user->displayName() }}</span>
                                                @if ($user->hasVerifiedBadge())
                                                    <span class="verify" role="img" aria-label="Doğrulanmış profil"><x-repost-icon name="verify" size="14" /></span>
                                                @endif
                                            </a>
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
                                    <h2 class="post__short-title h3"><a href="{{ route('posts.show', $post) }}" class="color-title"><span class="post__short-title-text">{{ $post->title }}</span></a></h2>
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
                            @if ($post->coverImageUrl() || count($post->galleryImageUrls()) > 0 || filled($post->video_url))
                                <section class="card-body post__short-media">
                                    @include('posts._media', ['post' => $post])
                                </section>
                            @endif
                            <footer class="post__footer card-body">
                                <div class="post__footer-stats small color-gray">
                                    <span>{{ number_format($post->likes_count) }} beğeni</span>
                                    <span>{{ number_format($post->views) }} görüntülenme</span>
                                </div>
                                @if ($post->user?->publicUsername())
                                    <span class="small color-gray">{{ '@'.$post->user->publicUsername() }}</span>
                                @endif
                            </footer>
                            @include('posts._interactions', ['post' => $post])
                        </article>
                    @empty
                        <section class="card card-body empty-state"><h2 class="h4">Henüz yazı yok</h2></section>
                    @endforelse
                    @include('layouts._pagination', ['paginator' => $posts])
                    </div>
                </section>

                <section class="tabs-panel" id="profile-panel-comments" role="tabpanel" aria-labelledby="profile-tab-comments" data-tab-panel="comments" tabindex="0" hidden>
                    <div class="feed">
                    @forelse ($comments as $comment)
                        <article class="comment card card-body">
                            <div class="user-box">
                                <a href="{{ route('profiles.show', $user) }}" class="avatar">
                                    <img src="{{ $user->avatarUrl() }}" alt="" width="40" height="40">
                                </a>
                                <div class="user-box-content">
                                    <a href="{{ route('profiles.show', $user) }}" class="name">
                                        <span class="name-text">{{ $user->displayName() }}</span>
                                        @if ($user->hasVerifiedBadge())
                                            <span class="verify" role="img" aria-label="Doğrulanmış profil"><x-repost-icon name="verify" size="14" /></span>
                                        @endif
                                    </a>
                                    <div class="meta"><time datetime="{{ $comment->created_at->toAtomString() }}">{{ $comment->created_at->diffForHumans() }}</time></div>
                                </div>
                            </div>
                            <div class="comment__content">
                                <div class="comment__text">{{ $comment->body }}</div>
                                @if ($comment->post)
                                    <div class="comment__news">
                                        <span>Yazı:</span>
                                        <a href="{{ route('posts.show', $comment->post) }}" class="link">{{ $comment->post->title }}</a>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <section class="card card-body empty-state"><h2 class="h4">Henüz yorum yok</h2></section>
                    @endforelse
                    @include('layouts._pagination', ['paginator' => $comments])
                    </div>
                </section>
                </div>
            </div>

            <aside class="profile__aside">
                <div class="widgets">
                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">Aboneler</h2></header>
                        <div class="widget__content widget__content--bleed">
                            <ul class="channel-subscriber-grid">
                                @forelse ($profileFollowers as $follower)
                                    <li>
                                        <a class="avatar {{ $follower->isOnline() ? 'online' : '' }}" href="{{ route('profiles.show', $follower) }}" aria-label="{{ $follower->displayName() }}" title="{{ $follower->displayName() }}">
                                            <img src="{{ $follower->avatarUrl() }}" alt="" width="40" height="40">
                                        </a>
                                    </li>
                                @empty
                                    <li class="small color-gray">Henüz abone yok.</li>
                                @endforelse
                            </ul>
                        </div>
                        <footer class="widget__footer">
                            @if ($followersCount > 8)
                                <a class="link-accent" href="#profileSubscribersModal" data-modal-btn="profile-subscribers" aria-haspopup="dialog" aria-controls="profileSubscribersModal">Tüm aboneleri gör</a>
                            @else
                                <span class="small color-gray">{{ number_format($followersCount) }} toplam takipçi</span>
                            @endif
                        </footer>
                    </section>
                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">Hakkında</h2></header>
                        <div class="widget__content profile__about">
                            @if ($user->website)
                                <a href="{{ $user->website }}" class="profile__site" target="_blank" rel="noopener noreferrer">
                                    <x-repost-icon name="link" size="16" />
                                    <span class="link">{{ parse_url($user->website, PHP_URL_HOST) ?: $user->website }}</span>
                                </a>
                            @endif
                            <div class="profile__about-info">
                                @if ($user->bio)
                                    <div class="trim" data-trim data-trim-limit="3" data-trim-link-text="Gizle">
                                        <div class="trim__content" data-trim-content>{{ $user->bio }}</div>
                                        <button type="button" class="trim__toggle" data-trim-toggle hidden>
                                            <span data-trim-label>Daha fazla göster</span>
                                            <x-repost-icon name="chevron-down" size="14" />
                                        </button>
                                    </div>
                                @endif
                                @if ($user->instagram || $user->telegram || $user->threads || $user->youtube)
                                    <div class="profile__social">
                                        @if ($user->instagram)
                                            <a class="btn btn-md btn-outline-secondary btn-square" href="https://instagram.com/{{ rawurlencode(ltrim($user->instagram, '@')) }}" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><x-repost-icon name="instagram" size="16" /></a>
                                        @endif
                                        @if ($user->telegram)
                                            <a class="btn btn-md btn-outline-secondary btn-square" href="https://t.me/{{ rawurlencode(ltrim($user->telegram, '@')) }}" aria-label="Telegram" target="_blank" rel="noopener noreferrer"><x-repost-icon name="telegram" size="16" /></a>
                                        @endif
                                        @if ($user->threads)
                                            <a class="btn btn-md btn-outline-secondary btn-square" href="https://threads.net/@{{ rawurlencode(ltrim($user->threads, '@')) }}" aria-label="Threads" target="_blank" rel="noopener noreferrer"><x-repost-icon name="threads" size="16" /></a>
                                        @endif
                                        @if ($user->youtube)
                                            <a class="btn btn-md btn-outline-secondary btn-square" href="https://youtube.com/@{{ rawurlencode(ltrim($user->youtube, '@')) }}" aria-label="YouTube" target="_blank" rel="noopener noreferrer"><x-repost-icon name="youtube" size="16" /></a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">Profil istatistikleri</h2></header>
                        <div class="widget__content">
                            <div class="profile__meta">
                                <span class="small color-gray">{{ number_format($posts->total()) }} yazı</span>
                                <span class="small color-gray">{{ number_format($comments->total()) }} yorum</span>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="profile__aside-footer">
                    @include('layouts._footer')
                </div>
            </aside>
        </div>

        <div class="modal" id="profileSubscribersModal" data-modal="profile-subscribers" aria-label="Tüm aboneler" aria-hidden="true">
            <div class="modal-content">
                <button type="button" class="btn btn-secondary btn-md btn-circle modal-close" data-modal-close aria-label="Kapat" title="Kapat">
                    <x-repost-icon name="close" size="16" class="color-gray" />
                </button>
                <header class="modal-header">
                    <h2 class="modal-title h4" id="profileSubscribersTitle">Tüm aboneler</h2>
                </header>
                <div class="modal-body">
                    <ul class="channel__subscribers">
                        @forelse ($allProfileFollowers as $follower)
                            <li class="user-card">
                                <a class="user-box" href="{{ route('profiles.show', $follower) }}">
                                    <span class="avatar {{ $follower->isOnline() ? 'online' : '' }}">
                                        <img src="{{ $follower->avatarUrl() }}" alt="" width="40" height="40" loading="lazy">
                                    </span>
                                    <span class="user-box-content">
                                        <strong class="name">
                                            <span class="name-text">{{ $follower->displayName() }}</span>
                                            @if ($follower->hasVerifiedBadge())
                                                <span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>
                                            @endif
                                        </strong>
                                        <span class="meta">{{ number_format($follower->posts_count) }} yazı</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="small color-gray">Henüz abone yok.</li>
                        @endforelse
                    </ul>
                    @if ($followersCount > $allProfileFollowers->count())
                        <p class="small color-gray">İlk 100 abone gösteriliyor.</p>
                    @endif
                </div>
            </div>
        </div>
    </article>
@endsection

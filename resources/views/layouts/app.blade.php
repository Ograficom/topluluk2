<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#f7f9fd">
    <title>@yield('title', $brandName) — {{ $brandName }}</title>
    <meta name="description" content="@yield('description', 'Repost topluluğunda yeni hikâyeleri ve fikirleri keşfedin.')">
    <link rel="icon" href="{{ $faviconUrl ?? asset('assets/img/meta/logo.svg') }}" type="{{ $faviconMimeType ?? 'image/svg+xml' }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fancyapp/fancybox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fancyapp/carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kit.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/engine.css') }}">
    <script>
        (() => {
            const root = document.documentElement;
            let theme;

            try {
                theme = localStorage.getItem('theme');
            } catch {}

            if (theme !== 'light' && theme !== 'dark') {
                theme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            root.dataset.theme = theme;
            root.dataset.bsTheme = theme;
            root.style.colorScheme = theme;
        })();
    </script>
    @stack('head')
</head>
<body>
    <header class="header">
        <div class="header__inner container">
            <div class="header__start">
                <a href="{{ route('posts.index') }}" class="logo header__logo" aria-label="{{ $brandName }} ana sayfa">
                    <img
                        src="{{ $brandLogoUrl ?? asset('assets/img/meta/logo.svg') }}"
                        alt=""
                        width="36"
                        height="36"
                        @class(['logo-icon', 'logo-icon--custom' => $brandLogoUrl])
                        onerror="this.onerror=null;this.src='{{ asset('assets/img/meta/logo.svg') }}';"
                    >
                    <span class="logo-text">{{ $brandName }}</span>
                </a>
            </div>
            <nav class="header__nav header__tabs" aria-label="Ana gezinme">
                <a href="{{ route('posts.index') }}" @class(['active' => request()->routeIs('posts.index')])>Akış</a>
                <a href="{{ route('channels.index') }}" @class(['active' => request()->routeIs('channels.*')])>Kanallar</a>
            </nav>
            <div class="header__end">
                <button class="btn btn-secondary-ghost btn-circle btn-md" type="button" data-modal-btn="search" aria-label="Ara" aria-haspopup="dialog" aria-controls="searchModal">
                    <x-repost-icon name="search" size="24" />
                </button>
                <button class="switcher btn btn-secondary-ghost btn-circle btn-md" type="button" data-theme-toggle aria-label="Temayı değiştir"></button>
                <a href="{{ auth()->check() ? route('posts.create') : route('login') }}" class="btn btn-md btn-white btn-rounded shadow-sm header__write">
                    <x-repost-icon name="pen" size="18" />
                    <span>Yaz</span>
                </a>
                @auth
                    <div class="dropdown dropdown-md">
                        <button id="userMenuButton" type="button" class="btn btn-secondary-ghost btn-circle avatar" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-controls="userMenu" aria-label="{{ auth()->user()->displayName() }}">
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="" width="40" height="40">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="userMenu" aria-labelledby="userMenuButton">
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a class="login__profile" href="{{ route('profiles.show', auth()->user()) }}">
                                    <span class="user-box">
                                        <span class="avatar"><img src="{{ auth()->user()->avatarUrl() }}" alt="" width="40" height="40"></span>
                                        <span class="user-box-content">
                                            <span class="name">
                                                <span class="name-text">{{ auth()->user()->displayName() }}</span>
                                                @if (auth()->user()->hasVerifiedBadge())
                                                    <span class="verify" role="img" aria-label="Doğrulanmış hesap" title="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>
                                                @endif
                                            </span>
                                            <span class="small color-gray">Profili görüntüle</span>
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('messenger.index') }}"><x-repost-icon name="message" size="22" class="color-gray" /><span>Mesajlar</span></a>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('favorites.index') }}"><x-repost-icon name="bookmark" size="22" class="color-gray" /><span>Favoriler</span></a>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('community.following') }}"><x-repost-icon name="user" size="20" class="color-gray" /><span>Takip akışı</span></a>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('subscriptions.index') }}"><x-repost-icon name="user" size="20" class="color-gray" /><span>Abonelikler</span></a>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('settings.edit') }}"><x-repost-icon name="setting" size="20" class="color-gray" /><span>Ayarlar</span></a>
                            </li>
                            @if (auth()->user()->is_admin)
                                <li class="dropdown-menu-item dropdown-menu-item-lg">
                                    <a href="{{ url('/admin') }}"><x-repost-icon name="adminpanel" size="20" class="color-gray" /><span>Yönetim paneli</span></a>
                                </li>
                            @endif
                            <li class="dropdown-menu-item dropdown-menu-item-lg is-danger">
                                <button type="submit" form="logoutForm"><x-repost-icon name="logout" size="20" /><span>Çıkış yap</span></button>
                            </li>
                        </ul>
                        <form id="logoutForm" action="{{ route('logout') }}" method="POST" hidden>
                            @csrf
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-rounded btn-md" aria-label="Sign in">
                        <x-repost-icon name="user" size="18" />
                        <span>Sign in</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <div class="modal modal-top search-modal" id="searchModal" data-modal="search" aria-hidden="true">
        <div class="modal-content" aria-label="Yazılarda ara">
            <form action="{{ route('posts.index') }}" method="GET" class="search">
                <div class="search__form">
                    <input id="story" name="q" type="search" value="{{ request('q') }}" placeholder="Yazılarda ara" aria-label="Yazılarda ara" minlength="3" required>
                </div>
            </form>
            <div class="search__actions">
                <button type="button" class="btn btn-secondary-ghost btn-circle search__options" data-search-clear aria-label="Aramayı temizle" hidden>
                    <x-repost-icon name="close" size="18" />
                </button>
            </div>
        </div>
    </div>

    @php($settingsPage = request()->routeIs('settings.edit', 'settings.legacy'))
    @php($profilePage = request()->routeIs('profiles.show'))
    @php($postCreatePage = request()->routeIs('posts.create'))
    @php($feedPage = request()->routeIs('posts.index'))
    @php($statsPage = request()->routeIs('stats.index'))
    <main class="container">
        <div class="main {{ $settingsPage || $profilePage || $postCreatePage ? 'main__grid-full' : 'main__grid' }}">
            <aside class="sidebar" aria-label="Kanallar ve yazarlar">
                <div class="sidebar__body">
                    <section class="sidebar__section">
                        <h2 class="sidebar__title">Kanallar</h2>
                        @if (auth()->user()?->is_admin)
                            <a class="small link-accent" href="{{ route('channels.create') }}">Kategori ekle</a>
                        @endif
                        <ul class="sidebar__list">
                            @forelse ($navigationChannels as $channel)
                                <li>
                                    <a href="{{ route('channels.show', $channel) }}" class="channel-icon no-icon" aria-label="{{ $channel->name }}" title="{{ $channel->name }}">
                                        <x-repost-icon name="image" size="16" />
                                    </a>
                                </li>
                            @empty
                                <li><span class="small color-gray">Henüz kanal yok</span></li>
                            @endforelse
                        </ul>
                    </section>
                    <section class="sidebar__section">
                        <h2 class="sidebar__title">Blogs</h2>
                        <ul class="sidebar__list">
                            @foreach ($sidebarAuthors as $author)
                                <li>
                                    <a href="{{ route('profiles.show', $author) }}" class="channel-icon" aria-label="{{ $author->displayName() }}" title="{{ $author->displayName() }}">
                                        <img src="{{ $author->avatarUrl() }}" alt="" width="50" height="50">
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>
                <div class="sidebar__footer">
                    <a href="{{ route('channels.index') }}" class="btn btn-circle btn-xl btn-secondary-ghost" aria-label="Tüm kanallar">
                        <x-repost-icon name="menu-alt" size="24" />
                    </a>
                </div>
            </aside>

            @if ($settingsPage)
                @yield('content')
            @elseif ($profilePage || $postCreatePage)
                <section class="feed">
                    @if (session('status'))
                        <div class="alert alert-success" role="status">{{ session('status') }}</div>
                    @endif
                    @yield('content')
                </section>
            @else
                @if ($statsPage)
                    <article class="feed">
                @else
                    <section class="feed">
                @endif
                    @if (session('status'))
                        <div class="alert alert-success" role="status">{{ session('status') }}</div>
                    @endif
                    @yield('content')
                @if ($statsPage)
                    </article>
                @else
                    </section>
                @endif

                @unless ($profilePage || $postCreatePage)
                <aside class="{{ $statsPage || $feedPage ? 'main__widgets' : 'profile__aside' }}" aria-label="Popüler içerikler">
                @unless ($statsPage || $feedPage)
                    <div class="side-panel__body">
                @endunless
                    <div class="widgets">
                        @if ($statsPage)
                            <section class="widget card card-body">
                                <header><h2 class="widget__title h6">Etiketler</h2></header>
                                <div class="widget__content tagscloud">
                                    @forelse ($popularTags as $tag)
                                        <span><a href="{{ route('posts.index', ['q' => $tag]) }}">{{ $tag }}</a></span>
                                    @empty
                                        <span class="small color-gray">Henüz etiket yok</span>
                                    @endforelse
                                </div>
                                <footer class="widget__footer"><a href="{{ route('posts.index') }}" class="link-accent">Tüm yazılar</a></footer>
                            </section>
                        @endif
                        <section class="widget card card-body">
                            <header><h2 class="widget__title h6">Popüler yazarlar</h2></header>
                            <div class="widget__content widget__content--bleed">
                                <ul class="widget__list">
                                    @forelse ($sidebarAuthors as $author)
                                        <li class="user-card">
                                            <a class="user-box" href="{{ route('profiles.show', $author) }}">
                                                <span class="avatar"><img src="{{ $author->avatarUrl() }}" alt="" width="40" height="40"></span>
                                                <span class="user-box-content">
                                                    <strong class="name">
                                                        <span class="name-text">{{ $author->displayName() }}</span>
                                                        @if ($author->hasVerifiedBadge())
                                                            <span class="verify" role="img" aria-label="Doğrulanmış hesap" title="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>
                                                        @endif
                                                    </strong>
                                                    <span class="meta">{{ $author->posts_count }} yazı</span>
                                                </span>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="user-card"><span class="small color-gray">Henüz yazar yok</span></li>
                                    @endforelse
                                </ul>
                            </div>
                            <footer class="widget__footer"><a href="{{ route('members.index') }}" class="link-accent">Üye sıralaması</a></footer>
                        </section>
                        <section class="widget card card-body">
                            <header><h2 class="widget__title h6">Popüler yazılar</h2></header>
                            <div class="widget__content widget__content--bleed">
                                <ul class="widget__list widget__list--separator">
                                    @forelse ($sidebarPosts as $post)
                                        <li class="entry-card">
                                            <article class="entry-box">
                                                <div class="entry-box-content">
                                                    <a href="{{ route('posts.show', $post) }}" class="text-clamp-2 color-text">{{ $post->title }}</a>
                                                    <div class="meta">
                                                        <span class="meta-item"><x-repost-icon name="comment" size="14" />{{ $post->comments_count }}</span>
                                                        <span class="meta-item"><x-repost-icon name="eye" size="14" />{{ number_format($post->views) }}</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                    @empty
                                        <li class="entry-card"><span class="small color-gray">Henüz yayınlanmış yazı yok</span></li>
                                    @endforelse
                                </ul>
                            </div>
                            <footer class="widget__footer"><a href="{{ route('posts.index') }}" class="link-accent">Tüm yazılar</a></footer>
                        </section>
                        <section class="widget card card-body">
                            <header><h2 class="widget__title h6">Popüler yorumlar</h2></header>
                            <div class="widget__content widget__content-offset">
                                <ul class="widget__list widget__list--limit widget__list--separator widget__list--separator-lg">
                                    @forelse ($sidebarComments as $comment)
                                        <li class="comm-card">
                                            <div class="user-box user-box-sm">
                                                @if ($comment->user)
                                                    <a href="{{ route('profiles.show', $comment->user) }}" class="avatar" aria-label="{{ $comment->user->displayName() }} profili">
                                                        <img src="{{ $comment->user->avatarUrl() }}" alt="" width="40" height="40">
                                                    </a>
                                                @else
                                                    <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                                                @endif
                                                <div class="user-box-content user-box-content--line">
                                                    @if ($comment->user)
                                                        <a href="{{ route('profiles.show', $comment->user) }}" class="name">
                                                            <span class="name-text">{{ $comment->user->displayName() }}</span>
                                                            @if ($comment->user->hasVerifiedBadge())
                                                                <span class="verify" role="img" aria-label="Doğrulanmış profil"><x-repost-icon name="verify" size="14" /></span>
                                                            @endif
                                                        </a>
                                                    @else
                                                        <span class="name"><span class="name-text">Repost kullanıcısı</span></span>
                                                    @endif
                                                    <time class="meta" datetime="{{ $comment->created_at->toAtomString() }}">{{ $comment->created_at->diffForHumans() }}</time>
                                                </div>
                                            </div>
                                            <div class="comm-card-short text-clamp-3">{{ $comment->body }}</div>
                                            @if ($comment->post)
                                                <div class="small text-clamp-1">
                                                    <span class="color-gray">Yazı:</span>
                                                    <a href="{{ route('posts.show', $comment->post) }}#comments" class="link">{{ $comment->post->title }}</a>
                                                </div>
                                            @endif
                                        </li>
                                    @empty
                                        <li class="comm-card"><span class="small color-gray">Henüz yorum yok.</span></li>
                                    @endforelse
                                </ul>
                            </div>
                        </section>
                    </div>
                @unless ($statsPage || $feedPage)
                    </div>
                @endunless
                @include('layouts._footer')
                </aside>
                @endunless
            @endif
        </div>
    </main>

    @if ($settingsPage)
        <div class="container">@include('layouts._footer')</div>
    @endif

    <section class="mobmenu" data-scroll-menu>
        <nav class="mobmenu__content" aria-label="Mobile navigation">
            <a class="mobmenu__link {{ request()->routeIs('posts.index') ? 'is-active' : '' }}" href="{{ route('posts.index') }}" aria-label="Home">
                <x-repost-icon name="home" size="20" /><span>Home</span>
            </a>
            <button class="mobmenu__link" type="button" data-side-panel-open="mobileAside" aria-controls="mobileAside" aria-expanded="false" aria-haspopup="dialog" title="Gezinme">
                <x-repost-icon name="compass" size="20" /><span>Gezinme</span>
            </button>
            <a class="mobmenu__link {{ request()->routeIs('channels.*') ? 'is-active' : '' }}" href="{{ route('channels.index') }}" aria-label="Channels">
                <x-repost-icon name="channels" size="20" /><span>Channels</span>
            </a>
        </nav>
    </section>

    @include('layouts._mobile-side-panel')

    <button class="btn btn-white btn-md btn-circle gotop" type="button" data-gotop aria-label="Yukarı çık" aria-hidden="true" tabindex="-1">
        <x-repost-icon name="chevron-up" size="18" class="color-gray" />
    </button>

    <script src="{{ asset('assets/libs/fancyapp/fancybox.umd.js') }}"></script>
    <script src="{{ asset('assets/libs/fancyapp/carousel.umd.js') }}"></script>
    <script src="{{ asset('assets/libs/sharer/sharer.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/init.js') }}"></script>
    @stack('scripts')
</body>
</html>

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
    <link rel="alternate" type="application/rss+xml" title="{{ $brandName }} RSS" href="{{ url('/rss.xml') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fancyapp/fancybox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fancyapp/carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kit.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v=20261106-settings-social-polish">
    <link rel="stylesheet" href="{{ asset('assets/css/engine.css') }}?v=20261106-social-account-management">
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
                                    <a href="{{ route('channels.create') }}" aria-label="Kategori oluştur" title="Kategori oluştur"><x-repost-icon name="plus" size="20" class="color-gray" /><span>Kategori oluştur</span></a>
                                </li>
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
                    <button type="button" class="btn btn-primary btn-rounded btn-md" data-modal-btn="login" aria-haspopup="dialog" aria-controls="loginModal"><x-repost-icon name="user" size="18" /><span>Giriş yap</span></button>
                @endauth
            </div>
        </div>
    </header>

    @guest
    <div class="modal auth-modal" id="loginModal" data-modal="login" role="dialog" aria-modal="true" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-content">
            <button type="button" class="btn btn-secondary btn-md btn-circle modal-close" data-modal-close aria-label="Kapat"><x-repost-icon name="close" size="16" class="color-gray" /></button>
            <div class="modal-body signin" data-auth-step="options">
                <h2 class="text-center h1" id="loginModalLabel">Giriş yap</h2>
                <p class="text-center color-gray">Tüm site özelliklerine erişmek için giriş yap.</p>
                <div class="signin__body">
                    @include('auth._social-buttons')
                    <button type="button" class="btn btn-primary btn-lg btn-full" data-auth-email>E-posta ile devam et</button>
                </div>
                <div class="signin__footer"><p class="small color-gray text-center">Devam ederek <a href="{{ route('pages.show', 'rules') }}" class="link">topluluk kurallarını</a> kabul etmiş olursun.</p></div>
            </div>
            <form class="modal-body signin" data-auth-step="email" action="{{ route('login.store') }}" method="POST" hidden>
                @csrf
                <button type="button" class="btn btn-secondary btn-md btn-circle modal-back" data-auth-back aria-label="Geri"><x-repost-icon name="chevron-left" size="18" class="color-gray" /></button>
                <h2 class="text-center h1">Giriş yap</h2>
                <p class="text-center color-gray">Hesabına erişmek için bilgilerini gir.</p>
                <div class="signin__body">
                    <div class="form-group"><input type="text" name="login_name" placeholder="Kullanıcı adı veya e-posta" autocomplete="username" required></div>
                    <div class="form-group view-pass"><input type="password" name="password" data-pass-form="header-login" placeholder="Şifre" autocomplete="current-password" required><button type="button" class="view-pass-btn" data-pass-btn="header-login" aria-label="Şifreyi göster" title="Şifreyi göster" aria-pressed="false"></button></div>
                    <div class="signin__submit"><button type="submit" class="btn btn-primary btn-lg btn-full">Giriş yap</button><a href="{{ route('register') }}" class="btn btn-secondary btn-lg btn-full">Hesap oluştur</a></div>
                </div>
                <div class="signin__footer"><p class="small color-gray text-center">Şifreni mi unuttun? <a href="{{ route('password.request') }}" class="link">Sıfırla</a></p></div>
            </form>
        </div>
    </div>
    @endguest

    <div class="modal modal-top search-modal" id="searchModal" data-modal="search" aria-hidden="true">
        <div class="modal-content" aria-label="Yazılarda ara">
            <form id="q_search" action="{{ route('posts.index') }}" method="GET" class="search">
                <div class="search__form">
                    <input id="story" name="q" type="search" value="{{ request('q') }}" placeholder="Yazılarda ara" aria-label="Yazılarda ara" minlength="3" required>
                </div>
            </form>
            <div class="search__actions">
                <button type="button" class="btn btn-secondary-ghost btn-circle search__options" data-search-clear aria-label="Aramayı temizle" hidden>
                    <x-repost-icon name="close" size="18" />
                </button>
                <button type="button" class="btn btn-secondary-ghost btn-circle search__options" data-search-voice aria-label="Sesli arama" aria-pressed="false">
                    <x-repost-icon name="mic" size="18" />
                </button>
            </div>
        </div>
    </div>

    @php($settingsPage = request()->routeIs('settings.edit', 'settings.legacy'))
    @php($profilePage = request()->routeIs('profiles.show'))
    @php($channelPage = request()->routeIs('channels.show', 'legacy.channel'))
    @php($postCreatePage = request()->routeIs('posts.create'))
    @php($feedPage = request()->routeIs('posts.index'))
    @php($channelsIndexPage = request()->routeIs('channels.index', 'legacy.channels'))
    @php($statsPage = request()->routeIs('stats.index'))
    <main class="container">
        <div class="main {{ $settingsPage || $profilePage || $channelPage || $postCreatePage ? 'main__grid-full' : 'main__grid' }}">
            <aside class="sidebar" aria-label="Kanallar ve yazarlar">
                <div class="sidebar__body">
                    <section class="sidebar__section">
                        <h2 class="sidebar__title">Kanallar</h2>
                        <ul class="sidebar__list">
                            @forelse ($navigationChannels as $channel)
                                <li>
                                    <a href="{{ route('channels.show', $channel) }}" class="channel-icon no-icon" aria-label="{{ $channel->name }}" title="{{ $channel->name }}">
                                        @if ($channel->image_path)
                                            <img src="{{ asset('storage/'.$channel->image_path) }}" alt="" width="50" height="50" loading="lazy">
                                        @else
                                            <x-repost-icon name="image" size="16" />
                                        @endif
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

            @if ($settingsPage || $profilePage || $channelPage)
                @yield('content')
            @elseif ($postCreatePage)
                <section class="feed">
                    @if (session('status'))
                        <div class="alert alert-success" role="status">{{ session('status') }}</div>
                    @endif
                    @yield('content')
                </section>
            @else
                @if ($statsPage || $channelsIndexPage)
                    <article class="feed">
                @else
                    <section class="feed">
                @endif
                    @if (session('status'))
                        <div class="alert alert-success" role="status">{{ session('status') }}</div>
                    @endif
                    @yield('content')
                @if ($statsPage || $channelsIndexPage)
                    </article>
                @else
                    </section>
                @endif

                @unless ($profilePage || $channelPage || $postCreatePage)
                <aside class="main__widgets" aria-label="Sağ sütun">
                    <div class="widgets">
                        <section class="widget card card-body">
                            <header><h2 class="widget__title h6">Etiketler</h2></header>
                            <div class="widget__content tagscloud">
                                @forelse ($popularTags as $tag => $count)
                                    <span><a href="{{ route('tags.show', ['tag' => $tag]) }}">{{ $tag }}</a></span>
                                @empty
                                    <span class="small color-gray">Henüz etiket yok</span>
                                @endforelse
                            </div>
                            <footer class="widget__footer"><a href="{{ route('tags.index') }}" class="link-accent">Tüm etiketler</a></footer>
                        </section>
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
                                            <article class="entry-box {{ $post->coverImageUrl() ? 'has-image' : '' }}">
                                                <div class="entry-box-content">
                                                    <div class="user-box">
                                                        @if ($post->user)
                                                            <a href="{{ route('profiles.show', $post->user) }}" class="avatar" aria-label="{{ $post->user->displayName() }} profili">
                                                                <img src="{{ $post->user->avatarUrl() }}" alt="" width="40" height="40">
                                                            </a>
                                                        @else
                                                            <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                                                        @endif
                                                        <div class="user-box-content">
                                                            @if ($post->user)
                                                                <a href="{{ route('profiles.show', $post->user) }}" class="name"><span class="name-text">{{ $post->user->displayName() }}</span></a>
                                                            @else
                                                                <span class="name"><span class="name-text">OgraFi yazarı</span></span>
                                                            @endif
                                                            <span class="meta">{{ $post->published_at?->translatedFormat('d F Y') ?? $post->created_at->translatedFormat('d F Y') }}</span>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('posts.show', $post) }}" class="text-clamp-2 color-text">{{ $post->title }}</a>
                                                    <div class="meta">
                                                        <span class="meta-item"><x-repost-icon name="comment" size="14" />{{ $post->comments_count }}</span>
                                                        <span class="meta-item"><x-repost-icon name="eye" size="14" />{{ number_format($post->views) }}</span>
                                                    </div>
                                                </div>
                                                @if ($post->coverImageUrl())
                                                    <a href="{{ route('posts.show', $post) }}" class="entry-box-image">
                                                        <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->title }}" width="100" height="80" loading="lazy">
                                                    </a>
                                                @endif
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
                            <header><h2 class="widget__title h6">Son yorumlar</h2></header>
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
                        @include('layouts._footer')
                    </div>
                </aside>
                @endunless
            @endif
        </div>
    </main>

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
    @guest
    <script>
    (() => {
        const modal = document.querySelector('[data-modal="login"]');
        if (!modal) return;
        const options = modal.querySelector('[data-auth-step="options"]');
        const email = modal.querySelector('[data-auth-step="email"]');
        modal.addEventListener('click', (event) => {
            if (event.target.closest('[data-auth-email]')) {
                options.hidden = true;
                email.hidden = false;
                email.querySelector('input[name="login_name"]')?.focus();
            }
            if (event.target.closest('[data-auth-back]')) {
                email.hidden = true;
                options.hidden = false;
            }
        });
        modal.addEventListener('close', () => {
            email.hidden = true;
            options.hidden = false;
        });
    })();
    </script>
    @endguest
    @stack('scripts')
</body>
</html>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f9fd">
    <title>@yield('title', config('app.name', 'OgraFi'))</title>
    <meta name="description" content="@yield('meta_description', 'OgraFi topluluğu: gündem, fikirler ve paylaşımlar.')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <script>
        (() => {
            let theme = 'light';
            try {
                const saved = localStorage.getItem('theme');
                if (saved === 'light' || saved === 'dark') theme = saved;
            } catch (_) {}
            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.bsTheme = theme;
            document.documentElement.style.colorScheme = theme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
    <svg xmlns="http://www.w3.org/2000/svg" class="repost-icon-sprite" aria-hidden="true">
        <symbol id="rf-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
        <symbol id="rf-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="rf-pen" viewBox="0 0 24 24"><path d="m4 16-.8 4.8L8 20l12-12-4-4zM14 6l4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
        <symbol id="rf-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M4 21c0-4 3-6 8-6s8 2 8 6" fill="none" stroke="currentColor" stroke-width="1.8"/></symbol>
        <symbol id="rf-message" viewBox="0 0 24 24"><path d="M20 11.5a8 8 0 0 1-8 8 9 9 0 0 1-4-.9L3 21l1.5-4.5A8 8 0 1 1 20 11.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
        <symbol id="rf-bookmark" viewBox="0 0 24 24"><path d="M6 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18l-6-4-6 4z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
        <symbol id="rf-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" fill="none" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="7" rx="1" fill="none" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="14" width="7" height="7" rx="1" fill="none" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="14" width="7" height="7" rx="1" fill="none" stroke="currentColor" stroke-width="1.8"/></symbol>
        <symbol id="rf-comment" viewBox="0 0 24 24"><path d="M4 5h16v12H9l-5 4z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
        <symbol id="rf-eye" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/></symbol>
        <symbol id="rf-tag" viewBox="0 0 24 24"><path d="M3 4h8l10 10-7 7L4 11z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="8" cy="8" r="1" fill="currentColor"/></symbol>
        <symbol id="rf-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></symbol>
        <symbol id="rf-moon" viewBox="0 0 24 24"><path d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></symbol>
        <symbol id="rf-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    </svg>

    <header class="header">
        <div class="header__inner container">
            <a href="{{ route('home') }}" class="logo header__logo" aria-label="OgraFi ana sayfa">
                <span class="logo-icon repost-brand-mark">O</span><span class="logo-text">OgraFi</span>
            </a>
            <nav class="header__nav header__tabs" aria-label="Ana menü">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Akış</a>
                <a href="{{ route('blog.categories') }}" class="{{ request()->routeIs('blog.categories', 'blog.category') ? 'active' : '' }}">Topluluklar</a>
            </nav>
            <div class="header__end">
                <form action="{{ route('search') }}" method="GET" class="repost-header-search">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="OgraFi'de ara" aria-label="OgraFi'de ara">
                    <button class="btn btn-secondary-ghost btn-circle btn-md" type="submit" aria-label="Ara"><svg width="20" height="20"><use href="#rf-search"/></svg></button>
                </form>
                <button class="btn btn-secondary-ghost btn-circle btn-md" type="button" data-theme-toggle aria-label="Temayı değiştir">
                    <svg width="19" height="19" class="theme-icon-light"><use href="#rf-moon"/></svg>
                    <svg width="19" height="19" class="theme-icon-dark"><use href="#rf-sun"/></svg>
                </button>
                @auth
                    <a href="{{ route('blog.create') }}" class="btn btn-primary btn-rounded header__write"><svg width="17" height="17"><use href="#rf-pen"/></svg><span>Yazı yaz</span></a>
                    <div class="dropdown dropdown-md">
                        <button class="btn btn-secondary-ghost btn-circle avatar" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Hesap menüsü">
                            @if (auth()->user()->profile_photo_url)
                                <img src="{{ auth()->user()->profile_photo_url }}" alt="" width="40" height="40">
                            @else
                                <span class="repost-avatar-fallback">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-menu-item"><a href="{{ route('users.show', auth()->user()->username) }}">Profilim</a></li>
                            <li class="dropdown-menu-item"><a href="{{ route('blog.bookmarks') }}">Kaydedilenler</a></li>
                            <li class="dropdown-menu-item"><a href="{{ route('messages.index') }}">Mesajlar</a></li>
                            <li class="dropdown-menu-item"><a href="{{ route('dashboard') }}">Hesap ayarları</a></li>
                            <li class="dropdown-menu-item">
                                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Çıkış yap</button></form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-rounded">Giriş yap</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-rounded">Üye ol</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="container repost-page-container {{ request()->routeIs('login', 'register', 'register.verify', 'register.complete', 'password.request', 'password.reset') ? 'repost-auth-layout' : '' }}">
        <div class="main main__grid repost-main-grid">
            <aside class="sidebar repost-left-sidebar" aria-label="Sol menü">
                <div class="sidebar__body">
                    <section class="sidebar__section">
                        <h2 class="sidebar__title">Menü</h2>
                        <ul class="repost-nav-list">
                            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}"><svg><use href="#rf-home"/></svg><span>Ana akış</span></a></li>
                            <li><a href="{{ route('blog.categories') }}"><svg><use href="#rf-grid"/></svg><span>Topluluklar</span></a></li>
                            @auth
                                <li><a href="{{ route('blog.bookmarks') }}"><svg><use href="#rf-bookmark"/></svg><span>Kaydedilenler</span></a></li>
                                <li><a href="{{ route('messages.index') }}"><svg><use href="#rf-message"/></svg><span>Mesajlar</span></a></li>
                            @endauth
                        </ul>
                    </section>
                    @hasSection('left-sidebar')
                        @yield('left-sidebar')
                    @else
                        @if(isset($communityCategories) && $communityCategories->isNotEmpty())
                            <section class="sidebar__section">
                                <h2 class="sidebar__title">Topluluklar</h2>
                                <ul class="repost-category-list">
                                    @foreach($communityCategories as $community)
                                        <li><a href="{{ route('blog.category', $community->slug) }}"><span class="repost-category-dot"></span>{{ $community->name }}</a></li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('blog.categories') }}" class="repost-sidebar-more">Tüm topluluklar</a>
                            </section>
                        @endif
                    @endif
                </div>
            </aside>

            <section class="feed repost-feed">
                @yield('content')
            </section>

            <aside class="main__widgets repost-right-sidebar" aria-label="Sağ içerik alanı">
                @hasSection('right-sidebar')
                    @yield('right-sidebar')
                @else
                    @if(isset($popularTags) && $popularTags->isNotEmpty())
                        <section class="widget card card-body">
                            <h2 class="widget__title h6">Popüler etiketler</h2>
                            <div class="widget__content repost-tag-cloud">
                                @foreach($popularTags as $tag)
                                    <a href="{{ route('search', ['q' => $tag->name]) }}">#{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                    @if(isset($suggestedUsers) && $suggestedUsers->isNotEmpty())
                        <section class="widget card card-body">
                            <h2 class="widget__title h6">Öne çıkan üyeler</h2>
                            <div class="widget__content">
                                <ul class="widget__list">
                                    @foreach($suggestedUsers as $suggestedUser)
                                        <li class="user-card">
                                            <a class="user-box" href="{{ route('users.show', $suggestedUser->username) }}">
                                                <span class="avatar"><span class="repost-avatar-fallback">{{ mb_substr($suggestedUser->name, 0, 1) }}</span></span>
                                                <span class="user-box-content"><strong class="name">{{ $suggestedUser->name }}</strong><span class="meta">{{ number_format((int) $suggestedUser->followers_count) }} takipçi</span></span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endif
                    @if(isset($popularComments) && $popularComments->isNotEmpty())
                        <section class="widget card card-body">
                            <h2 class="widget__title h6">Son yorumlar</h2>
                            <div class="widget__content">
                                <ul class="widget__list widget__list--separator">
                                    @foreach($popularComments as $comment)
                                        <li class="comm-card">
                                            <div class="user-box user-box-sm">
                                                <span class="avatar"><span class="repost-avatar-fallback">{{ mb_substr($comment->user?->name ?? $comment->author_name ?? '?', 0, 1) }}</span></span>
                                                <span class="user-box-content"><strong class="name">{{ $comment->user?->name ?? $comment->author_name }}</strong><span class="meta">{{ $comment->created_at?->diffForHumans() }}</span></span>
                                            </div>
                                            <p class="comm-card-short">{{ \Illuminate\Support\Str::limit(strip_tags($comment->content), 95) }}</p>
                                            @if($comment->post)
                                                <a class="small" href="{{ route('blog.post', $comment->post->slug) }}">Gönderiye git</a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endif
                @endif
            </aside>
        </div>
    </main>

    <nav class="repost-mobile-nav" aria-label="Mobil menü">
        <a href="{{ route('home') }}"><svg><use href="#rf-home"/></svg><span>Akış</span></a>
        <a href="{{ route('blog.categories') }}"><svg><use href="#rf-grid"/></svg><span>Topluluklar</span></a>
        @auth
            <a href="{{ route('blog.create') }}"><svg><use href="#rf-pen"/></svg><span>Yaz</span></a>
            <a href="{{ route('blog.bookmarks') }}"><svg><use href="#rf-bookmark"/></svg><span>Kaydedilenler</span></a>
        @else
            <a href="{{ route('login') }}"><svg><use href="#rf-user"/></svg><span>Giriş</span></a>
        @endauth
    </nav>
    @stack('scripts')
</body>
</html>

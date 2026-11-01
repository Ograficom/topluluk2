<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f9fd">
    <title>@yield('title', config('app.name', 'OgraFi'))</title>
    <meta name="description" content="@yield('meta_description', 'OgraFi topluluk akışı')">
    <meta property="og:site_name" content="OgraFi">
    <meta property="og:title" content="@yield('title', 'OgraFi')">
    <meta property="og:description" content="@yield('meta_description', 'OgraFi topluluk akışı')">
    <meta property="og:type" content="website">
    @php($canonicalUrl = trim($__env->yieldContent('canonical_url')))
    @if($canonicalUrl !== '')
        <link rel="canonical" href="{{ $canonicalUrl }}">
        <meta property="og:url" content="{{ $canonicalUrl }}">
    @endif
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/css/ografi.css', 'resources/css/repost-v2.css', 'resources/js/app.js'])
    @stack('styles')
    @stack('head')
</head>
<body>
<div id="repost-icon-sprite" class="sprite" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" fill="none" style="visibility:hidden;position:absolute" aria-hidden="true">
        <symbol id="home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>
        <symbol id="search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m20 20-4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="pen" viewBox="0 0 24 24"><path d="m3 17 12-12a2.1 2.1 0 0 1 3 3L6 20l-4 1 1-4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m13 7 4 4" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="message" viewBox="0 0 24 24"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 8.4 8.4 0 0 1-3.6-.8L3 21l1.8-4.9A8.5 8.5 0 1 1 21 11.5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>
        <symbol id="bookmark" viewBox="0 0 24 24"><path d="M6 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18l-6-4-6 4V4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>
        <symbol id="setting" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="m19.4 15 .1.1 1.2 1-1.5 2.6-1.5-.6a8 8 0 0 1-1.6.9l-.2 1.6h-3l-.2-1.6a8 8 0 0 1-1.6-.9l-1.5.6-1.5-2.6 1.2-1a8 8 0 0 1 0-1.9l-1.2-1 1.5-2.6 1.5.6a8 8 0 0 1 1.6-.9l.2-1.6h3l.2 1.6a8 8 0 0 1 1.6.9l1.5-.6 1.5 2.6-1.2 1a8 8 0 0 1 0 1.9Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></symbol>
        <symbol id="logout" viewBox="0 0 24 24"><path d="M10 17 15 12 10 7m5 5H3m9-9h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="verify" viewBox="0 0 24 24"><path d="m9 12 2 2 4-5m7 3a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="image" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><path d="m21 15-5-5L5 21" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="menu-alt" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="feed" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M16 3v4M8 3v4M3 10h18" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="sort" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10m-7 6h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="comment" viewBox="0 0 24 24"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 8.4 8.4 0 0 1-3.6-.8L3 21l1.8-4.9A8.5 8.5 0 1 1 21 11.5Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="eye" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="like" viewBox="0 0 24 24"><path d="M7 10v11H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3Zm0 0 5-8a3 3 0 0 1 2 3l-1 5h6a2 2 0 0 1 2 2l-2 7a2 2 0 0 1-2 2H7" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>
        <symbol id="share" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="19" r="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="m9 11 6-5M9 13l6 5" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="chevron-down" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="channels" viewBox="0 0 24 24"><path d="M4 5h16M4 12h16M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="7" cy="5" r="2" fill="currentColor"/><circle cx="17" cy="12" r="2" fill="currentColor"/><circle cx="10" cy="19" r="2" fill="currentColor"/></symbol>
        <symbol id="compass" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="m15 9-2 4-4 2 2-4 4-2Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
    </svg>
</div>

@php
    $hideGlobalHeader = $__env->hasSection('hide_global_header');
    $noContainerPadding = $__env->hasSection('no_container_padding');
    $isAuthLayout = request()->routeIs('login', 'register', 'register.verify', 'register.complete', 'password.request', 'password.reset', 'social.device.verify', 'two-factor.login');
@endphp

@unless($hideGlobalHeader ?? false)
<header class="header">
    <div class="header__inner container">
        <div class="header__start">
            <a class="logo header__logo" href="{{ url('/') }}" aria-label="OgraFi ana sayfa">
                <span class="logo-icon" aria-hidden="true">O</span><span class="logo-text">OgraFi</span>
            </a>
        </div>
        <nav class="header__nav header__tabs" aria-label="Ana menü">
            <a href="{{ url('/') }}" @if(request()->routeIs('home')) aria-current="page" class="active" @endif>Akış</a>
            <a href="{{ url('/Categorys') }}">Topluluklar</a>
            <a href="{{ url('/users') }}">Üyeler</a>
        </nav>
        <div class="header__end">
            <a href="{{ url('/search') }}" class="btn btn-secondary-ghost btn-circle btn-md" aria-label="Ara"><svg width="22" height="22"><use href="#search"/></svg></a>
            <button class="switcher btn btn-secondary-ghost btn-circle btn-md" data-theme-toggle type="button" aria-label="Tema değiştir"></button>
            @auth
                <a href="{{ url('/blog/create') }}" class="btn btn-md btn-white btn-rounded header__write"><svg width="18" height="18"><use href="#pen"/></svg><span>Yaz</span></a>
                <div class="dropdown dropdown-md">
                    <button class="btn btn-secondary-ghost btn-circle avatar" id="userMenuButton" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Hesap menüsü">
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="" width="40" height="40">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="userMenu" aria-labelledby="userMenuButton">
                        <li class="dropdown-menu-item dropdown-menu-item-lg"><a href="{{ url('/u/'.auth()->user()->username) }}"><span class="user-box"><span class="avatar"><img src="{{ auth()->user()->profile_photo_url }}" alt="" width="40" height="40"></span><span class="user-box-content"><span class="name">{{ auth()->user()->name }}</span><span class="small color-gray">Profili görüntüle</span></span></span></a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-lg"><a href="{{ url('/messages') }}"><svg width="20" height="20"><use href="#message"/></svg><span>Mesajlar</span></a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-lg"><a href="{{ route('blog.bookmarks') }}"><svg width="20" height="20"><use href="#bookmark"/></svg><span>Kaydedilenler</span></a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-lg"><a href="{{ url('/dashboard') }}"><svg width="20" height="20"><use href="#setting"/></svg><span>Hesap ayarları</span></a></li>
                        @if(auth()->user()->can('viewAny', \App\Models\Post::class) && auth()->user()->hasRole('admin'))
                            <li class="dropdown-menu-item dropdown-menu-item-lg"><a href="{{ url('/admin') }}">Yönetim paneli</a></li>
                        @endif
                        <li class="dropdown-menu-item dropdown-menu-item-lg is-danger">
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><svg width="20" height="20"><use href="#logout"/></svg><span>Çıkış yap</span></button></form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-md btn-secondary btn-rounded">Giriş yap</a>
                <a href="{{ route('register') }}" class="btn btn-md btn-primary btn-rounded">Üye ol</a>
            @endauth
        </div>
    </div>
</header>
@endunless

@php
    $sidebarCategories = $communityCategories ?? ($categories ?? collect());
    $sidebarUsers = $suggestedUsers ?? collect();
    $widgetTags = $popularTags ?? ($tags ?? collect());
@endphp

<main class="container {{ $noContainerPadding ? 'repost-container-bleed' : '' }} {{ $isAuthLayout ? 'repost-auth-layout' : '' }}">
    <div class="main {{ $hideGlobalHeader ? 'repost-editor-grid' : 'main__grid repost-main-grid' }}">
        @unless($hideGlobalHeader ?? false)
        <aside class="sidebar" aria-label="Topluluklar ve üyeler">
            <div class="sidebar__body">
                <section class="sidebar__section">
                    <h2 class="sidebar__title">Topluluklar</h2>
                    <ul class="sidebar__list">
                        @forelse($sidebarCategories->take(8) as $sidebarCategory)
                            <li>
                                <a class="channel-icon {{ $sidebarCategory->profile_image_url ? '' : 'no-icon' }}" href="{{ url('/Categorys/'.$sidebarCategory->slug) }}" title="{{ $sidebarCategory->name }}" aria-label="{{ $sidebarCategory->name }}">
                                    @if($sidebarCategory->profile_image_url)
                                        <img src="{{ $sidebarCategory->profile_image_url }}" alt="" width="50" height="50" loading="lazy">
                                    @else
                                        <span>{{ mb_substr($sidebarCategory->name, 0, 1) }}</span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li><a class="channel-icon no-icon" href="{{ url('/Categorys') }}" title="Toplulukları keşfet"><svg width="18" height="18"><use href="#channels"/></svg></a></li>
                        @endforelse
                    </ul>
                    <a class="sidebar__more" href="{{ url('/Categorys') }}">Tüm topluluklar</a>
                </section>
                <section class="sidebar__section">
                    <h2 class="sidebar__title">Önerilen üyeler</h2>
                    <ul class="sidebar__list">
                        @forelse($sidebarUsers->take(8) as $sidebarUser)
                            <li><a class="channel-icon" href="{{ url('/u/'.$sidebarUser->username) }}" title="{{ $sidebarUser->name }}"><img src="{{ $sidebarUser->profile_photo_url }}" alt="" width="50" height="50" loading="lazy"></a></li>
                        @empty
                            <li><a class="channel-icon no-icon" href="{{ url('/users') }}" title="Üyeleri keşfet"><svg width="18" height="18"><use href="#compass"/></svg></a></li>
                        @endforelse
                    </ul>
                    <a class="sidebar__more" href="{{ url('/users') }}">Üyeleri keşfet</a>
                </section>
            </div>
            <div class="sidebar__footer">
                <a class="btn btn-circle btn-xl btn-secondary-ghost" href="{{ url('/Categorys') }}" title="Toplulukları aç" aria-label="Toplulukları aç"><svg width="24" height="24"><use href="#menu-alt"/></svg></a>
            </div>
        </aside>
        @endunless

        <section class="feed" aria-label="Sayfa içeriği">
            @if($__env->hasSection('content'))
                @yield('content')
            @elseif(isset($slot))
                {{ $slot }}
            @endif
        </section>

        @unless($hideGlobalHeader ?? false)
        <aside class="main__widgets" aria-label="Yan bilgiler">
            <div class="widgets">
                @if($widgetTags->isNotEmpty())
                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">Popüler etiketler</h2></header>
                        <div class="widget__content tagscloud">
                            @foreach($widgetTags->take(12) as $widgetTag)
                                <span><a href="{{ url('/blog/tags?tag='.urlencode($widgetTag->slug ?? $widgetTag->name)) }}">{{ $widgetTag->name }}</a></span>
                            @endforeach
                        </div>
                        <footer class="widget__footer"><a class="link-accent" href="{{ url('/blog/tags') }}">Tüm etiketler</a></footer>
                    </section>
                @endif

                @if($sidebarUsers->isNotEmpty())
                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">Önerilen üyeler</h2></header>
                        <div class="widget__content widget__content--bleed">
                            <ul class="widget__list">
                                @foreach($sidebarUsers->take(5) as $widgetUser)
                                    <li class="user-card"><a class="user-box" href="{{ url('/u/'.$widgetUser->username) }}">
                                        <span class="avatar"><img src="{{ $widgetUser->profile_photo_url }}" alt="" width="40" height="40" loading="lazy"></span>
                                        <span class="user-box-content"><strong class="name"><span class="name-text">{{ $widgetUser->name }}</span></strong><span class="meta">{{ number_format((int)($widgetUser->followers_count ?? 0)) }} takipçi</span></span>
                                    </a></li>
                                @endforeach
                            </ul>
                        </div>
                        <footer class="widget__footer"><a class="link-accent" href="{{ url('/users') }}">Tüm üyeler</a></footer>
                    </section>
                @endif

                @if(isset($popularComments) && $popularComments->isNotEmpty())
                    <section class="widget card card-body">
                        <header><h2 class="widget__title h6">Son yorumlar</h2></header>
                        <div class="widget__content widget__content--bleed">
                            <ul class="widget__list widget__list--separator">
                                @foreach($popularComments->take(5) as $widgetComment)
                                    <li class="comm-card">
                                        <div class="user-box user-box-sm">
                                            <span class="avatar"><img src="{{ $widgetComment->user?->profile_photo_url ?? '' }}" alt="" width="40" height="40" loading="lazy"></span>
                                            <div class="user-box-content"><strong class="name">{{ $widgetComment->user?->name ?? $widgetComment->author_name ?? 'Üye' }}</strong><time class="meta">{{ optional($widgetComment->created_at)->diffForHumans() }}</time></div>
                                        </div>
                                        <div class="comm-card-short text-clamp-3">{{ strip_tags($widgetComment->content) }}</div>
                                        @if($widgetComment->post)<div class="small"><a class="link" href="{{ url('/tr/'.$widgetComment->post->slug) }}">{{ $widgetComment->post->title }}</a></div>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                @endif
                <footer class="footer">
                    <nav class="footer__menu" aria-label="Alt menü">
                        <a class="footer__menu-link" href="{{ url('/contact') }}">İletişim</a>
                        <a class="footer__menu-link" href="{{ url('/sayfa/kurallar') }}">Kurallar</a>
                        <a class="footer__menu-link" href="{{ route('users.index') }}">Üyeler</a>
                    </nav>
                    <small class="color-gray">OgraFi © {{ date('Y') }}</small>
                </footer>
            </div>
        </aside>
        @endunless
    </div>
</main>

@unless($hideGlobalHeader || $isAuthLayout)
<section class="mobmenu" data-scroll-menu>
    <nav class="mobmenu__content" aria-label="Mobil menü">
        <a class="mobmenu__link is-active" href="{{ url('/') }}"><svg width="20" height="20"><use href="#home"/></svg><span>Akış</span></a>
        <button class="mobmenu__link" type="button" data-side-panel-open="mobileAside" aria-controls="mobileAside"><svg width="20" height="20"><use href="#compass"/></svg><span>Keşfet</span></button>
        <a class="mobmenu__link" href="{{ url('/Categorys') }}"><svg width="20" height="20"><use href="#channels"/></svg><span>Topluluklar</span></a>
    </nav>
</section>
<div class="side-panel side-panel-start mobile-side-panel" id="mobileAside" data-side-panel="mobileAside" aria-hidden="true">
    <div class="side-panel__dialog" role="dialog" aria-modal="true" tabindex="-1">
        <header class="side-panel__header"><a href="{{ url('/') }}" class="logo"><span class="logo-icon">O</span><span class="logo-text">OgraFi</span></a><button class="btn btn-secondary btn-circle side-panel__close" type="button" data-side-panel-close aria-label="Kapat">×</button></header>
        <div class="side-panel__body">
            <nav class="menu">
                <a class="menu__link" href="{{ url('/') }}">Ana akış</a>
                <a class="menu__link" href="{{ url('/discover') }}">Keşfet</a>
                <a class="menu__link" href="{{ url('/Categorys') }}">Topluluklar</a>
                <a class="menu__link" href="{{ url('/users') }}">Üyeler</a>
                @auth<a class="menu__link" href="{{ url('/blog/create') }}">Gönderi oluştur</a>@endauth
            </nav>
        </div>
    </div>
</div>
@endunless
<x-cookie-banner />
<script>
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-url]');
    if (!button) return;
    const url = button.dataset.copyUrl;
    try {
        await navigator.clipboard.writeText(url);
        const previous = button.getAttribute('aria-label') || button.getAttribute('title') || '';
        button.setAttribute('aria-label', 'Bağlantı kopyalandı');
        button.setAttribute('title', 'Bağlantı kopyalandı');
        window.setTimeout(() => {
            if (previous) {
                button.setAttribute('aria-label', previous);
                button.setAttribute('title', previous);
            }
        }, 1800);
    } catch (error) {
        window.prompt('Bağlantıyı kopyala:', url);
    }
});
</script>
@stack('scripts')
</body>
</html>

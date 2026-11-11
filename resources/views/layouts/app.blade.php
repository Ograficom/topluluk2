<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#f7f9fd">
    <title>@yield('title', $brandName) — {{ $brandName }}</title>
    @php
        $seoDescription = trim($__env->yieldContent('description', $seoSettings?->seo_default_description ?: 'OgraFi; teknoloji, gündem, yaşam ve ilgi alanlarında yazıların, fikirlerin ve topluluk sohbetlerinin buluştuğu platformdur.'));
        $seoTitle = trim($__env->yieldContent('title', $seoSettings?->seo_default_title ?: $brandName));
        $seoCanonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $seoImage = trim($__env->yieldContent('seo_image'));
        if ($seoImage === '') {
            $defaultSeoImage = $seoSettings?->seo_og_image;
            $seoImage = filled($defaultSeoImage)
                ? (filter_var($defaultSeoImage, FILTER_VALIDATE_URL)
                    ? $defaultSeoImage
                    : \Illuminate\Support\Facades\Storage::disk('public')->url($defaultSeoImage))
                : ($brandLogoUrl ?? asset('assets/img/meta/logo.svg'));
        }
        $seoRobotsOverride = trim($__env->yieldContent('robots'));
        $seoKeywords = trim($__env->yieldContent('seo_keywords', $seoSettings?->seo_default_keywords ?? ''));
        $seoNoindex = $seoRobotsOverride === 'noindex,follow' || request()->filled('q')
            || request()->filled('sort')
            || request()->filled('period')
            || request()->filled('comments')
            || request()->filled('reply_to')
            || request()->routeIs(
                'login', 'legacy.signin', 'register', 'legacy.signup', 'password.*',
                'posts.create', 'posts.edit', 'channels.create', 'channels.edit',
                'settings.*', 'favorites.*', 'messenger.*', 'subscriptions.*',
                'community.following', 'advertisements.*', 'verification.*'
            );
        if ((int) request()->query('page', 1) > 1 && ! request()->filled('sort') && ! request()->filled('q')) {
            $seoCanonical .= '?page='.max(1, (int) request()->query('page'));
        }
    @endphp
    <meta name="description" content="{{ $seoDescription }}">
    @if (filled($seoKeywords))
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    <meta name="robots" content="{{ $seoNoindex ? 'noindex,follow' : ($seoRobotsOverride ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1') }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta property="og:locale" content="tr_TR">
    @if (filled(config('services.facebook.app_id')))
        <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">
    @endif
    <meta property="og:site_name" content="{{ $brandName }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $seoTitle }} — {{ $brandName }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }} — {{ $brandName }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <link rel="icon" href="{{ $faviconUrl ?? asset('assets/img/meta/logo.svg') }}" type="{{ $faviconMimeType ?? 'image/svg+xml' }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $brandName }} RSS" href="{{ url('/rss.xml') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fancyapp/fancybox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fancyapp/carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kit.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v=20261110-santral-real-brand-logos-hint-gap">
    <link rel="stylesheet" href="{{ asset('assets/css/engine.css') }}?v=20261110-santral-hero-buttons-inset-blue">
    <link rel="stylesheet" href="{{ asset('assets/css/ografi-overrides.css') }}?v=20261111-santral-dark-theme">
    <script>
        (() => {
            const root = document.documentElement;
            let theme = null;

            try {
                theme = localStorage.getItem('theme');
            } catch (error) {
                theme = null;
            }

            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            root.dataset.theme = theme;
            root.dataset.bsTheme = theme;
            root.style.colorScheme = theme;
        })();
    </script>
    @stack('head')
</head>
<body @class(['is-messenger-conversation' => request()->routeIs('messenger.show')])>
    <div id="repost-icon-sprite" class="sprite" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" fill="none" style="visibility:hidden;position:absolute" aria-hidden="true">
            <symbol id="message" viewBox="0 0 24 24">
                <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 8.4 8.4 0 0 1-3.6-.8L3 21l1.8-4.9A8.4 8.4 0 0 1 4 12.5 8.5 8.5 0 1 1 21 11.5Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </symbol>
        </svg>
    </div>

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
                @auth
                    <a href="{{ route('posts.create') }}" class="btn btn-md btn-white btn-rounded shadow-sm header__write">
                        <x-repost-icon name="pen" size="18" />
                        <span>Yaz</span>
                    </a>
                @endauth
                @auth
                    @php
                        $notificationUser = auth()->user();
                        $visibleNotificationCategories = collect(['replies', 'posts', 'announcements', 'followers', 'messages', 'mentions', 'security'])
                            ->filter(fn (string $category): bool => (bool) data_get($notificationUser->notification_preferences ?? [], 'stream.'.$category, true))
                            ->values()
                            ->all();
                        $visibleNotifications = fn ($query) => $query->where(function ($items) use ($visibleNotificationCategories): void {
                            $items->whereNull('data->category');
                            if ($visibleNotificationCategories !== []) {
                                $items->orWhereIn('data->category', $visibleNotificationCategories);
                            }
                        });
                        $headerNotifications = $notificationUser->notifications()
                            ->where($visibleNotifications)
                            ->latest()
                            ->limit(8)
                            ->get();
                        $unreadNotificationCount = $notificationUser->unreadNotifications()
                            ->where($visibleNotifications)
                            ->count();
                    @endphp
                    <div class="dropdown dropdown-md notification-dropdown">
                        <button id="notificationMenuButton" type="button" class="btn btn-secondary-ghost btn-circle notification-trigger" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-controls="notificationMenu" aria-label="Bildirimler{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' okunmamış' : '' }}">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @if ($unreadNotificationCount > 0)
                                <span class="notification-trigger__count">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                                <span class="notification-trigger__dot" aria-hidden="true"></span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-menu" id="notificationMenu" aria-labelledby="notificationMenuButton">
                            <div class="notification-menu__head">
                                <strong>Bildirimler</strong>
                                @if ($unreadNotificationCount > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit">Tümünü okundu işaretle</button>
                                    </form>
                                @endif
                            </div>
                            <div class="notification-menu__list">
                                @forelse ($headerNotifications as $notification)
                                    @php
                                        $notificationTitle = data_get($notification->data, 'title', 'Yeni bildirim');
                                        $notificationBody = data_get($notification->data, 'message', data_get($notification->data, 'body', 'Hesabında yeni bir hareket var.'));
                                    @endphp
                                    <div class="notification-item{{ $notification->read_at ? '' : ' is-unread' }}">
                                        <a href="{{ route('notifications.open', $notification->id) }}" class="notification-item__main">
                                            <span class="notification-item__icon">
                                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true"><path d="M12 3v18M3 12h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                            </span>
                                            <span class="notification-item__content">
                                                <strong>{{ $notificationTitle }}</strong>
                                                <span>{{ $notificationBody }}</span>
                                                <time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->diffForHumans() }}</time>
                                            </span>
                                            @if (! $notification->read_at)<span class="notification-item__unread" aria-label="Okunmadı"></span>@endif
                                        </a>
                                        <details class="notification-item__actions">
                                            <summary aria-label="Bildirim seçenekleri">
                                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg>
                                            </summary>
                                            <div class="notification-item__actions-menu">
                                                <form method="POST" action="{{ route($notification->read_at ? 'notifications.mark-unread' : 'notifications.mark-read', $notification->id) }}">
                                                    @csrf
                                                    <button type="submit">{{ $notification->read_at ? 'Okunmamış olarak işaretle' : 'Okundu olarak işaretle' }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" onsubmit="return confirm('Bu bildirimi silmek istediğine emin misin?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="is-danger">Silmek</button>
                                                </form>
                                            </div>
                                        </details>
                                    </div>
                                @empty
                                    <div class="notification-empty">
                                        <span class="notification-empty__icon"><svg viewBox="0 0 24 24" width="25" height="25" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                        <strong>Henüz bildirimin yok</strong>
                                        <span>Yeni etkileşimler burada görünecek.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <style>
                        .notification-trigger{position:relative!important;overflow:visible!important}
                        .notification-trigger__count{position:absolute;top:-4px;right:-5px;min-width:18px;height:18px;padding:0 4px;display:grid;place-items:center;border:2px solid var(--body-bg,#fff);border-radius:20px;background:#2563eb;color:#fff;font-size:10px;font-weight:800;line-height:1}
                        .notification-trigger__dot{position:absolute;top:1px;right:1px;width:8px;height:8px;border:2px solid #fff;border-radius:50%;background:#ef4444}
                        .notification-menu{width:min(360px,calc(100vw - 24px));max-height:470px;padding:0!important;overflow:hidden}
                        .notification-menu__head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 16px;border-bottom:1px solid var(--dropdown-menu-border-color,#e5e7eb)}
                        .notification-menu__head strong{font-size:15px;font-weight:800}
                        .notification-menu__head form button{padding:0;border:0;background:none;color:#2563eb;font-size:11px;font-weight:700;cursor:pointer}
                        .notification-menu__list{max-height:390px;overflow-y:auto}
                        .notification-item{position:relative;display:flex;align-items:flex-start;gap:5px;padding:10px 10px 10px 13px;border-bottom:1px solid rgba(148,163,184,.16)}
                        .notification-item:hover{background:rgba(148,163,184,.08)}
                        .notification-item.is-unread{background:rgba(37,99,235,.045)}
                        .notification-item__main{display:flex;align-items:flex-start;gap:11px;flex:1;min-width:0;padding:3px 0;text-decoration:none!important}
                        .notification-item__icon{display:grid;place-items:center;flex:none;width:34px;height:34px;border-radius:11px;background:#eff6ff;color:#2563eb}
                        .notification-item__actions{position:relative;flex:none;margin-left:auto}
                        .notification-item__actions summary{display:grid;place-items:center;width:30px;height:30px;border-radius:8px;color:#8b93a1;list-style:none;cursor:pointer}
                        .notification-item__actions summary::-webkit-details-marker{display:none}
                        .notification-item__actions summary:hover,.notification-item__actions[open] summary{background:rgba(148,163,184,.13);color:var(--body-color,#172033)}
                        .notification-item__actions-menu{position:absolute;z-index:30;top:calc(100% + 5px);right:0;width:210px;padding:6px;background:var(--dropdown-menu-bg,#fff);border:1px solid var(--dropdown-menu-border-color,#e5e7eb);border-radius:13px;box-shadow:0 8px 24px rgba(15,23,42,.13)}
                        .notification-item__actions-menu form{margin:0}
                        .notification-item__actions-menu button{display:block;width:100%;padding:10px 11px;border:0;border-radius:8px;background:transparent;color:var(--body-color,#374151);font-size:12px;text-align:left;cursor:pointer}
                        .notification-item__actions-menu button:hover{background:rgba(148,163,184,.12)}
                        .notification-item__actions-menu button.is-danger{color:#ef4444}
                        [data-theme="dark"] .notification-item__actions-menu{--dropdown-menu-bg:#202938;--dropdown-menu-border-color:#374151}
                        .notification-item__content{display:flex;flex:1;min-width:0;flex-direction:column;gap:3px}
                        .notification-item__content strong{color:var(--body-color,#172033);font-size:12px;font-weight:750}
                        .notification-item__content>span{color:var(--text-muted,#718096);font-size:11px;line-height:1.45;overflow-wrap:anywhere}
                        .notification-item__content time{margin-top:2px;color:var(--text-muted,#94a3b8);font-size:10px}
                        .notification-item__unread{flex:none;width:7px;height:7px;margin-top:7px;border-radius:50%;background:#2563eb}
                        .notification-empty{display:flex;align-items:center;flex-direction:column;gap:7px;padding:30px 15px;text-align:center}
                        .notification-empty__icon{display:grid;place-items:center;width:48px;height:48px;border-radius:50%;background:#f1f5f9;color:#94a3b8}
                        .notification-empty strong{font-size:13px}
                        .notification-empty>span:last-child{color:var(--text-muted,#718096);font-size:11px}
                        [data-theme="dark"] .notification-trigger__dot{border-color:#171e29}
                        [data-theme="dark"] .notification-item__icon,[data-theme="dark"] .notification-empty__icon{background:#1e3150;color:#93c5fd}
                        [data-theme="dark"] .notification-item__actions summary:hover,[data-theme="dark"] .notification-item__actions[open] summary{color:#e5e7eb}
                    </style>
                    @php($favoriteCount = auth()->user()->bookmarks()->count())
                    <div class="dropdown dropdown-md">
                        <button id="userMenuButton" type="button" @class(['btn btn-secondary-ghost btn-circle avatar', 'newpm' => $favoriteCount > 0]) data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-controls="userMenu" aria-label="{{ auth()->user()->displayName() }}">
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="" width="40" height="40">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="userMenu" aria-labelledby="userMenuButton">
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a class="login__profile" href="{{ auth()->user()->username ? route('profiles.show', ['identifier' => auth()->user()->username]) : route('settings.edit') }}">
                                    <span class="user-box">
                                        <span class="avatar"><img src="{{ auth()->user()->avatarUrl() }}" alt="" width="40" height="40"></span>
                                        <span class="user-box-content">
                                            <span class="name">
                                                <span class="name-text">{{ auth()->user()->displayName() }}</span>
                                                @if (auth()->user()->hasVerifiedCompanyBadge())
                                                    <x-company-verified-badge size="14" />
                                                @endif
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
                                <a href="{{ route('favorites.index') }}"><x-repost-icon name="bookmark" size="22" class="color-gray" /><span>Favoriler</span>@if ($favoriteCount > 0)<span class="dropdown-menu-count">{{ number_format($favoriteCount) }}</span>@endif</a>
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
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('advertisements.index') }}"><x-repost-icon name="feed" size="20" class="color-gray" /><span>Reklam yöneticisi</span><span style="display:inline-flex;align-items:center;justify-content:center;margin-left:auto;padding:3px 7px;border-radius:6px;background:#2563eb;color:#fff;font-size:10px;font-weight:700;line-height:1.2;">Yeni</span></a>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('santral') }}"><x-repost-icon name="message" size="20" class="color-gray" /><span>Santral hizmeti</span><span style="display:inline-flex;align-items:center;justify-content:center;margin-left:auto;padding:3px 7px;border-radius:6px;background:#2563eb;color:#fff;font-size:10px;font-weight:700;line-height:1.2;">Yeni</span></a>
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
    @php($postCreatePage = request()->routeIs('posts.create', 'posts.edit'))
    @php($feedPage = request()->routeIs('posts.index'))
    @php($channelsIndexPage = request()->routeIs('channels.index', 'legacy.channels'))
    @php($statsPage = request()->routeIs('stats.index'))
    @php($verificationPage = request()->routeIs('verification.notice'))
    <main class="container">
        <div class="main {{ $settingsPage || $profilePage || $channelPage ? 'main__grid-full' : 'main__grid' }}">
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
                    @if (session('status') && ! $verificationPage)
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
                        @if ($verificationPage)
                            <div role="status" style="display:inline-flex;align-items:center;gap:10px;padding:8px 14px;background:#2563eb;color:#fff;border-radius:8px;box-shadow:0 1px 2px rgb(0 0 0 / 8%);font-size:14px;">
                                <svg style="width:16px;height:16px;flex-shrink:0;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span style="font-weight:500;line-height:1;">{{ session('status') }}</span>
                            </div>
                        @else
                            <div class="alert alert-success" role="status">{{ session('status') }}</div>
                        @endif
                    @endif
                    @yield('content')
                @if ($statsPage || $channelsIndexPage)
                    </article>
                @else
                    </section>
                @endif

                @unless ($profilePage || $channelPage)
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
                                                        @if ($author->hasVerifiedCompanyBadge())
                                                                <x-company-verified-badge size="14" />
                                                            @endif
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
                            <header>
                                <h2 class="widget__title h6">Popüler yazılar</h2>
                            </header>
                            <div class="widget__content widget__content--bleed">
                                <ul class="widget__list widget__list--separator">
                                    @forelse ($sidebarPosts as $post)
                                        <li class="entry-card">
                                            <article class="entry-box {{ $post->coverImageUrl() ? 'has-image' : '' }}">
                                                <div class="entry-box-content">
                                                    <div class="user-box">
                                                        @if ($post->user)
                                                            <a href="{{ route('profiles.show', $post->user) }}" class="avatar" aria-label="{{ $post->user->displayName() }} profili">
                                                                <img src="{{ $post->user->avatarUrl() }}" alt="" width="40" height="40" loading="lazy">
                                                            </a>
                                                        @else
                                                            <span class="avatar">
                                                                <img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40" loading="lazy">
                                                            </span>
                                                        @endif
                                                        <div class="user-box-content">
                                                            @if ($post->user)
                                                                <a href="{{ route('profiles.show', $post->user) }}" class="name">
                                                                    <span class="name-text">{{ $post->user->displayName() }}</span>
                                                                    @if ($post->user->hasVerifiedCompanyBadge())
                                                                        <x-company-verified-badge size="14" />
                                                                    @endif
                                                                    @if ($post->user->hasVerifiedBadge())
                                                                        <span class="verify" role="img" aria-label="Doğrulanmış profil" title="Doğrulanmış profil">
                                                                            <x-repost-icon name="verify" size="14" />
                                                                        </span>
                                                                    @endif
                                                                </a>
                                                            @else
                                                                <span class="name"><span class="name-text">OgraFi yazarı</span></span>
                                                            @endif
                                                            <time class="meta" datetime="{{ ($post->published_at ?? $post->created_at)->toAtomString() }}">{{ ($post->published_at ?? $post->created_at)->translatedFormat('d F Y') }}</time>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('posts.show', $post) }}" class="text-clamp-2 color-text">{{ $post->title }}</a>
                                                    <div class="meta">
                                                        <span class="meta-item"><x-repost-icon name="comment" size="14" />{{ $post->comments_count }}</span>
                                                        <span class="meta-item"><x-repost-icon name="eye" size="14" />{{ number_format($post->views) }}</span>
                                                    </div>
                                                </div>
                                                @if ($post->coverImageUrl())
                                                    <a href="{{ route('posts.show', $post) }}" class="entry-box-image" aria-label="{{ $post->title }}">
                                                        <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->title }}" width="100" height="80" loading="lazy">
                                                    </a>
                                                @endif
                                            </article>
                                        </li>
                                    @empty
                                        <li class="entry-card">
                                            <span class="small color-gray">Henüz yayınlanmış yazı yok</span>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                            <footer class="widget__footer">
                                <a href="{{ route('posts.index') }}" class="link-accent">Tüm yazılar</a>
                            </footer>
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
                                                            @if ($comment->user->hasVerifiedCompanyBadge())
                    <x-company-verified-badge size="14" />
                @endif
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

    @unless (request()->routeIs('messenger.show'))
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
    @endunless

    @include('layouts._mobile-side-panel')

    <button class="btn btn-white btn-md btn-circle gotop" type="button" data-gotop aria-label="Yukarı çık" aria-hidden="true" tabindex="-1">
        <x-repost-icon name="chevron-up" size="18" class="color-gray" />
    </button>

    <script src="{{ asset('assets/libs/fancyapp/fancybox.umd.js') }}"></script>
    <script src="{{ asset('assets/libs/fancyapp/carousel.umd.js') }}"></script>
    <script src="{{ asset('assets/libs/sharer/sharer.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}?v=20261110-santral-art-top-centered"></script>
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
    @auth
    <script>
    (() => {
        const ping = () => {
            if (document.visibilityState !== 'visible') return;

            fetch(@json(route('presence.ping')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).catch(() => {});
        };

        ping();
        window.setInterval(ping, 60000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') ping();
        });


    })();
    </script>
    @endauth
    @include('layouts._cookie-consent')
    @stack('scripts')
</body>
</html>

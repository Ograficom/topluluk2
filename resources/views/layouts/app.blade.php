<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    @php
        $pwaSettings = \App\Models\SiteSetting::current();
        $newYearSettings = \App\Support\NewYearMode::settings($pwaSettings);
        $newYearActive = \App\Support\NewYearMode::active($newYearSettings) && ! request()->routeIs('messenger.show');
    @endphp
    <meta name="theme-color" content="{{ $pwaSettings?->pwa_theme_color ?? '#f7f9fd' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    @if ($pwaSettings?->pwa_enabled ?? true)
        <link rel="manifest" href="{{ route('pwa.manifest', [], false) }}">
        <link rel="apple-touch-icon" href="{{ \App\Http\Controllers\PwaIconController::manifestIcons($pwaSettings)[0]['src'] }}">
    @endif
    <title>@yield('title', $brandName) — {{ $brandName }}</title>
    <style>
        .pwa-titlebar { display: none; }
        @media (display-mode: window-controls-overlay) {
            .pwa-titlebar { display: flex; align-items: center; position: fixed; z-index: 10000;
                left: env(titlebar-area-x, 0px); top: env(titlebar-area-y, 0px);
                width: env(titlebar-area-width, 100%); height: env(titlebar-area-height, 32px);
                padding-inline: 12px; background: #2563eb; color: #fff;
                font: 600 13px system-ui; -webkit-app-region: drag; app-region: drag; }
            body { padding-top: env(titlebar-area-height, 32px); }
            .header { top: env(titlebar-area-height, 32px); }
        }

        .ografi-store-buttons {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .ografi-store-buttons .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0 10px;
            min-height: 28px;
            border-radius: 10px;
            color: var(--gray-500);
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            transition: transform .18s ease, background-color .2s ease, color .2s ease;
        }
        .ografi-store-buttons svg {
            width: 15px;
            height: 15px;
            flex: 0 0 15px;
        }
        @media (hover: hover) {
            .ografi-store-buttons .btn:hover {
                color: var(--gray-700);
                transform: translateY(-1px);
            }
        }
        .ografi-store-buttons .btn:active { transform: scale(.92); }
        .ografi-store-buttons .btn:focus-visible {
            outline: 2px solid var(--gray-500);
            outline-offset: 3px;
        }
        .ografi-store-bottom { display: none; padding: 12px 0 24px; }
        .ografi-store-bottom--standalone { display: block; }
        .ografi-store-bottom .ografi-store-buttons { justify-content: center; }
        @media screen and (max-width: 920px) {
            .ografi-store-bottom { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ografi-store-buttons .btn { transition: none; }
            .ografi-store-buttons .btn:hover,
            .ografi-store-buttons .btn:active { transform: none; }
        }
    </style>
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
        $seoNoindex = $seoRobotsOverride === 'noindex,follow'
            || (request()->routeIs('posts.index') && $seoSettings && ! $seoSettings->home_seo_index)
            || request()->filled('q')
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
    @php
        $isIndexableHomepage = request()->routeIs('posts.index')
            && ! request()->filled('q')
            && ! request()->filled('sort')
            && ! request()->filled('period');
    @endphp
    <meta name="robots" content="{{ $isIndexableHomepage ? 'index,follow' : ($seoNoindex ? 'noindex,follow' : ($seoRobotsOverride ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1')) }}">
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
    <link rel="stylesheet" href="{{ asset('assets/css/kit.css') }}?v=repost-original-20261124">
    <link rel="stylesheet" href="{{ asset('assets/css/ografi-kit-extensions.css') }}?v=20261124-v1">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v=repost-original-20261124">
    <link rel="stylesheet" href="{{ asset('assets/css/ografi-app-extensions.css') }}?v=20261125-gallery-visible-v2">
    <link rel="stylesheet" href="{{ asset('assets/css/engine.css') }}?v=repost-original-20261124">
    <link rel="stylesheet" href="{{ asset('assets/css/ografi-engine-extensions.css') }}?v=20261124-v1">
    <link rel="stylesheet" href="{{ asset('assets/css/ografi-overrides.css') }}?v=20261124-mobile-header-drawer-v2">
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

    <script>
        (() => {
            const storeAnimations = new WeakMap();
            document.addEventListener('click', event => {
                const button = event.target instanceof Element
                    ? event.target.closest('.ografi-store-buttons a') : null;
                if (!button || !button.animate || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                storeAnimations.get(button)?.cancel();
                const animation = button.animate([
                    { transform: 'scale(.92)' },
                    { transform: 'scale(1.04)', offset: .6 },
                    { transform: 'scale(1)' }
                ], { duration: 280, easing: 'ease-out' });
                storeAnimations.set(button, animation);
            });
        })();
    </script>
    @vite('resources/js/app.js')
    <link rel="stylesheet" href="{{ asset('assets/css/post-edit-history.css') }}?v=2">
    @stack('head')
    @if (! request()->routeIs('cp.*'))
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                '@id' => 'https://ografi.com/#organization',
                'name' => 'OgraFi',
                'url' => 'https://ografi.com/',
                'description' => $seoSettings?->seo_default_description ?: 'OgraFi; teknoloji, gündem, yaşam ve ilgi alanlarında yazıların, fikirlerin ve topluluk sohbetlerinin buluştuğu platformdur.',
                'telephone' => '+908503059806',
                'logo' => $brandLogoUrl ?? asset('assets/img/meta/logo.svg'),
                'areaServed' => [
                    '@type' => 'Country',
                    'name' => 'Türkiye',
                ],
                'contactPoint' => [[
                    '@type' => 'ContactPoint',
                    'telephone' => '+908503059806',
                    'contactType' => 'customer service',
                    'availableLanguage' => ['Turkish'],
                    'areaServed' => 'TR',
                ]],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
        </script>
    @endif
    @if ($newYearActive)
        @include('layouts._new-year-styles')
    @endif
</head>
<body data-authenticated="{{ auth()->check() ? 'true' : 'false' }}" @class(['is-messenger-conversation' => request()->routeIs('messenger.show')])>
    <div id="repost-icon-sprite" class="sprite" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" fill="none" style="visibility:hidden;position:absolute" aria-hidden="true">
            <symbol id="message" viewBox="0 0 24 24">
                <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 8.4 8.4 0 0 1-3.6-.8L3 21l1.8-4.9A8.4 8.4 0 0 1 4 12.5 8.5 8.5 0 1 1 21 11.5Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </symbol>
            <symbol id="more" viewBox="0 0 24 24">
                <circle cx="5" cy="12" r="2" fill="currentColor"/><circle cx="12" cy="12" r="2" fill="currentColor"/><circle cx="19" cy="12" r="2" fill="currentColor"/>
            </symbol>
        </svg>
    </div>

    <div class="pwa-titlebar" aria-hidden="true">OgraFi</div>
    <header class="header">
        @if (($pwaSettings?->pwa_enabled ?? true) && ! request()->routeIs('messenger.show'))
            @include('layouts._app-install-banner')
        @endif
        <div class="header__inner container">
            <div class="header__start">
                <a href="{{ route('posts.index') }}" class="logo header__logo{{ $newYearActive && $newYearSettings['logo'] ? ' ografi-ny-logo' : '' }}" aria-label="{{ $brandName }} ana sayfa">
                    <img
                        src="{{ $brandLogoUrl ?? asset('assets/img/meta/logo.svg') }}"
                        alt=""
                        width="36"
                        height="36"
                        @class(['logo-icon', 'logo-icon--custom' => $brandLogoUrl])
                        onerror="this.onerror=null;this.src='{{ asset('assets/img/meta/logo.svg') }}';"
                    >
                    @if ($newYearActive && $newYearSettings['logo'])
                        <svg class="ografi-ny-hat" viewBox="0 0 32 30" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 22C6 10 13 1 23 5c4 2 4 6 5 10l-6 2-3-5-4 11Z" fill="#d93648"/>
                            <rect x="3" y="20" width="23" height="7" rx="3.5" fill="#fff" stroke="#cde5f8"/>
                            <circle cx="27" cy="16" r="4" fill="#fff" stroke="#cde5f8"/>
                        </svg>
                    @endif
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
                    <a href="{{ route('posts.create') }}" class="btn btn-md btn-white btn-rounded shadow-sm header__write" data-post-composer-trigger aria-label="Yeni gönderi" title="Yeni gönderi">
                        <x-repost-icon name="pen" size="18" />
                        <span>Yaz</span>
                    </a>
                @endauth
                @auth
                    @php
                        $notificationUser = auth()->user();
                        $visibleNotifications = \App\Http\Controllers\NotificationController::visibleFilter($notificationUser);
                        $headerNotifications = $notificationUser->notifications()
                            ->where($visibleNotifications)
                            ->latest()
                            ->limit(30)
                            ->get();
                        $unreadNotificationCount = $notificationUser->unreadNotifications()
                            ->where($visibleNotifications)
                            ->count();
                    @endphp
                    <button type="button" class="notification-sheet-backdrop" aria-label="Bildirimleri kapat" aria-hidden="true" tabindex="-1"></button>
                    <style>
                        #desktopUserMenuButton{position:relative!important;overflow:visible!important}
                        #desktopUserMenuButton [data-notification-dot]{position:absolute;z-index:2;top:0;right:0;width:9px;height:9px;box-sizing:border-box;border:2px solid var(--body-bg,#fff);border-radius:50%;background:#ef4444;pointer-events:none}
                        #desktopUserMenuButton [data-notification-dot][hidden]{display:none!important}
                        .notification-menu .notification-item__actions-menu{position:static!important;width:100%;min-width:140px;max-width:100%;box-shadow:none;margin:4px 0}
                        .notification-menu .notification-item:has(details[open]){flex-wrap:wrap}
                        .notification-menu .notification-item:has(details[open]) .notification-item__actions{flex:0 0 100%;display:flex;flex-direction:column;align-items:flex-end}
                        .notification-menu__footer{display:flex;flex:none;gap:8px;flex-wrap:wrap;padding:8px 12px;border-top:1px solid var(--border-color)}
                        .notification-menu__footer form{margin:0}
                        .notification-menu__footer button{border:0;background:transparent;color:var(--color-primary);font:inherit;font-size:12px;cursor:pointer;padding:6px}
                        .notification-menu__footer .is-danger,.notification-menu__error{color:#ef4444}
                        .notification-menu__error{font-size:12px;padding:8px 12px}
                        .notification-menu__error[hidden]{display:none!important}
                        #desktopUserMenu [data-notification-count][hidden],#notificationDropdownToggle{display:none!important}
                        .notification-dropdown{position:absolute!important;inset:0 0 0 auto!important;width:0!important;min-width:0!important;pointer-events:none}
                        .notification-dropdown.is-open > .notification-menu{pointer-events:auto}
                        .notification-menu__head .notification-drawer-close{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;padding:0}
                        .notification-menu__head .notification-menu__actions{display:flex;align-items:center}
                        .notification-trigger{position:relative!important;overflow:visible!important}
                        .notification-trigger__count{position:absolute;top:-4px;right:-5px;min-width:18px;height:18px;padding:0 4px;display:grid;place-items:center;border:2px solid var(--body-bg,#fff);border-radius:20px;background:#2563eb;color:#fff;font-size:10px;font-weight:800;line-height:1}
                        .notification-trigger__dot{position:absolute;top:1px;right:1px;width:8px;height:8px;border:2px solid #fff;border-radius:50%;background:#ef4444}
                        .notification-menu{width:min(360px,calc(100vw - 24px));max-height:470px;padding:0!important;overflow:hidden}
                        .notification-menu__head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border-bottom:1px solid var(--dropdown-menu-border-color,#e5e7eb)}
                        .notification-menu__head strong{font-size:15px;font-weight:800;flex:none}
                        .notification-menu__head .notification-live-status{margin-left:auto!important;flex:none!important}
                        .notification-menu__list{max-height:390px;overflow-y:auto}
                        .notification-item{position:relative;display:flex;align-items:flex-start;gap:5px;padding:10px 10px 10px 13px;border-bottom:1px solid rgba(148,163,184,.16)}
                        /* Notification rows follow the actual Repost messenger list styling */
                        .notification-menu{background:var(--card-bg);border:1px solid var(--border-color);border-radius:14px}
                        .notification-menu__head{background:var(--card-bg)}
                        .notification-item{
                            position:relative;display:flex;min-width:0;align-items:center;gap:8px;
                            margin:3px 6px;padding:10px 6px 10px 10px;border:0;border-radius:12px;
                            background:transparent;transition:background-color var(--pm-transition);
                        }
                        .notification-item:last-child{border-bottom:0}
                        .notification-item:hover,.notification-item:focus-within{background-color:var(--pm-secondary-light)}
                        .notification-item.is-unread,.notification-item.is-unread:hover{background-color:transparent}
                        .notification-item.is-unread:focus-within,.notification-item.is-unread:hover{background-color:var(--pm-secondary-light)}
                        .notification-item__main{display:flex;min-width:0;flex:1 1 auto;align-items:center;gap:12px;padding:2px 0;text-decoration:none!important}
                        .notification-item__icon{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;border-radius:11px;background:var(--pm-secondary);color:var(--color-primary)}
                        .notification-item__content{display:flex;min-width:0;flex:1 1 auto;flex-direction:column;gap:3px}
                        .notification-item__content strong{display:block;overflow:hidden;color:var(--title-color);font-family:var(--font-secondary);font-size:.9rem;font-weight:700;line-height:1.3;text-overflow:ellipsis}
                        .notification-item__content>span{display:-webkit-box;overflow:hidden;color:var(--pm-muted);font-size:.8rem;line-height:1.4;-webkit-line-clamp:2;-webkit-box-orient:vertical}
                        .notification-item__content time{margin-top:2px;color:var(--pm-muted);font-size:.7rem;line-height:1.3}
                        .notification-item__unread{width:7px;height:7px;flex:0 0 7px;margin:0 3px 0 0;border-radius:50%;background:var(--color-primary)}
                        .notification-item__actions{position:relative;z-index:3;flex:none;margin-left:0}
                        .notification-item__actions summary{display:flex;width:34px;height:34px;align-items:center;justify-content:center;border:0;border-radius:10px;background:transparent;color:var(--pm-muted);cursor:pointer}
                        .notification-item__actions summary:hover,.notification-item__actions[open] summary{background:transparent;color:var(--text-color)}
                        .notification-item__actions .dropdown-menu{min-width:190px}
                        [data-theme="dark"] .notification-item{border:0}
                        [data-theme="dark"] .notification-item__icon{background:var(--pm-secondary);color:var(--color-primary)}
                        .notification-item:hover{background:rgba(148,163,184,.08)}
                        .notification-item.is-unread{background:rgba(37,99,235,.045)}
                        .notification-item__main{display:flex;align-items:flex-start;gap:11px;flex:1;min-width:0;padding:3px 0;text-decoration:none!important}
                        .notification-item__icon{display:grid;place-items:center;flex:none;width:34px;height:34px;border-radius:11px;background:#eff6ff;color:#2563eb}
                        .notification-item__icon svg{display:block;width:18px;height:18px;stroke:currentColor;fill:none}
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

                        .notification-sheet-backdrop{display:none}
                        @media (max-width: 767px){
                            body:is(.notification-sheet-open,:has(.notification-dropdown.is-open),:has(.notification-dropdown.show),:has(#notificationMenu.show)){overflow:hidden!important;overscroll-behavior:none}
                            body:is(.notification-sheet-open,:has(.notification-dropdown.is-open),:has(.notification-dropdown.show),:has(#notificationMenu.show)) > .header{z-index:120000!important}
                            body:is(.notification-sheet-open,:has(.notification-dropdown.is-open),:has(.notification-dropdown.show),:has(#notificationMenu.show)) :is(.mobmenu,.gotop){visibility:hidden!important;opacity:0!important;pointer-events:none!important}
                            .notification-sheet-backdrop{display:block;position:fixed;inset:0;z-index:119998;width:100%;height:100%;padding:0;border:0;background:var(--color-overlay-backdrop,rgba(15,23,42,.45));opacity:0;visibility:hidden;pointer-events:none;transition:opacity .24s ease,visibility .24s ease}
                            body:is(.notification-sheet-open,:has(.notification-dropdown.is-open),:has(.notification-dropdown.show),:has(#notificationMenu.show)) .notification-sheet-backdrop{opacity:1;visibility:visible;pointer-events:auto}
                            .notification-dropdown > .notification-menu{
                                position:fixed!important;inset:0 0 0 auto!important;z-index:119999!important;
                                display:flex!important;flex-direction:column!important;gap:0!important;
                                width:min(420px,100vw)!important;max-width:100%!important;
                                height:100vh!important;height:100dvh!important;min-height:0!important;max-height:none!important;
                                margin:0!important;padding:0!important;border:0!important;border-radius:0!important;
                                background:var(--card-bg,var(--body-bg))!important;box-shadow:var(--shadow-lg)!important;
                                overflow:hidden!important;transform:translate3d(100%,0,0)!important;
                                visibility:hidden;opacity:0;pointer-events:none;
                                transition:transform .26s cubic-bezier(.2,.75,.25,1),opacity .24s ease,visibility .26s ease;
                            }
                            .notification-dropdown:is(.is-open,.show) > .notification-menu,.notification-dropdown > .notification-menu.show{
                                transform:translate3d(0,0,0)!important;visibility:visible;opacity:1;pointer-events:auto;
                            }
                            #notificationMenu .notification-menu__head{position:relative;display:flex!important;align-items:center!important;justify-content:flex-start!important;gap:12px!important;flex:none;min-height:64px;padding:calc(12px + env(safe-area-inset-top,0px)) 16px 12px;border-bottom:1px solid var(--border-color)}
                            #notificationMenu .notification-menu__head strong{font:600 1.125rem/1.25 var(--font-secondary);color:var(--title-color);order:0}
                            #notificationMenu .notification-live-status{order:1;margin-left:auto!important;flex:none!important}
                            #notificationMenu .notification-menu__actions{order:2;display:flex;align-items:center;flex:none;margin-left:auto}
                            #notificationMenu .notification-menu__head:has(.notification-live-status) .notification-menu__actions{margin-left:0}
                            #notificationMenu .notification-drawer-close{display:inline-flex!important;align-items:center;justify-content:center;--btn-size:40px;width:40px;height:40px;padding:0;flex:0 0 40px;background:var(--gray-100);color:var(--text-color)}
                            #notificationMenu .notification-menu__list{flex:1 1 auto;min-height:0;max-height:none!important;overflow-y:auto;overflow-x:hidden;padding:0 16px calc(16px + env(safe-area-inset-bottom,0px));overscroll-behavior:contain;-webkit-overflow-scrolling:touch;scrollbar-width:thin}
                            #notificationMenu .notification-item{padding:14px 0;margin:0;border:0;border-bottom:1px solid var(--border-color);border-radius:0;gap:8px;background:transparent}
                            #notificationMenu .notification-item:last-child{border-bottom:0}
                            #notificationMenu .notification-item:hover,#notificationMenu .notification-item:focus-within{background:var(--gray-100)}
                            #notificationMenu .notification-item__main{gap:12px;align-items:flex-start}
                            #notificationMenu .notification-item__icon{width:40px;height:40px;flex:0 0 40px;border-radius:12px;background:var(--gray-100);color:var(--color-primary)}
                            #notificationMenu .notification-item__content strong{font-size:.95rem;font-weight:600;color:var(--title-color)}
                            #notificationMenu .notification-item__content>span{font-size:.85rem;color:var(--color-gray)}
                            #notificationMenu .notification-item__content time{font-size:.75rem;color:var(--color-gray)}
                            #notificationMenu .notification-item__actions summary{width:36px;height:36px}
                            #notificationMenu .notification-item__unread{margin-top:7px;flex:0 0 7px;background:var(--color-primary)}
                            .notification-item__actions[open]{z-index:5}
                            .notification-item__actions-menu{max-width:calc(100vw - 66px)}
                            .notification-item:nth-last-child(-n+2) .notification-item__actions-menu{top:auto;bottom:calc(100% + 5px)}
                        }

                        /* Repost messenger palette and compact dropdown, scoped to the notification panel. */
                        #notificationMenu{
                            --pm-primary:var(--color-primary);
                            --pm-secondary:var(--color-secondary);
                            --pm-secondary-light:var(--gray-100);
                            --pm-muted:var(--color-gray);
                            --pm-transition:.15s ease;
                            gap:0!important;min-height:0;width:min(376px,calc(100vw - 24px));
                            max-height:min(510px,calc(100dvh - 88px));padding:0!important;
                            border-radius:var(--dropdown-menu-radius,20px);
                            background:var(--card-bg);box-shadow:var(--dropdown-menu-shadow);
                        }
                        #notificationMenu .notification-menu__head{
                            display:flex!important;align-items:center!important;justify-content:flex-start!important;
                            flex:0 0 auto;gap:10px!important;padding:12px 14px;
                            min-height:56px;border-bottom:1px solid var(--border-color);
                        }
                        #notificationMenu .notification-menu__head strong{
                            order:0;flex:0 0 auto;color:var(--title-color);
                            font:700 1rem/1.3 var(--font-secondary);
                        }
                        #notificationMenu .notification-live-status{
                            order:1;flex:none!important;margin-left:auto!important;
                        }
                        #notificationMenu .notification-menu__actions{
                            order:2;display:flex;align-items:center;flex:none;margin-left:auto;
                        }
                        #notificationMenu .notification-menu__head:has(.notification-live-status) .notification-menu__actions{margin-left:0}
                        #notificationMenu .notification-drawer-close{
                            --btn-size:32px;display:inline-flex!important;align-items:center;justify-content:center;
                            flex:0 0 32px;width:32px;height:32px;min-width:32px;padding:0;
                            border-radius:50%;background:var(--pm-secondary);color:var(--title-color);
                        }
                        #notificationMenu .notification-menu__list{
                            flex:1 1 auto;min-height:0;max-height:360px;
                            padding:6px;overflow-x:hidden;overflow-y:auto;
                            overscroll-behavior:contain;scrollbar-width:thin;
                            scrollbar-color:var(--gray-300, #b8c2ce) transparent;
                        }
                        #notificationMenu .notification-item,
                        #notificationMenu .notification-item:has(details[open]){
                            position:relative;display:flex;align-items:center;flex-wrap:nowrap;
                            min-width:0;gap:0;padding:10px 8px 10px 10px;margin:0 0 5px;
                            border:0;border-radius:12px;background:transparent;
                            transition:background-color var(--pm-transition);
                        }
                        #notificationMenu .notification-item:last-child{margin-bottom:0}
                        #notificationMenu .notification-item.is-unread,
                        #notificationMenu .notification-item:hover,
                        #notificationMenu .notification-item:focus-within{background:var(--pm-secondary-light)}
                        #notificationMenu .notification-item:has(details[open]){z-index:31}
                        #notificationMenu .notification-item__main{
                            position:relative;display:flex;align-items:center;flex:1 1 auto;
                            min-width:0;min-height:40px;gap:10px;padding:0 32px 0 0;
                        }
                        #notificationMenu .notification-item__main:has(.notification-item__unread){padding-right:44px}
                        #notificationMenu .notification-item__icon{
                            display:grid;place-items:center;width:40px;height:40px;flex:0 0 40px;
                            border-radius:50%;background:var(--pm-secondary);color:var(--pm-primary);
                        }
                        #notificationMenu .notification-item__icon svg{width:20px;height:20px}
                        #notificationMenu .notification-item__content strong{
                            color:var(--title-color);font:700 .9rem/1.3 var(--font-secondary);
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
                        }
                        #notificationMenu .notification-item__content>span{
                            color:var(--pm-muted);font-size:.8rem;line-height:1.35;
                            display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
                        }
                        #notificationMenu .notification-item__content time{
                            color:var(--pm-muted);font-size:.7rem;line-height:1.35;margin:0;
                        }
                        #notificationMenu .notification-item__unread{
                            position:absolute;right:35px;top:7px;flex:none;width:7px;height:7px;margin:0;
                            background:var(--pm-primary);
                        }
                        #notificationMenu .notification-item__actions,
                        #notificationMenu .notification-item:has(details[open]) .notification-item__actions{
                            position:absolute;top:6px;right:5px;z-index:30;display:block;flex:none;margin:0;
                        }
                        #notificationMenu .notification-item__actions summary{
                            display:grid;place-items:center;width:32px;height:32px;padding:0;
                            border-radius:10px;color:var(--pm-muted);background:transparent;
                        }
                        #notificationMenu .notification-item__actions summary:hover,
                        #notificationMenu .notification-item__actions[open] summary{background:var(--pm-secondary);color:var(--title-color)}
                        #notificationMenu .notification-item__actions-menu{
                            position:absolute!important;z-index:32;top:calc(100% + 4px);bottom:auto;right:0;
                            width:210px;max-width:calc(100vw - 60px);min-width:0;margin:0;padding:5px;
                            border:1px solid var(--dropdown-menu-border-color,var(--border-color));
                            border-radius:var(--dropdown-menu-radius,14px);background:var(--dropdown-menu-bg,var(--card-bg));
                            box-shadow:var(--dropdown-menu-shadow);
                        }
                        #notificationMenu .notification-item__actions.opens-up .notification-item__actions-menu{top:auto;bottom:calc(100% + 4px)}
                        #notificationMenu .notification-item__actions-menu button{
                            min-height:35px;padding:8px 10px;border-radius:10px;color:var(--text-color);font-size:.82rem;
                        }
                        #notificationMenu .notification-item__actions-menu button:hover{background:var(--pm-secondary-light)}
                        #notificationMenu .notification-item__actions-menu button.is-danger{color:var(--color-red,#ef4444)}
                        #notificationMenu .notification-menu__footer{
                            display:flex;flex-direction:column;flex:0 0 auto;gap:8px;
                            padding:10px 12px 12px;border-top:1px solid var(--border-color);background:var(--card-bg);
                        }
                        #notificationMenu .notification-menu__all{
                            display:flex;align-items:center;justify-content:center;gap:8px;width:100%;
                            min-height:36px;border-radius:10px;background:var(--pm-secondary);
                            color:var(--title-color);font-size:.85rem;font-weight:600;text-decoration:none!important;
                        }
                        #notificationMenu .notification-menu__all:hover{background:var(--pm-secondary-light)}
                        #notificationMenu .notification-menu__bulk{display:flex;align-items:center;justify-content:space-between;gap:6px}
                        #notificationMenu .notification-menu__bulk button{
                            color:var(--pm-muted);font-size:.75rem;padding:6px 4px;border-radius:8px;
                        }
                        #notificationMenu .notification-menu__bulk button:hover{background:var(--pm-secondary-light);color:var(--title-color)}
                        #notificationMenu .notification-menu__bulk .is-danger{color:var(--color-red,#ef4444)}
                        @media(max-width:767px){
                            #notificationMenu .notification-menu__head{min-height:60px;padding:calc(12px + env(safe-area-inset-top,0px)) 16px 12px}
                            #notificationMenu .notification-menu__list{padding:8px 12px;max-height:none!important}
                            #notificationMenu .notification-menu__footer{padding:12px 16px calc(12px + env(safe-area-inset-bottom,0px))}
                        }
                        @media (max-width:767px) and (prefers-reduced-motion:reduce){
                            .notification-sheet-backdrop,.notification-dropdown > .notification-menu{transition:none!important}
                        }
                    </style>
                    @php
                        $desktopFavoriteCount = auth()->user()->bookmarks()->count();
                    @endphp
                    <div class="dropdown dropdown-md">
                        <button id="desktopUserMenuButton" type="button" class="btn btn-secondary-ghost btn-circle avatar" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-controls="desktopUserMenu" aria-label="Profil menüsünü aç" title="Profil">
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="" draggable="false" width="40" height="40">
                            <span data-notification-dot aria-hidden="true" @if ($unreadNotificationCount === 0) hidden @endif></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="desktopUserMenu" aria-labelledby="desktopUserMenuButton">
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a class="login__profile" href="{{ route('profiles.show', auth()->user()->username ?: auth()->id()) }}">
                                    <div class="user-box">
                                        <span class="avatar" aria-hidden="true"><img src="{{ auth()->user()->avatarUrl() }}" alt="" draggable="false" width="40" height="40"></span>
                                        <span class="user-box-content">
                                            <span class="name"><span class="name-text">{{ auth()->user()->displayName() }}</span></span>
                                            <span class="small color-gray">Profili görüntüle</span>
                                        </span>
                                    </div>
                                </a>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <button id="notificationMenuButton" type="button" aria-expanded="false" aria-haspopup="menu" aria-controls="notificationMenu" data-notification-user-id="{{ $notificationUser->id }}" data-notification-open-template="{{ route('notifications.open', ['notification' => '__NOTIFICATION__']) }}" data-unread-count="{{ $unreadNotificationCount }}" data-notification-feed="{{ \Illuminate\Support\Facades\Route::has('notifications.feed') ? route('notifications.feed') : '' }}" aria-label="Bildirimler{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' okunmamış' : '' }}">
                                    <svg class="color-gray" viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span>Bildirimler</span>
                                    <span class="dropdown-menu-count" data-notification-count @if ($unreadNotificationCount === 0) hidden @endif>{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                                </button>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('messenger.index') }}"><x-repost-icon name="message" size="22" class="color-gray" /><span>Mesajlar</span></a>
                            </li>
                            <li class="dropdown-menu-item dropdown-menu-item-lg">
                                <a href="{{ route('favorites.index') }}"><x-repost-icon name="bookmark" size="22" class="color-gray" /><span>Favoriler</span>@if ($desktopFavoriteCount > 0)<span class="dropdown-menu-count">{{ number_format($desktopFavoriteCount) }}</span>@endif</a>
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
                                <button type="submit" form="desktopLogoutForm"><x-repost-icon name="logout" size="20" /><span>Çıkış yap</span></button>
                            </li>
                        </ul>
                    <div class="dropdown dropdown-md notification-dropdown">
                        <button id="notificationDropdownToggle" type="button" data-dropdown-toggle aria-controls="notificationMenu" aria-hidden="true" tabindex="-1" hidden></button>
                        <div class="dropdown-menu dropdown-menu-end notification-menu" id="notificationMenu" aria-labelledby="notificationMenuButton">
                            <div class="notification-menu__head">
                                <strong id="notificationDrawerTitle">Bildirimler</strong>
                                <div class="notification-menu__actions">
                                    <button type="button" class="btn btn-secondary btn-circle notification-drawer-close" data-notification-drawer-close aria-label="Bildirimleri kapat">
                                        <x-repost-icon name="close" size="18" />
                                    </button>
                                </div>
                            </div>
                            <div class="notification-menu__list">
                                @include('layouts._notification-items', ['headerNotifications' => $headerNotifications])
                            </div>
                            <p data-notification-error class="notification-menu__error" role="alert" hidden></p>
                            <div class="notification-menu__footer">
                                 @if (\Illuminate\Support\Facades\Route::has('notifications.index'))
                                     <a href="{{ route('notifications.index') }}" class="notification-menu__all">Tüm bildirimler <x-repost-icon name="chevron-down" size="12" /></a>
                                 @endif
                                <div class="notification-menu__bulk">
                                    <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button type="submit">Tümünü okundu işaretle</button></form>
                                    <form method="POST" action="{{ route('notifications.destroy-all') }}" onsubmit="return confirm('Tüm bildirimlerini silmek istiyor musun?')">@csrf @method('DELETE')<button type="submit" class="is-danger">Tümünü sil</button></form>
                                </div>
                            </div>
                        </div>
                    </div>
                        <form id="desktopLogoutForm" action="{{ route('logout') }}" method="POST" hidden>@csrf</form>
                    </div>
                    <script>
                        (() => {
                            const row = document.getElementById('notificationMenuButton');
                            const toggle = document.getElementById('notificationDropdownToggle');
                            const profile = document.getElementById('desktopUserMenuButton');
                            const menu = document.getElementById('notificationMenu');
                            const root = toggle?.closest('.notification-dropdown');
                            const close = menu?.querySelector('[data-notification-drawer-close]');
                            if (!row || !toggle || !profile || !root || !close) return;
                            // Delegation also covers rows replaced by the live feed.
                            menu.addEventListener('toggle', event => {
                                const details = event.target;
                                if (!(details instanceof HTMLDetailsElement) || !details.matches('.notification-item__actions') || !details.open) return;
                                menu.querySelectorAll('.notification-item__actions[open]').forEach(other => {
                                    if (other !== details) other.open = false;
                                });
                                const list = menu.querySelector('.notification-menu__list');
                                const actions = details.querySelector('.notification-item__actions-menu');
                                if (!list || !actions) return;
                                details.classList.remove('opens-up');
                                const bounds = list.getBoundingClientRect();
                                const anchor = details.getBoundingClientRect();
                                const height = actions.offsetHeight + 6;
                                if (bounds.bottom - anchor.bottom < height && anchor.top - bounds.top >= height) {
                                    details.classList.add('opens-up');
                                } else if (bounds.bottom - anchor.bottom < height) {
                                    list.scrollTop += height - (bounds.bottom - anchor.bottom);
                                }
                            }, true);
                            menu.addEventListener('click', event => {
                                if (event.target.closest('.notification-item__actions')) return;
                                menu.querySelectorAll('.notification-item__actions[open]').forEach(details => { details.open = false; });
                            });
                            row.addEventListener('click', () => {
                                if (profile.getAttribute('aria-expanded') === 'true') profile.click();
                                if (!root.classList.contains('is-open')) toggle.click();
                                if (root.classList.contains('is-open')) close.focus({preventScroll:true});
                            });
                            root.addEventListener('dropdown:open', () => {
                                row.setAttribute('aria-expanded', 'true');
                                menu.setAttribute('aria-labelledby', 'notificationDrawerTitle');
                            });
                            root.addEventListener('dropdown:close', () => {
                                row.setAttribute('aria-expanded', 'false');
                            });
                            const closeDesktop = () => {
                                if (window.matchMedia('(max-width: 767px)').matches || !root.classList.contains('is-open')) return;
                                toggle.click();
                                profile.focus({preventScroll:true});
                            };
                            close.addEventListener('click', closeDesktop);
                            menu.addEventListener('keydown', event => {
                                if (event.key === 'Escape') closeDesktop();
                            });
                        })();
                    </script>
                @else
                    <button type="button" class="btn btn-primary btn-rounded btn-md" data-modal-btn="login" aria-haspopup="dialog" aria-controls="loginModal"><x-repost-icon name="user" size="18" /><span>Giriş yap</span></button>
                @endauth
            </div>
        </div>
        @if ($newYearActive && $newYearSettings['lights'])
            <div class="ografi-ny-lights" aria-hidden="true"></div>
        @endif
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

    @php
        $settingsPage = request()->routeIs('settings.edit', 'settings.legacy');
    @endphp
    @php
        $profilePage = request()->routeIs('profiles.show', 'profiles.legacy');
    @endphp
    @php
        $channelPage = request()->routeIs('channels.show', 'legacy.channel');
    @endphp
    @php
        $postCreatePage = request()->routeIs('posts.create', 'posts.edit');
    @endphp
    @php
        $feedPage = request()->routeIs('posts.index');
    @endphp
    @php
        $channelsIndexPage = request()->routeIs('channels.index', 'legacy.channels');
    @endphp
    @php
        $statsPage = request()->routeIs('stats.index');
    @endphp
    @php
        $subscriptionsPage = request()->routeIs('subscriptions.index');
    @endphp
    @php
        $verificationPage = request()->routeIs('verification.notice');
    @endphp
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
                @if ($subscriptionsPage)
                    <article class="feed subscriptions">
                @elseif ($statsPage || $channelsIndexPage)
                    <article class="feed">
                @else
                    <section class="feed">
                @endif
                    @if (request()->routeIs('posts.index', 'posts.latest', 'legacy.last-posts') && $newYearActive && $newYearSettings['countdown'])
                        @include('layouts._new-year-countdown')
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
                @if ($statsPage || $channelsIndexPage || $subscriptionsPage)
                    </article>
                @else
                    </section>
                @endif

                @unless ($profilePage || $channelPage)
                <aside class="main__widgets {{ $feedPage ? 'homepage-index-widgets' : '' }}" aria-label="Sağ sütun">
                    <div class="widgets">
                        @include('layouts._tags-widget')
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
                            @if ($sidebarAdvertisement)
                                <section class="widget card card-body sidebar-ad-widget" data-ad-impression="{{ route('advertisements.impression', $sidebarAdvertisement) }}" data-ad-refresh-at="{{ $sidebarAdvertisement->ends_at->timestamp * 1000 }}">
                                    <header class="sidebar-ad-widget__header">
                                        <h2 class="widget__title h6">Sponsorlu içerik</h2>
                                        <span class="sidebar-ad-widget__label">Reklam</span>
                                    </header>
                                    @if ($sidebarAdvertisement->imageUrl())
                                        <a class="sidebar-ad-widget__image" href="{{ route('advertisements.show', ['advertisement' => $sidebarAdvertisement, 'source' => 'sidebar']) }}" aria-label="{{ $sidebarAdvertisement->title }}">
                                            <img src="{{ $sidebarAdvertisement->imageUrl() }}" alt="{{ $sidebarAdvertisement->title }}" loading="lazy">
                                        </a>
                                    @endif
                                    <div class="sidebar-ad-widget__body">
                                        <h3 class="sidebar-ad-widget__title">{{ $sidebarAdvertisement->title }}</h3>
                                        <p class="sidebar-ad-widget__description">{{ \Illuminate\Support\Str::limit($sidebarAdvertisement->description, 150) }}</p>
                                        <a class="btn btn-primary sidebar-ad-widget__button" href="{{ route('advertisements.click', $sidebarAdvertisement) }}" rel="sponsored nofollow">{{ $sidebarAdvertisement->button_text ?: 'Daha fazla bilgi al' }}</a>
                                    </div>
                                </section>
                            @else
                                <section class="widget card card-body sidebar-ad-widget sidebar-ad-widget--empty" @if ($sidebarAdvertisementNextStartAt) data-ad-refresh-at="{{ \Illuminate\Support\Carbon::parse($sidebarAdvertisementNextStartAt)->timestamp * 1000 }}" @endif>
                                    <header class="sidebar-ad-widget__header">
                                        <h2 class="widget__title h6">Reklam alanı</h2>
                                        <span class="sidebar-ad-widget__label">Sponsorlu</span>
                                    </header>
                                    <div class="sidebar-ad-widget__empty-content">
                                        <strong>Markanı OgraFi topluluğuna tanıt.</strong>
                                        <p>Reklamını yayınla, daha fazla kişiye ulaş.</p>
                                        <a class="btn btn-primary sidebar-ad-widget__button" href="{{ route('advertisements.create') }}">Reklam ver</a>
                                    </div>
                                </section>
                            @endif
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
                        @unless ($profilePage)
                            @include('layouts._footer')
                        @endunless
                    </div>
                </aside>
                @endunless
            @endif
        </div>
        <div class="ografi-store-bottom{{ $postCreatePage ? ' ografi-store-bottom--standalone' : '' }}">
            @include('layouts._store-buttons')
        </div>
    </main>



    @unless (request()->routeIs('messenger.show'))
    <section class="mobmenu" data-scroll-menu>
        <nav class="mobmenu__content" aria-label="Ana gezinme">
            <a class="mobmenu__link {{ request()->routeIs('posts.index') ? 'is-active' : '' }}" href="{{ route('posts.index') }}" @if (request()->routeIs('posts.index')) aria-current="page" @endif>
                <x-repost-icon name="home" size="20" /><span>Ana sayfa</span>
            </a>
            <button class="mobmenu__link" type="button" data-side-panel-open="mobileAside" aria-controls="mobileAside" aria-expanded="false" aria-haspopup="dialog" title="Gezinme">
                <x-repost-icon name="compass" size="20" /><span>Gezinme</span>
            </button>
            <a class="mobmenu__link {{ request()->routeIs('channels.*') ? 'is-active' : '' }}" href="{{ route('channels.index') }}">
                <x-repost-icon name="channels" size="20" /><span>Kanallar</span>
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
    <script src="{{ asset('assets/js/init.js') }}?v=20261112-postim-swipe"></script>

    @auth
    <script>
    (() => {
        if (window.__ografiPostComposerBound) return;
        window.__ografiPostComposerBound = true;

        let composerReturnFocus = null;
        let composerPreviousOverflow = '';

        const closeComposer = () => {
            const modal = document.querySelector('.post-create-modal-page[data-live-composer="1"]');
            if (!modal) return;

            modal.querySelector('form')?.dispatchEvent(new Event('ografi:composer-close'));
            modal.remove();
            document.body.classList.remove('post-composer-open');
            document.body.style.overflow = composerPreviousOverflow;

            if (composerReturnFocus && document.contains(composerReturnFocus)) {
                composerReturnFocus.focus({ preventScroll: true });
            }
            composerReturnFocus = null;
        };

        document.addEventListener('click', async (event) => {
            const trigger = event.target.closest('[data-post-composer-trigger]');
            if (trigger) {
                event.preventDefault();
                if (document.querySelector('.post-create-modal-page[data-live-composer="1"]')) return;

                const originalText = trigger.innerHTML;
                trigger.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(trigger.href, {
                        cache: 'no-store',
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                    });
                    if (!response.ok) throw new Error('Composer request failed: ' + response.status);

                    const html = await response.text();
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    const source = parsed.querySelector('.post-create-modal-page');
                    if (!source) throw new Error('Composer markup not found');

                    composerReturnFocus = document.activeElement instanceof HTMLElement
                        ? document.activeElement
                        : trigger;
                    composerPreviousOverflow = document.body.style.overflow;

                    const modal = source.cloneNode(true);
                    modal.dataset.liveComposer = '1';
                    modal.setAttribute('role', 'dialog');
                    modal.setAttribute('aria-modal', 'true');
                    modal.setAttribute('aria-label', 'Yeni yayın');

                    document.body.appendChild(modal);
                    document.body.classList.add('post-composer-open');
                    document.body.style.overflow = 'hidden';

                    const composerScripts = Array.from(parsed.querySelectorAll('script')).filter(script =>
                        !script.src && (
                            script.textContent.includes('const start = () =>') ||
                            script.textContent.includes('const initComposerDetails = () =>')
                        )
                    );
                    composerScripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        newScript.textContent = oldScript.textContent;
                        document.body.appendChild(newScript);
                    });

                    modal.querySelector('#post-title')?.focus();
                } catch (error) {
                    console.error('Post composer açılamadı:', error);
                    window.location.href = trigger.href;
                } finally {
                    trigger.removeAttribute('aria-busy');
                    trigger.innerHTML = originalText;
                }
                return;
            }

            const liveModal = document.querySelector('.post-create-modal-page[data-live-composer="1"]');
            if (!liveModal) return;

            const expandButton = event.target.closest('[data-post-composer-expand]');
            if (expandButton) {
                const expanded = liveModal.classList.toggle('is-expanded');
                expandButton.setAttribute('aria-pressed', expanded ? 'true' : 'false');
                expandButton.setAttribute('aria-label', expanded ? 'Küçült' : 'Genişlet');
                return;
            }

            if (event.target === liveModal || event.target.closest('.addpost__modal-close')) {
                event.preventDefault();
                closeComposer();
            }
        });

        document.addEventListener('keydown', event => {
            const modal = document.querySelector('.post-create-modal-page[data-live-composer="1"]');
            if (!modal) return;

            if (event.key === 'Escape') {
                const openPanel = modal.querySelector('[data-composer-location-panel]:not([hidden]), [data-composer-video-panel]:not([hidden])');
                if (openPanel) {
                    event.preventDefault();
                    openPanel.hidden = true;
                    modal.querySelector('[data-composer-location-toggle]')?.setAttribute('aria-expanded', 'false');
                    modal.querySelector('[data-composer-video-toggle]')?.setAttribute('aria-expanded', 'false');
                    return;
                }

                const openSettings = modal.querySelector('.post-composer-advanced[open]');
                if (openSettings) {
                    event.preventDefault();
                    openSettings.open = false;
                    return;
                }

                event.preventDefault();
                closeComposer();
                return;
            }

            if (event.key !== 'Tab') return;

            const focusable = Array.from(modal.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )).filter(element => element.getClientRects().length > 0);

            if (!focusable.length) {
                event.preventDefault();
                modal.focus();
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && (document.activeElement === first || !modal.contains(document.activeElement))) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && (document.activeElement === last || !modal.contains(document.activeElement))) {
                event.preventDefault();
                first.focus();
            }
        });
    })();
    </script>
    @endauth

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
    @if ($sidebarAdvertisement)
    <script>
    (() => {
        const ads = document.querySelectorAll('[data-ad-impression]');
        if (!ads.length) return;

        ads.forEach(ad => {
            let sent = false;
            const sendImpression = () => {
                if (sent) return;
                sent = true;
                fetch(ad.dataset.adImpression, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).catch(() => { sent = false; });
            };

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(entries => {
                    if (entries.some(entry => entry.isIntersecting && entry.intersectionRatio >= 0.5)) {
                        sendImpression();
                        observer.disconnect();
                    }
                }, { threshold: 0.5 });
                observer.observe(ad);
            } else {
                sendImpression();
            }
        });
    })();
    </script>
@endif
<script>
(() => {
    const widgets = Array.from(document.querySelectorAll('.sidebar-ad-widget[data-ad-refresh-at]'));
    const refreshTimes = widgets
        .map(widget => Number(widget.dataset.adRefreshAt))
        .filter(timestamp => Number.isFinite(timestamp) && timestamp > 0);
    if (!refreshTimes.length) return;

    const refreshAt = Math.min(...refreshTimes);
    let refreshed = false;
    const refreshWhenDue = () => {
        if (refreshed || Date.now() < refreshAt) return;
        refreshed = true;
        window.location.reload();
    };

    const delay = Math.max(0, refreshAt - Date.now());
    window.setTimeout(refreshWhenDue, Math.min(delay + 250, 2147483647));
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') refreshWhenDue();
    });
})();
</script>
<script>
(() => {
    if (!('serviceWorker' in navigator)) return;
    const registerOgrafiWorker = () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/', updateViaCache: 'none' })
            .then(async () => {
                const registration = await navigator.serviceWorker.ready;
                const refresh = () => registration.active?.postMessage({ type: 'ografi-refresh-public-assets' });
                refresh();
                if (registration.sync) {
                    try { await registration.sync.register('ografi-public-assets'); } catch (_) {}
                }
                window.addEventListener('online', refresh);
                if (registration.periodicSync && navigator.permissions) {
                    try {
                        const permission = await navigator.permissions.query({ name: 'periodic-background-sync' });
                        if (permission.state === 'granted') {
                            const tags = await registration.periodicSync.getTags();
                            if (!tags.includes('ografi-public-assets')) {
                                await registration.periodicSync.register('ografi-public-assets', { minInterval: 24 * 60 * 60 * 1000 });
                            }
                        }
                    } catch (_) { /* Browser does not permit periodic sync. */ }
                }
            })
            .catch(error => console.error('OgraFi PWA service worker kaydedilemedi:', error));
    };
    registerOgrafiWorker();
})();
</script>
<script src="{{ asset('assets/js/post-edit-history.js') }}?v=2" defer></script>
@stack('scripts')
@if ($newYearActive)
    @include('layouts._new-year-effects')
@endif
</body>
</html>

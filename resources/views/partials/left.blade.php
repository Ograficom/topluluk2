@php
    $mobileSidebar = $mobileSidebar ?? false;
    $isFeed = request()->routeIs('home');
    $isVideo = request()->routeIs('video');
    $isFeatured = request()->routeIs('blog.popular');
    $isExplore = request()->routeIs('discover', 'discover.*');
    $isCategories = request()->routeIs('blog.categories', 'blog.category', 'blog.category.*');
    $isMessages = request()->routeIs('messages.*');
    $activeCategorySlug = request()->routeIs('blog.category', 'blog.category.*')
        ? (string) optional(request()->route('category'))->slug
        : '';
    $sidebarCategories = \App\Models\Category::query()
        ->withCount('posts')
        ->orderByDesc('posts_count')
        ->orderBy('name')
        ->take(10)
        ->get();
    $badgeColors = ['#ef4444', '#e11d48', '#ec4899', '#f43f5e', '#f97316', '#06b6d4', '#0ea5e9', '#10b981', '#84cc16', '#a855f7'];
    $referenceCategories = collect([
        ['name' => 'News', 'initials' => 'NE', 'color' => '#ef4444'],
        ['name' => 'Lifestyle', 'initials' => 'LI', 'color' => '#db1463'],
        ['name' => 'Fashion', 'initials' => 'FA', 'color' => '#ef4444'],
        ['name' => 'Politics', 'initials' => 'PO', 'color' => '#d91668'],
        ['name' => 'World', 'initials' => 'WO', 'color' => '#ef3d3d'],
        ['name' => 'Sports', 'initials' => 'SP', 'color' => '#16a9bf'],
        ['name' => 'Business', 'initials' => 'BU', 'color' => '#4ba447'],
        ['name' => 'Gadgets', 'initials' => 'GA', 'color' => '#df1260'],
        ['name' => 'Showbiz', 'initials' => 'SH', 'color' => '#ef4638'],
        ['name' => 'Crypto', 'initials' => 'CR', 'color' => '#f7b719'],
    ])->map(function (array $item) use ($sidebarCategories) {
        $matchedCategory = $sidebarCategories->first(
            fn ($category) => mb_strtolower((string) $category->name) === mb_strtolower($item['name'])
        );

        $item['url'] = $matchedCategory
            ? route('blog.category', ['category' => $matchedCategory->slug])
            : route('blog.categories');
        $item['slug'] = (string) optional($matchedCategory)->slug;

        return $item;
    });
    $staticFooterLinks = collect([
        ['label' => __('site.discover_page.title'), 'route' => 'discover'],
        ['label' => __('site.search.title'), 'route' => 'search'],
        ['label' => __('site.tags_page.title'), 'route' => 'blog.tags'],
        ['label' => __('site.users.kicker'), 'route' => 'users.index'],
        ['label' => 'Iletisim', 'route' => 'contact.create'],
    ])->filter(fn (array $link) => \Illuminate\Support\Facades\Route::has($link['route']))
        ->map(fn (array $link) => [
            'label' => $link['label'],
            'url' => route($link['route']),
        ]);
    $dynamicPageLinks = \App\Models\Page::query()
        ->published()
        ->orderBy('title')
        ->get(['title', 'slug'])
        ->reject(fn ($page) => (string) $page->slug === 'sss')
        ->map(fn ($page) => [
            'label' => $page->title,
            'url' => route('pages.show.short', ['slug' => $page->slug]),
        ]);
    $footerLinks = collect([
        [
            'label' => 'SSS',
            'url' => route('pages.sss'),
        ],
    ])->merge($staticFooterLinks)->merge($dynamicPageLinks)->unique('url')->values();
@endphp

<style>

    /* Desktop sol kolon genişliği daraltıldı */
    @media (min-width: 1024px) {
        body.alma-app:has(.layout-side--left) {
            --layout-left-width: 200px !important;
        }
    }

    .sidebar-wrapper {
        position: sticky !important;
        top: calc(var(--site-header-height, 70px) + 7px) !important;
        align-self: flex-start;
        z-index: 20;
        width: 100%;
        background: transparent !important;
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    @media (min-width: 1024px) {
        .sidebar-wrapper:not(.sidebar-wrapper--drawer) {
            position: fixed !important;
            top: 82px !important;
            left: max(
                0px,
                calc((100vw - var(--layout-shell-max, 1272px)) / 2)
            ) !important;
            width: var(--layout-left-width, 205px) !important;
            max-width: var(--layout-left-width, 205px) !important;
            height: calc(100dvh - 82px) !important;
            max-height: calc(100dvh - 82px) !important;
            overflow: hidden !important;
        }

        @supports not (height: 100dvh) {
            .sidebar-wrapper:not(.sidebar-wrapper--drawer) {
                height: calc(100vh - 82px) !important;
                max-height: calc(100vh - 82px) !important;
            }
        }
    }

    .sidebar-wrapper--drawer {
        --sidebar-drawer-bg: transparent;
        background: var(--sidebar-drawer-bg) !important;
    }

    .sidebar-wrapper--drawer .sidebar-scroll {
        background: var(--sidebar-drawer-bg) !important;
    }

    .sidebar-wrapper,
    .sidebar-scroll,
    .sidebar-section {
        background-color: transparent !important;
        box-shadow: none !important;
    }

    /*
        Sol menü kendi içinde kayar.
        Sayfanın sağındaki gereksiz tarayıcı scroll'unu engellemek için
        yükseklik viewport'a göre kontrollü verildi.
    */
    .sidebar-scroll {
        height: 100% !important;
        max-height: 100% !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
        padding-top: 10px;
        padding-bottom: 12px;
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    @supports not (height: 100dvh) {
        .sidebar-scroll {
            height: 100% !important;
            max-height: 100% !important;
        }
    }

    /*
        Esas sağdaki sayfa scroll'unu büyüten şeylerden biri main-grid alt boşluğu.
        Sol menünün olduğu sayfalarda bu boşluğu kaldırıyoruz.
    */
    body.alma-app:has(.layout-side--left) .main-grid {
        padding-bottom: 0 !important;
    }

    .sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(15, 23, 42, 0.15);
        border-radius: 4px;
    }
    .sidebar-scroll:hover::-webkit-scrollbar-thumb {
        background: rgba(15, 23, 42, 0.25);
    }

    .sidebar-section {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .nav-list {
        display: flex;
        flex-direction: column;
        gap: 7px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 13px;
        width: 100%;
        min-height: 50px;
        padding: 12px 15px;
        border-radius: 10px;
        color: #111827;
        text-decoration: none;
        background: transparent;
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 15px;
        line-height: 1.25;
        font-weight: 400;
        transition: background-color .15s ease, color .15s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .nav-item:hover,
    .nav-item:focus,
    .nav-item:focus-visible,
    .nav-item:active,
    .nav-item[data-active="true"] {
        background: #ffffff !important;
        color: #0f172a !important;
        outline: none;
        box-shadow: none !important;
    }

    .nav-item-icon-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 12px;
        color: currentColor;
        background: transparent !important;
        font-size: 22px;
    }

    .nav-item-icon-outline iconify-icon {
        font-size: 22px;
        line-height: 1;
    }

    .nav-item-icon-outline svg {
        width: 24px;
        height: 24px;
        display: block;
        color: currentColor;
    }

    .nav-item-label-row {
        display: flex;
        align-items: center;
        min-width: 0;
    }

    .nav-item-label {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 15px;
        line-height: 1.25;
        font-weight: 400;
        letter-spacing: -0.01em;
    }

    .nav-item-badge-new {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 9px;
        padding: 3px 8px;
        border-radius: 999px;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        color: #ffffff;
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 10.5px;
        line-height: 1;
        font-weight: 400;
        letter-spacing: -0.01em;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.22);
        transform-origin: center;
        animation: navNewBadgeFloat 2.2s ease-in-out infinite;
    }

    .nav-item-badge-new::after {
        content: "";
        position: absolute;
        inset: -3px;
        border-radius: inherit;
        border: 1px solid rgba(37, 99, 235, 0.35);
        opacity: 0;
        transform: scale(0.85);
        animation: navNewBadgePulse 2.2s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes navNewBadgeFloat {
        0%, 100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-1px) scale(1.04);
        }
    }

    @keyframes navNewBadgePulse {
        0% {
            opacity: 0;
            transform: scale(0.85);
        }

        45% {
            opacity: 1;
        }

        100% {
            opacity: 0;
            transform: scale(1.28);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .nav-item-badge-new,
        .nav-item-badge-new::after {
            animation: none !important;
        }
    }

    .sidebar-category-list {
        display: flex;
        flex-direction: column;
        gap: 7px;
        margin-top: 8px;
        padding-bottom: 12px;
        background: transparent !important;
    }

    .sidebar-category-link {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 48px;
        padding: 10px 15px;
        border-radius: 10px;
        color: #111827;
        text-decoration: none;
        background: transparent;
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 14px;
        line-height: 1.25;
        font-weight: 400;
        transition: background-color .15s ease, color .15s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .sidebar-category-link:hover,
    .sidebar-category-link:focus,
    .sidebar-category-link:focus-visible,
    .sidebar-category-link:active,
    .sidebar-category-link[data-active="true"] {
        background: #ffffff !important;
        color: #0f172a !important;
        outline: none;
        box-shadow: none !important;
    }

    .sidebar-category-avatar {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 38px !important;
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        min-height: 38px !important;
        max-width: 38px !important;
        max-height: 38px !important;
        border-radius: 50% !important;
        overflow: hidden !important;
        object-fit: cover;
        object-position: center;
        background: transparent !important;
        border: 1px solid rgba(148, 163, 184, 0.24);
        box-sizing: border-box !important;
        padding: 0 !important;
        line-height: 1 !important;
    }

    .sidebar-category-avatar--fallback {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 38px !important;
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        min-height: 38px !important;
        max-width: 38px !important;
        max-height: 38px !important;
        border-radius: 50% !important;
        overflow: hidden !important;
        padding: 0 !important;
        background: transparent !important;
        color: currentColor !important;
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 10.5px;
        font-weight: 400;
        line-height: 1 !important;
        text-transform: uppercase;
        box-sizing: border-box !important;
    }

    .sidebar-category-name {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 14px;
        line-height: 1.25;
        font-weight: 400;
    }

    .sidebar-footer {
        margin-top: 18px;
        padding-bottom: 12px;
        background: transparent !important;
    }

    .sidebar-footer,
    .sidebar-footer-link,
    .sidebar-footer-brand,
    .sidebar-footer-bottom {
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-weight: 400;
        font-size: 11px;
        line-height: 1.45;
    }

    @media (prefers-color-scheme: dark) {
        .sidebar-wrapper--drawer {
            --sidebar-drawer-bg: #020817;
            background: #020817 !important;
        }

        .sidebar-wrapper--drawer .sidebar-scroll {
            background: #020817 !important;
        }

        .nav-item,
        .sidebar-category-link,
        .nav-item-label,
        .sidebar-category-name,
        .sidebar-footer,
        .sidebar-footer-link,
        .sidebar-footer-brand,
        .sidebar-footer-bottom {
            color: #f8fafc !important;
        }

        .nav-item:hover,
        .nav-item:focus,
        .nav-item:focus-visible,
        .nav-item:active,
        .nav-item[data-active="true"],
        .sidebar-category-link:hover,
        .sidebar-category-link:focus,
        .sidebar-category-link:focus-visible,
        .sidebar-category-link:active,
        .sidebar-category-link[data-active="true"] {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }

        .nav-item[data-active="true"],
        .sidebar-category-link[data-active="true"] {
            background: #000000 !important;
            color: #ffffff !important;
        }

        .sidebar-category-avatar,
        .sidebar-category-avatar--fallback {
            background: transparent !important;
            border-color: rgba(255, 255, 255, 0.16);
        }

        .nav-item-badge-new {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            color: #ffffff;
            box-shadow: 0 8px 22px rgba(59, 130, 246, 0.24);
        }

        .nav-item-badge-new::after {
            border-color: rgba(96, 165, 250, 0.38);
        }

        .sidebar-footer,
        .sidebar-footer-link,
        .sidebar-footer-brand,
        .sidebar-footer-bottom {
            color: #e5e7eb;
        }
    }

    .dark .sidebar-wrapper--drawer {
        --sidebar-drawer-bg: #020817 !important;
        background: #020817 !important;
    }

    .dark .sidebar-wrapper--drawer .sidebar-scroll {
        background: #020817 !important;
    }

    .dark .nav-item,
    .dark .sidebar-category-link,
    .dark .nav-item-label,
    .dark .sidebar-category-name,
    .dark .sidebar-footer,
    .dark .sidebar-footer-link,
    .dark .sidebar-footer-brand,
    .dark .sidebar-footer-bottom {
        color: #f8fafc !important;
    }

    .dark .nav-item:hover,
    .dark .nav-item:focus,
    .dark .nav-item:focus-visible,
    .dark .nav-item:active,
    .dark .nav-item[data-active="true"],
    .dark .sidebar-category-link:hover,
    .dark .sidebar-category-link:focus,
    .dark .sidebar-category-link:focus-visible,
    .dark .sidebar-category-link:active,
    .dark .sidebar-category-link[data-active="true"] {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #ffffff !important;
    }

    .dark .nav-item[data-active="true"],
    .dark .sidebar-category-link[data-active="true"] {
        background: #000000 !important;
        color: #ffffff !important;
    }

    .dark .sidebar-category-avatar,
    .dark .sidebar-category-avatar--fallback {
        background: transparent !important;
        border-color: rgba(255, 255, 255, 0.16);
    }

    .dark .nav-item-badge-new {
        background: linear-gradient(135deg, #3b82f6, #06b6d4);
        color: #ffffff;
        box-shadow: 0 8px 22px rgba(59, 130, 246, 0.24);
    }

    .dark .nav-item-badge-new::after {
        border-color: rgba(96, 165, 250, 0.38);
    }

    .dark .sidebar-wrapper,
    .dark .sidebar-scroll,
    .dark .sidebar-section,
    .dark .sidebar-category-list {
        background-color: transparent !important;
    }

    .dark .sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
    }
    .dark .sidebar-scroll:hover::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
    }

    @media (max-width: 767px) {
        .sidebar-wrapper {
            position: static !important;
            top: auto !important;
            left: auto !important;
            width: 100% !important;
            max-width: none !important;
            height: auto !important;
            max-height: none !important;
            z-index: auto;
            background: transparent !important;
            overflow: visible !important;
        }

        .sidebar-scroll {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
            padding-top: 0;
            padding-bottom: 12px;
        }

        .nav-item {
            min-height: 50px;
            padding: 12px 14px;
            font-size: 15px;
        }

        .nav-item-label {
            font-size: 15px;
        }

        .nav-item-badge-new {
            margin-left: 8px;
            padding: 3px 7px;
            font-size: 10px;
        }

        .sidebar-category-link {
            min-height: 48px;
            padding: 10px 14px;
        }

        .sidebar-category-avatar,
        .sidebar-category-avatar--fallback {
            flex: 0 0 36px !important;
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            min-height: 36px !important;
            max-width: 36px !important;
            max-height: 36px !important;
            border-radius: 50% !important;
        }

        .sidebar-category-name {
            font-size: 14px;
        }
    }

    @media (min-width: 1024px) {
        .layout-side--left {
            position: relative !important;
            top: auto !important;
            align-self: start !important;
            min-height: 1px !important;
            overflow: visible !important;
            width: var(--layout-left-width, 200px) !important;
            min-width: var(--layout-left-width, 200px) !important;
        }
    }


    .nav-item[data-active="true"],
    .sidebar-category-link[data-active="true"] {
        background: #ffffff !important;
        color: #6b7280 !important;
        box-shadow: none !important;
    }

    .nav-item[data-active="true"] .nav-item-label,
    .nav-item[data-active="true"] .nav-item-icon-outline,
    .nav-item[data-active="true"] iconify-icon,
    .nav-item[data-active="true"] svg,
    .sidebar-category-link[data-active="true"] .sidebar-category-name,
    .sidebar-category-link[data-active="true"] .sidebar-category-avatar,
    .sidebar-category-link[data-active="true"] .sidebar-category-avatar--fallback {
        color: #6b7280 !important;
        border-color: rgba(107, 114, 128, 0.28) !important;
    }

    .nav-item:hover,
    .nav-item:focus,
    .nav-item:focus-visible,
    .nav-item:active,
    .sidebar-category-link:hover,
    .sidebar-category-link:focus,
    .sidebar-category-link:focus-visible,
    .sidebar-category-link:active {
        background: #ffffff !important;
        color: #6b7280 !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .nav-item:hover .nav-item-label,
    .nav-item:hover .nav-item-icon-outline,
    .nav-item:hover iconify-icon,
    .nav-item:hover svg,
    .sidebar-category-link:hover .sidebar-category-name,
    .sidebar-category-link:hover .sidebar-category-avatar,
    .sidebar-category-link:hover .sidebar-category-avatar--fallback {
        color: #6b7280 !important;
        border-color: rgba(107, 114, 128, 0.28) !important;
    }

    .dark .nav-item[data-active="true"],
    .dark .sidebar-category-link[data-active="true"] {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #9ca3af !important;
    }

    .dark .nav-item[data-active="true"] .nav-item-label,
    .dark .nav-item[data-active="true"] .nav-item-icon-outline,
    .dark .nav-item[data-active="true"] iconify-icon,
    .dark .nav-item[data-active="true"] svg,
    .dark .sidebar-category-link[data-active="true"] .sidebar-category-name,
    .dark .sidebar-category-link[data-active="true"] .sidebar-category-avatar,
    .dark .sidebar-category-link[data-active="true"] .sidebar-category-avatar--fallback {
        color: #9ca3af !important;
        border-color: rgba(156, 163, 175, 0.28) !important;
    }


    .sidebar-footer {
        font-size: 11px !important;
        line-height: 1.45 !important;
    }

    .sidebar-footer-links {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 9px;
    }

    .sidebar-footer-link,
    .sidebar-footer-brand,
    .sidebar-footer-bottom {
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
        font-size: 11px !important;
        line-height: 1.45 !important;
        font-weight: 400 !important;
        color: #6b7280 !important;
    }

    .sidebar-footer-bottom {
        margin-top: 8px;
    }


    /* Menü hizalama: ikon/avatar solda, yazılar aynı çizgide sabit */
    .nav-item,
    .sidebar-category-link {
        display: grid !important;
        grid-template-columns: 38px minmax(0, 1fr) !important;
        column-gap: 12px !important;
        align-items: center !important;
        padding-left: 14px !important;
        padding-right: 12px !important;
    }

    .nav-item-icon-outline,
    .sidebar-category-avatar,
    .sidebar-category-avatar--fallback {
        grid-column: 1 !important;
        justify-self: center !important;
    }

    .nav-item-label-row,
    .sidebar-category-name {
        grid-column: 2 !important;
        min-width: 0 !important;
        justify-self: stretch !important;
    }

    .nav-item-icon-outline {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        border-radius: 999px !important;
    }

    .sidebar-category-avatar,
    .sidebar-category-avatar--fallback {
        flex: none !important;
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        min-height: 38px !important;
        max-width: 38px !important;
        max-height: 38px !important;
    }

    /* En alt linkler artık çok solda durmaz; menü satırlarıyla aynı iç hizada başlar */
    .sidebar-footer {
        padding-left: 14px !important;
        padding-right: 12px !important;
        margin-top: 14px !important;
    }

    .sidebar-footer-links {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 6px 10px !important;
        align-items: center !important;
    }

    .sidebar-footer-link,
    .sidebar-footer-brand,
    .sidebar-footer-bottom {
        font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
        font-size: 10.5px !important;
        line-height: 1.45 !important;
        font-weight: 400 !important;
        color: #6b7280 !important;
        letter-spacing: 0 !important;
    }

    .sidebar-footer-bottom {
        margin-top: 7px !important;
        display: flex !important;
        align-items: center !important;
    }

    .sidebar-footer-brand {
        white-space: nowrap !important;
    }

    /* Seçili sayfa: beyaz zemin, yazı ve ikon gri */
    .nav-item[data-active="true"],
    .sidebar-category-link[data-active="true"] {
        background: #ffffff !important;
        color: #6b7280 !important;
        box-shadow: none !important;
    }

    .nav-item[data-active="true"] .nav-item-label,
    .nav-item[data-active="true"] .nav-item-icon-outline,
    .nav-item[data-active="true"] iconify-icon,
    .nav-item[data-active="true"] svg,
    .sidebar-category-link[data-active="true"] .sidebar-category-name,
    .sidebar-category-link[data-active="true"] .sidebar-category-avatar,
    .sidebar-category-link[data-active="true"] .sidebar-category-avatar--fallback {
        color: #6b7280 !important;
        border-color: rgba(107, 114, 128, 0.28) !important;
    }

    .nav-item:hover,
    .nav-item:focus,
    .nav-item:focus-visible,
    .nav-item:active,
    .sidebar-category-link:hover,
    .sidebar-category-link:focus,
    .sidebar-category-link:focus-visible,
    .sidebar-category-link:active {
        background: #ffffff !important;
        color: #6b7280 !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .nav-item:hover .nav-item-label,
    .nav-item:hover .nav-item-icon-outline,
    .nav-item:hover iconify-icon,
    .nav-item:hover svg,
    .sidebar-category-link:hover .sidebar-category-name,
    .sidebar-category-link:hover .sidebar-category-avatar,
    .sidebar-category-link:hover .sidebar-category-avatar--fallback {
        color: #6b7280 !important;
        border-color: rgba(107, 114, 128, 0.28) !important;
    }

    @media (max-width: 767px) {
        .nav-item,
        .sidebar-category-link {
            grid-template-columns: 36px minmax(0, 1fr) !important;
            column-gap: 11px !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .sidebar-footer {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
    }


    /* Sadece ölçü/boyut düzeni: butonlar görseldeki gibi daha kompakt */
    @media (min-width: 1024px) {
        .nav-list,
        .sidebar-category-list {
            align-items: flex-start !important;
        }

        .nav-item,
        .sidebar-category-link {
            width: 202px !important;
            max-width: 202px !important;
            min-height: 46px !important;
            height: 46px !important;
            padding: 0 12px !important;
            border-radius: 10px !important;
            grid-template-columns: 28px minmax(0, 1fr) !important;
            column-gap: 12px !important;
            box-sizing: border-box !important;
        }

        .nav-item-icon-outline {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            border-radius: 9px !important;
            font-size: 20px !important;
        }

        .nav-item-icon-outline iconify-icon {
            font-size: 20px !important;
        }

        .nav-item-icon-outline svg {
            width: 22px !important;
            height: 22px !important;
        }

        .sidebar-category-avatar,
        .sidebar-category-avatar--fallback {
            width: 26px !important;
            height: 26px !important;
            min-width: 26px !important;
            min-height: 26px !important;
            max-width: 26px !important;
            max-height: 26px !important;
            flex: 0 0 26px !important;
            font-size: 10px !important;
        }

        .sidebar-category-list {
            gap: 6px !important;
        }

        .nav-list {
            gap: 6px !important;
        }
    }


    /* Profil/kategori görsel boyutu biraz büyütüldü */
    @media (min-width: 1024px) {
        .sidebar-category-avatar,
        .sidebar-category-avatar--fallback {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            max-width: 32px !important;
            max-height: 32px !important;
            flex: 0 0 32px !important;
            font-size: 11px !important;
        }

        .nav-item,
        .sidebar-category-link {
            grid-template-columns: 32px minmax(0, 1fr) !important;
        }
    }

    /* İçerik biraz büyütüldü: genişlik dar kalır, yazı/icon/avatar daha rahat görünür */
    @media (min-width: 1024px) {
        .nav-item-label {
            font-size: 16px !important;
            line-height: 1.25 !important;
        }

        .sidebar-category-name {
            font-size: 15.5px !important;
            line-height: 1.25 !important;
        }

        .nav-item-badge-new {
            font-size: 11px !important;
            padding: 3px 8px !important;
        }
    }

    /* Alt menü linkleri biraz daha koyu; Ografi metni aynı bırakıldı */
    .sidebar-footer-link {
        color: #4b5563 !important;
    }

    .sidebar-footer-link:hover {
        color: #374151 !important;
    }

    .sidebar-footer-brand {
        color: #6b7280 !important;
    }


    /* Scroll çubuğu sadece left menü üzerinde gezinirken/kaydırırken görünsün */
    .sidebar-scroll {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        padding-right: 10px !important;
    }

    .sidebar-scroll::-webkit-scrollbar {
        width: 4px !important;
        height: 4px !important;
    }

    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent !important;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: transparent !important;
        border-radius: 999px !important;
    }

    .sidebar-wrapper:hover .sidebar-scroll,
    .sidebar-scroll.is-scrolling {
        scrollbar-width: thin !important;
        scrollbar-color: rgba(15, 23, 42, 0.22) transparent !important;
    }

    .sidebar-wrapper:hover .sidebar-scroll::-webkit-scrollbar-thumb,
    .sidebar-scroll.is-scrolling::-webkit-scrollbar-thumb {
        background: rgba(15, 23, 42, 0.22) !important;
    }

    .sidebar-wrapper:hover .sidebar-scroll::-webkit-scrollbar-thumb:hover,
    .sidebar-scroll.is-scrolling::-webkit-scrollbar-thumb:hover {
        background: rgba(15, 23, 42, 0.34) !important;
    }

    /* Sol menü hizalama: çift sola kaydırmayı kaldırıp menüyü sağa yaklaştırır */
    @media (min-width: 1024px) {
        .sidebar-wrapper:not(.sidebar-wrapper--drawer) {
            transform: none !important;
        }

        .sidebar-section,
        .sidebar-footer {
            transform: translateX(0) !important;
        }

        .nav-item,
        .sidebar-category-link {
            margin-right: 0 !important;
        }
    }

    .dark .sidebar-wrapper:hover .sidebar-scroll,
    .dark .sidebar-scroll.is-scrolling {
        scrollbar-color: rgba(255, 255, 255, 0.24) transparent !important;
    }

    /* Sol menü okunabilirliği: mevcut kompakt ölçüyü bozmadan içerik büyütüldü. */
    @media (min-width: 1024px) {
        .nav-item-label {
            font-size: 17px !important;
            line-height: 1.3 !important;
        }

        .nav-item-icon-outline,
        .nav-item-icon-outline iconify-icon {
            font-size: 22px !important;
        }

        .nav-item-icon-outline svg {
            width: 23px !important;
            height: 23px !important;
        }

        .sidebar-footer-link,
        .sidebar-footer-brand,
        .sidebar-footer-bottom {
            font-size: 12.5px !important;
            line-height: 1.55 !important;
        }
    }

    .dark .sidebar-wrapper:hover .sidebar-scroll::-webkit-scrollbar-thumb,
    .dark .sidebar-scroll.is-scrolling::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.24) !important;
    }

    /* Desktop reference clone: only the left column. */
    @media (min-width: 1024px) {
        body.alma-app .sidebar-wrapper:not(.sidebar-wrapper--drawer) {
            top: 86px !important;
            transform: translateX(-39px) !important;
            width: 210px !important;
            max-width: 210px !important;
            height: calc(100dvh - 86px) !important;
            max-height: calc(100dvh - 86px) !important;
        }

        body.alma-app .sidebar-scroll { padding: 0 8px 12px 0 !important; }
        body.alma-app .sidebar-section { gap: 0 !important; }

        body.alma-app .nav-list,
        body.alma-app .sidebar-category-list {
            width: 210px !important;
            gap: 0 !important;
        }

        body.alma-app .nav-item,
        body.alma-app .sidebar-category-link {
            width: 210px !important;
            max-width: 210px !important;
            height: 54px !important;
            min-height: 54px !important;
            padding: 0 8px !important;
            grid-template-columns: 34px minmax(0, 1fr) !important;
            column-gap: 9px !important;
            border-radius: 10px !important;
            color: #4b5563 !important;
        }

        body.alma-app .nav-item[data-active="true"] {
            height: 50px !important;
            min-height: 50px !important;
            margin: 2px 0 !important;
            background: #ffffff !important;
            color: #18181b !important;
        }

        body.alma-app .nav-item[data-active="true"] :is(.nav-item-label, .nav-item-icon-outline, iconify-icon, svg) {
            color: #18181b !important;
        }

        body.alma-app .nav-item[data-active="true"] .nav-item-icon-outline {
            color: #10b981 !important;
        }

        body.alma-app .nav-item:is(:hover, :focus-visible) {
            background: #ffffff !important;
            color: #18181b !important;
        }

        body.alma-app .nav-item:is(:hover, :focus-visible) .nav-item-icon-outline {
            color: #6b7280 !important;
        }

        body.alma-app .nav-item[data-active="true"]:is(:hover, :focus-visible) .nav-item-icon-outline {
            color: #10b981 !important;
        }

        body.alma-app .nav-item-icon-outline {
            width: 26px !important;
            height: 26px !important;
            min-width: 26px !important;
            border-radius: 7px !important;
            color: #6b7280 !important;
        }

        body.alma-app .nav-item-icon-outline :is(iconify-icon, svg) {
            width: 22px !important;
            height: 22px !important;
            font-size: 22px !important;
        }

        body.alma-app .nav-item-label {
            color: inherit !important;
            font-size: 18px !important;
            font-weight: 500 !important;
            line-height: 1.2 !important;
            letter-spacing: 0 !important;
        }

        body.alma-app .nav-item-badge-new { display: none !important; }

        body.alma-app .sidebar-category-list {
            margin-top: 0 !important;
            padding-bottom: 12px !important;
        }

        body.alma-app .sidebar-category-link {
            height: 54px !important;
            min-height: 54px !important;
        }

        body.alma-app .sidebar-category-avatar,
        body.alma-app .sidebar-category-avatar--fallback {
            width: 29px !important;
            height: 29px !important;
            min-width: 29px !important;
            min-height: 29px !important;
            max-width: 29px !important;
            max-height: 29px !important;
            border: 0 !important;
            color: #ffffff !important;
            font-size: 10px !important;
            font-weight: 500 !important;
        }

        body.alma-app .sidebar-category-name {
            color: #374151 !important;
            font-size: 18px !important;
            font-weight: 500 !important;
            line-height: 1.2 !important;
        }

        body.alma-app .sidebar-footer {
            margin-top: 8px !important;
            padding-left: 10px !important;
        }

        body.alma-app .nav-list > li:last-child {
            margin-top: 15px !important;
        }
    }

</style>

<aside
    class="sidebar-wrapper{{ $mobileSidebar ? ' sidebar-wrapper--drawer' : '' }}"
    @if($mobileSidebar)
        style="position: static; top: auto; left: auto; width: 100%; max-width: none; padding: 0; border-radius: 0; background: var(--sidebar-drawer-bg, transparent);"
    @endif
>
    <div
        class="sidebar-scroll"
        @if($mobileSidebar)
            style="max-height: none; overflow: visible; padding: 0; background: var(--sidebar-drawer-bg, transparent);"
        @endif
    >
        <div class="sidebar-section">
            <ul class="nav-list">
                <li>
                    <a class="nav-item" href="{{ route('home') }}" data-active="{{ $isFeed ? 'true' : 'false' }}">
                        <div class="nav-item-icon-outline">
                            <iconify-icon icon="lucide:panel-top"></iconify-icon>
                        </div>
                        <div class="nav-item-label-row">
                            <span class="nav-item-label">Feed</span>
                        </div>
                    </a>
                </li>

                <li>
                    <a class="nav-item" href="{{ route('video') }}" data-active="{{ $isVideo ? 'true' : 'false' }}">
                        <div class="nav-item-icon-outline" aria-hidden="true">
                            <iconify-icon icon="lucide:newspaper"></iconify-icon>
                        </div>
                        <div class="nav-item-label-row">
                            <span class="nav-item-label">News</span>
                        </div>
                    </a>
                </li>

                <li>
                    <a class="nav-item" href="{{ route('blog.popular') }}" data-active="{{ $isFeatured ? 'true' : 'false' }}">
                        <div class="nav-item-icon-outline">
                            <iconify-icon icon="lucide:badge"></iconify-icon>
                        </div>
                        <div class="nav-item-label-row">
                            <span class="nav-item-label">Featured</span>
                        </div>
                    </a>
                </li>

                <li>
                    <a class="nav-item" href="{{ route('discover') }}" data-active="{{ $isExplore ? 'true' : 'false' }}">
                        <div class="nav-item-icon-outline">
                            <iconify-icon icon="lucide:compass"></iconify-icon>
                        </div>
                        <div class="nav-item-label-row">
                            <span class="nav-item-label">Explore</span>
                        </div>
                    </a>
                </li>

                <li>
                    <a class="nav-item" href="{{ route('messages.index') }}" data-active="{{ $isMessages ? 'true' : 'false' }}">
                        <div class="nav-item-icon-outline">
                            <iconify-icon icon="lucide:message-square"></iconify-icon>
                        </div>
                        <div class="nav-item-label-row">
                            <span class="nav-item-label">Messages</span>
                        </div>
                    </a>
                </li>

                <li>
                    <a class="nav-item" href="{{ route('blog.categories') }}" data-active="{{ $isCategories ? 'true' : 'false' }}">
                        <div class="nav-item-icon-outline">
                            <iconify-icon icon="lucide:users-round"></iconify-icon>
                        </div>
                        <div class="nav-item-label-row">
                            <span class="nav-item-label">Communities</span>
                        </div>
                    </a>
                </li>
            </ul>

            @if($referenceCategories->isNotEmpty())
                <div class="sidebar-category-list">
                    @foreach($referenceCategories as $category)
                        <a
                            href="{{ $category['url'] }}"
                            class="sidebar-category-link"
                            data-active="{{ $category['slug'] !== '' && $activeCategorySlug === $category['slug'] ? 'true' : 'false' }}"
                        >
                            <span class="sidebar-category-avatar sidebar-category-avatar--fallback" style="background-color: {{ $category['color'] }} !important;">
                                {{ $category['initials'] }}
                            </span>
                            <span class="sidebar-category-name">{{ $category['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-footer-links">
                @foreach($footerLinks as $link)
                    <a class="sidebar-footer-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endforeach
            </div>

            <div class="sidebar-footer-bottom">
                <span class="sidebar-footer-brand">&copy; 2026 Ografi</span>
            </div>
        </div>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.sidebar-scroll').forEach(function (sidebarScroll) {
            let sidebarScrollTimer = null;

            sidebarScroll.addEventListener('scroll', function () {
                sidebarScroll.classList.add('is-scrolling');

                window.clearTimeout(sidebarScrollTimer);
                sidebarScrollTimer = window.setTimeout(function () {
                    sidebarScroll.classList.remove('is-scrolling');
                }, 900);
            }, { passive: true });
        });
    });
</script>

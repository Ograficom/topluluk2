@extends('layouts.app')

@section('hide_feed_header')
@endsection

@section('no_container_padding')
@endsection

@php
    use Illuminate\Support\Str;

    $categoryCollection = $categories ?? collect();

    if (is_object($categoryCollection) && method_exists($categoryCollection, 'getCollection')) {
        $categoryCollection = $categoryCollection->getCollection();
    }

    $currentUser = auth()->user();

    $activeTab = request('tab', 'discover');

    if (!in_array($activeTab, ['discover', 'mine'], true)) {
        $activeTab = 'discover';
    }

    if (!$currentUser && $activeTab === 'mine') {
        $activeTab = 'discover';
    }

    $sort = in_array((string) ($sort ?? request('sort', 'relevance')), ['relevance', 'time', 'members'], true)
        ? (string) ($sort ?? request('sort', 'relevance'))
        : 'relevance';

    $sortOptions = [
        'relevance' => 'Alaka düzeyi',
        'time' => 'Zaman',
        'members' => 'Üyeler',
    ];

    $visibleCategoryCollection = collect($categoryCollection);

    if ($activeTab === 'mine' && $currentUser) {
        $visibleCategoryCollection = $visibleCategoryCollection->filter(function ($category) use ($currentUser) {
            $ownerId = $category->user_id
                ?? $category->author_id
                ?? $category->created_by
                ?? $category->creator_id
                ?? optional($category->user ?? null)->id
                ?? optional($category->author ?? null)->id
                ?? optional($category->creator ?? null)->id
                ?? null;

            return (string) $ownerId === (string) $currentUser->id;
        })->values();
    }

    $canCreateCategory = auth()->check() && !auth()->user()->isBlockedFrom('categories');
    $createUrl = $canCreateCategory ? route('blog.category.create') : (Route::has('login') ? route('login') : '#');

    $discoverUrl = route('blog.categories', ['tab' => 'discover', 'sort' => $sort]);
    $mineUrl = route('blog.categories', ['tab' => 'mine', 'sort' => $sort]);

    $visibleCategoryCount = $visibleCategoryCollection->count();

    $siteName = config('app.name', 'Ografi');

    $pageTitle = $activeTab === 'mine'
        ? 'Benim Kategorilerim'
        : 'Kategoriler';

    $pageDescription = $activeTab === 'mine'
        ? 'Ografi üzerinde oluşturduğun kategorileri görüntüle, düzenle ve yönet.'
        : 'Ografi kategoriler sayfasında ilgi alanlarına göre kategorileri keşfet, popüler toplulukları incele ve yeni kategorilere katıl.';

    $baseCategoriesUrl = route('blog.categories');

    $canonicalUrl = $activeTab === 'discover' && $sort === 'relevance'
        ? $baseCategoriesUrl
        : route('blog.categories', [
            'tab' => $activeTab,
            'sort' => $sort,
        ]);

    $ogImage = 'https://ografi.com/storage/app/public/categories/cover/kategoriler.png';

    $categoryItems = collect($categoryCollection)->map(function ($category, $index) {
        $name = trim((string) ($category->name ?? ''));
        $slug = $category->slug ?? null;

        if ($name === '' || !$slug) {
            return null;
        }

        return [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => route('blog.category', $slug),
            'name' => $name,
        ];
    })->filter()->values();

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => url('/') . '#organization',
        'name' => $siteName,
        'url' => url('/'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $ogImage,
        ],
    ];

    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => url('/') . '#website',
        'name' => $siteName,
        'url' => url('/'),
        'publisher' => [
            '@id' => url('/') . '#organization',
        ],
        'inLanguage' => 'tr-TR',
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        '@id' => $canonicalUrl . '#breadcrumb',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Ana sayfa',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Kategoriler',
                'item' => $baseCategoriesUrl,
            ],
        ],
    ];

    $collectionPageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $canonicalUrl . '#webpage',
        'name' => $pageTitle . ' | ' . $siteName,
        'headline' => $pageTitle,
        'description' => $pageDescription,
        'url' => $canonicalUrl,
        'inLanguage' => 'tr-TR',
        'isPartOf' => [
            '@id' => url('/') . '#website',
        ],
        'publisher' => [
            '@id' => url('/') . '#organization',
        ],
        'breadcrumb' => [
            '@id' => $canonicalUrl . '#breadcrumb',
        ],
        'primaryImageOfPage' => [
            '@type' => 'ImageObject',
            'url' => $ogImage,
            'contentUrl' => $ogImage,
            'caption' => 'Ografi kategoriler sayfası öne çıkan görseli',
        ],
        'image' => [
            '@type' => 'ImageObject',
            'url' => $ogImage,
            'contentUrl' => $ogImage,
            'caption' => 'Ografi kategoriler görseli',
        ],
        'mainEntity' => [
            '@type' => 'ItemList',
            '@id' => $canonicalUrl . '#category-list',
            'name' => 'Ografi Kategoriler',
            'description' => 'Ografi üzerinde yer alan kategori listesi.',
            'numberOfItems' => $categoryItems->count(),
            'itemListElement' => $categoryItems->all(),
        ],
    ];

    $categoryItemListSchema = $categoryItems->isNotEmpty()
        ? [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            '@id' => $canonicalUrl . '#category-list',
            'name' => 'Ografi Kategoriler',
            'description' => 'Ografi üzerinde yer alan kategori listesi.',
            'numberOfItems' => $categoryItems->count(),
            'itemListElement' => $categoryItems->all(),
        ]
        : null;
@endphp

@section('title', $pageTitle . ' | ' . $siteName)
@section('meta_description', $pageDescription)

@push('head')
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="author" content="{{ $siteName }}">
    <meta name="theme-color" content="#2563eb">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }} | {{ $siteName }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="Ografi kategoriler sayfası öne çıkan görseli">
    <meta property="og:locale" content="tr_TR">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }} | {{ $siteName }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="Ografi kategoriler sayfası öne çıkan görseli">

    <script type="application/ld+json">
        {!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <script type="application/ld+json">
        {!! json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <script type="application/ld+json">
        {!! json_encode($collectionPageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <script type="application/ld+json">
        {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @if($categoryItemListSchema)
        <script type="application/ld+json">
            {!! json_encode($categoryItemListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
@endpush

@push('head')
<style>
    .categories-page {
        position: relative;
        isolation: isolate;
        z-index: 1;
        width: 100%;
        max-width: 41rem;
        margin: 16px auto 0;
        padding: 0 0 28px;
        overflow: visible !important;

        --categories-blue: #2563eb;
        --categories-blue-hover: #1d4ed8;
        --categories-text: #000000;
        --categories-muted: #71717a;
        --categories-desc: #5f6472;
        --categories-bg: #ffffff;
        --categories-hover: #f4f4f5;
        --categories-border: #e4e4e7;
        --categories-menu-bg: #ffffff;
        --categories-menu-border: #d4d4d8;
        --categories-menu-divider: #e5e7eb;
        --categories-menu-hover: #f3f4f6;
        --categories-filter-icon: #000000;
        --categories-filter-hover: #f4f4f5;
        --categories-filter-active: #e5e7eb;
    }

    .categories-panel,
    .categories-item {
        background: var(--categories-bg);
        border-radius: 8px;
        box-shadow: none;
    }

    .categories-panel {
        position: relative;
        overflow: visible !important;
        z-index: 1000;
    }

    .categories-panel:has(.categories-sort.is-open) {
        z-index: 9999;
    }

    .categories-panel__top {
        display: flex;
        min-height: 56px;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 10px 16px;
        border-bottom: 1px solid var(--categories-border);
    }

    .categories-title {
        margin: 0;
        color: var(--categories-text);
        font-size: 20px;
        font-weight: 500;
        line-height: 1.25;
    }

    .categories-create {
        display: inline-flex;
        height: 38px;
        min-width: 166px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 8px;
        background: var(--categories-blue);
        color: #ffffff !important;
        padding: 0 16px;
        font-size: 14.5px;
        font-weight: 500;
        line-height: 1;
        text-decoration: none;
    }

    .categories-create:hover,
    .categories-create:focus-visible {
        background: var(--categories-blue-hover);
        color: #ffffff !important;
    }

    .categories-panel__tabs {
        position: relative;
        display: flex;
        min-height: 50px;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 0 16px;
        overflow: visible !important;
        z-index: 2000;
    }

    .categories-tabs-left {
        display: inline-flex;
        height: 50px;
        align-items: center;
        gap: 22px;
        min-width: 0;
    }

    .categories-tab {
        display: inline-flex;
        height: 50px;
        align-items: center;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: var(--categories-muted);
        font-size: 15.5px;
        font-weight: 400;
        line-height: 1;
        text-decoration: none;
        white-space: nowrap;
    }

    .categories-tab:hover,
    .categories-tab:focus-visible {
        color: var(--categories-text);
    }

    .categories-tab.is-active {
        border-bottom-color: #000000;
        color: #000000;
        font-weight: 500;
    }

    .categories-sort {
        position: relative;
        display: inline-flex;
        align-items: center;
        z-index: 3000;
    }

    .categories-sort.is-open {
        z-index: 9999;
    }

    .categories-sort__trigger {
        position: relative;
        z-index: 10000;
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 8px;
        background: transparent !important;
        color: var(--categories-filter-icon) !important;
        cursor: pointer;
        padding: 0;
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }

    .categories-sort__trigger:hover,
    .categories-sort__trigger:focus-visible {
        background: var(--categories-filter-hover) !important;
        color: var(--categories-filter-icon) !important;
    }

    .categories-sort__trigger:active,
    .categories-sort.is-open .categories-sort__trigger {
        background: var(--categories-filter-active) !important;
        color: var(--categories-filter-icon) !important;
    }

    .categories-sort__trigger svg {
        width: 16px;
        height: 16px;
        display: block;
        color: currentColor !important;
        fill: currentColor !important;
        pointer-events: none;
    }

    .categories-sort__trigger svg path {
        fill: currentColor !important;
    }

    .categories-sort__menu {
        position: absolute;
        top: calc(100% + 5px);
        right: 0;
        width: 184px;
        border-radius: 7px;
        border: 1px solid var(--categories-menu-border);
        background: var(--categories-menu-bg);
        padding: 0;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
        z-index: 99999;
        overflow: hidden;
    }

    .categories-sort__menu[hidden] {
        display: none !important;
    }

    .categories-sort__title {
        display: block;
        width: 100%;
        padding: 9px 12px 8px;
        color: var(--categories-text);
        font-size: 14px;
        font-weight: 400;
        line-height: 1.2;
        pointer-events: none;
        user-select: none;
        border-bottom: 1px solid var(--categories-menu-divider);
        background: transparent;
    }

    .categories-sort__options {
        padding: 3px;
    }

    .categories-sort__option {
        display: flex;
        width: 100%;
        min-height: 34px;
        align-items: center;
        border: 0;
        border-radius: 5px;
        background: transparent;
        color: var(--categories-text);
        font-size: 13.5px;
        font-weight: 400;
        line-height: 1.2;
        padding: 0 9px;
        text-align: left;
        text-decoration: none;
    }

    .categories-sort__option[aria-current='true'],
    .categories-sort__option:hover,
    .categories-sort__option:focus-visible {
        background: var(--categories-menu-hover);
        color: var(--categories-text);
    }

    .categories-list {
        position: relative;
        z-index: 1;
        display: grid;
        gap: 14px;
        margin-top: 20px;
    }

    .categories-item {
        position: relative;
        z-index: 1;
        display: block;
        min-height: 96px;
        padding: 16px;
        text-decoration: none;
    }

    .categories-item:hover,
    .categories-item:focus-visible {
        background: var(--categories-bg);
    }

    .categories-item__head {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .categories-avatar {
        width: 36px;
        height: 36px;
        flex: 0 0 auto;
        overflow: hidden;
        border-radius: 999px;
        background: #f59e0b;
        color: #ffffff;
    }

    .categories-avatar img,
    .categories-avatar span {
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        object-fit: cover;
        font-size: 14px;
        font-weight: 500;
        line-height: 1;
    }

    .categories-item__meta {
        min-width: 0;
    }

    .categories-item__name {
        margin: 0;
        color: #000000;
        font-size: 17.5px;
        font-weight: 500;
        line-height: 1.2;
    }

    .categories-item__stats {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 5px;
        color: var(--categories-muted);
        font-size: 13px;
        font-weight: 400;
        line-height: 1.2;
    }

    .categories-item__stats strong {
        color: var(--categories-muted);
        font-weight: 400;
    }

    .categories-dot {
        width: 4px;
        height: 4px;
        border-radius: 999px;
        background: #a1a1aa;
    }

    .categories-item__description {
        margin: 14px 0 0;
        color: var(--categories-desc);
        font-size: 15px;
        font-weight: 400;
        line-height: 1.45;
    }

    .categories-empty {
        margin-top: 20px;
        border-radius: 8px;
        background: var(--categories-bg);
        padding: 18px;
        color: var(--categories-muted);
        font-size: 15px;
        font-weight: 400;
        text-align: center;
    }

    .categories-count {
        margin: 14px 0 0;
        padding: 0 4px;
        color: var(--categories-muted);
        font-size: 13.5px;
        font-weight: 400;
        line-height: 1.4;
        text-align: center;
    }

    .categories-pagination {
        display: none !important;
    }

    @media (prefers-color-scheme: dark) {
        .categories-page {
            --categories-text: #ffffff;
            --categories-muted: #d4d4d8;
            --categories-desc: #d4d4d8;
            --categories-bg: #18181b;
            --categories-hover: #27272a;
            --categories-border: #3f3f46;
            --categories-menu-bg: #18181b;
            --categories-menu-border: #3f3f46;
            --categories-menu-divider: #3f3f46;
            --categories-menu-hover: #27272a;
            --categories-filter-icon: #ffffff;
            --categories-filter-hover: #27272a;
            --categories-filter-active: #3f3f46;
        }

        .categories-title,
        .categories-tab.is-active,
        .categories-item__name,
        .categories-sort__title,
        .categories-sort__option {
            color: #ffffff;
        }

        .categories-tab {
            color: #d4d4d8;
        }

        .categories-tab:hover,
        .categories-tab:focus-visible {
            color: #ffffff;
        }

        .categories-tab.is-active {
            border-bottom-color: #ffffff;
        }

        .categories-sort__trigger {
            color: #ffffff !important;
        }

        .categories-sort__trigger:hover,
        .categories-sort__trigger:focus-visible {
            background: #27272a !important;
            color: #ffffff !important;
        }

        .categories-sort__trigger:active,
        .categories-sort.is-open .categories-sort__trigger {
            background: #3f3f46 !important;
            color: #ffffff !important;
        }

        .categories-sort__trigger svg,
        .categories-sort__trigger svg path {
            color: #ffffff !important;
            fill: #ffffff !important;
        }

        .categories-sort__menu {
            background: #18181b !important;
            border-color: #3f3f46;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.45);
        }

        .categories-sort__title {
            color: #ffffff;
            border-bottom-color: #3f3f46;
        }

        .categories-sort__option {
            color: #ffffff;
        }

        .categories-sort__option[aria-current='true'],
        .categories-sort__option:hover,
        .categories-sort__option:focus-visible {
            background: #27272a;
            color: #ffffff;
        }
    }

    .dark .categories-page {
        --categories-text: #ffffff;
        --categories-muted: #d4d4d8;
        --categories-desc: #d4d4d8;
        --categories-bg: #18181b;
        --categories-hover: #27272a;
        --categories-border: #3f3f46;
        --categories-menu-bg: #18181b;
        --categories-menu-border: #3f3f46;
        --categories-menu-divider: #3f3f46;
        --categories-menu-hover: #27272a;
        --categories-filter-icon: #ffffff;
        --categories-filter-hover: #27272a;
        --categories-filter-active: #3f3f46;
    }

    .dark .categories-title,
    .dark .categories-tab.is-active,
    .dark .categories-item__name,
    .dark .categories-sort__title,
    .dark .categories-sort__option {
        color: #ffffff;
    }

    .dark .categories-tab {
        color: #d4d4d8;
    }

    .dark .categories-tab:hover,
    .dark .categories-tab:focus-visible {
        color: #ffffff;
    }

    .dark .categories-tab.is-active {
        border-bottom-color: #ffffff;
    }

    .dark .categories-sort__trigger {
        color: #ffffff !important;
    }

    .dark .categories-sort__trigger:hover,
    .dark .categories-sort__trigger:focus-visible {
        background: #27272a !important;
        color: #ffffff !important;
    }

    .dark .categories-sort__trigger:active,
    .dark .categories-sort.is-open .categories-sort__trigger {
        background: #3f3f46 !important;
        color: #ffffff !important;
    }

    .dark .categories-sort__trigger svg,
    .dark .categories-sort__trigger svg path {
        color: #ffffff !important;
        fill: #ffffff !important;
    }

    .dark .categories-sort__menu {
        background: #18181b !important;
        border-color: #3f3f46;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.45);
    }

    .dark .categories-sort__title {
        color: #ffffff;
        border-bottom-color: #3f3f46;
    }

    .dark .categories-sort__option {
        color: #ffffff;
    }

    .dark .categories-sort__option[aria-current='true'],
    .dark .categories-sort__option:hover,
    .dark .categories-sort__option:focus-visible {
        background: #27272a;
        color: #ffffff;
    }

    @media (max-width: 640px) {
        .categories-page {
            position: relative;
            isolation: isolate;
            z-index: 1 !important;
            width: 100vw;
            max-width: none;
            margin: 12px calc(50% - 50vw) 0;
            padding: 0 0 24px;
            overflow: visible !important;
        }

        .categories-panel {
            width: 100%;
            border-radius: 0;
            overflow: visible !important;
            z-index: 5000 !important;
        }

        .categories-panel:has(.categories-sort.is-open) {
            z-index: 99999 !important;
        }

        .categories-panel__top,
        .categories-panel__tabs {
            padding-left: 14px;
            padding-right: 14px;
        }

        .categories-panel__tabs {
            z-index: 7000 !important;
            overflow: visible !important;
        }

        .categories-title {
            font-size: 19px;
        }

        .categories-create {
            min-width: auto;
            height: 36px;
            padding: 0 12px;
            font-size: 13.5px;
        }

        .categories-tabs-left {
            gap: 18px;
        }

        .categories-tab {
            font-size: 15px;
        }

        .categories-sort {
            z-index: 9000 !important;
        }

        .categories-sort.is-open {
            z-index: 99999 !important;
        }

        .categories-sort__trigger {
            z-index: 100000 !important;
            width: 36px;
            height: 36px;
        }

        .categories-sort__trigger svg {
            width: 17px;
            height: 17px;
        }

        .categories-sort__menu {
            top: calc(100% + 5px);
            width: min(190px, calc(100vw - 28px));
            max-width: calc(100vw - 28px);
            z-index: 100001 !important;
        }

        .categories-list {
            position: relative;
            z-index: 1 !important;
            width: 100%;
            gap: 12px;
            margin-top: 12px;
            padding: 0;
        }

        .categories-item {
            z-index: 1 !important;
            width: 100%;
            border-radius: 0;
            padding: 16px 14px;
        }

        .categories-item__name {
            font-size: 17px;
        }

        .categories-item__description {
            font-size: 14.5px;
        }

        .categories-count {
            padding: 0 14px;
        }

        .categories-empty {
            border-radius: 0;
        }
    }
</style>
@endpush

@section('content')
    <div class="categories-page">
        <section class="categories-panel">
            <div class="categories-panel__top">
                <h1 class="categories-title">Kategoriler</h1>

                <a href="{{ $createUrl }}" class="categories-create">
                    Yeni kategori oluştur
                </a>
            </div>

            <div class="categories-panel__tabs">
                <div class="categories-tabs-left">
                    <a
                        href="{{ $discoverUrl }}"
                        class="categories-tab {{ $activeTab === 'discover' ? 'is-active' : '' }}"
                        aria-current="{{ $activeTab === 'discover' ? 'page' : 'false' }}"
                    >
                        Keşfet
                    </a>

                    @auth
                        <a
                            href="{{ $mineUrl }}"
                            class="categories-tab {{ $activeTab === 'mine' ? 'is-active' : '' }}"
                            aria-current="{{ $activeTab === 'mine' ? 'page' : 'false' }}"
                        >
                            Benim
                        </a>
                    @endauth
                </div>

                <div class="categories-sort" data-categories-sort>
                    <button
                        type="button"
                        class="categories-sort__trigger"
                        data-categories-sort-trigger
                        aria-label="Sıralama menüsünü aç"
                        aria-expanded="false"
                        aria-controls="categories-sort-menu"
                    >
                        <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" aria-hidden="true">
                            <path fill="currentColor" fill-rule="evenodd" d="M2.402 1.494c3.114-.326 6.1-.326 9.215 0c.38.04.745.281.957.625c.205.333.242.715.054 1.064c-.952 1.773-2.301 3.403-4.186 4.626a.63.63 0 0 0-.284.524V11.7c0 .16-.095.304-.242.368l-1.494.648a.4.4 0 0 1-.561-.368V8.334a.63.63 0 0 0-.285-.525C3.692 6.586 2.342 4.956 1.39 3.183c-.375-.699.088-1.593 1.012-1.69M11.747.25a45 45 0 0 0-9.475 0C.602.425-.57 2.175.289 3.775c.987 1.838 2.383 3.561 4.322 4.893v3.68a1.65 1.65 0 0 0 2.31 1.514l1.493-.649a1.65 1.65 0 0 0 .994-1.514V8.668c1.939-1.332 3.334-3.055 4.322-4.894c.43-.8.31-1.659-.092-2.31C13.243.82 12.55.333 11.747.25" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <div id="categories-sort-menu" class="categories-sort__menu" data-categories-sort-menu hidden>
                        <div class="categories-sort__title">Göre sırala</div>

                        <div class="categories-sort__options">
                            @foreach($sortOptions as $sortKey => $sortLabel)
                                <a
                                    href="{{ route('blog.categories', ['tab' => $activeTab, 'sort' => $sortKey]) }}"
                                    class="categories-sort__option"
                                    aria-current="{{ $sort === $sortKey ? 'true' : 'false' }}"
                                >
                                    <span>{{ $sortLabel }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if($visibleCategoryCollection->isNotEmpty())
            <div class="categories-list">
                @foreach($visibleCategoryCollection as $category)
                    @php
                        $name = trim((string) ($category->name ?? ''));
                        $cover = $category->cover_image_url ?? $category->cover_image ?? null;
                        $profile = $category->profile_image_url ?? $category->profile_image ?? null;
                        $featured = $profile ?: $cover;
                        $initials = Str::upper(Str::substr($name, 0, 2));
                        $description = trim((string) ($category->description ?? ''));
                        $description = $description !== '' ? $description : 'Bu kategori için henüz açıklama eklenmemiş.';
                        $avatarHue = abs(crc32($name)) % 360;
                    @endphp

                    <a href="{{ route('blog.category', $category) }}" class="categories-item">
                        <div class="categories-item__head">
                            <div class="categories-avatar" style="background: hsl({{ $avatarHue }} 84% 48%);">
                                @if($featured)
                                    <img src="{{ $featured }}" alt="{{ $name }}" loading="lazy">
                                @else
                                    <span>{{ $initials }}</span>
                                @endif
                            </div>

                            <div class="categories-item__meta">
                                <h2 class="categories-item__name">{{ $name }}</h2>

                                <div class="categories-item__stats">
                                    <span><strong>{{ number_format((int) ($category->posts_count ?? 0)) }}</strong> hikayeler</span>
                                    <span class="categories-dot" aria-hidden="true"></span>
                                    <span><strong>{{ number_format((int) ($category->followers_count ?? 0)) }}</strong> üyeler</span>
                                </div>
                            </div>
                        </div>

                        <p class="categories-item__description">
                            {{ Str::limit(strip_tags($description), 140) }}
                        </p>
                    </a>
                @endforeach
            </div>

            <div class="categories-count">
                {{ number_format($visibleCategoryCount) }} kategori gösteriliyor
            </div>
        @else
            <div class="categories-empty">
                @if($activeTab === 'mine')
                    Henüz kendi oluşturduğun kategori yok.
                @else
                    Henüz kategori yok.
                @endif
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.querySelector('[data-categories-sort]');
            const trigger = document.querySelector('[data-categories-sort-trigger]');
            const menu = document.querySelector('[data-categories-sort-menu]');

            if (!root || !trigger || !menu) {
                return;
            }

            function openMenu() {
                root.classList.add('is-open');
                menu.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
            }

            function closeMenu() {
                root.classList.remove('is-open');
                menu.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            }

            function toggleMenu(event) {
                event.preventDefault();
                event.stopPropagation();

                if (menu.hidden) {
                    openMenu();
                } else {
                    closeMenu();
                }
            }

            trigger.addEventListener('click', toggleMenu);
            trigger.addEventListener('touchend', toggleMenu, { passive: false });

            root.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            document.addEventListener('click', closeMenu);

            document.addEventListener('touchend', function (event) {
                if (!root.contains(event.target)) {
                    closeMenu();
                }
            }, { passive: true });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenu();
                }
            });
        });
    </script>
@endsection
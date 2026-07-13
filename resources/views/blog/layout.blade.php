<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Blog')</title>
    <meta name="description" content="@yield('meta_description', 'OGrafi blog sayfalari.')">
    <link rel="canonical" href="{{ trim($__env->yieldContent('canonical_url')) ?: url()->current() }}">
    @include('partials.system-appearance')
    @include('partials.google-analytics')
    @include('partials.structured-data.site-graph')
    @include('partials.font-assets')
    @include('partials.tailwind-cdn')
    <style>
        [x-cloak] {
            display: none;
        }
        body {
            font-family: "Roboto", Arial, Helvetica, sans-serif;
            font-weight: 400;
        }
        body :where(h1, h2, h3, h4, h5, h6, strong, b, button, .font-light, .font-medium, .font-semibold, .font-bold, .font-extrabold, .font-black) {
            font-weight: 500 !important;
        }
        body :where(em, i) {
            font-style: italic;
            font-weight: 400 !important;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
        }

        @keyframes ddIn {
            from {
                opacity: 0;
                transform: translateY(-8px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes ddOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateY(-8px) scale(0.98);
            }
        }

        .dd-in {
            animation: ddIn 140ms ease-out forwards;
        }

        .dd-out {
            animation: ddOut 120ms ease-in forwards;
        }

    </style>
    <style>
    :root {
        --site-header-height: 64px;
        --page-max: 1272px;
        --profile-shell-width: 656px;
        --layout-left-width: 200px;
        --layout-right-width: 304px;
        --layout-column-gap: 56px;
        --layout-shell-inline: 0px;
        --layout-shell-max: var(--page-max);
        --page-bg: #f4f4f5;
        --header-bg: #d9f0ff;
        --card-bg: #ffffff;
        --card-radius: 10px;
    }

    .blog-layout-shell {
        width: 100%;
        max-width: var(--layout-shell-max);
        margin: 0 auto;
        padding: 16px 0 0;
        display: grid;
        grid-template-columns: var(--layout-left-width) minmax(0, var(--profile-shell-width)) var(--layout-right-width);
        gap: var(--layout-column-gap);
    }

    .blog-layout-main {
        min-width: 0;
        width: 100%;
        max-width: var(--profile-shell-width);
        margin: 0 auto;
    }

    .blog-layout-main > .mx-auto[class*="max-w-"],
    .blog-layout-main > div > .mx-auto[class*="max-w-"],
    .blog-layout-main > div > div > .mx-auto[class*="max-w-"] {
        max-width: var(--profile-shell-width) !important;
        width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .blog-layout-side {
        min-width: 0;
        width: 100%;
    }

    .blog-layout-side--left {
        max-width: var(--layout-left-width);
    }

    .blog-layout-side--right {
        max-width: var(--layout-right-width);
        justify-self: end;
        transform: none;
    }

    .layout-sticky {
        position: sticky;
        top: calc(var(--site-header-height) + 16px);
    }

    @media (max-width: 767px) {
        .community-shell {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .blog-layout-shell {
            padding-left: 0;
            padding-right: 0;
        }
    }

    @media (max-width: 1199px) and (min-width: 901px) {
        .blog-layout-shell {
            max-width: calc(var(--layout-left-width) + var(--profile-shell-width) + var(--layout-column-gap));
            grid-template-columns: var(--layout-left-width) minmax(0, var(--profile-shell-width));
            padding-left: 0;
            padding-right: 0;
        }

        .blog-layout-side--right {
            display: none !important;
        }
    }

    @media (max-width: 900px) {
        .blog-layout-shell {
            max-width: var(--profile-shell-width);
            grid-template-columns: minmax(0, 1fr);
            padding: 16px 14px 0;
        }

        .blog-layout-side {
            display: none !important;
        }
    }

    .community-card {
        border-radius: 0.75rem;
    }

    .community-card-body {
        padding: 1rem;
    }

    .community-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 0.5rem;
        padding: 0.5rem 0.625rem;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .community-link:hover {
        background: rgb(248 250 252 / 1);
        color: rgb(15 23 42 / 1);
    }

    .community-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        border: 1px solid rgb(226 232 240 / 1);
        background: rgb(248 250 252 / 1);
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .community-pill:hover {
        background: rgb(241 245 249 / 1);
        color: rgb(15 23 42 / 1);
    }

    .community-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        border: 1px solid rgb(226 232 240 / 1);
        background: #fff;
        padding: 0.5rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .community-btn:hover {
        border-color: rgb(203 213 225 / 1);
        background: rgb(248 250 252 / 1);
        color: rgb(15 23 42 / 1);
    }
</style>

    @include('partials.pwa-meta')
    @stack('head')
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-text-light dark:text-text-dark font-sans antialiased transition-colors duration-200 theme-minimal alma-app">
    @include('partials.preloader')
    @include('header')
    @include('partials.pwa-install-banner')

    <div class="blog-layout-shell">
        <aside class="blog-layout-side blog-layout-side--left hidden lg:block space-y-6 layout-sticky">
            @include('partials.ads.context-slot', [
                'slotKey' => 'ads_left_sidebar_top',
            ])

            @include('partials.left')
        </aside>

        <main class="blog-layout-main space-y-6 blog-content">
            @include('partials.community-feed')
            @if (session('status'))
                <div class="mb-4 rounded bg-emerald-500/10 text-emerald-100 px-4 py-3 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @include('partials.ads.context-slot', [
                'slotKey' => 'ads_main_before_content',
            ])

            @yield('content')

            @include('partials.ads.context-slot', [
                'slotKey' => 'ads_main_after_content',
            ])
        </main>

        <aside class="blog-layout-side blog-layout-side--right hidden lg:block space-y-6">
            @include('partials.right')
        </aside>
    </div>

    @unless (request()->routeIs('filament.*'))
        <x-mobile-bottom-nav />
    @endunless

    <x-cookie-banner />

    @include('partials.google-one-tap')
    @include('partials.external-link-bridge')
    @include('partials.image-lightbox')
    @stack('scripts')
    @include('partials.pwa-scripts')
</body>
</html>

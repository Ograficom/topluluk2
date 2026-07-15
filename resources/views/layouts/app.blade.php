<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $appName = trim((string) config('app.name', 'Ografi'));
        $rawPageTitle = trim((string) $__env->yieldContent('title', $appName));
        $documentTitle = $rawPageTitle !== '' ? $rawPageTitle : $appName;
        if ($appName !== '' && !str_contains(mb_strtolower($documentTitle), mb_strtolower($appName))) {
            $documentTitle .= ' | ' . $appName;
        }
        $metaDescription = trim((string) $__env->yieldContent('meta_description', $appName));
        $canonicalUrl = trim((string) $__env->yieldContent('canonical_url')) ?: url()->current();
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ e($metaDescription) }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=20260714a" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20260714a">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon-64.png') }}?v=20260714a">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('pwa/icon-192.png') }}?v=20260714a">
    <link rel="apple-touch-icon" href="{{ asset('pwa/icon-192.png') }}?v=20260714a">
    <title>{{ $documentTitle }}</title>
    @stack('seo')
    @include('partials.system-appearance')
    @include('partials.google-analytics')
    @include('partials.font-assets')
    {{-- Roboto font: post başlığı kalın, açıklama normal --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    @php
        $schemaThemeLayout = \App\Models\ThemeSetting::currentOrNull();
    @endphp
    @include('partials.structured-data.site-graph')
    @include('partials.tailwind-cdn')
    
    <style>
        [x-cloak] {
            display: none;
        }
        input[type="file"] {
            width: 100%;
            max-width: 100%;
            color: #334155;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 8px;
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        }
        input[type="file"]:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }
        input[type="file"]:focus,
        input[type="file"]:focus-visible {
            outline: none;
            border-color: #0b79ff;
            box-shadow: 0 0 0 3px rgba(11, 121, 255, 0.14);
        }
        input[type="file"]::file-selector-button {
            margin-right: 12px;
            border: 0;
            border-radius: 10px;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .2s ease;
        }
        input[type="file"]::file-selector-button:hover {
            background: #1e293b;
        }
        input[type="file"]::-webkit-file-upload-button {
            margin-right: 12px;
            border: 0;
            border-radius: 10px;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .2s ease;
        }
        input[type="file"]::-webkit-file-upload-button:hover {
            background: #1e293b;
        }
        html.dark input[type="file"] {
            color: #cbd5e1;
            background: #0f172a;
            border-color: #334155;
        }
        html.dark input[type="file"]:hover {
            border-color: #475569;
            background: #111827;
        }
        html.dark input[type="file"]::file-selector-button,
        html.dark input[type="file"]::-webkit-file-upload-button {
            background: #374151;
            color: #ffffff;
        }
        html.dark input[type="file"]::file-selector-button:hover,
        html.dark input[type="file"]::-webkit-file-upload-button:hover {
            background: #4b5563;
        }
        html {
            font-size: 14px;
            scroll-behavior: smooth;
        }
        body {
            font-family: "Roboto", Arial, Helvetica, sans-serif;
            font-weight: 400;
        }
        body :where(h1, h2, h3, h4, h5, h6, strong, b, button, .site-header-logo, .font-light, .font-medium, .font-semibold, .font-bold, .font-extrabold, .font-black) {
            font-weight: 500 !important;
        }
        body :where(em, i) {
            font-style: italic;
            font-weight: 400 !important;
        }
        body.alma-app :where(button, input[type="submit"], input[type="button"], .alma-button) {
            background: #ffffff !important;
            border-color: transparent !important;
            color: #18181b !important;
        }
        body.alma-app :where(button, input[type="submit"], input[type="button"], .alma-button):hover {
            background: #ffffff !important;
            border-color: transparent !important;
        }
        body.alma-app :where(button, input[type="submit"], input[type="button"], .alma-button):disabled {
            background: #ffffff !important;
            border-color: transparent !important;
            color: #a1a1aa !important;
            cursor: not-allowed;
        }
        body.alma-app .alma-post-card__inline-toggle,
        body.alma-app .alma-post-card__inline-toggle:hover,
        body.alma-app .alma-post-card__inline-toggle:focus,
        body.alma-app .alma-post-card__inline-toggle:focus-visible,
        body.alma-app .alma-post-card__inline-toggle:active {
            background: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
            outline: none !important;
        }
        body.alma-app .alma-post-card__metric-button,
        body.alma-app .alma-post-card__metric-button:hover,
        body.alma-app .alma-post-card__metric-button:focus,
        body.alma-app .alma-post-card__metric-button:focus-visible,
        body.alma-app .alma-post-card__metric-button:active {
            background: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
            outline: none !important;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        @media (max-width: 767.98px) {
            details[data-mobile-sheet="1"][open] {
                position: relative;
                z-index: 1100;
            }

            details[data-mobile-sheet="1"] [data-mobile-sheet-panel="1"] {
                position: fixed !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                top: auto !important;
                width: 100vw !important;
                max-width: 100vw !important;
                max-height: 78vh !important;
                margin: 0 !important;
                border-radius: 20px 20px 0 0 !important;
                overflow: auto !important;
                transform: translateY(110%);
                transition: transform .25s ease;
                z-index: 1102 !important;
            }

            details[data-mobile-sheet="1"][open] [data-mobile-sheet-panel="1"] {
                transform: translateY(0);
            }

            .mobile-sheet-backdrop {
                position: fixed;
                inset: 0;
                border: 0;
                background: rgba(15, 23, 42, 0.45);
                z-index: 1101;
            }
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

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .layout-side {
            min-width: 0;
        }
        .layout-main {
            min-width: 0;
            width: 100%;
            max-width: var(--profile-shell-width);
            margin-left: auto;
            margin-right: auto;
        }

        .layout-main .mx-auto.max-w-7xl,
        .layout-main .mx-auto.max-w-6xl,
        .layout-main .mx-auto.max-w-5xl,
        .layout-main .mx-auto.max-w-4xl,
        .layout-main .mx-auto.max-w-3xl,
        .layout-main .mx-auto.max-w-2xl {
            max-width: var(--profile-shell-width) !important;
            width: 100% !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        .layout-main > .mx-auto[class*="max-w-"],
        .layout-main > div > .mx-auto[class*="max-w-"],
        .layout-main > div > div > .mx-auto[class*="max-w-"],
        .messages-route-main > .mx-auto[class*="max-w-"],
        .messages-route-main > div > .mx-auto[class*="max-w-"],
        .messages-route-main > div > div > .mx-auto[class*="max-w-"] {
            max-width: var(--profile-shell-width) !important;
            width: 100% !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        .messages-route-main {
            min-width: 0;
            width: 100%;
            max-width: none !important;
            padding-left: 0 !important;
        }

        .layout-sticky {
            position: sticky;
            top: calc(var(--site-header-height) + 16px);
            align-self: start;
            max-height: calc(100vh - var(--site-header-height) - 24px);
            overflow: auto;
            padding-right: 12px;
        }

        :root {
            --profile-shell-width: 656px;
            --layout-left-width: 240px;
            --layout-right-width: 344px;
            --layout-column-gap: 36px;
            --layout-shell-inline: 24px;
            --layout-shell-max: calc(
                var(--layout-left-width) + var(--profile-shell-width) + var(--layout-right-width) + (var(--layout-column-gap) * 2) + (var(--layout-shell-inline) * 2)
            );
            --site-bg: #f4f4f5;
            --site-surface: #ffffff;
            --site-surface-muted: #fafafa;
            --site-border: rgba(39, 39, 42, 0.1);
            --site-shadow: none;
            --site-shadow-soft: none;
            --site-accent: #18181b;
            --site-accent-soft: #ffffff;
            --site-text: #18181b;
            --site-muted: #71717a;
            --site-header-bg: #ffffff;
            --site-hover-white: #ffffff;
            --site-hover-muted: #f4f4f5;
            --background: #f7f9fa;
            --foreground: #18181b;
            --border: rgba(39, 39, 42, 0.12);
            --primary: #18181b;
            --primary-foreground: #ffffff;
            --secondary: #ffffff;
            --secondary-foreground: #3f3f46;
            --muted: #f4f4f5;
            --muted-foreground: #71717a;
            --success: #ecfdf5;
            --success-foreground: #027a48;
            --accent: #0f766e;
            --accent-foreground: #ffffff;
            --destructive: #feefef;
            --destructive-foreground: #981b1b;
            --warning: #fff8e6;
            --warning-foreground: #7a4b00;
            --card: #ffffff;
            --card-foreground: #18181b;
            --sidebar: #ffffff;
            --sidebar-foreground: #18181b;
            --sidebar-primary: #18181b;
            --sidebar-primary-foreground: #ffffff;
            --radius-sm: 4px;
            --radius-md: 6px;
            --radius-lg: 8px;
            --radius-xl: 12px;
            --font-family-body: Roboto;
        }

        body.theme-minimal {
            background: var(--site-bg);
            background-image: none;
            color: var(--site-text);
        }

        .site-header {
            background: var(--site-header-bg);
            border-bottom: 1px solid var(--site-border);
            box-shadow: none;
            backdrop-filter: none;
        }

        body.route-discover .site-header,
        body.route-discover .site-header-shell {
            background: #ffffff !important;
        }

        .site-header .site-primary-btn {
            background: var(--site-accent);
            color: #ffffff;
            border-radius: 999px;
            padding: 0.55rem 1.4rem;
            font-weight: 500;
            box-shadow: none;
            transition: none;
        }

    .site-header .site-primary-btn:hover {
        transform: none;
        background: #27272a;
        box-shadow: none;
    }

        .site-card,
        .sidebar-card {
            background: var(--site-surface);
            border: 1px solid var(--site-border);
            border-radius: 20px;
            box-shadow: var(--site-shadow-soft);
        }

        .site-main-shell {
            width: 100%;
            max-width: var(--profile-shell-width);
            margin-left: auto;
            margin-right: auto;
        }

        .sidebar-card {
            background: var(--site-surface);
            padding: 18px;
        }

        .site-elevated {
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
        }

        .fade-up {
            animation: fadeUp 320ms ease-out both;
        }

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        

        .shadow-sm {
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        }

        .rounded-xl {
            border-radius: 18px;
        }

        .main-grid {
            max-width: var(--layout-shell-max);
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: var(--layout-left-width) minmax(0, 1fr) var(--layout-right-width);
            gap: var(--layout-column-gap);
            justify-content: center;
            align-items: start;
            padding-top: 24px;
            padding-bottom: 24px;
        }

        .main-grid--padded {
            padding-left: var(--layout-shell-inline);
            padding-right: var(--layout-shell-inline);
        }

        .main-grid--no-pad {
            padding-left: var(--layout-shell-inline);
            padding-right: var(--layout-shell-inline);
        }

        @media (max-width: 1024px) {
            .main-grid {
                grid-template-columns: minmax(0, 1fr);
                justify-content: stretch;
            }
        }

        @media (max-width: 767px) {
            .community-shell {
                padding-left: max(var(--alma-page-inline), env(safe-area-inset-left)) !important;
                padding-right: max(var(--alma-page-inline), env(safe-area-inset-right)) !important;
            }
        }

    </style>
    @livewireStyles
    @stack('head')
    <style>
    :root {
        --site-header-height: 70px;
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
        background: var(--site-hover-muted);
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
        background: var(--site-hover-white);
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
        background: var(--site-hover-muted);
        color: rgb(15 23 42 / 1);
    }

    :root {
        --alma-bg: #f4f4f5;
        --alma-header-bg: #ffffff;
        --alma-primary: #18181b;
        --alma-primary-strong: #27272a;
        --alma-text: #18181b;
        --alma-muted: #71717a;
        --alma-soft: #a1a1aa;
        --alma-hover-white: #ffffff;
        --alma-hover-muted: #f4f4f5;
        --alma-card: #ffffff;
        --alma-border: rgba(39, 39, 42, 0.1);
        --alma-shadow: none;
        --alma-page-inline: 14px;
        --alma-page-inline-tight: 12px;
        --alma-card-inline: 18px;
    }

    body.alma-app {
        background: var(--alma-bg);
        color: var(--alma-text);
    }

    body.alma-app *,
    body.alma-app *::before,
    body.alma-app *::after {
        box-shadow: none !important;
    }

    .site-header {
        background: var(--alma-header-bg);
        border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        backdrop-filter: none;
    }

    .site-header-shell {
        max-width: var(--layout-shell-max);
        margin: 0 auto;
        min-height: 72px;
        padding: 0 var(--layout-shell-inline);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .site-header-logo {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        line-height: 1;
        font-family: "Roboto", Arial, Helvetica, sans-serif;
        color: var(--alma-text);
    }

    .site-header-logo-image {
        display: block;
        width: auto;
        height: 34px;
    }

    .site-header-logo-wordmark {
        display: inline-flex;
        align-items: center;
        font-size: 1.2rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: var(--alma-text);
    }

    .site-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .site-icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid rgba(17, 24, 39, 0.08);
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--alma-text);
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .site-icon-btn:hover {
        background: var(--alma-hover-muted);
        border-color: rgba(17, 24, 39, 0.14);
    }

    

    

    

    

    @media (min-width: 1024px) {
        
    }

    .site-icon-btn--status {
        position: relative;
    }

    .site-status-dot {
        position: absolute;
        top: 9px;
        right: 9px;
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: #2600ff;
        box-shadow: 0 0 0 2px #ffffff;
    }

    .site-header-desktop-only {
        display: inline-flex;
    }

    .site-header .site-primary-btn {
        background: var(--alma-primary);
        color: #ffffff;
        border-radius: 12px;
        min-height: 40px;
        padding: 0 18px;
        font-weight: 500;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .site-header .site-primary-btn:hover {
        background: var(--alma-primary-strong);
    }

    .site-search-panel {
        position: relative;
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        width: 42px;
        overflow: visible;
    }

    .site-search-trigger {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid rgba(17, 24, 39, 0.04);
        background: #eceff3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #111827;
        transition: opacity 0.15s ease, background-color 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
    }

    .site-search-trigger:hover,
    .site-search-trigger:focus-visible {
        background: #ffffff;
        border-color: rgba(17, 24, 39, 0.1);
    }

    .site-search-trigger:focus-visible {
        outline: none;
    }

    .site-search-trigger iconify-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .site-search-panel.is-open .site-search-trigger {
        opacity: 0;
        pointer-events: none;
        transform: scale(0.92);
    }

    .site-search-dropdown-top {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0;
        border-bottom: 0;
        background: transparent;
    }

    .site-search-field {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-width: 0;
        flex: 1 1 auto;
        height: 40px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid rgba(17, 24, 39, 0.08);
        background: #ffffff;
        box-shadow: none;
        transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .site-search-field:hover {
        background: var(--alma-hover-muted);
        border-color: rgba(17, 24, 39, 0.12);
    }

    .site-search-field:focus-within {
        background: #ffffff;
        border-color: rgba(17, 24, 39, 0.14);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .site-search-icon {
        font-size: 17px;
        color: #6b7280;
        flex-shrink: 0;
    }

    .site-search-field input {
        width: 100%;
        border: 0;
        background: transparent;
        color: var(--alma-text);
        font-size: 14px;
    }

    .site-search-field input:focus {
        outline: none;
    }

    .site-search-clear {
        width: 26px;
        height: 26px;
        border: none;
        border-radius: 999px;
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        flex-shrink: 0;
    }

    .site-search-clear iconify-icon {
        font-size: 16px;
    }

    .site-search-clear.hidden {
        display: none;
    }

    .site-search-clear:hover {
        background: var(--alma-hover-muted);
    }

    .site-search-close {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background: #f3f4f6;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        flex-shrink: 0;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .site-search-close:hover {
        background: #ffffff;
        border-color: rgba(17, 24, 39, 0.12);
    }

    .site-search-close iconify-icon {
        font-size: 18px;
    }

    .site-search-dropdown {
        position: absolute;
        top: 50%;
        right: 0;
        left: auto;
        width: min(420px, calc(100vw - 260px));
        overflow: visible;
        border-radius: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
        transform: translateY(-50%);
        z-index: 70;
    }

    .site-search-results-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 100%;
        margin-top: 0;
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(17, 24, 39, 0.08);
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
    }

    .site-search-results {
        max-height: min(60vh, 420px);
        overflow-y: auto;
        padding: 10px 0 6px;
    }

    .site-search-empty {
        margin: 0;
        padding: 16px 18px 12px;
        font-size: 14px;
        color: #64748b;
    }

    .site-search-section {
        padding: 0 10px 8px;
    }

    .site-search-section-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 8px 8px;
    }

    .site-search-section-title {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
    }

    .site-search-section-line {
        height: 1px;
        flex: 1 1 auto;
        background: rgba(17, 24, 39, 0.14);
    }

    .site-search-section-list {
        display: flex;
        flex-direction: column;
    }

    .site-search-row {
        display: block;
        border-radius: 14px;
        padding: 8px 12px;
        text-decoration: none;
        color: #111827;
        transition: background-color 0.15s ease;
    }

    .site-search-row:hover {
        background: var(--alma-hover-muted);
    }

    .site-search-row-stack {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .site-search-avatar,
    .site-search-glyph {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .site-search-avatar {
        object-fit: cover;
    }

    .site-search-avatar--fallback,
    .site-search-glyph {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .site-search-avatar--fallback {
        background: #fde68a;
        color: #92400e;
        font-size: 11px;
        font-weight: 600;
    }

    .site-search-glyph {
        color: #111827;
    }

    .site-search-glyph iconify-icon {
        font-size: 18px;
    }

    .site-search-row-copy {
        min-width: 0;
    }

    .site-search-row-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
        font-weight: 600;
        color: #111827;
    }

    .site-search-row-meta {
        margin: 2px 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 12px;
        color: #6b7280;
    }

    .site-search-all {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px 14px;
        border-top: 1px solid rgba(17, 24, 39, 0.06);
        color: #111827;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        background: #ffffff;
    }

    .site-search-all:hover {
        background: var(--alma-hover-muted);
    }

    .site-search-all iconify-icon {
        font-size: 16px;
        color: #4b5563;
    }

    .site-search-all iconify-icon {
        font-size: 16px;
        color: #64748b;
    }

    .site-notifications-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: -12px;
        width: min(320px, calc(100vw - 24px));
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(17, 24, 39, 0.08);
        background: #ffffff;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.14);
        z-index: 72;
    }

    .site-notifications-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 14px;
        border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        background: #ffffff;
    }

    .site-notifications-panel-title {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
        color: #111827;
    }

    .site-notifications-more {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 0;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #111827;
        transition: background-color 0.15s ease;
    }

    .site-notifications-more:hover {
        background: var(--alma-hover-muted);
    }

    .site-notifications-more iconify-icon {
        font-size: 18px;
    }

    .site-notifications-actions-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 184px;
        padding: 8px;
        border-radius: 14px;
        border: 1px solid rgba(17, 24, 39, 0.08);
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.14);
        z-index: 73;
    }

    .site-notifications-menu-item {
        width: 100%;
        border: 0;
        border-radius: 12px;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        color: #111827;
        font-size: 14px;
        text-align: left;
        transition: background-color 0.15s ease;
    }

    .site-notifications-menu-item:hover {
        background: var(--alma-hover-muted);
    }

    .site-notifications-menu-item iconify-icon {
        font-size: 16px;
        color: #6b7280;
        flex-shrink: 0;
    }

    .site-notifications-list {
        max-height: min(70vh, 560px);
        overflow-y: auto;
        background: #ffffff;
    }

    .site-notifications-list::-webkit-scrollbar {
        width: 4px;
    }

    .site-notifications-list::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 999px;
    }

    .site-notifications-empty {
        margin: 0;
        padding: 18px 16px;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
    }

    .site-notification-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 14px;
        text-decoration: none;
        color: #111827;
        background: #ffffff;
        transition: background-color 0.15s ease;
    }

    .site-notification-item:hover {
        background: var(--alma-hover-muted);
    }

    .site-notification-item-avatar,
    .site-notification-item-avatar--fallback {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .site-notification-item-avatar {
        object-fit: cover;
    }

    .site-notification-item-avatar--fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    .site-notification-item-copy {
        min-width: 0;
        flex: 1 1 auto;
    }

    .site-notification-item-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1;
    }

    .site-notification-item-meta iconify-icon {
        font-size: 13px;
        color: #9ca3af;
    }

    .site-notification-item-title {
        margin: 0;
        color: #1f2937;
        font-size: 13px;
        line-height: 1.45;
    }

    .site-notification-item-title strong {
        font-weight: 600;
        color: #111827;
    }

    .site-notification-item-preview {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
    }

    .site-header-write-btn {
        display: inline-flex;
    }

    .site-menu-panel {
        border-radius: 18px;
        border: 1px solid rgba(17, 24, 39, 0.08);
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.1);
        backdrop-filter: blur(16px);
    }

    .site-avatar-fallback {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #e8f6f1;
        color: var(--alma-primary-strong);
        font-size: 12px;
        font-weight: 500;
    }

    .main-grid {
        max-width: var(--layout-shell-max);
        grid-template-columns: var(--layout-left-width) minmax(0, 1fr) var(--layout-right-width);
        gap: var(--layout-column-gap);
        padding-top: 32px;
        padding-bottom: 48px;
    }

    .layout-main {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 24px;
        padding-left: 14px;
    }

    .layout-side {
        min-width: 0;
        align-self: start;
    }

    

    .layout-side--right {
        position: static;
        width: 100%;
        max-width: none;
    }

    .layout-sticky {
        position: sticky;
        top: 96px;
        align-self: start;
        max-height: none;
        overflow: visible;
        padding-right: 0;
    }

    @media (min-width: 1181px) {
        .main-grid {
            max-width: var(--layout-shell-max);
            grid-template-columns: var(--layout-left-width) minmax(0, var(--profile-shell-width)) var(--layout-right-width);
        }

        .layout-main {
            width: 100%;
            max-width: var(--profile-shell-width);
            padding-left: 0;
        }

        .alma-post-card__image {
            height: auto;
        }
    }

    .site-card,
    .sidebar-card,
    .community-card,
    .alma-panel {
        background: var(--alma-card);
        border: 1px solid var(--alma-border);
        border-radius: 20px;
        box-shadow: var(--alma-shadow);
        padding-left: var(--alma-card-inline) !important;
        padding-right: var(--alma-card-inline) !important;
    }

    .site-card,
    .sidebar-card,
    .community-card {
        overflow: hidden;
    }

    .site-card > div,
    .site-card > section,
    .site-card > article,
    .site-card > header,
    .site-card > main,
    .site-card > form,
    .sidebar-card > div,
    .sidebar-card > section,
    .sidebar-card > article,
    .sidebar-card > header,
    .sidebar-card > main,
    .sidebar-card > form,
    .community-card > div,
    .community-card > section,
    .community-card > article,
    .community-card > header,
    .community-card > main,
    .community-card > form,
    .alma-panel > div,
    .alma-panel > section,
    .alma-panel > article,
    .alma-panel > header,
    .alma-panel > main,
    .alma-panel > form {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .sidebar-card {
        padding: 24px;
    }

    .alma-ad-slot {
        width: 100%;
        position: relative;
        overflow: hidden;
        border: 1px solid var(--alma-border);
        border-radius: 18px;
        background: var(--alma-card);
    }

    .alma-ad-slot__icon {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 2;
        display: block;
        width: 10px;
        height: 10px;
        pointer-events: none;
    }

    .alma-ad-slot__icon svg {
        display: block;
        width: 100%;
        height: 100%;
    }

    .alma-ad-slot__inner {
        padding: 16px;
    }

    .alma-ad-slot--cover {
        aspect-ratio: 16 / 9;
        min-height: 0 !important;
    }

    .alma-ad-slot--cover .alma-ad-slot__inner {
        width: 100%;
        min-height: 0 !important;
        height: 100%;
        aspect-ratio: 16 / 9;
    }

    .alma-ad-slot iframe,
    .alma-ad-slot img,
    .alma-ad-slot ins {
        max-width: 100% !important;
        display: block;
        margin-inline: auto;
    }

    .alma-ad-slot--mobile {
        display: none;
    }

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    .alma-feed-promo {
        padding: 22px 24px;
        text-align: center;
        font-size: 15px;
        color: var(--alma-text);
        border: 1px solid rgba(148, 163, 184, 0.16);
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(234, 246, 244, 0.96)),
            #ffffff;
    }

    .alma-feed-promo__eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #64748b;
    }

    .alma-feed-promo__copy {
        margin-top: 0.5rem;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.55;
        color: #0f172a;
    }

    .alma-feed-promo__action {
        border-color: rgba(148, 163, 184, 0.22);
        background: rgba(255, 255, 255, 0.82);
        color: #0f172a;
    }

    .alma-feed-promo__action iconify-icon {
        color: currentColor;
    }

    .alma-feed-promo a {
        font-weight: 500;
    }

    .alma-tabs {
        display: flex;
        align-items: center;
        gap: 24px;
        padding: 0 8px;
    }

    .alma-tab {
        position: relative;
        display: inline-flex;
        align-items: center;
        min-height: 44px;
        padding: 4px 0 8px;
        font-size: 15px;
        font-weight: 500;
        color: #475569;
    }

    .alma-tab.is-active {
        color: var(--alma-primary-strong);
    }

    .alma-tab.is-active::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 2px;
        border-radius: 999px;
        background: var(--alma-primary-strong);
    }

    .alma-post-card {
        border-radius: 20px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .alma-post-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .alma-post-card__identity {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1 1 auto;
    }

    .alma-post-card__avatar-link {
        display: inline-flex;
        flex-shrink: 0;
        position: relative;
        width: 38px;
        height: 38px;
    }

    .alma-post-card__avatar-link > a {
        display: inline-flex;
        width: 38px;
        height: 38px;
    }

    .alma-post-card__avatar {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        object-fit: cover;
        background: #e5e7eb;
        flex-shrink: 0;
    }

    .alma-post-card__avatar--fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eaf6f4;
        color: var(--alma-primary-strong);
        font-size: 12px;
        font-weight: 500;
    }

    .alma-post-card__avatar-badge {
        position: absolute;
        right: -2px;
        left: auto;
        bottom: -1px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        border: 2px solid #ffffff;
        background: #f04452;
        color: #ffffff;
        box-shadow: none;
        overflow: hidden;
        z-index: 2;
    }

    .alma-post-card__avatar-badge iconify-icon {
        font-size: 8px;
    }

    .alma-post-card__avatar-badge-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .alma-post-card__avatar-badge-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        font-size: 8px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: 0;
        color: #ffffff;
        text-transform: uppercase;
    }

    .alma-post-card__meta {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .alma-post-card__author-row,
    .alma-post-card__submeta {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .alma-post-card__author {
        font-size: 15px;
        font-weight: 700;
        color: #111111;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .alma-post-card__verified {
        color: #3b82f6;
        font-size: 14px;
        flex-shrink: 0;
    }

    .alma-post-card__submeta,
    .alma-post-card__submeta a,
    .alma-post-card__submeta time,
    .alma-post-card__submeta span {
        font-size: 13px;
        color: #65676b;
    }

    .alma-post-card__pinned {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .alma-post-card__pinned,
    .alma-post-card__pinned span {
        color: var(--alma-primary-strong);
        font-weight: 500;
    }

    .alma-post-card__pinned iconify-icon {
        font-size: 13px;
    }

    .alma-post-card__category {
        font-weight: 600;
        color: #1877f2;
    }

    .alma-post-card__header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .alma-post-card__header-pill,
    .alma-post-card__header-follow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        border-radius: 999px;
        padding: 0 12px;
        background: #ffffff;
        color: #111827;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid rgba(226, 232, 240, 0.92);
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.03);
        white-space: nowrap;
    }

    .alma-post-card__header-pill {
        background: #f8fafc;
    }

    .alma-post-card__header-pill iconify-icon {
        font-size: 14px;
        color: #475569;
    }

    .alma-post-card__header-follow {
        background: #fffaf0;
        border-color: rgba(251, 191, 36, 0.18);
    }

    .alma-post-card__header-follow.is-active {
        background: #eef6ff;
        color: #4b5563;
        border-color: rgba(148, 163, 184, 0.34);
    }

    .alma-post-card__dot {
        width: 4px;
        height: 4px;
        border-radius: 999px;
        background: #cbd5e1;
        flex-shrink: 0;
    }

    .alma-post-card__content {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .alma-post-card__title {
        font-size: 21px;
        line-height: 1.34;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: var(--alma-text);
    }

    .alma-post-card__title.is-hero {
        font-size: 26px;
    }

    .alma-post-card__title a {
        color: inherit;
    }

    .alma-post-card__excerpt {
        font-size: 14.5px;
        line-height: 1.65;
        color: #374151;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .alma-post-card__media-link {
        display: block;
        overflow: hidden;
        border-radius: 16px;
    }

    .alma-post-card__image {
        display: block;
        width: 100%;
        height: auto;
        max-width: 100%;
        margin-inline: 0;
        border-radius: 16px;
        object-fit: cover;
        aspect-ratio: 3840 / 2160;
        background: #e5e7eb;
    }

    .alma-post-card__media-link:hover .alma-post-card__image {
        transform: none;
    }

    .route-home .site-card,
    .route-home .alma-post-card,
    .route-home .alma-panel,
    .route-home .alma-ad-slot {
        box-shadow: none;
    }

    .route-home {
        --profile-shell-width: 656px;
        --layout-left-width: 240px;
        --layout-right-width: 344px;
        --layout-column-gap: 36px;
    }

    .route-home .alma-feed-promo,
    .route-home .alma-tab,
    .route-home .alma-post-card,
    .route-home .alma-post-card__image {
        transition: none !important;
        animation: none !important;
    }

    .route-home .alma-post-card__media-link:hover .alma-post-card__image {
        transform: none !important;
    }

    .alma-post-card__footer {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .alma-post-card__reactions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: 2px;
    }

    .alma-post-card__reaction-more {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        border-radius: 999px;
        padding: 0 14px;
        background: #f3f4f6;
        color: #111827;
        font-size: 14px;
        font-weight: 500;
    }

    .alma-post-card__reaction-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 32px;
        border-radius: 999px;
        padding: 0 8px;
        background: #f8fafc;
        border: 1px solid rgba(226, 232, 240, 0.9);
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .alma-post-card__reaction-pill:hover {
        background: #ffffff;
        border-color: rgba(203, 213, 225, 0.9);
        color: #111827;
    }

    .alma-post-card__reaction-pill-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        line-height: 1;
    }

    .alma-post-card__reaction-pill-count {
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
    }

    .alma-post-card__reaction-picker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid rgba(226, 232, 240, 0.9);
        color: #374151;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .alma-post-card__reaction-picker svg {
        width: 18px;
        height: 18px;
        display: block;
        flex-shrink: 0;
    }

    .alma-post-card__reaction-picker:hover {
        background: #ffffff;
        color: #111827;
    }

    .alma-post-card__inline-expand {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .alma-post-card__inline-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 14px;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid rgba(226, 232, 240, 0.92);
    }

    .alma-post-card__inline-preview {
        min-width: 0;
        flex: 1 1 auto;
        margin: 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.55;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .alma-post-card__inline-toggle {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        flex-shrink: 0;
        min-height: 30px;
        padding: 0;
        background: transparent;
        color: #6b7280;
        font-size: 13px;
        font-weight: 600;
    }

    .alma-post-card__inline-toggle iconify-icon {
        font-size: 16px;
        color: currentColor;
        transition: transform 0.2s ease;
    }

    .alma-post-card__inline-toggle[aria-expanded="true"] iconify-icon {
        transform: rotate(180deg);
    }

    .alma-post-card__inline-more {
        display: none;
        padding: 2px;
    }

    .alma-post-card__inline-more.is-open {
        display: block;
    }

    .alma-post-card__inline-text {
        margin: 0;
        color: #374151;
        font-size: 14px;
        line-height: 1.72;
        white-space: pre-line;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid rgba(226, 232, 240, 0.92);
        padding: 14px;
    }

    .alma-post-card__engagement {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        padding-top: 2px;
    }

    .alma-post-card__engagement-main {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .alma-post-card__icon-form {
        margin: 0;
    }

    .alma-post-card__metric-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 44px;
        min-width: 44px;
        border: none;
        border-radius: 999px;
        background: transparent;
        color: #374151;
        padding: 0 10px;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .alma-post-card__metric-button:hover {
        background: var(--alma-hover-muted);
    }

    .alma-post-card__metric-button iconify-icon {
        font-size: 18px;
    }

    .alma-post-card__metric-button span {
        font-size: 14px;
        font-weight: 500;
    }

    .alma-post-card__metric-button.is-copied {
        color: var(--alma-primary-strong);
    }

    .alma-post-card__views {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--alma-muted);
        font-size: 14px;
        font-weight: 500;
    }

    .alma-post-card__views iconify-icon {
        font-size: 18px;
    }

    .alma-post-card__comments-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        padding-top: 12px;
        border-top: 1px solid rgba(17, 24, 39, 0.08);
    }

    .alma-post-card__comments-meta {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-width: 0;
        flex: 1 1 auto;
    }

    .alma-post-card__comment-avatars {
        display: flex;
        align-items: center;
    }

    .alma-post-card__comment-avatar {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        object-fit: cover;
        background: #e5e7eb;
        border: 1px solid rgba(226, 232, 240, 0.92);
        flex-shrink: 0;
    }

    .alma-post-card__comment-avatar--fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 11px;
        font-weight: 500;
        background: #f1f5f9;
    }

    .alma-post-card__comment-avatar--fallback iconify-icon {
        font-size: 14px;
    }

    .alma-post-card__comments-count {
        font-size: 13px;
        font-weight: 400;
        color: #0f172a;
        white-space: normal;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.42;
    }

    .alma-post-card__comment-preview {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0;
        justify-content: center;
        padding-top: 2px;
    }

    .alma-post-card__menu {
        position: relative;
        flex-shrink: 0;
    }

    .alma-post-card__menu summary {
        list-style: none;
    }

    .alma-post-card__menu summary::-webkit-details-marker {
        display: none;
    }

    .alma-post-card__menu-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 999px;
        background: transparent;
        color: #475569;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .alma-post-card__menu-trigger:hover {
        background: var(--alma-hover-muted);
    }

    .alma-post-card__menu-trigger iconify-icon {
        font-size: 18px;
    }

    .alma-post-card__menu[open] .alma-post-card__menu-panel {
        display: flex;
    }

    .alma-post-card__menu-panel {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 180px;
        display: none;
        flex-direction: column;
        padding: 8px;
        border-radius: 14px;
        border: 1px solid rgba(17, 24, 39, 0.08);
        background: #fff;
        z-index: 15;
    }

    .alma-post-card__menu-form {
        margin: 0;
    }

    .alma-post-card__menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        border: none;
        border-radius: 10px;
        background: transparent;
        padding: 10px 12px;
        text-align: left;
        font-size: 14px;
        font-weight: 500;
        color: #334155;
    }

    .alma-post-card__menu-item iconify-icon {
        font-size: 15px;
        color: #64748b;
        flex-shrink: 0;
    }

    .alma-post-card__menu-item--button {
        cursor: pointer;
    }

    .alma-post-card__menu-item:hover {
        background: var(--alma-hover-muted);
    }

    .alma-post-card__menu-item--danger {
        color: #b91c1c;
    }

    .alma-post-card__menu-item--danger iconify-icon {
        color: #b91c1c;
    }

    .alma-widget__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .alma-widget__title {
        font-size: 16px;
        font-weight: 500;
        color: var(--alma-text);
    }

    .alma-widget__comment {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px 0;
        border-bottom: 1px solid rgba(17, 24, 39, 0.07);
        border-radius: 14px;
        margin: 0 -10px;
        padding-inline: 10px;
        transition: background-color 0.15s ease;
    }

    .alma-widget__comment:hover {
        background: var(--alma-hover-muted);
    }

    .alma-widget__comment:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .alma-widget__comment-user {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alma-widget__comment-avatar,
    .alma-widget__comment-avatar--fallback {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        flex-shrink: 0;
        object-fit: cover;
        background: #e5e7eb;
    }

    .alma-widget__comment-avatar--fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #475569;
        font-size: 11px;
        font-weight: 500;
    }

    .alma-widget__comment-meta {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .alma-widget__comment-author {
        font-size: 14px;
        font-weight: 500;
        color: var(--alma-text);
    }

    .alma-widget__comment-post {
        font-size: 13px;
        color: var(--alma-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    .alma-widget__comment-text {
        font-size: 14px;
        line-height: 1.5;
        color: var(--alma-text);
    }

    .alma-widget__comment-time {
        font-size: 12px;
        color: var(--alma-soft);
    }

    .alma-tag-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(17, 24, 39, 0.07);
        border-radius: 12px;
        margin: 0 -10px;
        padding-inline: 10px;
        transition: background-color 0.15s ease;
    }

    .alma-tag-item:hover {
        background: var(--alma-hover-muted);
    }

    .alma-tag-item:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .alma-tag-item__name {
        font-size: 14px;
        font-weight: 500;
        color: var(--alma-text);
    }

    .alma-tag-item__count {
        font-size: 13px;
        color: var(--alma-muted);
    }

    .alma-page-header {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .alma-page-header--compact-card {
        gap: 0;
        padding: 0;
        border-radius: 0;
        background: transparent;
    }

    .alma-page-kicker {
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--alma-soft);
    }

    .alma-page-title {
        font-size: 30px;
        line-height: 1.15;
        font-weight: 500;
        letter-spacing: -0.04em;
        color: var(--alma-text);
    }

    .alma-page-title--compact-card {
        margin: 0;
        font-size: 20px;
        line-height: 1.2;
        letter-spacing: -0.03em;
    }

    .alma-page-subtitle {
        font-size: 15px;
        line-height: 1.6;
        color: var(--alma-muted);
    }

    .alma-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .alma-button,
    .alma-button-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 14px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.5;
        letter-spacing: 0;
        transition: none;
    }

    .alma-button {
        background: #18181b;
        color: #ffffff;
        box-shadow: none;
    }

    .alma-button:hover {
        transform: none;
        background: #27272a;
    }

    .alma-button-secondary {
        background: #f4f4f5;
        color: #18181b;
        border: 0;
    }

    .alma-button-secondary:hover {
        background: #ffffff;
    }

    .site-menu-panel a:hover,
    .site-menu-panel button:hover {
        background: var(--alma-hover-muted) !important;
    }

    body.alma-app {
        font-weight: 400;
    }

    body.alma-app :where(
        .site-header .site-primary-btn,
        .community-pill,
        .community-btn,
        .site-avatar-fallback,
        .alma-feed-promo a,
        .alma-tab,
        .alma-post-card__avatar--fallback,
        .alma-post-card__author,
        .alma-post-card__category,
        .alma-post-card__pinned,
        .alma-post-card__title,
        .alma-post-card__comments-count,
        .alma-post-card__menu-item,
        .alma-widget__title,
        .alma-widget__comment-avatar--fallback,
        .alma-widget__comment-author,
        .alma-tag-item__name,
        .alma-page-kicker,
        .alma-page-title,
        .alma-button,
        .alma-button-secondary,
        .font-medium,
        .font-semibold,
        .font-bold,
        .font-extrabold,
        .font-black
    ){
        font-weight: 500 !important;
    }

    .alma-input,
    .alma-select {
        width: 100%;
        min-height: 44px;
        border-radius: 14px;
        border: 1px solid rgba(17, 24, 39, 0.08);
        background: rgba(255, 255, 255, 0.88);
        padding: 0 14px;
        font-size: 14px;
        color: var(--alma-text);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    }

    .alma-input:focus,
    .alma-select:focus {
        outline: none;
        border-color: rgba(2, 157, 113, 0.35);
        box-shadow: 0 0 0 4px rgba(2, 157, 113, 0.08);
    }

    .route-discover input[type="search"] {
        appearance: none;
        -webkit-appearance: none;
    }

    .route-discover input[type="search"]::-webkit-search-decoration,
    .route-discover input[type="search"]::-webkit-search-cancel-button,
    .route-discover input[type="search"]::-webkit-search-results-button,
    .route-discover input[type="search"]::-webkit-search-results-decoration {
        display: none;
        -webkit-appearance: none;
    }

    .alma-empty-state {
        padding: 28px 24px;
        text-align: center;
        font-size: 14px;
        color: var(--alma-muted);
    }

    [data-mobile-bottom-nav] {
        background: #ffffff !important;
        border: 1px solid rgba(17, 24, 39, 0.08) !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08) !important;
        backdrop-filter: none;
    }

    [data-mobile-bottom-nav] a,
    [data-mobile-bottom-nav] button {
        color: inherit;
        background: transparent !important;
        box-shadow: none !important;
    }

    [data-mobile-bottom-nav] svg {
        color: currentColor;
    }

    [data-mobile-bottom-nav] .text-fg-brand {
        color: var(--alma-primary) !important;
    }

    .mobile-bottom-nav__plus {
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }

    .mobile-bottom-nav__plus:hover {
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.18) !important;
    }

    @media (max-width: 1180px) {
        .main-grid {
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 44px;
        }

        .layout-main {
            padding-left: 0;
        }

        .main-grid > aside:first-child {
            display: none !important;
        }
    }

    @media (max-width: 1023px) {
        .site-search-panel {
            display: none;
        }

        .site-header-write-btn {
            display: none;
        }
    }

    @media (max-width: 960px) {
        .main-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .main-grid > aside:last-child {
            display: none !important;
        }
    }

    @media (max-width: 640px) {
        .site-header-shell,
        .community-shell,
        .main-grid {
            padding-left: max(var(--alma-page-inline), env(safe-area-inset-left));
            padding-right: max(var(--alma-page-inline), env(safe-area-inset-right));
        }

        .site-card,
        .sidebar-card,
        .community-card,
        .alma-panel {
            padding-left: var(--alma-card-inline) !important;
            padding-right: var(--alma-card-inline) !important;
        }

        .site-card > .p-3,
        .site-card > .p-4,
        .site-card > .p-5,
        .site-card > .p-6,
        .site-card > .px-3,
        .site-card > .px-4,
        .site-card > .px-5,
        .site-card > .px-6,
        .site-card > .pl-3,
        .site-card > .pl-4,
        .site-card > .pl-5,
        .site-card > .pl-6,
        .site-card > .pr-3,
        .site-card > .pr-4,
        .site-card > .pr-5,
        .site-card > .pr-6,
        .sidebar-card > .p-3,
        .sidebar-card > .p-4,
        .sidebar-card > .p-5,
        .sidebar-card > .p-6,
        .sidebar-card > .px-3,
        .sidebar-card > .px-4,
        .sidebar-card > .px-5,
        .sidebar-card > .px-6,
        .community-card > .p-3,
        .community-card > .p-4,
        .community-card > .p-5,
        .community-card > .p-6,
        .community-card > .px-3,
        .community-card > .px-4,
        .community-card > .px-5,
        .community-card > .px-6,
        .alma-panel > .p-3,
        .alma-panel > .p-4,
        .alma-panel > .p-5,
        .alma-panel > .p-6,
        .alma-panel > .px-3,
        .alma-panel > .px-4,
        .alma-panel > .px-5,
        .alma-panel > .px-6,
        .alma-panel > .pl-3,
        .alma-panel > .pl-4,
        .alma-panel > .pl-5,
        .alma-panel > .pl-6,
        .alma-panel > .pr-3,
        .alma-panel > .pr-4,
        .alma-panel > .pr-5,
        .alma-panel > .pr-6 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .route-discover .main-grid {
            padding-left: max(var(--alma-page-inline), env(safe-area-inset-left)) !important;
            padding-right: max(var(--alma-page-inline), env(safe-area-inset-right)) !important;
        }

        .route-discover .alma-post-card {
            width: 100%;
            max-width: none;
            margin-left: 0;
            margin-right: 0;
            border-radius: 14px;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .site-header-shell {
            min-height: 64px;
            gap: 12px;
        }

        .site-header-logo {
            gap: 8px;
            min-width: 0;
        }

        .site-header-logo-image {
            height: 30px;
        }

        .site-header-logo-wordmark {
            font-size: 1rem;
        }

        .site-header-actions {
            gap: 8px;
            flex-wrap: nowrap;
        }

        .site-header-desktop-only {
            display: none !important;
        }

        .main-grid {
            gap: 16px;
            padding-top: 16px;
            padding-bottom: 108px;
        }

        .layout-main > div > .mx-auto.max-w-7xl,
        .layout-main > div > .mx-auto.max-w-6xl,
        .layout-main > div > .mx-auto.max-w-5xl,
        .layout-main > div > .mx-auto.max-w-4xl,
        .layout-main > div > .mx-auto.max-w-3xl,
        .layout-main > div > .mx-auto.max-w-2xl {
            padding-left: max(var(--alma-page-inline), env(safe-area-inset-left)) !important;
            padding-right: max(var(--alma-page-inline), env(safe-area-inset-right)) !important;
        }

        .alma-post-card,
        .sidebar-card,
        .site-card,
        .community-card,
        .alma-panel,
        .message-show-card,
        .message-compose-box,
        .message-action-box,
        .site-menu-panel,
        .site-search-results-panel,
        .site-notifications-panel {
            border-radius: 16px;
        }

        .alma-post-card,
        .sidebar-card,
        .sidebar-card {
            padding: 18px;
        }

        .alma-post-card__title,
        .alma-post-card__title.is-hero {
            font-size: 18px;
            line-height: 1.35;
        }

        .alma-post-card__header,
        .alma-post-card__engagement,
        .alma-post-card__comments-strip {
            gap: 10px;
        }

        .alma-post-card__header-actions {
            gap: 6px;
        }

        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            min-height: 32px;
            padding-inline: 10px;
            font-size: 12px;
        }

        .alma-post-card__inline-summary,
        .alma-post-card__engagement {
            align-items: flex-start;
        }

        .alma-post-card__inline-summary {
            flex-wrap: wrap;
        }

        .alma-post-card__inline-toggle {
            padding: 0;
            padding-inline: 0;
            margin-left: auto;
            align-self: flex-end;
        }

        .alma-post-card__engagement-main {
            width: auto;
            justify-content: flex-start;
            gap: 14px;
        }

        .alma-post-card__comment-avatar {
            width: 28px;
            height: 28px;
        }

        .alma-page-title {
            font-size: 22px;
            line-height: 1.2;
        }

        .alma-page-subtitle,
        .alma-post-card__excerpt,
        .alma-post-card__inline-preview,
        .alma-post-card__inline-text,
        .alma-widget__comment-text,
        .alma-widget__comment-post,
        .alma-tag-item__name,
        .alma-widget__comment-author,
        .alma-post-card__author,
        .alma-post-card__comments-count,
        .community-btn,
        .community-pill,
        .message-chip-btn {
            font-size: 14px;
        }

        .alma-post-card__reaction-more {
            min-height: 32px;
            padding-inline: 12px;
            font-size: 13px;
        }

        .alma-feed-promo {
            padding: 16px;
        }

        .alma-feed-promo .flex {
            gap: 12px;
        }

        .alma-feed-promo__copy {
            font-size: 14px;
        }

        .alma-button,
        .alma-button-secondary,
        .site-primary-btn,
        .community-btn,
        .community-pill,
        .message-chip-btn,
        .site-icon-btn {
            border-radius: 14px;
        }

        .alma-input,
        .alma-select,
        .site-search-field,
        .site-search-field input,
        input:not([type="checkbox"]):not([type="radio"]),
        textarea,
        select {
            border-radius: 14px;
        }

        .alma-widget__header {
            margin-bottom: 14px;
        }

        .alma-widget__comment,
        .alma-tag-item {
            margin-inline: -6px;
            padding-inline: 6px;
        }

        [data-mobile-bottom-nav] {
            height: 60px !important;
            width: calc(100% - 16px) !important;
            max-width: 420px;
            bottom: max(8px, env(safe-area-inset-bottom));
            padding-inline: 6px;
            border-radius: 18px !important;
        }

        [data-mobile-bottom-nav] > div {
            gap: 2px;
        }

        [data-mobile-bottom-nav] a,
        [data-mobile-bottom-nav] button {
            min-width: 0;
        }

        .mobile-bottom-nav__plus {
            width: 42px !important;
            height: 42px !important;
            border-radius: 14px !important;
        }

        .route-home .alma-tabs {
            gap: 24px;
            padding: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .route-home .alma-tabs::-webkit-scrollbar {
            display: none;
        }

        .route-home .alma-tab {
            min-height: 44px;
            flex: 0 0 auto;
            padding: 4px 0 8px;
            border-radius: 0;
            border: none;
            background: transparent;
            color: #475569;
        }

        .route-home .alma-tab.is-active {
            background: transparent;
            color: var(--alma-primary-strong);
        }

        .route-home .alma-tab.is-active::after {
            display: block;
        }
    }

    @media (max-width: 480px) {
        .site-header-shell,
        .community-shell,
        .main-grid {
            padding-left: max(var(--alma-page-inline-tight), env(safe-area-inset-left));
            padding-right: max(var(--alma-page-inline-tight), env(safe-area-inset-right));
        }

        .main-grid {
            gap: 14px;
            padding-top: 14px;
            padding-bottom: 112px;
        }

        

        .layout-main > div > .mx-auto.max-w-7xl,
        .layout-main > div > .mx-auto.max-w-6xl,
        .layout-main > div > .mx-auto.max-w-5xl,
        .layout-main > div > .mx-auto.max-w-4xl,
        .layout-main > div > .mx-auto.max-w-3xl,
        .layout-main > div > .mx-auto.max-w-2xl {
            padding-left: max(var(--alma-page-inline-tight), env(safe-area-inset-left)) !important;
            padding-right: max(var(--alma-page-inline-tight), env(safe-area-inset-right)) !important;
        }

        .site-header-logo-wordmark {
            font-size: 0.95rem;
        }

        .site-icon-btn {
            width: 38px;
            height: 38px;
        }

        .alma-post-card,
        .sidebar-card,
        .site-card,
        .community-card,
        .alma-panel,
        .message-show-card,
        .message-compose-box,
        .message-action-box,
        .site-menu-panel,
        .site-search-results-panel,
        .site-notifications-panel {
            border-radius: 14px;
        }

        .alma-post-card,
        .sidebar-card,
        .community-card,
        .alma-panel {
            padding: 16px;
        }

        .alma-post-card__inline-summary {
            padding: 10px 12px;
            gap: 10px;
        }

        .alma-post-card__inline-toggle {
            min-height: 34px;
            padding-inline: 12px;
            font-size: 12px;
        }

        .alma-post-card__inline-text {
            padding: 12px;
        }

        .alma-post-card__comments-count {
            font-size: 12px;
        }

        .alma-button,
        .alma-button-secondary,
        .site-primary-btn,
        .community-btn,
        .community-pill,
        .message-chip-btn,
        .site-icon-btn {
            border-radius: 12px;
        }

        .alma-input,
        .alma-select,
        .site-search-field,
        .site-search-field input,
        input:not([type="checkbox"]):not([type="radio"]),
        textarea,
        select {
            border-radius: 12px;
        }
    }

    @media (max-width: 1023px) {
        .alma-ad-slot--desktop {
            display: none !important;
        }

        .alma-ad-slot--mobile {
            display: block;
        }
    }

    /* Post card refresh: static, Facebook-style visual treatment */
    .alma-post-card {
        font-family: "Segoe UI", Helvetica, Arial, sans-serif;
        background: var(--site-surface);
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 12px;
        box-shadow: none;
        padding: 18px 18px 16px;
        gap: 0;
        overflow: visible;
    }

    .route-home .alma-post-card {
        box-shadow: none !important;
    }

    .alma-post-card,
    .alma-post-card *,
    .alma-post-card *::before,
    .alma-post-card *::after {
        animation: none !important;
        transition: none !important;
    }

    .alma-post-card a {
        color: inherit;
    }

    .alma-post-card__header {
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }

    .alma-post-card__identity {
        min-width: 0;
        flex: 1 1 auto;
        align-items: center;
        gap: 10px;
    }

    .alma-post-card__avatar-link {
        position: relative;
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        margin-top: 0;
    }

    .alma-post-card__avatar-link > a {
        display: block;
        width: 40px;
        height: 40px;
    }

    .alma-post-card__avatar,
    .alma-post-card__avatar--fallback {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        object-fit: cover;
        display: block;
        background: #d8dbe1;
        border: none;
        box-shadow: none;
    }

    .alma-post-card__avatar--fallback {
        color: #1c1e21;
        background: #e4e6eb;
        font-size: 12px;
        font-weight: 700 !important;
        letter-spacing: 0.02em;
    }

    .alma-post-card__avatar-badge {
        right: -3px;
        left: auto;
        bottom: -2px;
        width: 19px;
        height: 19px;
        border-radius: 999px;
        object-fit: cover;
        border: 2px solid #f7f7f7;
        background: #f04452;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: none;
        z-index: 2;
    }

    .alma-post-card__avatar-badge-image {
        width: 100%;
        height: 100%;
        border-radius: 999px;
        object-fit: cover;
        display: block;
    }

    .alma-post-card__avatar-badge-icon {
        font-size: 9px;
    }

    .alma-post-card__avatar-badge-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        font-size: 8px;
        line-height: 1;
        font-weight: 800 !important;
        letter-spacing: 0;
        color: #ffffff;
        text-transform: uppercase;
        color: #0f4e67;
    }

    .alma-post-card__meta {
        font-family: Arial, Helvetica, sans-serif;
        min-width: 0;
        flex: 1 1 auto;
        gap: 1px;
        padding-top: 0;
    }

    .alma-post-card__author-row {
        gap: 5px;
        margin-bottom: 2px;
    }

    .alma-post-card__author {
        max-width: 240px;
        font-size: 15px;
        line-height: 1.26;
        color: #1a1a1a;
        font-weight: 700 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .alma-post-card__verified {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        margin-top: 0;
    }

    .alma-post-card__verified svg {
        display: block;
        width: 18px;
        height: 18px;
    }

    .alma-post-card__submeta,
    .alma-post-card__submeta a,
    .alma-post-card__submeta time,
    .alma-post-card__submeta span {
        font-size: 13px;
        line-height: 1.28;
        color: #65676b;
        font-weight: 500;
    }

    .alma-post-card__category {
        color: #1877f2;
        font-weight: 700 !important;
    }

    .alma-post-card__dot {
        width: 4px;
        height: 4px;
        background: #c6c6c6;
        opacity: 1;
    }

    .alma-post-card__pinned,
    .alma-post-card__pinned span {
        color: #1877f2;
        font-weight: 600 !important;
    }

    .alma-post-card__header-actions {
        align-items: center;
        gap: 8px;
        justify-content: flex-end;
        flex-shrink: 0;
    }

    .alma-post-card__header-pill,
    .alma-post-card__header-follow,
    .alma-post-card__menu-trigger {
        min-height: 34px;
        border-radius: 12px;
        border: none;
        background: #f3f3f3;
        color: #111111;
        box-shadow: none;
        font-size: 14px;
        font-weight: 600;
    }

    .alma-post-card__header-pill,
    .alma-post-card__header-follow {
        padding: 0 14px;
    }

    .alma-post-card__header-pill {
        cursor: pointer;
        appearance: none;
    }

    .alma-post-card__summary-ai-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .alma-post-card__summary-ai-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 38px;
        padding: 0 12px 0 8px;
        border-radius: 999px;
        border: 1px solid #d1d5db;
        background: #f3f4f6;
        color: #111827;
        box-shadow: none;
        transition: transform 0.16s ease, background-color 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease, color 0.16s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .alma-post-card__summary-ai-btn:hover {
        border-color: #d1d5db;
        background: #e5e7eb;
        box-shadow: none;
    }

    .alma-post-card__summary-ai-btn:active {
        transform: translateY(1px);
    }

    .alma-post-card__summary-ai-btn.is-clicked {
        box-shadow: 0 0 0 4px rgba(229, 231, 235, 0.92);
    }

    .alma-post-card__summary-ai-btn > [data-post-summary-label],
    .alma-post-card__summary-ai-btn-inner,
    .alma-post-card__summary-ai-spark,
    .alma-post-card__summary-ai-pulse-ring {
        display: none !important;
    }

    .alma-post-card__summary-ai-chip,
    .alma-post-card__summary-ai-icon-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 999px;
        flex-shrink: 0;
        background: #d1d5db;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.35);
    }

    .alma-post-card__summary-ai-icon {
        display: block;
        width: 14px;
        height: 14px;
        padding: 0;
        box-sizing: border-box;
        overflow: visible;
        stroke: #ffffff;
        fill: none;
        stroke-width: 1.9;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .alma-post-card__summary-ai-copy {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .alma-post-card__summary-ai-kicker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 20px;
        padding: 0 7px;
        border-radius: 999px;
        background: #e5e7eb;
        color: #4b5563;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .alma-post-card__summary-ai-label {
        font-size: 13px;
        line-height: 1;
        font-weight: 700;
        color: inherit;
        white-space: nowrap;
    }

    .alma-post-card__summary-ai-chevron {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 14px;
        height: 14px;
        color: #64748b;
        flex-shrink: 0;
    }

    .alma-post-card__summary-ai-chevron iconify-icon {
        font-size: 14px;
        transition: transform 0.18s ease;
    }

    .alma-post-card__summary-ai-btn[aria-expanded="true"] .alma-post-card__summary-ai-chevron iconify-icon {
        transform: rotate(180deg);
    }

    .alma-post-card__header-pill iconify-icon,
    .alma-post-card__header-pill svg,
    .alma-post-card__menu-trigger iconify-icon {
        font-size: 16px;
        color: currentColor;
    }

    .alma-post-card__header-pill svg {
        width: 16px;
        height: 16px;
        stroke: currentColor;
        stroke-width: 1.9;
        fill: none;
        flex-shrink: 0;
    }

    .alma-post-card__header-pill.is-active {
        background: #e5e7eb;
        color: #374151;
    }

    .alma-post-card__summary-ai-btn.is-active {
        background: #e5e7eb;
        border-color: #d1d5db;
        box-shadow: 0 0 0 4px rgba(229, 231, 235, 0.92);
        color: #111827;
    }

    .alma-post-card__summary-ai-btn.is-active .alma-post-card__summary-ai-kicker {
        background: #d1d5db;
        color: #4b5563;
    }

    .alma-post-card__header-follow.is-active {
        background: #e5e7eb;
        color: #374151;
    }

    .alma-post-card__content {
        gap: 0;
    }

    .alma-post-card__summary-panel {
        display: none;
        border-radius: 16px;
        background: #f9fafb;
        border: 1px solid rgba(226, 232, 240, 0.92);
        padding: 14px 15px;
        margin-bottom: 12px;
    }

    .alma-post-card__summary-panel.is-open {
        display: block;
    }

    .alma-post-card__summary-kicker {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
        padding: 0 10px;
        min-height: 24px;
        border-radius: 999px;
        background: #e5e7eb;
        font-size: 11px;
        line-height: 1;
        color: #4b5563;
        font-weight: 700 !important;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .alma-post-card__summary-kicker iconify-icon {
        font-size: 12px;
        color: currentColor;
    }

    .alma-post-card__summary-text {
        margin: 0;
        font-size: 14.5px;
        line-height: 1.65;
        color: #1f2937;
        white-space: pre-line;
    }

    @keyframes almaSummaryPulsePop {
        0% { transform: scale(1); }
        35% { transform: scale(1.08); }
        70% { transform: scale(0.98); }
        100% { transform: scale(1); }
    }

    @keyframes almaSummaryWaveBurst {
        0% {
            transform: translate(-50%, -50%) scale(0);
            opacity: .82;
        }
        70% {
            transform: translate(-50%, -50%) scale(1);
            opacity: .18;
        }
        100% {
            transform: translate(-50%, -50%) scale(1.15);
            opacity: 0;
        }
    }

    @keyframes almaSummaryOuterGlow {
        0% {
            opacity: 0;
            transform: scale(.78);
        }
        25% {
            opacity: .95;
        }
        100% {
            opacity: 0;
            transform: scale(1.12);
        }
    }

    @keyframes almaSummaryIconSpinFlash {
        0% {
            transform: scale(1) rotate(0deg);
            filter: brightness(1);
        }
        35% {
            transform: scale(1.18) rotate(8deg);
            filter: brightness(1.18);
        }
        100% {
            transform: scale(1) rotate(0deg);
            filter: brightness(1);
        }
    }

    @keyframes almaSummaryOrbitFloat {
        0% { transform: rotate(0deg); opacity: 0.72; }
        50% { opacity: 1; }
        100% { transform: rotate(360deg); opacity: 0.72; }
    }

    @keyframes almaSummaryCoreGlow {
        0%, 100% { transform: scale(1); filter: brightness(1); }
        50% { transform: scale(1.06); filter: brightness(1.14); }
    }

    @keyframes almaSummaryMiniTwinkle {
        0%, 100% { transform: scale(0.92); opacity: 0.5; }
        50% { transform: scale(1.08); opacity: 1; }
    }

    @keyframes almaSummaryDotOrbit {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes almaSummaryCoreBurst {
        0% { transform: scale(1) rotate(0deg); filter: brightness(1); }
        35% { transform: scale(1.18) rotate(10deg); filter: brightness(1.24); }
        100% { transform: scale(1) rotate(0deg); filter: brightness(1); }
    }

    @keyframes almaSummaryOrbitBurst {
        0% { transform: scale(1) rotate(0deg); opacity: 0.8; }
        35% { transform: scale(1.12) rotate(18deg); opacity: 1; }
        100% { transform: scale(1) rotate(0deg); opacity: 0.8; }
    }

    @keyframes almaSummaryDotBurst {
        0% { transform: scale(1) rotate(0deg); opacity: 0.85; }
        35% { transform: scale(1.28) rotate(20deg); opacity: 1; }
        100% { transform: scale(1) rotate(0deg); opacity: 0.85; }
    }

    @keyframes almaSummaryTextFlash {
        0% {
            opacity: 1;
            transform: translateX(0);
        }
        50% {
            opacity: .72;
            transform: translateX(2px);
        }
        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes almaSummaryShinePass {
        0% {
            opacity: 0;
            transform: rotate(18deg) translateX(0);
        }
        20% {
            opacity: 1;
        }
        100% {
            opacity: 0;
            transform: rotate(18deg) translateX(180px);
        }
    }

    @keyframes almaSummaryParticle1 {
        0% { opacity: 0; transform: translate(0,0) scale(0); }
        20% { opacity: 1; transform: translate(-2px,-4px) scale(1); }
        100% { opacity: 0; transform: translate(-18px,-16px) scale(.4); }
    }

    @keyframes almaSummaryParticle2 {
        0% { opacity: 0; transform: translate(0,0) scale(0); }
        18% { opacity: 1; transform: translate(2px,-2px) scale(1); }
        100% { opacity: 0; transform: translate(16px,-18px) scale(.45); }
    }

    @keyframes almaSummaryParticle3 {
        0% { opacity: 0; transform: translate(0,0) scale(0); }
        20% { opacity: 1; transform: translate(-2px,2px) scale(1); }
        100% { opacity: 0; transform: translate(-16px,16px) scale(.42); }
    }

    @keyframes almaSummaryParticle4 {
        0% { opacity: 0; transform: translate(0,0) scale(0); }
        22% { opacity: 1; transform: translate(2px,2px) scale(1); }
        100% { opacity: 0; transform: translate(18px,14px) scale(.45); }
    }

    .alma-post-card__title,
    .alma-post-card__title.is-hero {
        margin: 0 0 10px;
        font-size: 21px;
        line-height: 1.32;
        letter-spacing: -0.2px;
        color: #111111;
        font-weight: 700 !important;
    }

    .alma-post-card__excerpt {
        margin: 0 0 18px;
        font-size: 15px;
        line-height: 1.58;
        color: #222222;
        -webkit-line-clamp: 2;
    }

    .alma-post-card__media-link {
        border-radius: 14px;
        background: #dddddd;
        margin-bottom: 18px;
    }

    .alma-post-card__image {
        display: block;
        width: 100%;
        height: auto;
        max-width: 100%;
        margin-inline: 0;
        aspect-ratio: 3840 / 2160;
        object-fit: cover;
        border-radius: 14px;
        background: #d8dbe1;
    }

    .alma-post-card__media-link:hover .alma-post-card__image,
    .alma-post-card [data-rx-panel] button,
    .alma-post-card [data-rx-panel] button:hover {
        transform: none !important;
    }

    .alma-post-card__inline-expand {
        margin-top: 0;
        margin-bottom: 18px;
        align-items: flex-start;
    }

    .alma-post-card__inline-toggle {
        min-height: 0;
        padding: 0;
        padding-inline: 0;
        border: none;
        border-radius: 0;
        background: transparent !important;
        box-shadow: none !important;
        appearance: none;
        -webkit-appearance: none;
        color: #6b7280;
        font-size: 16px;
        line-height: 1.35;
        font-weight: 500;
        gap: 3px;
        margin-left: 0;
        align-self: flex-start;
    }

    .alma-post-card__inline-toggle:hover,
    .alma-post-card__inline-toggle:focus,
    .alma-post-card__inline-toggle:focus-visible,
    .alma-post-card__inline-toggle:active {
        background: transparent !important;
        box-shadow: none !important;
        border-color: transparent !important;
        outline: none !important;
    }

    .alma-post-card__inline-toggle iconify-icon {
        font-size: 14px;
        color: currentColor;
    }

    .alma-post-card__inline-more {
        padding-top: 10px;
    }

    .alma-post-card__inline-text {
        padding: 0;
        border: none;
        background: transparent;
        font-size: 15px;
        line-height: 1.62;
        color: #1c1e21;
    }

    .alma-post-card__footer {
        gap: 0;
    }

    .alma-post-card__reactions {
        gap: 14px;
        flex-wrap: wrap;
        padding-top: 0;
        margin-bottom: 14px;
    }

    .alma-post-card__reactions form,
    .alma-post-card__header-actions form {
        margin: 0;
    }

    .alma-post-card__reaction-pill {
        min-height: 0;
        padding: 0;
        border: none;
        background: transparent;
        color: #5f5f5f;
        gap: 0;
        font-size: 15px;
        font-weight: 500;
    }

    .alma-post-card__reaction-pill:hover {
        background: transparent;
        border-color: transparent;
        color: #050505;
    }

    .alma-post-card__reaction-pill-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 24px;
        gap: 4px;
        padding: 0 8px;
        border-radius: 999px;
        background: #f0f0f0;
        font-size: 15px;
        line-height: 1;
    }

    .alma-post-card__reaction-pill-icon img,
    .alma-post-card__reaction-pill-icon iconify-icon {
        width: 15px;
        height: 15px;
    }

    .alma-post-card__reaction-pill-icon .alma-post-card__reaction-pill-count {
        font-size: 13px;
        font-weight: 600;
        color: #5f5f5f;
        line-height: 1;
    }

    .alma-post-card__reaction-pill > .alma-post-card__reaction-pill-count {
        display: none;
    }

    .alma-post-card__reaction-more {
        min-height: 0;
        padding: 0;
        border-radius: 0;
        background: transparent;
        color: #5f5f5f;
        font-size: 15px;
        font-weight: 500;
    }

    .alma-post-card__reaction-picker {
        width: 30px;
        height: 30px;
        min-height: 30px;
        padding: 0;
        border: none;
        border-radius: 999px;
        background: transparent;
        color: #777777;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .alma-post-card__reaction-picker:hover {
        background: transparent;
        color: #777777;
    }

    .alma-post-card__engagement {
        background: #ffffff;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 8px;
        margin-bottom: 16px;
    }

    .alma-post-card__engagement-main {
        width: auto;
        gap: 22px;
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .alma-post-card__metric-button {
        min-height: 0;
        padding: 0;
        border: none;
        background: transparent;
        color: #5b5b5b;
        gap: 7px;
        font-size: 15px;
        font-weight: 500;
    }

    .alma-post-card__metric-button:hover {
        background: transparent;
        color: #050505;
    }

    .alma-post-card__metric-button iconify-icon {
        font-size: 20px;
    }

    .alma-post-card__metric-button > svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .alma-post-card__metric-button span {
        font-size: 15px;
        font-weight: 500;
    }

    .alma-post-card__views {
        color: #5b5b5b;
        gap: 7px;
        font-size: 15px;
        font-weight: 500;
    }

    .alma-post-card__views iconify-icon {
        font-size: 20px;
    }

    .alma-post-card__comments-strip {
        gap: 12px;
        padding-top: 2px;
        border-top: none;
    }

    .alma-post-card__comments-meta {
        align-items: flex-start;
        gap: 12px;
    }

    .alma-post-card__comment-avatar,
    .alma-post-card__comment-avatar--fallback {
        width: 28px;
        height: 28px;
        border: none;
        background: #d8d8d8;
    }

    .alma-post-card__comment-avatar--fallback {
        color: #1c1e21;
        font-size: 11px;
        font-weight: 700 !important;
    }

    .alma-post-card__comment-avatar--fallback iconify-icon {
        font-size: 15px;
    }

    .alma-post-card__comment-preview {
        padding-top: 2px;
    }

    .alma-post-card__comments-count {
        max-width: 560px;
        font-size: 14px;
        line-height: 1.45;
        color: #111111;
        font-weight: 400 !important;
        -webkit-line-clamp: 2;
    }

    .alma-post-card__comments-chevron {
        display: none;
    }

    .alma-post-card__menu-panel {
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14);
    }

    .alma-post-card__menu-item {
        color: #1c1e21;
        font-weight: 500 !important;
    }

    .alma-post-card__menu-item iconify-icon {
        color: #65676b;
    }

    .alma-post-card__menu-item:hover {
        background: #f2f3f5;
    }

    html.dark .alma-post-card,
    html.dark .alma-post-card__menu-panel {
        background: #ffffff !important;
        border-color: rgba(0, 0, 0, 0.06) !important;
        color: #050505 !important;
    }

    html.dark .alma-post-card {
        box-shadow: none !important;
    }

    html.dark .alma-post-card__menu-panel {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14) !important;
    }

    html.dark .alma-post-card iconify-icon {
        color: currentColor !important;
        fill: currentColor;
        stroke: currentColor;
    }

    html.dark .alma-post-card__author,
    html.dark .alma-post-card__title,
    html.dark .alma-post-card__comments-count,
    html.dark .alma-post-card__menu-item {
        color: #050505 !important;
    }

    html.dark .alma-post-card__excerpt,
    html.dark .alma-post-card__inline-text,
    html.dark .alma-post-card__summary-text {
        color: #1c1e21 !important;
    }

    html.dark .alma-post-card__submeta,
    html.dark .alma-post-card__submeta a,
    html.dark .alma-post-card__submeta time,
    html.dark .alma-post-card__submeta span,
    html.dark .alma-post-card__metric-button,
    html.dark .alma-post-card__views,
    html.dark .alma-post-card__reaction-pill,
    html.dark .alma-post-card__reaction-pill-count,
    html.dark .alma-post-card__reaction-more {
        color: #65676b !important;
    }

    html.dark .alma-post-card__category {
        color: #0b5ca8 !important;
    }

    html.dark .alma-post-card__engagement {
        background: #ffffff !important;
    }

    html.dark .alma-post-card__header-pill,
    html.dark .alma-post-card__header-follow,
    html.dark .alma-post-card__menu-trigger,
    html.dark .alma-post-card__reaction-picker,
    html.dark .alma-post-card__comment-avatar--fallback {
        background: #f2f3f5 !important;
        color: #050505 !important;
        border-color: transparent !important;
    }

    html.dark .alma-post-card__header-follow.is-active {
        background: #e7f3ff !important;
        color: #1877f2 !important;
    }

    html.dark .alma-post-card__header-pill.is-active {
        background: #e7f3ff !important;
        color: #1877f2 !important;
    }

    html.dark .alma-post-card__summary-ai-btn {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
        border-color: rgba(203, 213, 225, 0.88) !important;
        color: #111827 !important;
    }

    html.dark .alma-post-card__summary-ai-btn.is-active {
        background: #eef6ff !important;
        border-color: rgba(96, 165, 250, 0.36) !important;
        color: #0f172a !important;
    }

    html.dark .alma-post-card__summary-ai-kicker {
        background: #eef2ff !important;
        color: #4338ca !important;
    }

    html.dark .alma-post-card__summary-panel {
        background: #f6f7f9 !important;
        border-color: rgba(0, 0, 0, 0.06) !important;
    }

    html.dark .alma-post-card__summary-kicker {
        color: #65676b !important;
    }

    html.dark .alma-post-card__menu-item:hover {
        background: #f2f3f5 !important;
    }

    html.dark .alma-post-card__avatar--fallback {
        background: #e4e6eb !important;
        color: #1c1e21 !important;
    }

    html.dark .alma-post-card__avatar-badge {
        background: #ef4444 !important;
        border-color: #f7f7f7 !important;
        color: #ffffff !important;
    }

    html.dark .alma-post-card__verified {
        color: inherit !important;
    }

    @media (max-width: 768px) {
        .alma-post-card {
            border-radius: 12px;
            padding: 14px;
            gap: 0;
        }

        .alma-post-card__header {
            position: relative;
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            margin-bottom: 12px;
        }

        .alma-post-card__identity {
            width: 100%;
            padding-right: 92px;
        }

        .alma-post-card__header-actions {
            position: absolute;
            top: -2px;
            right: 0;
            justify-content: flex-end;
        }

        .alma-post-card__header-pill[data-post-summary-toggle] {
            min-width: 40px;
            min-height: 40px;
            padding: 0;
            border-radius: 999px;
            justify-content: center;
        }

        .alma-post-card__summary-ai-copy,
        .alma-post-card__summary-ai-chevron {
            display: none;
        }

        .alma-post-card__summary-ai-chip,
        .alma-post-card__summary-ai-icon-wrap {
            width: 22px;
            height: 22px;
        }

        .alma-post-card__summary-ai-icon {
            width: 14px;
            height: 14px;
        }

        .alma-post-card__author {
            font-size: 14px;
        }

        .alma-post-card__submeta,
        .alma-post-card__submeta a,
        .alma-post-card__submeta time,
        .alma-post-card__submeta span,
        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            font-size: 13px;
        }

        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            min-height: 34px;
            padding: 0 14px;
        }

        .alma-post-card__title,
        .alma-post-card__title.is-hero {
            font-size: 19px;
            line-height: 1.32;
        }

        .alma-post-card__excerpt,
        .alma-post-card__summary-text,
        .alma-post-card__inline-text {
            font-size: 14.5px;
            line-height: 1.58;
        }

        .alma-post-card__comments-count {
            font-size: 14px;
            line-height: 1.42;
        }

        .alma-post-card__image {
            height: auto;
        }

        .alma-post-card__engagement {
            align-items: center;
            padding: 12px 6px;
        }

        .alma-post-card__engagement-main,
        .alma-post-card__reactions {
            gap: 14px;
        }

        .alma-post-card__views,
        .alma-post-card__metric-button,
        .alma-post-card__reaction-pill {
            font-size: 13px;
        }

        .alma-post-card__metric-button span,
        .alma-post-card__views span,
        .alma-post-card__reaction-pill-count {
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .alma-post-card__author {
            font-size: 14px;
            line-height: 1.24;
        }

        .alma-post-card__submeta,
        .alma-post-card__submeta a,
        .alma-post-card__submeta time,
        .alma-post-card__submeta span,
        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            font-size: 13px;
            line-height: 1.25;
        }

        .alma-post-card__header-actions {
            gap: 6px;
        }

        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            min-height: 34px;
            padding: 0 14px;
        }

        .alma-post-card__identity {
            padding-right: 84px;
        }

        .alma-post-card__header-pill[data-post-summary-toggle] {
            min-width: 38px;
            min-height: 38px;
            padding: 0;
        }

        .alma-post-card__summary-ai-chip,
        .alma-post-card__summary-ai-icon-wrap {
            width: 20px;
            height: 20px;
        }

        .alma-post-card__summary-ai-icon {
            width: 12px;
            height: 12px;
        }

        .alma-post-card__title,
        .alma-post-card__title.is-hero {
            font-size: 18px;
            line-height: 1.3;
        }

        .alma-post-card__excerpt,
        .alma-post-card__summary-text,
        .alma-post-card__inline-text {
            font-size: 14px;
            line-height: 1.56;
        }

        .alma-post-card__comments-count,
        .alma-post-card__views,
        .alma-post-card__metric-button span,
        .alma-post-card__reaction-pill,
        .alma-post-card__reaction-pill-count {
            font-size: 13px;
        }

        .alma-post-card__metric-button iconify-icon,
        .alma-post-card__views iconify-icon {
            font-size: 18px;
        }

        .alma-post-card__reactions,
        .alma-post-card__engagement-main {
            gap: 12px;
        }

        .route-discover .main-grid {
            padding-left: max(var(--alma-page-inline-tight), env(safe-area-inset-left)) !important;
            padding-right: max(var(--alma-page-inline-tight), env(safe-area-inset-right)) !important;
        }

        .route-discover .alma-post-card {
            width: 100%;
            max-width: none;
            margin-left: 0;
            margin-right: 0;
            border-radius: 14px;
        }

        .route-discover .alma-discover-recommendations__list {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
    }

    

    

    /* Exact profile-row lock based on the provided reference HTML */
    .alma-post-card__identity--reference {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        min-height: 44px !important;
    }

    .alma-post-card__avatar-link--reference {
        position: relative !important;
        width: 48px !important;
        height: 48px !important;
        flex: 0 0 48px !important;
        margin: 0 !important;
        overflow: visible !important;
    }

    .alma-post-card__avatar-link--reference > .alma-post-card__avatar-anchor--reference {
        display: block !important;
        width: 48px !important;
        height: 48px !important;
        border-radius: 999px !important;
        overflow: hidden !important;
        text-decoration: none !important;
    }

    .alma-post-card__avatar--reference {
        display: block !important;
        width: 48px !important;
        height: 48px !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        border-radius: 999px !important;
        object-fit: cover !important;
        box-shadow: none !important;
    }

    .alma-post-card__avatar--fallback.alma-post-card__avatar--reference {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #f1f5f9 !important;
        color: #111111 !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        text-decoration: none !important;
    }

    .alma-post-card__avatar-badge--reference {
        position: absolute !important;
        left: 28px !important;
        right: auto !important;
        bottom: -2px !important;
        width: 28px !important;
        height: 28px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
        border: 2px solid #ffffff !important;
        border-radius: 999px !important;
        background: #ffffff !important;
        color: #7a7a7a !important;
        text-decoration: none !important;
        overflow: hidden !important;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.04) !important;
        z-index: 2 !important;
    }

    .alma-post-card__avatar-badge-image--reference {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: inherit !important;
        object-fit: cover !important;
    }

    .alma-post-card__avatar-badge-text--reference {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: inherit !important;
        background: #ffffff !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 9px !important;
        line-height: 1 !important;
        font-weight: 700 !important;
        letter-spacing: 0 !important;
        color: #7a7a7a !important;
        text-transform: uppercase !important;
    }

    .alma-post-card__meta--reference {
        min-width: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        gap: 0 !important;
        padding-top: 0 !important;
        font-family: Arial, Helvetica, sans-serif !important;
    }

    .alma-post-card__author-row--reference {
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        min-width: 0 !important;
        margin: 0 0 4px 0 !important;
        line-height: 1.2 !important;
    }

    .alma-post-card__author--reference {
        display: inline-block !important;
        max-width: 250px !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 15px !important;
        line-height: 1.2 !important;
        font-weight: 700 !important;
        color: #111111 !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .alma-post-card__submeta--reference {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        min-width: 0 !important;
        flex-wrap: nowrap !important;
        white-space: nowrap !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 14px !important;
        line-height: 1.2 !important;
        font-weight: 500 !important;
        color: #7a7a7a !important;
    }

    .alma-post-card__submeta--reference a,
    .alma-post-card__submeta--reference time,
    .alma-post-card__submeta--reference span {
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 14px !important;
        line-height: 1.2 !important;
        font-weight: 500 !important;
        color: #475569 !important;
    }

    .alma-post-card__category--reference {
        color: #475569 !important;
        font-weight: 500 !important;
        text-decoration: none !important;
    }

    .alma-post-card__time--reference {
        color: #475569 !important;
        font-weight: 500 !important;
    }

    .alma-post-card__dot--reference {
        display: none !important;
    }

    .alma-post-card__verified--reference {
        width: 14px !important;
        height: 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 14px !important;
    }

    .alma-post-card__verified--reference svg {
        display: block !important;
        width: 14px !important;
        height: 14px !important;
    }

    .site-header .site-primary-btn,
    .site-header .site-icon-btn,
    .site-header .site-search-trigger,
    .site-header .site-search-close,
    .site-header .site-notifications-more,
    [data-theme-toggle] {
        background: #f4f4f5 !important;
        border-color: rgba(148, 163, 184, 0.28) !important;
        color: #52525b !important;
        box-shadow: none !important;
    }

    .site-header .site-primary-btn:hover,
    .site-header .site-icon-btn:hover,
    .site-header .site-search-trigger:hover,
    .site-header .site-search-close:hover,
    .site-header .site-notifications-more:hover,
    [data-theme-toggle]:hover {
        background: #ffffff !important;
        border-color: rgba(148, 163, 184, 0.38) !important;
        color: #18181b !important;
        transform: none !important;
    }

    .site-header-write-btn {
        background: #18181b !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    .site-header .site-header-write-btn,
    .site-header a.site-header-write-btn,
    .site-header .site-header-write-btn:visited {
        background: #18181b !important;
        color: #ffffff !important;
    }

    .site-header-write-btn:hover {
        background: #27272a !important;
        border: none !important;
        color: #ffffff !important;
        transform: none !important;
    }

    button,
    [type="button"],
    [type="submit"],
    [type="reset"],
    .site-primary-btn,
    .site-icon-btn,
    .site-header-write-btn,
    .alma-post-card__header-pill,
    .alma-post-card__header-follow,
    .alma-post-card__reaction-pill,
    .alma-post-card__reaction-picker,
    .alma-post-card__metric-button,
    .alma-post-card__menu-trigger {
        border: none !important;
    }

    .alma-post-card__header-pill,
    .alma-post-card__header-follow,
    .alma-post-card__reaction-pill,
    .alma-post-card__reaction-picker,
    .alma-post-card__reaction-more,
    .alma-post-card__metric-button,
    .alma-post-card__menu-trigger {
        background: transparent !important;
        color: #71717a !important;
        border: none !important;
        box-shadow: none !important;
    }

    .alma-post-card__header-pill:hover,
    .alma-post-card__header-follow:hover,
    .alma-post-card__reaction-pill:hover,
    .alma-post-card__reaction-picker:hover,
    .alma-post-card__reaction-more:hover,
    .alma-post-card__metric-button:hover,
    .alma-post-card__menu-trigger:hover {
        background: #f4f4f5 !important;
        color: #18181b !important;
        border: none !important;
    }

    .alma-post-card__summary-ai-wrap,
    .alma-post-card__summary-panel {
        display: none !important;
    }

    .alma-post-card__metric-button.is-copied,
    .alma-post-card__header-follow.is-active {
        color: #18181b !important;
        background: #ffffff !important;
        border: none !important;
    }

    .alma-post-card__metric-button.is-bookmarked,
    .alma-post-card__metric-button.is-bookmarked:hover,
    .alma-post-card__metric-button.is-bookmarked:focus,
    .alma-post-card__metric-button.is-bookmarked:focus-visible,
    .alma-post-card__metric-button.is-bookmarked:active {
        color: #059669 !important;
        background: transparent !important;
    }

    .alma-post-card__menu-trigger,
    .post-show-profile__menu-trigger,
    [data-post-card-shell] .menu-btn,
    [data-post-card-shell] .menu-button {
        border-radius: 0 !important;
        background: transparent !important;
        color: #111111 !important;
        box-shadow: none !important;
        transform: none !important;
        transition: none !important;
    }

    .alma-post-card__menu-trigger:hover,
    .alma-post-card__menu-trigger:focus-visible,
    .post-show-profile__menu-trigger:hover,
    .post-show-profile__menu-trigger:focus-visible,
    [data-post-card-shell] .menu-btn:hover,
    [data-post-card-shell] .menu-btn:focus-visible,
    [data-post-card-shell] .menu-button:hover,
    [data-post-card-shell] .menu-button:focus-visible {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }

    .alma-post-card__menu-panel,
    .post-show-profile__menu-panel,
    [data-post-card-shell] .post-card__menu {
        border: 0 !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        box-shadow: none !important;
        color: #111111 !important;
    }

    .alma-post-card__menu-item,
    .post-show-profile__menu-item,
    .post-show-profile__menu-button,
    [data-post-card-shell] .post-card__menu-item {
        border-radius: 6px !important;
        background: transparent !important;
        color: #111111 !important;
        transition: none !important;
    }

    .alma-post-card__menu-item:hover,
    .alma-post-card__menu-item:focus-visible,
    .post-show-profile__menu-item:hover,
    .post-show-profile__menu-item:focus-visible,
    .post-show-profile__menu-button:hover,
    .post-show-profile__menu-button:focus-visible,
    [data-post-card-shell] .post-card__menu-item:hover,
    [data-post-card-shell] .post-card__menu-item:focus-visible {
        background: #f4f4f5 !important;
        color: #111111 !important;
        outline: none !important;
    }

    .alma-post-card__reaction-picker,
    .alma-post-card__metric-button,
    .alma-post-card__reaction-pill,
    .alma-post-card__reaction-more,
    [data-post-card-shell] .action-btn,
    [data-post-card-shell] .action-stat,
    [data-post-card-shell] .action-chip,
    [data-post-card-shell] .reaction-add,
    [data-post-card-shell] .smiley-btn,
    [data-post-card-shell] .post-card__action-button,
    [data-post-card-shell] .post-card__action-link,
    #comments .show-comment-card__vote-cluster,
    #comments .show-comment-card__thread-toggle,
    #comments .show-comment-card__reply,
    #comments .show-comment-card__action-icon,
    #comments .show-comment-form__tool,
    #comments .show-comment-form__send {
        background: transparent !important;
        color: #111111 !important;
        box-shadow: none !important;
        transform: none !important;
        transition: none !important;
    }

    .alma-post-card__reaction-picker:hover,
    .alma-post-card__reaction-picker:focus-visible,
    .alma-post-card__metric-button:hover,
    .alma-post-card__metric-button:focus-visible,
    .alma-post-card__reaction-pill:hover,
    .alma-post-card__reaction-pill:focus-visible,
    .alma-post-card__reaction-more:hover,
    .alma-post-card__reaction-more:focus-visible,
    [data-post-card-shell] .action-btn:hover,
    [data-post-card-shell] .action-btn:focus-visible,
    [data-post-card-shell] .action-stat:hover,
    [data-post-card-shell] .action-stat:focus-visible,
    [data-post-card-shell] .action-chip:hover,
    [data-post-card-shell] .action-chip:focus-visible,
    [data-post-card-shell] .reaction-add:hover,
    [data-post-card-shell] .reaction-add:focus-visible,
    [data-post-card-shell] .smiley-btn:hover,
    [data-post-card-shell] .smiley-btn:focus-visible,
    [data-post-card-shell] .post-card__action-button:hover,
    [data-post-card-shell] .post-card__action-button:focus-visible,
    [data-post-card-shell] .post-card__action-link:hover,
    [data-post-card-shell] .post-card__action-link:focus-visible,
    #comments .show-comment-card__vote-cluster:hover,
    #comments .show-comment-card__vote-cluster:focus-within,
    #comments .show-comment-card__thread-toggle:hover,
    #comments .show-comment-card__thread-toggle:focus-visible,
    #comments .show-comment-card__reply:hover,
    #comments .show-comment-card__reply:focus-visible,
    #comments .show-comment-card__action-icon:hover,
    #comments .show-comment-card__action-icon:focus-visible,
    #comments .show-comment-form__tool:hover,
    #comments .show-comment-form__tool:focus-visible,
    #comments .show-comment-form__send:hover,
    #comments .show-comment-form__send:focus-visible {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }

    .route-home .main-grid {
        align-items: start;
        padding-top: 0;
    }

    .route-home .layout-main {
        gap: 22px;
    }

    .route-home .alma-post-card {
        padding: 22px 24px 20px !important;
        border-radius: 18px !important;
        background: var(--site-surface) !important;
    }

    .route-home .sidebar-card {
        min-height: 178px;
        padding: 24px !important;
        border-radius: 18px !important;
        background: var(--site-surface) !important;
        box-shadow: none !important;
    }

    .route-home .alma-widget__header {
        margin-bottom: 34px;
    }

    .route-home .alma-widget__title {
        font-size: 17px;
        line-height: 1.35;
    }

    .route-home .alma-empty-state {
        min-height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--site-muted);
    }

    .route-home [data-post-card-shell] .post-card__menu,
    .route-home [data-post-card-shell] .post-card__reaction-menu,
    .route-home [data-post-card-reaction-menu] {
        background: #fff !important;
        border: 0 !important;
        border-radius: 4px !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
    }

    .route-home [data-post-card-shell] .post-card__reaction-menu:not([hidden]),
    .route-home [data-post-card-reaction-menu]:not([hidden]) {
        width: min(208px, calc(100vw - 32px)) !important;
        max-width: min(208px, calc(100vw - 32px)) !important;
        padding: 10px 12px 12px !important;
        gap: 10px 12px !important;
    }

    .route-home [data-post-card-shell] .post-card__menu-item,
    .route-home [data-post-card-shell] .post-card__reaction-option,
    .route-home [data-post-card-reaction-menu] .post-card__reaction-option {
        background: transparent !important;
        border-radius: 0 !important;
    }

    .route-home [data-post-card-shell] .post-card__reaction-form,
    .route-home [data-post-card-reaction-menu] .post-card__reaction-form,
    .route-home [data-post-card-reaction-menu] > a,
    .route-home [data-post-card-shell] .post-card__reaction-menu > a {
        flex-basis: 34px !important;
        width: 34px !important;
    }

    .route-home [data-post-card-shell] .post-card__reaction-option,
    .route-home [data-post-card-reaction-menu] .post-card__reaction-option {
        height: 34px !important;
        padding: 0 !important;
    }

    body.alma-app :where(
        [aria-label*="Diger islemler"],
        [aria-label*="Diğer işlemler"],
        [aria-label*="More"],
        [aria-label*="more"],
        [data-post-card-menu-trigger],
        [data-comments-sort-toggle],
        [data-user-menu-btn],
        [data-logo-menu-btn],
        [data-profile-actions-open],
        [data-sort-toggle],
        [data-create-actions-menu] summary,
        [data-category-menu] summary,
        [data-sort-menu] summary,
        [data-message-settings-menu] summary,
        summary.profile-summary-toggle,
        .menu-btn,
        .menu-button,
        .site-notifications-more,
        .alma-post-card__menu-trigger,
        .post-show-profile__menu-trigger,
        .show-comments-sort__toggle,
        .profile-reference-menu-summary,
        .profile-reference-actions-trigger
    ) {
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
        transition: none !important;
    }

    body.alma-app :where(
        [aria-label*="Diger islemler"],
        [aria-label*="Diğer işlemler"],
        [aria-label*="More"],
        [aria-label*="more"],
        [data-post-card-menu-trigger],
        [data-comments-sort-toggle],
        [data-user-menu-btn],
        [data-logo-menu-btn],
        [data-profile-actions-open],
        [data-sort-toggle],
        [data-create-actions-menu] summary,
        [data-category-menu] summary,
        [data-sort-menu] summary,
        [data-message-settings-menu] summary,
        summary.profile-summary-toggle,
        .menu-btn,
        .menu-button,
        .site-notifications-more,
        .alma-post-card__menu-trigger,
        .post-show-profile__menu-trigger,
        .show-comments-sort__toggle,
        .profile-reference-menu-summary,
        .profile-reference-actions-trigger
    ):where(:hover, :focus-visible, [aria-expanded="true"], [open] > summary) {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }

    body.alma-app :where(
        [data-post-card-menu],
        [data-post-card-reaction-menu],
        [data-user-menu-panel],
        [data-logo-menu-panel],
        [data-notifications-actions-menu],
        [data-comments-sort-menu],
        [data-profile-menu-panel],
        [data-sort-list],
        [data-create-actions-menu] > div,
        [data-category-menu] > div,
        [data-sort-menu] > div,
        [data-message-settings-menu] > div,
        .site-menu-panel,
        .alma-post-card__menu-panel,
        .post-show-profile__menu-panel,
        .show-comments-sort__menu,
        .profile-reference-sort-panel,
        .profile-reference-actions-dropdown,
        .message-settings-menu__panel,
        .post-card__menu,
        .post-card__reaction-menu
    ) {
        border: 0 !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
    }

    body.alma-app :where(
        [data-post-card-menu] a,
        [data-post-card-menu] button,
        [data-post-card-reaction-menu] a,
        [data-post-card-reaction-menu] button,
        [data-user-menu-panel] a,
        [data-user-menu-panel] button,
        [data-logo-menu-panel] a,
        [data-logo-menu-panel] button,
        [data-notifications-actions-menu] a,
        [data-notifications-actions-menu] button,
        [data-comments-sort-menu] button,
        [data-profile-menu-panel] a,
        [data-profile-menu-panel] button,
        [data-sort-list] button,
        [data-create-actions-menu] > div a,
        [data-create-actions-menu] > div button,
        [data-category-menu] > div a,
        [data-category-menu] > div button,
        [data-sort-menu] > div a,
        [data-sort-menu] > div button,
        [data-message-settings-menu] > div a,
        [data-message-settings-menu] > div button,
        .site-menu-panel a,
        .site-menu-panel button,
        .alma-post-card__menu-item,
        .post-show-profile__menu-item,
        .post-show-profile__menu-button,
        .show-comments-sort__option,
        .profile-reference-sort-option,
        .profile-reference-actions-dropdown a,
        .profile-reference-actions-dropdown button,
        .message-settings-menu__panel a,
        .message-settings-menu__panel button,
        .post-card__menu-item,
        .post-card__reaction-option
    ) {
        border: 0 !important;
        border-radius: 6px !important;
        background: transparent !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
        transition: none !important;
    }

    body.alma-app :where(
        [data-post-card-menu] a,
        [data-post-card-menu] button,
        [data-post-card-reaction-menu] a,
        [data-post-card-reaction-menu] button,
        [data-user-menu-panel] a,
        [data-user-menu-panel] button,
        [data-logo-menu-panel] a,
        [data-logo-menu-panel] button,
        [data-notifications-actions-menu] a,
        [data-notifications-actions-menu] button,
        [data-comments-sort-menu] button,
        [data-profile-menu-panel] a,
        [data-profile-menu-panel] button,
        [data-sort-list] button,
        [data-create-actions-menu] > div a,
        [data-create-actions-menu] > div button,
        [data-category-menu] > div a,
        [data-category-menu] > div button,
        [data-sort-menu] > div a,
        [data-sort-menu] > div button,
        [data-message-settings-menu] > div a,
        [data-message-settings-menu] > div button,
        .site-menu-panel a,
        .site-menu-panel button,
        .alma-post-card__menu-item,
        .post-show-profile__menu-item,
        .post-show-profile__menu-button,
        .show-comments-sort__option,
        .profile-reference-sort-option,
        .profile-reference-actions-dropdown a,
        .profile-reference-actions-dropdown button,
        .message-settings-menu__panel a,
        .message-settings-menu__panel button,
        .post-card__menu-item,
        .post-card__reaction-option
    ):where(:hover, :focus-visible, .is-active) {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }

    body.alma-app :where(
        .site-icon-btn,
        .profile-reference-icon-btn,
        .alma-post-card__metric-button,
        .alma-post-card__reaction-picker,
        .alma-post-card__reaction-pill,
        .alma-post-card__reaction-more,
        .post-reaction-bookmark-btn,
        .rx-summary-pill,
        [data-share-btn],
        [data-share-close],
        [data-reaction-overflow-toggle],
        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .action-chip,
        [data-post-card-shell] .reaction-add,
        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .post-card__action-button,
        [data-post-card-shell] .post-card__action-link,
        #comments .show-comment-card__vote-cluster,
        #comments .show-comment-card__thread-toggle,
        #comments .show-comment-card__reply,
        #comments .show-comment-card__action-icon,
        #comments .show-comment-form__tool,
        #comments .show-comment-form__send
    ) {
        background: transparent !important;
        color: #111111 !important;
        box-shadow: none !important;
        transform: none !important;
        transition: none !important;
    }

    body.alma-app :where(
        .site-icon-btn,
        .profile-reference-icon-btn,
        .alma-post-card__metric-button,
        .alma-post-card__reaction-picker,
        .alma-post-card__reaction-pill,
        .alma-post-card__reaction-more,
        .post-reaction-bookmark-btn,
        .rx-summary-pill,
        [data-share-btn],
        [data-share-close],
        [data-reaction-overflow-toggle],
        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .action-chip,
        [data-post-card-shell] .reaction-add,
        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .post-card__action-button,
        [data-post-card-shell] .post-card__action-link,
        #comments .show-comment-card__vote-cluster,
        #comments .show-comment-card__thread-toggle,
        #comments .show-comment-card__reply,
        #comments .show-comment-card__action-icon,
        #comments .show-comment-form__tool,
        #comments .show-comment-form__send
    ):where(:hover, :focus-visible, :focus-within) {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }

    :root {
        --profile-shell-width: 656px;
        --layout-left-width: 220px;
        --layout-right-width: 260px;
        --layout-column-gap: 32px;
        --layout-shell-inline: 0px;
    }

    .main-grid {
        display: grid;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        align-items: start;
    }

    .main-grid--padded {
        padding-top: 22px;
    }

    .layout-main {
        max-width: var(--profile-shell-width);
        padding-left: 0;
        padding-right: 0;
    }

    
    .layout-side--right {
        position: sticky;
        top: calc(var(--site-header-height) + 14px);
        max-height: calc(100vh - var(--site-header-height) - 24px);
        overflow: auto;
    }

    

    .site-card,
    .sidebar-card,
    .alma-panel,
    .alma-post-card,
    [data-post-card-shell] {
        border-radius: 8px !important;
    }

    .sidebar-card {
        padding: 16px !important;
    }

    

    

    @media (min-width: 1181px) {
        .main-grid {
            grid-template-columns: var(--layout-left-width) minmax(0, var(--profile-shell-width)) var(--layout-right-width);
        }
    }

    @media (max-width: 1180px) and (min-width: 961px) {
        .main-grid {
            max-width: calc(var(--profile-shell-width) + var(--layout-right-width) + var(--layout-column-gap) + (var(--layout-shell-inline) * 2));
            grid-template-columns: minmax(0, var(--profile-shell-width)) var(--layout-right-width);
        }
    }

    @media (max-width: 960px) {
        .main-grid {
            max-width: var(--profile-shell-width);
            grid-template-columns: minmax(0, 1fr);
        }
    }

    body.alma-app {
        --profile-shell-width: 656px;
        --layout-left-width: 220px;
        --layout-right-width: 260px;
        --layout-column-gap: 32px;
        --layout-shell-inline: 0px;
        --layout-shell-max: 1150px;
        --alma-primary: #00a971;
        --alma-primary-strong: #00a971;
        --site-accent: #00a971;
        --primary: #00a971;
        --accent: #00a971;
        --site-bg: #f7f9fa;
        --background: #f7f9fa;
        --site-text: #0f1419;
        --foreground: #0f1419;
        --site-muted: #536471;
        --muted-foreground: #536471;
        background-color: #f7f9fa !important;
        color: #0f1419 !important;
        font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    }

    body.alma-app .site-header {
        background-color: #ffffff !important;
        background-image: none !important;
        box-shadow: none !important;
    }

    body.alma-app .site-header-shell,
    body.alma-app .main-grid {
        max-width: var(--layout-shell-max) !important;
    }

    body.alma-app .main-grid {
        grid-template-columns: var(--layout-left-width) minmax(0, var(--profile-shell-width)) var(--layout-right-width) !important;
        column-gap: var(--layout-column-gap) !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.alma-app .layout-main {
        width: 100% !important;
        max-width: var(--profile-shell-width) !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.alma-app .profile-reference-shell,
    body.alma-app .category-reference-shell,
    body.alma-app .layout-main > .mx-auto[class*="max-w-"],
    body.alma-app .layout-main > div > .mx-auto[class*="max-w-"],
    body.alma-app .layout-main > div > div > .mx-auto[class*="max-w-"],
    body.alma-app .layout-main .mx-auto.max-w-7xl,
    body.alma-app .layout-main .mx-auto.max-w-6xl,
    body.alma-app .layout-main .mx-auto.max-w-5xl,
    body.alma-app .layout-main .mx-auto.max-w-4xl,
    body.alma-app .layout-main .mx-auto.max-w-3xl,
    body.alma-app .layout-main .mx-auto.max-w-2xl {
        width: 100% !important;
        max-width: var(--profile-shell-width) !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    body.alma-app .site-primary-btn,
    body.alma-app .site-header .site-header-write-btn,
    body.alma-app .profile-reference-btn-primary,
    body.alma-app .alma-button {
        background-color: #00a971 !important;
        color: #ffffff !important;
        border-color: #00a971 !important;
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    body.alma-app .site-primary-btn:hover,
    body.alma-app .site-header .site-header-write-btn:hover,
    body.alma-app .profile-reference-btn-primary:hover,
    body.alma-app .alma-button:hover {
        background-color: #00a971 !important;
        color: #ffffff !important;
        border-color: #00a971 !important;
    }

    

    

    body.alma-app .site-header [data-user-menu] .site-icon-btn {
        width: 40px !important;
        height: 40px !important;
        border-radius: 999px !important;
        overflow: hidden !important;
        padding: 0 !important;
    }

    body.alma-app .site-header [data-user-menu] .site-avatar-fallback {
        width: 100% !important;
        height: 100% !important;
        border-radius: 999px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    body.alma-app .site-header [data-user-menu] img {
        border-radius: 999px !important;
    }

    @media (max-width: 1180px) {
        body.alma-app .main-grid {
            max-width: calc(var(--profile-shell-width) + var(--layout-right-width) + var(--layout-column-gap)) !important;
            grid-template-columns: minmax(0, var(--profile-shell-width)) var(--layout-right-width) !important;
        }
    }

    @media (max-width: 960px) {
        body.alma-app .main-grid {
            max-width: var(--profile-shell-width) !important;
            grid-template-columns: minmax(0, 1fr) !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    }

    body.alma-app .main-grid,
    body.alma-app .main-grid.main-grid--padded,
    body.alma-app .main-grid.main-grid--no-pad {
        padding-top: 0 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Ografi ana layout - sağ sütun genişletilmiş düzen
    |--------------------------------------------------------------------------
    */

    body.alma-app {
        --profile-shell-width: 656px;
        --layout-left-width: 220px;
        --layout-right-width: 300px;
        --layout-column-gap: 28px;
        --layout-shell-inline: 0px;
        --layout-shell-max: 1182px;

        --alma-primary: #00a971;
        --alma-primary-strong: #00a971;
        --site-accent: #00a971;
        --primary: #00a971;
        --accent: #00a971;

        --site-bg: #f7f9fa;
        --background: #f7f9fa;
        --site-text: #0f1419;
        --foreground: #0f1419;
        --site-muted: #536471;
        --muted-foreground: #536471;

        background-color: #f7f9fa !important;
        color: #0f1419 !important;
        font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    }

    body.alma-app .site-header {
        background-color: #ffffff !important;
        background-image: none !important;
        box-shadow: none !important;
    }

    body.alma-app .site-header-shell,
    body.alma-app .main-grid {
        max-width: var(--layout-shell-max) !important;
    }

    body.alma-app .main-grid {
        width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        display: grid !important;
        grid-template-columns:
            var(--layout-left-width)
            minmax(0, var(--profile-shell-width))
            var(--layout-right-width) !important;
        column-gap: var(--layout-column-gap) !important;
        row-gap: 0 !important;
        justify-content: center !important;
        align-items: start !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 24px !important;
        padding-bottom: 48px !important;
    }

    body.alma-app .layout-main {
        width: 100% !important;
        max-width: var(--profile-shell-width) !important;
        min-width: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.alma-app .layout-side {
        min-width: 0 !important;
        align-self: start !important;
    }

    

    body.alma-app .layout-side--right {
        width: var(--layout-right-width) !important;
        min-width: var(--layout-right-width) !important;
        max-width: var(--layout-right-width) !important;
        position: static !important;
        align-self: start !important;
    }

    body.alma-app .layout-side--right .layout-sticky {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        position: sticky !important;
        top: 96px !important;
        overflow: visible !important;
        max-height: none !important;
        padding-right: 0 !important;
    }

    body.alma-app .layout-side--right .sidebar-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        background: #ffffff !important;
        border: 0 !important;
        border-radius: 6px !important;
        padding: 18px 13px !important;
        box-shadow: none !important;
        overflow: hidden !important;
    }

    body.alma-app .layout-side--right .sidebar-card + .sidebar-card {
        margin-top: 14px !important;
    }

    body.alma-app .layout-side--right .ografi-sidebar-force,
    body.alma-app .layout-side--right .ografi-right-widget-wrap,
    body.alma-app .layout-side--right .ografi-sidebar-area {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box !important;
        background: transparent !important;
    }

    

    

    /*
    |--------------------------------------------------------------------------
    | Sağ widget iç görünümü - ikinci görsele daha yakın
    |--------------------------------------------------------------------------
    */

    body.alma-app .layout-side--right .alma-widget__header,
    body.alma-app .layout-side--right .ografi-sidebar-header,
    body.alma-app .layout-side--right .ografi-right-widget-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin: 0 0 14px 0 !important;
        padding: 0 !important;
        border: 0 !important;
    }

    body.alma-app .layout-side--right .alma-widget__title,
    body.alma-app .layout-side--right .ografi-right-widget-title {
        margin: 0 !important;
        color: #000000 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
    }

    body.alma-app .layout-side--right .alma-widget__header-icon,
    body.alma-app .layout-side--right .ografi-sidebar-icon,
    body.alma-app .layout-side--right .ografi-right-widget-icon {
        color: #059669 !important;
        font-size: 18px !important;
        line-height: 1 !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment,
    body.alma-app .layout-side--right .ografi-comment-item {
        display: block !important;
        width: 100% !important;
        padding: 0 0 14px 0 !important;
        margin: 0 !important;
        color: inherit !important;
        text-decoration: none !important;
        background: transparent !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment + .alma-widget__comment,
    body.alma-app .layout-side--right .ografi-comment-item + .ografi-comment-item {
        padding-top: 13px !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment:last-child,
    body.alma-app .layout-side--right .ografi-comment-item:last-child {
        padding-bottom: 0 !important;
        border-bottom: 0 !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment-top,
    body.alma-app .layout-side--right .alma-widget__comment-user,
    body.alma-app .layout-side--right .ografi-comment-top {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment-avatar,
    body.alma-app .layout-side--right .alma-widget__comment-avatar--fallback,
    body.alma-app .layout-side--right .ografi-comment-avatar,
    body.alma-app .layout-side--right .ografi-comment-avatar-fallback {
        width: 29px !important;
        height: 29px !important;
        min-width: 29px !important;
        max-width: 29px !important;
        flex: 0 0 29px !important;
        border-radius: 999px !important;
        object-fit: cover !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment-avatar--fallback,
    body.alma-app .layout-side--right .ografi-comment-avatar-fallback {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #8b9cff !important;
        background: #eef2ff !important;
        font-size: 10px !important;
        font-weight: 400 !important;
        text-transform: uppercase !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment-meta,
    body.alma-app .layout-side--right .ografi-comment-meta {
        min-width: 0 !important;
        flex: 1 1 auto !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment-author,
    body.alma-app .layout-side--right .alma-widget__comment-post,
    body.alma-app .layout-side--right .ografi-comment-author,
    body.alma-app .layout-side--right .ografi-comment-post {
        display: block !important;
        overflow: hidden !important;
        max-width: 230px !important;
        color: #000000 !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        line-height: 1.15 !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment-text,
    body.alma-app .layout-side--right .ografi-comment-text {
        margin: 8px 0 0 0 !important;
        color: #000000 !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        line-height: 1.35 !important;
        word-break: break-word !important;
    }

    body.alma-app .layout-side--right .alma-widget__comment-time,
    body.alma-app .layout-side--right .ografi-comment-time {
        margin: 5px 0 0 0 !important;
        color: #64748b !important;
        font-size: 10px !important;
        font-weight: 400 !important;
        line-height: 1.2 !important;
    }

    body.alma-app .layout-side--right .alma-widget__tags,
    body.alma-app .layout-side--right .ografi-tag-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 13px !important;
        width: 100% !important;
    }

    body.alma-app .layout-side--right .alma-tag-item,
    body.alma-app .layout-side--right .ografi-tag-item {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        color: inherit !important;
        text-decoration: none !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
    }

    body.alma-app .layout-side--right .alma-tag-item__name,
    body.alma-app .layout-side--right .ografi-tag-name {
        display: block !important;
        min-width: 0 !important;
        overflow: hidden !important;
        color: #000000 !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        line-height: 1.25 !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    body.alma-app .layout-side--right .alma-tag-item__count,
    body.alma-app .layout-side--right .ografi-tag-count {
        display: block !important;
        flex: 0 0 auto !important;
        min-width: auto !important;
        height: auto !important;
        padding: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #000000 !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
    }

    body.alma-app .layout-side--right .alma-empty-state,
    body.alma-app .layout-side--right .ografi-empty-state {
        padding: 10px 0 !important;
        margin: 0 !important;
        color: #64748b !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        line-height: 1.4 !important;
        text-align: left !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Orta ekran
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1180px) and (min-width: 961px) {
        body.alma-app {
            --layout-right-width: 300px;
            --layout-column-gap: 28px;
            --layout-shell-max: 934px;
        }

        body.alma-app .main-grid {
            max-width: var(--layout-shell-max) !important;
            grid-template-columns:
                minmax(0, var(--profile-shell-width))
                var(--layout-right-width) !important;
            column-gap: var(--layout-column-gap) !important;
        }

        

        body.alma-app .layout-side--right {
            width: var(--layout-right-width) !important;
            min-width: var(--layout-right-width) !important;
            max-width: var(--layout-right-width) !important;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mobil / tablet
    |--------------------------------------------------------------------------
    */

    @media (max-width: 960px) {
        body.alma-app .main-grid {
            max-width: var(--profile-shell-width) !important;
            grid-template-columns: minmax(0, 1fr) !important;
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

        body.alma-app .layout-main {
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        

        body.alma-app .layout-side--right {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }

        body.alma-app .layout-side--right .layout-sticky {
            position: static !important;
            width: 100% !important;
            max-width: 100% !important;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dark mode
    |--------------------------------------------------------------------------
    */

    html.dark body.alma-app,
    body.alma-app.dark,
    [data-theme="dark"] body.alma-app {
        background-color: #0b1120 !important;
        color: #ffffff !important;
    }

    html.dark body.alma-app .layout-side--right .sidebar-card,
    [data-theme="dark"] body.alma-app .layout-side--right .sidebar-card {
        background: #111827 !important;
    }

    html.dark body.alma-app .layout-side--right .alma-widget__title,
    html.dark body.alma-app .layout-side--right .ografi-right-widget-title,
    html.dark body.alma-app .layout-side--right .alma-widget__comment-author,
    html.dark body.alma-app .layout-side--right .alma-widget__comment-post,
    html.dark body.alma-app .layout-side--right .ografi-comment-author,
    html.dark body.alma-app .layout-side--right .ografi-comment-post,
    html.dark body.alma-app .layout-side--right .alma-widget__comment-text,
    html.dark body.alma-app .layout-side--right .ografi-comment-text,
    html.dark body.alma-app .layout-side--right .alma-tag-item__name,
    html.dark body.alma-app .layout-side--right .alma-tag-item__count,
    html.dark body.alma-app .layout-side--right .ografi-tag-name,
    html.dark body.alma-app .layout-side--right .ografi-tag-count {
        color: #ffffff !important;
    }

    html.dark body.alma-app .layout-side--right .alma-widget__comment,
    html.dark body.alma-app .layout-side--right .ografi-comment-item {
        border-bottom-color: rgba(255, 255, 255, 0.12) !important;
    }

    html.dark body.alma-app .layout-side--right .alma-widget__comment-time,
    html.dark body.alma-app .layout-side--right .ografi-comment-time,
    html.dark body.alma-app .layout-side--right .alma-empty-state,
    html.dark body.alma-app .layout-side--right .ografi-empty-state {
        color: #9ca3af !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Sağ sütunu sağ tarafa doğru genişlet
    |--------------------------------------------------------------------------
    */

    body.alma-app {
        --layout-left-width: 240px !important;
        --profile-shell-width: 656px !important;
        --layout-right-width: 304px !important;
        --layout-column-gap: 28px !important;
        --layout-shell-inline: 0px !important;
        --right-sidebar-extra-width: 0px !important;

        --layout-shell-max: calc(
            var(--layout-left-width) +
            var(--profile-shell-width) +
            var(--layout-right-width) +
            (var(--layout-column-gap) * 2)
        ) !important;

        font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    }

    body.alma-app,
    body.alma-app * {
        font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    }

    body.alma-app .main-grid {
        width: 100% !important;
        max-width: var(--layout-shell-max) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        display: grid !important;
        grid-template-columns:
            var(--layout-left-width)
            minmax(0, var(--profile-shell-width))
            var(--layout-right-width) !important;
        column-gap: var(--layout-column-gap) !important;
        row-gap: 0 !important;
        justify-content: center !important;
        align-items: start !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.alma-app .layout-main {
        width: 100% !important;
        max-width: var(--profile-shell-width) !important;
        min-width: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.alma-app .layout-side--right {
        width: var(--layout-right-width) !important;
        min-width: var(--layout-right-width) !important;
        max-width: var(--layout-right-width) !important;
        align-self: start !important;
        margin-left: 0 !important;
        margin-right: -160px !important;
        padding-left: 0 !important;
        overflow: visible !important;
    }

    body.alma-app .layout-side--right .layout-sticky {
        position: static !important;
        top: auto !important;
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        max-height: none !important;
        height: auto !important;
        overflow: visible !important;
        padding-right: 0 !important;
    }

    body.alma-app .layout-side--right .sidebar-scroll {
        max-height: none !important;
        height: auto !important;
        overflow: visible !important;
        overflow-y: visible !important;
        overflow-x: visible !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        gap: 16px !important;
    }

    body.alma-app .layout-side--right .sidebar-scroll::-webkit-scrollbar,
    body.alma-app .layout-side--right .layout-sticky::-webkit-scrollbar {
        width: 0 !important;
        height: 0 !important;
        display: none !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Önemli kısım:
    | Kart genişliği sadece sağ tarafa doğru büyür.
    |--------------------------------------------------------------------------
    */

    body.alma-app .layout-side--right .sidebar-card {
        width: calc(100% + var(--right-sidebar-extra-width)) !important;
        max-width: calc(100% + var(--right-sidebar-extra-width)) !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: calc(var(--right-sidebar-extra-width) * -1) !important;
        box-sizing: border-box !important;

        background: #ffffff !important;
        border: 0 !important;
        border-radius: 18px !important;
        padding: 22px 20px !important;
        box-shadow: none !important;
        overflow: hidden !important;
    }

    body.alma-app .layout-side--right .sidebar-card + .sidebar-card {
        margin-top: 16px !important;
    }

    body.alma-app .layout-side--right .ografi-right-sidebar-block,
    body.alma-app .layout-side--right .ografi-sidebar-force,
    body.alma-app .layout-side--right .ografi-right-widget-wrap,
    body.alma-app .layout-side--right .ografi-sidebar-area {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        background: transparent !important;
        box-sizing: border-box !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Sağ widget metinleri - kalınlaştırma yok
    |--------------------------------------------------------------------------
    */

    body.alma-app .layout-side--right .ografi-widget-title,
    body.alma-app .layout-side--right .alma-widget__title,
    body.alma-app .layout-side--right .ografi-right-widget-title {
        margin: 0 !important;
        color: #111827 !important;
        font-size: 15px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
    }

    body.alma-app .layout-side--right .ografi-widget-header,
    body.alma-app .layout-side--right .alma-widget__header,
    body.alma-app .layout-side--right .ografi-sidebar-header,
    body.alma-app .layout-side--right .ografi-right-widget-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        margin: 0 0 18px 0 !important;
        padding: 0 !important;
        border: 0 !important;
    }

    body.alma-app .layout-side--right .ografi-widget-icon,
    body.alma-app .layout-side--right .alma-widget__header-icon,
    body.alma-app .layout-side--right .ografi-sidebar-icon,
    body.alma-app .layout-side--right .ografi-right-widget-icon {
        color: #059669 !important;
        font-size: 20px !important;
        line-height: 1 !important;
    }

    body.alma-app .layout-side--right .ografi-comment-item,
    body.alma-app .layout-side--right .alma-widget__comment {
        display: block !important;
        width: 100% !important;
        padding: 0 0 17px 0 !important;
        margin: 0 !important;
        color: inherit !important;
        text-decoration: none !important;
        background: transparent !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    body.alma-app .layout-side--right .ografi-comment-item + .ografi-comment-item,
    body.alma-app .layout-side--right .alma-widget__comment + .alma-widget__comment {
        padding-top: 15px !important;
    }

    body.alma-app .layout-side--right .ografi-comment-item:last-child,
    body.alma-app .layout-side--right .alma-widget__comment:last-child {
        padding-bottom: 0 !important;
        border-bottom: 0 !important;
    }

    body.alma-app .layout-side--right .ografi-comment-top,
    body.alma-app .layout-side--right .alma-widget__comment-top,
    body.alma-app .layout-side--right .alma-widget__comment-user {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    body.alma-app .layout-side--right .ografi-comment-avatar,
    body.alma-app .layout-side--right .ografi-comment-avatar-fallback,
    body.alma-app .layout-side--right .alma-widget__comment-avatar,
    body.alma-app .layout-side--right .alma-widget__comment-avatar--fallback {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        max-width: 34px !important;
        flex: 0 0 34px !important;
        border-radius: 999px !important;
        object-fit: cover !important;
    }

    body.alma-app .layout-side--right .ografi-comment-avatar-fallback,
    body.alma-app .layout-side--right .alma-widget__comment-avatar--fallback {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #eef2ff !important;
        color: #8b9cff !important;
        font-size: 11px !important;
        font-weight: 400 !important;
        text-transform: uppercase !important;
    }

    body.alma-app .layout-side--right .ografi-comment-meta,
    body.alma-app .layout-side--right .alma-widget__comment-meta {
        min-width: 0 !important;
        flex: 1 1 auto !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 3px !important;
    }

    body.alma-app .layout-side--right .ografi-comment-author,
    body.alma-app .layout-side--right .alma-widget__comment-author,
    body.alma-app .layout-side--right .ografi-comment-post,
    body.alma-app .layout-side--right .alma-widget__comment-post {
        display: block !important;
        overflow: hidden !important;
        max-width: 340px !important;
        color: #111827 !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    body.alma-app .layout-side--right .ografi-comment-text,
    body.alma-app .layout-side--right .alma-widget__comment-text {
        margin: 10px 0 0 0 !important;
        color: #111827 !important;
        font-size: 15px !important;
        font-weight: 400 !important;
        line-height: 1.45 !important;
        word-break: break-word !important;
    }

    body.alma-app .layout-side--right .ografi-comment-time,
    body.alma-app .layout-side--right .alma-widget__comment-time {
        margin: 6px 0 0 0 !important;
        color: #6b7280 !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
    }

    body.alma-app .layout-side--right .ografi-tag-list,
    body.alma-app .layout-side--right .alma-widget__tags {
        display: flex !important;
        flex-direction: column !important;
        gap: 15px !important;
        width: 100% !important;
    }

    body.alma-app .layout-side--right .ografi-tag-item,
    body.alma-app .layout-side--right .alma-tag-item {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        color: inherit !important;
        text-decoration: none !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
    }

    body.alma-app .layout-side--right .ografi-tag-name,
    body.alma-app .layout-side--right .alma-tag-item__name,
    body.alma-app .layout-side--right .ografi-tag-count,
    body.alma-app .layout-side--right .alma-tag-item__count {
        display: block !important;
        color: #111827 !important;
        font-size: 15px !important;
        font-weight: 400 !important;
        line-height: 1.35 !important;
    }

    body.alma-app .layout-side--right .ografi-tag-name,
    body.alma-app .layout-side--right .alma-tag-item__name {
        min-width: 0 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    body.alma-app .layout-side--right .ografi-tag-count,
    body.alma-app .layout-side--right .alma-tag-item__count {
        flex: 0 0 auto !important;
        min-width: auto !important;
        height: auto !important;
        padding: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Orta ekran
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1180px) and (min-width: 961px) {
        body.alma-app {
            --layout-right-width: 304px !important;
            --layout-column-gap: 28px !important;
            --right-sidebar-extra-width: 0px !important;
            --layout-shell-max: calc(
                var(--profile-shell-width) +
                var(--layout-right-width) +
                var(--layout-column-gap)
            ) !important;
        }

        body.alma-app .main-grid {
            grid-template-columns:
                minmax(0, var(--profile-shell-width))
                var(--layout-right-width) !important;
            column-gap: var(--layout-column-gap) !important;
        }

        
    }

    /*
    |--------------------------------------------------------------------------
    | Mobil
    |--------------------------------------------------------------------------
    */

    @media (max-width: 960px) {
        body.alma-app .main-grid {
            max-width: 100% !important;
            grid-template-columns: minmax(0, 1fr) !important;
            padding-left: 14px !important;
            padding-right: 14px !important;
            column-gap: 0 !important;
        }

        

        body.alma-app .layout-main,
        body.alma-app .layout-side--right {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        body.alma-app .layout-side--right .layout-sticky {
            position: static !important;
        }

        body.alma-app .layout-side--right .sidebar-card {
            width: 100% !important;
            max-width: 100% !important;
            margin-right: 0 !important;
        }

        body.alma-app.route-home .main-grid,
        body.alma-app.route-home .main-grid.main-grid--padded,
        body.alma-app.route-home .main-grid.main-grid--no-pad {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        body.alma-app.route-home .alma-post-card,
        body.alma-app.route-home [data-post-card-shell] {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
    }
</style>
    <style>
        /*
        |--------------------------------------------------------------------------
        | Roboto post başlığı ve açıklama düzeltmesi
        |--------------------------------------------------------------------------
        | Başlık: Roboto Bold / 700
        | Açıklama: Roboto Regular / 400
        */
        body.alma-app .alma-post-card__title,
        body.alma-app .alma-post-card__title.is-hero,
        body.alma-app .alma-post-card__title a {
            font-family: "Roboto", Arial, Helvetica, sans-serif !important;
            font-weight: 700 !important;
        }

        body.alma-app .alma-post-card__excerpt,
        body.alma-app .alma-post-card__summary-text,
        body.alma-app .alma-post-card__inline-preview,
        body.alma-app .alma-post-card__inline-text {
            font-family: "Roboto", Arial, Helvetica, sans-serif !important;
            font-weight: 400 !important;
        }
    </style>


    <style>
        /*
        |--------------------------------------------------------------------------
        | Ografi mobil alt sabit kutu temizliği
        |--------------------------------------------------------------------------
        | Eski mobil bottom nav, sidebar footer ve bottom-sheet dönüşümleri kaldırıldı.
        | Bu blok dosyanın sonuna yakın olduğu için önceki tekrar eden CSS kurallarını ezer.
        */
        @media (max-width: 1023.98px) {
            details[data-mobile-sheet="1"],
            details[data-mobile-sheet="1"][open],
            details[data-mobile-sheet="1"] [data-mobile-sheet-panel="1"],
            details[data-mobile-sheet="1"][open] [data-mobile-sheet-panel="1"],
            .mobile-sheet-backdrop {
                position: static !important;
                inset: auto !important;
                width: auto !important;
                max-width: none !important;
                max-height: none !important;
                transform: none !important;
                border-radius: inherit !important;
                z-index: auto !important;
            }

            .mobile-sheet-backdrop {
                display: none !important;
            }

            body.alma-app,
            html,
            body {
                padding-bottom: 0 !important;
            }

            body.alma-app .main-grid,
            .main-grid,
            .main-grid.main-grid--padded,
            .main-grid.main-grid--no-pad {
                padding-bottom: 24px !important;
            }
        }

        :root {
            --page-max: 1272px;
            --left-col: 200px;
            --main-col: 656px;
            --right-col: 304px;
            --grid-gap: 56px;
            --header-height: 64px;
            --card-radius: 10px;
            --page-bg: #f4f4f5;
            --header-bg: #d9f0ff;
            --card-bg: #ffffff;
        }

        html,
        body.alma-app {
            background: var(--page-bg) !important;
            background-color: var(--page-bg) !important;
        }

        body.alma-app {
            --site-header-height: var(--header-height) !important;
            --layout-left-width: var(--left-col) !important;
            --profile-shell-width: var(--main-col) !important;
            --layout-right-width: var(--right-col) !important;
            --layout-column-gap: var(--grid-gap) !important;
            --layout-shell-inline: 0px !important;
            --layout-shell-max: var(--page-max) !important;
            padding-top: var(--header-height) !important;
        }

        body.alma-app .site-header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: var(--header-height) !important;
            min-height: var(--header-height) !important;
            background: rgba(255, 255, 255, 0.82) !important;
            background-color: rgba(255, 255, 255, 0.82) !important;
            backdrop-filter: blur(14px) !important;
            -webkit-backdrop-filter: blur(14px) !important;
            border: 0 !important;
            box-shadow: none !important;
            z-index: 9990 !important;
        }

        body.alma-app .site-header-shell,
        body.alma-app .main-grid {
            width: 100% !important;
            max-width: var(--page-max) !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        body.alma-app .site-header-shell {
            height: var(--header-height) !important;
            min-height: var(--header-height) !important;
            background: transparent !important;
        }

        body.alma-app .main-grid,
        body.alma-app .main-grid.main-grid--padded,
        body.alma-app .main-grid.main-grid--no-pad {
            display: grid !important;
            grid-template-columns: var(--left-col) var(--main-col) var(--right-col) !important;
            column-gap: var(--grid-gap) !important;
            row-gap: 0 !important;
            align-items: start !important;
            justify-content: center !important;
            padding: 16px 0 24px !important;
        }

        body.alma-app .layout-side--left {
            width: var(--left-col) !important;
            max-width: var(--left-col) !important;
            min-width: var(--left-col) !important;
        }

        body.alma-app .layout-main {
            width: var(--main-col) !important;
            max-width: var(--main-col) !important;
            min-width: 0 !important;
            padding: 0 !important;
        }

        body.alma-app .layout-side--right {
            width: var(--right-col) !important;
            max-width: var(--right-col) !important;
            min-width: var(--right-col) !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding: 0 !important;
            transform: none !important;
            overflow: visible !important;
        }

        body.alma-app .alma-tabs {
            height: 52px !important;
            display: flex !important;
            align-items: center !important;
            gap: 32px !important;
            padding: 0 8px !important;
            margin: 0 !important;
        }

        body.alma-app .alma-tab {
            min-height: 52px !important;
            padding: 0 !important;
            font-size: 16px !important;
        }

        body.alma-app .alma-tab.is-active::after {
            content: none !important;
            display: none !important;
        }

        body.alma-app .alma-ad-slot,
        body.alma-app .site-card.alma-feed-promo {
            width: var(--main-col) !important;
            max-width: var(--main-col) !important;
            min-height: 128px !important;
            border-radius: var(--card-radius) !important;
            background: var(--card-bg) !important;
            box-shadow: none !important;
            border: 0 !important;
            overflow: hidden !important;
        }

        body.alma-app .alma-ad-slot__inner {
            min-height: 128px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        body.alma-app .alma-ad-slot--cover,
        body.alma-app .alma-ad-slot--cover .alma-ad-slot__inner {
            aspect-ratio: 16 / 9 !important;
            min-height: 0 !important;
        }

        body.alma-app .layout-side--right .alma-ad-slot {
            width: var(--right-col) !important;
            max-width: var(--right-col) !important;
            min-width: 0 !important;
            aspect-ratio: 9 / 16 !important;
            min-height: 0 !important;
            height: auto !important;
        }

        body.alma-app .layout-side--right .alma-ad-slot__inner {
            width: 100% !important;
            min-height: 0 !important;
            aspect-ratio: 9 / 16 !important;
        }

        body.alma-app .layout-side--right .alma-ad-slot__inner > * {
            max-width: 100% !important;
        }

        body.alma-app .layout-side--right .space-y-6 > .alma-ad-slot + * {
            margin-top: 14px !important;
        }

        body.alma-app .alma-post-card,
        body.alma-app [data-post-card-shell] {
            width: var(--main-col) !important;
            max-width: var(--main-col) !important;
            border-radius: var(--card-radius) !important;
            background: var(--card-bg) !important;
            padding: 20px !important;
            box-shadow: none !important;
            border: 0 !important;
            box-sizing: border-box !important;
        }

        body.alma-app .alma-post-card__title,
        body.alma-app .alma-post-card__title.is-hero {
            font-size: 21px !important;
            line-height: 1.25 !important;
            font-weight: 700 !important;
            letter-spacing: 0 !important;
        }

        body.alma-app .alma-post-card__media-link {
            width: 100% !important;
            margin-top: 18px !important;
            margin-bottom: 18px !important;
            border-radius: var(--card-radius) !important;
        }

        body.alma-app .alma-post-card__image {
            width: 100% !important;
            aspect-ratio: 16 / 9 !important;
            height: auto !important;
            border-radius: var(--card-radius) !important;
            object-fit: cover !important;
            margin: 0 !important;
        }

        body.alma-app .layout-side--right .ografi-sidebar-force,
        body.alma-app .layout-side--right .ografi-right-sidebar-block,
        body.alma-app .layout-side--right .ografi-right-widget-wrap,
        body.alma-app .layout-side--right .ografi-sidebar-area {
            width: var(--right-col) !important;
            max-width: var(--right-col) !important;
            min-width: var(--right-col) !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body.alma-app .layout-side--right .ografi-sidebar-card,
        body.alma-app .layout-side--right .sidebar-card {
            width: var(--right-col) !important;
            max-width: var(--right-col) !important;
            min-width: var(--right-col) !important;
            border-radius: var(--card-radius) !important;
            background: var(--card-bg) !important;
            padding: 16px !important;
            box-shadow: none !important;
            border: 0 !important;
            box-sizing: border-box !important;
        }

        body.alma-app .layout-side--right .ografi-sidebar-card + .ografi-sidebar-card,
        body.alma-app .layout-side--right .sidebar-card + .sidebar-card {
            margin-top: 16px !important;
        }

        body.alma-app .layout-side--right .ografi-comment-item,
        body.alma-app .layout-side--right .alma-widget__comment {
            min-height: 105px !important;
        }

        body.alma-app .nav-item,
        body.alma-app .sidebar-category-link {
            width: var(--left-col) !important;
            max-width: var(--left-col) !important;
        }

        body.alma-app .nav-item {
            height: 52px !important;
            min-height: 52px !important;
        }

        body.alma-app .nav-item[data-active="true"] {
            height: 48px !important;
            min-height: 48px !important;
        }

        body.alma-app .nav-item-icon-outline iconify-icon,
        body.alma-app .nav-item-icon-outline svg {
            width: 22px !important;
            height: 22px !important;
            font-size: 22px !important;
        }

        body.alma-app .sidebar-category-avatar,
        body.alma-app .sidebar-category-avatar--fallback {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            min-height: 28px !important;
            max-width: 28px !important;
            max-height: 28px !important;
            flex-basis: 28px !important;
        }

        @media (max-width: 1199px) and (min-width: 901px) {
            body.alma-app .main-grid,
            body.alma-app .main-grid.main-grid--padded,
            body.alma-app .main-grid.main-grid--no-pad {
                max-width: calc(var(--left-col) + var(--main-col) + var(--grid-gap)) !important;
                grid-template-columns: var(--left-col) var(--main-col) !important;
                column-gap: var(--grid-gap) !important;
            }

            body.alma-app .layout-side--right {
                display: none !important;
            }
        }

        @media (max-width: 900px) {
            html {
                min-height: 100% !important;
                overflow-x: hidden !important;
            }

            body.alma-app {
                min-height: 100dvh !important;
                padding-bottom: calc(100px + env(safe-area-inset-bottom, 0px)) !important;
            }

            html:has(body.route-post-show),
            body.alma-app.route-post-show {
                height: auto !important;
                max-height: none !important;
                overflow-y: auto !important;
                overscroll-behavior-y: auto !important;
                touch-action: pan-y !important;
                -webkit-overflow-scrolling: touch !important;
            }

            body.alma-app .site-header-shell,
            body.alma-app .main-grid,
            body.alma-app .main-grid.main-grid--padded,
            body.alma-app .main-grid.main-grid--no-pad {
                max-width: var(--main-col) !important;
            }

            body.alma-app .main-grid,
            body.alma-app .main-grid.main-grid--padded,
            body.alma-app .main-grid.main-grid--no-pad {
                grid-template-columns: minmax(0, 1fr) !important;
                padding: 16px 14px 24px !important;
            }

            body.alma-app.route-post-show .main-grid,
            body.alma-app.route-post-show .main-grid.main-grid--padded,
            body.alma-app.route-post-show .main-grid.main-grid--no-pad,
            body.alma-app.route-post-show .layout-main,
            body.alma-app.route-post-show .post-show-shell {
                height: auto !important;
                max-height: none !important;
                overflow-y: visible !important;
                touch-action: pan-y !important;
            }

            body.alma-app .layout-side--left,
            body.alma-app .layout-side--right {
                display: none !important;
            }

            body.alma-app .layout-main,
            body.alma-app .alma-ad-slot,
            body.alma-app .site-card.alma-feed-promo,
            body.alma-app .alma-post-card,
            body.alma-app [data-post-card-shell] {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>

    @include('partials.pwa-meta')
</head>
@php($isMessagesRoute = request()->routeIs('messages.*'))
@php($isCategoryRoute = request()->routeIs('blog.categories', 'blog.category', 'blog.category.*'))
@php($isPostShowRoute = request()->routeIs('blog.post'))

<body
    class="bg-[#f7f9fa] text-slate-900 font-sans antialiased theme-minimal alma-app {{ request()->routeIs('home') ? 'route-home' : '' }} {{ request()->routeIs('discover') ? 'route-discover' : '' }} {{ request()->routeIs('video') ? 'route-video' : '' }} {{ $isCategoryRoute ? 'route-category' : '' }} {{ $isPostShowRoute ? 'route-post-show' : '' }}"
    data-mentions-endpoint="{{ auth()->check() ? route('mentions.users') : '' }}"
>
    @include('partials.toasts')
    @unless ($__env->hasSection('hide_global_header'))
        @include('header')
    @endunless
    @include('partials.pwa-install-banner')

    @if ($__env->hasSection('custom_shell'))
        @yield('custom_shell')
    @else
        <div class="main-grid {{ $__env->hasSection('no_container_padding') ? 'main-grid--no-pad' : 'main-grid--padded' }}">
            <aside class="hidden lg:block layout-side layout-side--left">
                @include('partials.ads.context-slot', [
                    'slotKey' => 'ads_left_sidebar_top',
                ])

                @include('partials.left')
            </aside>

            

            <main class="space-y-6 layout-main">
                @unless ($__env->hasSection('hide_feed_header') || $isMessagesRoute || request()->routeIs('home'))
                    @include('partials.community-feed')
                @endunless

                @include('partials.ads.context-slot', [
                    'slotKey' => 'ads_main_before_content',
                ])

                @if ($__env->hasSection('fullwidth'))
                    @yield('fullwidth')
                @elseif ($__env->hasSection('content'))
                    @yield('content')
                @elseif (isset($slot))
                    {{ $slot }}
                @else
                    <div class="rounded-xl p-4">
                        Icerik yok.
                    </div>
                @endif

                @include('partials.ads.context-slot', [
                    'slotKey' => 'ads_main_after_content',
                ])
            </main>

            <aside class="hidden lg:block space-y-6 custom-scrollbar layout-side layout-side--right">
                @include('partials.right')
            </aside>
        </div>
    @endif

    @unless (request()->routeIs('filament.*') || $__env->hasSection('hide_mobile_bottom_nav'))
        <x-mobile-bottom-nav />
    @endunless

    <x-cookie-banner />

    @include('partials.external-link-bridge')
    @include('partials.image-lightbox')
    @include('partials.feed-load-more-styles')


    @stack('modals')
    @livewireScripts
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js" defer></script>
    @include('partials.google-one-tap')
    @include('partials.pwa-scripts')
    <script>
        // Mobil bottom-sheet dönüştürücü kaldırıldı. Dropdownlar artık altta sabit kutu oluşturmaz.
    </script>
    @include('partials.mention-assets')
    @stack('scripts')

    <style>
        /* Tablet landscape: use a centered single-column feed, not desktop sidebars. */
        @media (min-width: 1024px) and (max-width: 1279px) {
            html body.alma-app:not(#comments):not(#app) .main-grid {
                width: 100% !important;
                max-width: var(--profile-shell-width, 656px) !important;
                grid-template-columns: minmax(0, 1fr) !important;
                column-gap: 0 !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            html body.alma-app:not(#comments):not(#app) .layout-side--left,
            html body.alma-app:not(#comments):not(#app) .layout-side--right {
                display: none !important;
            }

            html body.alma-app:not(#comments):not(#app) .main-grid > main.layout-main {
                grid-column: 1 !important;
                grid-row: 1 !important;
                width: 100% !important;
                max-width: var(--profile-shell-width, 656px) !important;
                min-width: 0 !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }
    </style>
</body>
</html>

<title>@yield('title', config('app.name', 'Ografi'))</title>
<meta name="description" content="@yield('meta_description', 'Ografi üzerinde yeni yazılar, profiller, kategoriler ve topluluk içeriklerini keşfet.')">

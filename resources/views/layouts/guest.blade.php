<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="@yield('meta_description', 'OGrafi: giris ve kayit sayfalari.')">
        <link rel="canonical" href="{{ trim($__env->yieldContent('canonical_url')) ?: url()->current() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @include('partials.system-appearance')
        @include('partials.google-analytics')

        <!-- Favicon -->
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        <link rel="icon" href="/favicon.ico" type="image/x-icon">

        <!-- Fonts -->
        @include('partials.font-assets')
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
                background: #f3f4f6 !important;
                border-color: #d1d5db !important;
                color: #374151 !important;
            }
            body.alma-app :where(button, input[type="submit"], input[type="button"], .alma-button):hover {
                background: #e5e7eb !important;
                border-color: #cbd5e1 !important;
            }
            body.alma-app :where(button, input[type="submit"], input[type="button"], .alma-button):disabled {
                background: #e5e7eb !important;
                border-color: #e5e7eb !important;
                color: #9ca3af !important;
                cursor: not-allowed;
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

            .guest-content :where([class*="max-w-"], .mx-auto) {
                max-width: var(--profile-shell-width) !important;
                width: 100% !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .guest-content {
                width: 100%;
                max-width: var(--profile-shell-width);
                margin-left: auto;
                margin-right: auto;
            }

            .guest-content > .mx-auto[class*="max-w-"],
            .guest-content > div > .mx-auto[class*="max-w-"],
            .guest-content > div > div > .mx-auto[class*="max-w-"] {
                max-width: var(--profile-shell-width) !important;
                width: 100% !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            .community-shell {
                max-width: var(--layout-shell-max) !important;
                width: 100% !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            body.alma-app.auth-single {
                background: var(--alma-bg, #f4f6f7);
                color: var(--alma-text, inherit);
            }

            .auth-single-shell {
                min-height: 100vh;
            }

            .auth-single main {
                min-height: 100vh !important;
                padding-top: clamp(1.5rem, 5vw, 3rem) !important;
                padding-bottom: clamp(1.5rem, 5vw, 3rem) !important;
            }

            .auth-single .guest-content :where([class*="max-w-"], .mx-auto) {
                max-width: var(--profile-shell-width) !important;
                width: 100% !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }

            @media (max-width: 767px) {
                .community-shell {
                    padding-left: max(var(--alma-page-inline), env(safe-area-inset-left)) !important;
                    padding-right: max(var(--alma-page-inline), env(safe-area-inset-right)) !important;
                }
            }
        </style>

        <!-- Styles -->
        @livewireStyles
        <style>
    :root {
        --site-header-height: 64px;
        --profile-shell-width: 656px;
        --layout-left-width: 240px;
        --layout-right-width: 344px;
        --layout-column-gap: 36px;
        --layout-shell-inline: 24px;
        --layout-shell-max: calc(
            var(--layout-left-width) + var(--profile-shell-width) + var(--layout-right-width) + (var(--layout-column-gap) * 2) + (var(--layout-shell-inline) * 2)
        );
    }

    .layout-sticky {
        position: sticky;
        top: calc(var(--site-header-height) + 16px);
    }

    @media (min-width: 1024px) {
        .community-grid {
            grid-template-columns: repeat(12, minmax(0, 1fr));
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

    :root {
        --alma-bg: #f2f6f9;
        --alma-header-bg: rgba(220, 237, 251, 0.88);
        --alma-primary: #029d71;
        --alma-primary-strong: #017a58;
        --alma-text: #111827;
        --alma-muted: #6b7280;
        --alma-soft: #9ca3af;
        --alma-card: #ffffff;
        --alma-border: rgba(17, 24, 39, 0.08);
        --alma-shadow: 0 20px 45px rgba(15, 23, 42, 0.06);
        --alma-page-inline: 14px;
        --alma-card-inline: 18px;
    }

    body.alma-app {
        background:
            radial-gradient(circle at top left, rgba(2, 157, 113, 0.08), transparent 22rem),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 26rem),
            var(--alma-bg);
        color: var(--alma-text);
    }

    body.alma-app *,
    body.alma-app *::before,
    body.alma-app *::after {
        box-shadow: none !important;
    }

    .site-header {
        background: var(--alma-header-bg);
        border-bottom: 1px solid rgba(17, 24, 39, 0.05);
        backdrop-filter: blur(14px);
    }

    .site-header-shell {
        max-width: 1280px;
        margin: 0 auto;
        min-height: 72px;
        padding: 0 24px;
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
        border: 1px solid rgba(17, 24, 39, 0.06);
        background: rgba(255, 255, 255, 0.72);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--alma-text);
        transition: background-color 0.15s ease, color 0.15s ease;
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
        background: #16a34a;
        box-shadow: 0 0 0 2px #ffffff;
    }

    .site-header-desktop-only {
        display: inline-flex;
    }

    .site-header .site-primary-btn {
        background: var(--alma-primary);
        color: #fff;
        border-radius: 12px;
        min-height: 40px;
        padding: 0 18px;
        font-weight: 500;
        transition: background-color 0.15s ease, color 0.15s ease;
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

    @media (max-width: 1023px) {
        .site-search-panel {
            display: none;
        }

        .site-header-write-btn {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .site-header-actions {
            gap: 8px;
        }

        .site-header-desktop-only {
            display: none !important;
        }
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

    .site-card,
    .community-card,
    .alma-panel {
        background: var(--alma-card);
        border: 1px solid var(--alma-border);
        border-radius: 20px;
        box-shadow: var(--alma-shadow);
        padding-left: var(--alma-card-inline) !important;
        padding-right: var(--alma-card-inline) !important;
    }

    .site-card > div,
    .site-card > section,
    .site-card > article,
    .site-card > header,
    .site-card > main,
    .site-card > form,
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

    .alma-feed-promo {
        padding: 22px 24px;
        text-align: center;
        font-size: 15px;
        color: var(--alma-text);
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(234, 246, 244, 0.96)),
            #ffffff;
    }

    .alma-feed-promo a {
        color: #6b7280;
        font-weight: 500;
    }

    body.alma-app {
        font-weight: 400;
    }

    body.alma-app :where(
        .community-pill,
        .community-btn,
        .site-header .site-primary-btn,
        .site-avatar-fallback,
        .alma-feed-promo a,
        .font-medium,
        .font-semibold,
        .font-bold,
        .font-extrabold,
        .font-black
    ) {
        font-weight: 500 !important;
    }

    html.dark .auth-single-shell .bg-white,
    html.dark .auth-single-shell .site-card,
    html.dark .auth-single-shell .community-card {
        background-color: rgba(15, 23, 42, 0.9) !important;
    }

    html.dark .auth-single-shell .border-gray-200,
    html.dark .auth-single-shell .border-slate-200,
    html.dark .auth-single-shell .border-gray-300 {
        border-color: rgba(148, 163, 184, 0.18) !important;
    }

    html.dark .auth-single-shell .text-gray-900,
    html.dark .auth-single-shell .text-slate-900,
    html.dark .auth-single-shell .text-gray-800,
    html.dark .auth-single-shell .text-slate-800 {
        color: var(--alma-text) !important;
    }

    html.dark .auth-single-shell .text-gray-700,
    html.dark .auth-single-shell .text-gray-600,
    html.dark .auth-single-shell .text-gray-500,
    html.dark .auth-single-shell .text-slate-700,
    html.dark .auth-single-shell .text-slate-600,
    html.dark .auth-single-shell .text-slate-500 {
        color: var(--alma-muted) !important;
    }

    html.dark .auth-single-shell .bg-gray-900 {
        background-color: var(--alma-primary-strong) !important;
    }

    html.dark .auth-single-shell .bg-emerald-50,
    html.dark .auth-single-shell .bg-rose-50 {
        background-color: rgba(15, 23, 42, 0.74) !important;
    }

    html.dark .auth-single-shell .border-emerald-200 {
        border-color: rgba(16, 185, 129, 0.32) !important;
    }

    html.dark .auth-single-shell .border-rose-200 {
        border-color: rgba(244, 63, 94, 0.28) !important;
    }

    html.dark .auth-single-shell .text-emerald-800 {
        color: #86efac !important;
    }

    html.dark .auth-single-shell .text-rose-700 {
        color: #fda4af !important;
    }

    html.dark .auth-single-shell .text-sky-700 {
        color: #d1d5db !important;
    }

    html.dark .auth-single-shell .bg-sky-100,
    html.dark .auth-single-shell .focus\:ring-sky-100:focus,
    html.dark .auth-single-shell .focus\:ring-sky-200:focus,
    html.dark .auth-single-shell .focus\:ring-gray-200:focus {
        --tw-ring-color: rgba(209, 213, 219, 0.22) !important;
    }

    html.dark .auth-single-shell input,
    html.dark .auth-single-shell textarea,
    html.dark .auth-single-shell select {
        background-color: rgba(15, 23, 42, 0.76) !important;
        border-color: rgba(148, 163, 184, 0.18) !important;
        color: var(--alma-text) !important;
    }

    html.dark .auth-single-shell input::placeholder,
    html.dark .auth-single-shell textarea::placeholder {
        color: rgba(148, 163, 184, 0.78) !important;
    }

    html.dark .auth-single-shell .bg-gray-200,
    html.dark .auth-single-shell .bg-slate-200 {
        background-color: rgba(30, 41, 59, 0.92) !important;
    }

    html.dark .auth-single-shell a.rounded-2xl,
    html.dark .auth-single-shell button.rounded-2xl {
        border-color: rgba(148, 163, 184, 0.18) !important;
    }

    html.dark .auth-single-shell a.rounded-2xl.bg-white,
    html.dark .auth-single-shell button.rounded-2xl.bg-white,
    html.dark .auth-single-shell a.rounded-2xl:not(.bg-gray-900),
    html.dark .auth-single-shell button.rounded-2xl:not(.bg-gray-900) {
        background-color: rgba(15, 23, 42, 0.82) !important;
        color: var(--alma-text) !important;
    }

    html.dark .auth-single-shell a.rounded-2xl:hover,
    html.dark .auth-single-shell button.rounded-2xl:hover {
        background-color: rgba(30, 41, 59, 0.9) !important;
    }

    .site-header .site-primary-btn,
    .site-header .site-icon-btn,
    .site-header .site-search-trigger,
    .site-header .site-search-close,
    .site-header .site-notifications-more,
    [data-mobile-sidebar-close],
    [data-theme-toggle] {
        background: transparent !important;
        border-color: rgba(148, 163, 184, 0.28) !important;
        color: #475569 !important;
        box-shadow: none !important;
    }

    .site-header .site-primary-btn:hover,
    .site-header .site-icon-btn:hover,
    .site-header .site-search-trigger:hover,
    .site-header .site-search-close:hover,
    .site-header .site-notifications-more:hover,
    [data-mobile-sidebar-close]:hover,
    [data-theme-toggle]:hover {
        background: #ffffff !important;
        border-color: rgba(148, 163, 184, 0.38) !important;
        color: #0f172a !important;
        transform: none !important;
    }

    .site-header-write-btn {
        background: #f3f4f6 !important;
        border: none !important;
        color: #374151 !important;
        box-shadow: none !important;
    }

    .site-header-write-btn:hover {
        background: #e5e7eb !important;
        border: none !important;
        color: #1f2937 !important;
        transform: none !important;
    }

    button,
    [type="button"],
    [type="submit"],
    [type="reset"],
    .site-primary-btn,
    .site-icon-btn,
    .site-header-write-btn {
        border: none !important;
    }

    body.alma-app :where(
        [aria-label*="Diger islemler"],
        [aria-label*="Diğer işlemler"],
        [aria-label*="More"],
        [aria-label*="more"],
        [data-user-menu-btn],
        [data-logo-menu-btn],
        [data-profile-actions-open],
        [data-create-actions-menu] summary,
        [data-category-menu] summary,
        [data-sort-menu] summary,
        [data-message-settings-menu] summary,
        .menu-btn,
        .menu-button,
        .site-notifications-more,
        .profile-reference-menu-summary
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
        [data-user-menu-btn],
        [data-logo-menu-btn],
        [data-profile-actions-open],
        [data-create-actions-menu] summary,
        [data-category-menu] summary,
        [data-sort-menu] summary,
        [data-message-settings-menu] summary,
        .menu-btn,
        .menu-button,
        .site-notifications-more,
        .profile-reference-menu-summary
    ):where(:hover, :focus-visible, [aria-expanded="true"], [open] > summary) {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }

    body.alma-app :where(
        [data-user-menu-panel],
        [data-logo-menu-panel],
        [data-profile-menu-panel],
        [data-create-actions-menu] > div,
        [data-category-menu] > div,
        [data-sort-menu] > div,
        [data-message-settings-menu] > div,
        .site-menu-panel,
        .profile-reference-actions-dropdown,
        .message-settings-menu__panel
    ) {
        border: 0 !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
    }

    body.alma-app :where(
        [data-user-menu-panel] a,
        [data-user-menu-panel] button,
        [data-logo-menu-panel] a,
        [data-logo-menu-panel] button,
        [data-profile-menu-panel] a,
        [data-profile-menu-panel] button,
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
        .profile-reference-actions-dropdown a,
        .profile-reference-actions-dropdown button,
        .message-settings-menu__panel a,
        .message-settings-menu__panel button
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
        [data-user-menu-panel] a,
        [data-user-menu-panel] button,
        [data-logo-menu-panel] a,
        [data-logo-menu-panel] button,
        [data-profile-menu-panel] a,
        [data-profile-menu-panel] button,
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
        .profile-reference-actions-dropdown a,
        .profile-reference-actions-dropdown button,
        .message-settings-menu__panel a,
        .message-settings-menu__panel button
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

</style>

        @include('partials.pwa-meta')
        @stack('head')
        <style>
            :root {
                --ografi-font-family: "Roboto", Arial, Helvetica, sans-serif;
                --ografi-title-size: 24px;
                --ografi-description-size: 16px;
                --ografi-body-size: 19px;
            }

            html,
            body,
            body.alma-app,
            body.alma-app * {
                font-family: var(--ografi-font-family) !important;
                font-weight: 400 !important;
                letter-spacing: 0 !important;
            }

            body.alma-app :where(h1, h2, h3, h4, h5, h6, .page-title, .section-title, .card-title, .post-title, .alma-page-title, .alma-post-card__title, .ps-post-title, .ps-comments-title) {
                font-size: var(--ografi-title-size) !important;
                line-height: 1.35 !important;
                font-weight: 400 !important;
            }

            body.alma-app :where(.description, .excerpt, .summary, .subtitle, .subheading, .meta, .text-muted, .text-gray-500, .text-slate-500, .alma-post-card__excerpt, .alma-post-card__submeta, .ps-post-subline, .ps-source-label, .ps-source-domain) {
                font-size: var(--ografi-description-size) !important;
                line-height: 1.45 !important;
                font-weight: 400 !important;
            }

            body.alma-app :where(p, li, blockquote, figcaption, label, input, textarea, select, button, a, span, td, th, .prose, .post-content, .content, .body-text, .ps-post-body, .ps-post-body *, .ps-comment-text, .alma-post-card__body) {
                font-size: var(--ografi-body-size) !important;
                line-height: 1.55 !important;
                font-weight: 400 !important;
            }

            body.alma-app :where(svg, iconify-icon, img, video, iframe) {
                font-size: initial !important;
            }
        </style>
    
</head>
    @php
        $themeLayout = $themeLayout ?? \App\Models\ThemeSetting::currentOrNull();
        $authSingle = request()->routeIs(
            'login',
            'register',
            'password.*',
            'forgot-password',
            'reset-password',
            'two-factor.*'
        );
    @endphp
    <body class="bg-gray-100 dark:bg-gray-900 text-text-light dark:text-text-dark font-sans antialiased transition-colors duration-200 theme-minimal alma-app @auth logged-in @else logged-out @endauth {{ $authSingle ? 'auth-single' : '' }}"
          data-mentions-endpoint="{{ auth()->check() ? route('mentions.users') : '' }}"
          @if($themeLayout)
            style="@if($themeLayout->layout_bg_color)background: {{ $themeLayout->layout_bg_color }};@endif
                   @if($themeLayout->global_shadow) --shadow: {{ $themeLayout->global_shadow }}; @endif
                   @if($themeLayout->header_shadow) --header-shadow: {{ $themeLayout->header_shadow }}; @endif
                   @if($themeLayout->dark_bg_color) --bg-dark: {{ $themeLayout->dark_bg_color }}; @endif
                   @if($themeLayout->dark_surface_color) --surface-dark: {{ $themeLayout->dark_surface_color }}; @endif
                   @if($themeLayout->dark_surface2_color) --surface2-dark: {{ $themeLayout->dark_surface2_color }}; @endif
                   @if($themeLayout->dark_text_color) --text-dark: {{ $themeLayout->dark_text_color }}; @endif
                   @if($themeLayout->dark_muted_color) --muted-dark: {{ $themeLayout->dark_muted_color }}; @endif
                   @if($themeLayout->dark_border_color) --border-dark: {{ $themeLayout->dark_border_color }}; @endif
                   @if($themeLayout->dark_primary_color) --primary-dark: {{ $themeLayout->dark_primary_color }}; @endif"
          @endif>
        @include('partials.preloader')
        @include('partials.toasts')
        <div class="min-h-screen">
            @if ($authSingle)
                <div class="auth-single-shell">
                    {{ $slot }}
                </div>
            @else
                @include('header')
                @include('partials.pwa-install-banner')

                @php
                    $mw = $themeLayout?->layout_max_width_custom ?: $themeLayout?->layout_max_width;
                    $px = $themeLayout?->layout_padding_x_custom ?: $themeLayout?->layout_padding_x;
                    $py = $themeLayout?->layout_padding_y_custom ?: $themeLayout?->layout_padding_y;
                    $gap = $themeLayout?->layout_gap_custom ?: $themeLayout?->layout_gap;
                    $lw = $themeLayout?->layout_left_width_custom ?: $themeLayout?->layout_left_width;
                    $mwCol = $themeLayout?->layout_main_width_custom ?: $themeLayout?->layout_main_width;
                    $rw = $themeLayout?->layout_right_width_custom ?: $themeLayout?->layout_right_width;
                @endphp
                <main class="community-shell mx-auto w-full px-4 pb-6 pt-6"
                      @if($themeLayout)
                        style="@if($mw)max-width: {{ $mw }};@endif
                               @if($px)padding-left: {{ $px }};padding-right: {{ $px }};@endif
                               @if($py)padding-top: {{ $py }};padding-bottom: {{ $py }};@endif"
                      @endif>
                    <div class="grid grid-cols-12 gap-6 community-grid"
                         @if($themeLayout)
                            style="@if($gap)gap: {{ $gap }};@endif
                                   @if($lw || $mwCol || $rw)
                                       @if($lw) --layout-left: {{ $lw }}; @endif
                                       @if($mwCol) --layout-main: {{ $mwCol }}; @endif
                                       @if($rw) --layout-right: {{ $rw }}; @endif
                                   @endif"
                         @endif>
                        <section class="layout-main lg:col-span-12 guest-content" @if($themeLayout?->main_column_bg_color) style="background: {{ $themeLayout->main_column_bg_color }};" @endif>
                            @include('partials.community-feed')
                            <x-page-builder />
                            {{ $slot }}
                        </section>
                    </div>
                </main>
            @endif
        </div>

        @unless (request()->routeIs('filament.*') || $authSingle)
            <x-mobile-bottom-nav />
        @endunless

        <x-cookie-banner />

    @include('partials.google-one-tap')
    @include('partials.mention-assets')
    @include('partials.external-link-bridge')
    @include('partials.image-lightbox')
    @stack('scripts')
    @livewireScripts
        @include('partials.pwa-scripts')
</body>
</html>

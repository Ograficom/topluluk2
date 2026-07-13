@extends('layouts.app')

@section('title', 'Video')
@section('meta_description', 'Ografi video sayfasında reels ve shorts tarzı kısa videoları keşfet.')
@section('hide_feed_header', '1')
@push('head')
    <script>
        document.documentElement.classList.add('ografi-video-page');
    </script>

    <style>

        /* /video sayfasına özel: mobilde layout header, alt menü ve yan boşlukları tamamen kaldırır */
        @media (max-width: 1023px) {
            html.ografi-video-page,
            html.ografi-video-page body,
            body.ografi-video-page-ready {
                width: 100vw !important;
                max-width: 100vw !important;
                height: 100svh !important;
                min-height: 100svh !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                background: #000 !important;
            }

            body.ografi-video-page-ready > *:not(#reelsPage):not([data-mobile-bottom-nav]):not([data-mobile-login-drawer]):not(script):not(style):not(link):not(meta):not(title) {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }

            body.ografi-video-page-ready [data-mobile-bottom-nav] {
                z-index: 2147483647 !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                pointer-events: auto !important;
            }

            body.ografi-video-page-ready [data-mobile-login-drawer] {
                z-index: 2147483647 !important;
            }

            body.ografi-video-page-ready #reelsPage,
            html.ografi-video-page #reelsPage {
                position: fixed !important;
                inset: 0 !important;
                z-index: 2147483647 !important;
                width: 100vw !important;
                max-width: 100vw !important;
                height: 100svh !important;
                min-height: 100svh !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                overflow: hidden !important;
                background: #000 !important;
                transform: none !important;
            }

            body.ografi-video-page-ready #reelsPage .reels-shell,
            body.ografi-video-page-ready #reelsPage .reels-feed,
            body.ografi-video-page-ready #reelsPage .reel-slide,
            body.ografi-video-page-ready #reelsPage .reel-phone,
            html.ografi-video-page #reelsPage .reels-shell,
            html.ografi-video-page #reelsPage .reels-feed,
            html.ografi-video-page #reelsPage .reel-slide,
            html.ografi-video-page #reelsPage .reel-phone {
                width: 100vw !important;
                max-width: 100vw !important;
                height: 100svh !important;
                min-height: 100svh !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: #000 !important;
            }

            body.ografi-video-page-ready #reelsPage .reel-slide {
                align-items: stretch !important;
                justify-content: stretch !important;
            }

            body.ografi-video-page-ready #reelsPage .reels-desktop-nav,
            body.ografi-video-page-ready #reelsPage [data-reel-share] {
                display: none !important;
            }
        }

        /* Paylaş ikonundaki beyaz kutuyu kesin kapatır/eski cache kalsa bile görünmez */
        #reelsPage [data-reel-share],
        #reelsPage .reel-actions > button[data-reel-share],
        #reelsPage .reel-actions > .reel-action[data-reel-share] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        #reelsPage .reel-action,
        #reelsPage .reel-action-btn,
        #reelsPage .reel-action button,
        #reelsPage .reel-action[type="button"] {
            -webkit-appearance: none !important;
            appearance: none !important;
        }

        #reelsPage .reel-action-btn {
            background: rgba(0, 0, 0, .42) !important;
            color: #fff !important;
        }

        :root {
            --video-header-height: var(--site-header-height, 0px);
            --video-page-height: calc(100svh - var(--video-header-height));
            --video-safe-top: max(12px, env(safe-area-inset-top));
            --video-safe-bottom: max(16px, env(safe-area-inset-bottom));
            --video-pink: #ff2f55;
            --video-blue: #2563eb;
        }

        .reels-page {
            position: relative;
            width: 100%;
            height: var(--video-page-height);
            min-height: var(--video-page-height);
            overflow: hidden;
            background: #000;
            color: #fff;
        }

        .reels-shell {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .reels-feed {
            width: 100%;
            height: 100%;
            overflow-y: auto;
            scroll-snap-type: y mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            background: #000;
        }

        .reels-feed::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .reel-slide {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 100%;
            overflow: hidden;
            scroll-snap-align: start;
            scroll-snap-stop: always;
            background: #000;
            display: flex;
            justify-content: center;
            align-items: stretch;
        }

        .reel-phone {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #020617;
            --reel-fit-mode: cover;
        }

        .reel-media,
        .reel-video,
        .reel-image,
        .reel-fallback {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .reel-video,
        .reel-image {
            object-fit: var(--reel-fit-mode, cover);
            object-position: center;
            background: #000;
        }

        .reel-embed,
        .reel-embed-frame {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
            background: #000;
        }

        .reel-embed {
            overflow: hidden;
        }

        .reel-embed-frame {
            display: block;
        }

        .reel-slide.is-embed .reel-phone {
            background: #000;
        }

        .reel-slide.is-embed .reel-progress {
            display: none;
        }

        .reel-slide.is-landscape .reel-video,
        .reel-slide.is-landscape .reel-image,
        .reel-slide.is-square .reel-video,
        .reel-slide.is-square .reel-image {
            object-fit: contain;
            background: #000;
        }

        .reel-fallback {
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 20% 15%, rgba(255, 255, 255, .12), transparent 26%),
                radial-gradient(circle at 80% 85%, rgba(37, 99, 235, .28), transparent 32%),
                linear-gradient(180deg, #111827 0%, #020617 100%);
        }

        .reel-fallback__inner {
            width: min(76%, 300px);
            text-align: center;
            color: #fff;
        }

        .reel-fallback__icon {
            width: 78px;
            height: 78px;
            margin: 0 auto 14px;
            border-radius: 9999px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .16);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .reel-fallback__icon svg {
            width: 34px;
            height: 34px;
        }

        .reel-fallback__title {
            font-size: 1rem;
            line-height: 1.35;
            color: rgba(255, 255, 255, .92);
        }

        .reel-overlay {
            position: absolute;
            inset: 0;
            z-index: 5;
            pointer-events: none;
            background:
                linear-gradient(to bottom, rgba(0, 0, 0, .42) 0%, rgba(0, 0, 0, .12) 18%, rgba(0, 0, 0, 0) 38%),
                linear-gradient(to top, rgba(0, 0, 0, .92) 0%, rgba(0, 0, 0, .62) 24%, rgba(0, 0, 0, .16) 50%, rgba(0, 0, 0, 0) 70%);
        }

        .reels-topbar {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            z-index: 80;
            padding: var(--video-safe-top) 14px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            pointer-events: none;
        }

        .reels-topbar > * {
            pointer-events: auto;
        }

        .reels-top-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .reels-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            height: 44px;
            padding: 5px 13px 5px 6px;
            border-radius: 9999px;
            background: linear-gradient(135deg, rgba(0, 0, 0, .76), rgba(15, 23, 42, .58));
            border: 1px solid rgba(255, 255, 255, .16);
            color: #fff;
            text-decoration: none;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 14px 34px rgba(0, 0, 0, .28);
            transition: background-color .18s ease, transform .18s ease, border-color .18s ease;
        }

        .reels-brand:hover,
        .reels-brand:focus-within {
            background: linear-gradient(135deg, rgba(0, 0, 0, .90), rgba(15, 23, 42, .72));
            border-color: rgba(255, 255, 255, .24);
        }

        .reels-brand__home {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: inherit;
            text-decoration: none;
            min-width: 0;
        }

        .reels-brand__logo {
            width: 34px;
            height: 34px;
            border-radius: 9999px;
            object-fit: cover;
            background: #050505;
            border: 1px solid rgba(255, 255, 255, .20);
            padding: 4px;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .06), 0 8px 20px rgba(0, 0, 0, .22);
        }

        .reels-brand__text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .reels-brand__title {
            color: #fff;
            font-size: 14px;
            line-height: 1;
            letter-spacing: .01em;
        }

        .reels-brand__sub {
            color: rgba(255, 255, 255, .72);
            font-size: 10px;
            line-height: 1;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .reels-brand__fullscreen {
            width: 26px;
            height: 26px;
            border: 0;
            border-radius: 9999px;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .92);
            color: #111827;
            cursor: pointer;
            margin-left: 2px;
            transition: background-color .18s ease, transform .18s ease;
        }

        .reels-brand__fullscreen:hover,
        .reels-brand__fullscreen:focus-visible {
            background: #fff;
            transform: scale(1.04);
        }

        .reels-brand__fullscreen svg {
            width: 17px;
            height: 17px;
            display: block;
        }

        .reels-page.is-fullscreen .reels-brand__fullscreen {
            display: inline-flex;
        }

        .reels-tabs {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px;
            border-radius: 9999px;
            background: rgba(0, 0, 0, .24);
            border: 1px solid rgba(255, 255, 255, .12);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .reels-tab {
            height: 34px;
            padding: 0 13px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: transparent;
            color: rgba(255, 255, 255, .72);
            font-size: 13px;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: background-color .18s ease, color .18s ease;
        }

        .reels-tab:hover,
        .reels-tab:focus-visible {
            background: rgba(255, 255, 255, .12);
            color: #fff;
        }

        .reels-tab.is-active {
            background: rgba(255, 255, 255, .94);
            color: #0f172a;
        }

        .reels-top-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reels-top-btn {
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(0, 0, 0, .24);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            transition: background-color .18s ease, transform .18s ease;
        }

        .reels-top-btn:hover,
        .reels-top-btn:focus-visible {
            background: rgba(0, 0, 0, .42);
            transform: translateY(-1px);
        }

        .reels-top-btn svg {
            width: 20px;
            height: 20px;
        }

        .reels-user-menu {
            position: relative;
            z-index: 120;
        }

        .reels-user-trigger {
            width: 40px;
            height: 40px;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 9999px;
            background: rgba(0, 0, 0, .24);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            transition: background-color .18s ease, transform .18s ease;
        }

        .reels-user-trigger:hover,
        .reels-user-trigger:focus-visible,
        .reels-user-menu.is-open .reels-user-trigger {
            background: rgba(0, 0, 0, .44);
            transform: translateY(-1px);
        }

        .reels-user-trigger img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .reels-user-trigger svg {
            width: 20px;
            height: 20px;
        }

        .reels-user-initial {
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 14px;
            background: linear-gradient(135deg, #2563eb, #ff2f55);
        }

        .reels-user-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: 220px;
            padding: 8px;
            border-radius: 18px;
            background: rgba(15, 23, 42, .96);
            border: 1px solid rgba(255, 255, 255, .12);
            box-shadow: 0 24px 60px rgba(0, 0, 0, .42);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity .16s ease, visibility .16s ease, transform .16s ease;
        }

        .reels-user-menu.is-open .reels-user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .reels-menu-item {
            width: 100%;
            min-height: 42px;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 10px;
            border-radius: 13px;
            color: rgba(255, 255, 255, .88);
            text-decoration: none;
            font-size: 13px;
            line-height: 1.2;
            transition: background-color .16s ease, color .16s ease, transform .16s ease;
        }

        .reels-menu-item:hover,
        .reels-menu-item:focus-visible,
        .reels-menu-item:active {
            background: rgba(255, 255, 255, .10);
            color: #fff;
            transform: translateX(2px);
        }

        .reels-menu-item__icon {
            width: 30px;
            height: 30px;
            border-radius: 9999px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            background: rgba(255, 255, 255, .09);
            color: #fff;
        }

        .reels-menu-item__icon svg {
            width: 16px;
            height: 16px;
        }

        .reels-menu-divider {
            height: 1px;
            margin: 7px 4px;
            background: rgba(255, 255, 255, .10);
        }

        .reel-mute {
            position: absolute;
            left: 14px;
            top: calc(var(--video-safe-top) + 58px);
            z-index: 28;
            width: 42px;
            height: 42px;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(0, 0, 0, .30);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, .26);
            transition: background-color .18s ease, transform .18s ease, opacity .18s ease;
        }

        .reel-mute:hover,
        .reel-mute:focus-visible {
            background: rgba(255, 255, 255, .16);
            transform: translateY(-2px) scale(1.03);
        }

        .reel-mute svg {
            width: 18px;
            height: 18px;
        }

        .reel-mute .icon-sound {
            display: none;
        }

        .reel-mute.is-unmuted .icon-muted {
            display: none;
        }

        .reel-mute.is-unmuted .icon-sound {
            display: block;
        }

        .reel-mute.is-disabled {
            opacity: .52;
            cursor: default;
            pointer-events: none;
        }

        .reel-actions {
            position: absolute;
            right: 12px;
            bottom: calc(var(--video-safe-bottom) + 94px);
            z-index: 30;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .reel-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: #fff;
            text-decoration: none;
        }

        .reel-action form {
            margin: 0;
        }

        .reel-action-btn {
            width: 48px;
            height: 48px;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(0, 0, 0, .30);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, .26);
            transition: background-color .18s ease, transform .18s ease, color .18s ease;
        }

        .reel-action-btn:hover,
        .reel-action-btn:focus-visible {
            background: rgba(255, 255, 255, .16);
            transform: translateY(-2px) scale(1.03);
        }

        .reel-action-btn svg {
            width: 23px;
            height: 23px;
        }

        .reel-action-btn.is-like {
            color: #ff3b5f;
        }

        .reel-action-btn.is-bookmark {
            color: #facc15;
        }

        .reel-action-count,
        .reel-action-label {
            color: #fff;
            font-size: 11px;
            line-height: 1.1;
            text-shadow: 0 2px 8px rgba(0, 0, 0, .60);
        }

        .reel-author-mini {
            width: 48px;
            height: 58px;
            position: relative;
            display: inline-flex;
            align-items: flex-start;
            justify-content: center;
            text-decoration: none;
        }

        .reel-author-mini__avatar {
            width: 46px;
            height: 46px;
            border-radius: 9999px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, .92);
            background: rgba(255, 255, 255, .16);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 13px;
        }

        .reel-author-mini__avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .reel-author-mini__plus {
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 23px;
            height: 23px;
            border-radius: 9999px;
            background: var(--video-pink);
            color: #fff;
            display: grid;
            place-items: center;
            border: 2px solid #000;
        }

        .reel-author-mini__plus svg {
            width: 13px;
            height: 13px;
        }

        .reel-content {
            position: absolute;
            left: 0;
            right: 72px;
            bottom: 0;
            z-index: 28;
            padding: 0 16px calc(var(--video-safe-bottom) + 8px);
            color: #fff;
        }

        .reel-author-row {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            margin-bottom: 10px;
        }

        .reel-author-avatar {
            width: 42px;
            height: 42px;
            border-radius: 9999px;
            overflow: hidden;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border: 1px solid rgba(255, 255, 255, .34);
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-size: 13px;
        }

        .reel-author-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .reel-author-name {
            min-width: 0;
            max-width: 100%;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-shadow: 0 2px 12px rgba(0, 0, 0, .60);
        }

        .reel-follow-btn {
            flex: 0 0 auto;
            height: 28px;
            padding: 0 12px;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, .68);
            background: rgba(255, 255, 255, .08);
            color: #fff;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: background-color .18s ease, color .18s ease;
        }

        .reel-follow-btn:hover {
            background: #fff;
            color: #020617;
        }

        .reel-title {
            margin: 0;
            max-width: 100%;
            color: #fff;
            font-size: 14px;
            line-height: 1.45;
            text-shadow: 0 2px 12px rgba(0, 0, 0, .60);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .reel-summary {
            margin: 6px 0 0;
            max-width: 100%;
            color: rgba(255, 255, 255, .82);
            font-size: 13px;
            line-height: 1.42;
            text-shadow: 0 2px 12px rgba(0, 0, 0, .60);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }



        .reel-continue-btn {
            margin-top: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 14px;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, .20);
            background: rgba(0, 0, 0, .42);
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            line-height: 1;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .reel-continue-btn:hover,
        .reel-continue-btn:focus-visible {
            background: rgba(255, 255, 255, .14);
            color: #fff;
        }

        .reel-audio {
            margin-top: 9px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 100%;
            height: 30px;
            padding: 0 11px;
            border-radius: 9999px;
            background: rgba(0, 0, 0, .24);
            color: rgba(255, 255, 255, .88);
            font-size: 12px;
            line-height: 1;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .reel-audio svg {
            width: 14px;
            height: 14px;
            flex: 0 0 auto;
        }

        .reel-progress {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 36;
            height: 4px;
            background: rgba(255, 255, 255, .18);
            overflow: hidden;
        }

        .reel-progress span {
            display: block;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, #fff, var(--video-pink));
            transition: width .12s linear;
        }

        .reels-desktop-nav {
            display: none;
        }

        .reels-nav-btn {
            width: 48px;
            height: 48px;
            border: 0;
            border-radius: 9999px;
            background: rgba(255, 255, 255, .92);
            color: #111827;
            display: grid;
            place-items: center;
            cursor: pointer;
            box-shadow: 0 10px 26px rgba(0, 0, 0, .18);
            transition: background-color .18s ease, transform .18s ease, color .18s ease;
        }

        .reels-nav-btn:hover {
            background: #ffffff;
            transform: scale(1.05);
        }

        .reels-nav-btn svg {
            width: 22px;
            height: 22px;
        }

        .reels-nav-btn--fullscreen svg {
            width: 20px;
            height: 20px;
        }

        .reels-empty {
            height: 100%;
            min-height: 100%;
            display: grid;
            place-items: center;
            padding: 24px;
            text-align: center;
            color: rgba(255, 255, 255, .74);
            background: #020617;
        }

        .reels-empty-card {
            width: min(100%, 360px);
            border-radius: 28px;
            padding: 26px 22px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .reels-empty-icon {
            width: 74px;
            height: 74px;
            margin: 0 auto 16px;
            border-radius: 9999px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .10);
            color: #fff;
        }

        .reels-empty-icon svg {
            width: 34px;
            height: 34px;
        }

        .reels-empty strong {
            display: block;
            color: #fff;
            font-size: 1.12rem;
            line-height: 1.3;
        }

        .reels-empty p {
            margin: 8px 0 18px;
            font-size: 14px;
            line-height: 1.55;
        }

        .reels-empty a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            padding: 0 16px;
            border-radius: 9999px;
            color: #fff;
            background: var(--video-blue);
            text-decoration: none;
            font-size: 14px;
        }

        .reels-icon {
            display: block;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .reels-fullscreen-icon {
            display: block;
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .reels-page.is-fullscreen {
            height: 100svh !important;
            min-height: 100svh !important;
            background: #000 !important;
        }

        .reels-page.is-fullscreen .reel-slide {
            padding: 0 !important;
            background: #000 !important;
        }

        .reels-page.is-fullscreen .reel-phone {
            width: 100vw !important;
            height: 100svh !important;
            max-width: 100vw !important;
            max-height: 100svh !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .reels-page.is-fullscreen .reel-video,
        .reels-page.is-fullscreen .reel-image {
            object-fit: contain !important;
            background: #000 !important;
        }

        .reels-page.is-fullscreen .reels-desktop-nav {
            display: none !important;
        }

        .reels-page.is-fullscreen .reel-actions {
            right: 18px !important;
            left: auto !important;
            bottom: calc(var(--video-safe-bottom) + 100px) !important;
        }

        .reels-page.is-fullscreen .reel-content {
            right: 84px !important;
            left: 0 !important;
            bottom: 0 !important;
            max-width: none !important;
        }



        .reels-tabs,
        .reels-tab {
            display: none !important;
        }

        @media (min-width: 1024px) {
            .reels-page {
                background:
                    radial-gradient(circle at left center, rgba(37, 99, 235, .16), transparent 34%),
                    radial-gradient(circle at right bottom, rgba(255, 47, 85, .14), transparent 34%),
                    #020617;
            }

            .reels-feed {
                background: transparent;
            }

            .reel-slide {
                padding: 18px 118px 18px 24px;
                background: transparent;
                align-items: center;
            }

            .reel-phone {
                width: var(--reel-stage-width, min(430px, calc(100vw - 180px)));
                height: var(--reel-stage-height, calc(100svh - var(--video-header-height) - 36px));
                max-width: calc(100vw - 180px);
                max-height: calc(100svh - var(--video-header-height) - 36px);
                margin: auto;
                border-radius: 28px;
                box-shadow:
                    0 32px 90px rgba(0, 0, 0, .58),
                    0 0 0 1px rgba(255, 255, 255, .10);
            }

            .reel-slide.is-landscape .reel-phone {
                width: min(calc(100vw - 170px), calc((100svh - var(--video-header-height) - 36px) * var(--reel-aspect-ratio, 1.777))) !important;
                height: min(calc(100svh - var(--video-header-height) - 36px), calc((100vw - 170px) / var(--reel-aspect-ratio, 1.777))) !important;
                border-radius: 22px;
            }

            .reel-slide.is-square .reel-phone {
                border-radius: 24px;
            }

            .reel-slide.is-landscape .reel-video,
            .reel-slide.is-landscape .reel-image {
                object-fit: contain !important;
            }

            .reel-actions {
                left: calc(50% + (var(--reel-stage-width, 430px) / 2) + 18px);
                right: auto;
                bottom: 64px;
            }

            .reels-desktop-nav {
                position: fixed;
                right: 18px;
                top: 50%;
                z-index: 60;
                transform: translateY(-50%);
                display: flex;
                flex-direction: column;
                gap: 14px;
            }

            .reel-content {
                right: 74px;
                padding-left: 18px;
                padding-right: 18px;
            }

            .reel-slide.is-landscape .reel-content {
                right: 86px;
                max-width: 58%;
            }

            .reel-slide.is-landscape .reel-title,
            .reel-slide.is-landscape .reel-summary {
                max-width: 100%;
            }

            .reel-slide.is-landscape .reel-actions {
                bottom: 46px;
            }
        }

        @media (max-width: 1023px) {
            .reel-slide.is-landscape .reel-video,
            .reel-slide.is-landscape .reel-image,
            .reel-slide.is-square .reel-video,
            .reel-slide.is-square .reel-image {
                object-fit: contain;
            }

            .reel-slide.is-landscape .reel-content,
            .reel-slide.is-square .reel-content {
                right: 68px;
                bottom: 0;
            }
        }

        @media (max-width: 640px) {
            .reels-topbar {
                align-items: flex-start;
                gap: 8px;
                padding-left: 10px;
                padding-right: 10px;
            }

            .reels-top-left {
                gap: 8px;
                flex: 1;
                min-width: 0;
            }

            .reels-brand {
                height: 40px;
                padding-right: 9px;
                flex: 0 0 auto;
            }

            .reels-brand__logo {
                width: 30px;
                height: 30px;
            }

            .reels-brand__title {
                font-size: 13px;
            }

            .reels-brand__sub {
                display: none;
            }

            .reels-brand__fullscreen {
                width: 24px;
                height: 24px;
            }

            .reels-brand__fullscreen svg {
                width: 16px;
                height: 16px;
            }

            .reels-tabs {
                gap: 4px;
                padding: 3px;
                max-width: 100%;
                overflow: hidden;
            }

            .reels-tab {
                height: 32px;
                padding: 0 10px;
                font-size: 12px;
            }

            .reels-user-dropdown {
                width: 210px;
                right: -2px;
            }
        }

        @media (max-width: 480px) {
            .reel-actions {
                right: 10px;
                gap: 13px;
            }

            .reel-action-btn {
                width: 46px;
                height: 46px;
            }

            .reel-content {
                right: 68px;
                padding-left: 14px;
            }

            .reels-top-btn {
                width: 38px;
                height: 38px;
            }

            .reels-user-trigger {
                width: 38px;
                height: 38px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $viewer = auth()->user();
        $composeUrl = $viewer ? route('blog.create') : route('login');

        $brandLogoUrl = asset('images/ografi-logo.png') . '?v=20260714';

        $userProfileUrl = '#';

        if ($viewer && !empty($viewer->username)) {
            try {
                $userProfileUrl = route('profile.show', $viewer->username);
            } catch (\Throwable $e) {
                $userProfileUrl = '#';
            }
        }

        $userAvatarUrl = $viewer?->profile_photo_url
            ?? $viewer?->avatar
            ?? $viewer?->profile_photo_path
            ?? null;

        $userInitial = $viewer
            ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($viewer->name ?? $viewer->username ?? 'U', 0, 1))
            : null;

        $dashboardUrl = \Illuminate\Support\Facades\Route::has('dashboard')
            ? route('dashboard')
            : url('/dashboard');

        $videoUrl = url('/video');

        $savedUrl = \Illuminate\Support\Facades\Route::has('bookmarks.index')
            ? route('bookmarks.index')
            : url('/bookmarks');

        $defaultReactionType = \App\Models\ReactionType::query()
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN short_code = 'like' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first(['id', 'short_code']);

        $formatShortCount = function ($value): string {
            $value = (int) $value;

            if ($value >= 1000000) {
                return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.') . 'M';
            }

            if ($value >= 1000) {
                return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'K';
            }

            return number_format($value);
        };

        $normalizeMediaUrl = function (?string $value): ?string {
            $value = trim((string) $value);

            if ($value === '') {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($value, ['http://', 'https://', '//', 'data:'])) {
                return $value;
            }

            if (\Illuminate\Support\Str::startsWith($value, '/storage/')) {
                return url($value);
            }

            if (\Illuminate\Support\Str::startsWith($value, 'storage/')) {
                return asset($value);
            }

            if (\Illuminate\Support\Str::startsWith($value, ['/'])) {
                return url($value);
            }

            return asset($value);
        };

        $sanitizeEmbedUrl = function (?string $url): ?string {
            $url = trim((string) $url);

            if ($url === '') {
                return null;
            }

            $parts = parse_url($url);

            if (!is_array($parts)) {
                return null;
            }

            $scheme = strtolower((string) ($parts['scheme'] ?? ''));

            if (!in_array($scheme, ['http', 'https'], true)) {
                return null;
            }

            $host = strtolower((string) ($parts['host'] ?? ''));

            $allowedHosts = [
                'www.youtube.com',
                'youtube.com',
                'www.youtube-nocookie.com',
                'youtube-nocookie.com',
                'youtu.be',
                'www.tiktok.com',
                'tiktok.com',
                'player.vimeo.com',
                'www.vimeo.com',
                'vimeo.com',
                'www.dailymotion.com',
                'dailymotion.com',
                'dai.ly',
                'www.facebook.com',
                'facebook.com',
                'web.facebook.com',
                'm.facebook.com',
                'mobile.facebook.com',
                'fb.watch',
                'www.fb.watch',
            ];

            return in_array($host, $allowedHosts, true) ? $url : null;
        };

        $buildEmbedUrlFromUrl = function (?string $value) use ($sanitizeEmbedUrl): ?string {
            $value = trim((string) $value);

            if ($value === '') {
                return null;
            }

            if (!preg_match('#^https?://#i', $value)) {
                $value = 'https://' . ltrim($value, '/');
            }

            $parts = parse_url($value);

            if (!is_array($parts)) {
                return null;
            }

            $host = strtolower((string) ($parts['host'] ?? ''));
            $path = (string) ($parts['path'] ?? '/');
            $pathParts = array_values(array_filter(explode('/', trim($path, '/'))));
            parse_str((string) ($parts['query'] ?? ''), $query);

            if ($host === 'youtu.be') {
                $id = $pathParts[0] ?? null;

                return $id ? $sanitizeEmbedUrl('https://www.youtube.com/embed/' . rawurlencode($id)) : null;
            }

            if (str_ends_with($host, 'youtube.com') || str_ends_with($host, 'youtube-nocookie.com')) {
                $id = null;

                if (($pathParts[0] ?? '') === 'watch') {
                    $id = (string) ($query['v'] ?? '');
                } elseif (in_array(($pathParts[0] ?? ''), ['shorts', 'embed'], true)) {
                    $id = $pathParts[1] ?? null;
                }

                return $id ? $sanitizeEmbedUrl('https://www.youtube.com/embed/' . rawurlencode($id)) : null;
            }

            if (str_ends_with($host, 'tiktok.com')) {
                if (($pathParts[0] ?? '') === 'embed' && ($pathParts[1] ?? '') === 'v2' && !empty($pathParts[2])) {
                    return $sanitizeEmbedUrl('https://www.tiktok.com/embed/v2/' . rawurlencode((string) $pathParts[2]));
                }

                if (($pathParts[1] ?? '') === 'video' && !empty($pathParts[2])) {
                    return $sanitizeEmbedUrl('https://www.tiktok.com/embed/v2/' . rawurlencode((string) $pathParts[2]));
                }
            }

            if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
                $id = (($pathParts[0] ?? '') === 'video') ? ($pathParts[1] ?? null) : ($pathParts[0] ?? null);

                return ($id && preg_match('/^\d+$/', $id))
                    ? $sanitizeEmbedUrl('https://player.vimeo.com/video/' . $id)
                    : null;
            }

            if (str_ends_with($host, 'dailymotion.com')) {
                $id = (($pathParts[0] ?? '') === 'video') ? ($pathParts[1] ?? null) : null;

                return $id ? $sanitizeEmbedUrl('https://www.dailymotion.com/embed/video/' . rawurlencode($id)) : null;
            }

            if ($host === 'dai.ly') {
                $id = $pathParts[0] ?? null;

                return $id ? $sanitizeEmbedUrl('https://www.dailymotion.com/embed/video/' . rawurlencode($id)) : null;
            }

            if (str_ends_with($host, 'facebook.com') || str_ends_with($host, 'fb.watch')) {
                return $sanitizeEmbedUrl('https://www.facebook.com/plugins/video.php?href=' . rawurlencode($value) . '&show_text=false&width=500');
            }

            return null;
        };

        $describeEmbedMedia = function (?string $sourceUrl, ?string $embedUrl): array {
            $sourceUrl = trim((string) $sourceUrl);
            $embedUrl = trim((string) $embedUrl);
            $candidate = $sourceUrl !== '' ? $sourceUrl : $embedUrl;
            $host = strtolower((string) parse_url($candidate, PHP_URL_HOST));

            $label = 'Video';
            $thumbnail = null;

            if (str_contains($host, 'youtube') || $host === 'youtu.be') {
                $label = 'YouTube';

                if (preg_match('#(?:youtube(?:-nocookie)?\.com/embed/|youtu\.be/)([^?&/]+)#i', $candidate, $matches)) {
                    $thumbnail = 'https://i.ytimg.com/vi/' . rawurlencode((string) $matches[1]) . '/hqdefault.jpg';
                } elseif (preg_match('/[?&]v=([^&]+)/i', $candidate, $matches)) {
                    $thumbnail = 'https://i.ytimg.com/vi/' . rawurlencode((string) $matches[1]) . '/hqdefault.jpg';
                }
            } elseif (str_contains($host, 'tiktok')) {
                $label = 'TikTok';
            } elseif (str_contains($host, 'vimeo')) {
                $label = 'Vimeo';
            } elseif (str_contains($host, 'dailymotion') || str_contains($host, 'dai.ly')) {
                $label = 'Dailymotion';
            } elseif (str_contains($host, 'facebook') || str_contains($host, 'fb.watch')) {
                $label = 'Facebook';
            }

            return [
                'label' => $label,
                'source_url' => $sourceUrl !== '' ? $sourceUrl : ($embedUrl !== '' ? $embedUrl : null),
                'thumbnail' => $thumbnail,
            ];
        };

        $extractShortMedia = function ($post) use ($normalizeMediaUrl, $sanitizeEmbedUrl, $buildEmbedUrlFromUrl, $describeEmbedMedia) {
            $blocks = collect(is_array($post->content_json) ? ($post->content_json['blocks'] ?? []) : []);

            foreach ($blocks as $block) {
                if (!is_array($block)) {
                    continue;
                }

                $type = (string) ($block['type'] ?? '');
                $data = is_array($block['data'] ?? null) ? $block['data'] : [];

                if ($type === 'video') {
                    $rawVideoUrl = $data['url'] ?? data_get($data, 'file.url') ?? data_get($data, 'video.url') ?? null;
                    $videoUrl = $normalizeMediaUrl($rawVideoUrl);

                    if ($videoUrl) {
                        return [
                            'kind' => 'video',
                            'video' => [
                                'url' => $videoUrl,
                                'type' => $data['type'] ?? data_get($data, 'file.mime') ?? data_get($data, 'video.type') ?? 'video/mp4',
                                'subtitles' => $data['subtitles'] ?? [],
                            ],
                        ];
                    }
                }

                foreach ([
                    $data['src'] ?? null,
                    $data['embed'] ?? null,
                    $data['source'] ?? null,
                    $data['url'] ?? null,
                    data_get($data, 'file.url'),
                    data_get($data, 'video.url'),
                    data_get($data, 'meta.source'),
                ] as $candidate) {
                    $candidate = trim((string) $candidate);

                    if ($candidate === '') {
                        continue;
                    }

                    if (preg_match('/\.(mp4|webm|mov|m4v|ogv)(\?.*)?$/i', $candidate)) {
                        $videoUrl = $normalizeMediaUrl($candidate);

                        if ($videoUrl) {
                            return [
                                'kind' => 'video',
                                'video' => [
                                    'url' => $videoUrl,
                                    'type' => 'video/mp4',
                                    'subtitles' => $data['subtitles'] ?? [],
                                ],
                            ];
                        }
                    }

                    $embedUrl = $buildEmbedUrlFromUrl($candidate) ?? $sanitizeEmbedUrl($candidate);

                    if ($embedUrl) {
                        return [
                            'kind' => 'embed',
                            'url' => $embedUrl,
                        ] + $describeEmbedMedia($candidate, $embedUrl);
                    }
                }
            }

            $content = (string) ($post->content ?? '');

            foreach ([
                '/<video\b[^>]*src=["\']([^"\']+)["\']/i',
                '/<source\b[^>]*src=["\']([^"\']+)["\']/i',
            ] as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    $videoUrl = $normalizeMediaUrl($matches[1] ?? null);

                    if ($videoUrl) {
                        return [
                            'kind' => 'video',
                            'video' => [
                                'url' => $videoUrl,
                                'type' => 'video/mp4',
                                'subtitles' => [],
                            ],
                        ];
                    }
                }
            }

            if (preg_match('/<iframe\b[^>]*src=["\']([^"\']+)["\']/i', $content, $matches)) {
                $embedUrl = $buildEmbedUrlFromUrl($matches[1] ?? null) ?? $sanitizeEmbedUrl($matches[1] ?? null);

                if ($embedUrl) {
                    return [
                        'kind' => 'embed',
                        'url' => $embedUrl,
                    ] + $describeEmbedMedia($matches[1] ?? null, $embedUrl);
                }
            }

            if (preg_match_all('#https?://[^\s"\']+#i', $content, $matches)) {
                foreach (($matches[0] ?? []) as $candidate) {
                    if (preg_match('/\.(mp4|webm|mov|m4v|ogv)(\?.*)?$/i', $candidate)) {
                        $videoUrl = $normalizeMediaUrl($candidate);

                        if ($videoUrl) {
                            return [
                                'kind' => 'video',
                                'video' => [
                                    'url' => $videoUrl,
                                    'type' => 'video/mp4',
                                    'subtitles' => [],
                                ],
                            ];
                        }
                    }

                    $embedUrl = $buildEmbedUrlFromUrl($candidate) ?? $sanitizeEmbedUrl($candidate);

                    if ($embedUrl) {
                        return [
                            'kind' => 'embed',
                            'url' => $embedUrl,
                        ] + $describeEmbedMedia($candidate, $embedUrl);
                    }
                }
            }

            return null;
        };

        $buildSummary = function ($post): string {
            $summary = trim(strip_tags((string) ($post->excerpt ?? '')));

            if ($summary === '' && is_array($post->content_json)) {
                $summary = collect($post->content_json['blocks'] ?? [])
                    ->map(function ($block) {
                        $type = $block['type'] ?? null;
                        $data = $block['data'] ?? [];

                        return match ($type) {
                            'paragraph', 'header', 'quote' => trim(strip_tags((string) ($data['text'] ?? ''))),
                            'list' => collect($data['items'] ?? [])->flatten()->implode(' '),
                            'checklist' => collect($data['items'] ?? [])->pluck('text')->implode(' '),
                            default => trim(strip_tags((string) ($data['caption'] ?? ''))),
                        };
                    })
                    ->filter()
                    ->implode(' ');
            }

            if ($summary === '') {
                $summary = trim(strip_tags((string) ($post->content ?? '')));
            }

            $summary = preg_replace('/\s+/u', ' ', $summary) ?? $summary;

            return \Illuminate\Support\Str::limit(trim($summary), 120);
        };

        $getAuthorUrl = function ($author) {
            if (!$author) {
                return '#';
            }

            if (!empty($author->username)) {
                try {
                    return route('profile.show', $author->username);
                } catch (\Throwable $e) {
                    return '#';
                }
            }

            return '#';
        };

        $getAuthorAvatar = function ($author): ?string {
            if (!$author) {
                return null;
            }

            return $author->profile_photo_url
                ?? $author->avatar
                ?? $author->profile_photo_path
                ?? null;
        };

        $getAuthorName = function ($author): string {
            if (!$author) {
                return 'Ografi';
            }

            return trim((string) ($author->username ?? $author->name ?? 'Ografi'));
        };

        $shortsPosts = collect($videoPosts ?? [])
            ->map(function ($post) use ($extractShortMedia, $buildSummary) {
                $media = $extractShortMedia($post);

                if (!$media) {
                    return null;
                }

                return [
                    'post' => $post,
                    'media' => $media,
                    'summary' => $buildSummary($post),
                ];
            })
            ->filter()
            ->values();
    @endphp

    <div class="reels-page" id="reelsPage">
        <div class="reels-shell">
            <main id="reelsFeed" class="reels-feed" data-reels-feed>
                @forelse($shortsPosts as $entry)
                    @php
                        $post = $entry['post'];
                        $media = $entry['media'];
                        $author = $post->author ?? null;

                        $postUrl = route('blog.post', $post->slug);
                        $authorUrl = $getAuthorUrl($author);
                        $authorAvatar = $getAuthorAvatar($author);
                        $authorName = $getAuthorName($author);
                        $authorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authorName, 0, 1));

                        $title = trim((string) ($post->title ?? 'Video'));
                        $summary = trim((string) ($entry['summary'] ?? ''));
                        $commentsCount = $formatShortCount($post->comments_count ?? 0);
                        $reactionsCount = $formatShortCount($post->reactions_count ?? 0);
                        $bookmarksCount = $formatShortCount($post->bookmarks_count ?? 0);
                        $viewsCount = $formatShortCount($post->views_count ?? 0);

                        $shareTitle = $title !== '' ? $title : 'Ografi Video';
                        $embedLabel = $media['label'] ?? 'Video';
                    @endphp

                    <section class="reel-slide" data-reel-slide>
                        <article class="reel-phone">
                            <div class="reel-media">
                                @if(($media['kind'] ?? null) === 'video')
                                    <video
                                        class="reel-video"
                                        autoplay
                                        loop
                                        playsinline
                                        preload="metadata"
                                        data-reel-video
                                    >
                                        <source src="{{ $media['video']['url'] }}" type="{{ $media['video']['type'] ?? 'video/mp4' }}">

                                        @foreach(($media['video']['subtitles'] ?? []) as $track)
                                            @if(!empty($track['src']))
                                                <track
                                                    src="{{ $track['src'] }}"
                                                    kind="{{ $track['kind'] ?? 'subtitles' }}"
                                                    srclang="{{ $track['srclang'] ?? 'tr' }}"
                                                    label="{{ $track['label'] ?? 'Türkçe' }}"
                                                >
                                            @endif
                                        @endforeach
                                    </video>
                                @elseif(($media['kind'] ?? null) === 'embed' && !empty($media['url']))
                                    <div class="reel-embed" data-reel-embed>
                                        <iframe
                                            class="reel-embed-frame"
                                            src="{{ $media['url'] }}"
                                            title="{{ $title }} {{ $embedLabel }} video oynatıcısı"
                                            loading="lazy"
                                            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                                            allowfullscreen
                                            referrerpolicy="strict-origin-when-cross-origin"
                                        ></iframe>
                                    </div>
                                @elseif(!empty($media['thumbnail']))
                                    <img
                                        class="reel-image"
                                        src="{{ $media['thumbnail'] }}"
                                        alt="{{ $title }} video kapak görseli"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="reel-fallback">
                                        <div class="reel-fallback__inner">
                                            <div class="reel-fallback__icon">
                                                <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M9 18V6l10 6-10 6Z"></path>
                                                </svg>
                                            </div>
                                            <div class="reel-fallback__title">
                                                {{ $embedLabel }} videosunu açmak için gönderiye git.
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="reel-overlay"></div>

                            <div class="reels-topbar">
                                <div class="reels-top-left">
                                    <div class="reels-brand" aria-label="Ografi Video">
                                        <a href="{{ $videoUrl }}" class="reels-brand__home" aria-label="Video sayfası">
                                            <img
                                                class="reels-brand__logo"
                                                src="{{ $brandLogoUrl }}"
                                                alt="Ografi logo"
                                                loading="eager"
                                            >

                                            <span class="reels-brand__text">
                                                <span class="reels-brand__title">Shorts</span>
                                                <span class="reels-brand__sub">Ografi</span>
                                            </span>
                                        </a>

                                        <button type="button" class="reels-brand__fullscreen" data-reels-exit-fullscreen aria-label="Tam ekrandan çık">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.345 3.75v2.095a2.5 2.5 0 0 1-2.5 2.5H3.75M8.345 20.25v-2.095a2.5 2.5 0 0 0-2.5-2.5H3.75M15.655 3.75v2.095a2.5 2.5 0 0 0 2.5 2.5h2.095M15.655 20.25v-2.095a2.5 2.5 0 0 1 2.5-2.5h2.095"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="reels-top-actions">
                                    <a class="reels-top-btn" href="{{ $composeUrl }}" aria-label="Video paylaş">
                                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 5v14"></path>
                                            <path d="M5 12h14"></path>
                                        </svg>
                                    </a>

                                    <div class="reels-user-menu" data-reels-user-menu>
                                        @if($viewer)
                                            <button type="button" class="reels-user-trigger" data-reels-user-trigger aria-label="Kullanıcı menüsü" aria-expanded="false">
                                                @if($userAvatarUrl)
                                                    <img src="{{ $userAvatarUrl }}" alt="{{ $viewer->name ?? 'Kullanıcı' }}">
                                                @else
                                                    <span class="reels-user-initial">{{ $userInitial }}</span>
                                                @endif
                                            </button>

                                            <div class="reels-user-dropdown" data-reels-user-dropdown>
                                                <a class="reels-menu-item" href="{{ $videoUrl }}">
                                                    <span class="reels-menu-item__icon">
                                                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="m10 8 6 4-6 4V8Z"></path>
                                                            <rect x="3" y="5" width="18" height="14" rx="3"></rect>
                                                        </svg>
                                                    </span>
                                                    <span>Videolar</span>
                                                </a>

                                                <a class="reels-menu-item" href="{{ $savedUrl }}">
                                                    <span class="reels-menu-item__icon">
                                                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16Z"></path>
                                                        </svg>
                                                    </span>
                                                    <span>Kaydedilenler</span>
                                                </a>

                                                <div class="reels-menu-divider"></div>

                                                <a class="reels-menu-item" href="{{ $dashboardUrl }}">
                                                    <span class="reels-menu-item__icon">
                                                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M4 13h6V4H4v9Z"></path>
                                                            <path d="M14 20h6v-9h-6v9Z"></path>
                                                            <path d="M4 20h6v-3H4v3Z"></path>
                                                            <path d="M14 7h6V4h-6v3Z"></path>
                                                        </svg>
                                                    </span>
                                                    <span>Panel</span>
                                                </a>

                                                <a class="reels-menu-item" href="{{ $userProfileUrl }}">
                                                    <span class="reels-menu-item__icon">
                                                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M20 21a8 8 0 0 0-16 0"></path>
                                                            <circle cx="12" cy="7" r="4"></circle>
                                                        </svg>
                                                    </span>
                                                    <span>Profil</span>
                                                </a>
                                            </div>
                                        @else
                                            <a class="reels-user-trigger" href="{{ route('login') }}" aria-label="Giriş yap">
                                                <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="reel-mute {{ ($media['kind'] ?? null) === 'video' ? 'is-unmuted' : 'is-disabled' }}"
                                data-reel-mute
                                aria-label="Sesi aç veya kapat"
                            >
                                <svg class="reels-icon icon-muted" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M11 5 6 9H3v6h3l5 4V5Z"></path>
                                    <path d="m22 9-6 6"></path>
                                    <path d="m16 9 6 6"></path>
                                </svg>

                                <svg class="reels-icon icon-sound" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M11 5 6 9H3v6h3l5 4V5Z"></path>
                                    <path d="M15.5 8.5a5 5 0 0 1 0 7"></path>
                                    <path d="M18.5 5.5a9 9 0 0 1 0 13"></path>
                                </svg>
                            </button>

                            <div class="reel-actions">

                                <div class="reel-action">
                                    <form method="POST" action="{{ route('blog.post.reaction', $post) }}">
                                        @csrf

                                        @if($defaultReactionType)
                                            <input type="hidden" name="reaction_type_id" value="{{ $defaultReactionType->id }}">

                                            @if(filled($defaultReactionType->short_code))
                                                <input type="hidden" name="short_code" value="{{ $defaultReactionType->short_code }}">
                                            @endif
                                        @endif

                                        <button type="submit" class="reel-action-btn is-like" aria-label="Beğen">
                                            <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>
                                            </svg>
                                        </button>
                                    </form>

                                    <span class="reel-action-count">{{ $reactionsCount }}</span>
                                </div>

                                <a class="reel-action" href="{{ $postUrl }}#comments" aria-label="Yorumlar">
                                    <span class="reel-action-btn">
                                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"></path>
                                        </svg>
                                    </span>
                                    <span class="reel-action-count">{{ $commentsCount }}</span>
                                </a>

                                <a class="reel-action" href="{{ $postUrl }}" aria-label="Kaydet">
                                    <span class="reel-action-btn is-bookmark">
                                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16Z"></path>
                                        </svg>
                                    </span>
                                    <span class="reel-action-count">{{ $bookmarksCount }}</span>
                                </a>
                            </div>

                            <div class="reel-content">
                                <div class="reel-author-row">
                                    <a class="reel-author-avatar" href="{{ $authorUrl }}" aria-label="{{ $authorName }} profili">
                                        @if($authorAvatar)
                                            <img src="{{ $authorAvatar }}" alt="{{ $authorName }} profil fotoğrafı">
                                        @else
                                            {{ $authorInitial }}
                                        @endif
                                    </a>

                                    <a class="reel-author-name" href="{{ $authorUrl }}">
                                        {{ str_starts_with($authorName, '@') ? $authorName : '@' . $authorName }}
                                    </a>

                                    @if(!$viewer || optional($viewer)->id !== optional($author)->id)
                                        <a class="reel-follow-btn" href="{{ $authorUrl }}">Takip et</a>
                                    @endif
                                </div>

                                <p class="reel-title">
                                    {{ $title }}
                                </p>

                                @if($summary !== '' && $summary !== $title)
                                    <p class="reel-summary">
                                        {{ $summary }}
                                    </p>
                                @endif

                                <a class="reel-continue-btn" href="{{ $postUrl }}">Devamını görüntüle</a>

                                <div class="reel-audio">
                                    <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M9 18V5l12-2v13"></path>
                                        <circle cx="6" cy="18" r="3"></circle>
                                        <circle cx="18" cy="16" r="3"></circle>
                                    </svg>
                                    <span>{{ $viewsCount }} izlenme · Ografi Shorts</span>
                                </div>
                            </div>

                            <div class="reel-progress" aria-hidden="true">
                                <span data-reel-progress></span>
                            </div>
                        </article>
                    </section>
                @empty
                    <section class="reel-slide">
                        <div class="reels-empty">
                            <div class="reels-empty-card">
                                <div class="reels-empty-icon">
                                    <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m9 18 6-6-6-6v12Z"></path>
                                        <rect x="3" y="4" width="18" height="16" rx="3"></rect>
                                    </svg>
                                </div>

                                <strong>Henüz video yok</strong>
                                <p>Video içeren gönderiler burada Reels / Shorts akışı olarak görünecek.</p>

                                <a href="{{ $composeUrl }}">İlk videoyu paylaş</a>
                            </div>
                        </div>
                    </section>
                @endforelse
            </main>

            @if($shortsPosts->count() > 1)
                <div class="reels-desktop-nav" aria-label="Video gezinme">
                    <button type="button" class="reels-nav-btn" data-reels-prev aria-label="Önceki video">
                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m18 15-6-6-6 6"></path>
                        </svg>
                    </button>

                    <button type="button" class="reels-nav-btn reels-nav-btn--fullscreen" data-reels-fullscreen aria-label="Tam ekran aç veya kapat">
                        <svg class="reels-fullscreen-icon" viewBox="0 0 64 64" aria-hidden="true">
                            <path d="M60 21.5c1.2 0 2.3-1 2.3-2.3V5c0-1.8-1.4-3.2-3.2-3.2H44.7c-1.2 0-2.3 1-2.3 2.3s1 2.3 2.3 2.3h9.8L32 28.8L9.4 6.3h9.8c1.2 0 2.3-1 2.3-2.3s-1-2.3-2.3-2.3H5C3.2 1.8 1.8 3.2 1.8 5v14.3c0 1.2 1 2.3 2.3 2.3s2.3-1 2.3-2.3V9.4L28.8 32L6.3 54.6v-9.8c0-1.2-1-2.3-2.3-2.3s-2.3 1-2.3 2.3V59c0 1.8 1.4 3.2 3.2 3.2h14.3c1.2 0 2.3-1 2.3-2.3s-1-2.3-2.3-2.3H9.4L32 35.2l22.6 22.6h-9.8c-1.2 0-2.3 1-2.3 2.3s1 2.3 2.3 2.3H59c1.8 0 3.2-1.4 3.2-3.2V44.7c0-1.2-1-2.3-2.3-2.3s-2.3 1-2.3 2.3v9.8L35.2 32L57.8 9.4v9.8c0 1.3 1 2.3 2.2 2.3"/>
                        </svg>
                    </button>

                    <button type="button" class="reels-nav-btn" data-reels-next aria-label="Sonraki video">
                        <svg class="reels-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const forceVideoOnlyLayout = () => {
                const page = document.getElementById('reelsPage');

                if (!page) return;

                document.documentElement.classList.add('ografi-video-page');
                document.body.classList.add('ografi-video-page-ready');

                if (window.matchMedia('(max-width: 1023px)').matches) {
                    if (page.parentElement !== document.body) {
                        document.body.appendChild(page);
                    }

                    page.style.position = 'fixed';
                    page.style.inset = '0';
                    page.style.zIndex = '2147483647';
                    page.style.width = '100vw';
                    page.style.maxWidth = '100vw';
                    page.style.height = '100svh';
                    page.style.minHeight = '100svh';
                    page.style.margin = '0';
                    page.style.padding = '0';
                    page.style.borderRadius = '0';
                    page.style.background = '#000';

                    Array.from(document.body.children).forEach((child) => {
                        if (child === page || ['SCRIPT', 'STYLE', 'LINK', 'META', 'TITLE'].includes(child.tagName)) {
                            return;
                        }

                        child.style.setProperty('display', 'none', 'important');
                        child.style.setProperty('visibility', 'hidden', 'important');
                        child.style.setProperty('opacity', '0', 'important');
                        child.style.setProperty('pointer-events', 'none', 'important');
                    });
                }

                page.querySelectorAll('[data-reel-share]').forEach((button) => button.remove());
            };

            forceVideoOnlyLayout();
            window.addEventListener('resize', forceVideoOnlyLayout, { passive: true });
            window.setTimeout(forceVideoOnlyLayout, 50);
            window.setTimeout(forceVideoOnlyLayout, 250);
            window.setTimeout(forceVideoOnlyLayout, 1000);

            const reelsPage = document.getElementById('reelsPage');
            const feed = document.querySelector('[data-reels-feed]');

            if (!feed) return;

            const slides = Array.from(feed.querySelectorAll('[data-reel-slide]'));
            const videos = Array.from(feed.querySelectorAll('[data-reel-video]'));
            const posterImages = Array.from(feed.querySelectorAll('.reel-image'));
            const embeds = Array.from(feed.querySelectorAll('[data-reel-embed]'));
            const prevBtn = document.querySelector('[data-reels-prev]');
            const nextBtn = document.querySelector('[data-reels-next]');
            const fullscreenBtn = document.querySelector('[data-reels-fullscreen]');
            const shareButtons = Array.from(document.querySelectorAll('[data-reel-share]'));
            const muteButtons = Array.from(document.querySelectorAll('[data-reel-mute]'));

            if (!slides.length) return;

            let currentIndex = 0;
            let touchStartY = 0;
            let scrollLocked = false;

            videos.forEach((video) => {
                video.muted = false;
                video.volume = 1;
                video.removeAttribute('muted');
            });

            muteButtons.forEach((button) => {
                const slide = button.closest('[data-reel-slide]');
                const video = slide ? slide.querySelector('[data-reel-video]') : null;

                if (video) {
                    button.classList.add('is-unmuted');
                }
            });

            const setProgress = (video, value) => {
                const slide = video.closest('[data-reel-slide]');
                const progress = slide ? slide.querySelector('[data-reel-progress]') : null;

                if (progress) {
                    progress.style.width = `${Math.max(0, Math.min(100, value))}%`;
                }
            };

            const updateSlideAspect = (mediaElement) => {
                const slide = mediaElement.closest('[data-reel-slide]');
                const phone = slide ? slide.querySelector('.reel-phone') : null;

                if (!slide || !phone) return;

                const isEmbed = mediaElement.matches('[data-reel-embed]');
                const mediaWidth = mediaElement.videoWidth || mediaElement.naturalWidth || (isEmbed ? 9 : 9);
                const mediaHeight = mediaElement.videoHeight || mediaElement.naturalHeight || (isEmbed ? 16 : 16);

                if (!mediaWidth || !mediaHeight) return;

                const ratio = mediaWidth / mediaHeight;

                slide.style.setProperty('--reel-aspect-ratio', ratio.toFixed(4));
                slide.classList.remove('is-vertical', 'is-landscape', 'is-square', 'is-embed');

                if (isEmbed) {
                    slide.classList.add('is-embed');
                }

                if (ratio > 1.12) {
                    slide.classList.add('is-landscape');
                } else if (ratio < 0.88) {
                    slide.classList.add('is-vertical');
                } else {
                    slide.classList.add('is-square');
                }

                if (reelsPage && reelsPage.classList.contains('is-fullscreen')) {
                    phone.style.removeProperty('--reel-stage-width');
                    phone.style.removeProperty('--reel-stage-height');
                    return;
                }

                if (window.innerWidth >= 1024) {
                    const availableHeight = window.innerHeight - 36;
                    const availableWidth = window.innerWidth - 180;

                    let stageHeight = availableHeight;
                    let stageWidth = stageHeight * ratio;

                    if (stageWidth > availableWidth) {
                        stageWidth = availableWidth;
                        stageHeight = stageWidth / ratio;
                    }

                    phone.style.setProperty('--reel-stage-width', `${Math.round(stageWidth)}px`);
                    phone.style.setProperty('--reel-stage-height', `${Math.round(stageHeight)}px`);
                } else {
                    phone.style.removeProperty('--reel-stage-width');
                    phone.style.removeProperty('--reel-stage-height');
                }
            };

            const pauseAllExcept = (activeVideo = null) => {
                videos.forEach((video) => {
                    if (video !== activeVideo) {
                        video.pause();
                    }
                });
            };

            const playVideo = (video) => {
                if (!video) {
                    pauseAllExcept(null);
                    return;
                }

                pauseAllExcept(video);

                video.muted = false;
                video.volume = 1;
                video.removeAttribute('muted');

                const slide = video.closest('[data-reel-slide]');
                const muteButton = slide ? slide.querySelector('[data-reel-mute]') : null;

                if (muteButton) {
                    muteButton.classList.add('is-unmuted');
                }

                const promise = video.play();

                if (promise && typeof promise.catch === 'function') {
                    promise.catch(() => {});
                }
            };

            const goToSlide = (index) => {
                if (index < 0 || index >= slides.length) return;

                currentIndex = index;

                slides[index].scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            };

            const lockScrollBriefly = () => {
                scrollLocked = true;

                window.setTimeout(() => {
                    scrollLocked = false;
                }, 520);
            };

            videos.forEach((video) => {
                video.addEventListener('loadedmetadata', () => {
                    setProgress(video, 0);
                    updateSlideAspect(video);
                });

                video.addEventListener('timeupdate', () => {
                    if (!video.duration || !Number.isFinite(video.duration)) return;

                    setProgress(video, (video.currentTime / video.duration) * 100);
                });

                video.addEventListener('ended', () => {
                    setProgress(video, 100);
                });

                video.addEventListener('click', () => {
                    if (video.paused) {
                        playVideo(video);
                    } else {
                        video.pause();
                    }
                });
            });

            posterImages.forEach((image) => {
                if (image.complete) {
                    updateSlideAspect(image);
                } else {
                    image.addEventListener('load', () => {
                        updateSlideAspect(image);
                    });
                }
            });

            embeds.forEach((embed) => {
                updateSlideAspect(embed);
            });

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    const slide = entry.target;
                    const index = slides.indexOf(slide);
                    const video = slide.querySelector('[data-reel-video]');

                    if (entry.isIntersecting && entry.intersectionRatio >= 0.62) {
                        currentIndex = index;

                        const activeMedia = slide.querySelector('[data-reel-video], .reel-image, [data-reel-embed]');

                        if (activeMedia) {
                            updateSlideAspect(activeMedia);
                        }

                        if (video) {
                            playVideo(video);
                        } else {
                            pauseAllExcept(null);
                        }
                    } else if (video && entry.intersectionRatio < 0.35) {
                        video.pause();
                    }
                });
            }, {
                root: feed,
                threshold: [0.35, 0.62],
            });

            slides.forEach((slide) => observer.observe(slide));

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    goToSlide(currentIndex - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    goToSlide(currentIndex + 1);
                });
            }

            feed.addEventListener('wheel', (event) => {
                if (window.innerWidth < 1024) return;
                if (scrollLocked) return;
                if (Math.abs(event.deltaY) < 24) return;

                lockScrollBriefly();

                if (event.deltaY > 0) {
                    goToSlide(currentIndex + 1);
                } else {
                    goToSlide(currentIndex - 1);
                }
            }, { passive: true });

            feed.addEventListener('touchstart', (event) => {
                touchStartY = event.changedTouches[0].clientY;
            }, { passive: true });

            feed.addEventListener('touchend', (event) => {
                const touchEndY = event.changedTouches[0].clientY;
                const diff = touchStartY - touchEndY;

                if (Math.abs(diff) < 52 || scrollLocked) return;

                lockScrollBriefly();

                if (diff > 0) {
                    goToSlide(currentIndex + 1);
                } else {
                    goToSlide(currentIndex - 1);
                }
            }, { passive: true });

            window.addEventListener('resize', () => {
                const activeSlide = slides[currentIndex];

                if (!activeSlide) return;

                const activeMedia = activeSlide.querySelector('[data-reel-video], .reel-image, [data-reel-embed]');

                if (activeMedia) {
                    updateSlideAspect(activeMedia);
                }
            });

            muteButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const slide = button.closest('[data-reel-slide]');
                    const video = slide ? slide.querySelector('[data-reel-video]') : null;

                    if (!video) return;

                    video.muted = !video.muted;
                    video.volume = video.muted ? 0 : 1;

                    if (video.muted) {
                        video.setAttribute('muted', 'muted');
                    } else {
                        video.removeAttribute('muted');
                    }

                    button.classList.toggle('is-unmuted', !video.muted);

                    if (!video.paused) return;

                    playVideo(video);
                });
            });

            shareButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.getAttribute('data-share-url') || window.location.href;
                    const title = button.getAttribute('data-share-title') || document.title;
                    const label = button.querySelector('[data-share-label]');
                    const defaultText = label ? label.textContent : 'Paylaş';

                    try {
                        if (navigator.share) {
                            await navigator.share({ title, url });
                        } else if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(url);
                        } else {
                            window.open(url, '_blank', 'noopener,noreferrer');
                            return;
                        }

                        if (label) {
                            label.textContent = 'Kopyalandı';

                            window.setTimeout(() => {
                                label.textContent = defaultText;
                            }, 1300);
                        }
                    } catch (error) {
                        if (error && error.name === 'AbortError') {
                            return;
                        }

                        window.open(url, '_blank', 'noopener,noreferrer');
                    }
                });
            });

            const closeAllUserMenus = () => {
                document.querySelectorAll('[data-reels-user-menu]').forEach((menu) => {
                    menu.classList.remove('is-open');

                    const trigger = menu.querySelector('[data-reels-user-trigger]');

                    if (trigger) {
                        trigger.setAttribute('aria-expanded', 'false');
                    }
                });
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-reels-user-trigger]');

                if (trigger) {
                    event.preventDefault();
                    event.stopPropagation();

                    const menu = trigger.closest('[data-reels-user-menu]');
                    const willOpen = menu && !menu.classList.contains('is-open');

                    closeAllUserMenus();

                    if (menu && willOpen) {
                        menu.classList.add('is-open');
                        trigger.setAttribute('aria-expanded', 'true');
                    }

                    return;
                }

                if (!event.target.closest('[data-reels-user-menu]')) {
                    closeAllUserMenus();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAllUserMenus();
                }
            });

            const updateFullscreenState = () => {
                const isFullscreen = !!document.fullscreenElement;

                if (reelsPage) {
                    reelsPage.classList.toggle('is-fullscreen', isFullscreen);
                }

                if (fullscreenBtn) {
                    fullscreenBtn.setAttribute(
                        'aria-label',
                        isFullscreen ? 'Tam ekrandan çık' : 'Tam ekran aç'
                    );
                }

                const activeSlide = slides[currentIndex];

                if (!activeSlide) return;

                const activeMedia = activeSlide.querySelector('[data-reel-video], .reel-image, [data-reel-embed]');

                if (activeMedia) {
                    window.setTimeout(() => {
                        updateSlideAspect(activeMedia);
                    }, 80);
                }
            };

            if (fullscreenBtn && reelsPage) {
                fullscreenBtn.addEventListener('click', async () => {
                    try {
                        if (!document.fullscreenElement) {
                            if (reelsPage.requestFullscreen) {
                                await reelsPage.requestFullscreen();
                            }
                        } else if (document.exitFullscreen) {
                            await document.exitFullscreen();
                        }
                    } catch (e) {
                        // Sessiz geç.
                    }
                });

                document.addEventListener('fullscreenchange', updateFullscreenState);
                updateFullscreenState();
            }

            document.addEventListener('click', async (event) => {
                const exitButton = event.target.closest('[data-reels-exit-fullscreen]');

                if (!exitButton) return;

                event.preventDefault();
                event.stopPropagation();

                try {
                    if (document.fullscreenElement && document.exitFullscreen) {
                        await document.exitFullscreen();
                    }
                } catch (e) {
                    // Sessiz geç.
                }
            });
        });
    </script>
@endpush

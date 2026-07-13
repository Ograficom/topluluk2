<meta name="color-scheme" content="light dark">
<script>
    (() => {
        const root = document.documentElement;
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const storageKey = 'ografi-theme';

        const getStoredTheme = () => {
            try {
                const value = window.localStorage.getItem(storageKey);
                return value === 'dark' || value === 'light' ? value : null;
            } catch (error) {
                return null;
            }
        };

        const applyScheme = (preferredTheme = getStoredTheme()) => {
            const isDark = preferredTheme ? preferredTheme === 'dark' : media.matches;
            root.classList.toggle('dark', isDark);
            root.style.colorScheme = isDark ? 'dark' : 'light';
            root.dataset.systemTheme = isDark ? 'dark' : 'light';
            root.dataset.themePreference = preferredTheme ?? 'system';
            window.dispatchEvent(new CustomEvent('themechange', {
                detail: {
                    theme: isDark ? 'dark' : 'light',
                    preference: preferredTheme ?? 'system',
                },
            }));
        };

        window.setPreferredTheme = (theme) => {
            const normalized = theme === 'dark' || theme === 'light' ? theme : null;

            try {
                if (normalized) {
                    window.localStorage.setItem(storageKey, normalized);
                } else {
                    window.localStorage.removeItem(storageKey);
                }
            } catch (error) {
                // Ignore storage failures and still apply the chosen scheme for the current session.
            }

            applyScheme(normalized);
        };

        applyScheme();

        if (typeof media.addEventListener === 'function') {
            media.addEventListener('change', () => {
                if (!getStoredTheme()) {
                    applyScheme(null);
                }
            });
        } else if (typeof media.addListener === 'function') {
            media.addListener(() => {
                if (!getStoredTheme()) {
                    applyScheme(null);
                }
            });
        }
    })();
</script>
<style>
    :root {
        color-scheme: light;
        --device-shell-gutter: clamp(16px, 2.6vw, 24px);
        --device-shell-gap: clamp(16px, 2vw, 24px);
        --device-card-radius: clamp(16px, 1.25vw, 20px);
    }

    html {
        font-size: 100%;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
        scroll-padding-top: calc(var(--site-header-height, 64px) + 16px);
        scrollbar-gutter: stable;
    }

    html.dark {
        color-scheme: dark;
    }

    body {
        min-width: 320px;
        font-size: 1rem;
        line-height: 1.5;
    }

    body,
    main,
    section,
    article,
    aside {
        min-width: 0;
    }

    img,
    video,
    iframe,
    canvas,
    svg {
        max-width: 100%;
        height: auto;
    }

    input,
    textarea,
    select,
    button {
        font: inherit;
    }

    .site-header-logo-image {
        height: clamp(30px, 2.2vw, 34px);
    }

    .alma-post-card__title {
        font-size: clamp(1.25rem, 1.12rem + 0.55vw, 1.375rem);
    }

    .alma-post-card__title.is-hero {
        font-size: clamp(1.375rem, 1.18rem + 0.9vw, 1.625rem);
    }

    .alma-page-title {
        font-size: clamp(1.6rem, 1.24rem + 1.1vw, 1.875rem);
    }

    .alma-post-card__excerpt,
    .alma-page-subtitle,
    .sidebar-wrapper .sidebar-category-name,
    .left-sidebar-link,
    .community-btn,
    .community-pill {
        font-size: clamp(0.9375rem, 0.9rem + 0.15vw, 1rem);
    }

    @media (max-width: 1199px) {
        .site-header-shell,
        .community-shell,
        .main-grid {
            padding-left: var(--device-shell-gutter);
            padding-right: var(--device-shell-gutter);
        }

        .main-grid {
            gap: var(--device-shell-gap);
        }
    }

    @media (max-width: 1023px) {
        .site-header-shell {
            min-height: 68px;
            gap: 10px;
        }

        .site-header-actions {
            gap: 8px;
        }

        .site-search-panel:not(.hidden) {
            width: min(100%, 420px);
        }

        .site-search-field {
            min-width: 0;
            width: 100%;
        }

        .community-shell,
        .main-grid {
            padding-bottom: calc(88px + env(safe-area-inset-bottom));
        }

        .layout-sticky {
            position: static;
            top: auto;
            max-height: none;
            overflow: visible;
            padding-right: 0;
        }
    }

    @media (max-width: 767px) {
        html {
            scroll-padding-top: calc(var(--site-header-height, 64px) + 8px);
        }

        .site-header-shell,
        .community-shell,
        .main-grid {
            padding-left: max(10px, env(safe-area-inset-left));
            padding-right: max(10px, env(safe-area-inset-right));
        }

        .layout-main > div > .mx-auto.max-w-7xl,
        .layout-main > div > .mx-auto.max-w-6xl,
        .layout-main > div > .mx-auto.max-w-5xl,
        .layout-main > div > .mx-auto.max-w-4xl {
            padding-left: max(10px, env(safe-area-inset-left)) !important;
            padding-right: max(10px, env(safe-area-inset-right)) !important;
        }

        .site-header-actions {
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .site-search-panel {
            order: 3;
            width: 100%;
        }

        .site-search-panel:not(.hidden) {
            display: flex;
            width: 100%;
        }

        .site-primary-btn {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        [data-mobile-bottom-nav] {
            width: calc(100% - 1rem) !important;
            max-width: 32rem;
            bottom: max(0.75rem, env(safe-area-inset-bottom));
        }

        [data-mobile-login-drawer] aside,
        [data-mobile-search-drawer] aside {
            max-width: 100%;
            padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));
        }

        input,
        textarea,
        select {
            font-size: 16px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        html {
            scroll-behavior: auto;
        }

        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }

    html.dark {
        --site-bg: var(--bg-dark, #0b1220);
        --site-surface: var(--surface-dark, #111827);
        --site-surface-muted: var(--surface2-dark, #0f172a);
        --site-border: var(--border-dark, rgba(148, 163, 184, 0.18));
        --site-accent-soft: rgba(34, 197, 94, 0.14);
        --site-text: var(--text-dark, #e5e7eb);
        --site-muted: var(--muted-dark, #94a3b8);
        --site-header-bg: rgba(15, 23, 42, 0.84);
        --background: var(--bg-dark, #0b1220);
        --foreground: var(--text-dark, #e5e7eb);
        --border: var(--border-dark, rgba(148, 163, 184, 0.16));
        --secondary: rgba(30, 41, 59, 0.82);
        --secondary-foreground: #cbd5e1;
        --muted: rgba(30, 41, 59, 0.78);
        --muted-foreground: var(--muted-dark, #94a3b8);
        --card: var(--surface-dark, #111827);
        --card-foreground: var(--text-dark, #e5e7eb);
        --sidebar: rgba(15, 23, 42, 0.92);
        --sidebar-foreground: var(--text-dark, #e5e7eb);
        --alma-bg: var(--bg-dark, #0b1220);
        --alma-header-bg: rgba(15, 23, 42, 0.84);
        --alma-primary: var(--primary-dark, #029d71);
        --alma-primary-strong: var(--primary-dark, #029d71);
        --alma-text: var(--text-dark, #e5e7eb);
        --alma-muted: var(--muted-dark, #94a3b8);
        --alma-soft: #64748b;
        --alma-hover-white: rgba(30, 41, 59, 0.92);
        --alma-hover-muted: rgba(30, 41, 59, 0.82);
        --alma-card: var(--surface-dark, #111827);
        --alma-border: var(--border-dark, rgba(148, 163, 184, 0.16));
        --alma-shadow: 0 24px 48px rgba(2, 6, 23, 0.38);
    }

    html.dark body,
    html.dark body.theme-minimal,
    html.dark body.alma-app.auth-single {
        background-color: var(--alma-bg, var(--site-bg, #0b1220));
        color: var(--alma-text, var(--site-text, #e5e7eb));
    }

    html.dark body.theme-minimal {
        background-image:
            radial-gradient(circle at top left, rgba(2, 157, 113, 0.09), transparent 20rem),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.09), transparent 24rem);
    }

    html.dark button,
    html.dark [type="button"],
    html.dark [type="submit"],
    html.dark [type="reset"],
    html.dark .site-icon-btn,
    html.dark .site-search-trigger,
    html.dark .site-search-close,
    html.dark .site-notifications-more,
    html.dark .alma-post-card__metric-button,
    html.dark .alma-post-card__menu-trigger,
    html.dark .mobile-bottom-nav__plus,
    html.dark [data-mobile-sidebar-close],
    html.dark [data-theme-toggle],
    html.dark a.community-btn,
    html.dark a.alma-button,
    html.dark a.alma-button-secondary,
    html.dark a.site-primary-btn,
    html.dark a.inline-flex.items-center.justify-center.rounded-2xl,
    html.dark a.inline-flex.items-center.justify-center.rounded-xl,
    html.dark a.inline-flex.items-center.rounded-xl,
    html.dark a.inline-flex.items-center.rounded-2xl {
        border-color: rgba(148, 163, 184, 0.18);
        color: var(--alma-text, var(--site-text, #e5e7eb));
    }

    html.dark button:not(.site-primary-btn):not(.alma-button):not(.site-notifications-menu-item):not(.site-search-close):not(.mobile-bottom-nav__plus),
    html.dark [type="button"]:not(.site-primary-btn):not(.alma-button):not(.site-notifications-menu-item):not(.site-search-close):not(.mobile-bottom-nav__plus),
    html.dark [type="submit"]:not(.site-primary-btn):not(.alma-button),
    html.dark [type="reset"]:not(.site-primary-btn):not(.alma-button),
    html.dark .site-icon-btn,
    html.dark .site-search-trigger,
    html.dark .site-search-close,
    html.dark .site-notifications-more,
    html.dark .alma-post-card__metric-button,
    html.dark .alma-post-card__menu-trigger,
    html.dark .mobile-bottom-nav__plus,
    html.dark [data-mobile-sidebar-close],
    html.dark [data-theme-toggle],
    html.dark a.community-btn:not(.site-primary-btn):not(.alma-button),
    html.dark a.alma-button-secondary,
    html.dark a.inline-flex.items-center.justify-center.rounded-2xl:not(.site-primary-btn):not(.alma-button),
    html.dark a.inline-flex.items-center.justify-center.rounded-xl:not(.site-primary-btn):not(.alma-button),
    html.dark a.inline-flex.items-center.rounded-xl:not(.site-primary-btn):not(.alma-button),
    html.dark a.inline-flex.items-center.rounded-2xl:not(.site-primary-btn):not(.alma-button) {
        background-color: rgba(15, 23, 42, 0.82);
    }

    html.dark button:not(.site-primary-btn):not(.alma-button):hover,
    html.dark [type="button"]:not(.site-primary-btn):not(.alma-button):hover,
    html.dark [type="submit"]:not(.site-primary-btn):not(.alma-button):hover,
    html.dark [type="reset"]:not(.site-primary-btn):not(.alma-button):hover,
    html.dark .site-icon-btn:hover,
    html.dark .site-search-trigger:hover,
    html.dark .site-search-close:hover,
    html.dark .site-notifications-more:hover,
    html.dark .alma-post-card__metric-button:hover,
    html.dark .alma-post-card__menu-trigger:hover,
    html.dark .mobile-bottom-nav__plus:hover,
    html.dark [data-mobile-sidebar-close]:hover,
    html.dark [data-theme-toggle]:hover,
    html.dark a.community-btn:not(.site-primary-btn):not(.alma-button):hover,
    html.dark a.alma-button-secondary:hover,
    html.dark a.inline-flex.items-center.justify-center.rounded-2xl:not(.site-primary-btn):not(.alma-button):hover,
    html.dark a.inline-flex.items-center.justify-center.rounded-xl:not(.site-primary-btn):not(.alma-button):hover,
    html.dark a.inline-flex.items-center.rounded-xl:not(.site-primary-btn):not(.alma-button):hover,
    html.dark a.inline-flex.items-center.rounded-2xl:not(.site-primary-btn):not(.alma-button):hover {
        background-color: rgba(30, 41, 59, 0.9);
    }

    html.dark .site-header,
    html.dark .site-menu-panel,
    html.dark .site-search-trigger,
    html.dark .site-search-field,
    html.dark .site-search-results-panel,
    html.dark .site-notifications-panel,
    html.dark .site-notifications-panel-head,
    html.dark .site-notifications-actions-menu,
    html.dark .site-notifications-list,
    html.dark .site-notifications-menu-item,
    html.dark .site-notification-item,
    html.dark .site-icon-btn,
    html.dark .site-card,
    html.dark .sidebar-card,
    html.dark .community-card,
    html.dark .alma-panel,
    html.dark .alma-post-card,
    html.dark .alma-ad-slot,
    html.dark [data-mobile-bottom-nav],
    html.dark [data-mobile-login-drawer] aside,
    html.dark [data-mobile-search-drawer] aside,
    html.dark [data-mobile-search-surface],
    html.dark [data-mobile-sidebar-panel] {
        background-color: rgba(15, 23, 42, 0.88) !important;
        border-color: rgba(148, 163, 184, 0.16) !important;
        color: var(--alma-text, var(--site-text, #e5e7eb));
    }

    html.dark .site-header {
        background: var(--alma-header-bg, var(--site-header-bg, rgba(15, 23, 42, 0.84))) !important;
        border-bottom-color: rgba(148, 163, 184, 0.14) !important;
    }

    html.dark .site-search-field input,
    html.dark .site-search-trigger,
    html.dark .site-search-close,
    html.dark .site-search-all,
    html.dark .site-search-row,
    html.dark .site-notifications-panel-title,
    html.dark .site-notifications-more,
    html.dark .site-notifications-menu-item,
    html.dark .site-notification-item,
    html.dark .site-notification-item-title,
    html.dark .site-notification-item-title strong,
    html.dark .site-icon-btn,
    html.dark .site-header-logo,
    html.dark .community-link,
    html.dark .community-btn,
    html.dark .community-pill,
    html.dark .left-sidebar-link,
    html.dark .sidebar-wrapper .nav-item,
    html.dark .sidebar-wrapper .sidebar-category-link,
    html.dark .sidebar-wrapper .sidebar-footer-brand,
    html.dark .sidebar-wrapper .sidebar-footer-link,
    html.dark .alma-post-card__author,
    html.dark .alma-post-card__title,
    html.dark .alma-page-title,
    html.dark .alma-widget__title,
    html.dark .alma-tag-item__name,
    html.dark .alma-widget__comment-text,
    html.dark .alma-post-card__comment-author,
    html.dark .alma-post-card__comments-count,
    html.dark .alma-post-card__menu-item,
    html.dark .alma-post-card__views,
    html.dark .alma-button-secondary,
    html.dark iconify-icon,
    html.dark button i,
    html.dark a i,
    html.dark .site-icon-btn svg,
    html.dark .site-search-trigger svg,
    html.dark .site-search-close svg,
    html.dark .site-notifications-more svg,
    html.dark .mobile-bottom-nav__plus svg,
    html.dark [data-theme-toggle] svg,
    html.dark [data-mobile-sidebar-close] svg,
    html.dark .alma-post-card__metric-button svg,
    html.dark .alma-post-card__menu-trigger svg {
        color: var(--alma-text, var(--site-text, #e5e7eb));
        fill: currentColor;
        stroke: currentColor;
    }

    html.dark .sidebar-title,
    html.dark .left-sidebar-footer,
    html.dark .sidebar-wrapper .sidebar-footer,
    html.dark .sidebar-wrapper .sidebar-category-name,
    html.dark .sidebar-wrapper .nav-item:not([data-active="true"]),
    html.dark .left-sidebar-link:not(.is-active),
    html.dark .alma-post-card__excerpt,
    html.dark .alma-page-subtitle,
    html.dark .alma-widget__comment-post,
    html.dark .alma-widget__comment-time,
    html.dark .alma-tag-item__count,
    html.dark .site-search-empty,
    html.dark .site-search-row-meta,
    html.dark .site-search-section-title,
    html.dark .site-notifications-empty,
    html.dark .site-notification-item-meta,
    html.dark .site-notification-item-preview,
    html.dark .alma-post-card__submeta,
    html.dark .alma-post-card__submeta a,
    html.dark .alma-post-card__submeta time,
    html.dark .alma-post-card__submeta span {
        color: var(--alma-muted, var(--site-muted, #94a3b8));
    }

    html.dark .left-sidebar-link:hover,
    html.dark .left-sidebar-link.is-active,
    html.dark .sidebar-wrapper .nav-item[data-active="true"],
    html.dark .sidebar-wrapper .sidebar-category-link:hover,
    html.dark .sidebar-wrapper .sidebar-category-link[data-active="true"],
    html.dark .community-link:hover,
    html.dark .community-btn:hover,
    html.dark .community-pill:hover,
    html.dark .site-search-trigger:hover,
    html.dark .site-search-close:hover,
    html.dark .site-search-row:hover,
    html.dark .site-search-all:hover,
    html.dark .site-notifications-more:hover,
    html.dark .site-notifications-menu-item:hover,
    html.dark .site-notification-item:hover,
    html.dark .alma-post-card__metric-button:hover,
    html.dark .alma-post-card__menu-trigger:hover,
    html.dark .alma-post-card__menu-item:hover,
    html.dark .alma-widget__comment:hover,
    html.dark .alma-tag-item:hover,
    html.dark .sidebar-nav a.is-active {
        background: rgba(30, 41, 59, 0.88) !important;
        color: var(--alma-text, var(--site-text, #e5e7eb)) !important;
    }

    html.dark .site-search-field:hover,
    html.dark .site-search-field:focus-within,
    html.dark .alma-input,
    html.dark .alma-select,
    html.dark input:not([type="checkbox"]):not([type="radio"]),
    html.dark textarea,
    html.dark select {
        background: rgba(15, 23, 42, 0.76);
        border-color: rgba(148, 163, 184, 0.2);
        color: var(--alma-text, var(--site-text, #e5e7eb));
    }

    html.dark .site-search-field input::placeholder,
    html.dark input::placeholder,
    html.dark textarea::placeholder {
        color: rgba(148, 163, 184, 0.8);
    }

    html.dark .site-search-section-line,
    html.dark .site-notifications-panel-head,
    html.dark .site-search-all,
    html.dark .sidebar-wrapper .sidebar-footer,
    html.dark .alma-post-card__comments-strip {
        border-color: rgba(148, 163, 184, 0.14) !important;
    }

    html.dark .alma-post-card__reaction-more,
    html.dark .alma-post-card__reaction-picker,
    html.dark .alma-post-card__inline-summary,
    html.dark .alma-post-card__inline-text {
        background: rgba(15, 23, 42, 0.82);
        border-color: rgba(148, 163, 184, 0.16);
        color: var(--alma-text, var(--site-text, #e5e7eb));
    }

    html.dark .alma-post-card__reaction-picker:hover {
        background: rgba(30, 41, 59, 0.9);
        color: #fff;
    }

    html.dark .site-status-dot {
        box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.88);
    }

    html.dark .site-avatar-fallback,
    html.dark .site-notification-item-avatar--fallback,
    html.dark .alma-post-card__avatar--fallback,
    html.dark .alma-post-card__comment-avatar--fallback,
    html.dark .alma-widget__comment-avatar--fallback,
    html.dark .sidebar-wrapper .sidebar-category-avatar--fallback,
    html.dark .left-sidebar-topic-icon,
    html.dark .category-badge,
    html.dark .site-search-avatar--fallback,
    html.dark .site-search-glyph {
        background: rgba(30, 41, 59, 0.92) !important;
        color: var(--alma-text, var(--site-text, #e5e7eb)) !important;
    }

    html.dark .alma-post-card__header-pill,
    html.dark .alma-post-card__header-follow {
        background: rgba(15, 23, 42, 0.82);
        border-color: rgba(148, 163, 184, 0.16);
        color: var(--alma-text, var(--site-text, #e5e7eb));
    }

    html.dark .alma-post-card__reaction-pill,
    html.dark .alma-post-card__reaction-picker,
    html.dark .alma-post-card__reaction-more {
        background: rgba(15, 23, 42, 0.82);
        border-color: rgba(148, 163, 184, 0.18);
        color: var(--alma-text, var(--site-text, #e5e7eb));
    }

    html.dark .alma-post-card__header-follow.is-active {
        background: rgba(30, 64, 175, 0.2);
        border-color: rgba(96, 165, 250, 0.26);
        color: #bfdbfe;
    }

    html.dark .alma-post-card__avatar-badge {
        border-color: rgba(15, 23, 42, 0.88);
        color: #082f49;
    }

    html.dark .site-search-icon,
    html.dark .site-search-clear,
    html.dark .site-notifications-menu-item iconify-icon,
    html.dark .site-notification-item-meta iconify-icon,
    html.dark button i.ph,
    html.dark a i.ph,
    html.dark .alma-post-card__comments-chevron,
    html.dark .alma-post-card__menu-item iconify-icon,
    html.dark .alma-widget__comment-avatar--fallback iconify-icon,
    html.dark .sidebar-wrapper .nav-item-icon-outline,
    html.dark .sidebar-wrapper .nav-item-icon-outline iconify-icon,
    html.dark .sidebar-wrapper .category-badge,
    html.dark .left-sidebar-link .material-icons-outlined {
        color: var(--alma-muted, var(--site-muted, #94a3b8)) !important;
        fill: currentColor;
        stroke: currentColor;
    }

    html.dark .alma-post-card__inline-preview,
    html.dark .alma-post-card__inline-text,
    html.dark .alma-post-card__score iconify-icon {
        color: var(--alma-muted, var(--site-muted, #94a3b8));
    }

    html.dark .alma-post-card__inline-toggle {
        color: #d1d5db;
        background: transparent !important;
    }

    html.dark .sidebar-wrapper .nav-item[data-active="true"] .nav-item-icon-outline,
    html.dark .sidebar-wrapper .nav-item[data-active="true"] .nav-item-icon-outline iconify-icon,
    html.dark .site-primary-btn iconify-icon,
    html.dark .alma-button iconify-icon,
    html.dark a.site-primary-btn iconify-icon,
    html.dark a.alma-button iconify-icon {
        color: #ffffff !important;
        fill: currentColor;
        stroke: currentColor;
    }

    html.dark .alma-feed-promo {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(17, 24, 39, 0.92)),
            rgba(15, 23, 42, 0.94) !important;
        border-color: rgba(148, 163, 184, 0.18) !important;
        color: var(--alma-text, var(--site-text, #e5e7eb));
    }

    html.dark .alma-feed-promo__eyebrow {
        color: #94a3b8 !important;
    }

    html.dark .alma-feed-promo__copy {
        color: #e5e7eb !important;
    }

    html.dark .alma-feed-promo__action {
        background: rgba(15, 23, 42, 0.78) !important;
        border-color: rgba(148, 163, 184, 0.18) !important;
        color: #e5e7eb !important;
    }

    html.dark .alma-feed-promo__action iconify-icon {
        color: #e5e7eb !important;
    }

    html.dark [data-mobile-search-backdrop] {
        background: rgba(2, 6, 23, 0.72) !important;
    }

    html.dark [data-mobile-bottom-nav] .mobile-bottom-nav__plus {
        background: rgba(15, 23, 42, 0.96) !important;
        border-color: rgba(148, 163, 184, 0.18) !important;
        color: var(--alma-text, var(--site-text, #e5e7eb)) !important;
    }

    html.dark .site-primary-btn,
    html.dark .alma-button,
    html.dark a.site-primary-btn,
    html.dark a.alma-button {
        background: var(--alma-primary-strong, var(--site-accent, #22c55e)) !important;
        color: #ffffff !important;
    }

    html.dark .alma-button-secondary {
        background: rgba(15, 23, 42, 0.82);
        border-color: rgba(148, 163, 184, 0.18);
    }
</style>

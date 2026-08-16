<style>
    .simple-auth-page {
        --simple-auth-primary: #2563eb;
        --simple-auth-primary-hover: #1d4ed8;
        --simple-auth-primary-soft: #eff6ff;
        --simple-auth-primary-border: #bfdbfe;
        --simple-auth-ease-out: cubic-bezier(0.23, 1, 0.32, 1);
        background: #f6f7f9;
        font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-optical-sizing: auto;
    }

    .simple-auth-page .simple-auth-card {
        max-width: 27rem;
        border-color: #dfe3e8;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05), 0 12px 32px rgba(15, 23, 42, .045);
        transition: opacity 200ms var(--simple-auth-ease-out), transform 200ms var(--simple-auth-ease-out);
    }

    html body .simple-auth-page .simple-auth-brand:not(#comments *):not(#app *) {
        font-size: 1.5rem !important;
        line-height: 1.1 !important;
        letter-spacing: 0 !important;
    }

    html body .simple-auth-page .simple-auth-title:not(#comments *):not(#app *) {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        letter-spacing: 0 !important;
    }

    html body .simple-auth-page .simple-auth-description:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-description-email:not(#comments *):not(#app *) {
        font-size: 1rem !important;
        font-weight: 400 !important;
        line-height: 1.55 !important;
        letter-spacing: 0 !important;
    }

    html body .simple-auth-page .simple-auth-label:not(#comments *):not(#app *) {
        font-size: .9375rem !important;
        font-weight: 500 !important;
        line-height: 1.35 !important;
        letter-spacing: 0 !important;
    }

    html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *) {
        height: 3rem !important;
        min-height: 3rem !important;
        padding-right: 12px !important;
        padding-left: 12px !important;
        font-size: 1rem !important;
        line-height: normal !important;
        letter-spacing: 0 !important;
        transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease !important;
    }

    html body .simple-auth-page .simple-auth-input-wrap .simple-auth-input:not(#comments *):not(#app *) {
        padding-left: 38px !important;
    }

    html body .simple-auth-page .simple-auth-input-wrap .simple-auth-eye ~ .simple-auth-input:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-input-wrap .simple-auth-input:has(+ .simple-auth-eye):not(#comments *):not(#app *) {
        padding-right: 44px !important;
    }

    html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *):focus {
        border-color: var(--simple-auth-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14) !important;
    }

    html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *) {
        height: 2.875rem !important;
        min-height: 2.875rem !important;
        background: var(--simple-auth-primary) !important;
        font-size: .9375rem !important;
        font-weight: 600 !important;
        line-height: 2.875rem !important;
        letter-spacing: 0 !important;
        box-shadow: 0 1px 2px rgba(37, 99, 235, .22) !important;
        transition: background-color 160ms ease, box-shadow 160ms ease, transform 120ms var(--simple-auth-ease-out) !important;
    }

    html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *):hover {
        background: var(--simple-auth-primary-hover) !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, .2) !important;
    }

    html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *):active {
        transform: scale(.98);
    }

    html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *):focus-visible,
    html body .simple-auth-page a:not(#comments *):not(#app *):focus-visible,
    html body .simple-auth-page button:not(#comments *):not(#app *):focus-visible {
        outline: 3px solid rgba(37, 99, 235, .28) !important;
        outline-offset: 2px !important;
    }

    html body .simple-auth-page .simple-auth-remember:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-remember span:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-link:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-register:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-register a:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-footer:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-footer a:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-change:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-switch:not(#comments *):not(#app *) {
        font-size: .9375rem !important;
        line-height: 1.4 !important;
        letter-spacing: 0 !important;
    }

    .simple-auth-page input[type="checkbox"] {
        accent-color: var(--simple-auth-primary) !important;
    }

    .simple-auth-page .simple-auth-link:hover,
    .simple-auth-page .simple-auth-register a:hover,
    .simple-auth-page .simple-auth-footer a:hover,
    .simple-auth-page .simple-auth-change:hover,
    .simple-auth-page .simple-auth-switch:hover {
        color: var(--simple-auth-primary) !important;
    }

    .simple-auth-page .simple-auth-alert,
    .simple-auth-page .simple-auth-notice {
        opacity: 1;
        transform: translateY(0) scale(1);
        transition: opacity 200ms var(--simple-auth-ease-out), transform 200ms var(--simple-auth-ease-out);
    }

    .simple-auth-page .simple-auth-alert {
        padding: 12px 14px;
        font-size: .9375rem;
        line-height: 1.5;
    }

    .simple-auth-page .simple-auth-alert--success,
    .simple-auth-page .simple-auth-notice--success {
        border-color: var(--simple-auth-primary-border) !important;
        background: var(--simple-auth-primary-soft) !important;
        color: #1e40af !important;
    }

    .simple-auth-page .simple-auth-notice {
        grid-template-columns: 18px minmax(0, 1fr);
        column-gap: 10px;
        padding: 12px 14px;
        border-radius: 7px;
        box-shadow: 0 5px 16px rgba(15, 23, 42, .06);
    }

    .simple-auth-page .simple-auth-notice__icon {
        width: 18px;
        height: 18px;
        margin-top: 1px;
    }

    .simple-auth-page .simple-auth-notice__title {
        margin-bottom: 3px;
        font-size: .9375rem;
        font-weight: 600;
        line-height: 1.35;
        letter-spacing: 0;
    }

    .simple-auth-page .simple-auth-notice__text {
        font-size: .875rem;
        line-height: 1.5;
        letter-spacing: 0;
    }

    .simple-auth-page .simple-auth-notice--success .simple-auth-notice__icon,
    .simple-auth-page .simple-auth-notice--success .simple-auth-notice__title,
    .simple-auth-page .simple-auth-notice--success .simple-auth-notice__text {
        color: #1d4ed8 !important;
    }

    html.dark body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *):focus {
        border-color: #60a5fa !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .2) !important;
    }

    html.dark body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *) {
        background: var(--simple-auth-primary) !important;
    }

    html.dark body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *):hover {
        background: var(--simple-auth-primary-hover) !important;
    }

    html.dark .simple-auth-page .simple-auth-alert--success,
    html.dark .simple-auth-page .simple-auth-notice--success {
        border-color: rgba(96, 165, 250, .38) !important;
        background: rgba(37, 99, 235, .14) !important;
        color: #bfdbfe !important;
    }

    html.dark .simple-auth-page .simple-auth-notice--success .simple-auth-notice__icon,
    html.dark .simple-auth-page .simple-auth-notice--success .simple-auth-notice__title,
    html.dark .simple-auth-page .simple-auth-notice--success .simple-auth-notice__text {
        color: #bfdbfe !important;
    }

    @starting-style {
        .simple-auth-page .simple-auth-card {
            opacity: 0;
            transform: translateY(8px) scale(.99);
        }

        .simple-auth-page .simple-auth-alert,
        .simple-auth-page .simple-auth-notice {
            opacity: 0;
            transform: translateY(-6px) scale(.97);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .simple-auth-page .simple-auth-card,
        .simple-auth-page .simple-auth-alert,
        .simple-auth-page .simple-auth-notice {
            transform: none !important;
            transition: opacity 160ms ease !important;
        }

        @starting-style {
            .simple-auth-page .simple-auth-card,
            .simple-auth-page .simple-auth-alert,
            .simple-auth-page .simple-auth-notice {
                opacity: 0;
                transform: none;
            }
        }
    }

    @media (prefers-contrast: more) {
        .simple-auth-page .simple-auth-card,
        .simple-auth-page .simple-auth-input,
        .simple-auth-page .simple-auth-alert,
        .simple-auth-page .simple-auth-notice {
            border-color: currentColor !important;
        }
    }
</style>

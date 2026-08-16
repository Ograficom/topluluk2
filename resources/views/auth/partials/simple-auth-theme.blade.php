<style>
    .simple-auth-page {
        --simple-auth-primary: #2563eb;
        --simple-auth-primary-hover: #1d4ed8;
        --simple-auth-primary-soft: #eff6ff;
        --simple-auth-primary-border: #bfdbfe;
    }

    html body .simple-auth-page .simple-auth-brand:not(#comments *):not(#app *) {
        font-size: 22px !important;
        letter-spacing: 0 !important;
    }

    html body .simple-auth-page .simple-auth-title:not(#comments *):not(#app *) {
        font-size: 17px !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        letter-spacing: 0 !important;
    }

    html body .simple-auth-page .simple-auth-description:not(#comments *):not(#app *),
    html body .simple-auth-page .simple-auth-description-email:not(#comments *):not(#app *) {
        font-size: 13px !important;
        font-weight: 400 !important;
        line-height: 1.55 !important;
        letter-spacing: 0 !important;
    }

    html body .simple-auth-page .simple-auth-label:not(#comments *):not(#app *) {
        font-size: 13px !important;
        font-weight: 500 !important;
        line-height: 1.35 !important;
        letter-spacing: 0 !important;
    }

    html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *) {
        height: 42px !important;
        min-height: 42px !important;
        padding-right: 12px !important;
        padding-left: 12px !important;
        font-size: 14px !important;
        line-height: normal !important;
        letter-spacing: 0 !important;
        transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease !important;
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
        height: 40px !important;
        min-height: 40px !important;
        background: var(--simple-auth-primary) !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        line-height: 40px !important;
        letter-spacing: 0 !important;
        box-shadow: 0 1px 2px rgba(37, 99, 235, .22) !important;
        transition: background-color .18s ease, box-shadow .18s ease, transform .18s ease !important;
    }

    html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *):hover {
        background: var(--simple-auth-primary-hover) !important;
        box-shadow: 0 4px 10px rgba(37, 99, 235, .22) !important;
        transform: translateY(-1px);
    }

    html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *):active {
        transform: translateY(0);
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
        font-size: 13px !important;
        line-height: 1.4 !important;
        letter-spacing: 0 !important;
    }

    .simple-auth-page input[type="checkbox"] {
        accent-color: var(--simple-auth-primary) !important;
    }

    .simple-auth-page .simple-auth-alert,
    .simple-auth-page .simple-auth-notice {
        animation: simple-auth-notice-in .32s cubic-bezier(.22, 1, .36, 1) both;
    }

    .simple-auth-page .simple-auth-alert {
        padding: 12px 14px;
        font-size: 13px;
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
        animation: simple-auth-icon-in .4s .08s cubic-bezier(.34, 1.56, .64, 1) both;
    }

    .simple-auth-page .simple-auth-notice__title {
        margin-bottom: 3px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.35;
        letter-spacing: 0;
    }

    .simple-auth-page .simple-auth-notice__text {
        font-size: 12px;
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

    @keyframes simple-auth-notice-in {
        from { opacity: 0; transform: translateY(-8px) scale(.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes simple-auth-icon-in {
        from { opacity: 0; transform: scale(.65); }
        to { opacity: 1; transform: scale(1); }
    }

    @media (prefers-reduced-motion: reduce) {
        .simple-auth-page .simple-auth-alert,
        .simple-auth-page .simple-auth-notice,
        .simple-auth-page .simple-auth-notice__icon {
            animation: none !important;
        }

        html body .simple-auth-page .simple-auth-submit:not(#comments *):not(#app *),
        html body .simple-auth-page .simple-auth-input:not(#comments *):not(#app *) {
            transition: none !important;
        }
    }
</style>

<style>
    html body.alma-app .layout-main .ografi-feed-loadmore {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        margin: 18px 0 28px !important;
        padding: 0 !important;
        background: transparent !important;
    }

    html body.alma-app .layout-main .ografi-feed-loadmore__buttons {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
    }

    html body.alma-app .layout-main .ografi-feed-page-button--prev,
    html body.alma-app .layout-main .ografi-feed-loadmore__count,
    html body.alma-app .layout-main .ografi-feed-loadmore__text,
    html body.alma-app .layout-main .ografi-feed-page-button--next > span:not(.ografi-feed-page-button__icon):not(.ografi-feed-loadmore__spinner) {
        display: none !important;
    }

    html body.alma-app .layout-main :is(.ografi-feed-loadmore__button, .ografi-feed-page-button--next) {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 46px !important;
        min-width: 46px !important;
        max-width: 46px !important;
        height: 46px !important;
        min-height: 46px !important;
        max-height: 46px !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 999px !important;
        background: #ffffff !important;
        color: #111827 !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04) !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    html body.alma-app .layout-main :is(.ografi-feed-loadmore__button, .ografi-feed-page-button--next):is(:hover, :focus-visible) {
        background: #f8fafc !important;
        transform: translateY(-1px) !important;
        outline: none !important;
    }

    html body.alma-app .layout-main :is(
        .ografi-feed-loadmore__button > svg,
        .ografi-feed-loadmore__icon,
        .ografi-feed-loadmore__icon svg,
        .ografi-feed-page-button--next .ografi-feed-page-button__icon,
        .ografi-feed-page-button--next .ografi-feed-page-button__icon svg
    ) {
        display: block !important;
        width: 20px !important;
        height: 20px !important;
        margin: 0 !important;
        color: currentColor !important;
    }

    html body.alma-app .layout-main .ografi-feed-loadmore__spinner {
        display: none !important;
    }

    html body.alma-app .layout-main :is(.ografi-feed-loadmore__button, .ografi-feed-page-button--next).is-loading {
        pointer-events: none !important;
        opacity: .65 !important;
    }

    html body.alma-app .layout-main .ografi-feed-loadmore__button.is-loading > svg,
    html body.alma-app .layout-main .ografi-feed-page-button--next.is-loading .ografi-feed-page-button__icon {
        animation: ografi-feed-loader-spin .75s linear infinite !important;
    }

    @keyframes ografi-feed-loader-spin {
        to { transform: rotate(360deg); }
    }
</style>


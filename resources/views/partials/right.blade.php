<style>
    .ografi-right-stack {
        display: flex !important;
        flex-direction: column !important;
        gap: 18px !important;
        row-gap: 18px !important;
    }

    .ografi-right-stack > * {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .ografi-right-stack .ografi-sidebar-force {
        display: flex !important;
        flex-direction: column !important;
        gap: 18px !important;
        row-gap: 18px !important;
    }

    .ografi-right-stack .ografi-sidebar-card,
    .ografi-right-stack .alma-ad-slot {
        height: auto !important;
        min-height: 0 !important;
        aspect-ratio: auto !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .ografi-right-stack .alma-ad-slot__inner {
        width: 100% !important;
        height: auto !important;
        min-height: 0 !important;
        aspect-ratio: auto !important;
        padding: 0 !important;
    }

    .ografi-right-stack .alma-ad-slot__frame {
        width: 100% !important;
        min-height: 0 !important;
        border: 0 !important;
    }

    .ografi-ad-sticky {
        display: flex !important;
        flex-direction: column !important;
        gap: 18px !important;
        row-gap: 18px !important;
        z-index: 5 !important;
    }

    /*
        Sag sutunun kendisi, ana akis kadar uzun bir grid hucresine
        "stretch" ile yayilmazsa, reklam kutusunun sticky kalabilecegi
        alan sadece sag sutunun kendi (kisa) icerigi kadar olur ve akis
        cok daha uzun oldugunda reklam erkenden "birakiliyor". Bu yuzden
        sag sutunu grid satirinin tam yuksekligine yayiyoruz.
    */
    body.alma-app .layout-side--right {
        align-self: stretch !important;
    }

    body.alma-app .layout-side--right .ografi-right-stack {
        min-height: 100% !important;
    }

    body.alma-app .layout-side--right .ografi-ad-sticky {
        position: sticky !important;
        top: calc(var(--site-header-height, 70px) + 14px) !important;
        align-self: start !important;
    }
</style>

<div class="space-y-6 ografi-right-stack">
    @include('partials.right.widgets')

    <div class="layout-sticky ografi-ad-sticky">
        @include('partials.ads.slot', [
            'slotKey' => 'ads_sidebar_top',
        ])
    </div>
</div>

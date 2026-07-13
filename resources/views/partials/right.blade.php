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
</style>

<div class="space-y-6 ografi-right-stack">
    @include('partials.ads.slot', [
        'slotKey' => 'ads_sidebar_top',
    ])

    @include('partials.ads.context-slot', [
        'slotKey' => 'ads_sidebar_story',
    ])

    @include('partials.right.widgets')
</div>

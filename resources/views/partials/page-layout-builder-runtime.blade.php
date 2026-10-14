@php
    $ografiCurrentLayoutKey = \App\Services\PageLayoutCatalog::currentPageKey();
    $ografiLayoutTheme = \App\Models\ThemeSetting::currentOrNull();
    $ografiPageLayoutConfig = $ografiCurrentLayoutKey
        ? \App\Services\PageLayoutCatalog::publicConfig(
            $ografiCurrentLayoutKey,
            $ografiLayoutTheme?->page_layouts ?? [],
            request()->boolean('ografi_layout_preview'),
        )
        : null;
@endphp

@if($ografiPageLayoutConfig)
    <script type="application/json" id="ografi-page-layout-config">{!! json_encode($ografiPageLayoutConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script src="{{ asset('js/ografi-page-layout-runtime.js') }}" defer></script>
@endif

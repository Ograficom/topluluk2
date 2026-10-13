@php
    $ografiComponentStyles = \App\Services\ThemeComponentDesign::normalize($ografiBrand?->component_styles ?? []);
    $ografiComponentCss = app(\App\Services\ThemeComponentDesign::class)->render($ografiComponentStyles);
    $ografiFooter = $ografiComponentStyles['footer'];
@endphp

@if($ografiFooter['visible'])
    <footer class="ografi-site-footer" data-site-footer>
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 text-center sm:flex-row sm:text-left">
            <span>{{ $ografiFooter['text'] !== '' ? $ografiFooter['text'] : (date('Y') . ' © ' . config('app.name', 'Ografi')) }}</span>
            @if($ografiFooter['link_label'] !== '' && $ografiFooter['link_url'] !== '')
                <a href="{{ $ografiFooter['link_url'] }}">{{ $ografiFooter['link_label'] }}</a>
            @endif
        </div>
    </footer>
@endif

@if($ografiComponentCss !== '')
    <style id="ografi-component-design">
        {!! $ografiComponentCss !!}
    </style>
@endif

{{-- Keep administrator-authored custom CSS last so it can still override the visual controls. --}}
@if(trim((string) ($ografiCustomCssSafe ?? '')) !== '')
    <style id="ografi-custom-theme">
        {!! $ografiCustomCssSafe !!}
    </style>
@endif

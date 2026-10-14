@php
    $ografiRawComponentStyles = $ografiBrand?->component_styles;
    $ografiComponentStyles = \App\Services\ThemeComponentDesign::normalize($ografiRawComponentStyles ?? []);
    $ografiComponentCss = is_array($ografiRawComponentStyles) && $ografiRawComponentStyles !== []
        ? app(\App\Services\ThemeComponentDesign::class)->render($ografiComponentStyles)
        : '';
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

@if(request()->boolean('ografi_component_preview') && (auth()->user()?->isAdmin() ?? false))
    <script>
        (() => {
            const previewType = 'ografi-component-design-apply-preview';
            const styleId = 'ografi-component-design-live-preview-style';

            window.addEventListener('message', (event) => {
                if (event.origin !== window.location.origin || event.source !== window.parent || !event.data) return;
                if (event.data.type !== previewType || typeof event.data.css !== 'string') return;
                if (event.data.css.length > 20000) return;

                let style = document.getElementById(styleId);
                if (!style) {
                    style = document.createElement('style');
                    style.id = styleId;
                    document.body.appendChild(style);
                }

                // Draft CSS is rendered server-side from the allow-listed style schema.
                // Appending it after the saved theme/custom CSS gives the draft preview
                // the correct cascade without writing anything to the database.
                style.textContent = event.data.css;
            });
        })();
    </script>
@endif

@php
    $slotKey = (string) ($slotKey ?? $key ?? '');
    $wrapperClass = trim((string) ($wrapperClass ?? ''));
    $device = (string) ($device ?? 'all');
    $content = $slotKey !== '' ? \App\Models\Snippet::render($slotKey) : '';

    $classes = ['alma-ad-slot'];
    if ($device === 'desktop') {
        $classes[] = 'alma-ad-slot--desktop';
    } elseif ($device === 'mobile') {
        $classes[] = 'alma-ad-slot--mobile';
    }
    if ($wrapperClass !== '') {
        $classes[] = $wrapperClass;
    }
@endphp

@if(trim($content) !== '')
    <div class="{{ implode(' ', $classes) }}" data-ad-slot="{{ $slotKey }}">
        @include('partials.ads.icon')
        <div class="alma-ad-slot__inner">
            <iframe
                class="alma-ad-slot__frame"
                src="{{ route('ads.frame', ['slotKey' => $slotKey]) }}"
                title="Reklam"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                sandbox="allow-scripts allow-popups allow-popups-to-escape-sandbox allow-forms"
                style="display:block;width:100%;height:1px;min-height:0;border:0;background:transparent;"
            ></iframe>
        </div>
    </div>

    @once
        <script>
            window.addEventListener('message', function (event) {
                const data = event.data || {};

                if (data.type !== 'alma-ad-frame-resize' || !data.slotKey) {
                    return;
                }

                const nextHeight = Math.max(1, Math.ceil(Number(data.height) || 0));

                document.querySelectorAll('iframe.alma-ad-slot__frame').forEach(function (iframe) {
                    if (iframe.contentWindow === event.source) {
                        iframe.style.height = nextHeight + 'px';
                    }
                });
            });
        </script>
    @endonce
@endif

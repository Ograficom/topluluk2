@php
    $slotKey = (string) ($slotKey ?? $key ?? '');
    $wrapperClass = trim((string) ($wrapperClass ?? ''));
    $device = (string) ($device ?? 'all');
    $content = $slotKey !== '' ? \App\Models\Snippet::render($slotKey) : '';

    // Sol sutun, masaustunde sabit (position:fixed) menu nedeniyle normal
    // akista yer kaplamiyor - bos oldugunda kendi reklam cagrimizi burada
    // gostermek menuyle cakisiyor. Gercek bir reklam girildiyse yine gorunur,
    // sadece bos-durum yedeğimiz bu tek slotta devre disi.
    $skipHouseAd = $slotKey === 'ads_left_sidebar_top';

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

@if($slotKey !== '' && trim($content) !== '')
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
@elseif($slotKey !== '' && !$skipHouseAd)
    {{--
        Snippet bos/pasif ise kutuyu tamamen bos birakmak yerine (bozuk gorunuyor
        ve reklam alani hic tanitilmiyor), Ografi'nin kendi "reklam ver" cagrisini
        gosteriyoruz - ayni boyut/konumda, gercek reklam geldiginde otomatik yerini birakir.
    --}}
    <div class="{{ implode(' ', array_filter([$device === 'desktop' ? 'alma-ad-slot--desktop' : null, $device === 'mobile' ? 'alma-ad-slot--mobile' : null, $wrapperClass !== '' ? $wrapperClass : null])) }}" data-ad-slot="{{ $slotKey }}" data-ad-slot-house="1">
        @include('partials.ads.house-ad', ['slotKey' => $slotKey])
    </div>
@endif

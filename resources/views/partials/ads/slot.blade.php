@php
    $slotKey = (string) ($slotKey ?? $key ?? '');
    $wrapperClass = trim((string) ($wrapperClass ?? ''));
    $device = (string) ($device ?? 'all');
    $content = $slotKey !== '' ? \App\Models\Snippet::render($slotKey) : '';

    // Admin Filament'ta bu konumu bilerek kapattiysa (Aktif: kapali), kutu
    // hicbir sekilde gorunmemeli - ne gercek reklam ne de "reklam ver" yedegi.
    $isDisabled = $slotKey !== '' && trim($content) === '' && \App\Models\Snippet::isExplicitlyDisabled($slotKey);

    $classes = ['alma-ad-slot', 'alma-ad-slot--dismissible'];
    if ($device === 'desktop') {
        $classes[] = 'alma-ad-slot--desktop';
    } elseif ($device === 'mobile') {
        $classes[] = 'alma-ad-slot--mobile';
    }
    if ($wrapperClass !== '') {
        $classes[] = $wrapperClass;
    }

    // IAB standart olculerine gore kutu sekli: genis akis alanlari icin
    // leaderboard (728x90), diger tum konumlar icin rectangle (300x250).
    $leaderboardSlots = ['ads_feed_top', 'ads_feed_inline'];
    $adShape = in_array($slotKey, $leaderboardSlots, true) ? 'leaderboard' : 'rectangle';
@endphp

@if($slotKey !== '' && trim($content) !== '')
    <div class="{{ implode(' ', $classes) }}" data-ad-slot="{{ $slotKey }}">
        @include('partials.ads.tagbar')
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
@elseif($slotKey !== '' && !$isDisabled)
    {{--
        Snippet hic yapilandirilmamis/icerik girilmemisse kutuyu tamamen bos
        birakmak yerine (bozuk gorunuyor ve reklam alani hic tanitilmiyor),
        Ografi'nin kendi "reklam ver" cagrisini gosteriyoruz - ayni boyut/
        konumda, gercek reklam geldiginde otomatik yerini birakir. Ancak admin
        bu konumu Filament'tan bilerek kapattiysa ($isDisabled), bu yedek de
        gosterilmez - admin'in "burada hicbir sey olmasin" tercihine saygi
        gosterilir.
    --}}
    <div class="{{ implode(' ', $classes) }}" data-ad-slot="{{ $slotKey }}" data-ad-slot-house="1" data-ad-shape="{{ $adShape }}">
        @include('partials.ads.tagbar')
        @include('partials.ads.house-ad', ['slotKey' => $slotKey, 'shape' => $adShape])
    </div>
@endif

@once
    <style>
        /* ============================================================
           Reklam kutusu tasarimi - IAB standart reklam olculerine
           (leaderboard 728x90, rectangle 300x250) ve profesyonel
           "house ad" konvansiyonlarina (kenara kadar dolu gorsel, kutu
           icinde kutu yok, kucuk kose etiketi - buyuk ayri bar degil)
           dayali. Bkz: MonetizePros/IAB house-ad arastirmasi.
           ============================================================ */
        .alma-ad-slot {
            width: 100%;
            max-width: 728px;
            border-radius: 8px;
            overflow: hidden;
            margin: 0 auto 20px auto;
            position: relative;
        }

        .alma-ad-slot--dismissible {
            position: relative;
        }

        /* House-ad govde gorseli her zaman kenardan kenara, bosluksuz */
        .alma-ad-slot[data-ad-slot-house="1"] .house-ad {
            display: block;
            width: 100%;
            height: 100%;
        }

        .alma-ad-slot[data-ad-slot-house="1"] .house-ad img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* body.alma-app .alma-ad-slot { min-height:128px !important; ... } site geneli
           kurali ayni ozellik uzerinde !important kullandigi icin asagidaki oran
           kilitleme kurallari da !important olmak zorunda, aksi halde min-height
           tabani aspect-ratio hesaplamasini eziyor (bkz. bu oturumdaki tekrar eden
           .kapat-btn/.alma-ad-tagbar__close sifirlama sorunuyla ayni kok neden). */
        body.alma-app .alma-ad-slot[data-ad-slot-house="1"] {
            min-height: 0 !important;
        }

        /* Leaderboard orani (728x90 ~ 8:1) - ust/ic akis reklamlari */
        body.alma-app .alma-ad-slot[data-ad-slot-house="1"][data-ad-shape="leaderboard"] {
            aspect-ratio: 728 / 90 !important;
        }

        /* Rectangle orani (300x250 ~ 1.2:1) - kompakt/mobil reklamlari */
        body.alma-app .alma-ad-slot[data-ad-slot-house="1"][data-ad-shape="rectangle"] {
            aspect-ratio: 300 / 250 !important;
            max-width: 300px !important;
        }

        /* ============================================================
           Kucuk kose etiketi - IAB native reklam etiketi konvansiyonuna
           uygun: gorselin uzerine bindirilmis, dusuk kontrastli, kucuk.
           Buyuk ayri bir bar DEGIL.
           ============================================================ */
        .alma-ad-tagbar {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .alma-ad-tagbar__label {
            display: inline-flex;
            align-items: center;
            height: 18px;
            padding: 0 7px;
            border-radius: 4px;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #ffffff;
        }

        .alma-ad-tagbar__close,
        body.alma-app .alma-ad-tagbar__close {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            padding: 0;
            border: 0 !important;
            border-radius: 4px;
            background: rgba(15, 23, 42, 0.55) !important;
            color: #ffffff !important;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .alma-ad-tagbar__close:hover,
        body.alma-app .alma-ad-tagbar__close:hover {
            background: rgba(220, 38, 38, 0.85) !important;
            color: #ffffff !important;
        }

        .alma-ad-tagbar__close svg {
            width: 10px;
            height: 10px;
        }

        .alma-ad-slot--dismissing {
            opacity: 0 !important;
            pointer-events: none;
            transition: opacity .15s ease;
        }
    </style>
    <script>
        document.addEventListener('click', function (event) {
            var closeBtn = event.target.closest('[data-ad-dismiss]');
            if (!closeBtn) {
                return;
            }

            var wrapper = closeBtn.closest('.alma-ad-slot--dismissible');
            if (!wrapper) {
                return;
            }

            wrapper.classList.add('alma-ad-slot--dismissing');
            window.setTimeout(function () {
                wrapper.remove();
            }, 160);
        });
    </script>
@endonce
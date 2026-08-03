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
    <div class="{{ implode(' ', array_merge($classes, ['alma-ad-slot--bare'])) }}" data-ad-slot="{{ $slotKey }}" data-ad-slot-house="1">
        @include('partials.ads.house-ad', ['slotKey' => $slotKey])
    </div>
@endif

@once
    <style>
        /* Bos reklam alanlarinin yeni kutu tasarimi - kullanicinin verdigi kod
           birebir korunuyor (stil degistirilmiyor). */
        .reklam-alani {
            width: 100%;
            max-width: 728px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
        }

        .reklam-ust {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background-color: #ffffff;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 700;
            color: #333;
        }

        .kapat-btn {
            background-color: #1a1a1a;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            width: 20px;
            height: 20px;
            margin-left: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .kapat-btn:hover {
            background-color: #444;
        }

        .reklam-icerik {
            width: 100%;
            line-height: 0;
        }

        .reklam-resim {
            width: 100%;
            height: auto;
            display: block;
        }

        /* .alma-ad-slot'un kendi kart cercevesi (border/arka plan/dolgu) yeni kutu
           tasarimiyla cakismasin diye bos reklam alanlarinda kaldiriliyor - boylece
           .reklam-alani kendi cercevesiyle temiz gorunuyor. */
        .alma-ad-slot--bare {
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            overflow: visible !important;
        }

        .alma-ad-slot--dismissible {
            position: relative;
        }

        .alma-ad-tagbar {
            position: absolute;
            top: 10px;
            right: 12px;
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .alma-ad-tagbar__label {
            display: inline-flex;
            align-items: center;
            height: 20px;
            padding: 0 8px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }

        .alma-ad-tagbar__close {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            padding: 0;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: #ffffff;
            color: #94a3b8;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease;
        }

        .alma-ad-tagbar__close:hover {
            background: #f1f5f9;
            color: #475569;
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

        document.addEventListener('click', function (event) {
            var kapatBtn = event.target.closest('[data-reklam-kapat]');
            if (!kapatBtn) {
                return;
            }

            var box = document.getElementById(kapatBtn.getAttribute('data-reklam-kapat'));
            if (box) {
                box.style.display = 'none';
            }
        });
    </script>
@endonce

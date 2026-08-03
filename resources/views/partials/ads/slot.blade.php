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
    <div class="{{ implode(' ', $classes) }}" data-ad-slot="{{ $slotKey }}" data-ad-slot-house="1">
        @include('partials.ads.tagbar')
        @include('partials.ads.house-ad', ['slotKey' => $slotKey])
    </div>
@endif

@once
    <style>
        .house-ad {
            display: flex;
            width: 100%;
            box-sizing: border-box;
            flex-direction: column;
            position: relative;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            padding: 16px;
            text-decoration: none;
            overflow: hidden;
        }

        .house-ad__visual {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-sizing: border-box;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #e9edf3;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        }

        .house-ad__visual--vertical {
            width: 100%;
            aspect-ratio: 16 / 10;
            margin-bottom: 4px;
        }

        .house-ad__visual--vertical .house-ad__logo {
            width: 64px;
            height: 64px;
        }

        .house-ad__visual--wide {
            width: 96px;
            height: 96px;
        }

        .house-ad__visual--wide .house-ad__logo {
            width: 56px;
            height: 56px;
        }

        .house-ad__visual--compact {
            width: 64px;
            height: 64px;
        }

        .house-ad__visual--compact .house-ad__logo {
            width: 40px;
            height: 40px;
        }

        .house-ad__body {
            display: flex;
            flex: 1 1 auto;
            min-height: 0;
        }

        .house-ad__body--vertical {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            padding-top: 8px;
        }

        .house-ad__body--wide {
            align-items: center;
            gap: 20px;
        }

        .house-ad__body--compact {
            align-items: center;
            gap: 14px;
        }

        .house-ad__logo {
            display: block;
            width: 40px;
            height: 40px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .house-ad__logo--wide {
            width: 56px;
            height: 56px;
        }

        .house-ad__copy {
            min-width: 0;
            flex: 1 1 auto;
        }

        .house-ad__title {
            margin: 0;
            color: #0f172a;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.01em;
        }

        .house-ad__title--compact {
            font-size: 13px;
        }

        .house-ad__text {
            margin: 0;
            color: #64748b;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .house-ad__body--vertical .house-ad__text {
            max-width: 220px;
        }

        .house-ad__cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 999px;
            background: #2563eb;
            color: #ffffff !important;
            padding: 8px 16px;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .house-ad__body--compact .house-ad__cta {
            margin-top: 6px;
            padding: 6px 13px;
            font-size: 11.5px;
        }

        .house-ad:hover .house-ad__cta {
            background: #1d4ed8;
        }

        @media (max-width: 480px) {
            .house-ad__body--wide {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
                gap: 10px;
            }

            .house-ad__body--wide .house-ad__cta--wide {
                align-self: flex-start;
            }
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
    </script>
@endonce

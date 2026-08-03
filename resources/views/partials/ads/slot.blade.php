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
        
        <!-- PREMIUM HOUSE AD (REKLAM VER) BAŞLANGICI -->
        <div class="reklam-alani" id="reklamKutusu-{{ $slotKey }}">
            <div class="reklam-ust">
                <span class="reklam-etiketi">Reklam</span>
                <!-- Buton hedefi dinamik $slotKey ile belirlendi -->
                <button class="kapat-btn" data-reklam-kapat="reklamKutusu-{{ $slotKey }}" title="Reklamı Kapat">&#x2715;</button>
            </div>
            <div class="reklam-icerik">
                <!-- href kısmına kendi Reklam Ver sayfanın linkini girebilirsin -->
                <a href="/reklam-ver">
                    <img src="https://picsum.photos/728/200" alt="Reklam Ver" class="reklam-resim">
                </a>
            </div>
        </div>
        <!-- PREMIUM HOUSE AD BİTİŞİ -->

    </div>
@endif

@once
    <style>
        /* ========================================================= */
        /* PREMIUM HOUSE AD (YEDEK REKLAM) TASARIMI                  */
        /* ========================================================= */
        .reklam-alani {
            width: 100%;
            max-width: 728px;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15), 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            position: relative;
            margin: 0 auto; /* Kutuyu bulunduğu alanda ortalamak için */
        }

        .reklam-ust {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background-color: #fbfbfb;
            padding: 6px 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .reklam-etiketi {
            background-color: #eeeeee;
            color: #666666;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 8px;
            border-radius: 5px;
            margin-right: 10px;
            user-select: none;
        }

        .kapat-btn,
        body.alma-app .kapat-btn {
            background-color: #e4e4e4 !important;
            color: #555555 !important;
            border: none;
            border-radius: 6px;
            width: 22px;
            height: 22px;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            padding: 0;
        }

        .kapat-btn:hover,
        body.alma-app .kapat-btn:hover {
            background-color: #ff4757 !important;
            color: #ffffff !important;
            transform: rotate(90deg);
            box-shadow: 0 3px 8px rgba(255, 71, 87, 0.3);
        }

        .reklam-icerik {
            width: 100%;
            line-height: 0;
            background-color: #f5f5f5;
        }

        .reklam-resim {
            width: 100%;
            height: auto;
            display: block;
            transition: opacity 0.2s ease;
        }

        .reklam-resim:hover {
            opacity: 0.93; /* Üzerine gelince resmin hafif solması tıkla hissini artırır */
        }

        /* ========================================================= */
        /* ALMA AD SLOT SİSTEM STİLLERİ                              */
        /* ========================================================= */
        
        /* Premium tasarımın kendi gölgesi olduğu için sistemin dış çerçevesi iptal edilir */
        .alma-ad-slot--bare {
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            overflow: visible !important;
            box-shadow: none !important;
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
        // 1. Standart sistem reklamını (Iframe vb.) kapatma
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

        // 2. Premium Yedek Reklamı (House Ad) Kapatma ve Animasyonu
        document.addEventListener('click', function (event) {
            var kapatBtn = event.target.closest('[data-reklam-kapat]');
            if (!kapatBtn) {
                return;
            }

            var box = document.getElementById(kapatBtn.getAttribute('data-reklam-kapat'));
            if (box) {
                // Küçülme ve silinme efekti
                box.style.transition = "opacity 0.3s ease, transform 0.3s ease";
                box.style.opacity = "0";
                box.style.transform = "scale(0.96)";
                
                // Animasyon bitince DOM'da yer kaplamaması için display: none
                window.setTimeout(function () {
                    box.style.display = 'none';
                }, 300);
            }
        });
    </script>
@endonce
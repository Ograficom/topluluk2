@extends('layouts.app')

@section('title', __('site.tags_page.title'))

@section('content')
    @php
        $themeTags = \App\Models\ThemeSetting::render('tags');
        $sort = $sort ?? 'popular';
        $sortOptions = [
            'popular' => ['label' => 'Popüler', 'icon' => 'lucide:flame'],
            'newest' => ['label' => 'Yeni', 'icon' => 'lucide:sparkles'],
            'oldest' => ['label' => 'Eski', 'icon' => 'lucide:history'],
        ];
    @endphp
    @if ($themeTags !== '')
        <div class="mb-4">
            {!! $themeTags !!}
        </div>
    @endif

    <style>
        /* Arama + siralama tetikleyicileri artik bagimsiz bir kutu degil,
           page-title-identity kutusunun ("trailing" slotu) icinde, kutunun sag
           ucunda duruyor - Kullanicilar sayfasindaki users-toolbar ile AYNI
           desen (once sadece siralama vardi, simdi solunda arama ikonu da var).
           Ikon butonlari Arama sayfasindaki ayarlar/bilgi tetikleyicileriyle
           (og-search-settings__trigger) AYNI kalip: 32x32, cift class ile
           sitedeki genel buton sifirlamasindan ozgullukce ustun, basinca
           translateY(1px) ani geri bildirim. */
        .tags-toolbar {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .tags-sort {
            position: relative;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .tags-toolbar__icon.tags-toolbar__icon {
            display: inline-flex !important;
            flex: 0 0 auto;
            width: 32px !important;
            height: 32px !important;
            align-items: center;
            justify-content: center;
            border: 0 !important;
            border-radius: 999px !important;
            background: transparent !important;
            color: #52525b !important;
            cursor: pointer;
            transition: background-color .15s ease, transform .08s ease-out;
        }

        .tags-toolbar__icon:active {
            transform: translateY(1px);
        }

        .tags-toolbar__icon iconify-icon {
            font-size: 16px;
        }

        .tags-toolbar__icon.tags-toolbar__icon:hover,
        .tags-toolbar__icon.tags-toolbar__icon:focus-visible,
        .tags-toolbar__icon.tags-toolbar__icon[aria-expanded="true"],
        .tags-sort.is-open .tags-toolbar__icon.tags-toolbar__icon {
            background: #f3f4f6 !important;
            outline: none;
        }

        html.dark .tags-toolbar__icon {
            color: #cbd5e1 !important;
        }

        html.dark .tags-toolbar__icon:hover,
        html.dark .tags-toolbar__icon:focus-visible,
        html.dark .tags-toolbar__icon[aria-expanded="true"],
        html.dark .tags-sort.is-open .tags-toolbar__icon {
            background: #1e293b !important;
        }

        /* Arama paneli - Kullanicilar sayfasindaki users-search-panel ile ayni
           grid-satir buyume/kuculme animasyonu: acilirken "materyal" gibi
           belirir, kapanirken katlanir. */
        .tags-search-panel {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows .2s ease, opacity .15s ease;
        }

        .tags-search-panel.is-open {
            grid-template-rows: 1fr;
            opacity: 1;
        }

        .tags-search-panel__inner {
            min-height: 0;
            overflow: hidden;
        }

        .tags-search-panel__form {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 3px;
            padding: 4px 4px 4px 14px;
            border: 1px solid rgba(217, 221, 227, .78);
            border-radius: 999px;
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            box-shadow: 0 1px 0 rgba(255, 255, 255, .55) inset, 0 10px 28px rgba(15, 23, 42, .08);
        }

        /* Input#id + iki class + input turu, Kullanicilar sayfasindaki ayni
           hotfix'i tekrar eder: sitedeki genel "body.alma-app :where(input,
           textarea, select):not(#comments *) {background:var(--ui-surface-muted)
           !important}" kuralini ozgulluk kiyaslamasinda class katmaninda gececek
           sekilde eziyor. */
        input#tags-search-input.tags-search-panel__input.tags-search-panel__input {
            flex: 1 1 auto;
            min-width: 0;
            min-height: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 6px 0 !important;
            font-size: 14px;
            color: #050505 !important;
            outline: none !important;
        }

        input#tags-search-input.tags-search-panel__input.tags-search-panel__input::placeholder {
            color: #9ca3af;
        }

        .tags-search-panel__submit {
            flex: 0 0 auto;
        }

        html.dark .tags-search-panel__form {
            border-color: rgba(63, 63, 70, .76);
            background: rgba(24, 24, 27, .82);
            box-shadow: 0 1px 0 rgba(255, 255, 255, .08) inset, 0 10px 28px rgba(0, 0, 0, .22);
        }

        html.dark input#tags-search-input.tags-search-panel__input.tags-search-panel__input {
            color: #fafafa !important;
        }

        /* Canli arama sirasinda liste hafifce sonukleserek "araniyor" hissi
           verir; sonuc gelince normale doner. */
        .tags-list {
            transition: opacity .15s ease;
        }

        .tags-list.is-loading {
            opacity: .55;
        }

        /* Post-show yukleme dalgasi: mevcut kartlar da iskelet satirlari gibi
           sabit boyutlarini koruyarak ustunden yumusak bir shimmer gecirir. */
        .tags-list.is-loading .tag-row:not(.tag-row--skeleton) {
            position: relative;
            overflow: hidden;
        }

        .tags-list.is-loading .tag-row:not(.tag-row--skeleton)::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background: linear-gradient(105deg, transparent 12%, rgba(255, 255, 255, .62) 45%, transparent 78%);
            background-size: 220% 100%;
            animation: ografiImgWave 1.15s ease-in-out infinite;
        }

        html.dark .tags-list.is-loading .tag-row:not(.tag-row--skeleton)::after {
            background: linear-gradient(105deg, transparent 12%, rgba(255, 255, 255, .12) 45%, transparent 78%);
            background-size: 220% 100%;
        }

        .tags-list__empty {
            padding: 24px 0;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        html.dark .tags-list__empty {
            color: #94a3b8;
        }

        @media (prefers-reduced-motion: reduce) {
            .tags-search-panel,
            .tags-list {
                transition: none;
            }
        }

        @media (prefers-reduced-transparency: reduce) {
            .tags-search-panel__form {
                background: #ffffff;
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
            }

            html.dark .tags-search-panel__form {
                background: #18181b;
            }
        }

        /* Acilir menu - once [hidden] ozniteligiyle ac/kapa yapiyordu (duz,
           animasyonsuz). Arama sayfasindaki ayarlar menusuyle ayni "materialize,
           don't just fade" davranisina tasindi: visibility+opacity+kucuk
           olcek/kayma, tetikleyiciden (sag ust) belirir. [hidden] KASITLI
           kullanilmiyor - Chromium'da display:none donusumu bazen
           getBoundingClientRect()'i 0x0 birakiyor (bkz. arama sayfasi notu). */
        .tags-sort__menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 190px;
            border-radius: 16px;
            border: 1px solid #e4e4e7;
            background: #ffffff;
            padding: 8px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .14);
            z-index: 40;
            visibility: hidden;
            opacity: 0;
            transform: scale(.96) translateY(-6px);
            transform-origin: top right;
            transition: opacity .16s ease, transform .16s ease, visibility 0s linear .16s;
        }

        .tags-sort__menu.is-open {
            visibility: visible;
            opacity: 1;
            transform: scale(1) translateY(0);
            transition: opacity .16s ease, transform .16s ease;
        }

        @media (prefers-reduced-motion: reduce) {
            .tags-sort__menu {
                transform: none;
                transition: opacity .12s ease, visibility 0s linear .12s;
            }

            .tags-sort__menu.is-open {
                transform: none;
                transition: opacity .12s ease;
            }
        }

        .tags-sort__label {
            display: block;
            margin: 4px 8px 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .tags-sort__divider {
            height: 1px;
            margin: 4px 0 8px;
            background: rgba(148, 163, 184, .22);
        }

        .tags-sort__avatar {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            border-radius: 999px;
            object-fit: cover;
        }

        .tags-sort__options {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .tags-sort__option {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 36px;
            padding: 0 10px;
            border-radius: 10px;
            color: #3f3f46;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease;
        }

        .tags-sort__option iconify-icon {
            font-size: 15px;
            flex-shrink: 0;
        }

        .tags-sort__option[aria-current="true"],
        .tags-sort__option:hover,
        .tags-sort__option:focus-visible {
            background: #f3f4f6;
            color: #0f172a;
            outline: none;
        }

        .tags-sort__option[aria-current="true"] {
            color: #1d4ed8;
        }

        .tags-sort__option[aria-current="true"] iconify-icon {
            color: #2563eb;
        }

        html.dark .tags-sort__menu {
            background: #18181b;
            border-color: #27272a;
        }

        html.dark .tags-sort__label {
            color: #71717a;
        }

        html.dark .tags-sort__option {
            color: #d4d4d8;
        }

        html.dark .tags-sort__option[aria-current="true"],
        html.dark .tags-sort__option:hover,
        html.dark .tags-sort__option:focus-visible {
            background: #27272a;
            color: #f4f4f5;
        }

        html.dark .tags-sort__option[aria-current="true"] {
            color: #93c5fd;
        }

        html.dark .tags-sort__option[aria-current="true"] iconify-icon {
            color: #93c5fd;
        }

        /* Etiket satiri - Arama sayfasindaki Kategoriler/Sayfalar sonuc
           satirlariyla (og-result-row--chip) AYNI iOS Ayarlar tarzi desen:
           solda renkli ikon rozeti, ortada isim, sagda sayi rozeti + gezinme
           oku - once sadece isim+sayi olan duz bir satirdi. */
        .tag-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, .9);
            background: #ffffff;
            text-decoration: none;
            transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
        }

        .tag-row:hover,
        .tag-row:focus-visible {
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, .06);
            outline: none;
        }

        .tag-row:active {
            transform: translateY(0) scale(.99);
            transition: transform 80ms ease-out;
        }

        .tag-row__icon {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: linear-gradient(160deg, #dbeafe, #eff6ff);
            color: #2563eb;
            font-size: 16px;
        }

        .tag-row__name {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 600;
            color: #0f172a;
        }

        .tag-row__count {
            flex: 0 0 auto;
            padding: 3px 10px;
            border-radius: 999px;
            background: #f1f5f9;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        .tag-row__chevron {
            flex: 0 0 auto;
            font-size: 15px;
            color: #cbd5e1;
        }

        html.dark .tag-row {
            background: #18181b;
            border-color: #27272a;
        }

        html.dark .tag-row:hover {
            background: #1f1f23;
        }

        html.dark .tag-row__icon {
            background: linear-gradient(160deg, #1e3a8a, #172554);
            color: #93c5fd;
        }

        html.dark .tag-row__name {
            color: #f4f4f5;
        }

        html.dark .tag-row__count {
            background: #27272a;
            color: #a1a1aa;
        }

        html.dark .tag-row__chevron {
            color: #52525b;
        }

        /* Yukleme sirasinda gercek satirla ayni boyutta dalgali (shimmer)
           iskelet kutu - sayfadaki gorsellerle ayni ografiImgWave animasyonunu
           kullanir (bkz. layouts/app.blade.php), boylece tum site tek bir
           yukleme dili paylasir. */
        .tag-row--skeleton {
            min-height: 60px;
            border-color: transparent;
            background: linear-gradient(105deg, #eef2fb 0%, #ffffff 45%, #eef2fb 82%);
            background-size: 200% 100%;
            animation: ografiImgWave 1.15s ease-in-out infinite;
            pointer-events: none;
        }

        html.dark .tag-row--skeleton {
            background: linear-gradient(105deg, #18181b 0%, #27272a 45%, #18181b 82%);
            background-size: 200% 100%;
        }

        /* Etiket baslik kutusu her ekranda beyaz kalir. Kutunun ustundeki
           bosluk, sayfanin #f6f4f0 arka plan rengini koruyan yari saydam
           katmanla tamamen kaplanir; arkadan gecen icerik hafifce bulanir. */
        .page-title-identity.page-title-identity {
            overflow: visible;
            isolation: isolate;
            border: 1px solid #e1e5eb !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            background-image: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: none !important;
            filter: none !important;
            opacity: 1 !important;
        }

        /* Partial yerine bu sayfada dogrudan uretilen gercek beyaz kapsul. */
        .tags-page-title.tags-page-title {
            display: flex !important;
            box-sizing: border-box !important;
            position: relative !important;
            z-index: 3 !important;
            width: 100% !important;
            min-height: 38px !important;
            align-items: center !important;
            gap: 0 !important;
            padding: 2px 10px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            background-image: none !important;
            box-shadow: none !important;
            opacity: 1 !important;
        }

        .tags-page-title__back {
            position: relative;
            z-index: 3;
            display: inline-flex;
            flex: 0 0 auto;
            width: 34px;
            height: 30px;
            align-items: center;
            justify-content: flex-start;
            margin-right: 10px;
            padding: 0 10px 0 2px;
            border: 0 !important;
            border-right: 1px solid #e5e7eb !important;
            border-radius: 0 !important;
            background: #ffffff !important;
            color: #111827 !important;
            text-decoration: none !important;
        }

        .tags-page-title__back iconify-icon {
            font-size: 17px;
        }

        .tags-page-title__text {
            position: relative;
            z-index: 3;
            min-width: 0;
            color: #111111 !important;
            font-size: 18px;
            font-weight: 500;
            line-height: 1.2;
            white-space: nowrap;
        }

        .tags-page-title__trailing {
            position: relative;
            z-index: 3;
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        body.route-tags .page-title-identity__edge-blur {
            display: block;
            position: absolute;
            z-index: -1;
            pointer-events: none;
            background: rgba(246, 244, 240, 0.62);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        /* Blur yalnizca baslik kutusunun ve main sutununun genisliginde kalir;
           sol menuye veya sag panele tasmaz. */
        body.route-tags .page-title-identity__edge-blur--top {
            right: 0;
            bottom: -6px;
            left: 0;
            width: 100%;
            height: calc(100% + 18px);
            transform: none;
            background: rgba(246, 244, 240, 0.62);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        body.route-tags [data-tags-search-panel] {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        body.route-tags [data-tags-search-panel]::before {
            display: none !important;
            content: none !important;
        }

        body.route-tags [data-tags-search-panel] > * {
            position: relative;
            z-index: 1;
        }

        body.route-tags .tags-search-panel__inner,
        body.route-tags .tags-search-panel__form {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        html.dark .page-title-identity.page-title-identity {
            border-color: #e1e5eb !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            background-image: none !important;
            color: #111827 !important;
        }

        html.dark body.route-tags .page-title-identity .tags-toolbar__icon {
            color: #52525b !important;
        }

        /* Mobilde de beyaz kutu + tam genislikte hafif blur korunur. */
        @media (max-width: 640px) {
            body.route-tags .page-title-identity {
                background: #ffffff !important;
            }

            body.route-tags .page-title-identity__edge-blur--top {
                height: calc(100% + 18px + env(safe-area-inset-top, 0px));
            }
        }

        /* Masaustunde header kaybolmaz; Etiketler kimlik kutusu ve acilan
           arama paneli main icinde header'in altinda sticky kalir. */
        @media (min-width: 641px) {
            body.route-tags .site-header {
                position: sticky;
                top: 0;
                z-index: 50;
            }

            body.route-tags .page-title-identity {
                position: sticky;
                top: 64px;
                z-index: 30;
                min-height: 34px;
                padding: 2px 10px;
                border: 1px solid #e1e5eb !important;
                border-radius: 999px !important;
                background: #ffffff !important;
                background-color: #ffffff !important;
                background-image: none !important;
            }

            body.route-tags [data-tags-search-panel] {
                position: sticky;
                top: 104px;
                z-index: 29;
                background: transparent !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }

            body.route-tags .page-title-identity__edge-blur--top {
                height: calc(100% + 18px);
            }

            html.dark body.route-tags [data-tags-search-panel] {
                background: rgba(24, 24, 27, .82);
            }

            html.dark body.route-tags .page-title-identity__edge-blur {
                background: rgba(24, 24, 27, .68);
            }
        }
    </style>

    @php($search = $search ?? '')

    <div class="space-y-4">
        <div class="page-title-identity tags-page-title">
            <div class="page-title-identity__edge-blur page-title-identity__edge-blur--top" aria-hidden="true"></div>
            <a href="{{ url()->previous() }}" class="tags-page-title__back" aria-label="Geri">
                <iconify-icon icon="lucide:arrow-left" aria-hidden="true"></iconify-icon>
            </a>
            <span class="tags-page-title__text">{{ __('site.tags_page.title') }}</span>
            <div class="tags-page-title__trailing">
                {!! view('blog.partials.tags-toolbar', ['sort' => $sort, 'sortOptions' => $sortOptions, 'search' => $search])->render() !!}
            </div>
        </div>
        <div data-identity-spacer aria-hidden="true"></div>

        <div class="tags-search-panel {{ $search !== '' ? 'is-open' : '' }}" data-tags-search-panel>
            <div class="tags-search-panel__inner">
                <form method="GET" action="{{ route('blog.tags') }}" class="tags-search-panel__form" data-tags-search-form>
                    @if ($sort !== 'popular')
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif
                    <input
                        type="search"
                        id="tags-search-input"
                        name="q"
                        value="{{ $search }}"
                        placeholder="{{ __('site.tags_page.search_placeholder') }}"
                        class="tags-search-panel__input"
                        autocomplete="off"
                        data-tags-search-input
                    >
                    <button type="submit" class="tags-toolbar__icon tags-search-panel__submit" aria-label="{{ __('site.tags_page.search_button') }}">
                        <iconify-icon icon="lucide:search" aria-hidden="true"></iconify-icon>
                    </button>
                </form>
                <p class="sr-only" role="status" aria-live="polite" data-tags-status></p>
            </div>
        </div>
        <div data-search-panel-spacer aria-hidden="true"></div>

        <div class="space-y-4 tags-list" data-tags-list data-total="{{ $tags->total() }}">
            @forelse ($tags as $tag)
                <a
                    href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
                    class="tag-row"
                    data-tag-row
                >
                    <span class="tag-row__icon" aria-hidden="true"><iconify-icon icon="lucide:hash"></iconify-icon></span>
                    <span class="tag-row__name">{{ $tag->name }}</span>
                    <span class="tag-row__count">{{ number_format($tag->posts_count) }}</span>
                    <iconify-icon class="tag-row__chevron" icon="lucide:chevron-right" aria-hidden="true"></iconify-icon>
                </a>
            @empty
                <p class="tags-list__empty" data-tags-empty>{{ __('site.tags_page.empty') }}</p>
            @endforelse

            @include('partials.tags-load-more', ['tags' => $tags])
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                // Panel artik position:fixed (header.blade.php'deki paylasilan
                // script kutunun hemen altina "top" ile sabitliyor) - normal
                // akistan ciktigi icin altindaki icerigin ziplamamasi icin bu
                // spacer'i panel acilip kapanirken elle (an-be-an, CSS
                // transition ile) buyutup kucultuyoruz.
                const panel = document.querySelector('[data-tags-search-panel]');
                const trigger = document.querySelector('[data-tags-search-trigger]');
                const input = document.querySelector('[data-tags-search-input]');
                const inner = panel?.querySelector('.tags-search-panel__inner');
                const panelSpacer = document.querySelector('[data-search-panel-spacer]');
                const identity = document.querySelector('.page-title-identity');

                /* Tema/layout hangi sirada yuklenirse yuklensin baslik kutusu
                   yari saydam kalmasin. Degerler dogrudan elemente important
                   olarak yazilir; blur sadece dis edge katmaninda kalir. */
                const forceWhiteIdentity = () => {
                    if (!identity) return;

                    identity.style.setProperty('background', '#ffffff', 'important');
                    identity.style.setProperty('background-color', '#ffffff', 'important');
                    identity.style.setProperty('background-image', 'none', 'important');
                    identity.style.setProperty('border', '1px solid #dfe3e8', 'important');
                    identity.style.setProperty('border-radius', '999px', 'important');
                    identity.style.setProperty('backdrop-filter', 'none', 'important');
                    identity.style.setProperty('-webkit-backdrop-filter', 'none', 'important');
                    identity.style.setProperty('box-shadow', 'none', 'important');
                    identity.style.setProperty('filter', 'none', 'important');
                    identity.style.setProperty('opacity', '1', 'important');
                };

                /* Layout mobil/masaustu akisi paneli position:fixed yaptiginda
                   genislik viewport'a tasmasin. Panel her zaman Etiketler
                   kutusunun soluna ve genisligine birebir oturur. */
                const syncPanelGeometry = () => {
                    if (!panel || !identity) return;

                    forceWhiteIdentity();

                    const panelPosition = window.getComputedStyle(panel).position;

                    if (panelPosition === 'fixed') {
                        const identityRect = identity.getBoundingClientRect();
                        panel.style.setProperty('left', `${identityRect.left}px`, 'important');
                        panel.style.setProperty('right', 'auto', 'important');
                        panel.style.setProperty('width', `${identityRect.width}px`, 'important');
                        panel.style.setProperty('max-width', `${identityRect.width}px`, 'important');
                    } else {
                        panel.style.removeProperty('left');
                        panel.style.removeProperty('right');
                        panel.style.removeProperty('width');
                        panel.style.removeProperty('max-width');
                    }
                };

                const syncPanelSpacer = (open) => {
                    if (!panelSpacer || !inner) return;
                    panelSpacer.style.height = open ? `${inner.scrollHeight}px` : '0px';
                };

                if (panel && trigger && input) {
                    forceWhiteIdentity();
                    window.requestAnimationFrame(syncPanelGeometry);
                    window.setTimeout(forceWhiteIdentity, 100);
                    window.setTimeout(forceWhiteIdentity, 500);

                    if (panel.classList.contains('is-open')) {
                        syncPanelSpacer(true);
                    }

                    trigger.addEventListener('click', () => {
                        const willOpen = !panel.classList.contains('is-open');
                        panel.classList.toggle('is-open', willOpen);
                        trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                        syncPanelSpacer(willOpen);
                        window.requestAnimationFrame(syncPanelGeometry);
                        if (willOpen) {
                            window.requestAnimationFrame(() => input.focus());
                        }
                    });

                    window.addEventListener('resize', syncPanelGeometry, { passive: true });
                    window.addEventListener('scroll', syncPanelGeometry, { passive: true });

                    if ('ResizeObserver' in window && identity) {
                        new ResizeObserver(syncPanelGeometry).observe(identity);
                    }
                }
            })();

            (() => {
                // Etiket listesi: yazarken 300ms sonra otomatik arar, yarim kalan
                // istekleri iptal eder ve sonucu sayfa yenilenmeden altta gosterir
                // - Kullanicilar sayfasindaki canli arama ile ayni desen.
                const form = document.querySelector('[data-tags-search-form]');
                const searchInput = document.querySelector('[data-tags-search-input]');
                const list = document.querySelector('[data-tags-list]');
                const status = document.querySelector('[data-tags-status]');

                if (form && searchInput && list) {
                    let debounceTimer = null;
                    let activeController = null;

                    const runSearch = async () => {
                        if (activeController) activeController.abort();
                        const controller = new AbortController();
                        activeController = controller;

                        const url = new URL(form.action, window.location.origin);
                        const formData = new FormData(form);
                        for (const [key, value] of formData.entries()) {
                            if (value !== '') url.searchParams.set(key, value);
                            else url.searchParams.delete(key);
                        }

                        list.classList.add('is-loading');

                        try {
                            const response = await fetch(url, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html, application/xhtml+xml' },
                                credentials: 'same-origin',
                                signal: controller.signal,
                            });

                            if (!response.ok) {
                                throw new Error('Etiket aramasi basarisiz: ' + response.status);
                            }

                            const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                            const newList = doc.querySelector('[data-tags-list]');

                            if (newList) {
                                list.innerHTML = newList.innerHTML;
                                list.dataset.total = newList.dataset.total || '0';
                            }
                            if (status) {
                                status.textContent = (list.dataset.total || '0') + ' etiket bulundu';
                            }

                            window.history.replaceState(null, '', url.toString());
                        } catch (error) {
                            if (error.name !== 'AbortError') {
                                console.error(error);
                            }
                        } finally {
                            if (activeController === controller) {
                                list.classList.remove('is-loading');
                                activeController = null;
                            }
                        }
                    };

                    searchInput.addEventListener('input', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(runSearch, 300);
                    });

                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        clearTimeout(debounceTimer);
                        runSearch();
                    });
                }
            })();

            (() => {
                const root = document.querySelector('[data-tags-sort]');
                const trigger = document.querySelector('[data-tags-sort-trigger]');
                const menu = document.querySelector('[data-tags-sort-menu]');

                if (root && trigger && menu) {
                    const openMenu = () => {
                        root.classList.add('is-open');
                        menu.classList.add('is-open');
                        trigger.setAttribute('aria-expanded', 'true');
                    };

                    const closeMenu = () => {
                        root.classList.remove('is-open');
                        menu.classList.remove('is-open');
                        trigger.setAttribute('aria-expanded', 'false');
                    };

                    trigger.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        if (menu.classList.contains('is-open')) closeMenu(); else openMenu();
                    });

                    root.addEventListener('click', (event) => event.stopPropagation());
                    document.addEventListener('click', closeMenu);
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') closeMenu();
                    });
                }
            })();

            (() => {
                const nextSelector = '[data-tags-load-next]';
                const controlsSelector = '[data-tags-load-more]';
                const rowSelector = '[data-tag-row]';

                const rowKey = (row) => row.getAttribute('href') || '';

                const buildSkeletonRow = () => {
                    const row = document.createElement('div');
                    row.className = 'tag-row tag-row--skeleton';
                    row.setAttribute('aria-hidden', 'true');
                    return row;
                };

                document.addEventListener('click', async (event) => {
                    const button = event.target instanceof Element ? event.target.closest(nextSelector) : null;
                    if (!button) return;

                    event.preventDefault();

                    if (button.dataset.loading === '1') return;

                    const controls = button.closest(controlsSelector);
                    const parent = controls ? controls.parentElement : null;
                    const url = button.getAttribute('href');

                    if (!controls || !parent || !url) {
                        window.location.href = button.href;
                        return;
                    }

                    button.dataset.loading = '1';
                    button.classList.add('is-loading');
                    button.setAttribute('aria-busy', 'true');

                    const skeletons = Array.from({ length: 6 }, buildSkeletonRow);
                    skeletons.forEach((row) => parent.insertBefore(row, controls));

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                Accept: 'text/html, application/xhtml+xml',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            throw new Error('Etiket istegi basarisiz: ' + response.status);
                        }

                        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                        const currentKeys = new Set(
                            Array.from(document.querySelectorAll(rowSelector)).map(rowKey).filter(Boolean)
                        );
                        const fragment = document.createDocumentFragment();

                        Array.from(doc.querySelectorAll(rowSelector)).forEach((row) => {
                            const key = rowKey(row);
                            if (!key || currentKeys.has(key)) return;
                            currentKeys.add(key);
                            fragment.appendChild(row);
                        });

                        skeletons.forEach((row) => row.remove());
                        parent.insertBefore(fragment, controls);

                        const nextControls = doc.querySelector(controlsSelector);
                        if (nextControls) {
                            controls.replaceWith(nextControls);
                        } else {
                            controls.remove();
                        }
                    } catch (error) {
                        skeletons.forEach((row) => row.remove());
                        window.location.href = button.href;
                    }
                }, true);
            })();
        </script>
    @endpush
@endsection

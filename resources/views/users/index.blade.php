@extends('layouts.app')

@section('title', __('site.users.title'))
@section('meta_description', __('site.users.meta_description'))

@section('content')
    <style>
        /* Kullanicilar sayfasi basligindaki (page-title-identity) arama ve
           siralama tetikleyicileri - filtre menusu tags-sort ile ayni desen,
           siniflar sayfa-ozel kalsin diye users- on ekiyle ayristirildi. */
        .users-toolbar {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .users-toolbar__sort {
            position: relative;
            display: inline-flex;
        }

        /* .users-toolbar__icon iki kez yazildi (ozgullugu bilerek yukseltmek
           icin) - site genelindeki "body.alma-app :where(button...) {
           background:#fff !important}" resetiyle esitlik/oncelik yarisini
           kaybetmesin diye. Tek sinif + !important o kuraldan (body+class
           turunden) daha dusuk ozgullukte kalip eziliyordu. Etiketler
           sayfasindaki tags-toolbar__icon ile BIREBIR AYNI kalip: 32x32,
           #f3f4f6/#1e293b hover, iconify-icon 16px, basma geri bildirimi. */
        .users-toolbar__icon.users-toolbar__icon {
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

        .users-toolbar__icon iconify-icon {
            font-size: 16px;
        }

        .users-toolbar__icon.users-toolbar__icon:hover,
        .users-toolbar__icon.users-toolbar__icon:focus-visible,
        .users-toolbar__icon.users-toolbar__icon[aria-expanded="true"],
        .users-toolbar__sort.is-open .users-toolbar__icon.users-toolbar__icon {
            background: #f3f4f6 !important;
            outline: none;
        }

        .users-toolbar__icon:active {
            transform: translateY(1px);
        }

        html.dark .users-toolbar__icon {
            color: #cbd5e1 !important;
        }

        html.dark .users-toolbar__icon.users-toolbar__icon:hover,
        html.dark .users-toolbar__icon.users-toolbar__icon:focus-visible,
        html.dark .users-toolbar__icon.users-toolbar__icon[aria-expanded="true"],
        html.dark .users-toolbar__sort.is-open .users-toolbar__icon.users-toolbar__icon {
            background: #1e293b !important;
        }

        /* Bu sayfadaki TUM buton ve tiklanabilir alanlar icin tek, tutarli
           "fare uzerine gelince gri" kurali - site genelindeki
           "body.alma-app :where(button...) {!important}" resetiyle ayni
           ozgulluk yarisini kazanmasi icin .users-page iki kez yazildi.
           Kullanici satirinin kendisi (avatar+isim linki) BU kuralin disinda
           birakildi: kutunun kendi yuvarlatilmis kartina gomulu, ayri bir
           gri kutu olarak tasip kart kenarlariyla uyumsuz gorunuyordu -
           satir zaten tikla-profile-git olarak calisiyor, ekstra bir hover
           kutusuna ihtiyaci yok (Sadelik ilkesi: her eleman yerini hak eder). */
        .users-page.users-page button:not(.users-toolbar__icon):not(.users-follow-trigger):hover,
        .users-page.users-page button:not(.users-toolbar__icon):not(.users-follow-trigger):focus-visible {
            background: rgba(15, 15, 18, .06) !important;
            outline: none;
        }

        html.dark .users-page.users-page button:not(.users-toolbar__icon):not(.users-follow-trigger):hover,
        html.dark .users-page.users-page button:not(.users-toolbar__icon):not(.users-follow-trigger):focus-visible {
            background: rgba(255, 255, 255, .1) !important;
        }

        /* Takip et/Takiptesin: durum kendi rengini tasir (mavi = eylem
           bekliyor, gri = zaten yapildi - iOS'taki "Follow/Following" ayrimi
           gibi), fare uzerine gelince kendi tonunun koyusuna doner. Basinca
           ("Response" ilkesi - aninda, beklemeden) hafifce kuculur. */
        .users-follow-trigger {
            transition: background-color .15s ease, color .15s ease, transform .08s ease-out;
        }

        .users-follow-trigger:active {
            transform: scale(0.96);
        }

        .users-follow-trigger.users-follow-trigger--follow {
            background: #2563eb !important;
            color: #ffffff !important;
        }

        .users-follow-trigger.users-follow-trigger--follow:hover,
        .users-follow-trigger.users-follow-trigger--follow:focus-visible {
            background: #1d4ed8 !important;
            outline: none;
        }

        .users-follow-trigger.users-follow-trigger--following {
            background: #f1f5f9 !important;
            color: #334155 !important;
        }

        .users-follow-trigger.users-follow-trigger--following:hover,
        .users-follow-trigger.users-follow-trigger--following:focus-visible {
            background: #e2e8f0 !important;
            outline: none;
        }

        html.dark .users-follow-trigger.users-follow-trigger--following {
            background: #1e293b !important;
            color: #cbd5e1 !important;
        }

        html.dark .users-follow-trigger.users-follow-trigger--following:hover,
        html.dark .users-follow-trigger.users-follow-trigger--following:focus-visible {
            background: #334155 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .users-follow-trigger {
                transition: background-color .15s ease, color .15s ease;
            }

            .users-follow-trigger:active {
                transform: none;
            }
        }

        /* Acilir menu - Etiketler sayfasindaki tags-sort__menu ile BIREBIR
           AYNI "materialize, don't just fade" davranisi: [hidden] yerine
           visibility+opacity+kucuk olcek/kayma, tetikleyiciden (sag ust)
           belirir. [hidden] KASITLI kullanilmiyor - Chromium'da bazi
           durumlarda display:none donusumu getBoundingClientRect()'i 0x0
           birakiyor. */
        .users-toolbar__menu {
            display: flex;
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
            flex-direction: column;
            visibility: hidden;
            opacity: 0;
            transform: scale(.96) translateY(-6px);
            transform-origin: top right;
            transition: opacity .16s ease, transform .16s ease, visibility 0s linear .16s;
        }

        .users-toolbar__menu.is-open {
            visibility: visible;
            opacity: 1;
            transform: scale(1) translateY(0);
            transition: opacity .16s ease, transform .16s ease;
        }

        @media (prefers-reduced-motion: reduce) {
            .users-toolbar__menu {
                transform: none;
                transition: opacity .12s ease, visibility 0s linear .12s;
            }

            .users-toolbar__menu.is-open {
                transform: none;
                transition: opacity .12s ease;
            }
        }

        .users-toolbar__label {
            display: block;
            margin: 4px 8px 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .users-toolbar__options {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .users-toolbar__option {
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

        .users-toolbar__option iconify-icon {
            font-size: 15px;
            flex-shrink: 0;
        }

        .users-toolbar__option[aria-current="true"],
        .users-toolbar__option:hover,
        .users-toolbar__option:focus-visible {
            background: #f3f4f6;
            color: #0f172a;
            outline: none;
        }

        .users-toolbar__option[aria-current="true"] {
            color: #1d4ed8;
        }

        .users-toolbar__option[aria-current="true"] iconify-icon {
            color: #2563eb;
        }

        html.dark .users-toolbar__menu {
            background: #18181b;
            border-color: #27272a;
        }

        html.dark .users-toolbar__label {
            color: #71717a;
        }

        html.dark .users-toolbar__option {
            color: #d4d4d8;
        }

        html.dark .users-toolbar__option[aria-current="true"],
        html.dark .users-toolbar__option:hover,
        html.dark .users-toolbar__option:focus-visible {
            background: #27272a;
            color: #f4f4f5;
        }

        html.dark .users-toolbar__option[aria-current="true"] {
            color: #93c5fd;
        }

        html.dark .users-toolbar__option[aria-current="true"] iconify-icon {
            color: #93c5fd;
        }

        /* Arama paneli - buyume/kuculme yuksekligi grid ile animasyonlu,
           boylece acilirken "materyal" gibi belirir, kapanirken katlanir. */
        .users-search-panel {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows .2s ease, opacity .15s ease;
        }

        .users-search-panel.is-open {
            grid-template-rows: 1fr;
            opacity: 1;
        }

        .users-search-panel__inner {
            min-height: 0;
            overflow: hidden;
        }

        .users-search-panel__form {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 3px;
            padding: 4px 4px 4px 14px;
            border: 1px solid #d9dde3;
            border-radius: 999px;
            background: #ffffff;
        }

        /* Input#id + iki class + input turu = (id:1, class:2, type:1),
           site genelindeki "body.alma-app :where(input,textarea,select)
           :not(#comments *) {background:var(--ui-surface-muted) !important}"
           kuralini (id:1, class:1, type:1) ozgulluk kiyaslamasinda class
           katmaninda gececek sekilde eziyor - boylece kutu duz gri
           dikdortgen degil, pill'in kendi beyaz/cerceveli gorunumunu alir. */
        input#users-search-input.users-search-panel__input.users-search-panel__input {
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

        input#users-search-input.users-search-panel__input.users-search-panel__input::placeholder {
            color: #9ca3af;
        }

        .users-search-panel__submit {
            flex: 0 0 auto;
        }

        html.dark .users-search-panel__form {
            border-color: #27272a;
            background: #18181b;
        }

        html.dark input#users-search-input.users-search-panel__input.users-search-panel__input {
            color: #fafafa !important;
        }

        /* Kullanici satirlari - Etiketler sayfasindaki tag-row ile BIREBIR
           AYNI kutu: ince 1px kenarlik (once golgeli Tailwind "ring" idi,
           gercek border degildi), golgesiz duz yuzey, satirlar arasi bosluk
           16px (once 8px'ti - gap-2 yerine yukarida gap-4). */
        .users-row {
            border: 1px solid rgba(226, 232, 240, .9);
        }

        html.dark .users-row {
            background: #18181b !important;
            border-color: #27272a;
        }

        /* Canli arama sirasinda liste hafifce sonukleserek "araniyor" hissi
           verir; sonuc gelince normale doner. Response ilkesi: geri bildirim
           beklemeden, ama sonuc gelene kadar da liste donuk kalmaz. */
        .users-page-list {
            transition: opacity .15s ease;
        }

        .users-page-list.is-loading {
            opacity: .55;
        }

        @media (prefers-reduced-motion: reduce) {
            .users-search-panel,
            .users-page-list {
                transition: none;
            }
        }
    </style>

    <div class="space-y-4 users-page">
        @include('partials.page-title-identity', [
            'title' => __('site.users.title'),
            'trailing' => view('users.partials.users-toolbar', ['sort' => $sort, 'search' => $search])->render(),
        ])
        <div data-identity-spacer aria-hidden="true"></div>

        <div class="users-search-panel {{ $search !== '' ? 'is-open' : '' }}" data-users-search-panel>
            <div class="users-search-panel__inner">
                <form method="GET" action="{{ route('users.index') }}" class="users-search-panel__form" data-users-search-form>
                    @if ($sort !== 'newest')
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif
                    <input
                        type="search"
                        id="users-search-input"
                        name="q"
                        value="{{ $search }}"
                        placeholder="{{ __('site.users.search_placeholder') }}"
                        class="users-search-panel__input"
                        autocomplete="off"
                        data-users-search-input
                    >
                    <button type="submit" class="users-toolbar__icon users-search-panel__submit" aria-label="{{ __('site.users.search_button') }}">
                        <iconify-icon icon="lucide:search" aria-hidden="true"></iconify-icon>
                    </button>
                </form>
                <p class="sr-only" role="status" aria-live="polite" data-users-status></p>
            </div>
            <div class="page-title-identity__edge-blur page-title-identity__edge-blur--bottom" aria-hidden="true"></div>
        </div>
        <div data-search-panel-spacer aria-hidden="true"></div>

        <div class="grid grid-cols-1 gap-4 users-page-list" data-users-list data-total="{{ $users->total() }}">
        @forelse ($users as $user)
            <div class="users-row rounded-[14px] bg-white px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <a href="{{ route('users.show', $user) }}" class="shrink-0">
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-12 w-12 rounded-full object-cover">
                        </a>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('users.show', $user) }}" class="flex w-fit max-w-full items-center gap-1">
                                <p class="truncate text-lg font-semibold leading-tight text-slate-900 dark:text-slate-100">{{ $user->name }}</p>
                                <x-verification-badge :user="$user" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" />
                            </a>
                            <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ '@' . ($user->username ?: __('site.users.default_username')) }}</p>
                        </div>
                    </div>

                    @auth
                        @if(auth()->id() !== $user->id)
                            @php $isFollowing = (bool) ($user->is_followed_by_viewer ?? false); @endphp
                            <form method="POST" action="{{ route('users.follow', $user) }}" class="m-0 shrink-0">
                                @csrf
                                <button type="submit" class="users-follow-trigger {{ $isFollowing ? 'users-follow-trigger--following' : 'users-follow-trigger--follow' }} rounded-full px-5 py-2 text-sm font-medium">
                                    {{ $isFollowing ? 'Takiptesin' : 'Takip et' }}
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="users-follow-trigger users-follow-trigger--follow shrink-0 rounded-full px-5 py-2 text-sm font-medium">
                            Takip et
                        </a>
                    @endauth
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-6">
                {{ __('site.users.empty') }}
            </p>
        @endforelse
        </div>

        <div class="text-xs text-slate-500 dark:text-slate-400" data-users-meta>
            {{ $users->links() }}
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
                const panel = document.querySelector('[data-users-search-panel]');
                const trigger = document.querySelector('[data-users-search-trigger]');
                const input = document.querySelector('[data-users-search-input]');
                const inner = panel?.querySelector('.users-search-panel__inner');
                const panelSpacer = document.querySelector('[data-search-panel-spacer]');

                const syncPanelSpacer = (open) => {
                    if (!panelSpacer || !inner) return;
                    panelSpacer.style.height = open ? `${inner.scrollHeight}px` : '0px';
                };

                if (panel && trigger && input) {
                    if (panel.classList.contains('is-open')) {
                        syncPanelSpacer(true);
                    }

                    trigger.addEventListener('click', () => {
                        const willOpen = !panel.classList.contains('is-open');
                        panel.classList.toggle('is-open', willOpen);
                        trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                        syncPanelSpacer(willOpen);
                        if (willOpen) {
                            window.requestAnimationFrame(() => input.focus());
                        }
                    });
                }
            })();

            (() => {
                const root = document.querySelector('[data-users-sort]');
                const trigger = document.querySelector('[data-users-sort-trigger]');
                const menu = document.querySelector('[data-users-sort-menu]');

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
                // Kullanicilar listesi: yazarken 300ms sonra otomatik arar
                // (Enter/ikona tiklamak beklemeden aninda arar), yarim kalan
                // istekleri iptal eder ve sonucu sayfa yenilenmeden altta
                // gosterir. URL da sessizce (replaceState) guncellenir ki
                // sayfa yenilendiginde arama kaybolmasin.
                const form = document.querySelector('[data-users-search-form]');
                const input = document.querySelector('[data-users-search-input]');
                const list = document.querySelector('[data-users-list]');
                const meta = document.querySelector('[data-users-meta]');
                const status = document.querySelector('[data-users-status]');

                if (!form || !input || !list) return;

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
                            throw new Error('Kullanici aramasi basarisiz: ' + response.status);
                        }

                        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                        const newList = doc.querySelector('[data-users-list]');
                        const newMeta = doc.querySelector('[data-users-meta]');

                        if (newList) {
                            list.innerHTML = newList.innerHTML;
                            list.dataset.total = newList.dataset.total || '0';
                        }
                        if (newMeta && meta) {
                            meta.innerHTML = newMeta.innerHTML;
                        }
                        if (status) {
                            status.textContent = (list.dataset.total || '0') + ' kullanıcı bulundu';
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

                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(runSearch, 300);
                });

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    clearTimeout(debounceTimer);
                    runSearch();
                });
            })();
        </script>
    @endpush
@endsection


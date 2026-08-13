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
           turunden) daha dusuk ozgullukte kalip eziliyordu. */
        .users-toolbar__icon.users-toolbar__icon {
            display: inline-flex;
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            align-items: center;
            justify-content: center;
            border: 0 !important;
            border-radius: 999px;
            background: transparent !important;
            color: inherit !important;
            cursor: pointer;
            transition: background-color .15s ease, transform .1s ease;
        }

        .users-toolbar__icon svg {
            width: 15px;
            height: 15px;
            pointer-events: none;
        }

        .users-toolbar__icon.users-toolbar__icon:hover,
        .users-toolbar__icon.users-toolbar__icon:focus-visible,
        .users-toolbar__icon.users-toolbar__icon[aria-expanded="true"],
        .users-toolbar__sort.is-open .users-toolbar__icon.users-toolbar__icon {
            background: rgba(15, 15, 18, .06) !important;
            outline: none;
        }

        .users-toolbar__icon:active {
            transform: translateY(1px);
        }

        html.dark .users-toolbar__icon.users-toolbar__icon:hover,
        html.dark .users-toolbar__icon.users-toolbar__icon:focus-visible,
        html.dark .users-toolbar__icon.users-toolbar__icon[aria-expanded="true"],
        html.dark .users-toolbar__sort.is-open .users-toolbar__icon.users-toolbar__icon {
            background: rgba(255, 255, 255, .1) !important;
        }

        .users-toolbar__menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 168px;
            border-radius: 14px;
            border: 1px solid #e4e4e7;
            background: #ffffff;
            padding: 6px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
            z-index: 40;
        }

        .users-toolbar__menu[hidden] {
            display: none !important;
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
            border-radius: 9px;
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

        html.dark .users-toolbar__menu {
            background: #18181b;
            border-color: #27272a;
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
            gap: 8px;
            margin-top: 8px;
            padding: 6px 6px 6px 14px;
            border: 1px solid #d9dde3;
            border-radius: 999px;
            background: #ffffff;
        }

        .users-search-panel__input {
            flex: 1 1 auto;
            min-width: 0;
            border: 0;
            background: transparent;
            font-size: 14px;
            color: #050505;
            outline: none;
        }

        .users-search-panel__input::placeholder {
            color: #9ca3af;
        }

        .users-search-panel__submit {
            flex: 0 0 auto;
            border: 0;
            border-radius: 999px;
            background: #f4f4f5;
            color: #18181b;
            font-size: 13px;
            font-weight: 600;
            padding: 7px 14px;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .users-search-panel__submit:hover {
            background: #e4e4e7;
        }

        html.dark .users-search-panel__form {
            border-color: #27272a;
            background: #18181b;
        }

        html.dark .users-search-panel__input {
            color: #fafafa;
        }

        html.dark .users-search-panel__submit {
            background: #27272a;
            color: #fafafa;
        }

        html.dark .users-search-panel__submit:hover {
            background: #3f3f46;
        }

        @media (prefers-reduced-motion: reduce) {
            .users-search-panel {
                transition: none;
            }
        }
    </style>

    <div class="space-y-4">
        @include('partials.page-title-identity', [
            'title' => __('site.users.title'),
            'trailing' => view('users.partials.users-toolbar', ['sort' => $sort, 'search' => $search])->render(),
        ])

        <div class="users-search-panel {{ $search !== '' ? 'is-open' : '' }}" data-users-search-panel>
            <div class="users-search-panel__inner">
                <form method="GET" action="{{ route('users.index') }}" class="users-search-panel__form">
                    @if ($sort !== 'newest')
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="{{ __('site.users.search_placeholder') }}"
                        class="users-search-panel__input"
                        data-users-search-input
                    >
                    <button type="submit" class="users-search-panel__submit">{{ __('site.users.search_button') }}</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-2">
        @forelse ($users as $user)
            <div class="rounded-[26px] bg-white dark:bg-slate-900 px-4 py-3 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700/80">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('users.show', $user) }}" class="flex min-w-0 items-center gap-3">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-12 w-12 rounded-full object-cover">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1">
                                <p class="truncate text-lg font-semibold leading-tight text-slate-900 dark:text-slate-100">{{ $user->name }}</p>
                                <x-verification-badge :user="$user" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" />
                            </div>
                            <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ '@' . ($user->username ?: __('site.users.default_username')) }}</p>
                        </div>
                    </a>

                    @auth
                        @if(auth()->id() !== $user->id)
                            <form method="POST" action="{{ route('users.follow', $user) }}" class="m-0 shrink-0">
                                @csrf
                                <button type="submit" class="rounded-full bg-slate-100 dark:bg-slate-800 px-5 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 transition hover:bg-slate-200 dark:hover:bg-slate-700">
                                    {{ (bool) ($user->is_followed_by_viewer ?? false) ? 'Takiptesin' : 'Takip et' }}
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="shrink-0 rounded-full bg-slate-100 dark:bg-slate-800 px-5 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 transition hover:bg-slate-200 dark:hover:bg-slate-700">
                            Takip et
                        </a>
                    @endauth
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-slate-900 p-6 text-sm text-slate-500 dark:text-slate-400 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700/80">
                {{ __('site.users.empty') }}
            </div>
        @endforelse
        </div>

        <div class="flex items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <span>Sayfa basina {{ $users->perPage() }} kullanici</span>
            {{ $users->links() }}
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const panel = document.querySelector('[data-users-search-panel]');
                const trigger = document.querySelector('[data-users-search-trigger]');
                const input = document.querySelector('[data-users-search-input]');

                if (panel && trigger && input) {
                    trigger.addEventListener('click', () => {
                        const willOpen = !panel.classList.contains('is-open');
                        panel.classList.toggle('is-open', willOpen);
                        trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
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
                        menu.hidden = false;
                        trigger.setAttribute('aria-expanded', 'true');
                    };

                    const closeMenu = () => {
                        root.classList.remove('is-open');
                        menu.hidden = true;
                        trigger.setAttribute('aria-expanded', 'false');
                    };

                    trigger.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        if (menu.hidden) openMenu(); else closeMenu();
                    });

                    root.addEventListener('click', (event) => event.stopPropagation());
                    document.addEventListener('click', closeMenu);
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') closeMenu();
                    });
                }
            })();
        </script>
    @endpush
@endsection


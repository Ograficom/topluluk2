@php
    $homeActive = request()->routeIs('home');
    $searchActive = request()->routeIs('search');
    $messagesActive = request()->routeIs('messages.*');
@endphp

<nav
    data-mobile-bottom-nav
    class="mobile-bottom-nav fixed left-1/2 z-50 hidden -translate-x-1/2 sm:hidden"
    aria-label="{{ __('site.mobile_nav.menu') }}"
>
    <div class="mobile-bottom-nav__main">
        <a
            href="{{ route('home') }}"
            class="mobile-bottom-nav__item {{ $homeActive ? 'mobile-bottom-nav__item--active' : '' }}"
            aria-label="{{ __('site.mobile_nav.home') }}"
            @if($homeActive) aria-current="page" @endif
        >
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M0 0h24v24H0z" fill="none" />
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M2 12.204c0-2.289 0-3.433.52-4.381c.518-.949 1.467-1.537 3.364-2.715l2-1.241C9.889 2.622 10.892 2 12 2s2.11.622 4.116 1.867l2 1.241c1.897 1.178 2.846 1.766 3.365 2.715S22 9.915 22 12.203v1.522c0 3.9 0 5.851-1.172 7.063S17.771 22 14 22h-4c-3.771 0-5.657 0-6.828-1.212S2 17.626 2 13.725z" />
                    <path stroke-linecap="round" d="M12 15v3" />
                </g>
            </svg>
            <span class="sr-only">{{ __('site.mobile_nav.home') }}</span>
        </a>

        <a
            href="{{ route('search') }}"
            class="mobile-bottom-nav__item {{ $searchActive ? 'mobile-bottom-nav__item--active' : '' }}"
            aria-label="{{ __('site.mobile_nav.search') }}"
            @if($searchActive) aria-current="page" @endif
        >
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="6.75" stroke="currentColor" stroke-width="1.9"></circle>
                <path stroke="currentColor" stroke-linecap="round" stroke-width="1.9" d="m16 16 3.75 3.75"></path>
            </svg>
            <span class="sr-only">{{ __('site.mobile_nav.search') }}</span>
        </a>

        @auth
            <a
                href="{{ route('messages.index') }}"
                class="mobile-bottom-nav__item {{ $messagesActive ? 'mobile-bottom-nav__item--active' : '' }}"
                aria-label="{{ __('site.sidebar.messages') }}"
                @if($messagesActive) aria-current="page" @endif
            >
        @else
            <a
                href="{{ route('login') }}"
                class="mobile-bottom-nav__item"
                aria-label="{{ __('site.sidebar.messages') }}"
            >
        @endauth
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.25 12a9.23 9.23 0 0 1-2.705 6.54A9.25 9.25 0 0 1 12 21.25a9.2 9.2 0 0 1-3.795-.81l-3.867.572a1.195 1.195 0 0 1-1.361-1.43l.537-3.923A8.9 8.9 0 0 1 2.75 12a9.23 9.23 0 0 1 2.705-6.54A9.25 9.25 0 0 1 12 2.75a9.26 9.26 0 0 1 6.545 2.71A9.24 9.24 0 0 1 21.25 12" />
                </svg>
                <span class="sr-only">{{ __('site.sidebar.messages') }}</span>
            </a>
    </div>

    @auth
        <a
            href="{{ route('blog.create') }}"
            class="mobile-bottom-nav__plus"
            aria-label="{{ __('site.mobile_nav.new_item') }}"
        >
    @else
        <a
            href="{{ route('login') }}"
            class="mobile-bottom-nav__plus"
            aria-label="{{ __('site.mobile_nav.new_item') }}"
        >
    @endauth
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-width="1.8" d="M12 5v14M5 12h14" />
            </svg>
            <span class="sr-only">{{ __('site.mobile_nav.new_item') }}</span>
        </a>

</nav>

<style>
    @media (max-width: 639.98px) {
        html body [data-mobile-bottom-nav].mobile-bottom-nav {
            position: fixed !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            left: 50% !important;
            right: auto !important;
            top: auto !important;
            bottom: max(8px, env(safe-area-inset-bottom, 0px)) !important;
            z-index: 2147483000 !important;
            width: 286px !important;
            max-width: calc(100vw - 24px) !important;
            height: 58px !important;
            min-height: 58px !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            transform: translate3d(-50%, 0, 0) !important;
            pointer-events: auto !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav > .mobile-bottom-nav__main {
            position: relative !important;
            flex: 0 0 230px !important;
            width: 230px !important;
            height: 58px !important;
            min-height: 58px !important;
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            align-items: center !important;
            padding: 5px !important;
            gap: 0 !important;
            border: 1px solid rgba(255,255,255,.88) !important;
            border-radius: 20px !important;
            background: rgba(255,255,255,.56) !important;
            box-shadow:
                0 10px 30px rgba(15,23,42,.12),
                0 2px 8px rgba(15,23,42,.06),
                inset 0 1px 0 rgba(255,255,255,.95),
                inset 0 -1px 0 rgba(255,255,255,.25) !important;
            -webkit-backdrop-filter: blur(28px) saturate(180%) !important;
            backdrop-filter: blur(28px) saturate(180%) !important;
            isolation: isolate !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item {
            display: inline-flex !important;
            width: 100% !important;
            height: 44px !important;
            min-height: 44px !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 14px !important;
            background: transparent !important;
            color: #334155 !important;
            box-shadow: none !important;
            transform: none !important;
            transition: color 140ms ease, background-color 140ms ease, transform 100ms ease !important;
            -webkit-tap-highlight-color: transparent !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item--active {
            color: #2563eb !important;
            background: rgba(255,255,255,.34) !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item:active {
            transform: scale(.92) !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__plus {
            position: relative !important;
            flex: 0 0 48px !important;
            width: 48px !important;
            min-width: 48px !important;
            max-width: 48px !important;
            height: 48px !important;
            min-height: 48px !important;
            max-height: 48px !important;
            margin: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid rgba(255,255,255,.92) !important;
            border-radius: 15px !important;
            background: #2563eb !important;
            color: #fff !important;
            box-shadow:
                0 10px 24px rgba(37,99,235,.24),
                0 2px 7px rgba(15,23,42,.10),
                inset 0 1px 0 rgba(255,255,255,.48) !important;
            -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
            backdrop-filter: blur(16px) saturate(180%) !important;
            transform: translateZ(0) !important;
            transition: transform 140ms ease, background-color 140ms ease, box-shadow 140ms ease !important;
            z-index: 2147483002 !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__plus:hover {
            background: #1d4ed8 !important;
            box-shadow:
                0 12px 28px rgba(37,99,235,.28),
                0 2px 7px rgba(15,23,42,.10),
                inset 0 1px 0 rgba(255,255,255,.48) !important;
            transform: translateY(-1px) !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__plus:active {
            background: #1e40af !important;
            transform: scale(.93) !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav svg {
            display: block !important;
            width: 20px !important;
            height: 20px !important;
            flex: 0 0 20px !important;
            color: currentColor !important;
        }

        html body {
            padding-bottom: calc(70px + env(safe-area-inset-bottom, 0px)) !important;
        }

        html.dark body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__main {
            border-color: rgba(255,255,255,.14) !important;
            background: rgba(15,23,42,.58) !important;
        }

        html.dark body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item {
            color: #cbd5e1 !important;
        }

        html.dark body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item--active {
            color: #60a5fa !important;
            background: rgba(255,255,255,.08) !important;
        }
    }
</style>

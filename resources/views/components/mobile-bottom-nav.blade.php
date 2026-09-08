@php
    $homeActive = request()->routeIs('home');
    $searchActive = request()->routeIs('search');
    $videoActive = request()->routeIs('video');
    $messagesActive = request()->routeIs('messages.*');
@endphp

<nav
    data-mobile-bottom-nav
    class="mobile-bottom-nav fixed left-1/2 z-50 hidden -translate-x-1/2 sm:hidden"
    aria-label="{{ __('site.mobile_nav.menu') }}"
>
    <div class="grid h-full grid-cols-5 items-center">
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
                href="{{ route('blog.create') }}"
                class="mobile-bottom-nav__item mobile-bottom-nav__plus"
                aria-label="{{ __('site.mobile_nav.new_item') }}"
            >
        @else
            <a
                href="{{ route('login') }}"
                class="mobile-bottom-nav__item mobile-bottom-nav__plus"
                aria-label="{{ __('site.mobile_nav.new_item') }}"
            >
        @endauth
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14" />
                </svg>
                <span class="sr-only">{{ __('site.mobile_nav.new_item') }}</span>
            </a>

        <a
            href="{{ route('video') }}"
            class="mobile-bottom-nav__item {{ $videoActive ? 'mobile-bottom-nav__item--active' : '' }}"
            aria-label="Video"
            @if($videoActive) aria-current="page" @endif
        >
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M6.75 5.75h7.5A3.25 3.25 0 0 1 17.5 9v6a3.25 3.25 0 0 1-3.25 3.25h-7.5A3.25 3.25 0 0 1 3.5 15V9a3.25 3.25 0 0 1 3.25-3.25Z" />
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="m17.5 10 3-2v8l-3-2" />
            </svg>
            <span class="sr-only">Video</span>
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
</nav>

<style>
    @media (max-width: 639.98px) {
        html body [data-mobile-bottom-nav].mobile-bottom-nav {
            position: fixed !important;
            display: block !important;
            left: 50% !important;
            right: auto !important;
            top: auto !important;
            bottom: max(6px, env(safe-area-inset-bottom, 0px)) !important;
            z-index: 900 !important;
            width: calc(100% - 20px) !important;
            max-width: 380px !important;
            height: 50px !important;
            min-height: 50px !important;
            max-height: 50px !important;
            margin: 0 !important;
            padding: 0 5px !important;
            overflow: hidden !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 14px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            box-shadow: none !important;
            filter: none !important;
            -webkit-backdrop-filter: none !important;
            backdrop-filter: none !important;
            transform: translate3d(-50%, 0, 0) !important;
            transition: none !important;
            animation: none !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav > div {
            display: grid !important;
            grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
            align-items: center !important;
            width: 100% !important;
            height: 100% !important;
            gap: 0 !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item {
            display: inline-flex !important;
            width: 100% !important;
            min-width: 0 !important;
            height: 40px !important;
            min-height: 40px !important;
            max-height: 40px !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 10px !important;
            background: transparent !important;
            background-color: transparent !important;
            color: #334155 !important;
            box-shadow: none !important;
            transform: none !important;
            transition: none !important;
            animation: none !important;
            -webkit-tap-highlight-color: transparent !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item--active {
            color: #2563eb !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__plus {
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            height: 40px !important;
            border: 0 !important;
            border-radius: 10px !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        html body [data-mobile-bottom-nav].mobile-bottom-nav svg {
            display: block !important;
            width: 21px !important;
            height: 21px !important;
            flex: 0 0 21px !important;
            color: currentColor !important;
        }

        html body {
            padding-bottom: calc(64px + env(safe-area-inset-bottom, 0px)) !important;
        }

        html.dark body [data-mobile-bottom-nav].mobile-bottom-nav {
            border-color: #273244 !important;
            background: #111827 !important;
            background-color: #111827 !important;
        }

        html.dark body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item {
            color: #cbd5e1 !important;
        }

        html.dark body [data-mobile-bottom-nav].mobile-bottom-nav .mobile-bottom-nav__item--active {
            color: #60a5fa !important;
        }
    }
</style>

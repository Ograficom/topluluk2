@php
    if (isAppInstalled()) {
        $favicon = settings()->group('general')->get('site_favicon');
        $favicon = !empty($favicon) ? Storage::disk(getCurrentDisk())->url($favicon) : asset('/images/favicon.png');
        $google_analytics_code = settings()->group('advanced')->get('google_analytics_code');
        $custom_head_code = settings()->group('advanced')->get('custom_head_code');
        $custom_footer_code = settings()->group('advanced')->get('custom_footer_code');
    } else {
        $favicon = asset('/images/favicon.png');
        $google_analytics_code = '';
        $custom_head_code = '';
        $custom_footer_code = '';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="theme-{{ config('alma.appearance.theme', 'emerald') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="shortcut icon" href="{{ $favicon }}" type="image/png">
    @inertiaHead
    @if (config('alma.pwa_active') === true)
        @include('partials.pwa')
    @endif
    @include('feed::links')
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', config('alma.appearance.default_font')) }}:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Styles -->
    <style>
        .font-sans {
            font-family: @php echo config('alma.appearance.default_font')
        @endphp
        ,
        system-ui, -apple-system, sans-serif !important;
        }

        @media (max-width: 639px) {
            header > div > div:first-child {
                gap: 0.375rem !important;
            }

            header > div > div:first-child > a {
                height: 2.5rem !important;
                width: auto !important;
                align-items: center !important;
            }

            header > div > div:first-child > a img {
                width: auto !important;
                height: 2.25rem !important;
                max-height: 2.25rem !important;
            }

            header [data-ografi-brand-name] {
                margin-left: 0.25rem !important;
            }
        }
    </style>
    <script>
        (function() {
            const recoverFromStaleThemeBundle = (values) => {
                const details = values.filter(Boolean).map(String).join(' ');
                const isStaleThemeBundle = details.includes("reading 'appearance'")
                    || details.includes('useThemeConfig-CMn459mP.js')
                    || details.includes('AppLayout-CDEtE47D.js');

                if (! isStaleThemeBundle || sessionStorage.getItem('ografi-asset-recovery-v2') === '1') {
                    return;
                }

                sessionStorage.setItem('ografi-asset-recovery-v2', '1');

                Promise.all([
                    'caches' in window
                        ? caches.keys().then((keys) => Promise.all(keys.map((key) => caches.delete(key))))
                        : Promise.resolve(),
                    'serviceWorker' in navigator
                        ? navigator.serviceWorker.getRegistrations().then((items) => Promise.all(items.map((item) => item.unregister())))
                        : Promise.resolve(),
                ]).finally(() => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('_assets', Date.now().toString());
                    window.location.replace(url.toString());
                });
            };

            window.addEventListener('error', (event) => {
                recoverFromStaleThemeBundle([event.message, event.filename, event.error?.stack]);
            });
            window.addEventListener('unhandledrejection', (event) => {
                recoverFromStaleThemeBundle([event.reason?.message, event.reason?.stack, event.reason]);
            });

            const originalConsoleError = console.error.bind(console);
            console.error = (...args) => {
                originalConsoleError(...args);
                recoverFromStaleThemeBundle(args.map((item) => item?.stack || item?.message || item));
            };

            var root = document.documentElement;
            var theme = @json('theme-'.config('alma.appearance.theme', 'emerald'));
            root.classList.remove('dark');
            root.classList.add(theme);
            root.style.setProperty('--radius', @json(config('alma.appearance.radius', '0.5').'rem'));

            try {
                localStorage.setItem('vueuse-color-scheme', 'light');
            } catch (error) {}
        }());
    </script>
    @if ($google_analytics_code !== '')
        {!! $google_analytics_code !!}
    @endif
    @if ($custom_head_code !== '')
        {!! $custom_head_code !!}
    @endif
    <!-- Scripts -->
    @routes
    @vite('resources/js/app.js')
</head>

<body class="font-sans antialiased">
    @inertia
    @if (($page['component'] ?? null) === 'Story/Show' && ! empty($page['props']['story']))
        @php($fallbackStory = $page['props']['story'])
        <main data-story-fallback class="min-h-screen bg-[#f4f4f5] px-4 py-8 text-[#18181b]">
            <article class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-sm sm:p-10">
                <h1 class="text-2xl font-bold leading-tight sm:text-4xl">
                    {{ $fallbackStory['title'] ?? '' }}
                </h1>

                @if (! empty($fallbackStory['subtitle']))
                    <p class="mt-4 text-base text-[#71717a] sm:text-lg">
                        {{ $fallbackStory['subtitle'] }}
                    </p>
                @endif

                <div class="mt-8 space-y-4 text-base leading-7 sm:text-lg">
                    @foreach (($fallbackStory['content'] ?? []) as $block)
                        @if (! empty($block['data']['text']))
                            <p>{{ strip_tags($block['data']['text']) }}</p>
                        @endif
                    @endforeach
                </div>

                @if (! empty($fallbackStory['source_url']))
                    <a href="{{ $fallbackStory['source_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="mt-8 flex items-center justify-between rounded-2xl bg-[#f7f7f8] px-5 py-4 text-[#18181b] no-underline">
                        <span>
                            <span class="block text-xs uppercase tracking-wide text-[#71717a]">Source</span>
                            <strong class="mt-1 block">{{ $fallbackStory['source_host'] ?? $fallbackStory['source_url'] }}</strong>
                        </span>
                        <span aria-hidden="true" class="text-xl">↗</span>
                    </a>
                @endif
            </article>
        </main>
        <style>
            #app:not(:empty) + [data-story-fallback] { display: none; }
        </style>
    @endif
    @if ($custom_footer_code !== '')
        {!! $custom_footer_code !!}
    @endif
</body>

</html>

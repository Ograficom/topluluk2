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
        <main data-story-fallback class="ogf-fallback">
            <header class="ogf-fallback__header">
                <a href="/" class="ogf-fallback__brand"><img src="https://ografi.com/uploads/media/01KXC8H7ENWNCD3VE38WXP3YTJ.png" alt="Ografi"><strong>Ografi</strong></a>
                <a href="/login" class="ogf-fallback__login">Giriş yap</a>
            </header>
            <div class="ogf-fallback__page">
            <article class="ogf-fallback__card">
                @if (! empty($fallbackStory['user']))
                    <div class="ogf-fallback__author">
                        <img src="{{ $fallbackStory['user']['avatar_url'] ?? '' }}" alt="">
                        <div><strong>{{ $fallbackStory['user']['display_name'] ?? $fallbackStory['user']['username'] ?? '' }}</strong><span>{{ $fallbackStory['created_at']['human'] ?? '' }}</span></div>
                    </div>
                @endif
                <h1>
                    {{ $fallbackStory['title'] ?? '' }}
                </h1>

                @if (! empty($fallbackStory['subtitle']))
                    <p class="ogf-fallback__subtitle">
                        {{ $fallbackStory['subtitle'] }}
                    </p>
                @endif

                <div class="ogf-fallback__content">
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
                       class="ogf-fallback__source">
                        <span>
                            <span>Source</span>
                            <strong>{{ $fallbackStory['source_host'] ?? $fallbackStory['source_url'] }}</strong>
                        </span>
                        <b aria-hidden="true">&nearr;</b>
                    </a>
                @endif
            </article>
            </div>
        </main>
        <style>
            #app:not(:empty) + [data-story-fallback] { display: none; }
            .ogf-fallback { min-height: 100vh; background: #f4f4f5; color: #18181b; font-family: Roboto, Arial, sans-serif; }
            .ogf-fallback__header { height: 64px; padding: 0 max(20px, calc((100vw - 884px) / 2)); background: rgba(255,255,255,.94); border-bottom: 1px solid #e4e4e7; display: flex; align-items: center; justify-content: space-between; }
            .ogf-fallback__brand { display: flex; align-items: center; gap: 9px; color: #18181b; text-decoration: none; font-size: 20px; }
            .ogf-fallback__brand img { width: 30px; height: 30px; object-fit: contain; }
            .ogf-fallback__login { padding: 9px 15px; border-radius: 10px; background: #2563eb; color: white; text-decoration: none; font-size: 14px; font-weight: 600; }
            .ogf-fallback__page { width: min(100% - 32px, 720px); margin: 32px auto; }
            .ogf-fallback__card { background: white; border: 1px solid #e4e4e7; border-radius: 16px; padding: 28px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
            .ogf-fallback__author { display: flex; align-items: center; gap: 11px; margin-bottom: 24px; }
            .ogf-fallback__author img { width: 42px; height: 42px; border-radius: 50%; background: #f4f4f5; }
            .ogf-fallback__author strong, .ogf-fallback__author span { display: block; }
            .ogf-fallback__author strong { font-size: 15px; }
            .ogf-fallback__author span { margin-top: 3px; color: #71717a; font-size: 13px; }
            .ogf-fallback__card h1 { margin: 0; font-size: clamp(25px, 4vw, 38px); line-height: 1.18; letter-spacing: -.025em; }
            .ogf-fallback__subtitle { margin: 16px 0 0; color: #52525b; font-size: 17px; line-height: 1.65; }
            .ogf-fallback__content { margin-top: 28px; font-size: 17px; line-height: 1.75; }
            .ogf-fallback__content p { margin: 0 0 16px; }
            .ogf-fallback__source { margin-top: 30px; padding: 16px 18px; border-radius: 14px; background: #f7f7f8; color: #18181b; text-decoration: none; display: flex; align-items: center; justify-content: space-between; transition: background .15s ease; }
            .ogf-fallback__source:hover { background: #eeeeef; }
            .ogf-fallback__source span span { display: block; color: #71717a; font-size: 11px; line-height: 1; letter-spacing: .08em; text-transform: uppercase; }
            .ogf-fallback__source strong { display: block; margin-top: 6px; font-size: 15px; }
            .ogf-fallback__source b { font-size: 22px; font-weight: 400; }
            @media (max-width: 640px) { .ogf-fallback__header { padding: 0 16px; } .ogf-fallback__page { width: 100%; margin: 0; } .ogf-fallback__card { border-width: 0 0 1px; border-radius: 0; padding: 22px 18px 28px; } }
        </style>
    @endif
    @if ($custom_footer_code !== '')
        {!! $custom_footer_code !!}
    @endif
</body>

</html>

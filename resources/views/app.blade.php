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
            window.addEventListener('error', function(event) {
                const details = [event.message, event.filename, event.error?.stack].filter(Boolean).join(' ');
                const isStaleThemeBundle = details.includes("reading 'appearance'")
                    || details.includes('useThemeConfig-CMn459mP.js')
                    || details.includes('AppLayout-CDEtE47D.js');

                if (! isStaleThemeBundle || sessionStorage.getItem('ografi-asset-recovery') === '1') {
                    return;
                }

                sessionStorage.setItem('ografi-asset-recovery', '1');

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
            });

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
    <script>
        (function() {
            const logoUrl = 'https://ografi.com/uploads/media/01KXC8H7ENWNCD3VE38WXP3YTJ.png';

            const replaceLegacyLogo = () => {
                document.querySelectorAll('svg.max-h-7').forEach((legacyLogo) => {
                    const image = document.createElement('img');
                    image.src = logoUrl;
                    image.alt = 'Ografi';
                    image.className = legacyLogo.getAttribute('class') || 'max-h-7 w-full h-full';
                    image.style.objectFit = 'contain';
                    legacyLogo.replaceWith(image);
                });

                document.querySelectorAll('img.max-h-7[alt="logo"]').forEach((image) => {
                    if (image.src !== logoUrl) image.src = logoUrl;
                });
            };

            const openResourcesMenu = () => {
                document.querySelectorAll('button[data-state="closed"]').forEach((button) => {
                    if (button.textContent.trim().includes('Resources')) {
                        button.click();
                    }
                });
            };

            const addHeaderBrandName = () => {
                const logo = document.querySelector('header img[alt="Ografi"], header img[alt="logo"]');
                const logoLink = logo?.closest('a');

                if (! logoLink || logoLink.querySelector('[data-ografi-brand-name]')) {
                    return;
                }

                const brandName = document.createElement('span');
                brandName.dataset.ografiBrandName = 'true';
                brandName.textContent = 'Ografi';
                brandName.className = 'ml-2 text-xl font-bold tracking-tight text-foreground';
                logoLink.appendChild(brandName);
            };

            const applyLegacyUiFixes = () => {
                replaceLegacyLogo();
                addHeaderBrandName();
                openResourcesMenu();
            };

            document.addEventListener('DOMContentLoaded', applyLegacyUiFixes);
            document.addEventListener('inertia:navigate', applyLegacyUiFixes);

            new MutationObserver(applyLegacyUiFixes).observe(document.documentElement, {
                childList: true,
                subtree: true,
            });
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
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
</head>

<body class="font-sans antialiased">
    @inertia
    @if ($custom_footer_code !== '')
        {!! $custom_footer_code !!}
    @endif
</body>

</html>

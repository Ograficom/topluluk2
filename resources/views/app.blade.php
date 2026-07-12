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
    </style>
    <script>
        (function() {
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
            const logoUrl = 'https://ografi.com/uploads/media/01KXC0BBQ5RS7D6914VPF4R9AJ.png';

            const replaceLegacyLogo = () => {
                document.querySelectorAll('svg.max-h-7').forEach((legacyLogo) => {
                    const image = document.createElement('img');
                    image.src = logoUrl;
                    image.alt = 'Ografi';
                    image.className = legacyLogo.getAttribute('class') || 'max-h-7 w-full h-full';
                    image.style.objectFit = 'contain';
                    legacyLogo.replaceWith(image);
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

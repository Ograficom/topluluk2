<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Kurulum</title>
        @include('partials.system-appearance')
        @include('partials.google-analytics')
        @include('partials.font-assets')
        @include('partials.tailwind-cdn')
        <style>
            [x-cloak] {
                display: none;
            }

            body {
                font-family: "Roboto", Arial, Helvetica, sans-serif;
                font-weight: 400;
            }

            body :where(h1, h2, h3, h4, h5, h6, strong, b, button, .font-light, .font-medium, .font-semibold, .font-bold, .font-extrabold, .font-black) {
                font-weight: 500 !important;
            }

            body :where(em, i) {
                font-style: italic;
                font-weight: 400 !important;
            }

            @keyframes ddIn {
                from {
                    opacity: 0;
                    transform: translateY(-8px) scale(0.98);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            @keyframes ddOut {
                from {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
                to {
                    opacity: 0;
                    transform: translateY(-8px) scale(0.98);
                }
            }

            .dd-in {
                animation: ddIn 140ms ease-out forwards;
            }

            .dd-out {
                animation: ddOut 120ms ease-in forwards;
            }

            .install-content :where([class*="max-w-"], .mx-auto) {
                max-width: 100% !important;
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
        </style>
        <style>
    :root {
        --site-header-height: 64px;
    }

    .layout-sticky {
        position: sticky;
        top: calc(var(--site-header-height) + 16px);
    }

    @media (min-width: 1024px) {
        .community-grid {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }
    }

    .community-card {
        border-radius: 0.75rem;
    }

    .community-card-body {
        padding: 1rem;
    }

    .community-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 0.5rem;
        padding: 0.5rem 0.625rem;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .community-link:hover {
        background: rgb(248 250 252 / 1);
        color: rgb(15 23 42 / 1);
    }

    .community-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        border: 1px solid rgb(226 232 240 / 1);
        background: rgb(248 250 252 / 1);
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .community-pill:hover {
        background: rgb(241 245 249 / 1);
        color: rgb(15 23 42 / 1);
    }

    .community-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        border: 1px solid rgb(226 232 240 / 1);
        background: #fff;
        padding: 0.5rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .community-btn:hover {
        border-color: rgb(203 213 225 / 1);
        background: rgb(248 250 252 / 1);
        color: rgb(15 23 42 / 1);
    }
</style>

</head>
    @php
        $themeLayout = null;
    @endphp
    <body class="bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100 font-body antialiased transition-colors duration-200 theme-minimal alma-app @auth logged-in @else logged-out @endauth">
        <main class="mx-auto w-full max-w-[656px] px-4 pb-6 pt-0">
            <div class="flex min-h-screen flex-col py-4">
                <header class="mb-4">
                    <div class="uppercase text-xs tracking-[0.3em] text-slate-400">Grafi</div>
                    <h1 class="mt-2 text-xl font-semibold">Kurulum Sihirbazi</h1>
                </header>

                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm p-6">
                    {{ $slot }}
                </div>

                <footer class="mt-3 text-xs text-slate-500">
                    Ilk kurulum icin yonlendirme ekrani.
                </footer>
            </div>
        </main>
        @include('partials.external-link-bridge')
        @include('partials.image-lightbox')
    </body>
</html>

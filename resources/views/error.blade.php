@php
    $logo = settings()->group('general')->get('site_logo');
    $logo = !empty($logo) ? Storage::disk(getCurrentDisk())->url($logo) : asset('/images/logo.svg');
    $favicon = settings()->group('general')->get('site_favicon');
    $favicon = !empty($favicon) ? Storage::disk(getCurrentDisk())->url($favicon) : asset('/images/favicon.png');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ $favicon }}" type="image/png">
    <title>{{ $title . ' - ' . env('APP_NAME') }}</title>

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
        sans-serif !important;
        }
    </style>
    @vite(['resources/css/app.css'])
</head>

<body class="font-inter h-full antialiased">
    @if ($errorCode === '503')
        <div class="grid min-h-full grid-cols-1 grid-rows-[1fr,auto,1fr] bg-white lg:grid-cols-[max(50%,36rem),1fr]">
            <header
                class="mx-auto w-full max-w-7xl px-6 pt-6 sm:pt-10 lg:col-span-2 lg:col-start-1 lg:row-start-1 lg:px-8">
                <a href="/">
                    <img src="{{ $logo }}" class="max-h-12" alt="logo" />
                </a>
            </header>
            <main
                class="mx-auto w-full max-w-7xl px-6 py-24 sm:py-32 lg:col-span-2 lg:col-start-1 lg:row-start-2 lg:px-8">
                <div class="max-w-lg">
                    <p class="text-base font-semibold leading-8 text-primary">{{ $errorCode }}</p>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        @if (!empty(config('system.maintenance.headline')))
                            {{ config('system.maintenance.headline') }}
                        @else
                            {{ $errorTitle }}
                        @endif
                    </h1>
                    <p class="mt-6 text-base leading-7 text-gray-600">
                        @if (!empty(config('system.maintenance.message')))
                            {{ config('system.maintenance.message') }}
                        @else
                            {{ $errorMsg }}
                        @endif
                    </p>
                </div>
            </main>
            <div class="hidden md:relative md:col-start-2 md:row-start-1 md:row-end-4 md:block">
                <img src="{{ asset('images/maintenance.jpg') }}"
                    alt="maintenance" class="absolute inset-0 h-full w-full object-cover">
            </div>
        </div>
    @else
        <div class="grid min-h-full grid-cols-1 grid-rows-[1fr,auto,1fr] bg-white lg:grid-cols-[max(50%,36rem),1fr]">
            <header
                class="mx-auto w-full max-w-7xl px-6 pt-6 sm:pt-10 lg:col-span-2 lg:col-start-1 lg:row-start-1 lg:px-8">
                <a href="/">
                    <img src="{{ $logo }}" class="max-h-12" alt="logo" />
                </a>
            </header>
            <main
                class="mx-auto w-full max-w-7xl px-6 py-24 sm:py-32 lg:col-span-2 lg:col-start-1 lg:row-start-2 lg:px-8">
                <div class="max-w-lg">
                    <p class="text-base font-semibold leading-8 text-primary">{{ $errorCode }}</p>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        {{ $errorTitle }}
                    </h1>
                    <p class="mt-6 text-base leading-7 text-gray-600">
                        {{ $errorMsg }}
                    </p>
                    @if ($homeLink)
                        <div class="mt-10">
                            <a href="{{ route('feed.home') }}"
                                class="rounded bg-gray-700 px-4 py-2 font-bold text-white hover:bg-gray-600">
                                {{ __('Back to home') }}
                            </a>
                        </div>
                    @endif
                </div>
            </main>
            <div class="hidden md:relative md:col-start-2 md:row-start-1 md:row-end-4 md:block">
                <img src="{{ asset('images/error-page.jpg') }}"
                    alt="error" class="absolute inset-0 h-full w-full object-cover">
            </div>
        </div>
    @endif
</body>

</html>

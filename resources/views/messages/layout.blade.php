<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <title>@yield('title', __('Messages')) - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', config('alma.appearance.default_font', 'Roboto')) }}:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['{{ config("alma.appearance.default_font", "Roboto") }}', 'sans-serif'],
                    },
                    borderRadius: {
                        'alma': '{{ config("alma.appearance.radius", "0.5") }}rem',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-6 overflow-x-auto">
                    <a href="{{ route('feed.home') }}" class="text-xl font-bold text-primary-600 hover:text-primary-700 transition flex-shrink-0">
                        {{ config('app.name', 'Alma') }}
                    </a>
                    <a href="{{ route('feed.home') }}" class="text-sm text-gray-600 hover:text-gray-900 transition whitespace-nowrap">{{ __('Home') }}</a>
                    <a href="{{ route('messages.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 transition whitespace-nowrap">{{ __('Messages') }}
                        @auth
                            @php $unread = auth()->user()->totalUnreadMessages(); @endphp
                            @if($unread > 0)
                                <span class="ml-1 inline-flex items-center justify-center bg-red-500 text-white text-xs rounded-full h-4 min-w-[16px] px-1">{{ $unread > 99 ? '99+' : $unread }}</span>
                            @endif
                        @endauth
                    </a>
                    <a href="{{ route('kyc.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition whitespace-nowrap">
                        {{ __('KYC') }}
                        @auth
                            @if(auth()->user()->kyc_status === 'verified')
                                <span class="ml-1 text-green-500">✓</span>
                            @endif
                        @endauth
                    </a>
                    <a href="{{ route('activity-log.my') }}" class="text-sm text-gray-600 hover:text-gray-900 transition whitespace-nowrap">{{ __('Activity') }}</a>
                    @auth
                        <a href="{{ route('user.show', auth()->user()->username) }}" class="text-sm text-gray-600 hover:text-gray-900 transition whitespace-nowrap">{{ __('Profile') }}</a>
                    @endauth
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-sm text-gray-600">{{ Auth::user()->name ?? Auth::user()->username }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 transition">{{ __('Logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-primary-600 hover:text-primary-700">{{ __('Login') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @if (session('success'))
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-alma text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-alma text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</body>
</html>

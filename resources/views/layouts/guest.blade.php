<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OgraFi')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="container repost-guest-container">
        <a href="{{ url('/') }}" class="logo repost-guest-logo"><span class="logo-icon">O</span><span class="logo-text">OgraFi</span></a>
        <section class="card card-body repost-guest-card">
            {{ $slot }}
        </section>
    </main>
</body>
</html>

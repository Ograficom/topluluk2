@php
    $pwa = \App\Models\PwaSetting::currentOrNull();
    $basePath = rtrim(request()->getBaseUrl(), '/');
    $manifestUrl = ($basePath !== '' ? $basePath : '') . '/manifest.json';
    $browserThemeColor = '#2563eb';
@endphp
<meta name="theme-color" content="{{ $browserThemeColor }}">
@if ($pwa && $pwa->is_enabled && !request()->routeIs('video'))
    <link rel="manifest" href="{{ $manifestUrl }}">
    <meta name="application-name" content="{{ $pwa->app_name ?? config('app.name', 'OGrafi') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $pwa->short_name ?? $pwa->app_name ?? config('app.name', 'OGrafi') }}">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    @php($appleIcon = $pwa->icon_192 ?: $pwa->icon_512)
    @if ($appleIcon)
        <link rel="apple-touch-icon" href="{{ $pwa->iconUrl($appleIcon) }}">
    @endif
@endif

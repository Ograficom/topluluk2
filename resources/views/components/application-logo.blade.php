@php
    $logoUrl = \App\Support\OptimizedImage::variantUrl(asset('storage/branding/logo.png'), 'header-96')
        ?? asset('storage/branding/logo.png');
    [$logoWidth, $logoHeight] = \App\Support\OptimizedImage::dimensions($logoUrl, [96, 96]);
@endphp

<img
    src="{{ $logoUrl }}"
    alt="{{ config('app.name', 'Ografi') }}"
    width="{{ $logoWidth }}"
    height="{{ $logoHeight }}"
    loading="eager"
    decoding="async"
    draggable="false"
    ondragstart="return false;"
    {{ $attributes }}
/>

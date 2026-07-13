@php
    $logoUrl = asset('images/ografi-logo.png') . '?v=20260714';
    [$logoWidth, $logoHeight] = [512, 512];
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

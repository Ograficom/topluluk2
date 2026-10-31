@props(['variant' => 'card', 'as' => 'div'])
@php
    $variantClasses = [
        'card' => 'rp-surface',
        'soft' => 'rp-surface rp-surface--soft',
        'toolbar' => 'rp-surface rp-surface--toolbar',
        'plain' => 'rp-surface rp-surface--plain',
    ];
    $surfaceClass = $variantClasses[$variant] ?? $variantClasses['card'];
@endphp
<{{ $as }} {{ $attributes->class([$surfaceClass]) }}>
    {{ $slot }}
</{{ $as }}>

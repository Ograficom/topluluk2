@props(['variant' => 'card'])
@php
    $variantClasses = [
        'card' => 'rp-surface',
        'soft' => 'rp-surface rp-surface--soft',
        'toolbar' => 'rp-surface rp-surface--toolbar',
        'plain' => 'rp-surface rp-surface--plain',
    ];
    $surfaceClass = $variantClasses[$variant] ?? $variantClasses['card'];
@endphp
<div {{ $attributes->class([$surfaceClass]) }}>
    {{ $slot }}
</div>

@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 rounded-2xl border border-transparent bg-clip-padding font-medium whitespace-nowrap transition-colors outline-none focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_iconify-icon]:shrink-0 [&_svg]:shrink-0';

    $variantClasses = [
        'primary' => 'bg-primary text-primary-foreground hover:opacity-90',
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-muted',
        'plain' => 'bg-transparent text-foreground hover:bg-muted',
        'outline' => 'border-border bg-background text-foreground hover:bg-muted',
        'danger' => 'bg-destructive text-white hover:opacity-90',
    ];

    $sizeClasses = [
        'sm' => 'h-7 px-3 text-sm',
        'md' => 'h-8 px-3 text-sm',
        'lg' => 'h-9 px-4 text-sm',
    ];

    // The iOS design-system Button/Primary is a 48px primary action. We only bind
    // the large primary web button so compact utility buttons keep their web sizing.
    $figmaNodeId = $variant === 'primary' && $size === 'lg' ? '1:143' : null;
@endphp

<button
    {{ $attributes->merge([
        'type' => $type,
        'class' => implode(' ', [
            $baseClasses,
            $variantClasses[$variant] ?? $variantClasses['primary'],
            $sizeClasses[$size] ?? $sizeClasses['md'],
        ]),
    ]) }}
    @if($figmaNodeId) data-figma-node="{{ $figmaNodeId }}" @endif
>
    {{ $slot }}
</button>

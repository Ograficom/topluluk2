@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold leading-6 tracking-normal focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed [&_iconify-icon]:shrink-0 [&_svg]:shrink-0';

    $variantClasses = [
        'primary' => 'bg-zinc-900 text-white hover:bg-zinc-800 active:bg-zinc-950 dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-white dark:active:bg-zinc-200',
        'secondary' => 'bg-zinc-100 text-zinc-950 hover:bg-zinc-200 active:bg-zinc-300 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700 dark:active:bg-zinc-600',
        'plain' => 'bg-transparent text-zinc-950 hover:bg-zinc-100 active:bg-zinc-200 dark:text-white dark:hover:bg-zinc-800 dark:active:bg-zinc-700',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800',
    ];

    $sizeClasses = [
        'sm' => 'min-h-8 px-3 py-1.5 text-sm',
        'md' => 'min-h-10 px-3.5 py-2 text-sm',
        'lg' => 'min-h-11 px-5 py-2.5 text-base',
    ];
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
>
    {{ $slot }}
</button>



<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex h-8 items-center justify-center gap-1.5 rounded-2xl border border-transparent bg-destructive px-3 text-sm font-medium text-white transition-opacity hover:opacity-90 focus-visible:border-ring focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_iconify-icon]:shrink-0 [&_svg]:shrink-0']) }}>
    {{ $slot }}
</button>



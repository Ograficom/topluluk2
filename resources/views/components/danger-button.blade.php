<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-red-600 px-3.5 py-2 text-sm font-semibold leading-6 tracking-normal text-white hover:bg-red-700 active:bg-red-800 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 [&_iconify-icon]:shrink-0 [&_svg]:shrink-0']) }}>
    {{ $slot }}
</button>



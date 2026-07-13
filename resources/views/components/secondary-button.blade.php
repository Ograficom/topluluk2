<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-zinc-100 px-3.5 py-2 text-sm font-semibold leading-6 tracking-normal text-zinc-950 hover:bg-zinc-200 active:bg-zinc-300 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700 dark:active:bg-zinc-600 [&_iconify-icon]:shrink-0 [&_svg]:shrink-0']) }}>
    {{ $slot }}
</button>




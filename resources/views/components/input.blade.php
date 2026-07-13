@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'block min-h-10 w-full rounded-lg bg-zinc-100 px-3.5 py-2 text-sm leading-6 text-zinc-950 placeholder:text-zinc-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-400']) !!}>



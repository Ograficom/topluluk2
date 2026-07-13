@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium leading-6 text-zinc-950 dark:text-white']) }}>
    {{ $value ?? $slot }}
</label>

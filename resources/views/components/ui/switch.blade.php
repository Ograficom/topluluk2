@props([
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
])

<label class="group relative inline-flex cursor-pointer items-center">
    @if ($name && ! $disabled)
        <input type="hidden" name="{{ $name }}" value="0">
    @endif

    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        value="{{ $value }}"
        role="switch"
        @checked($checked)
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'peer sr-only']) }}
    >
    <span
        class="relative h-7 w-12 rounded-full border border-slate-300 bg-slate-200 transition-all duration-200 group-hover:bg-white peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-500/15 peer-checked:border-emerald-500 peer-checked:bg-emerald-500 peer-checked:group-hover:bg-emerald-500 peer-disabled:cursor-not-allowed peer-disabled:opacity-50"
        aria-hidden="true"
    ></span>
    <span
        class="pointer-events-none absolute left-[3px] top-[3px] h-5 w-5 rounded-full bg-white shadow-[0_2px_8px_rgba(15,23,42,0.18)] transition-all duration-200 peer-checked:translate-x-5"
        aria-hidden="true"
    ></span>
</label>

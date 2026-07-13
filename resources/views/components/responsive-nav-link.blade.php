@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 text-start text-base font-medium text-slate-800 bg-white border border-slate-200 focus:outline-none focus:text-slate-900 focus:bg-slate-100 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 focus:outline-none focus:text-gray-800 focus:bg-gray-50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>


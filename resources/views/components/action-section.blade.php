@php
    $hasTitle = trim(strip_tags($title ?? '')) !== '';
    $hasDescription = trim(strip_tags($description ?? '')) !== '';
    $hasIntro = $hasTitle || $hasDescription;
@endphp

<div {{ $attributes->merge(['class' => $hasIntro ? 'md:grid md:grid-cols-3 md:gap-6' : '']) }}>
    @if ($hasIntro)
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>
    @endif

    <div class="{{ $hasIntro ? 'mt-5 md:mt-0 md:col-span-2' : '' }}">
        <div class="px-4 py-5 sm:p-6 bg-white border border-slate-200 shadow-sm sm:rounded-lg">
            {{ $content }}
        </div>
    </div>
</div>


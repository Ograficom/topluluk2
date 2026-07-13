@props(['submit'])

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
        <form wire:submit="{{ $submit }}">
            <div class="px-4 py-5 sm:p-6 bg-white border border-slate-200 shadow-sm {{ isset($actions) ? 'sm:rounded-tl-md sm:rounded-tr-md' : 'sm:rounded-md' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="flex items-center justify-end px-4 py-3 bg-white border border-t-0 border-slate-200 text-end sm:px-6 shadow-sm sm:rounded-bl-md sm:rounded-br-md">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>


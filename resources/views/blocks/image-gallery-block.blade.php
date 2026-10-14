@php
    $images = collect(data_get($block->data, 'images', []))
        ->filter(fn ($path) => is_string($path) && trim($path) !== '')
        ->take(20);
@endphp
@if ($images->isNotEmpty())
    <figure class="my-6">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($images as $path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}"
                     alt="{{ data_get($block->data, 'caption') ?: 'Gönderi görseli' }}"
                     loading="lazy" decoding="async" class="h-auto w-full rounded-lg object-cover">
            @endforeach
        </div>
        @if (filled(data_get($block->data, 'caption')))
            <figcaption class="mt-2 text-sm text-slate-500">{{ data_get($block->data, 'caption') }}</figcaption>
        @endif
    </figure>
@endif

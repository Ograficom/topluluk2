@php
    $tone = in_array(data_get($block->data, 'tone'), ['info', 'success', 'warning', 'neutral'], true)
        ? data_get($block->data, 'tone')
        : 'info';
    $classes = [
        'info' => 'border-blue-200 bg-blue-50 text-slate-800',
        'success' => 'border-green-200 bg-green-50 text-slate-800',
        'warning' => 'border-amber-200 bg-amber-50 text-slate-800',
        'neutral' => 'border-slate-200 bg-slate-50 text-slate-800',
    ];
@endphp
<aside class="my-6 rounded-lg border p-4 {{ $classes[$tone] }}" role="note">
    @if (filled(data_get($block->data, 'heading')))
        <h3 class="mb-2 font-semibold">{{ data_get($block->data, 'heading') }}</h3>
    @endif
    <p class="whitespace-pre-line">{{ data_get($block->data, 'body') }}</p>
</aside>

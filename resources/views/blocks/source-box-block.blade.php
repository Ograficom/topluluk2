@php
    $sourceUrl = trim((string) data_get($block->data, 'url'));
    $parts = $sourceUrl !== '' ? parse_url($sourceUrl) : false;
    $safeUrl = is_array($parts) && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
        ? $sourceUrl
        : null;
@endphp
<aside class="my-5 rounded-md bg-slate-50 p-3 text-sm text-slate-700" aria-label="Kaynakça">
    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Kaynak</p>
    @if ($safeUrl)
        <a href="{{ $safeUrl }}" target="_blank" rel="nofollow noopener noreferrer" class="font-medium underline decoration-slate-300 underline-offset-2">
            {{ data_get($block->data, 'label') ?: $safeUrl }}
        </a>
    @else
        <span class="font-medium">{{ data_get($block->data, 'label') }}</span>
    @endif
    @if (filled(data_get($block->data, 'author')))
        <p class="mt-1">{{ data_get($block->data, 'author') }}</p>
    @endif
    <div class="mt-1 flex flex-wrap gap-x-3 text-xs text-slate-500">
        @if (filled(data_get($block->data, 'published_at')))
            <span>Yayın: {{ data_get($block->data, 'published_at') }}</span>
        @endif
        @if (filled(data_get($block->data, 'accessed_at')))
            <span>Erişim: {{ data_get($block->data, 'accessed_at') }}</span>
        @endif
    </div>
</aside>

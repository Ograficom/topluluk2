@php
    $sourceUrl = trim((string) data_get($block->data, 'source_url'));
    $parts = $sourceUrl !== '' ? parse_url($sourceUrl) : false;
    $safeUrl = is_array($parts) && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
        ? $sourceUrl
        : null;
@endphp
<figure class="my-6 border-l-4 border-slate-300 pl-4">
    <blockquote class="text-lg leading-relaxed text-slate-700">{{ data_get($block->data, 'quote') }}</blockquote>
    @if (filled(data_get($block->data, 'attribution')) || $safeUrl)
        <figcaption class="mt-2 text-sm text-slate-500">
            @if (filled(data_get($block->data, 'attribution')))
                <span>{{ data_get($block->data, 'attribution') }}</span>
            @endif
            @if ($safeUrl)
                <a href="{{ $safeUrl }}" rel="nofollow noopener noreferrer" target="_blank" class="underline">Kaynak</a>
            @endif
        </figcaption>
    @endif
</figure>

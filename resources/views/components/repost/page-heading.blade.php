@props(['title', 'description' => null, 'eyebrow' => null])
<header {{ $attributes->class(['rp-page-heading']) }}>
    <div class="min-w-0">
        @if (filled($eyebrow))
            <div class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $eyebrow }}</div>
        @endif
        <h1>{{ $title }}</h1>
        @if (filled($description))
            <p>{{ $description }}</p>
        @endif
    </div>
    @if (isset($actions))
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endif
</header>

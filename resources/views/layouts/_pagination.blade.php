@if ($paginator->hasPages())
    <nav class="navigation" aria-label="Sayfalandırma">
        @unless ($paginator->onFirstPage())
            <a class="btn btn-md btn-secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev">Önceki</a>
        @endunless

        @if ($paginator->hasMorePages())
            <a class="btn btn-md btn-secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Daha fazla göster</a>
        @endif
    </nav>
@endif

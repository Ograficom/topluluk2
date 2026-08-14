@php
    $sort = $sort ?? 'popular';
    $sortOptions = $sortOptions ?? [];
@endphp

<div class="tags-sort" data-tags-sort>
    <button
        type="button"
        class="tags-sort__trigger"
        data-tags-sort-trigger
        aria-label="Sıralama menüsünü aç"
        aria-expanded="false"
        aria-controls="tags-sort-menu"
    >
        <iconify-icon icon="lucide:arrow-up-down" aria-hidden="true"></iconify-icon>
    </button>

    <div id="tags-sort-menu" class="tags-sort__menu" data-tags-sort-menu>
        <span class="tags-sort__label">{{ __('site.search.sort_label') }}</span>
        <div class="tags-sort__options">
            @foreach ($sortOptions as $sortKey => $sortOption)
                <a
                    href="{{ route('blog.tags', ['sort' => $sortKey]) }}"
                    class="tags-sort__option"
                    aria-current="{{ $sort === $sortKey ? 'true' : 'false' }}"
                >
                    <iconify-icon icon="{{ $sortOption['icon'] }}" aria-hidden="true"></iconify-icon>
                    <span>{{ $sortOption['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

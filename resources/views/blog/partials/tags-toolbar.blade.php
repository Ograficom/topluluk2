@php
    $sort = $sort ?? 'popular';
    $sortOptions = $sortOptions ?? [];
    $search = $search ?? '';
@endphp

<div class="tags-toolbar" data-tags-toolbar>
    <button
        type="button"
        class="tags-toolbar__icon"
        data-tags-search-trigger
        aria-label="{{ __('site.tags_page.search_placeholder') }}"
        aria-expanded="{{ $search !== '' ? 'true' : 'false' }}"
        aria-controls="tags-search-panel"
    >
        <iconify-icon icon="lucide:search" aria-hidden="true"></iconify-icon>
    </button>

    <div class="tags-sort" data-tags-sort>
        <button
            type="button"
            class="tags-toolbar__icon"
            data-tags-sort-trigger
            aria-label="Sıralama menüsünü aç"
            aria-expanded="false"
            aria-controls="tags-sort-menu"
        >
            <iconify-icon icon="lucide:arrow-up-down" aria-hidden="true"></iconify-icon>
        </button>

        <div id="tags-sort-menu" class="tags-sort__menu" data-tags-sort-menu>
            <a
                href="{{ route('blog.tags', array_filter(['mine' => 1, 'q' => $search !== '' ? $search : null])) }}"
                class="tags-sort__option tags-sort__option--mine"
                aria-label="Benim etiketlerim"
            >
                <iconify-icon icon="lucide:user-round" aria-hidden="true"></iconify-icon>
                <span>Benim</span>
            </a>
            <div class="tags-sort__divider" role="separator"></div>
            <span class="tags-sort__label">{{ __('site.search.sort_label') }}</span>
            <div class="tags-sort__options">
                @foreach ($sortOptions as $sortKey => $sortOption)
                    <a
                        href="{{ route('blog.tags', array_filter(['sort' => $sortKey, 'q' => $search !== '' ? $search : null])) }}"
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
</div>

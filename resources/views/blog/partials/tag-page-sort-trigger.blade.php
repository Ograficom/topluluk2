@php
    $sort = $sort ?? 'newest';
    $sortOptions = [
        'popular' => ['label' => 'Popüler', 'icon' => 'lucide:flame'],
        'newest' => ['label' => 'Yeni', 'icon' => 'lucide:sparkles'],
        'oldest' => ['label' => 'Eski', 'icon' => 'lucide:history'],
    ];
@endphp

<div class="tag-page-sort" data-tag-page-sort>
    <button
        type="button"
        class="tag-page-sort__trigger"
        data-tag-page-sort-trigger
        aria-label="Sıralama menüsünü aç"
        aria-expanded="false"
        aria-controls="tag-page-sort-menu"
    >
        <iconify-icon icon="lucide:arrow-up-down" aria-hidden="true"></iconify-icon>
    </button>

    <div id="tag-page-sort-menu" class="tag-page-sort__menu" data-tag-page-sort-menu>
        <span class="tag-page-sort__label">Sırala</span>
        <div class="tag-page-sort__options">
            @foreach ($sortOptions as $sortKey => $sortOption)
                <a
                    href="{{ request()->fullUrlWithQuery(['sort' => $sortKey]) }}"
                    class="tag-page-sort__option"
                    aria-current="{{ $sort === $sortKey ? 'true' : 'false' }}"
                >
                    <iconify-icon icon="{{ $sortOption['icon'] }}" aria-hidden="true"></iconify-icon>
                    <span>{{ $sortOption['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

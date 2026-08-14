@php
    $sort = $sort ?? 'newest';
    $search = $search ?? '';
    $sortOptions = [
        'popular' => ['label' => 'Popüler', 'icon' => 'lucide:flame'],
        'newest' => ['label' => 'Yeni', 'icon' => 'lucide:sparkles'],
        'oldest' => ['label' => 'Eski', 'icon' => 'lucide:history'],
    ];
@endphp

<div class="users-toolbar" data-users-toolbar>
    <button
        type="button"
        class="users-toolbar__icon"
        data-users-search-trigger
        aria-label="Kullanıcılarda ara"
        aria-expanded="{{ $search !== '' ? 'true' : 'false' }}"
        aria-controls="users-search-panel"
    >
        <iconify-icon icon="lucide:search" aria-hidden="true"></iconify-icon>
    </button>

    <div class="users-toolbar__sort" data-users-sort>
        <button
            type="button"
            class="users-toolbar__icon"
            data-users-sort-trigger
            aria-label="Sıralama menüsünü aç"
            aria-expanded="false"
            aria-controls="users-sort-menu"
        >
            <iconify-icon icon="lucide:arrow-up-down" aria-hidden="true"></iconify-icon>
        </button>

        <div id="users-sort-menu" class="users-toolbar__menu" data-users-sort-menu>
            <span class="users-toolbar__label">Sırala</span>
            <div class="users-toolbar__options">
                @foreach ($sortOptions as $sortKey => $sortOption)
                    <a
                        href="{{ route('users.index', array_filter(['sort' => $sortKey, 'q' => $search !== '' ? $search : null])) }}"
                        class="users-toolbar__option"
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

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
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-hidden="true">
            <path fill="currentColor" fill-rule="evenodd" d="M7 1.5a5.5 5.5 0 1 0 3.397 9.83l3.387 3.384a.75.75 0 1 0 1.06-1.061l-3.386-3.384A5.5 5.5 0 0 0 7 1.5M2.75 7a4.25 4.25 0 1 1 8.5 0a4.25 4.25 0 0 1-8.5 0" clip-rule="evenodd"></path>
        </svg>
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
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14" aria-hidden="true">
                <path fill="currentColor" fill-rule="evenodd" d="M2.402 1.494c3.114-.326 6.1-.326 9.215 0c.38.04.745.281.957.625c.205.333.242.715.054 1.064c-.952 1.773-2.301 3.403-4.186 4.626a.63.63 0 0 0-.284.524V11.7c0 .16-.095.304-.242.368l-1.494.648a.4.4 0 0 1-.561-.368V8.334a.63.63 0 0 0-.285-.525C3.692 6.586 2.342 4.956 1.39 3.183c-.375-.699.088-1.593 1.012-1.69M11.747.25a45 45 0 0 0-9.475 0C.602.425-.57 2.175.289 3.775c.987 1.838 2.383 3.561 4.322 4.893v3.68a1.65 1.65 0 0 0 2.31 1.514l1.493-.649a1.65 1.65 0 0 0 .994-1.514V8.668c1.939-1.332 3.334-3.055 4.322-4.894c.43-.8.31-1.659-.092-2.31C13.243.82 12.55.333 11.747.25" clip-rule="evenodd"></path>
            </svg>
        </button>

        <div id="users-sort-menu" class="users-toolbar__menu" data-users-sort-menu hidden>
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

@php
    $faqSort = $faqSort ?? 'ordered';
    $faqSearch = $faqSearch ?? '';
    $faqSortOptions = $faqSortOptions ?? [];
@endphp

<div class="faq-toolbar" data-faq-toolbar>
    <button
        type="button"
        class="faq-toolbar__icon"
        data-faq-search-trigger
        aria-label="Sıkça sorulan sorularda ara"
        aria-expanded="{{ $faqSearch !== '' ? 'true' : 'false' }}"
        aria-controls="faq-search-panel"
    >
        <iconify-icon icon="lucide:search" aria-hidden="true"></iconify-icon>
    </button>

    <div class="faq-sort" data-faq-sort>
        <button
            type="button"
            class="faq-toolbar__icon"
            data-faq-sort-trigger
            aria-label="Sıralama menüsünü aç"
            aria-expanded="false"
            aria-controls="faq-sort-menu"
        >
            <iconify-icon icon="lucide:arrow-up-down" aria-hidden="true"></iconify-icon>
        </button>

        <div id="faq-sort-menu" class="faq-sort__menu" data-faq-sort-menu>
            <span class="faq-sort__label">Sırala</span>
            <div class="faq-sort__options">
                @foreach ($faqSortOptions as $sortKey => $sortOption)
                    <a
                        href="{{ route('pages.sss', array_filter(['sort' => $sortKey !== 'ordered' ? $sortKey : null, 'q' => $faqSearch !== '' ? $faqSearch : null])) }}"
                        class="faq-sort__option"
                        aria-current="{{ $faqSort === $sortKey ? 'true' : 'false' }}"
                    >
                        <iconify-icon icon="{{ $sortOption['icon'] }}" aria-hidden="true"></iconify-icon>
                        <span>{{ $sortOption['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@php
    $posts = $posts ?? collect();
    $popularPosts = $popularPosts ?? collect();
    $reactionTypes = collect($reactionTypes ?? []);

    $categoryToShow = $category ?? null;

    if (!$categoryToShow && isset($activeCategory) && isset($categories) && $activeCategory) {
        $categoryToShow = collect($categories)->firstWhere('slug', $activeCategory);
    }

    $hasCategory = !empty($category ?? null) || !empty($activeCategory ?? null);
    $creator = $categoryToShow?->creator ?? null;

    $creatorName = trim((string) ($creator?->name ?? '')) !== ''
        ? $creator->name
        : 'Bilinmiyor';

    $createdAtLabel = $categoryToShow?->created_at?->translatedFormat('d F Y H:i') ?? 'Bilinmiyor';
    $updatedAtLabel = $categoryToShow?->updated_at?->translatedFormat('d F Y H:i') ?? 'Bilinmiyor';

    $followersCount = isset($followersCount) ? (int) $followersCount : 0;

    $postsCount = (int) (
        $categoryPostsCount
        ?? ($categoryToShow?->posts_count ?? collect($posts)->count())
    );

    $description = trim((string) ($categoryToShow?->description ?? ''));
    $descriptionText = $description !== '' ? $description : 'Kategori aciklamasi eklenmemis.';

    $creatorHandle = trim((string) ($creator?->username ?? '')) !== ''
        ? '@' . $creator->username
        : $creatorName;

    $createdAtText = $categoryToShow?->created_at
        ? $categoryToShow->created_at->translatedFormat('F Y') . "'te olusturuldu."
        : 'Olusturulma tarihi bilinmiyor.';

    $postsCollection = $posts;

    if (is_object($postsCollection) && method_exists($postsCollection, 'getCollection')) {
        $postsCollection = $postsCollection->getCollection();
    }

    $postItems = collect($postsCollection)->map(function ($post, $index) {
        $title = trim((string) ($post->title ?? ''));
        $slug = $post->slug ?? null;

        if ($title === '' || !$slug) {
            return null;
        }

        return [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'item' => [
                '@type' => 'BlogPosting',
                'headline' => $title,
                'url' => route('blog.post', $post),
            ],
        ];
    })->filter()->values();

    $postsItemList = null;

    if ($postItems->isNotEmpty()) {
        $postsItemList = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $postItems->all(),
        ];
    }

    $renderPostCard = function ($post) use ($reactionTypes) {
        $featured = $post->featured_image_url
            ?? $post->featured_image
            ?? $post->cover_image
            ?? null;

        $reactionTypesAll = $reactionTypes->isNotEmpty()
            ? $reactionTypes
            : collect($post->reactionTypes ?? []);

        $typeMap = collect($reactionTypesAll)->mapWithKeys(function ($type) {
            $id = $type['id'] ?? ($type->id ?? null);

            return $id ? [
                $id => [
                    'id' => $id,
                    'short_code' => $type['short_code'] ?? ($type->short_code ?? null),
                    'emoji' => $type['emoji'] ?? ($type->emoji ?? null),
                    'gif_url' => $type['gif_url'] ?? ($type->gif_url ?? null),
                    'label' => $type['label'] ?? ($type->label ?? null),
                ],
            ] : [];
        });

        $reactionCounts = collect($post->reaction_counts ?? [])->mapWithKeys(
            fn ($cnt, $typeId) => [$typeId => $cnt]
        );

        if ($reactionCounts->isEmpty() && method_exists($post, 'reactions')) {
            $reactionCounts = $post->reactions()
                ->whereNotNull('reaction_type_id')
                ->selectRaw('reaction_type_id, count(*) as count')
                ->groupBy('reaction_type_id')
                ->pluck('count', 'reaction_type_id');
        }

        $reactionPills = $reactionCounts->map(function ($count, $typeId) use ($typeMap) {
            $type = $typeMap->get($typeId);

            if (!$type) {
                return null;
            }

            return [
                'type_id' => $type['id'] ?? $typeId,
                'count' => (int) $count,
                'icon' => $type['emoji'] ?? $type['gif_url'] ?? null,
                'emoji' => $type['emoji'] ?? null,
                'gif_url' => $type['gif_url'] ?? null,
                'label' => $type['label'] ?? null,
                'short_code' => $type['short_code'] ?? null,
            ];
        })->filter()->values();

        return view('blog.post-card', [
            'post' => $post,
            'title' => filled($post->title)
                ? $post->title
                : ('/' . ltrim((string) ($post->slug ?? ''), '/')),
            'excerpt' => trim(strip_tags($post->excerpt ?? $post->content ?? '')),
            'featuredImage' => $featured,
            'createdAt' => $post->published_at,
            'authorName' => optional($post->author)->name ?? 'Topluluk',
            'authorAvatar' => optional($post->author)->profile_photo_url ?? null,
            'reactions' => $reactionPills,
            'reactionTypes' => $reactionTypesAll,
            'isHero' => false,
        ])->render();
    };

    $sortOptions = [
        'latest' => 'En sonuncu',
        'popular' => 'Tepe',
    ];

    $activeSort = 'latest';
    $activeSortLabel = $sortOptions[$activeSort];

    $sortIcon = <<<'SVG'
<svg viewBox="0 0 24 24" class="profile-reference-sort-icon" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M5 7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M8 12h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M11 17h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
SVG;

    $calendarIcon = <<<'SVG'
<svg viewBox="0 0 24 24" class="profile-reference-info-icon" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 4V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M16 4V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 10H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><rect x="4" y="6" width="16" height="14" rx="3" stroke="currentColor" stroke-width="1.8"/></svg>
SVG;

    $creatorIcon = <<<'SVG'
<svg viewBox="0 0 24 24" class="profile-reference-info-icon" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 12a3.5 3.5 0 1 0 0-7a3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="1.8"/><path d="M5 19c0-3.038 2.91-5.5 6.5-5.5S18 15.962 18 19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M19 8v3m-1.5-1.5h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
SVG;
@endphp

@push('styles')
    <style>
        @media (max-width: 640px) {
            html,
            body {
                min-height: 100% !important;
                height: auto !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
            }

            main,
            #app,
            .app,
            .page,
            .site-page,
            .site-main,
            .content,
            .main-content,
            .blog-page,
            .category-reference-page,
            .profile-reference-page,
            .profile-reference-shell,
            .profile-reference-content,
            [data-category-tab-panel],
            [data-category-post-panel] {
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
            }

            [data-category-post-panel][hidden],
            [data-category-tab-panel][hidden] {
                display: none !important;
            }

            .profile-reference-content,
            .profile-reference-shell,
            .category-reference-page,
            .profile-reference-page,
            .category-post-feed,
            .category-post-feed__item {
                display: block !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
            }

            .post-card,
            .profile-post-card-wrapper,
            .category-post-feed__item {
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .post-card__media-wrap,
            .post-card__media-scroller,
            .post-card__media-slide {
                max-height: none !important;
            }

            body {
                padding-bottom: 96px !important;
            }
        }
    </style>
@endpush

@if($postsItemList)
    @push('head')
        <script type="application/ld+json">
            {!! json_encode($postsItemList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endpush
@endif

@if($hasCategory)
    <div class="profile-reference-tabs-bar">
        <nav class="profile-reference-tabs" aria-label="Tabs">
            <button type="button" class="profile-reference-tab" data-category-tab-trigger="posts" data-category-post-mode="latest" aria-current="page">En sonuncu</button>
            <button type="button" class="profile-reference-tab" data-category-tab-trigger="posts" data-category-post-mode="popular" aria-current="false">Tepe</button>
            <button type="button" class="profile-reference-tab" data-category-tab-trigger="info" aria-current="false">Bilgi</button>
        </nav>

        <details class="profile-reference-sort" data-auto-close-details data-category-sort-wrap>
            <summary>
                {!! $sortIcon !!}
                <span data-category-sort-label>{{ $activeSortLabel }}</span>
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M7 14L12 9L17 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </summary>

            <div class="profile-reference-sort-panel">
                @foreach($sortOptions as $sortKey => $sortLabel)
                    <button
                        type="button"
                        class="profile-reference-sort-option w-full text-left"
                        data-category-sort-option="{{ $sortKey }}"
                        aria-current="{{ $activeSort === $sortKey ? 'true' : 'false' }}"
                    >
                        {{ $sortLabel }}
                    </button>
                @endforeach
            </div>
        </details>
    </div>

    <div class="profile-reference-content">
        <div data-category-tab-panel="posts">
            <div data-category-post-panel="latest" @if($activeSort !== 'latest') hidden @endif>
                @forelse($posts as $post)
                    <div class="profile-post-card-wrapper">
                        {!! $renderPostCard($post) !!}
                    </div>
                @empty
                    <div class="profile-reference-empty">Henuz yazi bulunamadi.</div>
                @endforelse
            </div>

            <div data-category-post-panel="popular" @if($activeSort !== 'popular') hidden @endif>
                @forelse($popularPosts as $post)
                    <div class="profile-post-card-wrapper">
                        {!! $renderPostCard($post) !!}
                    </div>
                @empty
                    <div class="profile-reference-empty">Henuz one cikan yazi bulunamadi.</div>
                @endforelse
            </div>
        </div>

        <div data-category-tab-panel="info" hidden>
            <section class="profile-reference-info-card">
                <h2 class="profile-reference-info-title">Kategori Bilgileri</h2>
                <p class="profile-reference-info-description">{{ $descriptionText }}</p>

                <div class="profile-reference-info-list">
                    <div class="profile-reference-info-item">
                        {!! $calendarIcon !!}
                        <span>{{ $createdAtText }}</span>
                    </div>

                    <div class="profile-reference-info-item">
                        {!! $creatorIcon !!}
                        <span>
                            Yaratici
                            @if($creator)
                                <a href="{{ route('users.show', $creator) }}" class="profile-reference-info-link">{{ $creatorHandle }}</a>
                            @else
                                {{ $creatorHandle }}
                            @endif
                        </span>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const sortRoot = document.querySelector('.profile-reference-sort');
                const sortWrap = document.querySelector('[data-category-sort-wrap]');
                const sortLabel = sortRoot?.querySelector('[data-category-sort-label]');
                const sortOptions = sortRoot ? Array.from(sortRoot.querySelectorAll('[data-category-sort-option]')) : [];
                const tabButtons = Array.from(document.querySelectorAll('[data-category-tab-trigger]'));

                const tabPanels = {
                    posts: document.querySelector('[data-category-tab-panel="posts"]'),
                    info: document.querySelector('[data-category-tab-panel="info"]'),
                };

                const panels = {
                    latest: document.querySelector('[data-category-post-panel="latest"]'),
                    popular: document.querySelector('[data-category-post-panel="popular"]'),
                };

                const setActiveTab = (mode, postMode = null) => {
                    Object.entries(tabPanels).forEach(([key, panel]) => {
                        if (panel) {
                            panel.hidden = key !== mode;
                        }
                    });

                    tabButtons.forEach((button) => {
                        const buttonMode = button.getAttribute('data-category-tab-trigger') || 'posts';
                        const buttonPostMode = button.getAttribute('data-category-post-mode');

                        const active = buttonMode === mode && (
                            mode !== 'posts' || !buttonPostMode || buttonPostMode === (postMode || 'latest')
                        );

                        button.setAttribute('aria-current', active ? 'page' : 'false');
                    });

                    if (sortWrap) {
                        sortWrap.hidden = mode !== 'posts';
                    }

                    if (mode !== 'posts') {
                        sortRoot?.removeAttribute('open');
                        return;
                    }

                    if (postMode) {
                        setActiveSort(postMode);
                    }
                };

                const setActiveSort = (mode) => {
                    Object.entries(panels).forEach(([key, panel]) => {
                        if (panel) {
                            panel.hidden = key !== mode;
                        }
                    });

                    sortOptions.forEach((option) => {
                        const active = option.getAttribute('data-category-sort-option') === mode;
                        option.setAttribute('aria-current', active ? 'true' : 'false');
                    });

                    const activeOption = sortOptions.find((option) => option.getAttribute('data-category-sort-option') === mode);

                    if (activeOption && sortLabel) {
                        sortLabel.textContent = activeOption.textContent.trim();
                    }

                    sortRoot?.removeAttribute('open');
                };

                tabButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const mode = button.getAttribute('data-category-tab-trigger') || 'posts';
                        const postMode = button.getAttribute('data-category-post-mode');

                        setActiveTab(mode, postMode);
                    });
                });

                sortOptions.forEach((option) => {
                    option.addEventListener('click', () => {
                        const mode = option.getAttribute('data-category-sort-option') || 'latest';

                        setActiveSort(mode);
                    });
                });

                setActiveTab('posts', @js($activeSort));
            })();
        </script>
    @endpush
@else
    <section class="category-post-feed space-y-3 overflow-visible max-h-none h-auto pb-24">
        @forelse($posts as $post)
            <div class="category-post-feed__item block overflow-visible max-h-none h-auto">
                {!! $renderPostCard($post) !!}
            </div>
        @empty
            <div class="profile-reference-empty">Henuz yazi bulunamadi.</div>
        @endforelse
    </section>
@endif
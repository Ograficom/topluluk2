@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $breadcrumbs = $breadcrumbs ?? null;
    $breadcrumbItems = collect($breadcrumbs ?? [])->filter(function ($item) {
        return is_array($item) && !empty($item['name']) && !empty($item['url']);
    })->values();

    if ($breadcrumbItems->isEmpty()) {
        $breadcrumbItems = collect([
            ['name' => 'Ana sayfa', 'url' => route('home')],
        ]);
    }

    $posts = $posts ?? collect();
    $popularPosts = $popularPosts ?? collect();
    $categories = collect($categories ?? []);
    $reactionTypes = $reactionTypes ?? collect();

    $routeCategorySlug = trim((string) request()->route('category'));
    if ($routeCategorySlug === '') {
        $routeCategorySlug = trim((string) request()->segment(count(request()->segments())));
    }

    $activeCategorySlug = trim((string) ($activeCategory ?? request()->query('category', $routeCategorySlug)));

    $categoryToShow = $category ?? null;

    if (!$categoryToShow && $activeCategorySlug !== '') {
        $categoryToShow = $categories->first(function ($item) use ($activeCategorySlug) {
            $slug = trim((string) ($item->slug ?? ''));
            $nameSlug = Str::slug((string) ($item->name ?? ''));

            return $slug === $activeCategorySlug || $nameSlug === $activeCategorySlug;
        });
    }

    if (!$categoryToShow && $routeCategorySlug !== '') {
        $categoryToShow = $categories->first(function ($item) use ($routeCategorySlug) {
            $slug = trim((string) ($item->slug ?? ''));
            $nameSlug = Str::slug((string) ($item->name ?? ''));

            return $slug === $routeCategorySlug || $nameSlug === $routeCategorySlug;
        });
    }

    $isCategoryPage = filled($categoryToShow)
        || filled($activeCategorySlug)
        || request()->is('Categorys/*')
        || request()->is('categories/*')
        || request()->is('category/*');

    $postsCanPaginate = is_object($posts)
        && method_exists($posts, 'hasMorePages')
        && method_exists($posts, 'nextPageUrl');

    $loadedCount = $postsCanPaginate && method_exists($posts, 'lastItem')
        ? (int) ($posts->lastItem() ?? count($posts))
        : count($posts);

    $totalCount = $postsCanPaginate && method_exists($posts, 'total')
        ? (int) $posts->total()
        : count($posts);
@endphp

@if($isCategoryPage)
    @section('hide_feed_header', '1')
    @section('no_container_padding')
    @endsection
@endif

@section('title', $isCategoryPage && filled($categoryToShow?->name) ? $categoryToShow->name : 'Ografi Ana Sayfa')

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbItems->map(function ($item, $index) {
        return [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
            'item' => $item['url'],
        ];
    })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

<style>
    .ografi-feed-loadmore {
        width: 100%;
        margin: 20px 0 30px;
        padding: 0 12px;
        box-sizing: border-box;
        text-align: center;
    }

    .ografi-feed-loadmore__button {
        display: inline-flex;
        width: min(100%, 310px);
        min-height: 52px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: 0;
        border-radius: 14px;
        background: #3b82f6;
        color: #ffffff;
        font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 21px;
        font-weight: 500;
        line-height: 1;
        text-decoration: none;
        box-shadow: none;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .ografi-feed-loadmore__button:hover,
    .ografi-feed-loadmore__button:focus,
    .ografi-feed-loadmore__button:active {
        background: #2563eb;
        color: #ffffff;
        text-decoration: none;
        outline: none;
    }

    .ografi-feed-loadmore__count {
        display: block;
        margin-top: 12px;
        color: #111827;
        font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        font-size: 16px;
        font-weight: 500;
        line-height: 1.2;
        letter-spacing: 0.01em;
    }

    .ografi-feed-loadmore__spinner {
        display: none;
        width: 28px;
        height: 28px;
        border: 4px solid rgba(255, 255, 255, 0.38);
        border-top-color: #ffffff;
        border-radius: 999px;
        animation: ografi-loadmore-spin 0.8s linear infinite;
    }

    .ografi-feed-loadmore__button.is-loading {
        pointer-events: none;
        opacity: 0.95;
    }

    .ografi-feed-loadmore__button.is-loading .ografi-feed-loadmore__text {
        display: none;
    }

    .ografi-feed-loadmore__button.is-loading .ografi-feed-loadmore__spinner {
        display: inline-block;
    }

    @keyframes ografi-loadmore-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .category-index-feed__content {
        width: 100%;
        max-width: 100%;
        margin-top: 16px;
        padding: 0;
        box-sizing: border-box;
    }

    @media (max-width: 640px) {
        html,
        body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
            box-sizing: border-box !important;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box !important;
        }

        body.route-category .main-grid,
        body.route-category .main-grid.main-grid--no-pad,
        body.route-category .layout-main,
        body.route-category main,
        body.alma-app.route-category .main-grid,
        body.alma-app.route-category .main-grid.main-grid--no-pad,
        body.alma-app.route-category .layout-main,
        body.alma-app.route-category main,
        body:has(.category-reference-card) .main-grid,
        body:has(.category-reference-card) .main-grid.main-grid--no-pad,
        body:has(.category-reference-card) .layout-main,
        body:has(.category-reference-card) main {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            box-sizing: border-box !important;
            overflow-x: hidden !important;
        }

        .profile-reference-page.category-reference-page {
            width: 100vw !important;
            max-width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow-x: hidden !important;
        }

        .profile-reference-page.category-reference-page .profile-reference-shell,
        .category-index-feed__content {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            box-sizing: border-box !important;
        }

        body [data-post-card-shell],
        body .post-card,
        body article[data-post-card-shell] {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
        }

        .ografi-feed-loadmore {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
            box-sizing: border-box !important;
        }

        .ografi-feed-loadmore__button {
            width: 100% !important;
            max-width: 100% !important;
            min-height: 50px;
            border-radius: 13px;
            font-size: 20px;
        }

        .ografi-feed-loadmore__count {
            font-size: 15px;
        }
    }

    html.dark .ografi-feed-loadmore__count,
    .dark .ografi-feed-loadmore__count {
        color: #f8fafc;
    }

    html.dark .ografi-feed-loadmore__button,
    .dark .ografi-feed-loadmore__button {
        background: #2563eb;
        color: #ffffff;
    }

    html.dark .ografi-feed-loadmore__button:hover,
    html.dark .ografi-feed-loadmore__button:focus,
    html.dark .ografi-feed-loadmore__button:active,
    .dark .ografi-feed-loadmore__button:hover,
    .dark .ografi-feed-loadmore__button:focus,
    .dark .ografi-feed-loadmore__button:active {
        background: #1d4ed8;
        color: #ffffff;
    }
.ografi-feed-loadmore {
    margin: 16px 0 28px;
    padding: 0;
}

.ografi-feed-loadmore__button {
    width: 44px;
    height: 44px;
    min-height: 44px;
    border-radius: 999px;
    background: #ffffff;
    color: #111111;
    font-size: 0;
}

.ografi-feed-loadmore__button:hover,
.ografi-feed-loadmore__button:focus,
.ografi-feed-loadmore__button:active,
html.dark .ografi-feed-loadmore__button,
.dark .ografi-feed-loadmore__button,
html.dark .ografi-feed-loadmore__button:hover,
html.dark .ografi-feed-loadmore__button:focus,
html.dark .ografi-feed-loadmore__button:active,
.dark .ografi-feed-loadmore__button:hover,
.dark .ografi-feed-loadmore__button:focus,
.dark .ografi-feed-loadmore__button:active {
    background: #ffffff;
    color: #111111;
}

.ografi-feed-loadmore__icon,
.ografi-feed-loadmore__icon svg {
    display: block;
    width: 18px;
    height: 18px;
}

.ografi-feed-loadmore__count,
.ografi-feed-loadmore__spinner,
.ografi-feed-loadmore__text {
    display: none !important;
}

.ografi-feed-loadmore__button.is-loading {
    opacity: 0.65;
}
</style>
@endpush

@php
    $renderPostCard = function ($post, $loop = null) use ($reactionTypes) {
        $featured = $post->featured_image_url
            ?? $post->featured_image
            ?? $post->cover_image
            ?? null;

        $reactionTypesAll = $reactionTypes ?? ($post->reactionTypes ?? collect());

        $typeMap = collect($reactionTypesAll)->mapWithKeys(function ($type) {
            $id = $type['id'] ?? ($type->id ?? null);

            return $id ? [$id => [
                'id' => $id,
                'short_code' => $type['short_code'] ?? ($type->short_code ?? null),
                'emoji' => $type['emoji'] ?? ($type->emoji ?? null),
                'gif_url' => $type['gif_url'] ?? ($type->gif_url ?? null),
                'label' => $type['label'] ?? ($type->label ?? null),
            ]] : [];
        });

        $reactionCounts = collect($post->reaction_counts ?? [])->mapWithKeys(fn ($cnt, $typeId) => [$typeId => $cnt]);

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

            $icon = $type['emoji'] ?? $type['gif_url'] ?? null;

            return [
                'type_id' => $type['id'] ?? $typeId,
                'count' => (int) $count,
                'icon' => $icon,
                'emoji' => $type['emoji'] ?? null,
                'gif_url' => $type['gif_url'] ?? null,
                'label' => $type['label'] ?? null,
                'short_code' => $type['short_code'] ?? null,
            ];
        })->filter()->values();

        return [
            'post' => $post,
            'title' => filled($post->title) ? $post->title : ('/' . ltrim((string) ($post->slug ?? ''), '/')),
            'excerpt' => trim(strip_tags($post->excerpt ?? $post->content ?? '')),
            'featuredImage' => $featured,
            'createdAt' => $post->published_at,
            'authorName' => optional($post->author)->name ?? 'Topluluk',
            'authorAvatar' => optional($post->author)->profile_photo_url ?? null,
            'reactions' => $reactionPills,
            'reactionTypes' => $reactionTypesAll,
            'isHero' => $loop?->first ?? false,
        ];
    };
@endphp

@section('content')
    @if($isCategoryPage)
        <div class="profile-reference-page category-reference-page">
            <div class="profile-reference-shell">
                @include('blog.categories.categories-show', [
                    'categories' => $categories,
                    'activeCategory' => $activeCategorySlug,
                    'category' => $categoryToShow,
                    'categoryPostsCount' => $categoryPostsCount ?? null,
                    'categoryViews' => $categoryViews ?? null,
                    'followersCount' => $followersCount ?? 0,
                    'isCategoryJoined' => $isCategoryJoined ?? false,
                ])

                <div class="category-index-feed__content profile-reference-content" data-category-post-panel>
                    @forelse($posts as $post)
                        <div class="profile-post-card-wrapper">
                            @include('blog.post-card', $renderPostCard($post, $loop))
                        </div>

                        @include('partials.ads.feed-breaks', [
                            'iteration' => $loop->iteration,
                            'isLast' => $loop->last,
                        ])
                    @empty
                        <div class="profile-reference-empty">
                            Henuz yazi bulunamadi.
                        </div>
                    @endforelse

                    @if($postsCanPaginate && $posts->hasMorePages())
                        <div class="ografi-feed-loadmore">
                            <a
                                href="{{ $posts->nextPageUrl() }}"
                                class="ografi-feed-loadmore__button"
                                rel="next"
                                data-load-more-button
                                aria-label="25 gonderi daha goster"
                            >
                                <span class="ografi-feed-loadmore__text">Devamını göster</span>
                                <span class="ografi-feed-loadmore__icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4"/>
                                    </svg>
                                </span>
                            </a>

                            <span class="ografi-feed-loadmore__count">
                                {{ $loadedCount }} / {{ $totalCount }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        @include('partials.ads.slot', [
            'slotKey' => 'ads_feed_top',
            'wrapperClass' => 'mb-4',
        ])

        @forelse($posts as $post)
            @include('blog.post-card', $renderPostCard($post, $loop))

            @include('partials.ads.feed-breaks', [
                'iteration' => $loop->iteration,
                'isLast' => $loop->last,
            ])
        @empty
            <div class="rounded-xl bg-card-light p-6 text-center text-sm text-muted-light shadow-sm dark:bg-card-dark dark:text-muted-dark">
                Henuz yazi bulunamadi.
            </div>
        @endforelse

        @if($postsCanPaginate && $posts->hasMorePages())
            <div class="ografi-feed-loadmore">
                <a
                    href="{{ $posts->nextPageUrl() }}"
                    class="ografi-feed-loadmore__button"
                    rel="next"
                    data-load-more-button
                    aria-label="25 gonderi daha goster"
                >
                    <span class="ografi-feed-loadmore__text">Devamını göster</span>
                    <span class="ografi-feed-loadmore__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4"/>
                        </svg>
                    </span>
                </a>

                <span class="ografi-feed-loadmore__count">
                    {{ $loadedCount }} / {{ $totalCount }}
                </span>
            </div>
        @endif
    @endif

    <script>
        document.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-load-more-button]');

            if (!button) {
                return;
            }

            event.preventDefault();

            if (button.classList.contains('is-loading')) {
                return;
            }

            const controls = button.closest('.ografi-feed-loadmore');
            const parent = controls?.parentElement;
            const url = button.getAttribute('href');

            if (!controls || !parent || !url) {
                window.location.href = button.href;
                return;
            }

            button.classList.add('is-loading');
            button.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html, application/xhtml+xml'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    window.location.href = button.href;
                    return;
                }

                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                const currentKeys = new Set(Array.from(document.querySelectorAll('[data-post-card-shell]')).map(function (card) {
                    return card.id || card.getAttribute('data-post-url') || '';
                }).filter(Boolean));

                const incoming = Array.from(doc.querySelectorAll('[data-post-card-shell]'))
                    .map(function (card) {
                        return card.closest('.profile-post-card-wrapper') || card;
                    })
                    .filter(function (node) {
                        const card = node.matches('[data-post-card-shell]') ? node : node.querySelector('[data-post-card-shell]');
                        const key = card?.id || card?.getAttribute('data-post-url') || '';
                        return !key || !currentKeys.has(key);
                    });

                incoming.forEach(function (node) {
                    parent.insertBefore(node, controls);
                });

                const nextControls = doc.querySelector('.ografi-feed-loadmore');
                if (nextControls?.querySelector('[data-load-more-button]')) {
                    controls.replaceWith(nextControls);
                } else {
                    controls.remove();
                }
            } catch (error) {
                window.location.href = button.href;
            }
        });
    </script>
@endsection

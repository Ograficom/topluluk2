@extends('layouts.app')

@php
    $templateTitle = $templateTitle ?? 'Ografi Template';
    $posts = $posts ?? collect();
    $reactionTypes = $reactionTypes ?? collect();
    $showFeedAd = $showFeedAd ?? true;

    // Varsayılan akış: tarih filtresi uygulama, en son paylaşılan gönderileri göster.
    // Üstteki buton metni yine "Bugün" olarak kalır; sadece filtre mantığı varsayılanda kapalıdır.
    $activeFeedTimeFilter = request()->query('feed_time', '24h');
    $feedTimeFilters = collect([
        '24h' => 'Bugün',
        'week' => 'Hafta',
        'month' => 'Ay',
        'year' => 'Yıl',
        'latest' => 'Tüm zamanlar',
    ]);

    if (! $feedTimeFilters->has($activeFeedTimeFilter)) {
        $activeFeedTimeFilter = 'latest';
    }

    $activeFeedTimeLabel = $feedTimeFilters->get($activeFeedTimeFilter, 'En son paylaşılanlar');

    $postsCanPaginate = is_object($posts)
        && method_exists($posts, 'hasMorePages')
        && method_exists($posts, 'nextPageUrl');

    $postsHasPreviousPage = $postsCanPaginate
        && method_exists($posts, 'currentPage')
        && (int) $posts->currentPage() > 1;

    $postsPreviousPageUrl = $postsHasPreviousPage && method_exists($posts, 'previousPageUrl')
        ? $posts->previousPageUrl()
        : null;

    $postsNextPageUrl = $postsCanPaginate && method_exists($posts, 'nextPageUrl')
        ? $posts->nextPageUrl()
        : null;

    $loadedCount = $postsCanPaginate && method_exists($posts, 'lastItem')
        ? (int) ($posts->lastItem() ?? count($posts))
        : count($posts);

    $totalCount = $postsCanPaginate && method_exists($posts, 'total')
        ? (int) $posts->total()
        : count($posts);


    $newPostMinutes = (int) config('ografi.feed_new_post_minutes', 30);
    $hasNewPosts = isset($hasNewPosts)
        ? (bool) $hasNewPosts
        : collect($posts ?? [])->contains(function ($post) use ($newPostMinutes) {
            try {
                $postDate = $post->created_at ?? ($post->published_at ?? null);

                return filled($postDate)
                    && \Illuminate\Support\Carbon::parse($postDate)->gt(now()->subMinutes(max(1, $newPostMinutes)));
            } catch (\Throwable $exception) {
                return false;
            }
        });
@endphp

@section('title', $templateTitle)



























@section('content')
    <div class="home-feed-shell space-y-6 pt-2 sm:pt-3 lg:pt-4">
        <style>
            /* İlk boya düzeltmesi: CSS dosyanın altına gelmeden önce üst borsa alanını skeleton olarak gösterir */
            @keyframes ografi-market-wave-critical {
                0% { background-position: 120% 0; }
                100% { background-position: -120% 0; }
            }

            .home-feed-shell .live-market-widget {
                position: relative !important;
            }

            .home-feed-shell .live-market-widget.is-market-loading {
                min-height: 42px !important;
                overflow: visible !important;
            }

            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__mobile-dropdown {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }

            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__mobile-filter,
            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item {
                position: relative !important;
                overflow: hidden !important;
                pointer-events: none !important;
            }

            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__mobile-date,
            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__chevron-icon,
            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__label,
            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__value,
            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__arrow {
                opacity: 0 !important;
                color: transparent !important;
                -webkit-text-fill-color: transparent !important;
            }

            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before,
            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::before,
            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::after {
                content: "" !important;
                position: absolute !important;
                display: block !important;
                border-radius: 999px !important;
                background: linear-gradient(90deg, #e5e7eb 0%, #f8fafc 50%, #e5e7eb 100%) !important;
                background-size: 220% 100% !important;
                animation: ografi-market-wave-critical 1.15s ease-in-out infinite !important;
            }

            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before {
                top: 50% !important;
                left: 8px !important;
                width: 58px !important;
                height: 10px !important;
                transform: translateY(-50%) !important;
            }

            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::before {
                top: 2px !important;
                left: 0 !important;
                width: 64px !important;
                height: 8px !important;
            }

            .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::after {
                top: 17px !important;
                left: 0 !important;
                width: 42px !important;
                height: 10px !important;
            }

            html.dark .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before,
            html.dark .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::before,
            html.dark .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::after,
            .dark .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before,
            .dark .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::before,
            .dark .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::after {
                background: linear-gradient(90deg, #1f2937 0%, #334155 50%, #1f2937 100%) !important;
                background-size: 220% 100% !important;
            }

            @media (min-width: 641px) {
                .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::before {
                    top: 50% !important;
                    width: 72px !important;
                    height: 9px !important;
                    transform: translateY(-50%) !important;
                }

                .home-feed-shell .live-market-widget.is-market-loading .live-market-widget__item::after {
                    display: none !important;
                    content: none !important;
                }
            }
        </style>

        <div class="home-feed-toolbar" aria-label="Gönderi filtreleri">
            <div class="home-feed-toolbar__modes" role="tablist" aria-label="Akış türü">
                <button type="button" class="home-feed-toolbar__mode is-active" role="tab" aria-selected="true" data-feed-mode="all">Tüm</button>
                <button type="button" class="home-feed-toolbar__mode" role="tab" aria-selected="false" data-feed-mode="discuss">Tartışmak</button>
                <button type="button" class="home-feed-toolbar__mode" role="tab" aria-selected="false" data-feed-mode="read">Okumak</button>
            </div>

            <div class="home-feed-toolbar__period is-mode-hidden" data-feed-filter-menu>
                <button
                    type="button"
                    class="home-feed-toolbar__period-toggle"
                    aria-label="Gönderileri tarihe göre sırala"
                    aria-expanded="false"
                    data-feed-filter-toggle
                >
                    <span data-feed-filter-label>Bugün</span>
                    <svg viewBox="0 0 20 20" fill="none" focusable="false" aria-hidden="true">
                        <path d="m6 12 4-4 4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div class="home-feed-toolbar__dropdown" data-feed-filter-dropdown>
                    @foreach($feedTimeFilters as $filterKey => $filterLabel)
                        <a
                            href="{{ $filterKey === 'latest' ? request()->fullUrlWithoutQuery(['feed_time', 'page']) : request()->fullUrlWithQuery(['feed_time' => $filterKey, 'page' => null]) }}"
                            class="home-feed-toolbar__option @if($activeFeedTimeFilter === $filterKey) is-active @endif"
                            data-feed-filter-option
                            data-filter="{{ $filterKey }}"
                        >
                            <span>{{ $filterLabel }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <style>
            .home-feed-toolbar {
                position: relative !important;
                z-index: 40 !important;
                display: flex !important;
                align-items: center !important;
                width: auto !important;
                min-height: 38px !important;
                padding: 3px 10px !important;
                margin: 0 !important;
                overflow: visible !important;
                border: 1px solid #d9dde3 !important;
                border-radius: 16px !important;
                background: #fff !important;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .03) !important;
                font-family: "Roboto", system-ui, sans-serif !important;
            }

            .home-feed-toolbar__period.is-mode-hidden {
                display: none !important;
            }
            .home-feed-toolbar__modes {
                display: flex !important;
                align-items: center !important;
                gap: 2px !important;
            }
            .home-feed-toolbar__mode,
            .home-feed-toolbar__period-toggle {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-height: 32px !important;
                padding: 0 14px !important;
                border: 0 !important;
                border-radius: 12px !important;
                background: transparent !important;
                color: #6b7280 !important;
                font-size: 14px !important;
                font-weight: 500 !important;
                line-height: 1 !important;
                white-space: nowrap !important;
                box-shadow: none !important;
                cursor: pointer !important;
            }
            .home-feed-toolbar__mode.is-active {
                background: #f1f1f3 !important;
                color: #171717 !important;
                font-weight: 600 !important;
            }
            .home-feed-toolbar__period {
                position: relative !important;
                margin-left: 2px !important;
            }
            .home-feed-toolbar__period-toggle {
                gap: 3px !important;
                padding: 0 8px 0 14px !important;
                color: #111827 !important;
                font-weight: 600 !important;
            }
            .home-feed-toolbar__period-toggle svg {
                width: 14px !important;
                height: 14px !important;
                transition: transform .16s ease !important;
            }
            .home-feed-toolbar__period.is-open .home-feed-toolbar__period-toggle svg {
                transform: rotate(180deg) !important;
            }
            .home-feed-toolbar__dropdown {
                position: absolute !important;
                top: calc(100% + 4px) !important;
                left: 0 !important;
                z-index: 1000 !important;
                display: flex !important;
                flex-direction: column !important;
                width: 128px !important;
                padding: 5px !important;
                border: 1px solid #d9dde3 !important;
                border-radius: 10px !important;
                background: #fff !important;
                box-shadow: 0 3px 8px rgba(15, 23, 42, .18) !important;
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
                transform: translateY(-4px) !important;
                transition: opacity .14s ease, transform .14s ease, visibility .14s ease !important;
            }
            .home-feed-toolbar__period.is-open .home-feed-toolbar__dropdown {
                opacity: 1 !important;
                visibility: visible !important;
                pointer-events: auto !important;
                transform: translateY(0) !important;
            }
            .home-feed-toolbar__option {
                display: flex !important;
                align-items: center !important;
                min-height: 36px !important;
                padding: 0 8px !important;
                border-radius: 7px !important;
                color: #222 !important;
                font-size: 14px !important;
                font-weight: 400 !important;
                text-decoration: none !important;
            }
            .home-feed-toolbar__option:hover,
            .home-feed-toolbar__option.is-active {
                background: #f1f1f3 !important;
                color: #111 !important;
            }
            html.dark .home-feed-toolbar,
            .dark .home-feed-toolbar,
            html.dark .home-feed-toolbar__dropdown,
            .dark .home-feed-toolbar__dropdown {
                border-color: #374151 !important;
                background: #111827 !important;
            }
            html.dark .home-feed-toolbar__mode,
            .dark .home-feed-toolbar__mode,
            html.dark .home-feed-toolbar__period-toggle,
            .dark .home-feed-toolbar__period-toggle,
            html.dark .home-feed-toolbar__option,
            .dark .home-feed-toolbar__option {
                color: #d1d5db !important;
            }
            html.dark .home-feed-toolbar__mode.is-active,
            .dark .home-feed-toolbar__mode.is-active,
            html.dark .home-feed-toolbar__option:hover,
            html.dark .home-feed-toolbar__option.is-active,
            .dark .home-feed-toolbar__option:hover,
            .dark .home-feed-toolbar__option.is-active {
                background: #273244 !important;
                color: #fff !important;
            }
            @media (max-width: 640px) {
                .home-feed-toolbar { padding-inline: 5px !important; }
                .home-feed-toolbar__mode { padding-inline: 9px !important; font-size: 13px !important; }
                .home-feed-toolbar__period-toggle { padding-inline: 8px 5px !important; font-size: 13px !important; }
            }
        </style>

        @if($showFeedAd)
            @include('partials.ads.slot', [
                'slotKey' => 'ads_feed_top',
            ])
        @endif

        @forelse($posts as $post)
            @php
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

                $reactionCounts = collect($post->reaction_counts ?? [])
                    ->mapWithKeys(fn ($cnt, $typeId) => [$typeId => $cnt]);

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
            @endphp

            @php
                $postPublishedAtIso = '';

                try {
                    $postPublishedAtIso = filled($post->published_at ?? null)
                        ? \Illuminate\Support\Carbon::parse($post->published_at)->toIso8601String()
                        : '';
                } catch (\Throwable $exception) {
                    $postPublishedAtIso = '';
                }
            @endphp

            <div class="ografi-filterable-post" data-post-published="{{ $postPublishedAtIso }}">
                @include('blog.post-card', [
                    'post' => $post,
                    'title' => filled($post->title) ? $post->title : ('/' . ltrim((string) ($post->slug ?? ''), '/')),
                    'excerpt' => trim(strip_tags($post->excerpt ?? $post->content ?? '')),
                    'featuredImage' => $featured,
                    'createdAt' => $post->published_at,
                    'authorName' => optional($post->author)->name ?? __('site.post.community_author'),
                    'authorAvatar' => optional($post->author)->profile_photo_url ?? null,
                    'reactions' => $reactionPills,
                    'reactionTypes' => $reactionTypesAll,
                    'isHero' => $loop->first,
                ])
            </div>
        @empty
            <div class="alma-empty-state">
                {{ $emptyText ?? __('There is nothing here yet') }}
            </div>
        @endforelse

        <div class="ografi-feed-filter-empty" data-feed-filter-empty hidden>
            Seçilen zaman aralığında gönderi bulunamadı.
        </div>

        @if($postsCanPaginate && ($postsHasPreviousPage || $posts->hasMorePages()))
            <div class="ografi-feed-loadmore">
                <div class="ografi-feed-loadmore__buttons">
                    @if($postsHasPreviousPage && $postsPreviousPageUrl)
                        <a
                            href="{{ $postsPreviousPageUrl }}"
                            class="ografi-feed-page-button ografi-feed-page-button--prev"
                            rel="prev"
                            data-load-more-button
                        >
                            <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M15 5 8 12l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span>Geri</span>
                            <span class="ografi-feed-loadmore__spinner" aria-hidden="true"></span>
                        </a>
                    @else
                        <span class="ografi-feed-page-button ografi-feed-page-button--prev is-disabled" aria-disabled="true">
                            <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M15 5 8 12l7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span>Geri</span>
                        </span>
                    @endif

                    <span class="ografi-feed-loadmore__count">
                        {{ $loadedCount }} / {{ $totalCount }}
                    </span>

                    @if($posts->hasMorePages() && $postsNextPageUrl)
                        <a
                            href="{{ $postsNextPageUrl }}"
                            class="ografi-feed-page-button ografi-feed-page-button--next"
                            rel="next"
                            data-load-more-button
                        >
                            <span>İleri</span>
                            <span class="ografi-feed-page-button__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="ografi-feed-loadmore__spinner" aria-hidden="true"></span>
                        </a>
                    @else
                        <span class="ografi-feed-page-button ografi-feed-page-button--next is-disabled" aria-disabled="true">
                            <span>Bitti</span>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap');
.home-feed-shell {
            text-align: left !important;
        }

        .home-feed-shell :where(h1, h2, h3, h4, h5, h6, .alma-widget__title, .post-card__title, .blog-post-card__title, .entry-title) {
            text-align: left !important;
        }

        .home-feed-shell > .live-market-widget + * {
            margin-top: 5px !important;
        }

        .live-market-widget {
            width: 100% !important;
            margin: 2px 0 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            text-align: right !important;
            font-family: "Roboto", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
        }

        .live-market-widget__mobile-panel {
            display: none !important;
        }

        .live-market-widget__mobile-panel-head,
        .live-market-widget__mobile-ranges,
        .live-market-widget__mobile-range,
        .live-market-widget__mobile-date,
        .live-market-widget__mobile-filter {
            font-family: inherit !important;
        }

        .live-market-widget__track {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 16px !important;
            width: 100% !important;
            min-height: 22px !important;
            margin-left: auto !important;
            margin-right: 0 !important;
            padding: 4px 0 0 !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            white-space: nowrap !important;
            text-align: right !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        .live-market-widget__track::-webkit-scrollbar {
            display: none !important;
        }

        .live-market-widget__item {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 4px !important;
            min-width: max-content !important;
            color: #5f6368 !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            letter-spacing: .01em !important;
            text-align: left !important;
            text-decoration: none !important;
            cursor: pointer !important;
            border: 0 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .live-market-widget__item:hover,
        .live-market-widget__item:focus,
        .live-market-widget__item:active {
            text-decoration: none !important;
            color: #374151 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .live-market-widget__label {
            color: #5f6368 !important;
            font-weight: 400 !important;
        }

        .live-market-widget__value {
            color: #4b5563 !important;
            font-weight: 400 !important;
        }

        .live-market-widget__item:hover .live-market-widget__label,
        .live-market-widget__item:hover .live-market-widget__value {
            color: #111827 !important;
        }

        .live-market-widget__arrow {
            display: inline-block !important;
            width: 0 !important;
            height: 0 !important;
            margin-left: 1px !important;
            flex: 0 0 auto !important;
        }

        .live-market-widget__arrow.is-down {
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
            border-left: 4px solid transparent !important;
            border-right: 4px solid transparent !important;
            border-top: 6px solid #ef4444 !important;
            border-bottom: 0 !important;
            border-radius: 0 !important;
        }

        .live-market-widget__arrow.is-up {
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
            border-left: 4px solid transparent !important;
            border-right: 4px solid transparent !important;
            border-bottom: 6px solid #22c55e !important;
            border-top: 0 !important;
            border-radius: 0 !important;
        }

        .live-market-widget__arrow.is-flat {
            width: 6px !important;
            height: 6px !important;
            border: 0 !important;
            border-radius: 2px !important;
            background: #9ca3af !important;
        }

        html.dark .live-market-widget__item,
        html.dark .live-market-widget__label,
        .dark .live-market-widget__item,
        .dark .live-market-widget__label {
            color: #d1d5db !important;
        }

        html.dark .live-market-widget__value,
        .dark .live-market-widget__value {
            color: #e5e7eb !important;
        }

        html.dark .live-market-widget__item:hover,
        html.dark .live-market-widget__item:hover .live-market-widget__label,
        html.dark .live-market-widget__item:hover .live-market-widget__value,
        .dark .live-market-widget__item:hover,
        .dark .live-market-widget__item:hover .live-market-widget__label,
        .dark .live-market-widget__item:hover .live-market-widget__value {
            color: #ffffff !important;
        }

        .ografi-feed-loadmore {
            width: 100% !important;
            margin: 16px 0 26px !important;
            padding: 0 10px !important;
            box-sizing: border-box !important;
            text-align: center !important;
        }

        .ografi-feed-loadmore__buttons {
            display: grid !important;
            grid-template-columns: minmax(72px, 0.85fr) auto minmax(122px, 1.25fr) !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            width: min(100%, 390px) !important;
            margin: 0 auto !important;
            box-sizing: border-box !important;
        }

        .ografi-feed-page-button {
            display: inline-flex !important;
            width: 100% !important;
            min-height: 34px !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            border-radius: 11px !important;
            font-family: "Roboto", sans-serif !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
            text-decoration: none !important;
            box-shadow: none !important;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease !important;
        }

        .ografi-feed-page-button--prev {
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            color: #6b7280 !important;
        }

        .ografi-feed-page-button--next {
            background: #2563eb !important;
            border: 1px solid #2563eb !important;
            color: #ffffff !important;
            padding-inline: 10px !important;
        }

        .ografi-feed-page-button--prev:hover,
        .ografi-feed-page-button--prev:focus,
        .ografi-feed-page-button--prev:active {
            background: #f9fafb !important;
            border-color: #d1d5db !important;
            color: #111827 !important;
            outline: none !important;
        }

        .ografi-feed-page-button--next:hover,
        .ografi-feed-page-button--next:focus,
        .ografi-feed-page-button--next:active {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
            outline: none !important;
        }

        .ografi-feed-page-button__icon {
            display: inline-flex !important;
            width: 17px !important;
            height: 17px !important;
            align-items: center !important;
            justify-content: center !important;
            color: currentColor !important;
            flex: 0 0 auto !important;
        }

        .ografi-feed-page-button__icon svg {
            width: 12px !important;
            height: 12px !important;
            display: block !important;
        }

        .ografi-feed-page-button.is-disabled {
            opacity: 0.52 !important;
            pointer-events: none !important;
            cursor: not-allowed !important;
        }

        .ografi-feed-loadmore__count {
            display: inline-flex !important;
            min-width: 58px !important;
            min-height: 28px !important;
            margin: 0 !important;
            padding: 0 9px !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            color: #111827 !important;
            font-family: "Roboto", sans-serif !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }

        .ografi-feed-loadmore__spinner {
            display: none !important;
            width: 16px !important;
            height: 16px !important;
            border: 2px solid rgba(255, 255, 255, 0.38) !important;
            border-top-color: currentColor !important;
            border-radius: 999px !important;
            animation: ografi-loadmore-spin 0.8s linear infinite !important;
        }

        .ografi-feed-page-button.is-loading > span:not(.ografi-feed-loadmore__spinner) {
            display: none !important;
        }

        .ografi-feed-page-button.is-loading .ografi-feed-loadmore__spinner {
            display: inline-block !important;
        }

        @keyframes ografi-loadmore-spin {
            to {
                transform: rotate(360deg);
            }
        }

        html.dark .ografi-feed-page-button--prev,
        .dark .ografi-feed-page-button--prev,
        html.dark .ografi-feed-loadmore__count,
        .dark .ografi-feed-loadmore__count {
            background: #111827 !important;
            border-color: #1f2937 !important;
            color: #f8fafc !important;
        }

        html.dark .ografi-feed-page-button--prev:hover,
        html.dark .ografi-feed-page-button--prev:focus,
        html.dark .ografi-feed-page-button--prev:active,
        .dark .ografi-feed-page-button--prev:hover,
        .dark .ografi-feed-page-button--prev:focus,
        .dark .ografi-feed-page-button--prev:active {
            background: #1f2937 !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }

        html.dark .ografi-feed-page-button--next,
        .dark .ografi-feed-page-button--next {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
        }

        html.dark .ografi-feed-page-button--next:hover,
        html.dark .ografi-feed-page-button--next:focus,
        html.dark .ografi-feed-page-button--next:active,
        .dark .ografi-feed-page-button--next:hover,
        .dark .ografi-feed-page-button--next:focus,
        .dark .ografi-feed-page-button--next:active {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
        }

        @media (max-width: 640px) {
            .home-feed-shell > .live-market-widget + * {
                margin-top: 4px !important;
            }

            .live-market-widget {
                position: relative !important;
                z-index: 80 !important;
                display: flex !important;
                align-items: flex-start !important;
                justify-content: space-between !important;
                gap: 10px !important;
                width: 100% !important;
                margin-top: 1px !important;
                padding: 0 8px !important;
                box-sizing: border-box !important;
                text-align: right !important;
                overflow: visible !important;
            }

            .live-market-widget__mobile-panel {
                position: relative !important;
                z-index: 90 !important;
                display: flex !important;
                flex: 0 0 104px !important;
                width: 104px !important;
                min-width: 104px !important;
                flex-direction: column !important;
                gap: 8px !important;
                border: 1px solid #ececec !important;
                border-radius: 14px !important;
                background: #ffffff !important;
                padding: 8px !important;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
                overflow: visible !important;
            }

            .live-market-widget__mobile-panel-head {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 6px !important;
            }

            .live-market-widget__mobile-date {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-height: 34px !important;
                flex: 1 1 auto !important;
                padding: 0 10px !important;
                border: 0 !important;
                border-radius: 10px !important;
                background: #f0f0f0 !important;
                color: #18181b !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                line-height: 1 !important;
                text-align: center !important;
                cursor: default !important;
                box-shadow: none !important;
                appearance: none !important;
            }

            .live-market-widget__mobile-filter {
                display: inline-flex !important;
                width: 24px !important;
                height: 24px !important;
                align-items: center !important;
                justify-content: center !important;
                border: 0 !important;
                background: transparent !important;
                color: #52525b !important;
                padding: 0 !important;
                border-radius: 999px !important;
                box-shadow: none !important;
                appearance: none !important;
            }

            .live-market-widget__mobile-filter svg {
                width: 18px !important;
                height: 18px !important;
                display: block !important;
            }

            .live-market-widget__mobile-ranges {
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 13px !important;
                padding: 1px 6px 2px !important;
            }

            .live-market-widget__mobile-range {
                display: block !important;
                color: #18181b !important;
                font-size: 12px !important;
                font-weight: 400 !important;
                line-height: 1 !important;
                white-space: nowrap !important;
                text-align: left !important;
            }

            .live-market-widget__track {
                display: flex !important;
                flex: 1 1 auto !important;
                align-items: flex-start !important;
                justify-content: flex-end !important;
                gap: 18px !important;
                width: auto !important;
                min-height: 39px !important;
                margin-left: auto !important;
                margin-right: 0 !important;
                padding: 2px 0 4px !important;
                overflow-x: auto !important;
                overflow-y: hidden !important;
                white-space: nowrap !important;
                text-align: right !important;
                scrollbar-width: none !important;
                -ms-overflow-style: none !important;
            }

            .live-market-widget__item {
                display: grid !important;
                grid-template-columns: auto auto !important;
                grid-template-rows: auto auto !important;
                align-items: center !important;
                justify-items: start !important;
                justify-content: start !important;
                column-gap: 3px !important;
                row-gap: 5px !important;
                min-width: max-content !important;
                padding: 0 !important;
                font-size: 11px !important;
                line-height: 1 !important;
                text-align: left !important;
            }

            .live-market-widget__label {
                grid-column: 1 / -1 !important;
                display: block !important;
                color: #4b5563 !important;
                font-size: 0 !important;
                font-weight: 400 !important;
                line-height: 1 !important;
                letter-spacing: .01em !important;
            }

            .live-market-widget__label::after {
                content: attr(data-short) !important;
                display: block !important;
                font-size: 10px !important;
                font-weight: 400 !important;
                line-height: 1 !important;
            }

            .live-market-widget__value {
                display: inline-block !important;
                color: #4b5563 !important;
                font-size: 11px !important;
                font-weight: 500 !important;
                line-height: 1 !important;
            }

            .live-market-widget__item.is-market-up .live-market-widget__value {
                color: #15803d !important;
            }

            .live-market-widget__item.is-market-down .live-market-widget__value {
                color: #dc2626 !important;
            }

            .live-market-widget__item.is-market-flat .live-market-widget__value {
                color: #4b5563 !important;
            }

            .live-market-widget__arrow {
                margin-left: 1px !important;
                align-self: center !important;
                justify-self: start !important;
            }

            .live-market-widget__arrow.is-up {
                border-left-width: 4px !important;
                border-right-width: 4px !important;
                border-bottom-width: 7px !important;
            }

            .live-market-widget__arrow.is-down {
                border-left-width: 4px !important;
                border-right-width: 4px !important;
                border-top-width: 7px !important;
            }

            .live-market-widget__arrow.is-flat {
                width: 6px !important;
                height: 6px !important;
                border-radius: 2px !important;
            }

            html.dark .live-market-widget__mobile-panel,
            .dark .live-market-widget__mobile-panel {
                background: #111827 !important;
                border-color: #1f2937 !important;
                box-shadow: none !important;
            }

            html.dark .live-market-widget__mobile-date,
            .dark .live-market-widget__mobile-date {
                background: #1f2937 !important;
                color: #f8fafc !important;
            }

            html.dark .live-market-widget__mobile-filter,
            .dark .live-market-widget__mobile-filter,
            html.dark .live-market-widget__mobile-range,
            .dark .live-market-widget__mobile-range {
                color: #e5e7eb !important;
            }

            html.dark .live-market-widget__label,
            .dark .live-market-widget__label {
                color: #cbd5e1 !important;
            }

            html.dark .live-market-widget__value,
            .dark .live-market-widget__value,
            html.dark .live-market-widget__item.is-market-flat .live-market-widget__value,
            .dark .live-market-widget__item.is-market-flat .live-market-widget__value {
                color: #e5e7eb !important;
            }

            html.dark .live-market-widget__item.is-market-up .live-market-widget__value,
            .dark .live-market-widget__item.is-market-up .live-market-widget__value {
                color: #4ade80 !important;
            }

            html.dark .live-market-widget__item.is-market-down .live-market-widget__value,
            .dark .live-market-widget__item.is-market-down .live-market-widget__value {
                color: #f87171 !important;
            }
        }

        @media (max-width: 420px) {
            .live-market-widget {
                gap: 8px !important;
                padding-inline: 4px !important;
            }

            .live-market-widget__mobile-panel {
                flex-basis: 100px !important;
                width: 100px !important;
                min-width: 100px !important;
                padding: 7px !important;
                border-radius: 12px !important;
            }

            .live-market-widget__mobile-date {
                min-height: 32px !important;
                font-size: 11px !important;
            }

            .live-market-widget__mobile-ranges {
                gap: 12px !important;
                padding-inline: 5px !important;
            }

            .live-market-widget__mobile-range {
                font-size: 11px !important;
            }

            .live-market-widget__track {
                gap: 14px !important;
            }

            .ografi-feed-loadmore {
                padding: 0 6px !important;
            }

            .ografi-feed-loadmore__buttons {
                width: 100% !important;
                grid-template-columns: minmax(64px, .75fr) auto minmax(118px, 1.35fr) !important;
                gap: 6px !important;
            }

            .ografi-feed-page-button {
                min-height: 33px !important;
                border-radius: 10px !important;
                font-size: 11px !important;
            }

            .ografi-feed-loadmore__count {
                min-width: 54px !important;
                min-height: 27px !important;
                font-size: 10px !important;
                padding: 0 7px !important;
            }
        }


        .ografi-filterable-post.is-filter-hidden {
            display: none !important;
        }

        .ografi-feed-filter-empty {
            width: 100% !important;
            padding: 20px 14px !important;
            border-radius: 14px !important;
            background: #ffffff !important;
            border: 1px solid #eef0f3 !important;
            color: #6b7280 !important;
            font-family: "Roboto", sans-serif !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            text-align: center !important;
        }

        html.dark .ografi-feed-filter-empty,
        .dark .ografi-feed-filter-empty {
            background: #111827 !important;
            border-color: #1f2937 !important;
            color: #d1d5db !important;
        }

        @media (max-width: 640px) {
            .live-market-widget__mobile-panel {
                position: relative !important;
                z-index: 9990 !important;
                overflow: visible !important;
            }

            .live-market-widget__mobile-panel-head {
                position: relative !important;
                align-items: center !important;
            }

            .live-market-widget__mobile-date {
                flex: 0 0 auto !important;
                min-width: 58px !important;
                padding: 0 8px !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                line-height: 1.05 !important;
                white-space: normal !important;
            }

            .live-market-widget__mobile-sort {
                position: relative !important;
                z-index: 99999 !important;
                display: inline-flex !important;
                margin-left: auto !important;
                flex: 0 0 auto !important;
            }

            .live-market-widget__mobile-filter {
                width: 28px !important;
                height: 28px !important;
                border-radius: 10px !important;
                background: transparent !important;
                color: #111827 !important;
            }

            .live-market-widget__mobile-filter:hover,
            .live-market-widget__mobile-filter:focus,
            .live-market-widget__mobile-filter.is-open {
                background: #f3f4f6 !important;
                color: #111827 !important;
                outline: none !important;
            }

            .live-market-widget__mobile-filter-text {
                position: absolute !important;
                width: 1px !important;
                height: 1px !important;
                overflow: hidden !important;
                clip: rect(0 0 0 0) !important;
                white-space: nowrap !important;
            }

            .live-market-widget__mobile-filter svg {
                width: 18px !important;
                height: 18px !important;
                transition: transform .16s ease !important;
            }

            .live-market-widget__mobile-filter.is-open svg {
                transform: rotate(180deg) !important;
            }

            .live-market-widget__mobile-dropdown {
                position: absolute !important;
                top: calc(100% + 10px) !important;
                right: 0 !important;
                z-index: 99999 !important;
                display: none !important;
                width: 172px !important;
                max-width: calc(100vw - 18px) !important;
                padding: 7px !important;
                border-radius: 16px !important;
                border: 1px solid #e5e7eb !important;
                background: #ffffff !important;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .22) !important;
                text-align: left !important;
                overflow: visible !important;
            }

            .live-market-widget__mobile-dropdown::before {
                content: "" !important;
                position: absolute !important;
                top: -7px !important;
                right: 13px !important;
                width: 12px !important;
                height: 12px !important;
                background: inherit !important;
                border-left: 1px solid #e5e7eb !important;
                border-top: 1px solid #e5e7eb !important;
                transform: rotate(45deg) !important;
            }

            .live-market-widget__mobile-sort.is-open .live-market-widget__mobile-dropdown {
                display: grid !important;
                gap: 4px !important;
            }

            .live-market-widget__mobile-panel:has(.live-market-widget__mobile-sort.is-open) {
                z-index: 99999 !important;
            }

            .live-market-widget__mobile-option {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                min-height: 36px !important;
                padding: 0 11px !important;
                border-radius: 11px !important;
                color: #111827 !important;
                font-size: 12px !important;
                font-weight: 400 !important;
                line-height: 1 !important;
                text-decoration: none !important;
                white-space: nowrap !important;
            }

            .live-market-widget__mobile-option:hover,
            .live-market-widget__mobile-option:focus,
            .live-market-widget__mobile-option.is-active {
                background: #f3f4f6 !important;
                color: #111827 !important;
                outline: none !important;
            }

            .live-market-widget__mobile-option.is-active::after {
                content: "" !important;
                width: 6px !important;
                height: 6px !important;
                border-radius: 999px !important;
                background: #2563eb !important;
                margin-left: 8px !important;
                flex: 0 0 auto !important;
            }

            .live-market-widget__mobile-active-filter {
                display: block !important;
                padding: 0 6px 2px !important;
                color: #18181b !important;
                font-size: 12px !important;
                font-weight: 400 !important;
                line-height: 1 !important;
                text-align: left !important;
                white-space: nowrap !important;
            }

            .live-market-widget__mobile-ranges,
            .live-market-widget__mobile-range {
                display: none !important;
            }

            html.dark .live-market-widget__mobile-filter,
            .dark .live-market-widget__mobile-filter {
                color: #f8fafc !important;
            }

            html.dark .live-market-widget__mobile-filter:hover,
            html.dark .live-market-widget__mobile-filter:focus,
            html.dark .live-market-widget__mobile-filter.is-open,
            .dark .live-market-widget__mobile-filter:hover,
            .dark .live-market-widget__mobile-filter:focus,
            .dark .live-market-widget__mobile-filter.is-open {
                background: #1f2937 !important;
                color: #ffffff !important;
            }

            html.dark .live-market-widget__mobile-dropdown,
            .dark .live-market-widget__mobile-dropdown {
                background: #111827 !important;
                border-color: #1f2937 !important;
                box-shadow: 0 18px 42px rgba(0, 0, 0, .34) !important;
            }

            html.dark .live-market-widget__mobile-dropdown::before,
            .dark .live-market-widget__mobile-dropdown::before {
                border-left-color: #1f2937 !important;
                border-top-color: #1f2937 !important;
            }

            html.dark .live-market-widget__mobile-option,
            .dark .live-market-widget__mobile-option,
            html.dark .live-market-widget__mobile-active-filter,
            .dark .live-market-widget__mobile-active-filter {
                color: #e5e7eb !important;
            }

            html.dark .live-market-widget__mobile-option:hover,
            html.dark .live-market-widget__mobile-option:focus,
            html.dark .live-market-widget__mobile-option.is-active,
            .dark .live-market-widget__mobile-option:hover,
            .dark .live-market-widget__mobile-option:focus,
            .dark .live-market-widget__mobile-option.is-active {
                background: #1f2937 !important;
                color: #ffffff !important;
            }
        }


        /* Mobil filtre menüsü - temiz görünüm düzeltmesi */
        @media (max-width: 640px) {
            .live-market-widget,
            .live-market-widget * {
                box-sizing: border-box !important;
            }

            .live-market-widget {
                overflow: visible !important;
                align-items: flex-start !important;
                gap: 10px !important;
            }

            .live-market-widget__mobile-panel {
                position: relative !important;
                z-index: 20 !important;
                display: flex !important;
                flex: 0 0 116px !important;
                width: 116px !important;
                min-width: 116px !important;
                max-width: 116px !important;
                flex-direction: column !important;
                gap: 8px !important;
                padding: 6px !important;
                overflow: visible !important;
                border: 1px solid #e9ecef !important;
                border-radius: 12px !important;
                background: #ffffff !important;
                box-shadow: 0 2px 8px rgba(15, 23, 42, .06) !important;
            }

            .live-market-widget__mobile-panel-head,
            .live-market-widget__mobile-sort,
            .live-market-widget__mobile-ranges {
                display: contents !important;
            }

            .live-market-widget__mobile-filter {
                position: relative !important;
                z-index: 2 !important;
                display: flex !important;
                width: 100% !important;
                height: auto !important;
                min-height: 38px !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 6px !important;
                padding: 6px 8px !important;
                border: 0 !important;
                border-radius: 10px !important;
                background: #f3f4f6 !important;
                color: #111827 !important;
                text-align: left !important;
                box-shadow: none !important;
                appearance: none !important;
            }

            .live-market-widget__mobile-filter:hover,
            .live-market-widget__mobile-filter:focus,
            .live-market-widget__mobile-filter.is-open {
                background: #eef2f7 !important;
                color: #111827 !important;
                outline: none !important;
            }

            .live-market-widget__mobile-filter-content {
                display: flex !important;
                min-width: 0 !important;
                flex: 1 1 auto !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 3px !important;
            }

            .live-market-widget__mobile-date {
                display: block !important;
                width: auto !important;
                min-width: 0 !important;
                min-height: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                color: #111827 !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                line-height: 1.05 !important;
                white-space: nowrap !important;
            }

            .live-market-widget__mobile-active-filter {
                display: block !important;
                max-width: 74px !important;
                overflow: hidden !important;
                color: #6b7280 !important;
                font-size: 10px !important;
                font-weight: 400 !important;
                line-height: 1.05 !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }

            .live-market-widget__mobile-filter-text {
                position: static !important;
                width: auto !important;
                height: auto !important;
                overflow: visible !important;
                clip: auto !important;
                white-space: normal !important;
            }

            .live-market-widget__mobile-filter svg {
                display: block !important;
                width: 16px !important;
                height: 16px !important;
                flex: 0 0 16px !important;
                color: #111827 !important;
                transition: transform .16s ease !important;
            }

            .live-market-widget__mobile-filter.is-open svg {
                transform: rotate(180deg) !important;
            }

            .live-market-widget__mobile-dropdown {
                position: static !important;
                inset: auto !important;
                z-index: 1 !important;
                display: none !important;
                width: 100% !important;
                max-width: none !important;
                min-width: 0 !important;
                margin: 0 !important;
                padding: 4px !important;
                overflow: hidden !important;
                border: 1px solid #edf0f3 !important;
                border-radius: 12px !important;
                background: #ffffff !important;
                box-shadow: none !important;
                text-align: left !important;
            }

            .live-market-widget__mobile-dropdown::before {
                display: none !important;
                content: none !important;
            }

            .live-market-widget__mobile-panel.is-open .live-market-widget__mobile-dropdown,
            .live-market-widget__mobile-sort.is-open .live-market-widget__mobile-dropdown {
                display: flex !important;
                flex-direction: column !important;
                gap: 2px !important;
            }

            .live-market-widget__mobile-option {
                position: relative !important;
                display: flex !important;
                width: 100% !important;
                min-height: 28px !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 7px !important;
                padding: 0 8px !important;
                overflow: hidden !important;
                border-radius: 9px !important;
                color: #111827 !important;
                font-size: 11px !important;
                font-weight: 400 !important;
                line-height: 1 !important;
                text-align: left !important;
                text-decoration: none !important;
                white-space: nowrap !important;
            }

            .live-market-widget__mobile-option:hover,
            .live-market-widget__mobile-option:focus,
            .live-market-widget__mobile-option.is-active {
                background: #f3f4f6 !important;
                color: #111827 !important;
                outline: none !important;
            }

            .live-market-widget__mobile-option-dot {
                display: none !important;
                width: 6px !important;
                height: 6px !important;
                flex: 0 0 6px !important;
                border-radius: 999px !important;
                background: #2563eb !important;
            }

            .live-market-widget__mobile-option.is-active .live-market-widget__mobile-option-dot {
                display: inline-block !important;
            }

            .live-market-widget__track {
                z-index: 1 !important;
            }

            html.dark .live-market-widget__mobile-panel,
            .dark .live-market-widget__mobile-panel {
                border-color: #1f2937 !important;
                background: #111827 !important;
                box-shadow: none !important;
            }

            html.dark .live-market-widget__mobile-filter,
            .dark .live-market-widget__mobile-filter {
                background: #1f2937 !important;
                color: #ffffff !important;
            }

            html.dark .live-market-widget__mobile-date,
            html.dark .live-market-widget__mobile-filter svg,
            .dark .live-market-widget__mobile-date,
            .dark .live-market-widget__mobile-filter svg {
                color: #ffffff !important;
            }

            html.dark .live-market-widget__mobile-active-filter,
            .dark .live-market-widget__mobile-active-filter {
                color: #cbd5e1 !important;
            }

            html.dark .live-market-widget__mobile-dropdown,
            .dark .live-market-widget__mobile-dropdown {
                border-color: #273449 !important;
                background: #0f172a !important;
            }

            html.dark .live-market-widget__mobile-option,
            .dark .live-market-widget__mobile-option {
                color: #e5e7eb !important;
            }

            html.dark .live-market-widget__mobile-option:hover,
            html.dark .live-market-widget__mobile-option:focus,
            html.dark .live-market-widget__mobile-option.is-active,
            .dark .live-market-widget__mobile-option:hover,
            .dark .live-market-widget__mobile-option:focus,
            .dark .live-market-widget__mobile-option.is-active {
                background: #1f2937 !important;
                color: #ffffff !important;
            }
        }

        @media (max-width: 420px) {
            .live-market-widget__mobile-panel {
                flex-basis: 112px !important;
                width: 112px !important;
                min-width: 112px !important;
                max-width: 112px !important;
                padding: 5px !important;
            }

            .live-market-widget__mobile-option {
                min-height: 29px !important;
                padding-inline: 7px !important;
                font-size: 10.5px !important;
            }
        }


        /* Final mobile filter fix: no height jump, transparent title, no active text under date */
        @media (max-width: 640px) {
            .home-feed-shell,
            .home-feed-shell > .live-market-widget,
            .live-market-widget {
                overflow: visible !important;
            }

            .live-market-widget {
                position: relative !important;
                z-index: 30 !important;
                align-items: flex-start !important;
                min-height: 54px !important;
                height: 54px !important;
                padding-top: 8px !important;
                padding-bottom: 6px !important;
                background: #f8fafc !important;
            }

            .live-market-widget__mobile-panel {
                position: relative !important;
                z-index: 80 !important;
                flex: 0 0 108px !important;
                width: 108px !important;
                min-width: 108px !important;
                height: 38px !important;
                min-height: 38px !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                background: transparent !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .live-market-widget__mobile-panel.is-open {
                height: 38px !important;
                min-height: 38px !important;
                max-height: 38px !important;
            }

            .live-market-widget__mobile-filter {
                display: flex !important;
                width: 108px !important;
                height: 38px !important;
                min-height: 38px !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 7px !important;
                padding: 0 10px !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 11px !important;
                background: #ffffff !important;
                color: #111827 !important;
                box-shadow: none !important;
            }

            .live-market-widget__mobile-filter:hover,
            .live-market-widget__mobile-filter:focus,
            .live-market-widget__mobile-filter.is-open {
                background: #ffffff !important;
                border-color: #e5e7eb !important;
                color: #111827 !important;
                outline: none !important;
                box-shadow: none !important;
            }

            .live-market-widget__mobile-filter-content {
                display: flex !important;
                align-items: center !important;
                justify-content: flex-start !important;
                min-width: 0 !important;
                width: auto !important;
                height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
            }

            .live-market-widget__mobile-date {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: flex-start !important;
                width: auto !important;
                min-width: 0 !important;
                min-height: 0 !important;
                height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                color: #111827 !important;
                font-size: 12px !important;
                font-weight: 700 !important;
                line-height: 1 !important;
                text-align: left !important;
                box-shadow: none !important;
            }

            .live-market-widget__mobile-active-filter {
                display: none !important;
            }

            .live-market-widget__mobile-filter svg {
                width: 15px !important;
                height: 15px !important;
                flex: 0 0 15px !important;
                margin: 0 !important;
                color: #111827 !important;
                transition: transform .16s ease !important;
            }

            .live-market-widget__mobile-filter.is-open svg {
                transform: rotate(180deg) !important;
            }

            .live-market-widget__mobile-dropdown {
                position: absolute !important;
                top: calc(100% + 6px) !important;
                left: 0 !important;
                z-index: 9999 !important;
                display: flex !important;
                width: 126px !important;
                min-width: 126px !important;
                max-width: 126px !important;
                flex-direction: column !important;
                gap: 2px !important;
                padding: 7px !important;
                margin: 0 !important;
                background: #ffffff !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 12px !important;
                box-shadow: 0 14px 34px rgba(15, 23, 42, .14) !important;
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
                transform: translateY(-4px) !important;
                overflow: visible !important;
            }

            .live-market-widget__mobile-dropdown::before {
                content: "" !important;
                position: absolute !important;
                top: -6px !important;
                left: 18px !important;
                width: 10px !important;
                height: 10px !important;
                background: #ffffff !important;
                border-left: 1px solid #e5e7eb !important;
                border-top: 1px solid #e5e7eb !important;
                transform: rotate(45deg) !important;
            }

            .live-market-widget__mobile-panel.is-open .live-market-widget__mobile-dropdown {
                opacity: 1 !important;
                visibility: visible !important;
                pointer-events: auto !important;
                transform: translateY(0) !important;
            }

            .live-market-widget__mobile-option {
                position: relative !important;
                z-index: 2 !important;
                display: flex !important;
                min-height: 28px !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 8px !important;
                padding: 0 8px !important;
                border-radius: 9px !important;
                color: #111827 !important;
                font-size: 12px !important;
                font-weight: 400 !important;
                line-height: 1 !important;
                text-decoration: none !important;
                background: transparent !important;
                white-space: nowrap !important;
            }

            .live-market-widget__mobile-option:hover,
            .live-market-widget__mobile-option:focus,
            .live-market-widget__mobile-option.is-active {
                background: #f3f4f6 !important;
                color: #111827 !important;
                outline: none !important;
            }

            .live-market-widget__mobile-option-dot {
                width: 6px !important;
                height: 6px !important;
                border-radius: 999px !important;
                background: transparent !important;
                flex: 0 0 6px !important;
            }

            .live-market-widget__mobile-option.is-active .live-market-widget__mobile-option-dot {
                background: #2563eb !important;
            }

            .live-market-widget__track {
                position: relative !important;
                z-index: 40 !important;
                min-height: 38px !important;
                height: 38px !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                align-items: flex-start !important;
            }

            html.dark .live-market-widget,
            .dark .live-market-widget {
                background: #0f172a !important;
            }

            html.dark .live-market-widget__mobile-panel,
            .dark .live-market-widget__mobile-panel {
                background: transparent !important;
                border: 0 !important;
                box-shadow: none !important;
            }

            html.dark .live-market-widget__mobile-filter,
            .dark .live-market-widget__mobile-filter {
                background: #111827 !important;
                border-color: #1f2937 !important;
                color: #f8fafc !important;
            }

            html.dark .live-market-widget__mobile-date,
            html.dark .live-market-widget__mobile-filter svg,
            .dark .live-market-widget__mobile-date,
            .dark .live-market-widget__mobile-filter svg {
                color: #f8fafc !important;
                background: transparent !important;
            }

            html.dark .live-market-widget__mobile-dropdown,
            .dark .live-market-widget__mobile-dropdown {
                background: #111827 !important;
                border-color: #1f2937 !important;
                box-shadow: 0 14px 34px rgba(0, 0, 0, .35) !important;
            }

            html.dark .live-market-widget__mobile-dropdown::before,
            .dark .live-market-widget__mobile-dropdown::before {
                background: #111827 !important;
                border-color: #1f2937 !important;
            }

            html.dark .live-market-widget__mobile-option,
            .dark .live-market-widget__mobile-option {
                color: #e5e7eb !important;
            }

            html.dark .live-market-widget__mobile-option:hover,
            html.dark .live-market-widget__mobile-option:focus,
            html.dark .live-market-widget__mobile-option.is-active,
            .dark .live-market-widget__mobile-option:hover,
            .dark .live-market-widget__mobile-option:focus,
            .dark .live-market-widget__mobile-option.is-active {
                background: #1f2937 !important;
                color: #ffffff !important;
            }
        }

        @media (max-width: 420px) {
            .live-market-widget__mobile-panel,
            .live-market-widget__mobile-filter {
                width: 104px !important;
                min-width: 104px !important;
                flex-basis: 104px !important;
            }

            .live-market-widget__mobile-dropdown {
                width: 124px !important;
                min-width: 124px !important;
                max-width: 124px !important;
            }
        }


        /* Son düzeltme: menü PC + mobil görünür, beyaz başlık arka planı/sınır/kalınlık/çift mavi nokta kaldırıldı */
        .live-market-widget {
            display: flex !important;
            align-items: flex-start !important;
            justify-content: space-between !important;
            gap: 12px !important;
            overflow: visible !important;
            position: relative !important;
            z-index: 80 !important;
        }

        .live-market-widget__mobile-panel {
            display: flex !important;
            position: relative !important;
            z-index: 120 !important;
            flex: 0 0 auto !important;
            width: auto !important;
            min-width: 96px !important;
            height: 40px !important;
            min-height: 40px !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .live-market-widget__mobile-filter {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            width: auto !important;
            min-width: 96px !important;
            height: 40px !important;
            min-height: 40px !important;
            padding: 0 10px !important;
            margin: 0 !important;
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            color: #111827 !important;
            font-family: "Roboto", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            appearance: none !important;
        }

        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter:active,
        .live-market-widget__mobile-filter.is-open {
            background: transparent !important;
            border: 0 !important;
            outline: none !important;
            box-shadow: none !important;
            color: #111827 !important;
        }

        .live-market-widget__mobile-filter-content,
        .live-market-widget__mobile-date {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        .live-market-widget__mobile-date {
            display: inline-flex !important;
            padding: 0 !important;
            margin: 0 !important;
            min-width: 0 !important;
            min-height: 0 !important;
            height: auto !important;
            color: #111827 !important;
            font-family: "Roboto", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
        }

        .live-market-widget__mobile-active-filter {
            display: none !important;
        }

        .live-market-widget__mobile-filter svg {
            width: 14px !important;
            height: 14px !important;
            color: #111827 !important;
            flex: 0 0 14px !important;
        }

        .live-market-widget__mobile-dropdown {
            position: absolute !important;
            top: calc(100% + 4px) !important;
            left: 0 !important;
            z-index: 99999 !important;
            display: flex !important;
            width: 132px !important;
            min-width: 132px !important;
            max-width: 132px !important;
            flex-direction: column !important;
            gap: 2px !important;
            padding: 7px !important;
            margin: 0 !important;
            background: #ffffff !important;
            border: 0 !important;
            border-radius: 12px !important;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .14) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transform: translateY(-4px) !important;
            overflow: visible !important;
        }

        .live-market-widget__mobile-dropdown::before {
            display: none !important;
            content: none !important;
        }

        .live-market-widget__mobile-panel.is-open .live-market-widget__mobile-dropdown,
        .live-market-widget__mobile-sort.is-open .live-market-widget__mobile-dropdown {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: translateY(0) !important;
        }

        .live-market-widget__mobile-option {
            min-height: 28px !important;
            padding: 0 9px !important;
            border-radius: 9px !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #111827 !important;
            font-family: "Roboto", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
        }

        .live-market-widget__mobile-option:hover,
        .live-market-widget__mobile-option:focus,
        .live-market-widget__mobile-option.is-active {
            background: #f3f4f6 !important;
            color: #111827 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .live-market-widget__mobile-option-dot,
        .live-market-widget__mobile-option::before,
        .live-market-widget__mobile-option::after {
            display: none !important;
            content: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }

        .live-market-widget__track {
            flex: 1 1 auto !important;
            width: auto !important;
            min-width: 0 !important;
        }

        html.dark .live-market-widget__mobile-panel,
        .dark .live-market-widget__mobile-panel,
        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-filter:hover,
        html.dark .live-market-widget__mobile-filter:focus,
        html.dark .live-market-widget__mobile-filter:active,
        html.dark .live-market-widget__mobile-filter.is-open,
        .dark .live-market-widget__mobile-filter:hover,
        .dark .live-market-widget__mobile-filter:focus,
        .dark .live-market-widget__mobile-filter:active,
        .dark .live-market-widget__mobile-filter.is-open {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #f8fafc !important;
        }

        html.dark .live-market-widget__mobile-date,
        html.dark .live-market-widget__mobile-filter svg,
        .dark .live-market-widget__mobile-date,
        .dark .live-market-widget__mobile-filter svg {
            color: #f8fafc !important;
            background: transparent !important;
            font-weight: 400 !important;
        }

        html.dark .live-market-widget__mobile-dropdown,
        .dark .live-market-widget__mobile-dropdown {
            background: #111827 !important;
            border: 0 !important;
            box-shadow: 0 14px 34px rgba(0, 0, 0, .34) !important;
        }

        html.dark .live-market-widget__mobile-option,
        .dark .live-market-widget__mobile-option {
            color: #e5e7eb !important;
            background: transparent !important;
        }

        html.dark .live-market-widget__mobile-option:hover,
        html.dark .live-market-widget__mobile-option:focus,
        html.dark .live-market-widget__mobile-option.is-active,
        .dark .live-market-widget__mobile-option:hover,
        .dark .live-market-widget__mobile-option:focus,
        .dark .live-market-widget__mobile-option.is-active {
            background: #1f2937 !important;
            color: #ffffff !important;
        }

        @media (min-width: 641px) {
            .live-market-widget {
                padding: 0 8px !important;
            }

            .live-market-widget__mobile-panel {
                display: flex !important;
            }

            .live-market-widget__mobile-filter {
                min-width: 96px !important;
            }
        }


        /* Son düzeltme: 18 Mayıs alanındaki beyaz arka planı tamamen kaldır */
        .live-market-widget .live-market-widget__mobile-filter,
        .live-market-widget .live-market-widget__mobile-filter:hover,
        .live-market-widget .live-market-widget__mobile-filter:focus,
        .live-market-widget .live-market-widget__mobile-filter:active,
        .live-market-widget .live-market-widget__mobile-filter.is-open,
        .live-market-widget .live-market-widget__mobile-filter-content,
        .live-market-widget .live-market-widget__mobile-date {
            background: transparent !important;
            background-color: transparent !important;
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
        }

        .live-market-widget .live-market-widget__mobile-date {
            border-radius: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            font-weight: 400 !important;
        }

    

        /* Son düzeltme: Her zaman yazısını kaldır + 18 Mayıs yanına yanıp sönen mavi nokta */
        .live-market-widget__mobile-filter-content {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 0 !important;
            gap: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .live-market-widget__mobile-date {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: auto !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #111827 !important;
            font-family: "Roboto", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            text-align: left !important;
            white-space: nowrap !important;
        }

        .live-market-widget__mobile-active-filter {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        .live-market-widget__pulse-dot {
            position: absolute !important;
            top: -5px !important;
            right: -8px !important;
            width: 6px !important;
            height: 6px !important;
            border-radius: 999px !important;
            background: #2563eb !important;
            box-shadow: 0 0 0 0 rgba(37, 99, 235, .45) !important;
            animation: ografi-blue-dot-pulse 1.15s ease-in-out infinite !important;
            pointer-events: none !important;
            z-index: 3 !important;
        }

        @keyframes ografi-blue-dot-pulse {
            0%, 100% {
                opacity: .45;
                transform: scale(.82);
                box-shadow: 0 0 0 0 rgba(37, 99, 235, .25);
            }
            50% {
                opacity: 1;
                transform: scale(1);
                box-shadow: 0 0 0 4px rgba(37, 99, 235, 0);
            }
        }

        .live-market-widget__mobile-filter,
        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter:active,
        .live-market-widget__mobile-filter.is-open {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .live-market-widget__mobile-option-dot {
            display: none !important;
        }

        html.dark .live-market-widget__mobile-date,
        .dark .live-market-widget__mobile-date {
            color: #f8fafc !important;
            background: transparent !important;
        }



        /* Final düzeltme: PC ve mobilde filtre menüsü sola hizalı, Her zaman/çift nokta yok */
        .live-market-widget {
            overflow: visible !important;
        }

        .live-market-widget__mobile-panel {
            position: relative !important;
            overflow: visible !important;
            text-align: left !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .live-market-widget__mobile-filter,
        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter:active {
            position: relative !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            outline: none !important;
            color: #0f172a !important;
            font-weight: 400 !important;
            text-align: left !important;
        }

        .live-market-widget__mobile-filter-content {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            min-width: 0 !important;
        }

        .live-market-widget__mobile-date {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #0f172a !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            padding: 0 !important;
            margin: 0 !important;
            text-align: left !important;
        }

        .live-market-widget__mobile-active-filter {
            display: none !important;
        }

        .live-market-widget__pulse-dot {
            position: absolute !important;
            right: -9px !important;
            top: -7px !important;
            width: 5px !important;
            height: 5px !important;
            min-width: 5px !important;
            min-height: 5px !important;
            border-radius: 999px !important;
            background: #2563eb !important;
            box-shadow: 0 0 0 0 rgba(37, 99, 235, .38) !important;
            animation: ografi-blue-dot-pulse 1.15s ease-in-out infinite !important;
        }

        .live-market-widget__mobile-dropdown {
            left: 0 !important;
            right: auto !important;
            top: calc(100% + 8px) !important;
            width: 152px !important;
            min-width: 152px !important;
            max-width: 152px !important;
            transform: none !important;
            text-align: left !important;
            background: #f8fafc !important;
            border: 0 !important;
            border-radius: 12px !important;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .12) !important;
            overflow: hidden !important;
            z-index: 9999 !important;
            padding: 6px !important;
        }

        .live-market-widget__mobile-option {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            width: 100% !important;
            min-height: 32px !important;
            padding: 0 10px !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 9px !important;
            background: transparent !important;
            color: #0f172a !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            text-align: left !important;
            white-space: nowrap !important;
            box-shadow: none !important;
        }

        .live-market-widget__mobile-option:hover,
        .live-market-widget__mobile-option:focus,
        .live-market-widget__mobile-option.is-active {
            background: #eef2f7 !important;
            color: #0f172a !important;
            outline: none !important;
        }

        .live-market-widget__mobile-option-dot,
        .live-market-widget__mobile-option::after,
        .live-market-widget__mobile-option.is-active::after {
            display: none !important;
            content: none !important;
        }

        @keyframes ografi-blue-dot-pulse {
            0%, 100% {
                opacity: .35;
                transform: scale(.82);
                box-shadow: 0 0 0 0 rgba(37, 99, 235, .32);
            }
            50% {
                opacity: 1;
                transform: scale(1.08);
                box-shadow: 0 0 0 5px rgba(37, 99, 235, 0);
            }
        }

        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-date,
        .dark .live-market-widget__mobile-date {
            background: transparent !important;
            color: #f8fafc !important;
        }

        html.dark .live-market-widget__mobile-dropdown,
        .dark .live-market-widget__mobile-dropdown {
            background: #111827 !important;
            border: 0 !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .34) !important;
        }

        html.dark .live-market-widget__mobile-option,
        .dark .live-market-widget__mobile-option {
            background: transparent !important;
            color: #e5e7eb !important;
        }

        html.dark .live-market-widget__mobile-option:hover,
        html.dark .live-market-widget__mobile-option:focus,
        html.dark .live-market-widget__mobile-option.is-active,
        .dark .live-market-widget__mobile-option:hover,
        .dark .live-market-widget__mobile-option:focus,
        .dark .live-market-widget__mobile-option.is-active {
            background: #1f2937 !important;
            color: #ffffff !important;
        }

    

        /* Mobil sol menü z-index düzeltmesi: döviz/filtre barı açılan menünün üstünde kalmasın */
        @media (max-width: 640px) {
            .home-feed-shell,
            .home-feed-shell > .live-market-widget,
            .live-market-widget,
            .live-market-widget__mobile-panel,
            .live-market-widget__track,
            .live-market-widget__mobile-dropdown {
                z-index: 1 !important;
            }

            .live-market-widget {
                position: relative !important;
                isolation: auto !important;
            }

            .live-market-widget__mobile-panel,
            .live-market-widget__track,
            .live-market-widget__mobile-dropdown {
                position: relative !important;
            }

            .live-market-widget__mobile-dropdown {
                z-index: 2 !important;
            }

            body:has(.mobile-menu-open) .live-market-widget,
            body:has(.sidebar-open) .live-market-widget,
            body:has(.drawer-open) .live-market-widget,
            body:has(.offcanvas-open) .live-market-widget,
            body:has(.is-sidebar-open) .live-market-widget,
            body:has(.is-menu-open) .live-market-widget,
            body:has([data-mobile-menu].is-open) .live-market-widget,
            body:has([data-sidebar-menu].is-open) .live-market-widget,
            body:has([data-drawer-menu].is-open) .live-market-widget {
                z-index: 0 !important;
            }

            .mobile-menu,
            .mobile-menu-panel,
            .mobile-navigation,
            .mobile-navigation-panel,
            .sidebar-menu,
            .sidebar-panel,
            .drawer-menu,
            .drawer-panel,
            .navigation-drawer,
            .offcanvas-menu,
            .offcanvas-panel,
            .alma-mobile-menu,
            .alma-mobile-menu-panel,
            .alma-drawer,
            .alma-drawer-panel,
            [data-mobile-menu],
            [data-sidebar-menu],
            [data-drawer-menu],
            [data-offcanvas-menu] {
                z-index: 999999 !important;
            }

            .mobile-menu-backdrop,
            .drawer-backdrop,
            .sidebar-backdrop,
            .offcanvas-backdrop,
            .alma-mobile-backdrop,
            .alma-drawer-backdrop,
            [data-mobile-backdrop],
            [data-drawer-backdrop],
            [data-sidebar-backdrop],
            [data-offcanvas-backdrop] {
                z-index: 999998 !important;
            }
        }



        /* Final: Bugün yazısı kaldırıldı, filtre ikonu eklendi, aktif menü nokta göstergesi */
        .live-market-widget__mobile-filter {
            min-width: 38px !important;
            width: 38px !important;
            height: 38px !important;
            min-height: 38px !important;
            padding: 0 !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 0 !important;
        }

        .live-market-widget__mobile-filter-content {
            position: relative !important;
            display: inline-flex !important;
            width: 24px !important;
            height: 24px !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .live-market-widget__mobile-date,
        .live-market-widget__mobile-active-filter,
        .live-market-widget__mobile-filter > svg:not(.live-market-widget__filter-icon) {
            display: none !important;
        }

        .live-market-widget__filter-icon {
            display: block !important;
            width: 22px !important;
            height: 22px !important;
            color: #111827 !important;
            flex: 0 0 22px !important;
        }

        .live-market-widget__pulse-dot {
            position: absolute !important;
            top: 1px !important;
            right: 1px !important;
            width: 5px !important;
            height: 5px !important;
            border-radius: 999px !important;
            background: #2563eb !important;
            box-shadow: 0 0 0 0 rgba(37, 99, 235, .42) !important;
            animation: ografi-blue-dot-pulse 1.15s ease-in-out infinite !important;
            pointer-events: none !important;
            z-index: 4 !important;
        }

        .live-market-widget__mobile-dropdown {
            left: 0 !important;
            width: 148px !important;
            min-width: 148px !important;
            max-width: 148px !important;
        }

        .live-market-widget__mobile-option {
            justify-content: space-between !important;
            gap: 8px !important;
            padding-right: 9px !important;
        }

        .live-market-widget__mobile-option-dot,
        .live-market-widget__mobile-option::after {
            display: none !important;
            content: none !important;
        }

        .live-market-widget__mobile-option.is-active .live-market-widget__mobile-option-dot {
            display: inline-block !important;
            width: 6px !important;
            height: 6px !important;
            min-width: 6px !important;
            border-radius: 999px !important;
            background: #2563eb !important;
            flex: 0 0 6px !important;
        }

        html.dark .live-market-widget__filter-icon,
        .dark .live-market-widget__filter-icon {
            color: #f8fafc !important;
        }


        /* Filtre iconu tıklama düzeltmesi: ikon görsel kalır, tıklama butona gider */
        .live-market-widget__mobile-filter {
            position: relative !important;
            cursor: pointer !important;
            pointer-events: auto !important;
        }

        .live-market-widget__mobile-filter-content,
        .live-market-widget__filter-icon {
            pointer-events: none !important;
        }

        .live-market-widget__mobile-dropdown,
        .live-market-widget__mobile-option {
            pointer-events: auto !important;
        }


        /* EN SON DÜZELTME: filtre ikonu kalsın, açılır menü kesin görünsün */
        .home-feed-shell,
        .home-feed-shell > .live-market-widget,
        .live-market-widget {
            overflow: visible !important;
        }

        .live-market-widget {
            position: relative !important;
            z-index: 200 !important;
        }

        .live-market-widget__mobile-panel {
            position: relative !important;
            z-index: 300 !important;
            overflow: visible !important;
        }

        .live-market-widget__mobile-panel.is-open {
            z-index: 999999 !important;
        }

        .live-market-widget__mobile-filter {
            position: relative !important;
            z-index: 2 !important;
            display: inline-flex !important;
            width: 38px !important;
            min-width: 38px !important;
            height: 38px !important;
            min-height: 38px !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 999px !important;
            background: transparent !important;
            color: #111827 !important;
            box-shadow: none !important;
            cursor: pointer !important;
            pointer-events: auto !important;
            appearance: none !important;
        }

        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter.is-open {
            background: #f3f4f6 !important;
            color: #111827 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .live-market-widget__mobile-filter-content {
            position: relative !important;
            display: inline-flex !important;
            width: 24px !important;
            height: 24px !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 !important;
            pointer-events: none !important;
        }

        .live-market-widget__filter-icon {
            display: block !important;
            width: 22px !important;
            height: 22px !important;
            color: currentColor !important;
            transform: none !important;
            pointer-events: none !important;
        }

        .live-market-widget__mobile-filter.is-open .live-market-widget__filter-icon,
        .live-market-widget__mobile-filter.is-open svg {
            transform: none !important;
        }

        .live-market-widget__mobile-date,
        .live-market-widget__mobile-active-filter,
        .live-market-widget__mobile-filter > svg:not(.live-market-widget__filter-icon) {
            display: none !important;
        }

        .live-market-widget__pulse-dot {
            position: absolute !important;
            top: 1px !important;
            right: 1px !important;
            width: 5px !important;
            height: 5px !important;
            min-width: 5px !important;
            min-height: 5px !important;
            border-radius: 999px !important;
            background: #2563eb !important;
            box-shadow: 0 0 0 0 rgba(37, 99, 235, .42) !important;
            animation: ografi-blue-dot-pulse 1.15s ease-in-out infinite !important;
            pointer-events: none !important;
            z-index: 5 !important;
        }

        .live-market-widget__mobile-dropdown {
            position: absolute !important;
            top: calc(100% + 7px) !important;
            left: 0 !important;
            right: auto !important;
            z-index: 1000000 !important;
            display: flex !important;
            width: 154px !important;
            min-width: 154px !important;
            max-width: 154px !important;
            flex-direction: column !important;
            gap: 3px !important;
            padding: 7px !important;
            margin: 0 !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 14px !important;
            background: #ffffff !important;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .22) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transform: translateY(-4px) !important;
            overflow: visible !important;
        }

        .live-market-widget__mobile-dropdown::before {
            content: "" !important;
            position: absolute !important;
            top: -6px !important;
            left: 14px !important;
            display: block !important;
            width: 10px !important;
            height: 10px !important;
            background: #ffffff !important;
            border-left: 1px solid #e5e7eb !important;
            border-top: 1px solid #e5e7eb !important;
            transform: rotate(45deg) !important;
        }

        .live-market-widget__mobile-panel.is-open .live-market-widget__mobile-dropdown {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: translateY(0) !important;
        }

        .live-market-widget__mobile-option {
            position: relative !important;
            z-index: 2 !important;
            display: flex !important;
            width: 100% !important;
            min-height: 34px !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 8px !important;
            padding: 0 10px !important;
            border: 0 !important;
            border-radius: 10px !important;
            background: transparent !important;
            color: #111827 !important;
            font-family: "Roboto", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            box-shadow: none !important;
            pointer-events: auto !important;
        }

        .live-market-widget__mobile-option:hover,
        .live-market-widget__mobile-option:focus,
        .live-market-widget__mobile-option.is-active {
            background: #f3f4f6 !important;
            color: #111827 !important;
            outline: none !important;
        }

        .live-market-widget__mobile-option-dot,
        .live-market-widget__mobile-option::after {
            display: none !important;
            content: none !important;
        }

        .live-market-widget__mobile-option.is-active .live-market-widget__mobile-option-dot {
            display: inline-block !important;
            width: 6px !important;
            height: 6px !important;
            min-width: 6px !important;
            border-radius: 999px !important;
            background: #2563eb !important;
            flex: 0 0 6px !important;
        }

        .live-market-widget__track {
            position: relative !important;
            z-index: 1 !important;
        }

        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter {
            color: #f8fafc !important;
        }

        html.dark .live-market-widget__mobile-filter:hover,
        html.dark .live-market-widget__mobile-filter:focus,
        html.dark .live-market-widget__mobile-filter.is-open,
        .dark .live-market-widget__mobile-filter:hover,
        .dark .live-market-widget__mobile-filter:focus,
        .dark .live-market-widget__mobile-filter.is-open {
            background: #1f2937 !important;
            color: #ffffff !important;
        }

        html.dark .live-market-widget__mobile-dropdown,
        .dark .live-market-widget__mobile-dropdown {
            background: #111827 !important;
            border-color: #1f2937 !important;
            box-shadow: 0 18px 44px rgba(0, 0, 0, .38) !important;
        }

        html.dark .live-market-widget__mobile-dropdown::before,
        .dark .live-market-widget__mobile-dropdown::before {
            background: #111827 !important;
            border-left-color: #1f2937 !important;
            border-top-color: #1f2937 !important;
        }

        html.dark .live-market-widget__mobile-option,
        .dark .live-market-widget__mobile-option {
            color: #e5e7eb !important;
        }

        html.dark .live-market-widget__mobile-option:hover,
        html.dark .live-market-widget__mobile-option:focus,
        html.dark .live-market-widget__mobile-option.is-active,
        .dark .live-market-widget__mobile-option:hover,
        .dark .live-market-widget__mobile-option:focus,
        .dark .live-market-widget__mobile-option.is-active {
            background: #1f2937 !important;
            color: #ffffff !important;
        }

    

        /* Filtre ikonu: beyaz arka plan + lacivert ikon + 28px */
        .live-market-widget__mobile-filter {
            width: 42px !important;
            min-width: 42px !important;
            height: 42px !important;
            min-height: 42px !important;
            padding: 0 !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #eef2f7 !important;
            box-shadow: 0 1px 4px rgba(15, 23, 42, .08) !important;
        }

        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter:active,
        .live-market-widget__mobile-filter.is-open {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #e5e7eb !important;
            box-shadow: 0 1px 4px rgba(15, 23, 42, .08) !important;
        }

        .live-market-widget__mobile-filter-content {
            position: relative !important;
            display: inline-flex !important;
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            min-height: 42px !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        .live-market-widget__filter-icon,
        .live-market-widget__mobile-filter svg {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            min-height: 28px !important;
            flex: 0 0 28px !important;
            color: #0f172a !important;
        }

        .live-market-widget__filter-icon path,
        .live-market-widget__mobile-filter svg path {
            fill: #0f172a !important;
        }

        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-filter:hover,
        html.dark .live-market-widget__mobile-filter:focus,
        html.dark .live-market-widget__mobile-filter:active,
        html.dark .live-market-widget__mobile-filter.is-open,
        .dark .live-market-widget__mobile-filter:hover,
        .dark .live-market-widget__mobile-filter:focus,
        .dark .live-market-widget__mobile-filter:active,
        .dark .live-market-widget__mobile-filter.is-open,
        html.dark .live-market-widget__mobile-filter-content,
        .dark .live-market-widget__mobile-filter-content {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #e5e7eb !important;
        }

        html.dark .live-market-widget__filter-icon,
        html.dark .live-market-widget__mobile-filter svg,
        .dark .live-market-widget__filter-icon,
        .dark .live-market-widget__mobile-filter svg {
            color: #0f172a !important;
        }

        html.dark .live-market-widget__filter-icon path,
        html.dark .live-market-widget__mobile-filter svg path,
        .dark .live-market-widget__filter-icon path,
        .dark .live-market-widget__mobile-filter svg path {
            fill: #0f172a !important;
        }

        .live-market-widget__pulse-dot {
            top: 2px !important;
            right: 2px !important;
        }


        /* Final: görseldeki filtre ikonu - beyaz kutu + lacivert çizgi ikon */
        .live-market-widget__mobile-filter {
            width: 46px !important;
            min-width: 46px !important;
            height: 46px !important;
            min-height: 46px !important;
            padding: 0 !important;
            border-radius: 16px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #eef2f7 !important;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .10) !important;
        }

        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter:active,
        .live-market-widget__mobile-filter.is-open {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #e5e7eb !important;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .12) !important;
            outline: none !important;
        }

        .live-market-widget__mobile-filter-content {
            position: relative !important;
            display: inline-flex !important;
            width: 46px !important;
            min-width: 46px !important;
            height: 46px !important;
            min-height: 46px !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 16px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            pointer-events: none !important;
        }

        .live-market-widget__filter-icon,
        .live-market-widget__mobile-filter svg.live-market-widget__filter-icon {
            display: block !important;
            width: 28px !important;
            min-width: 28px !important;
            height: 28px !important;
            min-height: 28px !important;
            flex: 0 0 28px !important;
            color: #0f172a !important;
            transform: none !important;
        }

        .live-market-widget__filter-icon path,
        .live-market-widget__mobile-filter svg.live-market-widget__filter-icon path {
            fill: none !important;
            stroke: #0f172a !important;
            stroke-width: 1.5 !important;
            stroke-linecap: round !important;
            stroke-linejoin: round !important;
        }

        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-filter:hover,
        html.dark .live-market-widget__mobile-filter:focus,
        html.dark .live-market-widget__mobile-filter:active,
        html.dark .live-market-widget__mobile-filter.is-open,
        .dark .live-market-widget__mobile-filter:hover,
        .dark .live-market-widget__mobile-filter:focus,
        .dark .live-market-widget__mobile-filter:active,
        .dark .live-market-widget__mobile-filter.is-open,
        html.dark .live-market-widget__mobile-filter-content,
        .dark .live-market-widget__mobile-filter-content {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #e5e7eb !important;
        }

        html.dark .live-market-widget__filter-icon,
        .dark .live-market-widget__filter-icon {
            color: #0f172a !important;
        }

        html.dark .live-market-widget__filter-icon path,
        .dark .live-market-widget__filter-icon path {
            fill: none !important;
            stroke: #0f172a !important;
        }

        .live-market-widget__pulse-dot {
            top: 5px !important;
            right: 5px !important;
        }

    

        /* Final düzeltme: filtre ikonuna köşesi yumuşatılmış beyaz arka plan */
        .live-market-widget__mobile-filter {
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            min-height: 42px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 16px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            border: 1px solid rgba(15, 23, 42, 0.08) !important;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08) !important;
            color: #0f172a !important;
            overflow: visible !important;
        }

        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter:active,
        .live-market-widget__mobile-filter.is-open {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: rgba(15, 23, 42, 0.12) !important;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.12) !important;
            outline: none !important;
        }

        .live-market-widget__filter-icon-wrap {
            position: relative !important;
            width: 100% !important;
            height: 100% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 16px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        .live-market-widget__filter-icon-wrap svg,
        .live-market-widget__mobile-filter svg {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            min-height: 28px !important;
            color: #0f172a !important;
            stroke: currentColor !important;
            transform: none !important;
        }

        .live-market-widget__mobile-filter.is-open svg {
            transform: none !important;
        }

        .live-market-widget__pulse-dot {
            top: 4px !important;
            right: 4px !important;
        }

        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-filter:hover,
        html.dark .live-market-widget__mobile-filter:focus,
        html.dark .live-market-widget__mobile-filter:active,
        html.dark .live-market-widget__mobile-filter.is-open,
        .dark .live-market-widget__mobile-filter:hover,
        .dark .live-market-widget__mobile-filter:focus,
        .dark .live-market-widget__mobile-filter:active,
        .dark .live-market-widget__mobile-filter.is-open,
        html.dark .live-market-widget__filter-icon-wrap,
        .dark .live-market-widget__filter-icon-wrap {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: rgba(15, 23, 42, 0.08) !important;
        }

        html.dark .live-market-widget__filter-icon-wrap svg,
        html.dark .live-market-widget__mobile-filter svg,
        .dark .live-market-widget__filter-icon-wrap svg,
        .dark .live-market-widget__mobile-filter svg {
            color: #0f172a !important;
            stroke: currentColor !important;
        }


        /* Final geri alma: Bugün yazısı + aşağı/yukarı ikon geri getirildi */
        .live-market-widget__mobile-filter {
            position: relative !important;
            z-index: 2 !important;
            display: inline-flex !important;
            width: auto !important;
            min-width: 84px !important;
            height: 40px !important;
            min-height: 40px !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            padding: 0 10px !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            color: #111827 !important;
            cursor: pointer !important;
            pointer-events: auto !important;
        }

        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter:active,
        .live-market-widget__mobile-filter.is-open {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #111827 !important;
            outline: none !important;
        }

        .live-market-widget__mobile-filter-content {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: auto !important;
            height: auto !important;
            min-width: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            pointer-events: none !important;
        }

        .live-market-widget__mobile-date {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: auto !important;
            min-width: 0 !important;
            min-height: 0 !important;
            height: auto !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            color: #111827 !important;
            font-family: "Roboto", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }

        .live-market-widget__chevron-icon,
        .live-market-widget__mobile-filter > svg.live-market-widget__chevron-icon {
            display: block !important;
            width: 14px !important;
            height: 14px !important;
            min-width: 14px !important;
            min-height: 14px !important;
            flex: 0 0 14px !important;
            margin: 0 !important;
            color: currentColor !important;
            stroke: currentColor !important;
            transform: rotate(0deg) !important;
            transition: transform .16s ease !important;
            pointer-events: none !important;
        }

        .live-market-widget__mobile-filter.is-open .live-market-widget__chevron-icon {
            transform: rotate(180deg) !important;
        }

        .live-market-widget__filter-icon {
            display: none !important;
        }

        .live-market-widget__pulse-dot {
            top: -5px !important;
            right: -8px !important;
            width: 6px !important;
            height: 6px !important;
            border-radius: 999px !important;
            background: #2563eb !important;
        }

        .live-market-widget__mobile-dropdown {
            position: absolute !important;
            top: calc(100% + 6px) !important;
            left: 0 !important;
            z-index: 999999 !important;
            display: flex !important;
            width: 148px !important;
            min-width: 148px !important;
            max-width: 148px !important;
            flex-direction: column !important;
            gap: 2px !important;
            padding: 7px !important;
            margin: 0 !important;
            background: #ffffff !important;
            border: 0 !important;
            border-radius: 12px !important;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .14) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transform: translateY(-4px) !important;
            overflow: visible !important;
        }

        .live-market-widget__mobile-panel.is-open .live-market-widget__mobile-dropdown {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: translateY(0) !important;
        }

        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-filter:hover,
        html.dark .live-market-widget__mobile-filter:focus,
        html.dark .live-market-widget__mobile-filter:active,
        html.dark .live-market-widget__mobile-filter.is-open,
        .dark .live-market-widget__mobile-filter:hover,
        .dark .live-market-widget__mobile-filter:focus,
        .dark .live-market-widget__mobile-filter:active,
        .dark .live-market-widget__mobile-filter.is-open {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #f8fafc !important;
        }

        html.dark .live-market-widget__mobile-date,
        html.dark .live-market-widget__chevron-icon,
        .dark .live-market-widget__mobile-date,
        .dark .live-market-widget__chevron-icon {
            color: #f8fafc !important;
            stroke: currentColor !important;
            background: transparent !important;
        }


        /* Final düzeltme: Bugün yazısı ile up/down ikonunu yaklaştır */
        .live-market-widget__mobile-filter {
            min-width: auto !important;
            width: auto !important;
            gap: 2px !important;
            padding-left: 0 !important;
            padding-right: 2px !important;
        }

        .live-market-widget__mobile-filter-content {
            margin-right: 0 !important;
        }

        .live-market-widget__mobile-date {
            margin-right: 0 !important;
        }

        .live-market-widget__chevron-icon,
        .live-market-widget__mobile-filter > svg.live-market-widget__chevron-icon {
            margin-left: 0 !important;
            width: 13px !important;
            height: 13px !important;
            min-width: 13px !important;
            min-height: 13px !important;
            flex-basis: 13px !important;
        }

        /* EN SON DÜZELTME: Bugün yazısı ve ok ikonu bitişik; tam ekran loader yok */
        .live-market-widget__mobile-filter {
            display: inline-flex !important;
            width: auto !important;
            min-width: 0 !important;
            height: 28px !important;
            min-height: 28px !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 2px !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #0f172a !important;
        }

        .live-market-widget__mobile-filter:hover,
        .live-market-widget__mobile-filter:focus,
        .live-market-widget__mobile-filter:active,
        .live-market-widget__mobile-filter.is-open {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #0f172a !important;
            outline: none !important;
        }

        .live-market-widget__mobile-filter-content {
            position: relative !important;
            display: inline-flex !important;
            width: auto !important;
            min-width: 0 !important;
            height: auto !important;
            min-height: 0 !important;
            align-items: center !important;
            justify-content: flex-start !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            color: inherit !important;
        }

        .live-market-widget__mobile-date {
            display: inline-flex !important;
            width: auto !important;
            min-width: 0 !important;
            height: auto !important;
            min-height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #0f172a !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }

        .live-market-widget__chevron-icon,
        .live-market-widget__mobile-filter > svg.live-market-widget__chevron-icon {
            display: block !important;
            width: 11px !important;
            height: 11px !important;
            min-width: 11px !important;
            min-height: 11px !important;
            flex: 0 0 11px !important;
            margin-left: 1px !important;
            color: #0f172a !important;
            transform: none !important;
        }

        .live-market-widget__mobile-filter.is-open .live-market-widget__chevron-icon {
            transform: rotate(180deg) !important;
        }

        .live-market-widget__pulse-dot {
            top: -5px !important;
            right: -7px !important;
        }

        html.dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-filter:hover,
        html.dark .live-market-widget__mobile-filter:focus,
        html.dark .live-market-widget__mobile-filter:active,
        html.dark .live-market-widget__mobile-filter.is-open,
        html.dark .live-market-widget__mobile-date,
        html.dark .live-market-widget__chevron-icon,
        .dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter:hover,
        .dark .live-market-widget__mobile-filter:focus,
        .dark .live-market-widget__mobile-filter:active,
        .dark .live-market-widget__mobile-filter.is-open,
        .dark .live-market-widget__mobile-date,
        .dark .live-market-widget__chevron-icon {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: #f8fafc !important;
        }


        /* Yükleme efekti tamamen kaldırıldı: video, görsel ve yazılar normal görünür */
        .home-feed-shell.is-passive-loading,
        .home-feed-shell [data-og-skeleton],
        .home-feed-shell .is-passive-loading {
            animation: none !important;
            filter: none !important;
            opacity: 1 !important;
            color: inherit !important;
            background: inherit !important;
        }

        .home-feed-shell video,
        .home-feed-shell iframe,
        .home-feed-shell img {
            opacity: 1 !important;
            filter: none !important;
        }

    

        /* =========================================================
           OGRAFI FINAL DARK FIX
           Bugün yazısı + açılır menü + dark mode beyazlık temizleme
           Bu blok en sonda durmalı; önceki çakışan CSS kurallarını ezer.
        ========================================================= */
        .live-market-widget,
        .live-market-widget * {
            box-sizing: border-box !important;
        }

        .live-market-widget {
            overflow: visible !important;
        }

        .live-market-widget__mobile-panel {
            position: relative !important;
            overflow: visible !important;
        }

        .live-market-widget__mobile-filter {
            cursor: pointer !important;
            -webkit-tap-highlight-color: transparent !important;
        }

        .live-market-widget__mobile-date {
            display: inline-flex !important;
            opacity: 1 !important;
            visibility: visible !important;
            width: auto !important;
            height: auto !important;
            min-width: 0 !important;
            min-height: 0 !important;
            overflow: visible !important;
            clip: auto !important;
            white-space: nowrap !important;
            text-indent: 0 !important;
        }

        .live-market-widget__chevron-icon {
            display: inline-block !important;
            width: 15px !important;
            height: 15px !important;
            flex: 0 0 15px !important;
            transition: transform .16s ease !important;
        }

        .live-market-widget__mobile-filter.is-open .live-market-widget__chevron-icon,
        .live-market-widget__mobile-panel.is-open .live-market-widget__chevron-icon {
            transform: rotate(180deg) !important;
        }

        .live-market-widget__mobile-dropdown {
            position: absolute !important;
            top: calc(100% + 7px) !important;
            left: 0 !important;
            z-index: 999999 !important;
            width: 138px !important;
            min-width: 138px !important;
            max-width: 138px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 3px !important;
            padding: 7px !important;
            border-radius: 13px !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transform: translateY(-5px) !important;
            transition: opacity .14s ease, transform .14s ease, visibility .14s ease !important;
        }

        .live-market-widget__mobile-panel.is-open .live-market-widget__mobile-dropdown {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: translateY(0) !important;
        }

        .live-market-widget__mobile-option {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 8px !important;
            min-height: 30px !important;
            padding: 0 9px !important;
            border-radius: 10px !important;
            text-decoration: none !important;
            white-space: nowrap !important;
        }

        .live-market-widget__mobile-option-dot {
            width: 6px !important;
            height: 6px !important;
            flex: 0 0 6px !important;
            border-radius: 999px !important;
            background: transparent !important;
        }

        .live-market-widget__mobile-option.is-active .live-market-widget__mobile-option-dot {
            background: #2563eb !important;
        }

        html.dark .live-market-widget,
        .dark .live-market-widget,
        html.dark .home-feed-shell .live-market-widget,
        .dark .home-feed-shell .live-market-widget {
            background: transparent !important;
            background-color: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
        }

        html.dark .live-market-widget__mobile-panel,
        .dark .live-market-widget__mobile-panel,
        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-filter:hover,
        html.dark .live-market-widget__mobile-filter:focus,
        html.dark .live-market-widget__mobile-filter:active,
        html.dark .live-market-widget__mobile-filter.is-open,
        .dark .live-market-widget__mobile-filter:hover,
        .dark .live-market-widget__mobile-filter:focus,
        .dark .live-market-widget__mobile-filter:active,
        .dark .live-market-widget__mobile-filter.is-open,
        html.dark .live-market-widget__mobile-filter-content,
        .dark .live-market-widget__mobile-filter-content,
        html.dark .live-market-widget__mobile-date,
        .dark .live-market-widget__mobile-date {
            background: transparent !important;
            background-color: transparent !important;
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
        }

        html.dark .live-market-widget__mobile-filter,
        .dark .live-market-widget__mobile-filter,
        html.dark .live-market-widget__mobile-date,
        .dark .live-market-widget__mobile-date,
        html.dark .live-market-widget__mobile-filter-content,
        .dark .live-market-widget__mobile-filter-content,
        html.dark .live-market-widget__label,
        .dark .live-market-widget__label,
        html.dark .live-market-widget__value,
        .dark .live-market-widget__value,
        html.dark .live-market-widget__item,
        .dark .live-market-widget__item {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        html.dark .live-market-widget__chevron-icon,
        .dark .live-market-widget__chevron-icon,
        html.dark .live-market-widget__mobile-filter svg,
        .dark .live-market-widget__mobile-filter svg {
            color: #9ca3af !important;
            stroke: #9ca3af !important;
            fill: none !important;
            -webkit-text-fill-color: #9ca3af !important;
        }

        html.dark .live-market-widget__arrow.is-up,
        .dark .live-market-widget__arrow.is-up {
            border-bottom-color: #9ca3af !important;
        }

        html.dark .live-market-widget__arrow.is-down,
        .dark .live-market-widget__arrow.is-down {
            border-top-color: #9ca3af !important;
        }

        html.dark .live-market-widget__arrow.is-flat,
        .dark .live-market-widget__arrow.is-flat {
            background: #9ca3af !important;
            border-color: transparent !important;
        }

        html.dark .live-market-widget__mobile-dropdown,
        .dark .live-market-widget__mobile-dropdown {
            background: #0f172a !important;
            background-color: #0f172a !important;
            border: 1px solid #1f2937 !important;
            box-shadow: 0 18px 38px rgba(0, 0, 0, .36) !important;
        }

        html.dark .live-market-widget__mobile-option,
        .dark .live-market-widget__mobile-option {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            background: transparent !important;
        }

        html.dark .live-market-widget__mobile-option:hover,
        html.dark .live-market-widget__mobile-option:focus,
        html.dark .live-market-widget__mobile-option.is-active,
        .dark .live-market-widget__mobile-option:hover,
        .dark .live-market-widget__mobile-option:focus,
        .dark .live-market-widget__mobile-option.is-active {
            background: #1f2937 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            outline: none !important;
        }

        html.dark .live-market-widget__pulse-dot,
        .dark .live-market-widget__pulse-dot {
            background: #60a5fa !important;
            box-shadow: 0 0 0 0 rgba(96, 165, 250, .45) !important;
        }

        /* Layout header beyaz kalıyorsa bu sayfada koyu moda çek */
        html.dark body,
        .dark body {
            background-color: #070f1f !important;
        }

        html.dark header,
        html.dark .site-header,
        html.dark .app-header,
        html.dark .topbar,
        html.dark .navbar,
        html.dark .main-header,
        html.dark [data-site-header],
        .dark header,
        .dark .site-header,
        .dark .app-header,
        .dark .topbar,
        .dark .navbar,
        .dark .main-header,
        .dark [data-site-header] {
            background: #070f1f !important;
            background-color: #070f1f !important;
            color: #ffffff !important;
            border-color: #1f2937 !important;
        }

        html.dark header *,
        html.dark .site-header *,
        html.dark .app-header *,
        html.dark .topbar *,
        html.dark .navbar *,
        html.dark .main-header *,
        html.dark [data-site-header] *,
        .dark header *,
        .dark .site-header *,
        .dark .app-header *,
        .dark .topbar *,
        .dark .navbar *,
        .dark .main-header *,
        .dark [data-site-header] * {
            color: #ffffff !important;
        }

        @media (max-width: 640px) {
            html.dark .live-market-widget,
            .dark .live-market-widget {
                background: transparent !important;
                background-color: transparent !important;
            }

            html.dark .live-market-widget__mobile-filter,
            .dark .live-market-widget__mobile-filter {
                min-width: 86px !important;
                width: auto !important;
                height: 38px !important;
                color: #ffffff !important;
                -webkit-text-fill-color: #ffffff !important;
            }

            html.dark .live-market-widget__mobile-date,
            .dark .live-market-widget__mobile-date {
                font-size: 12px !important;
                font-weight: 400 !important;
                color: #ffffff !important;
                -webkit-text-fill-color: #ffffff !important;
            }
        }



        /* Üst borsa / Bugün alanı için dalgalı yüklenme efekti */
        @keyframes ografi-market-wave-loading {
            0% {
                background-position: 120% 0;
            }

            100% {
                background-position: -120% 0;
            }
        }

        .live-market-widget.is-market-loading .live-market-widget__mobile-filter,
        .live-market-widget.is-market-loading .live-market-widget__item {
            position: relative !important;
            overflow: hidden !important;
            pointer-events: none !important;
        }

        .live-market-widget.is-market-loading .live-market-widget__mobile-date,
        .live-market-widget.is-market-loading .live-market-widget__chevron-icon,
        .live-market-widget.is-market-loading .live-market-widget__label,
        .live-market-widget.is-market-loading .live-market-widget__value,
        .live-market-widget.is-market-loading .live-market-widget__arrow {
            opacity: 0 !important;
        }

        .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before,
        .live-market-widget.is-market-loading .live-market-widget__item::before,
        .live-market-widget.is-market-loading .live-market-widget__item::after {
            content: "" !important;
            position: absolute !important;
            display: block !important;
            border-radius: 999px !important;
            background: linear-gradient(90deg, #e5e7eb 0%, #f8fafc 48%, #e5e7eb 100%) !important;
            background-size: 220% 100% !important;
            animation: ografi-market-wave-loading 1.15s ease-in-out infinite !important;
        }

        .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before {
            top: 50% !important;
            left: 8px !important;
            width: 58px !important;
            height: 10px !important;
            transform: translateY(-50%) !important;
        }

        .live-market-widget.is-market-loading .live-market-widget__item::before {
            top: 2px !important;
            left: 0 !important;
            width: 64px !important;
            max-width: 78% !important;
            height: 8px !important;
        }

        .live-market-widget.is-market-loading .live-market-widget__item::after {
            top: 17px !important;
            left: 0 !important;
            width: 42px !important;
            height: 10px !important;
        }

        html.dark .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before,
        html.dark .live-market-widget.is-market-loading .live-market-widget__item::before,
        html.dark .live-market-widget.is-market-loading .live-market-widget__item::after,
        .dark .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before,
        .dark .live-market-widget.is-market-loading .live-market-widget__item::before,
        .dark .live-market-widget.is-market-loading .live-market-widget__item::after {
            background: linear-gradient(90deg, #1f2937 0%, #334155 48%, #1f2937 100%) !important;
            background-size: 220% 100% !important;
        }

        @media (min-width: 641px) {
            .live-market-widget.is-market-loading .live-market-widget__item::before {
                top: 50% !important;
                width: 72px !important;
                height: 9px !important;
                transform: translateY(-50%) !important;
            }

            .live-market-widget.is-market-loading .live-market-widget__item::after {
                display: none !important;
                content: none !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .live-market-widget.is-market-loading .live-market-widget__mobile-filter::before,
            .live-market-widget.is-market-loading .live-market-widget__item::before,
            .live-market-widget.is-market-loading .live-market-widget__item::after {
                animation: none !important;
            }
        }

        .ografi-feed-loadmore {
            margin: 16px 0 28px !important;
            padding: 0 !important;
        }

        .ografi-feed-loadmore__buttons {
            display: flex !important;
            justify-content: center !important;
        }

        .ografi-feed-page-button--prev,
        .ografi-feed-loadmore__count,
        .ografi-feed-page-button--next > span:not(.ografi-feed-page-button__icon):not(.ografi-feed-loadmore__spinner),
        .ografi-feed-loadmore__spinner {
            display: none !important;
        }

        .ografi-feed-page-button--next {
            width: 44px !important;
            height: 44px !important;
            min-width: 44px !important;
            min-height: 44px !important;
            padding: 0 !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            color: #111111 !important;
            box-shadow: none !important;
        }

        .ografi-feed-page-button--next:hover,
        .ografi-feed-page-button--next:focus,
        .ografi-feed-page-button--next:active,
        html.dark .ografi-feed-page-button--next,
        .dark .ografi-feed-page-button--next,
        html.dark .ografi-feed-page-button--next:hover,
        html.dark .ografi-feed-page-button--next:focus,
        html.dark .ografi-feed-page-button--next:active,
        .dark .ografi-feed-page-button--next:hover,
        .dark .ografi-feed-page-button--next:focus,
        .dark .ografi-feed-page-button--next:active {
            background: #ffffff !important;
            color: #111111 !important;
        }

        .ografi-feed-page-button__icon,
        .ografi-feed-page-button__icon svg {
            display: block !important;
            width: 18px !important;
            height: 18px !important;
        }

        .ografi-feed-page-button--next.is-loading {
            opacity: 0.65 !important;
        }
    </style>

    <script>

(() => {
            const widgets = document.querySelectorAll('[data-live-market-widget]');

            if (!widgets.length) {
                return;
            }

            if (window.__ografiLiveMarketWidgetStarted) {
                return;
            }

            window.__ografiLiveMarketWidgetStarted = true;

            const setMarketLoadingState = (isLoading) => {
                widgets.forEach((widget) => {
                    widget.classList.toggle('is-market-loading', isLoading);
                    widget.setAttribute('aria-busy', isLoading ? 'true' : 'false');
                });
            };

            let hasCompletedInitialMarketLoad = false;
            const minimumInitialMarketLoadingMs = 900;
            const waitForMarketSkeleton = (ms) => new Promise((resolve) => window.setTimeout(resolve, Math.max(0, ms)));

            setMarketLoadingState(true);

            const readStoredValue = (symbol) => {
                try {
                    const value = Number(localStorage.getItem(`ografi_market_${symbol}`));

                    return Number.isFinite(value) && value > 0 ? value : null;
                } catch (error) {
                    return null;
                }
            };

            const writeStoredValue = (symbol, value) => {
                try {
                    if (Number.isFinite(value) && value > 0) {
                        localStorage.setItem(`ografi_market_${symbol}`, String(value));
                    }
                } catch (error) {
                    return null;
                }
            };

            const previousValues = {
                usdtry: readStoredValue('usdtry'),
                eurtry: readStoredValue('eurtry'),
                btcusd: readStoredValue('btcusd'),
                goldtry: readStoredValue('goldtry'),
            };

            const formatTry = (value) => {
                if (!Number.isFinite(value)) {
                    return '-';
                }

                return value.toLocaleString('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            };

            const formatUsd = (value) => {
                if (!Number.isFinite(value)) {
                    return '-';
                }

                if (value >= 1000) {
                    return (value / 1000).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }) + 'K';
                }

                return value.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            };

            const setArrow = (arrow, currentValue, previousValue) => {
                arrow.classList.remove('is-up', 'is-down', 'is-flat');

                if (!Number.isFinite(previousValue) || currentValue === previousValue) {
                    arrow.classList.add('is-flat');
                    return 'flat';
                }

                if (currentValue > previousValue) {
                    arrow.classList.add('is-up');
                    return 'up';
                }

                arrow.classList.add('is-down');
                return 'down';
            };

            const updateItem = (symbol, value, formatter) => {
                document.querySelectorAll('[data-live-market-widget]').forEach((widget) => {
                    const item = widget.querySelector(`[data-symbol="${symbol}"]`);

                    if (!item) {
                        return;
                    }

                    const valueElement = item.querySelector('[data-value]');
                    const arrowElement = item.querySelector('[data-arrow]');

                    if (!valueElement || !arrowElement) {
                        return;
                    }

                    const oldValue = previousValues[symbol];

                    valueElement.textContent = formatter(value);

                    const direction = setArrow(arrowElement, value, oldValue);

                    item.classList.remove('is-market-up', 'is-market-down', 'is-market-flat');
                    item.classList.add(`is-market-${direction}`);

                    previousValues[symbol] = value;
                    writeStoredValue(symbol, value);
                });
            };

            const fetchJsonWithTimeout = async (url, timeoutMs = 9000) => {
                const controller = new AbortController();
                const timeout = window.setTimeout(() => controller.abort(), timeoutMs);

                try {
                    const separator = url.includes('?') ? '&' : '?';
                    const response = await fetch(`${url}${separator}_=${Date.now()}`, {
                        cache: 'no-store',
                        signal: controller.signal,
                        headers: {
                            Accept: 'application/json, text/plain, */*',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(`API yanıt vermedi: ${response.status}`);
                    }

                    return await response.json();
                } finally {
                    window.clearTimeout(timeout);
                }
            };

            const normalizeMarketNumber = (value) => {
                if (value === null || value === undefined) {
                    return null;
                }

                if (typeof value === 'number') {
                    return Number.isFinite(value) ? value : null;
                }

                let text = String(value)
                    .trim()
                    .replace(/₺|TL|TRY|USD|EUR|\$|€/gi, '')
                    .replace(/%/g, '')
                    .replace(/\s+/g, '');

                if (!text) {
                    return null;
                }

                const hasComma = text.includes(',');
                const hasDot = text.includes('.');

                if (hasComma && hasDot) {
                    text = text.replace(/\./g, '').replace(',', '.');
                } else if (hasComma) {
                    text = text.replace(',', '.');
                }

                const parsed = Number(text.replace(/[^0-9.-]/g, ''));

                return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
            };

            const findMarketEntry = (data, names) => {
                if (!data || typeof data !== 'object') {
                    return null;
                }

                const entries = Object.entries(data);
                const normalizedNames = names.map((name) => name.toLocaleLowerCase('tr-TR'));

                for (const [key, value] of entries) {
                    const normalizedKey = String(key).toLocaleLowerCase('tr-TR');

                    if (normalizedNames.some((name) => normalizedKey === name || normalizedKey.includes(name))) {
                        return value;
                    }
                }

                return null;
            };

            const readMarketEntryValue = (entry) => {
                if (!entry || typeof entry !== 'object') {
                    return normalizeMarketNumber(entry);
                }

                return normalizeMarketNumber(
                    entry.Satış
                    ?? entry.satış
                    ?? entry.satis
                    ?? entry.Satis
                    ?? entry.sell
                    ?? entry.Sell
                    ?? entry.selling
                    ?? entry.Selling
                    ?? entry.price
                    ?? entry.Price
                    ?? entry.value
                    ?? entry.Value
                    ?? entry.last
                    ?? entry.Last
                    ?? entry.Alış
                    ?? entry.alis
                    ?? entry.buy
                    ?? entry.Buy
                );
            };

            const fetchLocalTurkeyRates = async () => {
                const data = await fetchJsonWithTimeout('https://finans.truncgil.com/v4/today.json');

                const usdTry = readMarketEntryValue(findMarketEntry(data, ['USD', 'Amerikan Doları', 'Dolar']));
                const eurTry = readMarketEntryValue(findMarketEntry(data, ['EUR', 'Euro', 'Avro']));
                const gramGoldTry = readMarketEntryValue(findMarketEntry(data, ['Gram Altın', 'GRAMALTIN', 'Gram Altin', 'GA']));

                let updated = false;

                if (Number.isFinite(usdTry)) {
                    updateItem('usdtry', usdTry, formatTry);
                    updated = true;
                }

                if (Number.isFinite(eurTry)) {
                    updateItem('eurtry', eurTry, formatTry);
                    updated = true;
                }

                if (Number.isFinite(gramGoldTry)) {
                    updateItem('goldtry', gramGoldTry, formatTry);
                    updated = true;
                }

                if (!updated) {
                    throw new Error('Yerel piyasa verisi okunamadı.');
                }
            };

            const fetchCurrencyFallbackRates = async () => {
                const currencySources = [
                    async () => {
                        const data = await fetchJsonWithTimeout('https://open.er-api.com/v6/latest/USD');
                        const usdTry = normalizeMarketNumber(data?.rates?.TRY);
                        const eurUsd = normalizeMarketNumber(data?.rates?.EUR);
                        const eurTry = Number.isFinite(usdTry) && Number.isFinite(eurUsd) && eurUsd > 0
                            ? usdTry / eurUsd
                            : null;

                        return { usdTry, eurTry };
                    },
                    async () => {
                        const data = await fetchJsonWithTimeout('https://api.frankfurter.app/latest?from=USD&to=TRY,EUR');
                        const usdTry = normalizeMarketNumber(data?.rates?.TRY);
                        const eurUsd = normalizeMarketNumber(data?.rates?.EUR);
                        const eurTry = Number.isFinite(usdTry) && Number.isFinite(eurUsd) && eurUsd > 0
                            ? usdTry / eurUsd
                            : null;

                        return { usdTry, eurTry };
                    },
                ];

                let latestUsdTry = null;

                for (const source of currencySources) {
                    try {
                        const rates = await source();

                        if (Number.isFinite(rates?.usdTry)) {
                            latestUsdTry = rates.usdTry;
                            updateItem('usdtry', rates.usdTry, formatTry);
                        }

                        if (Number.isFinite(rates?.eurTry)) {
                            updateItem('eurtry', rates.eurTry, formatTry);
                        }

                        if (Number.isFinite(rates?.usdTry) || Number.isFinite(rates?.eurTry)) {
                            break;
                        }
                    } catch (error) {
                        // Bir sonraki döviz kaynağını dene.
                    }
                }

                if (!Number.isFinite(latestUsdTry)) {
                    latestUsdTry = readStoredValue('usdtry');
                }

                await fetchGoldTryRate(latestUsdTry);
            };

            const fetchGoldTryRate = async (usdTry) => {
                const goldSources = [
                    async () => {
                        const data = await fetchJsonWithTimeout('https://api.gold-api.com/price/XAU');

                        return normalizeMarketNumber(
                            data?.price
                            ?? data?.ask
                            ?? data?.bid
                            ?? data?.value
                        );
                    },
                    async () => {
                        const data = await fetchJsonWithTimeout('https://api.coingecko.com/api/v3/simple/price?ids=tether-gold,pax-gold&vs_currencies=usd');

                        return normalizeMarketNumber(data?.['tether-gold']?.usd)
                            ?? normalizeMarketNumber(data?.['pax-gold']?.usd);
                    },
                ];

                for (const source of goldSources) {
                    try {
                        const goldUsdPerOunce = await source();
                        const gramGoldTry = Number.isFinite(goldUsdPerOunce) && Number.isFinite(usdTry)
                            ? (goldUsdPerOunce * usdTry) / 31.1034768
                            : null;

                        if (Number.isFinite(gramGoldTry)) {
                            updateItem('goldtry', gramGoldTry, formatTry);
                            return;
                        }
                    } catch (error) {
                        // Bir sonraki altın kaynağını dene.
                    }
                }

                const oldGoldValue = readStoredValue('goldtry');

                if (Number.isFinite(oldGoldValue)) {
                    updateItem('goldtry', oldGoldValue, formatTry);
                }
            };

            const fetchCurrencyAndGoldRates = async () => {
                try {
                    await fetchLocalTurkeyRates();

                    if (!Number.isFinite(previousValues.goldtry)) {
                        await fetchGoldTryRate(previousValues.usdtry ?? readStoredValue('usdtry'));
                    }

                    return;
                } catch (error) {
                    await fetchCurrencyFallbackRates();
                }
            };

            const fetchCryptoRates = async () => {
                const cryptoSources = [
                    async () => {
                        const data = await fetchJsonWithTimeout('https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT');
                        return normalizeMarketNumber(data?.price);
                    },
                    async () => {
                        const data = await fetchJsonWithTimeout('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
                        return normalizeMarketNumber(data?.bitcoin?.usd);
                    },
                    async () => {
                        const data = await fetchJsonWithTimeout('https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD');
                        return normalizeMarketNumber(data?.USD);
                    },
                    async () => {
                        const data = await fetchJsonWithTimeout('https://blockchain.info/ticker');
                        return normalizeMarketNumber(data?.USD?.last);
                    },
                ];

                for (const source of cryptoSources) {
                    try {
                        const btcUsd = await source();

                        if (Number.isFinite(btcUsd)) {
                            updateItem('btcusd', btcUsd, formatUsd);
                            return;
                        }
                    } catch (error) {
                        // Bir sonraki BTC kaynağını dene.
                    }
                }

                const oldBtcValue = readStoredValue('btcusd');

                if (Number.isFinite(oldBtcValue)) {
                    updateItem('btcusd', oldBtcValue, formatUsd);
                }
            };

            const fetchServerMarketRates = async () => {
                const data = await fetchJsonWithTimeout(@json(route('borsa.ticker')));
                const rates = data?.rates ?? {};

                if (Number.isFinite(Number(rates.usdtry))) {
                    updateItem('usdtry', Number(rates.usdtry), formatTry);
                }

                if (Number.isFinite(Number(rates.eurtry))) {
                    updateItem('eurtry', Number(rates.eurtry), formatTry);
                }
            };

            const showStoredMarketValues = () => {
                if (Number.isFinite(previousValues.usdtry)) {
                    updateItem('usdtry', previousValues.usdtry, formatTry);
                }

                if (Number.isFinite(previousValues.eurtry)) {
                    updateItem('eurtry', previousValues.eurtry, formatTry);
                }

                if (Number.isFinite(previousValues.btcusd)) {
                    updateItem('btcusd', previousValues.btcusd, formatUsd);
                }

                if (Number.isFinite(previousValues.goldtry)) {
                    updateItem('goldtry', previousValues.goldtry, formatTry);
                }
            };

            const setMarketFallbackText = () => {
                document.querySelectorAll('[data-live-market-widget] [data-value]').forEach((valueElement) => {
                    if (valueElement.textContent.trim().toLocaleLowerCase('tr-TR') === 'yükleniyor') {
                        valueElement.textContent = '-';
                    }
                });
            };

            const loadMarketData = async () => {
                if (widgets.length === 0) {
                    return;
                }

                const isInitialLoad = !hasCompletedInitialMarketLoad;
                const initialMarketLoadingStartedAt = Date.now();

                if (isInitialLoad) {
                    setMarketLoadingState(true);
                }

                try {
                    showStoredMarketValues();

                    await fetchServerMarketRates().catch(() => {});

                    setMarketFallbackText();
                } finally {
                    if (isInitialLoad) {
                        const remainingSkeletonTime = minimumInitialMarketLoadingMs - (Date.now() - initialMarketLoadingStartedAt);

                        if (remainingSkeletonTime > 0) {
                            await waitForMarketSkeleton(remainingSkeletonTime);
                        }

                        hasCompletedInitialMarketLoad = true;
                        setMarketLoadingState(false);
                    }
                }
            };



            const feedFilterState = {
                active: @json($activeFeedTimeFilter),
                labels: @json($feedTimeFilters),
            };

            const getFilterLimit = (filter) => {
                const now = Date.now();
                const hour = 60 * 60 * 1000;
                const day = 24 * hour;

                if (filter === 'latest') {
                    return null;
                }

                if (filter === '24h') {
                    return now - day;
                }

                if (filter === 'week') {
                    return now - (7 * day);
                }

                if (filter === 'month') {
                    return now - (30 * day);
                }

                if (filter === 'year') {
                    return now - (365 * day);
                }

                return null;
            };

            const applyFeedFilter = (filter) => {
                const limit = getFilterLimit(filter);
                const posts = Array.from(document.querySelectorAll('[data-post-published]'));
                const emptyState = document.querySelector('[data-feed-filter-empty]');
                const label = document.querySelector('[data-feed-filter-label]');
                let visibleCount = 0;

                posts.forEach((post) => {
                    const published = Date.parse(post.getAttribute('data-post-published') || '');
                    const isVisible = !limit || (Number.isFinite(published) && published >= limit);

                    post.classList.toggle('is-filter-hidden', !isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                if (emptyState) {
                    emptyState.hidden = visibleCount > 0;
                }

                if (label) {
                    label.textContent = feedFilterState.labels?.[filter] || 'En son paylaşılanlar';
                }

                document.querySelectorAll('[data-feed-filter-option]').forEach((option) => {
                    option.classList.toggle('is-active', option.getAttribute('data-filter') === filter);
                });
            };

            const setupFeedFilterMenu = () => {
                const closeFeedFilterMenus = (exceptMenu = null) => {
                    document.querySelectorAll('[data-feed-filter-menu]').forEach((menu) => {
                        if (exceptMenu && menu === exceptMenu) {
                            return;
                        }

                        const toggle = menu.querySelector('[data-feed-filter-toggle]');

                        menu.classList.remove('is-open');

                        if (toggle) {
                            toggle.classList.remove('is-open');
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                };

                document.addEventListener('click', (event) => {
                    const toggle = event.target.closest('[data-feed-filter-toggle]');
                    const option = event.target.closest('[data-feed-filter-option]');

                    if (toggle) {
                        event.preventDefault();
                        event.stopPropagation();

                        const menu = toggle.closest('[data-feed-filter-menu]');

                        if (!menu) {
                            return;
                        }

                        const willOpen = !menu.classList.contains('is-open');

                        closeFeedFilterMenus(menu);

                        menu.classList.toggle('is-open', willOpen);
                        toggle.classList.toggle('is-open', willOpen);
                        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

                        return;
                    }

                    if (option) {
                        event.preventDefault();
                        event.stopPropagation();

                        const filter = option.getAttribute('data-filter') || 'latest';

                        feedFilterState.active = filter;
                        applyFeedFilter(filter);

                        const url = new URL(window.location.href);

                        if (filter === 'latest') {
                            url.searchParams.delete('feed_time');
                        } else {
                            url.searchParams.set('feed_time', filter);
                        }

                        url.searchParams.delete('page');
                        window.history.replaceState({}, '', url.toString());

                        closeFeedFilterMenus();

                        return;
                    }

                    if (!event.target.closest('[data-feed-filter-menu]')) {
                        closeFeedFilterMenus();
                    }
                });
            };

            setupFeedFilterMenu();
            document.addEventListener('click', (event) => {
                const mode = event.target.closest('[data-feed-mode]');
                if (!mode) return;

                document.querySelectorAll('[data-feed-mode]').forEach((button) => {
                    const active = button === mode;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-selected', active ? 'true' : 'false');
                });

                document.querySelectorAll('[data-feed-filter-menu]').forEach((period) => {
                    period.classList.toggle('is-mode-hidden', mode.dataset.feedMode !== 'read');
                });
            });
            applyFeedFilter(feedFilterState.active || 'latest');

            if (widgets.length > 0) {
                loadMarketData();
                window.__ografiLiveMarketInterval = setInterval(loadMarketData, 60000);
            }
        })();
    </script>
    <script>
        document.addEventListener('click', async function (event) {
            const button = event.target.closest('[data-load-more-button]');

            if (!button || button.getAttribute('rel') !== 'next') {
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

                const incoming = Array.from(doc.querySelectorAll('.ografi-filterable-post'))
                    .filter(function (node) {
                        const card = node.querySelector('[data-post-card-shell]');
                        const key = card?.id || card?.getAttribute('data-post-url') || '';
                        return card && (!key || !currentKeys.has(key));
                    });

                incoming.forEach(function (node) {
                    parent.insertBefore(node, controls);
                });

                const nextControls = Array.from(doc.querySelectorAll('.ografi-feed-loadmore')).find(function (node) {
                    return node.querySelector('[data-load-more-button][rel="next"]');
                });

                if (nextControls) {
                    controls.replaceWith(nextControls);
                } else {
                    controls.remove();
                }
            } catch (error) {
                window.location.href = button.href;
            }
        });
    </script>
<style>
/* ===== Mobil doviz alanını sağ kenardan içeri / sola alma düzeltmesi ===== */
@media (max-width: 640px) {
    .live-market-widget {
        justify-content: flex-start !important;
        gap: 8px !important;
        padding-left: 8px !important;
        padding-right: 10px !important;
    }

    .live-market-widget__mobile-panel {
        flex: 0 0 auto !important;
        margin-right: 4px !important;
    }

    .live-market-widget__track {
        flex: 1 1 auto !important;
        width: auto !important;
        min-width: 0 !important;
        justify-content: flex-start !important;
        margin-left: 0 !important;
        margin-right: auto !important;
        padding-left: 4px !important;
        padding-right: 14px !important;
        transform: translateX(-6px) !important;
        scroll-padding-left: 0 !important;
        scroll-padding-right: 16px !important;
    }

    .live-market-widget__item {
        flex: 0 0 auto !important;
    }
}

@media (max-width: 420px) {
    .live-market-widget {
        gap: 6px !important;
        padding-left: 6px !important;
        padding-right: 8px !important;
    }

    .live-market-widget__track {
        gap: 12px !important;
        padding-left: 2px !important;
        padding-right: 16px !important;
        transform: translateX(-8px) !important;
    }
}

/* Compact market row and more readable feed typography. */
@media (min-width: 641px) {
    .home-feed-shell .live-market-widget__track {
        width: max-content !important;
        max-width: 100% !important;
        gap: 10px !important;
        margin-left: auto !important;
        margin-right: 0 !important;
    }

    .home-feed-shell .live-market-widget__item {
        gap: 3px !important;
        font-size: 12px !important;
    }
}

.home-feed-shell [data-post-card-shell] .post-title,
.home-feed-shell [data-post-card-shell] .post-title__link {
    font-size: 22px !important;
    font-weight: 700 !important;
    line-height: 1.35 !important;
}

.home-feed-shell [data-post-card-shell] .post-summary,
.home-feed-shell [data-post-card-shell] .post-card__full-content,
.home-feed-shell [data-post-card-shell] .post-card__inline-text {
    font-size: 15.5px !important;
    line-height: 1.62 !important;
}

.home-feed-shell [data-post-card-shell] .post-card__tag,
.home-feed-shell [data-post-card-shell] .expand-link {
    font-size: 14.5px !important;
}

.home-feed-shell [data-post-card-shell] .author-name {
    font-size: 15px !important;
}

.home-feed-shell [data-post-card-shell] .author-subline,
.home-feed-shell [data-post-card-shell] .post-time {
    font-size: 13px !important;
}

/* Mobile post cards: full-width, readable typography and solid black actions. */
@media (max-width: 640px) {
    .home-feed-shell {
        width: 100% !important;
        max-width: 100% !important;
        padding-inline: 0 !important;
    }

    .home-feed-shell [data-post-card-shell] {
        width: 100% !important;
        max-width: 100% !important;
        margin-inline: 0 !important;
        padding: 14px 15px 12px !important;
        border-radius: 12px !important;
        box-sizing: border-box !important;
    }

    .home-feed-shell [data-post-card-shell] .post-header {
        margin-bottom: 10px !important;
    }

    .home-feed-shell [data-post-card-shell] .author-name {
        font-size: 14px !important;
        font-weight: 600 !important;
        color: #111 !important;
    }

    .home-feed-shell [data-post-card-shell] .author-subline,
    .home-feed-shell [data-post-card-shell] .post-time {
        font-size: 12px !important;
        line-height: 1.35 !important;
        color: #4b5563 !important;
    }

    .home-feed-shell [data-post-card-shell] .post-title,
    .home-feed-shell [data-post-card-shell] .post-title__link {
        font-size: 19px !important;
        font-weight: 700 !important;
        line-height: 1.32 !important;
        letter-spacing: -.01em !important;
        color: #050505 !important;
    }

    .home-feed-shell [data-post-card-shell] .post-summary,
    .home-feed-shell [data-post-card-shell] .post-card__full-content,
    .home-feed-shell [data-post-card-shell] .post-card__inline-text {
        font-size: 15px !important;
        line-height: 1.5 !important;
        color: #111 !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__media-wrap,
    .home-feed-shell [data-post-card-shell] .post-card__media-scroller,
    .home-feed-shell [data-post-card-shell] .post-card__media-frame {
        width: 100% !important;
        max-width: 100% !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__media-image,
    .home-feed-shell [data-post-card-shell] .hero-image {
        width: 100% !important;
        min-height: 210px !important;
        max-height: 430px !important;
        object-fit: cover !important;
        border-radius: 10px !important;
    }

    .home-feed-shell [data-post-card-shell] .action-bar {
        min-height: 48px !important;
        padding-top: 9px !important;
        margin-top: 10px !important;
        border-top: 1px solid #e5e7eb !important;
    }

    .home-feed-shell [data-post-card-shell] :where(.action-btn, .post-metric, .action-chip, .post-card__inline-icon),
    .home-feed-shell [data-post-card-shell] :where(.action-btn, .post-metric, .action-chip, .post-card__inline-icon) * {
        color: #050505 !important;
        stroke: currentColor !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__inline-icon {
        width: 24px !important;
        height: 24px !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__comment-icon,
    .home-feed-shell [data-post-card-shell] .post-card__bookmark-icon {
        width: 23px !important;
        height: 23px !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__view-icon {
        width: 20px !important;
        height: 20px !important;
    }

    html body .home-feed-shell,
    html body .home-feed-shell .ografi-filterable-post {
        width: 100vw !important;
        min-width: 0 !important;
        max-width: 100vw !important;
        overflow-x: hidden !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] {
        width: 100vw !important;
        min-width: 0 !important;
        max-width: 100vw !important;
        margin-right: calc(50% - 50vw) !important;
        margin-left: calc(50% - 50vw) !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] :is(
        .post-header,
        .post-title,
        .post-summary-shell,
        .post-card__full-content,
        .post-card__tags,
        .reactions-row,
        .comment-row
    ) {
        width: calc(100vw - 32px) !important;
        min-width: 0 !important;
        max-width: calc(100vw - 32px) !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] :is(
        .post-card__media-wrap,
        .post-card__media-scroller,
        .post-card__media-slide,
        .post-card__media-link,
        .post-card__media-frame,
        .post-card__media-image
    ) {
        width: calc(100vw - 32px) !important;
        min-width: calc(100vw - 32px) !important;
        max-width: calc(100vw - 32px) !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] :is(.post-title, .post-title__link, .post-summary, .post-card__tag) {
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: normal !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] .action-bar {
        width: 100vw !important;
        min-width: 100vw !important;
        max-width: 100vw !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] .action-left {
        gap: clamp(0px, .75vw, 3px) !important;
        padding-left: 8px !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] .action-left :is(
        .action-btn,
        .post-card__action-link,
        .post-card__action-button,
        .action-chip
    ) {
        width: 36px !important;
        min-width: 36px !important;
        height: 36px !important;
        min-height: 36px !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        border-radius: 50% !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] .action-left :is(
        .action-btn,
        .post-card__action-link,
        .post-card__action-button,
        .action-chip
    ):has(.action-chip__label) {
        width: auto !important;
        min-width: 48px !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
        border-radius: 999px !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] :is(
        .post-title,
        .post-title__link
    ) {
        font-family: Roboto, Arial, sans-serif !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        line-height: 1.42 !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] :is(
        .post-summary,
        .post-summary.is-collapsed,
        [data-post-card-summary]
    ) {
        font-family: Roboto, Arial, sans-serif !important;
        font-size: 17px !important;
        font-weight: 400 !important;
        line-height: 1.48 !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] .expand-link {
        font-size: 16px !important;
        font-weight: 600 !important;
        line-height: 22px !important;
    }

    html body.route-home .home-feed-shell article.post-card.post-card[data-post-card-shell] h2.post-title,
    html body.route-home .home-feed-shell article.post-card.post-card[data-post-card-shell] h2.post-title > a.post-title__link {
        font-family: Roboto, Arial, sans-serif !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        line-height: 1.42 !important;
    }

    html body.route-home .home-feed-shell article.post-card.post-card[data-post-card-shell] .post-summary-shell .post-summary,
    html body.route-home .home-feed-shell article.post-card.post-card[data-post-card-shell] .post-summary-shell .post-summary.is-collapsed {
        font-family: Roboto, Arial, sans-serif !important;
        font-size: 17px !important;
        font-weight: 400 !important;
        line-height: 1.48 !important;
    }

    html body.route-home .home-feed-shell article.post-card.post-card[data-post-card-shell] a.expand-link,
    html body.route-home .home-feed-shell article.post-card.post-card[data-post-card-shell] button.expand-link {
        font-size: 16px !important;
        font-weight: 600 !important;
        line-height: 22px !important;
    }

    /* Reference mobile card: dense editorial layout, without a vote row. */
    .home-feed-shell [data-post-card-shell] {
        padding: 10px 15px 0 !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        background: #fff !important;
        box-shadow: none !important;
        overflow: hidden !important;
    }

    .home-feed-shell [data-post-card-shell] .post-header {
        min-height: 38px !important;
        margin: 0 0 5px !important;
        align-items: flex-start !important;
    }

    .home-feed-shell [data-post-card-shell] .author-avatar {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
    }

    .home-feed-shell [data-post-card-shell] .author-block {
        gap: 7px !important;
    }

    .home-feed-shell [data-post-card-shell] .author-name {
        font-size: 13px !important;
        line-height: 17px !important;
        font-weight: 600 !important;
    }

    .home-feed-shell [data-post-card-shell] .author-subline,
    .home-feed-shell [data-post-card-shell] .post-time,
    .home-feed-shell [data-post-card-shell] .author-subline__topic {
        font-size: 11px !important;
        line-height: 15px !important;
    }

    .home-feed-shell [data-post-card-shell] .post-title,
    .home-feed-shell [data-post-card-shell] .post-title__link {
        margin: 0 0 18px !important;
        font-family: Roboto, Arial, sans-serif !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        line-height: 1.42 !important;
        letter-spacing: 0 !important;
        color: #050505 !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__media-wrap {
        margin: 0 0 17px !important;
        border: 0 !important;
        border-radius: 8px !important;
        overflow: hidden !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__media-frame,
    .home-feed-shell [data-post-card-shell] .post-card__media-image,
    .home-feed-shell [data-post-card-shell] .hero-image {
        display: block !important;
        width: 100% !important;
        min-height: 0 !important;
        height: auto !important;
        max-height: none !important;
        aspect-ratio: 1.5 / 1 !important;
        object-fit: cover !important;
        border-radius: 8px !important;
    }

    .home-feed-shell [data-post-card-shell] .post-summary-shell,
    .home-feed-shell [data-post-card-shell] .post-summary-shell.is-collapsed {
        max-height: none !important;
        margin: 0 0 3px !important;
        overflow: visible !important;
    }

    .home-feed-shell [data-post-card-shell] .post-summary,
    .home-feed-shell [data-post-card-shell] .post-summary.is-collapsed,
    .home-feed-shell [data-post-card-shell] [data-post-card-summary] {
        display: -webkit-box !important;
        margin: 0 !important;
        overflow: hidden !important;
        -webkit-box-orient: vertical !important;
        -webkit-line-clamp: 6 !important;
        font-family: Roboto, Arial, sans-serif !important;
        font-size: 17px !important;
        font-weight: 400 !important;
        line-height: 1.48 !important;
        color: #111 !important;
    }

    .home-feed-shell [data-post-card-shell] .expand-link {
        margin: 4px 0 14px !important;
        padding: 0 !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        line-height: 20px !important;
        color: #009b55 !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__tags {
        gap: 8px 12px !important;
        margin: 0 0 17px !important;
    }

    .home-feed-shell [data-post-card-shell] .post-card__tag {
        font-size: 13px !important;
        line-height: 20px !important;
        color: #009b55 !important;
    }

    .home-feed-shell [data-post-card-shell] .reactions-row {
        min-height: 35px !important;
        margin: 0 0 6px !important;
        gap: 7px !important;
    }

    .home-feed-shell [data-post-card-shell] .action-bar {
        min-height: 48px !important;
        margin: 0 -15px !important;
        padding: 0 8px !important;
        border-top: 1px solid #e5e7eb !important;
    }
}

@media (min-width: 1440px) {
    html body .home-feed-shell article.post-card[data-post-card-shell] :is(.post-title, .post-title__link):not(#comments *):not(#app *) {
        font-size: 21px !important;
        font-weight: 700 !important;
        line-height: 1.38 !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] :is(.post-summary, [data-post-card-summary], .post-card__full-content, .post-card__inline-text):not(#comments *):not(#app *) {
        font-size: 17px !important;
        font-weight: 400 !important;
        line-height: 1.52 !important;
    }

    html body .home-feed-shell article.post-card[data-post-card-shell] .expand-link:not(#comments *):not(#app *) {
        font-size: 16px !important;
        font-weight: 600 !important;
        line-height: 24px !important;
    }
}

/* Final feed toolbar appearance: match the compact reference tabs. */
html body .home-feed-shell .home-feed-toolbar {
    min-height: 38px !important;
    padding: 3px 6px !important;
    border: 1px solid #d9dde3 !important;
    border-radius: 18px !important;
    background: #fff !important;
    box-shadow: none !important;
}

html body .home-feed-shell .home-feed-toolbar__modes {
    gap: 0 !important;
}

html body .home-feed-shell .home-feed-toolbar__mode {
    min-height: 32px !important;
    padding: 0 11px !important;
    border-radius: 13px !important;
    color: #73737c !important;
    font-size: 14px !important;
    font-weight: 600 !important;
}

html body .home-feed-shell .home-feed-toolbar__mode.is-active {
    background: #f1f1f3 !important;
    color: #050505 !important;
    font-weight: 700 !important;
}

@media (min-width: 641px) {
    html body .home-feed-shell .home-feed-toolbar {
        transform: translateX(-6px) !important;
    }
}

@media (max-width: 640px) {
    html body .home-feed-shell .home-feed-toolbar {
        width: 100% !important;
        min-height: 34px !important;
        padding: 2px 4px !important;
        margin: 0 !important;
        border-radius: 16px !important;
        transform: none !important;
    }

    html body .home-feed-shell .home-feed-toolbar__modes {
        gap: 0 !important;
    }

    html body .home-feed-shell .home-feed-toolbar__mode {
        min-height: 28px !important;
        padding: 0 10px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
    }

    html body .home-feed-shell .home-feed-toolbar__mode.is-active {
        font-weight: 700 !important;
    }
}
</style>
@endsection

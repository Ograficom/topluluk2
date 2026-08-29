@extends('layouts.app')

@section('title', __('site.drafts_page.title'))

@section('content')
    <style>
        .drafts-page {
            background: transparent !important;
        }

        .drafts-page-header {
            display: flex;
            align-items: center;
            min-height: 48px;
            gap: 10px;
            padding: 0 14px;
            border: 1px solid rgba(148, 163, 184, .28);
            border-radius: 999px;
            background: rgba(255, 255, 255, .86);
            box-shadow: none !important;
        }

        .drafts-page-header__back,
        .drafts-page-header__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #334155;
        }

        .drafts-page-header__back:hover,
        .drafts-page-header__action:hover {
            background: rgba(15, 23, 42, .045);
        }

        .drafts-page-header__title {
            min-width: 0;
            flex: 1 1 auto;
            font-size: 18px;
            font-weight: 600;
            line-height: 1;
            color: #111827;
        }

        .drafts-feed {
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .drafts-feed > * {
            box-shadow: none !important;
        }

        .drafts-feed > *:hover,
        .drafts-feed article:hover,
        .drafts-feed [class*="post-card"]:hover,
        .drafts-feed [data-post-card]:hover {
            box-shadow: none !important;
            transform: none !important;
        }

        html.dark .drafts-page-header,
        html[data-theme="dark"] .drafts-page-header,
        html[data-system-theme="dark"] .drafts-page-header {
            background: rgba(15, 23, 42, .78);
            border-color: rgba(255, 255, 255, .09);
        }

        html.dark .drafts-page-header__title,
        html.dark .drafts-page-header__back,
        html.dark .drafts-page-header__action,
        html[data-theme="dark"] .drafts-page-header__title,
        html[data-theme="dark"] .drafts-page-header__back,
        html[data-theme="dark"] .drafts-page-header__action,
        html[data-system-theme="dark"] .drafts-page-header__title,
        html[data-system-theme="dark"] .drafts-page-header__back,
        html[data-system-theme="dark"] .drafts-page-header__action {
            color: #f8fafc;
        }

        @media (max-width: 640px) {
            .drafts-page {
                margin-inline: -4px;
            }

            .drafts-page-header {
                min-height: 46px;
                padding: 0 10px;
            }

            .drafts-page-header__title {
                font-size: 17px;
            }
        }
    </style>

    <div class="drafts-page space-y-4">
        <header id="drafts" class="drafts-page-header" aria-label="{{ __('site.drafts_page.heading') }}">
            <a href="{{ url()->previous() }}" class="drafts-page-header__back" aria-label="Geri">
                <iconify-icon icon="lucide:arrow-left" class="text-[18px]"></iconify-icon>
            </a>

            <h1 class="drafts-page-header__title">{{ __('site.drafts_page.heading') }}</h1>

            <a href="{{ url('/search') }}" class="drafts-page-header__action" aria-label="Ara" title="Ara">
                <iconify-icon icon="lucide:search" class="text-[18px]"></iconify-icon>
            </a>
            <button type="button" class="drafts-page-header__action" aria-label="Bilgi" title="Taslaklar">
                <iconify-icon icon="lucide:info" class="text-[18px]"></iconify-icon>
            </button>
            <button type="button" class="drafts-page-header__action" aria-label="Filtreler" title="Filtreler">
                <iconify-icon icon="lucide:sliders-horizontal" class="text-[18px]"></iconify-icon>
            </button>
        </header>

        <section class="drafts-feed">
            @if($posts->isEmpty())
                <div class="alma-empty-state">
                    {{ __('site.drafts_page.empty') }}
                </div>
            @else
                @forelse($posts as $post)
                    @php
                        $featured = $post->featured_image_url
                            ?? $post->featured_image
                            ?? $post->cover_image
                            ?? null;

                        $reactionTypesAll = $reactionTypes ?? ($post->reactionTypes ?? collect());
                    @endphp
                    @include('blog.post-card', [
                        'post' => $post,
                        'title' => filled($post->title) ? $post->title : ('/' . ltrim((string) ($post->slug ?? ''), '/')),
                        'excerpt' => trim(strip_tags($post->excerpt ?? $post->content ?? '')),
                        'featuredImage' => $featured,
                        'createdAt' => $post->updated_at,
                        'authorName' => optional($post->author)->name ?? __('site.post.community_author'),
                        'authorAvatar' => optional($post->author)->profile_photo_url ?? null,
                        'reactions' => collect(),
                        'reactionTypes' => $reactionTypesAll,
                        'isHero' => $loop->first,
                    ])
                @empty
                    <div class="alma-empty-state">
                        {{ __('site.drafts_page.empty_posts') }}
                    </div>
                @endforelse

                @include('partials.feed-load-more', ['posts' => $posts])
            @endif
        </section>
    </div>
@endsection

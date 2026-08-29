@extends('layouts.app')

@section('title', __('site.drafts_page.title'))
@section('no_container_padding')
@endsection

@section('content')
    <style>
        body.alma-app:has(.drafts-page) main,
        body.alma-app:has(.drafts-page) main > div,
        body.alma-app:has(.drafts-page) .drafts-page,
        body.alma-app:has(.drafts-page) .drafts-feed {
            background: transparent !important;
            box-shadow: none !important;
        }

        .drafts-page {
            width: 100%;
            min-width: 0;
            background: transparent !important;
        }

        .drafts-page-header {
            display: flex;
            align-items: center;
            gap: 6px;
            width: 100%;
            min-height: 38px;
            padding: 3px 12px;
            border: 1px solid #d9dde3;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: none !important;
        }

        .drafts-page-header__back,
        .drafts-page-header__action {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 32px !important;
            height: 32px !important;
            flex: 0 0 32px;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 999px !important;
            background: transparent !important;
            color: #52525b !important;
            box-shadow: none !important;
            transition: background-color .15s ease, transform .08s ease-out;
        }

        .drafts-page-header__back {
            width: 26px !important;
            height: 26px !important;
            flex-basis: 26px;
            color: #334155 !important;
        }

        .drafts-page-header__back:hover,
        .drafts-page-header__action:hover,
        .drafts-page-header__back:focus-visible,
        .drafts-page-header__action:focus-visible {
            background: #f3f4f6 !important;
            outline: none;
        }

        .drafts-page-header__back:active,
        .drafts-page-header__action:active {
            transform: translateY(1px);
        }

        .drafts-page-header__divider {
            width: 1px;
            height: 16px;
            flex: 0 0 1px;
            background: #0f172a;
            opacity: .15;
        }

        .drafts-page-header__title {
            min-width: 0;
            flex: 1 1 auto;
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.2;
            color: #0f172a;
        }

        .drafts-page-header__actions {
            display: flex;
            align-items: center;
            gap: 2px;
            flex: 0 0 auto;
            margin-left: auto;
        }

        .drafts-feed {
            display: flex !important;
            flex-direction: column !important;
            gap: 20px !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .drafts-feed > * {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .drafts-feed > article,
        .drafts-feed > [class*="post-card"],
        .drafts-feed > [data-post-card] {
            margin: 0 !important;
            box-shadow: none !important;
            transition-property: border-color, background-color, color !important;
        }

        .drafts-feed > *:hover,
        .drafts-feed article:hover,
        .drafts-feed [class*="post-card"]:hover,
        .drafts-feed [data-post-card]:hover {
            box-shadow: none !important;
            transform: none !important;
            translate: none !important;
        }

        html.dark .drafts-page-header,
        html[data-theme="dark"] .drafts-page-header,
        html[data-system-theme="dark"] .drafts-page-header {
            background: #111827;
            border-color: rgba(255, 255, 255, .10);
        }

        html.dark .drafts-page-header__title,
        html[data-theme="dark"] .drafts-page-header__title,
        html[data-system-theme="dark"] .drafts-page-header__title {
            color: #f8fafc;
        }

        html.dark .drafts-page-header__divider,
        html[data-theme="dark"] .drafts-page-header__divider,
        html[data-system-theme="dark"] .drafts-page-header__divider {
            background: #ffffff;
        }

        html.dark .drafts-page-header__back,
        html.dark .drafts-page-header__action,
        html[data-theme="dark"] .drafts-page-header__back,
        html[data-theme="dark"] .drafts-page-header__action,
        html[data-system-theme="dark"] .drafts-page-header__back,
        html[data-system-theme="dark"] .drafts-page-header__action {
            color: #cbd5e1 !important;
        }

        html.dark .drafts-page-header__back:hover,
        html.dark .drafts-page-header__action:hover,
        html[data-theme="dark"] .drafts-page-header__back:hover,
        html[data-theme="dark"] .drafts-page-header__action:hover,
        html[data-system-theme="dark"] .drafts-page-header__back:hover,
        html[data-system-theme="dark"] .drafts-page-header__action:hover {
            background: #1e293b !important;
        }

        @media (max-width: 640px) {
            .drafts-page-header {
                min-height: 38px;
                padding: 3px 8px;
                border-radius: 18px;
            }

            .drafts-feed {
                gap: 14px !important;
            }
        }
    </style>

    <div class="drafts-page space-y-4">
        <header id="drafts" class="drafts-page-header" aria-label="{{ __('site.drafts_page.heading') }}">
            <a href="{{ url()->previous() }}" class="drafts-page-header__back" aria-label="Geri">
                <iconify-icon icon="lucide:arrow-left" class="text-[15px]"></iconify-icon>
            </a>
            <span class="drafts-page-header__divider" aria-hidden="true"></span>
            <h1 class="drafts-page-header__title">{{ __('site.drafts_page.heading') }}</h1>

            <div class="drafts-page-header__actions">
                <a href="{{ url('/search') }}" class="drafts-page-header__action" aria-label="Ara" title="Ara">
                    <iconify-icon icon="lucide:search" class="text-[16px]"></iconify-icon>
                </a>
                <button type="button" class="drafts-page-header__action" aria-label="Bilgi" title="Taslaklar">
                    <iconify-icon icon="lucide:info" class="text-[16px]"></iconify-icon>
                </button>
                <button type="button" class="drafts-page-header__action" aria-label="Filtreler" title="Filtreler">
                    <iconify-icon icon="lucide:sliders-horizontal" class="text-[16px]"></iconify-icon>
                </button>
            </div>
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

@extends('layouts.app')

@section('title', __('site.archive_page.title'))
@section('meta_description', __('site.archive_page.meta_description'))

@push('head')
<style>
    .page-title-identity {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 38px;
        padding: 3px 17px;
        border: 1px solid #d9dde3;
        border-radius: 18px;
        background: #ffffff;
        color: #050505;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        box-sizing: border-box;
        box-shadow: none;
    }

    html.dark .page-title-identity,
    .dark .page-title-identity {
        border-color: #27272a;
        background: #18181b;
        color: #fafafa;
    }

    .alma-archive-picker-card {
        border: 1px solid #e4e4e7;
        border-radius: 18px;
        background: #ffffff;
        padding: 16px;
    }

    html.dark .alma-archive-picker-card,
    .dark .alma-archive-picker-card {
        border-color: #27272a;
        background: #18181b;
    }

    .alma-archive-range-note {
        font-size: 13px;
        color: #5f6472;
    }

    html.dark .alma-archive-range-note,
    .dark .alma-archive-range-note {
        color: #a1a1aa;
    }

    @media (max-width: 640px) {
        .page-title-identity {
            width: 100vw;
            min-height: 34px;
            margin-right: calc(50% - 50vw);
            margin-left: calc(50% - 50vw);
            padding: 2px 14px;
            border-right: 0;
            border-left: 0;
            border-radius: 16px;
            font-size: 13px;
        }
    }
</style>
@endpush

@section('content')
    <div class="space-y-4">
        <section class="space-y-4">
            <h1 class="page-title-identity">{{ __('site.archive_page.heading') }}</h1>
            <p class="alma-archive-range-note">{{ __('site.archive_page.subtitle') }}</p>
        </section>

        <section class="alma-archive-picker-card">
            @livewire('archive-date-range-picker', [
                'from' => optional($archiveFrom)->toDateString(),
                'to' => optional($archiveTo)->toDateString(),
            ])

            <p class="alma-archive-range-note" style="margin-top: 12px;">
                @if ($archiveFrom && $archiveTo)
                    {{ __('site.archive_page.range_label', ['from' => $archiveFrom->translatedFormat('d M Y'), 'to' => $archiveTo->translatedFormat('d M Y')]) }}
                @else
                    {{ __('site.archive_page.no_range') }}
                @endif
            </p>
        </section>

        @include('partials.ads.slot', [
            'slotKey' => 'ads_feed_top',
        ])

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
                    if (!$type) return null;
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
            @endphp
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

            @include('partials.ads.feed-breaks', [
                'iteration' => $loop->iteration,
                'isLast' => $loop->last,
            ])
        @empty
            <div class="alma-empty-state">
                {{ __('site.archive_page.empty_posts') }}
            </div>
        @endforelse

        @include('partials.feed-load-more', ['posts' => $posts])
    </div>
@endsection

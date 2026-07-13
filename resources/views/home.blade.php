@extends('layouts.app')

@php
    $posts = $posts ?? collect();
    $reactionTypes = $reactionTypes ?? collect();
@endphp

@section('title', 'Ografi')
@section('hide_feed_header', '1')
@section('content')
    <div class="space-y-6">
        @include('partials.ads.slot', [
            'slotKey' => 'ads_feed_top',
        ])

        <div class="alma-tabs">
            <a href="{{ route('home') }}" class="alma-tab is-active">{{ __('New') }}</a>
            <a href="{{ route('discover') }}" class="alma-tab">{{ __('Active') }}</a>
            <a href="{{ route('blog.popular') }}" class="alma-tab">{{ __('Top') }}</a>
        </div>

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

            @empty
                <div class="alma-empty-state">
                    {{ __('There is nothing here yet') }}
                </div>
            @endforelse
        </div>
@endsection

@extends('layouts.app')

@section('title', __('site.drafts_page.title'))

@section('content')
    <div class="space-y-6">
        <header id="drafts" class="alma-page-header alma-page-header--compact-card">
            <h1 class="alma-page-title alma-page-title--compact-card">{{ __('site.drafts_page.heading') }}</h1>
        </header>

        <section class="alma-panel p-6">
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

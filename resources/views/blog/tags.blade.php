@extends('layouts.app')

@section('title', __('site.tags_page.title'))

@section('content')
    @php($themeTags = \App\Models\ThemeSetting::render('tags'))
    @if ($themeTags !== '')
        <div class="mb-4">
            {!! $themeTags !!}
        </div>
    @endif

    <div class="alma-panel p-6">
        <div class="alma-page-header mb-6">
            <p class="alma-page-kicker">{{ __('site.tags_page.kicker') }}</p>
            <h1 class="alma-page-title">{{ __('site.tags_page.title') }}</h1>
            <p class="alma-page-subtitle">{{ __('site.tags_page.subtitle') }}</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($tags as $tag)
                <a
                    href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
                    class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 transition hover:bg-white"
                >
                    <span class="font-semibold text-slate-900">#{{ $tag->name }}</span>
                    <span class="text-sm text-slate-500">{{ __('site.tags_page.posts_count', ['count' => $tag->posts_count]) }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-4 text-slate-600">
            {{ $tags->links() }}
        </div>
    </div>
@endsection

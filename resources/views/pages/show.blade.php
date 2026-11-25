@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: $page->title.' — OgraFi hakkında bilgiler, topluluk politikaları ve site içerikleri.')
@section('canonical', $page->canonical_url ?: route('pages.show', $page->slug))
@section('robots', ($page->robots_index ?? true) ? 'index,follow' : 'noindex,follow')
@section('og_type', 'article')
@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page->meta_title ?: $page->title,
            'description' => $page->meta_description ?: $page->title,
            'url' => $page->canonical_url ?: route('pages.show', $page->slug),
            'inLanguage' => 'tr-TR',
            'isPartOf' => ['@type' => 'WebSite', 'name' => $brandName, 'url' => route('posts.index')],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
@endpush
@if (filled($page->meta_keywords))
    @section('seo_keywords', $page->meta_keywords)
@endif
@if (filled($page->og_image))
    @section('seo_image', $page->og_image)
@endif

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/site-page-feedback.css') }}?v=1">
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/site-page-feedback.js') }}?v=1" defer></script>
@endpush

@section('content')
    <article class="card card-body">
        <h1>{{ $page->title }}</h1>

        <div class="editor">
            {!! $page->content !!}
        </div>

        <footer class="static__footer">
            <div class="static__counts">
                <span class="static__counts-item">
                    <x-repost-icon name="clock" size="14" />
                    <time datetime="{{ ($page->published_at ?? $page->created_at)->toAtomString() }}">{{ ($page->published_at ?? $page->created_at)->translatedFormat('d F Y') }}</time>
                </span>
                <span class="static__counts-item">
                    <x-repost-icon name="eye" size="14" />
                    {{ number_format($page->views ?? 0) }}
                </span>
            </div>
            <div class="site-feedback-actions">
            @if (auth()->check() && auth()->user()->is_admin)
                <a class="btn btn-sm btn-secondary" href="{{ \App\Filament\Resources\SitePages\SitePageResource::getUrl('edit', ['record' => $page]) }}">Düzenle</a>
            @endif
                @include('pages._feedback-trigger')
            </div>
        </footer>
        @include('pages._feedback-panel')
    </article>
@endsection

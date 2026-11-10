@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: $page->title.' — OgraFi hakkında bilgiler, topluluk politikaları ve site içerikleri.')
@section('canonical', $page->canonical_url ?: route('pages.show', $page->slug))
@section('robots', ($page->robots_index ?? true) ? 'index,follow' : 'noindex,follow')
@section('og_type', 'article')
@if (filled($page->meta_keywords))
    @section('seo_keywords', $page->meta_keywords)
@endif
@if (filled($page->og_image))
    @section('seo_image', $page->og_image)
@endif

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
            @if (auth()->check() && auth()->user()->is_admin)
                <a class="btn btn-sm btn-secondary" href="{{ \App\Filament\Resources\SitePages\SitePageResource::getUrl('edit', ['record' => $page]) }}">Düzenle</a>
            @endif
        </footer>
    </article>
@endsection

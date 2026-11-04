@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)

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
                <a class="btn btn-sm btn-secondary" href="{{ \\App\\Filament\\Resources\\SitePages\\SitePageResource::getUrl('edit', ['record' => $page]) }}">Düzenle</a>
            @endif
        </footer>
    </article>
@endsection

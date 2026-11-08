@extends('layouts.app')

@section('title', 'RSS akışı')
@section('description', 'OgraFi üzerinde takip ettiğiniz dış kaynaklardan derlenen RSS haberleri.')

@section('content')
    <section class="feed rss-reader">
        <header class="hero">
            <h1 class="h2 hero__title">RSS akışı</h1>
            <p class="color-gray">Takip ettiğiniz sitelerin en yeni yazıları tek yerde.</p>
            @if ($lastUpdatedAt)
                <p class="small color-gray">Son kontrol: {{ \Illuminate\Support\Carbon::parse($lastUpdatedAt)->format('d.m.Y H:i') }}</p>
            @endif
        </header>

        <nav class="rss-reader-sources" aria-label="RSS kaynak filtresi">
            <a href="{{ route('rss.reader') }}" @class(['is-active' => ! $selectedSource])>Tümü</a>
            @foreach ($sources as $source)
                <a href="{{ route('rss.reader', ['source' => $source->id]) }}" @class(['is-active' => (int) $selectedSource === $source->id])>{{ $source->name }}</a>
            @endforeach
        </nav>

        @forelse ($items as $item)
            <article class="card rss-reader-item">
                @if ($item->image_url)
                    <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer" class="rss-reader-image">
                        <img src="{{ $item->image_url }}" alt="" loading="lazy" referrerpolicy="no-referrer">
                    </a>
                @endif
                <div class="card-body">
                    <div class="rss-reader-meta">
                        <span>{{ $item->source->name }}</span>
                        @if ($item->author)<span>· {{ $item->author }}</span>@endif
                        <span>· {{ ($item->published_at ?? $item->created_at)->diffForHumans() }}</span>
                    </div>
                    <h2 class="h3 rss-reader-title"><a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer">{{ $item->title }}</a></h2>
                    @if ($item->summary)
                        <p class="color-text rss-reader-summary">{{ $item->summary }}</p>
                    @endif
                    <a class="link-accent rss-reader-open" href="{{ $item->link }}" target="_blank" rel="noopener noreferrer">Kaynağı oku <x-repost-icon name="arrow-right" size="14" /></a>
                </div>
            </article>
        @empty
            <section class="card card-body empty-state">
                <h2 class="h4">Henüz RSS içeriği yok</h2>
                <p class="color-gray">Yönetim panelinden RSS kaynakları ekleyin. Kaynaklar eklendikten sonra içerikler burada görünür.</p>
            </section>
        @endforelse

        @include('layouts._pagination', ['paginator' => $items])
    </section>
@endsection

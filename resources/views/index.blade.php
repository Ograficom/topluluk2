@extends('layouts.repost')

@section('title', 'OgraFi — Topluluk akışı')
@section('meta_description', 'OgraFi topluluğunda güncel yazıları, fikirleri ve tartışmaları keşfet.')

@section('content')
    <div class="repost-feed-heading">
        <div>
            <span class="repost-eyebrow">TOPLULUK AKIŞI</span>
            <h1>Gündemden kopma.</h1>
            <p>Topluluğun paylaşımlarını keşfet, fikrini paylaş.</p>
        </div>
        @auth
            <a href="{{ route('blog.create') }}" class="btn btn-primary btn-rounded"><svg width="17" height="17"><use href="#rf-pen"/></svg> Gönderi oluştur</a>
        @else
            <a href="{{ route('register') }}" class="btn btn-primary btn-rounded">Topluluğa katıl</a>
        @endauth
    </div>
    <div class="repost-feed-tabs">
        <a href="{{ route('home') }}" class="is-active">En yeni</a>
        <a href="{{ route('discover') }}">Keşfet</a>
    </div>
    @forelse($posts as $post)
        @include('components.repost-post-card', ['post' => $post])
    @empty
        <section class="card card-body repost-empty-state">
            <h2>Henüz paylaşım yok</h2>
            <p>Toplulukta ilk gönderiyi sen paylaşabilirsin.</p>
            @auth
                <a href="{{ route('blog.create') }}" class="btn btn-primary btn-rounded">İlk gönderiyi oluştur</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-rounded">Üye ol</a>
            @endauth
        </section>
    @endforelse
    @if($posts instanceof \Illuminate\Contracts\Pagination\Paginator || $posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="repost-pagination">{{ $posts->links() }}</div>
    @endif
@endsection

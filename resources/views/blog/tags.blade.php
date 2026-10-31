@extends('layouts.repost')

@section('title', 'Etiketler — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">KEŞFET</span><h1>Etiketler</h1><p>İlgilendiğin konulara göre gönderileri bul.</p></div></div>
    <form method="GET" action="{{ route('blog.tags') }}" class="repost-sort-row">
        <input type="search" name="q" value="{{ $search }}" placeholder="Etiket ara">
        <select name="sort">
            <option value="popular" @selected($sort === 'popular')>Popüler</option>
            <option value="newest" @selected($sort === 'newest')>En yeni</option>
            <option value="oldest" @selected($sort === 'oldest')>En eski</option>
        </select>
        <button class="btn btn-secondary btn-rounded" type="submit">Filtrele</button>
    </form>
    <div class="repost-tag-directory">
        @forelse($tags as $tag)
            <a href="{{ route('search', ['q' => $tag->name]) }}" class="card card-body"><strong>#{{ $tag->name }}</strong><span class="meta">{{ number_format((int) $tag->posts_count) }} gönderi</span></a>
        @empty
            <div class="card card-body repost-empty-state">Etiket bulunamadı.</div>
        @endforelse
    </div>
    <div class="repost-pagination">{{ $tags->links() }}</div>
@endsection

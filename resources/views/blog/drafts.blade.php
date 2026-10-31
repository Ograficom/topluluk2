@extends('layouts.repost')

@section('title', 'Taslaklarım — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">İÇERİKLERİM</span><h1>Taslaklarım</h1><p>Henüz yayımlamadığın gönderiler.</p></div><a href="{{ route('blog.create') }}" class="btn btn-primary btn-rounded">Yeni gönderi</a></div>
    <div class="card repost-drafts-list">
        @forelse($posts as $post)
            <div class="repost-draft-row">
                <div><strong>{{ $post->title }}</strong><p class="meta">{{ $post->updated_at?->diffForHumans() }} · {{ $post->category?->name ?? 'Kategorisiz' }}</p></div>
                <a href="{{ route('blog.post.edit', $post->slug) }}" class="btn btn-secondary btn-rounded">Düzenle</a>
            </div>
        @empty
            <div class="repost-empty-state">Henüz taslağın yok.</div>
        @endforelse
    </div>
    <div class="repost-pagination">{{ $posts->links() }}</div>
@endsection

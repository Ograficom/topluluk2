@extends('layouts.app')

@section('title', 'Son yorumlar — OgraFi')
@section('content')
    <header class="repost-page-heading"><div><span class="repost-eyebrow">TOPLULUK</span><h1>Son yorumlar</h1><p>Gönderilerdeki yeni konuşmalar.</p></div></header>
    <section class="card card-body">
        @forelse(($comments ?? collect()) as $comment)
            <article class="repost-comment">
                <strong>{{ $comment->user?->name ?? $comment->author_name ?? 'Üye' }}</strong>
                <time class="meta">{{ $comment->created_at?->diffForHumans() }}</time>
                <p>{{ strip_tags($comment->content) }}</p>
                @if($comment->post)<a href="{{ route('blog.post', $comment->post->slug) }}">{{ $comment->post->title }}</a>@endif
            </article>
        @empty
            <p class="color-gray">Henüz yorum bulunmuyor.</p>
        @endforelse
    </section>
@endsection

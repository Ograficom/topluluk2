@extends('layouts.app')

@section('title', 'Son yorumlar')

@section('content')
    <header class="hero__header-main"><div><h1 class="h3">Son yorumlar</h1><p class="small color-gray">Toplulukta yapılan en yeni yorumlar.</p></div></header>
    @forelse ($comments as $comment)
        <article class="comment card card-body">
            <div class="user-box user-box-sm">
                <a class="avatar" href="{{ $comment->user ? route('profiles.show', $comment->user) : '#' }}"><img src="{{ $comment->user?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></a>
                <div class="user-box-content">
                    <strong class="name">{{ $comment->user?->displayName() ?? 'Silinmiş kullanıcı' }}</strong>
                    <time class="meta" datetime="{{ $comment->created_at->toAtomString() }}">{{ $comment->created_at->diffForHumans() }}</time>
                </div>
            </div>
            <div class="comment__content"><p class="comment__text">{{ $comment->body }}</p></div>
            @if ($comment->post)<div class="small color-gray">Yazı: <a class="link" href="{{ route('posts.show', $comment->post) }}#comments">{{ $comment->post->title }}</a></div>@endif
        </article>
    @empty
        <section class="card card-body empty-state"><h2 class="h4">Henüz yorum yok</h2></section>
    @endforelse
    {{ $comments->links() }}
@endsection

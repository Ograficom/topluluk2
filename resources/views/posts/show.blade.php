@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->excerpt)

@section('content')
    <div class="speedbar">
        <a href="{{ route('posts.index') }}">OgraFi</a>
        @if ($post->channel)
            / <a href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>
        @endif
        / <span>{{ $post->title }}</span>
    </div>

    <article class="card card-body">
        <header class="post__header">
            <div class="user-box">
                @if ($post->user)
                    <a href="{{ route('profiles.show', $post->user) }}" class="avatar">
                        <img src="{{ $post->user->avatarUrl() }}" alt="" width="40" height="40">
                    </a>
                @else
                    <span class="avatar">
                        <img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40">
                    </span>
                @endif
                <div class="user-box-content">
                    <span class="name">
                        @if ($post->user)
                            <a class="name-text" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->full_name ?: $post->user->name }}</a>
                        @else
                            <span class="name-text">Repost yazarı</span>
                        @endif
                        @if ($post->user?->hasVerifiedBadge())
                            <span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>
                        @endif
                    </span>
                    <div class="meta">
                        @if ($post->channel)
                            <a href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>
                        @endif
                        <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
        </header>

        @if ($post->coverImageUrl())
            <div class="post__media-full">
                <div class="post__media">
                    <a href="{{ $post->coverImageUrl() }}" data-fancybox="full-post">
                        <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->title }}" class="post__media-image">
                    </a>
                </div>
            </div>
        @endif

        <h1 class="post__title-full">{{ $post->title }}</h1>

        <section class="editor">
            @foreach (preg_split('/\R{2,}/', trim($post->body)) ?: [] as $paragraph)
                @if (filled($paragraph))
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @endif
            @endforeach
        </section>

        @if (filled($post->tags))
            <div class="post__tags post__tags-full">
                @foreach ($post->tags as $tag)
                    <a href="{{ route('posts.index', ['q' => $tag]) }}">{{ $tag }}</a>
                @endforeach
            </div>
        @endif

        <footer class="post__footer post__footer-full">
            <div class="post__footer-stats small color-gray">
                <span>{{ number_format($post->likes_count) }} beğeni</span>
                <span>{{ number_format($post->dislikes_count) }} beğenmeme</span>
                <span>{{ number_format($post->views) }} görüntülenme</span>
            </div>
            @if ($post->channel)
                <a class="link small" href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>
            @endif
        </footer>

        @include('posts._interactions', ['post' => $post])
    </article>

    <section class="card card-body" id="comments">
        <h2 class="h5">Yorumlar ({{ $post->comments_count }})</h2>
        @if ($errors->any())
            <div class="color-red" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @auth
            <form class="addcomment" action="{{ route('posts.comments.store', $post) }}" method="POST">
                @csrf
                <textarea id="comment-body" name="body" rows="8" maxlength="5000" placeholder="Yorum yaz" aria-label="Yorum yaz" required>{{ old('body') }}</textarea>
                <div>
                    <button class="btn btn-primary" type="submit">Yorum gönder</button>
                </div>
            </form>
        @else
            <p class="addcomments__guest"><a class="link" href="{{ route('login') }}">Yorum yapmak için giriş yapın.</a></p>
        @endauth

        <div class="post__comments">
            @forelse ($post->comments as $comment)
                <article class="comment">
                    <div class="user-box">
                        @if ($comment->user)
                            <a href="{{ route('profiles.show', $comment->user) }}" class="avatar">
                                <img src="{{ $comment->user->avatarUrl() }}" alt="" width="40" height="40">
                            </a>
                        @else
                            <span class="avatar">
                                <img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40">
                            </span>
                        @endif
                        <div class="user-box-content">
                            <span class="name">
                                @if ($comment->user)
                                    <a class="name-text" href="{{ route('profiles.show', $comment->user) }}">{{ $comment->user->full_name ?: $comment->user->name }}</a>
                                @else
                                    <span class="name-text">Repost kullanıcısı</span>
                                @endif
                                @if ($comment->user?->hasVerifiedBadge())
                                    <span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>
                                @endif
                            </span>
                            <div class="meta">
                                <time datetime="{{ $comment->created_at->toAtomString() }}">{{ $comment->created_at->diffForHumans() }}</time>
                            </div>
                        </div>
                    </div>
                    <div class="comment__content">
                        <div class="comment__text">{{ $comment->body }}</div>
                    </div>
                </article>
            @empty
                <p class="color-gray">Henüz yorum yok.</p>
            @endforelse
        </div>
    </section>
@endsection

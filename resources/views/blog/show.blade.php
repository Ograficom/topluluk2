@extends('layouts.app')

@section('title', $post->getSEOTitle() ?: $post->title)
@section('meta_description', $post->getSEODescription() ?: ($post->excerpt ?: 'OgraFi topluluk gönderisi.'))
@section('canonical_url', $post->getUrlForSEO())

@section('content')
    <article class="card post repost-post-detail">
        <section class="card-body">
            <nav class="repost-breadcrumb" aria-label="İçerik yolu"><a href="{{ route('home') }}">OgraFi</a>@if($post->category)<span>/</span><a href="{{ route('blog.category', $post->category->slug) }}">{{ $post->category->name }}</a>@endif</nav>
            <header class="post__header">
                <div class="user-box">
                    <a class="avatar" href="{{ route('users.show', $post->author->username) }}"><img src="{{ $post->author->profile_photo_url }}" alt="" width="40" height="40"></a>
                    <div class="user-box-content">
                        <a class="name" href="{{ route('users.show', $post->author->username) }}">{{ $post->author->name }}</a>
                        <div class="meta"><time datetime="{{ optional($post->published_at)->toIso8601String() }}">{{ optional($post->published_at ?? $post->created_at)->format('d.m.Y H:i') }}</time></div>
                    </div>
                </div>
                @auth
                    @if(auth()->id() === $post->author_id)
                        <a class="btn btn-secondary btn-sm btn-rounded" href="{{ route('blog.post.edit', $post->slug) }}">Düzenle</a>
                    @else
                        <a class="btn btn-secondary btn-sm btn-rounded" href="{{ route('blog.post.report.form', $post->slug) }}">Bildir</a>
                    @endif
                @endauth
            </header>
            <h1 class="repost-article-title">{{ $post->title }}</h1>
            @if($post->excerpt)<p class="repost-article-excerpt">{{ $post->excerpt }}</p>@endif
            @if($post->featured_image_url)
                <figure class="repost-article-cover"><img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}"></figure>
            @endif
            @if($post->tags->isNotEmpty())
                <div class="post__tags">@foreach($post->tags as $tag)<a href="{{ route('search', ['q' => $tag->name]) }}">#{{ $tag->name }}</a>@endforeach</div>
            @endif
            <div class="editor repost-article-content">{!! $post->content !!}</div>
        </section>
        <footer class="post__footer card-body">
            <div class="post__footer-stats small color-gray"><span>{{ number_format((int)$post->views_count) }} görüntülenme</span><span>{{ $post->comments->count() }} yorum</span></div>
            @auth
                <form method="POST" action="{{ route('blog.post.bookmark', $post->slug) }}">@csrf<button class="btn btn-secondary btn-sm btn-rounded" type="submit">{{ $isBookmarked ? 'Kaydedildi' : 'Kaydet' }}</button></form>
            @endauth
        </footer>
        @if(isset($reactionTypes) && $reactionTypes->isNotEmpty())
            <div class="post__options card-body">
                <div class="post__options-set">
                    @foreach($reactionTypes as $reactionType)
                        <form method="POST" action="{{ route('blog.post.reaction', $post->slug) }}">@csrf<input type="hidden" name="reaction_type_id" value="{{ $reactionType->id }}"><button class="btn btn-md btn-primary-soft" type="submit">{{ $reactionType->emoji ?: $reactionType->label }}</button></form>
                    @endforeach
                </div>
            </div>
        @endif
    </article>

    <section class="card card-body repost-comments" id="comments">
        <header class="repost-section-heading"><h2 class="h5">Yorumlar <span class="color-gray">({{ $post->comments->count() }})</span></h2></header>
        @auth
            @if(!$post->comments_disabled)
                <form method="POST" action="{{ route('blog.post.comment', $post->slug) }}" class="repost-comment-form">
                    @csrf
                    <label for="comment-content">Yorumun</label>
                    <textarea id="comment-content" name="content" rows="4" required maxlength="10000" placeholder="Düşünceni paylaş...">{{ old('content') }}</textarea>
                    @error('content')<p class="color-red">{{ $message }}</p>@enderror
                    <button class="btn btn-primary btn-rounded" type="submit">Yorum gönder</button>
                </form>
            @endif
        @else
            <p class="color-gray"><a href="{{ route('login') }}">Giriş yap</a> ve sohbete katıl.</p>
        @endauth
        <div class="repost-comment-list">
            @forelse($post->comments as $comment)
                <article class="repost-comment">
                    <div class="user-box">
                        <span class="avatar"><img src="{{ $comment->user?->profile_photo_url ?? '' }}" alt="" width="36" height="36"></span>
                        <div class="user-box-content"><strong class="name">{{ $comment->user?->name ?? $comment->author_name ?? 'Üye' }}</strong><time class="meta">{{ $comment->created_at?->diffForHumans() }}</time></div>
                    </div>
                    <div class="repost-comment-content">{!! nl2br(e(strip_tags($comment->content))) !!}</div>
                </article>
            @empty
                <p class="color-gray">Henüz yorum yapılmamış. İlk yorumu sen yaz.</p>
            @endforelse
        </div>
    </section>

    @if(isset($recommendedPosts) && $recommendedPosts->isNotEmpty())
        <section class="feed repost-related-posts"><h2 class="h5">Bunlar da ilgini çekebilir</h2>
            @foreach($recommendedPosts as $recommendedPost)
                @include('partials.post-card', ['post' => $recommendedPost, 'reactionTypes' => $reactionTypes ?? collect()])
            @endforeach
        </section>
    @endif
@endsection

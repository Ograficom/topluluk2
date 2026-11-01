<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $post->meta_title ?: $post->title }} · OgraFi</title>
  <meta name="description" content="{{ $post->meta_description ?: $post->excerpt }}">
  <link rel="stylesheet" href="{{ asset('css/repost-kit.css') }}">
  <link rel="stylesheet" href="{{ asset('css/repost-theme.css') }}">
  <link rel="stylesheet" href="{{ asset('css/repost-ui.css') }}">
</head>
<body class="repost-ui">
<header class="site-header"><div class="site-header-shell">
  <a href="{{ route('home') }}" class="logo"><img src="{{ asset('images/ografi-logo.png') }}" alt="OgraFi" width="36" height="36"><span>OgraFi</span></a>
  <nav><a href="{{ route('home') }}">Ana sayfa</a><a href="{{ route('blog.categories') }}">Topluluklar</a><a href="{{ route('blog.tags') }}">Etiketler</a></nav>
  @auth<a href="{{ route('blog.create') }}">Gönderi oluştur</a>@else<a href="{{ route('login') }}">Giriş yap</a>@endauth
</div></header>
<main class="main"><div class="main__grid main__grid-full">
  <aside class="main__aside"><a href="{{ route('home') }}">Akış</a><a href="{{ route('blog.categories') }}">Topluluklar</a><a href="{{ route('blog.tags') }}">Etiketler</a></aside>
  <section class="feed">
    <article class="card post">
      <section class="card-body">
        <div class="meta">
          @if($post->category)<a href="{{ route('blog.category', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a>@endif
          <time datetime="{{ optional($post->published_at)->toIso8601String() }}">{{ optional($post->published_at ?? $post->created_at)->diffForHumans() }}</time>
        </div>
        <h1 class="h2">{{ $post->title }}</h1>
        <div class="user-box">
          <img class="avatar" src="{{ $post->author?->profile_photo_path ? \Illuminate\Support\Facades\Storage::url($post->author->profile_photo_path) : asset('images/ografi-logo.png') }}" alt="" width="44" height="44">
          <div class="user-box-content">
            <a class="name" href="{{ $post->author?->username ? route('users.show', ['user' => $post->author->username]) : route('users.index') }}">{{ $post->author?->name ?? 'Silinmiş kullanıcı' }}</a>
            <span class="meta">{{ $post->author?->username ? '@'.$post->author->username : '' }}</span>
          </div>
        </div>
        @if($post->featured_image)
          <img class="post__media-image" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy">
        @endif
        @if($post->excerpt)<p class="lead">{{ $post->excerpt }}</p>@endif
        <div class="post-content">{!! $post->content !!}</div>
        @if($post->tags->isNotEmpty())
          <div class="post__tags">@foreach($post->tags as $tag)<a href="{{ route('blog.index', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>@endforeach</div>
        @endif
      </section>
      <section class="card-body post__options">
        <div class="post__options-set">
          @foreach($reactionTypes as $reactionType)
            <form method="POST" action="{{ route('blog.post.reaction', ['post' => $post->slug]) }}">
              @csrf
              <input type="hidden" name="reaction_type_id" value="{{ $reactionType->id }}">
              <button class="btn btn-md btn-primary-soft" type="submit" title="{{ $reactionType->label }}">
                @if($reactionType->gif_url)<img src="{{ $reactionType->gif_url }}" alt="" width="22" height="22">@else{{ $reactionType->emoji }}@endif
                <span>{{ collect($reactionSummary)->firstWhere('short_code', $reactionType->short_code)['count'] ?? 0 }}</span>
              </button>
            </form>
          @endforeach
          <a class="btn btn-md btn-primary-soft" href="#comments">Yorumlar ({{ $post->comments->count() }})</a>
        </div>
        @auth
          <form method="POST" action="{{ route('blog.post.bookmark', ['post' => $post->slug]) }}">@csrf
            <button class="btn btn-md btn-square btn-primary-soft" type="submit">{{ $isBookmarked ? 'Kaydedildi' : 'Kaydet' }}</button>
          </form>
        @else
          <a class="btn btn-md btn-square btn-primary-soft" href="{{ route('login') }}">Kaydet</a>
        @endauth
      </section>
    </article>

    <section class="card card-body" id="comments">
      <h2 class="h4">Yorumlar ({{ $post->comments->count() }})</h2>
      @auth
        <form method="POST" action="{{ route('blog.post.comment', ['post' => $post->slug]) }}" class="addcomment">
          @csrf
          <textarea name="content" rows="4" required maxlength="10000" placeholder="Yorumunu yaz...">{{ old('content') }}</textarea>
          @error('content')<p class="text-danger">{{ $message }}</p>@enderror
          <button class="btn btn-primary" type="submit">Yorum gönder</button>
        </form>
      @else
        <p>Yorum yazmak için <a href="{{ route('login') }}">giriş yap</a>.</p>
      @endauth
      @forelse($post->comments as $comment)
        <article class="comment">
          <div class="user-box">
            <img class="avatar" src="{{ $comment->user?->profile_photo_path ? \Illuminate\Support\Facades\Storage::url($comment->user->profile_photo_path) : asset('images/ografi-logo.png') }}" alt="" width="36" height="36">
            <strong>{{ $comment->user?->name ?? $comment->author_name ?? 'Kullanıcı' }}</strong>
            <time class="meta">{{ $comment->created_at?->diffForHumans() }}</time>
          </div>
          <p>{{ $comment->content }}</p>
          @auth
            <div class="comment__options">
              <form method="POST" action="{{ route('blog.comment.like', ['comment' => $comment->id]) }}">@csrf<button type="submit">Beğen ({{ $comment->likes_count ?? 0 }})</button></form>
              <form method="POST" action="{{ route('blog.comment.dislike', ['comment' => $comment->id]) }}">@csrf<button type="submit">Beğenme</button></form>
            </div>
          @endauth
        </article>
      @empty
        <p class="color-gray">Henüz yorum yapılmamış.</p>
      @endforelse
    </section>
    @if($recommendedPosts->isNotEmpty())
      <section class="card card-body"><h2 class="h4">Benzer gönderiler</h2>
        @foreach($recommendedPosts as $recommendedPost)
          <article class="entry-card"><a href="{{ route('blog.post', ['post' => $recommendedPost->slug]) }}">{{ $recommendedPost->title }}</a><span class="meta">{{ $recommendedPost->author?->name }}</span></article>
        @endforeach
      </section>
    @endif
  </section>
</div></main>
<script src="{{ asset('js/repost-ui.js') }}" defer></script>
</body></html>

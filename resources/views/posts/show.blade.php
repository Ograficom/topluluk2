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
                    <a href="{{ route('profiles.show', $post->user) }}" class="avatar"><img src="{{ $post->user->avatarUrl() }}" alt="" width="40" height="40"></a>
                @else
                    <span class="avatar"><img src="{{ asset('assets/img/noavatar.png') }}" alt="" width="40" height="40"></span>
                @endif
                <div class="user-box-content">
                    <span class="name">
                        @if ($post->user)
                            <a class="name-text" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->displayName() }}</a>
                        @else
                            <span class="name-text">Repost yazarı</span>
                        @endif
                        @if ($post->user?->hasVerifiedCompanyBadge())
                    <x-company-verified-badge size="14" />
                @endif
                @if ($post->user?->hasVerifiedBadge())<span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>@endif
                    </span>
                    <div class="meta">
                        @if ($post->channel)<a href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>@endif
                        <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <button class="btn btn-secondary-ghost btn-circle btn-xs" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Yazı menüsünü aç"><x-repost-icon name="more" size="16" /></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if (auth()->user()?->is($post->user) || auth()->user()?->is_admin)
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.edit', $post) }}">Yazıyı düzenle</a></li>
                    @endif
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="mailto:{{ config('mail.from.address') }}?subject={{ rawurlencode('Yazı bildirimi: '.$post->title) }}&amp;body={{ rawurlencode(route('posts.show', $post)) }}">Yazıyı bildir</a></li>
                </ul>
            </div>
        </header>

        @if ($post->coverImageUrl() || count($post->galleryImageUrls()) > 0 || filled($post->video_url))
            <div class="post__media-full">@include('posts._media', ['post' => $post])</div>
        @endif

        <h1 class="post__title-full">{{ $post->title }}</h1>
        <section class="editor">
            @foreach (preg_split('/\R{2,}/', trim($post->body)) ?: [] as $paragraph)
                @if (filled($paragraph))<p>{!! nl2br(e($paragraph)) !!}</p>@endif
            @endforeach
        </section>

        @if ($post->tagRecords->isNotEmpty())
            <div class="post__tags post__tags-full">
                @foreach ($post->tagRecords as $tag)
                    <a href="{{ route('tags.show', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
                @endforeach
            </div>
        @elseif ($post->tags)
            <div class="post__tags post__tags-full">
                @foreach ($post->tags as $tag)
                    <a href="{{ route('tags.show', ['tag' => \Illuminate\Support\Str::slug($tag)]) }}">{{ $tag }}</a>
                @endforeach
            </div>
        @endif

        <footer class="post__footer post__footer-full">
            <div class="post__footer-stats small color-gray">
                <span>{{ number_format($post->likes_count) }} beğeni</span>
                <span>{{ number_format($post->views) }} görüntülenme</span>
            </div>
            @if ($post->channel)<a class="link small" href="{{ route('channels.show', $post->channel) }}">{{ $post->channel->name }}</a>@endif
        </footer>
        @include('posts._interactions', ['post' => $post])
    </article>

    <section class="card card-body" id="comments">
        <h2 class="h5">Yorumlar ({{ $post->comments_count }})</h2>
        @if ($errors->any())
            <div class="color-red" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
        @endif
        @auth
            <form class="addcomment" action="{{ route('posts.comments.store', $post) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $replyTo?->id }}">
                @if ($replyTo)
                    <p class="small color-gray">{{ $replyTo->user?->displayName() ?? 'Repost kullanıcısı' }} kullanıcısına yanıt yazıyorsunuz. <a class="link" href="{{ route('posts.show', $post) }}#comments">İptal</a></p>
                @endif
                <textarea id="comment-body" name="body" rows="8" maxlength="5000" placeholder="Yorum yaz" aria-label="Yorum yaz">{{ old('body') }}</textarea>
                <input id="comment-images" type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple hidden>
                <div><button class="btn-link link" type="button" data-comment-images-trigger>Resim yükle</button> <span class="small color-gray" data-comment-images-selection aria-live="polite"></span></div>
                <div><button class="btn btn-primary" type="submit">Yorum gönder</button></div>
            </form>
        @else
            <form class="addcomment" action="{{ route('login') }}" method="GET">
                <textarea id="comment-body" rows="8" placeholder="Yorum yazabilmek için giriş yapın" aria-label="Yorum yazabilmek için giriş yapın" disabled></textarea>
                <div><button class="btn btn-primary" type="submit">Giriş yap ve yorum yaz</button></div>
            </form>
        @endauth
        <div class="post__comments comments-tree-list">
            @forelse ($post->comments as $comment)
                @include('posts._comment', ['comment' => $comment, 'post' => $post])
            @empty
                <p class="color-gray">Henüz yorum yok.</p>
            @endforelse
        </div>
        @if (auth()->user()?->is_admin)
            <form class="mass_comments_action" id="comment-moderation-form" action="{{ route('posts.comments.moderate', $post) }}" method="POST">
                @csrf
                <label for="post-comments-action">Seçili yorumlar:</label>
                <select class="form-select" id="post-comments-action" name="action" required>
                    <option value="">— İşlem —</option>
                    <option value="delete">Sil</option>
                </select>
                <button class="btn btn-primary btn-md" type="submit">Uygula</button>
            </form>
        @endif
    </section>
@endsection

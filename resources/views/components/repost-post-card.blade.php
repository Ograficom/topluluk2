@props(['post'])

@php
    $postUrl = route('blog.post', $post->slug);
    $author = $post->author;
    $authorName = $author?->name ?? 'OgraFi üyesi';
    $authorInitial = mb_substr($authorName, 0, 1);
    $imageUrl = $post->featured_image_url ?? null;
    if (!$imageUrl && filled($post->featured_image ?? null)) {
        $storedImage = (string) $post->featured_image;
        $imageUrl = str_starts_with($storedImage, 'http://') || str_starts_with($storedImage, 'https://') || str_starts_with($storedImage, '/')
            ? $storedImage
            : \Illuminate\Support\Facades\Storage::url($storedImage);
    }
    $commentCount = (int) ($post->comments_count ?? 0);
    $viewCount = (int) ($post->views_count ?? $post->views ?? 0);
@endphp

<article class="card post repost-post-card">
    <section class="card-body">
        <header class="post__header">
            <div class="user-box">
                <a href="{{ $author?->username ? route('users.show', $author->username) : route('home') }}" class="avatar" aria-label="{{ $authorName }}">
                    @if($author?->profile_photo_url)
                        <img src="{{ $author->profile_photo_url }}" alt="" width="40" height="40" loading="lazy">
                    @else
                        <span class="repost-avatar-fallback">{{ $authorInitial }}</span>
                    @endif
                </a>
                <div class="user-box-content">
                    <a href="{{ $author?->username ? route('users.show', $author->username) : route('home') }}" class="name">{{ $authorName }}</a>
                    <div class="meta">
                        @if($post->category)
                            <a href="{{ route('blog.category', $post->category->slug) }}">{{ $post->category->name }}</a>
                            <span aria-hidden="true">·</span>
                        @endif
                        <time datetime="{{ optional($post->published_at ?? $post->created_at)->toIso8601String() }}">{{ optional($post->published_at ?? $post->created_at)->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
        </header>
        <div class="post__short">
            <h2 class="post__short-title h3"><a href="{{ $postUrl }}" class="color-title"><span class="post__short-title-text">{{ $post->title }}</span></a></h2>
            @if(filled($post->excerpt))
                <p class="repost-post-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt), 240) }}</p>
            @endif
            @if($post->tags?->isNotEmpty())
                <div class="post__tags">
                    @foreach($post->tags as $tag)
                        <a href="{{ route('search', ['q' => $tag->name]) }}">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    @if($imageUrl)
        <a href="{{ $postUrl }}" class="repost-post-image-link">
            <img src="{{ $imageUrl }}" alt="{{ $post->title }}" class="post__media-image" loading="lazy">
        </a>
    @endif
    <footer class="post__footer card-body">
        <div class="post__footer-stats small color-gray">
            <span><svg width="15" height="15"><use href="#rf-comment"/></svg> {{ number_format($commentCount) }} yorum</span>
            <span><svg width="15" height="15"><use href="#rf-eye"/></svg> {{ number_format($viewCount) }} görüntülenme</span>
        </div>
        <a href="{{ $postUrl }}" class="repost-read-more">Gönderiyi oku</a>
    </footer>
</article>

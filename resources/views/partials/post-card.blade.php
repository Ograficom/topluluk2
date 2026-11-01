@php
    $postAuthor = $post->author;
    $postCategory = $post->category;
    $postUrl = url('/tr/'.$post->slug);
    $postExcerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 220);
    $postReactionCounts = $post->reaction_counts ?? collect();
@endphp
<article class="card post post-card" data-post-card-shell>
    <section class="card-body">
        <header class="post__header">
            <div class="user-box">
                <a class="avatar" href="{{ $postAuthor ? url('/u/'.$postAuthor->username) : '#' }}">
                    @if($postAuthor)<img src="{{ $postAuthor->profile_photo_url }}" alt="{{ $postAuthor->name }}" width="40" height="40" loading="lazy">@else<span>O</span>@endif
                </a>
                <div class="user-box-content">
                    <a class="name" href="{{ $postAuthor ? url('/u/'.$postAuthor->username) : '#' }}">
                        <span class="name-text">{{ $postAuthor?->name ?? 'OgraFi üyesi' }}</span>
                        @if($postAuthor?->is_verified)<span class="verify" role="img" aria-label="Doğrulanmış hesap"><svg width="14" height="14"><use href="#verify"/></svg></span>@endif
                    </a>
                    <div class="meta">
                        @if($postCategory)<a href="{{ url('/Categorys/'.$postCategory->slug) }}">{{ $postCategory->name }}</a>@endif
                        <time datetime="{{ optional($post->published_at)->toIso8601String() }}">{{ optional($post->published_at ?? $post->created_at)->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
        </header>
        <div class="post__short">
            <h2 class="post__short-title h3"><a class="color-title" href="{{ $postUrl }}"><span class="post__short-title-text">{{ $post->title }}</span></a></h2>
            @if($postExcerpt)<div class="text-clamp-3 color-text">{{ $postExcerpt }}</div>@endif
            @if(is_array($post->link_preview ?? null) && filled($post->link_preview['url'] ?? null))
                @php($linkPreview = $post->link_preview)
                <a href="{{ $linkPreview['url'] }}" target="_blank" rel="nofollow noopener noreferrer" class="alma-link-preview" aria-label="{{ $linkPreview['title'] ?? $linkPreview['host'] ?? 'Bağlantıyı aç' }}">
                    @if(filled($linkPreview['image_url'] ?? null))
                        <div class="alma-link-preview__media">
                            <img src="{{ $linkPreview['image_url'] }}" alt="{{ $linkPreview['title'] ?? $linkPreview['host'] ?? 'Bağlantı önizlemesi' }}" loading="lazy" class="alma-link-preview__image">
                        </div>
                    @endif
                    <div class="alma-link-preview__body">
                        <div class="alma-link-preview__head">
                            <div class="min-w-0">
                                <span class="alma-link-preview__eyebrow">{{ $linkPreview['site_name'] ?: ($linkPreview['source_label'] ?? 'Kaynak') }}</span>
                                <span class="alma-link-preview__title">{{ $linkPreview['title'] ?: ($linkPreview['host'] ?? '') }}</span>
                            </div>
                        </div>
                        @if(filled($linkPreview['description'] ?? null))
                            <p class="alma-link-preview__description">{{ $linkPreview['description'] }}</p>
                        @endif
                        @if(filled($linkPreview['host'] ?? null))
                            <span class="alma-link-preview__host">{{ $linkPreview['host'] }}</span>
                        @endif
                    </div>
                </a>
            @endif
            @if($post->relationLoaded('tags') && $post->tags->isNotEmpty())
                <div class="post__tags">@foreach($post->tags as $tag)<a href="{{ url('/blog/tags?tag='.urlencode($tag->slug)) }}">{{ $tag->name }}</a>@endforeach</div>
            @endif
        </div>
    </section>

    @if($post->featured_image_url)
        <section class="card-body post__short-media">
            <a class="post__media" href="{{ $postUrl }}">
                <img class="post__media-image" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy">
            </a>
        </section>
    @endif

    <footer class="post__footer card-body">
        <div class="post__footer-stats small color-gray">
            <span>{{ number_format((int)($post->reactions_count ?? 0)) }} tepki</span>
            <span>{{ number_format((int)($post->views_count ?? 0)) }} görüntülenme</span>
            <span>{{ number_format((int)($post->comments_count ?? 0)) }} yorum</span>
        </div>
        <span class="small color-gray">{{ $postAuthor?->username ? '@'.$postAuthor->username : '' }}</span>
    </footer>

    <div class="post__options card-body">
        <div class="post__options-set">
            @if(isset($reactionTypes) && $reactionTypes->isNotEmpty())
                @auth
                    @foreach($reactionTypes->take(3) as $reactionType)
                        <form method="POST" action="{{ route('blog.post.reaction', $post->slug) }}">
                            @csrf
                            <input type="hidden" name="reaction_type_id" value="{{ $reactionType->id }}">
                            <button class="btn btn-md btn-square btn-primary-soft" type="submit" title="{{ $reactionType->label }}">
                                @if($reactionType->gif_url)
                                    <img src="{{ $reactionType->gif_url }}" alt="{{ $reactionType->label }}" width="20" height="20" loading="lazy">
                                @else
                                    <span>{{ $reactionType->emoji ?: '♥' }}</span>
                                @endif
                            </button>
                        </form>
                        <span class="post__options-count">{{ (int)($postReactionCounts[$reactionType->id] ?? 0) }}</span>
                    @endforeach
                @else
                    <a class="btn btn-md btn-primary-soft" href="{{ route('login') }}">Tepki vermek için giriş yap</a>
                @endauth
            @else
                <a class="btn btn-md btn-square btn-primary-soft" href="{{ $postUrl }}" title="Gönderiyi aç"><svg width="20" height="20"><use href="#like"/></svg></a>
            @endif
            <a class="btn btn-md btn-primary-soft" href="{{ $postUrl }}#comments" title="Yorumlar"><svg width="20" height="20"><use href="#comment"/></svg>{{ number_format((int)($post->comments_count ?? 0)) }}</a>
            <a class="btn btn-md btn-secondary-soft" href="{{ $postUrl }}" title="Gönderiyi görüntüle"><svg width="18" height="18"><use href="#eye"/></svg></a>
        </div>
        <div class="post__options-set">
            @auth
                <form method="POST" action="{{ url('/blog/posts/'.$post->slug.'/bookmark') }}">@csrf
                    <button class="btn btn-md btn-square btn-secondary-soft {{ $post->is_bookmarked ?? false ? 'is-active' : '' }}" type="submit" title="Kaydet"><svg width="19" height="19"><use href="#bookmark"/></svg></button>
                </form>
            @endauth
            <button class="btn btn-md btn-square btn-secondary-soft" type="button" data-copy-url="{{ $postUrl }}" title="Bağlantıyı kopyala"><svg width="19" height="19"><use href="#share"/></svg></button>
        </div>
    </div>
</article>

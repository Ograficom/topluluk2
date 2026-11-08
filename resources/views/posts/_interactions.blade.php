@php($viewerReaction = $post->relationLoaded('reactions') ? $post->reactions->first()?->type : null)
@php($netReactionCount = ($post->likes_count ?? 0) - ($post->dislikes_count ?? 0))
@php($isBookmarked = $post->getAttribute('is_bookmarked'))
@if ($isBookmarked === null && auth()->check())
    @php($isBookmarked = auth()->user()->bookmarks()->whereKey($post->id)->exists())
@endif

<div class="post__options">
    <div class="post__options-set">
        @auth
            <form action="{{ route('posts.reactions.store', ['post' => $post, 'type' => 'like']) }}" method="POST">
                @csrf
                <button class="btn btn-md btn-square btn-primary-soft {{ $viewerReaction === 'like' ? 'is-active' : '' }}" type="submit" title="Beğen" aria-label="Beğen" aria-pressed="{{ $viewerReaction === 'like' ? 'true' : 'false' }}">
                    <x-repost-icon name="like" size="20" />
                </button>
            </form>
        @else
            <a class="btn btn-md btn-square btn-primary-soft" href="{{ route('login') }}" title="Beğenmek için giriş yap" aria-label="Beğenmek için giriş yap">
                <x-repost-icon name="like" size="20" />
            </a>
        @endauth
        <span class="post__options-count">{{ $netReactionCount > 0 ? '+' : '' }}{{ number_format($netReactionCount) }}</span>

        @auth
            <form action="{{ route('posts.reactions.store', ['post' => $post, 'type' => 'dislike']) }}" method="POST">
                @csrf
                <button class="btn btn-md btn-square btn-primary-soft {{ $viewerReaction === 'dislike' ? 'is-active' : '' }}" type="submit" title="Beğenme" aria-label="Beğenme" aria-pressed="{{ $viewerReaction === 'dislike' ? 'true' : 'false' }}">
                    <x-repost-icon name="dislike" size="20" />
                </button>
            </form>
        @else
            <a class="btn btn-md btn-square btn-primary-soft" href="{{ route('login') }}" title="Beğenmemek için giriş yap" aria-label="Beğenmemek için giriş yap">
                <x-repost-icon name="dislike" size="20" />
            </a>
        @endauth
        <a class="btn btn-md btn-primary-soft" href="{{ route('posts.show', $post) }}#comments" title="Yorumlar" aria-label="Yorumlar">
            <x-repost-icon name="comment" size="20" />
            {{ number_format($post->comments_count ?? 0) }}
        </a>
        <button class="btn btn-md btn-square btn-primary-soft" type="button" data-share-post data-share-title="{{ $post->title }}" data-share-url="{{ route('posts.show', $post) }}" aria-label="Paylaş" title="Paylaş">
            <x-repost-icon name="share" size="20" />
        </button>
    </div>
    @auth
        <form action="{{ route('posts.bookmarks.toggle', $post) }}" method="POST">
            @csrf
            <button class="btn btn-md btn-square btn-primary-soft {{ $isBookmarked ? 'is-active' : '' }}" type="submit" title="{{ $isBookmarked ? 'Favorilerden çıkar' : 'Favorilere ekle' }}" aria-label="{{ $isBookmarked ? 'Favorilerden çıkar' : 'Favorilere ekle' }}" aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}">
                <x-repost-icon name="bookmark" size="20" />
            </button>
        </form>
    @else
        <a class="btn btn-md btn-square btn-primary-soft" href="{{ route('login') }}" title="Favorilere eklemek için giriş yap" aria-label="Favorilere eklemek için giriş yap"><x-repost-icon name="bookmark" size="20" /></a>
    @endauth
</div>

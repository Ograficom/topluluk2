@php($viewerReaction = $post->relationLoaded('reactions') ? $post->reactions->first()?->type : null)

<div class="post__options">
    <div class="post__options-set">
        @auth
            <form action="{{ route('posts.reactions.store', ['post' => $post, 'type' => 'like']) }}" method="POST">
                @csrf
                <button class="btn btn-md btn-square btn-primary-soft {{ $viewerReaction === 'like' ? 'is-active' : '' }}" type="submit" title="Like" aria-label="Like" aria-pressed="{{ $viewerReaction === 'like' ? 'true' : 'false' }}">
                    <x-repost-icon name="like" size="20" />
                </button>
            </form>
        @else
            <a class="btn btn-md btn-square btn-primary-soft" href="{{ route('login') }}" title="Sign in to like" aria-label="Sign in to like">
                <x-repost-icon name="like" size="20" />
            </a>
        @endauth
        <span class="post__options-count">{{ number_format($post->likes_count ?? 0) }}</span>

        @auth
            <form action="{{ route('posts.reactions.store', ['post' => $post, 'type' => 'dislike']) }}" method="POST">
                @csrf
                <button class="btn btn-md btn-square btn-primary-soft {{ $viewerReaction === 'dislike' ? 'is-active' : '' }}" type="submit" title="Dislike" aria-label="Dislike" aria-pressed="{{ $viewerReaction === 'dislike' ? 'true' : 'false' }}">
                    <x-repost-icon name="dislike" size="20" />
                </button>
            </form>
        @else
            <a class="btn btn-md btn-square btn-primary-soft" href="{{ route('login') }}" title="Sign in to dislike" aria-label="Sign in to dislike">
                <x-repost-icon name="dislike" size="20" />
            </a>
        @endauth
        <span class="post__options-count">{{ number_format($post->dislikes_count ?? 0) }}</span>

        <a class="btn btn-md btn-primary-soft" href="{{ route('posts.show', $post) }}#comments" title="Comments" aria-label="Comments">
            <x-repost-icon name="comment" size="20" />
            {{ number_format($post->comments_count ?? 0) }}
        </a>
    </div>
    <button class="btn btn-md btn-square btn-primary-soft" type="button" title="Favoriler henüz etkin değil" aria-label="Favorilere ekle" disabled>
        <x-repost-icon name="bookmark" size="20" />
    </button>
</div>

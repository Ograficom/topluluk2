@php($viewerReaction = $post->relationLoaded('reactions') ? $post->reactions->first()?->type : null)
@php($netReactionCount = ($post->likes_count ?? 0) - ($post->dislikes_count ?? 0))
@php($isBookmarked = $post->getAttribute('is_bookmarked'))
@if ($isBookmarked === null && auth()->check())
    @php($isBookmarked = auth()->user()->bookmarks()->whereKey($post->id)->exists())
@endif

<div class="post__options {{ $interactionContainerClass ?? '' }}">
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
        <div class="dropdown dropup dropdown-md post-share-dropdown">
            <button class="btn btn-md btn-square btn-primary-soft" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Paylaş" title="Paylaş">
                <x-repost-icon name="share" size="20" />
            </button>
            <ul class="dropdown-menu dropdown-menu-center post-share-menu">
                <li class="dropdown-menu-item dropdown-menu-item-sm">
                    <a href="https://t.me/share/url?url={{ urlencode(route('posts.show', $post)) }}&amp;text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        <span>Send to Telegram</span>
                    </a>
                </li>
                <li class="dropdown-menu-item dropdown-menu-item-sm">
                    <button type="button" data-copy-share-url="{{ route('posts.show', $post) }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.07 0l3-3A5 5 0 0 0 13 2.93l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.07 0l-3 3A5 5 0 0 0 11 21.07l1.71-1.71"/></svg>
                        <span>Copy link</span>
                    </button>
                </li>
            </ul>
        </div>
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

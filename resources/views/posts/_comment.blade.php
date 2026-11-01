<article class="comment" id="comment-{{ $comment->id }}">
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
        <div class="comment__text">{!! nl2br(e($comment->body)) !!}</div>
        <div class="comment__options">
            @auth
                <form class="comment__rating-box" action="{{ route('posts.comments.reactions.like', ['post' => $post, 'comment' => $comment]) }}" method="POST">
                    @csrf
                    <button class="comment__rating {{ ($comment->viewer_reacted ?? false) ? 'is-active' : '' }}" type="submit" aria-label="Yorumu beğen" aria-pressed="{{ ($comment->viewer_reacted ?? false) ? 'true' : 'false' }}">
                        <span class="comment__rating-icon"><x-repost-icon name="like" size="16" /></span>
                        <span class="comment__rating-count">{{ number_format($comment->reactions_count ?? 0) }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" aria-label="Yorumu beğenmek için giriş yapın">
                    <span class="comment__rating">
                        <span class="comment__rating-icon"><x-repost-icon name="like" size="16" /></span>
                        <span class="comment__rating-count">{{ number_format($comment->reactions_count ?? 0) }}</span>
                    </span>
                </a>
            @endauth

            @if (($comment->replies_count ?? 0) > 0)
                <a class="comment__replycount" href="#comment-{{ $comment->id }}">{{ $comment->replies_count }} yanıt</a>
            @endif

            @if (blank($comment->parent_id))
                <a class="comment__reply" href="{{ route('posts.show', ['post' => $post, 'reply_to' => $comment->id]) }}#comment-body">Yanıtla</a>
            @endif
        </div>
    </div>

    @if ($comment->replies->isNotEmpty())
        <div class="comment__children">
            @foreach ($comment->replies as $reply)
                @include('posts._comment', ['comment' => $reply, 'post' => $post])
            @endforeach
        </div>
    @endif
</article>

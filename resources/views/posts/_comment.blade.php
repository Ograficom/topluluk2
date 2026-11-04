<article class="comment {{ ($channelCommentCard ?? false) ? 'card card-body' : '' }}" id="comment-{{ $comment->id }}">
    @if (auth()->user()?->is_admin && ($allowModeration ?? true))
        <div class="comment__admin">
            <input class="form-check-input" type="checkbox" name="selected_comments[]" value="{{ $comment->id }}" form="{{ $moderationFormId ?? 'comment-moderation-form' }}" aria-label="{{ $comment->user?->displayName() ?? 'Kullanıcı' }} yorumunu seç">
        </div>
    @endif
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
                    <a class="name-text" href="{{ route('profiles.show', $comment->user) }}">{{ $comment->user->displayName() }}</a>
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
        @if (filled($comment->body))
            <div class="comment__text">{!! nl2br(e($comment->body)) !!}</div>
        @endif
        @if ($imageUrls = $comment->imageUrls())
            <ul class="comments-image-gallery">
                @foreach ($imageUrls as $imageUrl)
                    <li>
                        <a href="{{ $imageUrl }}" data-fancybox="post-comment-{{ $comment->id }}">
                            <img src="{{ $imageUrl }}" alt="Yorum eki" loading="lazy">
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        @if (($showPostLink ?? false) && $comment->post)
            <div class="comment__news">
                <span>Yazıda:</span>
                <a class="link" href="{{ route('posts.show', $comment->post) }}">{{ $comment->post->title }}</a>
            </div>
        @endif
        <div class="comment__options">
            @auth
                <form class="comment__rating-box" action="{{ route('posts.comments.reactions.like', ['post' => $post, 'comment' => $comment]) }}" method="POST">
                    @csrf
                    <button class="btn-link comment__rating {{ ($comment->viewer_reacted ?? false) ? 'is-active' : '' }}" type="submit" aria-label="Yorumu beğen" aria-pressed="{{ ($comment->viewer_reacted ?? false) ? 'true' : 'false' }}">
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

            <div class="dropdown dropup dropdown-sm">
                <button class="btn btn-secondary-ghost btn-circle btn-xs" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Yorum menüsünü aç">
                    <x-repost-icon name="more" size="14" class="color-gray" />
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-menu-item dropdown-menu-item-sm">
                        <a href="mailto:{{ config('mail.from.address') }}?subject={{ rawurlencode('Yorum bildirimi') }}&amp;body={{ rawurlencode(route('posts.show', $post).'#comment-'.$comment->id) }}">Report</a>
                    </li>
                    @if (auth()->user()?->is($comment->user) || auth()->user()?->is_admin)
                        <li class="dropdown-menu-item dropdown-menu-item-sm">
                            <a href="#comment-edit-{{ $comment->id }}" data-comment-edit-toggle="{{ $comment->id }}">Edit</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    @if (auth()->user()?->is($comment->user) || auth()->user()?->is_admin)
        <form
            id="comment-edit-{{ $comment->id }}"
            class="card card-body"
            action="{{ route('posts.comments.update', ['post' => $post, 'comment' => $comment]) }}"
            method="POST"
            hidden
            data-comment-edit-form
        >
            @csrf
            @method('PATCH')
            <label class="visually-hidden" for="comment-edit-body-{{ $comment->id }}">Edit comment</label>
            <textarea id="comment-edit-body-{{ $comment->id }}" name="body" rows="4" maxlength="5000" required>{{ $comment->body }}</textarea>
            @error('body')
                <p class="small color-red">{{ $message }}</p>
            @enderror
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-secondary btn-sm" type="button" data-comment-edit-cancel="{{ $comment->id }}">Cancel</button>
                <button class="btn btn-primary btn-sm" type="submit">Save</button>
            </div>
        </form>
    @endif

    @if ($comment->replies->isNotEmpty())
        <div class="comment__children">
            @foreach ($comment->replies as $reply)
                @include('posts._comment', ['comment' => $reply, 'post' => $post])
            @endforeach
        </div>
    @endif
</article>

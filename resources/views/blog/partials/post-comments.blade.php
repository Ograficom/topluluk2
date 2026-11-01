<div data-ografi-layout-section="comments" class="repost-comment-list">
    @forelse($post->comments as $comment)
        <article class="repost-comment">
            <div class="user-box">
                <span class="avatar"><img src="{{ $comment->user?->profile_photo_url ?? '' }}" alt="" width="36" height="36"></span>
                <div class="user-box-content"><strong class="name">{{ $comment->user?->name ?? $comment->author_name ?? 'Üye' }}</strong><time class="meta">{{ $comment->created_at?->diffForHumans() }}</time></div>
            </div>
            <div class="repost-comment-content">{!! nl2br(e(strip_tags($comment->content))) !!}</div>
            @auth
                <div class="repost-comment-actions">
                    <form method="POST" action="{{ route('blog.comment.like', $comment->id) }}">@csrf<button class="btn btn-secondary btn-xs" type="submit">Beğen · {{ (int)($comment->likes_count ?? 0) }}</button></form>
                    <form method="POST" action="{{ route('blog.comment.dislike', $comment->id) }}">@csrf<button class="btn btn-secondary btn-xs" type="submit">Beğenme · {{ (int)($comment->dislikes_count ?? 0) }}</button></form>
                </div>
            @else
                <span class="small color-gray">{{ (int)($comment->likes_count ?? 0) }} beğeni</span>
            @endauth
        </article>
    @empty
        <p class="color-gray">Henüz yorum yapılmamış. İlk yorumu sen yaz.</p>
    @endforelse
</div>

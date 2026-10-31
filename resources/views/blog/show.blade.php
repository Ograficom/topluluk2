@extends('layouts.repost')

@section('title', $post->meta_title ?: $post->title . ' — OgraFi')
@section('meta_description', $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 155)))
@section('canonical_url', route('blog.post', $post->slug))

@section('content')
    <article class="card repost-detail-card">
        <div class="card-body">
            <div class="repost-detail-breadcrumb">
                <a href="{{ route('home') }}">Ana akış</a>
                @if($post->category)
                    <span>/</span><a href="{{ route('blog.category', $post->category->slug) }}">{{ $post->category->name }}</a>
                @endif
            </div>
            <h1 class="repost-detail-title">{{ $post->title }}</h1>
            @if(filled($post->excerpt))
                <p class="repost-detail-excerpt">{{ $post->excerpt }}</p>
            @endif
            <div class="repost-detail-author">
                <span class="avatar">
                    @if($post->author?->profile_photo_url)
                        <img src="{{ $post->author->profile_photo_url }}" alt="" width="42" height="42">
                    @else
                        <span class="repost-avatar-fallback">{{ mb_substr($post->author?->name ?? 'O', 0, 1) }}</span>
                    @endif
                </span>
                <span>
                    @if($post->author?->username)
                        <a href="{{ route('users.show', $post->author->username) }}" class="name">{{ $post->author->name }}</a>
                    @else
                        <span class="name">OgraFi üyesi</span>
                    @endif
                    <time class="meta">{{ optional($post->published_at ?? $post->created_at)->diffForHumans() }}</time>
                </span>
                @auth
                    <form method="POST" action="{{ route('blog.post.bookmark', $post->slug) }}" class="repost-detail-bookmark">
                        @csrf
                        <button class="btn btn-secondary btn-rounded" type="submit">{{ $isBookmarked ? 'Kaydedildi' : 'Kaydet' }}</button>
                    </form>
                @endauth
            </div>
        </div>

        @if($post->featured_image_url)
            <img class="repost-detail-cover" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
        @endif

        <div class="card-body repost-detail-content">
            {!! $post->content !!}
        </div>

        @if($post->tags?->isNotEmpty())
            <div class="card-body post__tags">
                @foreach($post->tags as $tag)
                    <a href="{{ route('search', ['q' => $tag->name]) }}">#{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif

        @if(!empty($pollBlocks))
            <div class="card-body repost-polls">
                @foreach($pollBlocks as $poll)
                    <section class="repost-poll">
                        <h2>{{ $poll['question'] }}</h2>
                        <form method="POST" action="{{ route('blog.post.poll.vote', $post->slug) }}">
                            @csrf
                            <input type="hidden" name="block_id" value="{{ $poll['id'] }}">
                            @foreach($poll['options'] as $optionIndex => $option)
                                <label class="repost-poll-option">
                                    <input type="radio" name="option_index" value="{{ $optionIndex }}" @checked((string) ($poll['user_vote'] ?? '') === (string) $optionIndex) @disabled($poll['expired'] || $poll['user_vote'] !== null)>
                                    <span>{{ $option }}</span>
                                    @if($poll['total'] > 0)
                                        <small>{{ $poll['percentages'][$optionIndex] ?? 0 }}% · {{ $poll['counts'][$optionIndex] ?? 0 }}</small>
                                    @endif
                                </label>
                            @endforeach
                            @if(!$poll['expired'] && $poll['user_vote'] === null)
                                <button class="btn btn-primary btn-rounded" type="submit">Oy ver</button>
                            @else
                                <p class="meta">{{ number_format((int) $poll['total']) }} oy</p>
                            @endif
                        </form>
                    </section>
                @endforeach
            </div>
        @endif

        <div class="card-body repost-reactions">
            <h2>Bu gönderiye tepki ver</h2>
            <div class="repost-reaction-list">
                @foreach($reactionTypes as $reactionType)
                    @auth
                        <form method="POST" action="{{ route('blog.post.reaction', $post->slug) }}">
                            @csrf
                            <input type="hidden" name="reaction_type_id" value="{{ $reactionType->id }}">
                            <button type="submit" class="btn btn-secondary repost-reaction-button">
                                @if($reactionType->gif_url)
                                    <img src="{{ $reactionType->gif_url }}" alt="" loading="lazy">
                                @else
                                    <span>{{ $reactionType->emoji }}</span>
                                @endif
                                <span>{{ $reactionType->label }}</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary repost-reaction-button" title="Tepki vermek için giriş yap">
                            @if($reactionType->gif_url)
                                <img src="{{ $reactionType->gif_url }}" alt="" loading="lazy">
                            @else
                                <span>{{ $reactionType->emoji }}</span>
                            @endif
                            <span>{{ $reactionType->label }}</span>
                        </a>
                    @endauth
                @endforeach
            </div>
            @if(!empty($reactionSummary))
                <div class="repost-reaction-summary">
                    @foreach($reactionSummary as $reaction)
                        <span>{{ $reaction['emoji'] ?: $reaction['label'] }} {{ number_format((int) $reaction['count']) }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </article>

    <section class="repost-comments" id="comments">
        <h2>Yorumlar ({{ $post->comments->count() }})</h2>
        @auth
            <form method="POST" action="{{ route('blog.post.comment', $post->slug) }}" class="card card-body repost-comment-form">
                @csrf
                <label for="comment-content">Yorumun</label>
                <textarea id="comment-content" name="content" rows="4" required maxlength="5000" placeholder="Düşünceni paylaş...">{{ old('content') }}</textarea>
                @error('content')<p class="repost-form-error">{{ $message }}</p>@enderror
                <button class="btn btn-primary btn-rounded" type="submit">Yorum gönder</button>
            </form>
        @else
            <p class="card card-body">Yorum yazmak için <a href="{{ route('login') }}">giriş yap</a>.</p>
        @endauth

        @forelse($post->comments as $comment)
            <article class="card card-body repost-comment">
                <div class="user-box">
                    <span class="avatar"><span class="repost-avatar-fallback">{{ mb_substr($comment->user?->name ?? $comment->author_name ?? '?', 0, 1) }}</span></span>
                    <span class="user-box-content"><strong class="name">{{ $comment->user?->name ?? $comment->author_name ?? 'Ziyaretçi' }}</strong><time class="meta">{{ $comment->created_at?->diffForHumans() }}</time></span>
                </div>
                <p>{{ $comment->content }}</p>
            </article>
        @empty
            <p class="repost-empty-comments">İlk yorumu sen yaz.</p>
        @endforelse
    </section>
@endsection

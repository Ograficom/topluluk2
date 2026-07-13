{{-- Ografi post comments panel --}}

@php
    /*
     |--------------------------------------------------------------------------
     | Single file bootstrap
     |--------------------------------------------------------------------------
     | Bu dosya tek başına kullanılabilsin diye yorum koleksiyonu, gruplama,
     | sayaç, yazar id ve yorum metni/media render helper'ı burada hazırlanır.A
     */
    $commentsDisabled = $commentsDisabled ?? false;
    $ogxAllComments = isset($comments) ? collect($comments) : collect($post->comments ?? []);
    $commentsGrouped = $commentsGrouped ?? $ogxAllComments->groupBy('parent_id');
    $rootComments = $rootComments ?? $commentsGrouped->get(null, collect());
    $commentsCount = $commentsCount ?? $ogxAllComments->count();
    $ogxPostAuthorId = (int) ($post->author_id ?? $post->user_id ?? 0);
    $ogxGiphySearchUrl = \Illuminate\Support\Facades\Route::has('blog.giphy.search')
        ? route('blog.giphy.search')
        : null;

    $ogxMentionService = app(\App\Services\MentionService::class);
    $ogxRenderCommentText = static function ($comment) use ($ogxMentionService): string {
        $rawText = (string) ($comment->content ?? $comment->body ?? '');
        $tokenPattern = '/\[(gif|img):([^\]\s]+)\]/i';
        $segments = preg_split($tokenPattern, $rawText, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (!is_array($segments) || count($segments) <= 1) {
            return $ogxMentionService->linkifyPlainText($rawText);
        }

        $html = '';

        for ($i = 0; $i < count($segments); $i++) {
            if ($i % 3 === 0) {
                $html .= $ogxMentionService->linkifyPlainText((string) $segments[$i]);
                continue;
            }

            if ($i % 3 === 2) {
                $mediaType = strtolower((string) ($segments[$i - 1] ?? 'img'));
                $mediaRawUrl = trim((string) $segments[$i]);

                if ($mediaRawUrl !== ''
                    && !preg_match('/^(https?:)?\/\//i', $mediaRawUrl)
                    && !str_starts_with($mediaRawUrl, 'data:')
                ) {
                    $mediaRawUrl = ltrim($mediaRawUrl, '/');

                    if (str_starts_with($mediaRawUrl, 'storage/')) {
                        $mediaRawUrl = asset($mediaRawUrl);
                    } else {
                        $mediaRawUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($mediaRawUrl);
                    }
                }

                $mediaUrl = e($mediaRawUrl);
                $mediaClass = $mediaType === 'gif' ? ' ogx-comment-media--gif' : '';
                $mediaAlt = $mediaType === 'gif' ? 'Comment gif' : 'Comment media';

                $html .= '<div class="ogx-comment-media' . $mediaClass . '">';
                $html .= '<img src="' . $mediaUrl . '" alt="' . e($mediaAlt) . '" loading="lazy" decoding="async">';
                $html .= '</div>';
            }
        }

        return $html;
    };

    /*
     |--------------------------------------------------------------------------
     | SEO: Comment structured data
     |--------------------------------------------------------------------------
     | Bu blok yorumları arama motorlarının daha iyi anlaması için JSON-LD üretir.
     | Görünür yorum verisini kullanır; kullanıcıya gösterilmeyen sahte veri üretmez.
     */
    $ogxSeoBaseUrl = url()->current();
    $ogxSeoPostId = $ogxSeoBaseUrl . '#ografi-blog-post-' . (string) ($post->id ?? 'current');
    $ogxSeoCommentsEnabled = !($commentsDisabled ?? false) && isset($rootComments) && (int) ($commentsCount ?? 0) > 0;

    $ogxCleanSeoText = static function ($value, int $limit = 700): string {
        $value = (string) $value;
        $value = preg_replace('/\[(gif|img):([^\]\s]+)\]/i', ' ', $value);
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', trim($value));

        return \Illuminate\Support\Str::limit($value, $limit, '');
    };

    $ogxCommentToSchema = function ($comment) use (&$ogxCommentToSchema, $commentsGrouped, $ogxSeoBaseUrl, $ogxSeoPostId, $ogxCleanSeoText) {
        $commentText = $ogxCleanSeoText($comment->content ?? $comment->body ?? '', 700);

        if ($commentText === '') {
            return null;
        }

        $commentUser = $comment->user ?? null;
        $commentAuthorName = trim((string) (optional($commentUser)->name ?? $comment->author_name ?? 'Kullanıcı'));
        $commentAuthorName = $commentAuthorName !== '' ? $commentAuthorName : 'Kullanıcı';

        $commentId = (string) ($comment->id ?? md5($commentText));
        $commentUrl = $ogxSeoBaseUrl . '#comment-' . $commentId;

        $commentSchema = [
            '@type' => 'Comment',
            '@id' => $commentUrl,
            'url' => $commentUrl,
            'text' => $commentText,
            'author' => [
                '@type' => 'Person',
                'name' => $commentAuthorName,
            ],
            'about' => [
                '@id' => $ogxSeoPostId,
            ],
            'upvoteCount' => (int) ($comment->likes_count ?? $comment->like_count ?? $comment->likes ?? 0),
        ];

        if (!empty($comment->created_at)) {
            $commentSchema['dateCreated'] = optional($comment->created_at)->toIso8601String();
            $commentSchema['datePublished'] = optional($comment->created_at)->toIso8601String();
        }

        if (!empty($comment->updated_at) && !empty($comment->created_at) && $comment->updated_at->gt($comment->created_at)) {
            $commentSchema['dateModified'] = optional($comment->updated_at)->toIso8601String();
        }

        $childCommentSchemas = collect($commentsGrouped->get($comment->id, collect()))
            ->values()
            ->map(fn ($childComment) => $ogxCommentToSchema($childComment))
            ->filter()
            ->values()
            ->all();

        if (!empty($childCommentSchemas)) {
            $commentSchema['commentCount'] = count($childCommentSchemas);
            $commentSchema['comment'] = $childCommentSchemas;
        }

        return $commentSchema;
    };

    $ogxSeoCommentSchemas = $ogxSeoCommentsEnabled
        ? collect($rootComments ?? collect())
            ->values()
            ->map(fn ($rootComment) => $ogxCommentToSchema($rootComment))
            ->filter()
            ->values()
            ->all()
        : [];

    $ogxSeoPostTitle = $ogxCleanSeoText($post->title ?? $post->name ?? config('app.name', 'Ografi') . ' gönderisi', 120);
    $ogxSeoPostTitle = $ogxSeoPostTitle !== '' ? $ogxSeoPostTitle : config('app.name', 'Ografi') . ' gönderisi';

    $ogxCommentsJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        '@id' => $ogxSeoPostId,
        'url' => $ogxSeoBaseUrl,
        'headline' => $ogxSeoPostTitle,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $ogxSeoBaseUrl,
        ],
        'commentCount' => (int) ($commentsCount ?? count($ogxSeoCommentSchemas)),
        'interactionStatistic' => [
            '@type' => 'InteractionCounter',
            'interactionType' => [
                '@type' => 'CommentAction',
            ],
            'userInteractionCount' => (int) ($commentsCount ?? count($ogxSeoCommentSchemas)),
        ],
        'comment' => $ogxSeoCommentSchemas,
    ];
@endphp

@if($ogxSeoCommentsEnabled && !empty($ogxSeoCommentSchemas))
    <script type="application/ld+json">
{!! json_encode($ogxCommentsJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif

<section id="comments" class="ogx-comments-panel shadcn-comment-card" data-ogx-comments aria-labelledby="comments-title">
  <header class="ogx-panel-header">
    <h2 class="ogx-comment-count" id="comments-title">{{ ((int) ($commentsCount ?? 0) > 0) ? ('Yorumlar ' . number_format((int) ($commentsCount ?? 0), 0, ',', '.')) : 'Yorumlar' }}</h2>

    <div class="ogx-filter-wrap" data-ogx-filter>
      <button type="button" class="ogx-filter-btn" data-ogx-filter-trigger aria-haspopup="menu" aria-expanded="false" aria-label="Yorum filtresi">
        <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="w-5 h-5 shrink-0" aria-hidden="true">
          <g fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9.5 14a3 3 0 1 1 0 6a3 3 0 0 1 0-6Zm5-10a3 3 0 1 0 0 6a3 3 0 0 0 0-6Z"></path>
            <path stroke-linecap="round" d="M15 16.959h7m-13-10H2m0 10h2m18-10h-2"></path>
          </g>
        </svg>
      </button>

      <div class="ogx-filter-menu shadcn-menu shadcn-menu--compact" data-ogx-filter-menu hidden style="width: 152px !important; min-width: 152px !important; max-width: min(152px, calc(100vw - 24px)) !important; box-sizing: border-box !important; padding: 8px !important; overflow: hidden !important; border: 1px solid #e4e4e7 !important; border-radius: 16px !important; background: #ffffff !important; color: #18181b !important; box-shadow: 0 1px 2px rgba(0,0,0,.05), 0 8px 24px rgba(15,23,42,.08) !important; filter: none !important;">
        <button type="button" class="ogx-filter-item is-active" data-ogx-sort="popular">
          <span>Popüler</span>
        </button>
        <button type="button" class="ogx-filter-item" data-ogx-sort="new">
          <span>Yeni</span>
        </button>
      </div>
    </div>
  </header>

  @if($commentsDisabled)
    <div class="ogx-empty">Bu gönderide yorumlar yazar tarafından kapatıldı.</div>
  @else
    <form method="POST" action="{{ route('blog.post.comment', $post) }}" enctype="multipart/form-data" id="show-comment-form" class="ogx3-composer" data-ogx-composer data-comment-authenticated="{{ auth()->check() ? '1' : '0' }}" data-login-url="{{ route('login') }}">
      @csrf
      <input type="hidden" name="parent_id" value="">

      <div class="ogx3-field">
        <textarea
          id="show-comment-input"
          name="content"
          data-mentionable="users"
          data-comment-main-input
          data-ogx-max-height="520"
          class="ogx3-textarea"
          rows="1"
          maxlength="500"
          placeholder="Yorumunu buraya yaz..."
          wrap="soft">{{ old('content') }}</textarea>

        <div class="ogx3-preview" id="show-comment-image-preview" data-ogx-preview hidden></div>
        <div id="show-comment-gif-preview" data-gif-preview class="ogx3-preview" hidden></div>

        <div class="ogx3-toolbar">
          <div class="ogx3-tools">
            <div class="ogx3-emoji" data-comment-emoji>
              <button class="ogx3-icon-button" type="button" data-comment-emoji-button aria-haspopup="menu" aria-expanded="false" aria-label="Emoji ekle" title="Emoji ekle">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.9"></circle>
                  <path d="M8.5 10h.01M15.5 10h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"></path>
                  <path d="M8.5 14.5c1 1.3 2.1 1.9 3.5 1.9s2.5-.6 3.5-1.9" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </button>

              <div class="ogx3-emoji-menu" data-comment-emoji-menu hidden>
                @foreach(['😀','😁','😂','😍','😎','😢','😡','👍','👎','👏','🙏','🔥','❤️','🎉'] as $ogxEmoji)
                  <button type="button" class="ogx3-emoji-item" data-comment-emoji-value="{{ $ogxEmoji }}">{{ $ogxEmoji }}</button>
                @endforeach
              </div>
            </div>

            <button class="ogx3-icon-button" type="button" data-comment-file-button data-comment-file-target="#show-comment-image-file" aria-label="Fotoğraf ekle" title="Fotoğraf ekle">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <rect x="3" y="5" width="18" height="14" rx="3" stroke="currentColor" stroke-width="1.9"></rect>
                <circle cx="8.5" cy="10" r="1.4" stroke="currentColor" stroke-width="1.9"></circle>
                <path d="M21 15l-4.2-4.2a1.6 1.6 0 0 0-2.3 0L8 17" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M11 14l-1.3-1.3a1.6 1.6 0 0 0-2.3 0L3 17" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </button>

            @if($ogxGiphySearchUrl)
              <button class="ogx3-gif-button" type="button" data-giphy-trigger aria-haspopup="dialog" aria-expanded="false" aria-label="GIF ekle" title="GIF ekle">GIF</button>
            @endif
          </div>

          <div class="ogx3-submit-group">
            <span class="ogx3-counter"><span data-ogx-char-count>0</span>/500</span>
            <button class="ogx3-submit" type="submit" data-ogx-submit-comment aria-label="Yorumu gönder" title="Yorumu gönder" disabled>
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3.65 20.35c-.34.14-.68.11-.98-.1a1.02 1.02 0 0 1-.43-.88l.82-5.62L13.2 12 3.06 10.25l-.82-5.62c-.05-.37.09-.67.43-.88.3-.21.64-.24.98-.1l17.32 7.4c.45.19.68.51.68.95s-.23.76-.68.95L3.65 20.35Z" fill="currentColor"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div class="ogx3-mention-menu" data-comment-mention-menu hidden></div>
      <input id="show-comment-image-file" name="image" type="file" accept="image/*" class="ogx3-file-input" data-comment-file-input data-comment-preview-target="#show-comment-image-preview">
    </form>
  @endif

  <section class="ogx-comments-list" data-ogx-comments-list>
    @if($commentsDisabled)
      <div class="ogx-empty">Yeni yorum kabul edilmiyor.</div>
    @elseif($rootComments->isNotEmpty())
      @foreach($rootComments as $comment)
        @php
          $commentUser = $comment->user ?? null;
          $commentUserName = (string) (optional($commentUser)->name ?? 'Kullanıcı');
          $commentUserHandle = optional($commentUser)->username ? '@' . ltrim((string) $commentUser->username, '@') : $commentUserName;
          $commentUserAvatar = optional($commentUser)->profile_photo_url ?? null;
          $commentInitials = collect(preg_split('/\s+/', trim($commentUserName), -1, PREG_SPLIT_NO_EMPTY))
              ->take(2)
              ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
              ->implode('') ?: 'U';
          $commentText = (string) ($comment->content ?? $comment->body ?? '');
          $commentLikes = (int) ($comment->likes_count ?? $comment->like_count ?? $comment->likes ?? 0);
          $commentDislikes = (int) ($comment->dislikes_count ?? $comment->dislike_count ?? $comment->dislikes ?? 0);
          $canEditComment = auth()->check() && (
              (int) auth()->id() === (int) ($comment->user_id ?? 0)
              || (int) auth()->id() === $ogxPostAuthorId
          );
          $canReportComment = auth()->check()
              && !$canEditComment
              && (int) auth()->id() !== (int) ($comment->user_id ?? 0);
          $commentReportUrl = null;
          if ($canReportComment) {
              if (\Illuminate\Support\Facades\Route::has('blog.comment.report.form')) {
                  $commentReportUrl = route('blog.comment.report.form', $comment);
              } elseif (\Illuminate\Support\Facades\Route::has('blog.comments.report.form')) {
                  $commentReportUrl = route('blog.comments.report.form', $comment);
              } elseif (\Illuminate\Support\Facades\Route::has('comments.report.form')) {
                  $commentReportUrl = route('comments.report.form', $comment);
              } else {
                  $commentReportUrl = '#';
              }
          }
          $childComments = collect($commentsGrouped->get($comment->id, []))->values();
          $isMineComment = auth()->check() && (int) auth()->id() === (int) ($comment->user_id ?? 0);
        @endphp

        <article class="ogx-comment" id="comment-{{ $comment->id }}" aria-label="Yorum: {{ $commentUserName }}" data-seo-comment-url="{{ url()->current() }}#comment-{{ $comment->id }}" data-ogx-comment data-ogx-mine="{{ $isMineComment ? '1' : '0' }}" data-ogx-likes="{{ $commentLikes }}" data-ogx-created="{{ optional($comment->created_at)->timestamp ?? 0 }}">
          <div class="ogx-avatar">
            @if($commentUserAvatar)
              <img src="{{ $commentUserAvatar }}" alt="{{ $commentUserName }}" loading="lazy" decoding="async">
            @else
              <span>{{ $commentInitials }}</span>
            @endif
          </div>

          <div class="ogx-comment-main">
            <div class="ogx-meta">
              <span class="ogx-username">{{ $commentUserName }}</span>
              @if($commentUser)
                <x-verification-badge :user="$commentUser" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" />
              @endif
            </div>

            <div class="ogx-submeta">
              <span>{{ optional($comment->created_at)->diffForHumans() }}</span>
              @if((int) ($comment->user_id ?? 0) === $ogxPostAuthorId)
                <span class="ogx-author-label">Yazar</span>
              @endif
            </div>

            <div class="ogx-comment-text">{!! $ogxRenderCommentText($comment) !!}</div>

            <div class="ogx-comment-actions">
@if($childComments->isNotEmpty())
                <button
                  type="button"
                  class="ogx-replies-plus-btn"
                  data-ogx-replies-toggle
                  data-replies-target="#replies-{{ $comment->id }}"
                  aria-expanded="false"
                  aria-label="Yanıtları göster"
                  title="Yanıtları göster"
                >
                  <span class="ogx-replies-plus-mark">+</span>
                  <span class="ogx-replies-minus-mark">−</span>
                </button>
              @endif

              <div class="ogx-votes" aria-label="Yorum tepki butonları">
@if(\Illuminate\Support\Facades\Route::has('blog.comment.like'))
                  <form method="POST" action="{{ route('blog.comment.like', $comment) }}">
                    @csrf
                    <button class="ogx-vote-btn" type="submit" onclick="event.stopPropagation();" aria-label="Beğen">
                      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m0-14-5 5m5-5 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                  </form>
                @else
                  <button class="ogx-vote-btn" type="button" aria-label="Beğen">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m0-14-5 5m5-5 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                @endif

                <span class="ogx-vote-count">{{ $commentLikes > 0 ? number_format($commentLikes) : '0' }}</span>

                @if(\Illuminate\Support\Facades\Route::has('blog.comment.dislike'))
                  <form method="POST" action="{{ route('blog.comment.dislike', $comment) }}">
                    @csrf
                    <button class="ogx-vote-btn" type="submit" onclick="event.stopPropagation();" aria-label="Beğenme">
                      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5m0 14-5-5m5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                  </form>
                @else
                  <button class="ogx-vote-btn" type="button" aria-label="Beğenme">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5m0 14-5-5m5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                @endif
              </div>

              @auth
                @unless($commentsDisabled)
                  <button type="button" class="ogx-reply-btn" data-comment-reply-toggle="#reply-form-{{ $comment->id }}">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 13.4876 3.36093 14.891 4 16.1272L3 21L7.8728 20C9.10904 20.6391 10.5124 21 12 21Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Cevap</span>
                  </button>
                @endunless
              @endauth

              @if($canEditComment || $commentReportUrl)
                <div class="ogx-menu-wrap" data-comment-more>
                  <button class="ogx-more-btn" type="button" data-comment-more-trigger aria-haspopup="menu" aria-expanded="false" aria-label="Yorum menüsü">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 12h.01M12 12h.01M17.5 12h.01" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/></svg>
                  </button>
                  <div class="ogx-comment-menu" data-comment-more-menu hidden>
                    @if($canEditComment && \Illuminate\Support\Facades\Route::has('blog.comment.update'))
                      <button type="button" data-comment-edit-toggle="#edit-form-{{ $comment->id }}">Düzenle</button>
                    @endif
                    @if($canEditComment && \Illuminate\Support\Facades\Route::has('blog.comment.delete'))
                      <form method="POST" action="{{ route('blog.comment.delete', $comment) }}" onsubmit="return confirm('Bu yorum silinsin mi?');">
                        @csrf
                        @method('DELETE')
                        <button class="danger" type="submit">Sil</button>
                      </form>
                    @endif
                    @if($commentReportUrl)
                      <a href="{{ $commentReportUrl }}" @if($commentReportUrl === '#') onclick="event.preventDefault();" @endif>Şikayet et</a>
                    @endif
                  </div>
                </div>
              @endif
            </div>

            @if($canEditComment && \Illuminate\Support\Facades\Route::has('blog.comment.update'))
              <form method="POST" action="{{ route('blog.comment.update', $comment) }}" class="ogx-edit-form" id="edit-form-{{ $comment->id }}">
                @csrf
                @method('PUT')
                <div class="ogx-reply-compose">
                  <textarea
                    name="content"
                    data-mentionable="users"
                    data-ogx-autogrow
                    data-ogx-max-height="420"
                    rows="1"
                    required
                    style="height:36px;min-height:36px;max-height:none;overflow-y:hidden;resize:none;"
                  
          oninput="this.style.setProperty('height','auto','important');this.style.setProperty('max-height','none','important');this.style.setProperty('overflow-y','hidden','important');this.style.setProperty('height',(this.scrollHeight + 2) + 'px','important');">{{ $commentText }}</textarea>
                  <div class="ogx-form-actions">
                    <button type="button" class="ogx-ghost-btn" data-comment-edit-toggle="#edit-form-{{ $comment->id }}">Vazgeç</button>
                    <button type="submit" class="ogx-primary-btn">Kaydet</button>
                  </div>
                </div>
              </form>
            @endif

            @auth
              @unless($commentsDisabled)
                <form method="POST" action="{{ route('blog.post.comment', $post) }}" enctype="multipart/form-data" class="ogx-reply-form" id="reply-form-{{ $comment->id }}">
                  @csrf
                  <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                  <div class="ogx-reply-compose">
                    <textarea
                      name="content"
                      data-mentionable="users"
                      data-ogx-autogrow
                      data-ogx-max-height="420"
                      rows="1"
                      style="height:36px;min-height:36px;max-height:none;overflow-y:hidden;resize:none;"
                      placeholder="{{ ltrim($commentUserHandle, '@') }} yanıt yaz..."
                    
          oninput="this.style.setProperty('height','auto','important');this.style.setProperty('max-height','none','important');this.style.setProperty('overflow-y','hidden','important');this.style.setProperty('height',(this.scrollHeight + 2) + 'px','important');"></textarea>
                    <button type="button" class="ogx-image-btn" data-comment-file-button data-comment-file-target="#reply-image-file-{{ $comment->id }}" aria-label="Resim ekle" title="Resim ekle">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7.8A2.8 2.8 0 0 1 6.8 5h10.4A2.8 2.8 0 0 1 20 7.8v8.4a2.8 2.8 0 0 1-2.8 2.8H6.8A2.8 2.8 0 0 1 4 16.2V7.8Z" fill="none" stroke="currentColor" stroke-width="1.7"/>
                        <path d="m6.5 16 3.1-3.2 2.4 2.4 2.2-2.7 3.3 3.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="15.8" cy="9.2" r="1.2" fill="currentColor"/>
                      </svg>
                    </button>
                    @if($ogxGiphySearchUrl)
                      <button type="button" class="ogx-gif-btn ogx-gif-btn--reply" data-giphy-trigger aria-haspopup="dialog" aria-expanded="false" aria-label="GIF ekle" title="GIF ekle">GIF</button>
                    @endif
                    <input id="reply-image-file-{{ $comment->id }}" name="image" type="file" accept="image/*" class="ogx-file-input" data-comment-file-input data-comment-preview-target="#reply-image-preview-{{ $comment->id }}">
                    <div id="reply-image-preview-{{ $comment->id }}" class="ogx-preview-strip ogx-preview-strip--reply" hidden></div>
                    <div class="ogx-preview-strip ogx-preview-strip--reply" data-giphy-preview hidden></div>
                    <div class="ogx-form-actions">
                      <button type="button" class="ogx-ghost-btn" data-comment-reply-toggle="#reply-form-{{ $comment->id }}">Vazgeç</button>
                      <button type="submit" class="ogx-primary-btn">Yanıtla</button>
                    </div>
                  </div>
                </form>
              @endunless
            @endauth
            @if($childComments->isNotEmpty())
              <div class="ogx-replies is-collapsed" id="replies-{{ $comment->id }}">
                @foreach($childComments as $reply)
                  @php
                    $replyUser = $reply->user ?? null;
                    $replyUserName = (string) (optional($replyUser)->name ?? 'Kullanıcı');
                    $replyAvatar = optional($replyUser)->profile_photo_url ?? null;
                    $replyInitials = collect(preg_split('/\s+/', trim($replyUserName), -1, PREG_SPLIT_NO_EMPTY))
                        ->take(2)
                        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                        ->implode('') ?: 'U';
                    $replyText = (string) ($reply->content ?? $reply->body ?? '');
                    $replyLikes = (int) ($reply->likes_count ?? $reply->like_count ?? $reply->likes ?? 0);
                    $replyDislikes = (int) ($reply->dislikes_count ?? $reply->dislike_count ?? $reply->dislikes ?? 0);
                    $replyChildComments = collect($commentsGrouped->get($reply->id, []))->values();
                    $canEditReply = auth()->check() && (
                        (int) auth()->id() === (int) ($reply->user_id ?? 0)
                        || (int) auth()->id() === $ogxPostAuthorId
                    );
                    $canReportReply = auth()->check()
                        && !$canEditReply
                        && (int) auth()->id() !== (int) ($reply->user_id ?? 0);
                    $replyReportUrl = null;
                    if ($canReportReply) {
                        if (\Illuminate\Support\Facades\Route::has('blog.comment.report.form')) {
                            $replyReportUrl = route('blog.comment.report.form', $reply);
                        } elseif (\Illuminate\Support\Facades\Route::has('blog.comments.report.form')) {
                            $replyReportUrl = route('blog.comments.report.form', $reply);
                        } elseif (\Illuminate\Support\Facades\Route::has('comments.report.form')) {
                            $replyReportUrl = route('comments.report.form', $reply);
                        } else {
                            $replyReportUrl = '#';
                        }
                    }
                  @endphp

                  <article class="ogx-comment ogx-comment--reply" id="comment-{{ $reply->id }}" aria-label="Yanıt: {{ $replyUserName }}" data-seo-comment-url="{{ url()->current() }}#comment-{{ $reply->id }}" data-ogx-comment data-ogx-mine="{{ auth()->check() && (int) auth()->id() === (int) ($reply->user_id ?? 0) ? '1' : '0' }}" data-ogx-likes="{{ $replyLikes }}" data-ogx-created="{{ optional($reply->created_at)->timestamp ?? 0 }}">
                    <div class="ogx-avatar">
                      @if($replyAvatar)
                        <img src="{{ $replyAvatar }}" alt="{{ $replyUserName }}" loading="lazy" decoding="async">
                      @else
                        <span>{{ $replyInitials }}</span>
                      @endif
                    </div>
                    <div class="ogx-comment-main">
                      <div class="ogx-meta">
                        <span class="ogx-username">{{ $replyUserName }}</span>
                        @if($replyUser)
                          <x-verification-badge :user="$replyUser" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" />
                        @endif
                      </div>
                      <div class="ogx-submeta">
                        <span>{{ optional($reply->created_at)->diffForHumans() }}</span>
                        @if((int) ($reply->user_id ?? 0) === $ogxPostAuthorId)
                          <span class="ogx-author-label">Yazar</span>
                        @endif
                      </div>
                      <div class="ogx-comment-text">{!! $ogxRenderCommentText($reply) !!}</div>

                      <div class="ogx-comment-actions">
                        @if(isset($replyChildComments) && $replyChildComments->isNotEmpty())
                          <button
                            type="button"
                            class="ogx-replies-plus-btn"
                            data-ogx-replies-toggle
                            data-replies-target="#replies-{{ $reply->id }}"
                            aria-expanded="false"
                            aria-label="Yanıtları göster"
                            title="Yanıtları göster"
                          >
                            <span class="ogx-replies-plus-mark">+</span>
                            <span class="ogx-replies-minus-mark">−</span>
                          </button>
                        @endif

                        <div class="ogx-votes" aria-label="Yanıt tepki butonları">
                          @if(\Illuminate\Support\Facades\Route::has('blog.comment.like'))
                            <form method="POST" action="{{ route('blog.comment.like', $reply) }}">
                              @csrf
                              <button class="ogx-vote-btn" type="submit" onclick="event.stopPropagation();" aria-label="Beğen">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m0-14-5 5m5-5 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                              </button>
                            </form>
                          @else
                            <button class="ogx-vote-btn" type="button" aria-label="Beğen">
                              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m0-14-5 5m5-5 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                          @endif

                          <span class="ogx-vote-count">{{ $replyLikes > 0 ? number_format($replyLikes) : '0' }}</span>

                          @if(\Illuminate\Support\Facades\Route::has('blog.comment.dislike'))
                            <form method="POST" action="{{ route('blog.comment.dislike', $reply) }}">
                              @csrf
                              <button class="ogx-vote-btn" type="submit" onclick="event.stopPropagation();" aria-label="Beğenme">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5m0 14-5-5m5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                              </button>
                            </form>
                          @else
                            <button class="ogx-vote-btn" type="button" aria-label="Beğenme">
                              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5m0 14-5-5m5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                          @endif
                        </div>

                        @if($canEditReply || $replyReportUrl)
                          <div class="ogx-menu-wrap" data-comment-more>
                            <button class="ogx-more-btn" type="button" data-comment-more-trigger aria-haspopup="menu" aria-expanded="false" aria-label="Yanıt menüsü">
                              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 12h.01M12 12h.01M17.5 12h.01" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/></svg>
                            </button>
                            <div class="ogx-comment-menu" data-comment-more-menu hidden>
                              @if($canEditReply && \Illuminate\Support\Facades\Route::has('blog.comment.update'))
                                <button type="button" data-comment-edit-toggle="#edit-form-{{ $reply->id }}">Düzenle</button>
                              @endif
                              @if($canEditReply && \Illuminate\Support\Facades\Route::has('blog.comment.delete'))
                                <form method="POST" action="{{ route('blog.comment.delete', $reply) }}" onsubmit="return confirm('Bu yanıt silinsin mi?');">
                                  @csrf
                                  @method('DELETE')
                                  <button class="danger" type="submit">Sil</button>
                                </form>
                              @endif
                              @if($replyReportUrl)
                                <a href="{{ $replyReportUrl }}" @if($replyReportUrl === '#') onclick="event.preventDefault();" @endif>Şikayet et</a>
                              @endif
                            </div>
                          </div>
                        @endif
                      </div>

                      @if($canEditReply && \Illuminate\Support\Facades\Route::has('blog.comment.update'))
                        <form method="POST" action="{{ route('blog.comment.update', $reply) }}" class="ogx-edit-form" id="edit-form-{{ $reply->id }}">
                          @csrf
                          @method('PUT')
                          <div class="ogx-reply-compose">
                            <textarea
                              name="content"
                              data-mentionable="users"
                              data-ogx-autogrow
                              data-ogx-max-height="420"
                              rows="1"
                              required
                              style="height:36px;min-height:36px;max-height:none;overflow-y:hidden;resize:none;"
                            
          oninput="this.style.setProperty('height','auto','important');this.style.setProperty('max-height','none','important');this.style.setProperty('overflow-y','hidden','important');this.style.setProperty('height',(this.scrollHeight + 2) + 'px','important');">{{ $replyText }}</textarea>
                            <div class="ogx-form-actions">
                              <button type="button" class="ogx-ghost-btn" data-comment-edit-toggle="#edit-form-{{ $reply->id }}">Vazgeç</button>
                              <button type="submit" class="ogx-primary-btn">Kaydet</button>
                            </div>
                          </div>
                        </form>
                      @endif

                      @if(isset($replyChildComments) && $replyChildComments->isNotEmpty())
                        <div class="ogx-replies is-collapsed" id="replies-{{ $reply->id }}">
                          @foreach($replyChildComments as $nestedReply)
                            @php
                              $nestedUser = $nestedReply->user ?? null;
                              $nestedUserName = (string) (optional($nestedUser)->name ?? 'Kullanıcı');
                              $nestedAvatar = optional($nestedUser)->profile_photo_url ?? null;
                              $nestedInitials = collect(preg_split('/\s+/', trim($nestedUserName), -1, PREG_SPLIT_NO_EMPTY))
                                  ->take(2)
                                  ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                                  ->implode('') ?: 'U';
                              $nestedLikes = (int) ($nestedReply->likes_count ?? $nestedReply->like_count ?? $nestedReply->likes ?? 0);
                              $nestedDislikes = (int) ($nestedReply->dislikes_count ?? $nestedReply->dislike_count ?? $nestedReply->dislikes ?? 0);
                            @endphp

                            <article class="ogx-comment ogx-comment--reply" id="comment-{{ $nestedReply->id }}" aria-label="Yanıt: {{ $nestedUserName }}" data-seo-comment-url="{{ url()->current() }}#comment-{{ $nestedReply->id }}" data-ogx-comment data-ogx-mine="{{ auth()->check() && (int) auth()->id() === (int) ($nestedReply->user_id ?? 0) ? '1' : '0' }}" data-ogx-likes="{{ $nestedLikes }}" data-ogx-created="{{ optional($nestedReply->created_at)->timestamp ?? 0 }}">
                              <div class="ogx-avatar">
                                @if($nestedAvatar)
                                  <img src="{{ $nestedAvatar }}" alt="{{ $nestedUserName }}" loading="lazy" decoding="async">
                                @else
                                  <span>{{ $nestedInitials }}</span>
                                @endif
                              </div>

                              <div class="ogx-comment-main">
                                <div class="ogx-meta">
                                  <span class="ogx-username">{{ $nestedUserName }}</span>
                                  @if($nestedUser)
                                    <x-verification-badge :user="$nestedUser" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" />
                                  @endif
                                </div>

                                <div class="ogx-submeta">
                                  <span>{{ optional($nestedReply->created_at)->diffForHumans() }}</span>
                                  @if((int) ($nestedReply->user_id ?? 0) === $ogxPostAuthorId)
                                    <span class="ogx-author-label">Yazar</span>
                                  @endif
                                </div>

                                <div class="ogx-comment-text">{!! $ogxRenderCommentText($nestedReply) !!}</div>

                                <div class="ogx-comment-actions">
                                  <div class="ogx-votes" aria-label="Yanıt tepki butonları">
                                    @if(\Illuminate\Support\Facades\Route::has('blog.comment.like'))
                                      <form method="POST" action="{{ route('blog.comment.like', $nestedReply) }}">
                                        @csrf
                                        <button class="ogx-vote-btn" type="submit" onclick="event.stopPropagation();" aria-label="Beğen">
                                          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m0-14-5 5m5-5 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                      </form>
                                    @else
                                      <button class="ogx-vote-btn" type="button" aria-label="Beğen">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14m0-14-5 5m5-5 5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                      </button>
                                    @endif

                                    <span class="ogx-vote-count">{{ $nestedLikes > 0 ? number_format($nestedLikes) : '0' }}</span>

                                    @if(\Illuminate\Support\Facades\Route::has('blog.comment.dislike'))
                                      <form method="POST" action="{{ route('blog.comment.dislike', $nestedReply) }}">
                                        @csrf
                                        <button class="ogx-vote-btn" type="submit" onclick="event.stopPropagation();" aria-label="Beğenme">
                                          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5m0 14-5-5m5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                      </form>
                                    @else
                                      <button class="ogx-vote-btn" type="button" aria-label="Beğenme">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5m0 14-5-5m5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                      </button>
                                    @endif
                                  </div>
                                </div>
                              </div>
                            </article>
                          @endforeach
                        </div>
                      @endif

                    </div>
                  </article>
                @endforeach
              </div>
            @endif
          </div>
        </article>
      @endforeach
    @else
      <div class="ogx-empty">İlk yorumu sen yaz.</div>
    @endif
  </section>
</section>


@auth
  @if($ogxGiphySearchUrl)
    <div id="ogx-giphy-modal" class="ogx-giphy-modal" hidden>
      <div class="ogx-giphy-backdrop" data-giphy-close></div>

      <div class="ogx-giphy-dialog" role="dialog" aria-modal="true" aria-labelledby="ogx-giphy-title">
        <div class="ogx-giphy-head">
          <div id="ogx-giphy-title" class="ogx-giphy-title">GIF Ekle</div>
          <button type="button" class="ogx-giphy-close" data-giphy-close aria-label="Kapat">×</button>
        </div>

        <input
          id="ogx-giphy-q"
          class="ogx-giphy-search"
          type="text"
          placeholder="GIF ara..."
          autocomplete="off"
        >

        <div id="ogx-giphy-status" class="ogx-giphy-status"></div>
        <div id="ogx-giphy-grid" class="ogx-giphy-grid"></div>
      </div>
    </div>
  @endif
@endauth

<style>
  .post-show-shell .ogx-comments-panel,
  .ogx-comments-panel {
    width: 100% !important;
    max-width: 680px !important;
    margin: 14px auto 0 !important;
    padding: 16px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    color: #111827 !important;
    font-family: "Segoe UI", Arial, sans-serif !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .ogx-comments-panel *,
  .ogx-comments-panel *::before,
  .ogx-comments-panel *::after {
    box-sizing: border-box !important;
  }

  .ogx-comments-panel button,
  .ogx-comments-panel textarea,
  .ogx-comments-panel input {
    font: inherit !important;
  }

  .ogx-panel-header {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    margin: 0 0 12px !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
  }

  .ogx-comment-count {
    margin: 0 !important;
    color: #111827 !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    letter-spacing: 0 !important;
  }

  .ogx-filter-wrap,
  .ogx-menu-wrap,
  .ogx-emoji-wrap {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
  }

  .ogx-filter-btn,
  .ogx-more-btn,
  .ogx-emoji-btn,
  .ogx-image-btn,
  .ogx-vote-btn,
  .ogx-reply-btn,
  .ogx-replies-plus-btn {
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    cursor: pointer !important;
    -webkit-tap-highlight-color: transparent !important;
  }

  .ogx-filter-btn,
  .ogx-more-btn,
  .ogx-emoji-btn,
  .ogx-image-btn {
    width: 34px !important;
    height: 34px !important;
    padding: 0 !important;
    border-radius: 50% !important;
    color: #4b5563 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: color 0.18s ease, transform 0.18s ease !important;
  }

  .ogx-filter-btn:hover,
  .ogx-filter-btn.is-open,
  .ogx-more-btn:hover,
  .ogx-more-btn.is-open,
  .ogx-emoji-btn:hover,
  .ogx-emoji-btn.is-open,
  .ogx-image-btn:hover {
    background: transparent !important;
    color: #2563eb !important;
  }

  .ogx-filter-btn svg,
  .ogx-more-btn svg,
  .ogx-emoji-btn svg,
  .ogx-image-btn svg {
    width: 20px !important;
    height: 20px !important;
    display: block !important;
  }

  .ogx-filter-menu,
  .ogx-comment-menu,
  .ogx-emoji-menu {
    position: absolute !important;
    z-index: 60 !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 10px !important;
    background: #ffffff !important;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.10) !important;
  }

  .ogx-filter-menu,
  .ogx-comment-menu {
    top: 42px !important;
    right: 0 !important;
    width: 132px !important;
    padding: 6px !important;
  }

  .ogx-filter-menu[hidden],
  .ogx-comment-menu[hidden],
  .ogx-emoji-menu[hidden] {
    display: none !important;
  }

  .ogx-filter-menu:not([hidden]),
  .ogx-comment-menu:not([hidden]) {
    display: block !important;
  }

  .ogx-filter-item,
  .ogx-comment-menu button,
  .ogx-comment-menu a {
    width: 100% !important;
    min-height: 34px !important;
    padding: 8px 10px !important;
    border: 0 !important;
    border-radius: 8px !important;
    background: transparent !important;
    color: #374151 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    text-align: left !important;
    text-decoration: none !important;
    cursor: pointer !important;
  }

  .ogx-filter-item:hover,
  .ogx-filter-item.is-active,
  .ogx-comment-menu button:hover,
  .ogx-comment-menu a:hover {
    background: transparent !important;
    color: #2563eb !important;
  }

  .ogx-filter-item.is-active::after {
    content: "" !important;
    width: 6px !important;
    height: 6px !important;
    border-radius: 50% !important;
    background: #2563eb !important;
    flex-shrink: 0 !important;
  }

  .ogx-comment-menu .danger {
    color: #dc2626 !important;
  }

  .ogx-composer-form {
    width: 100% !important;
    margin: 0 0 22px !important;
    padding: 0 !important;
    overflow: visible !important;
  }

  .ogx-composer-box {
    width: 100% !important;
    background: #eef0f3 !important;
    border: 1px solid #d7dbe0 !important;
    border-radius: 10px !important;
    padding: 12px !important;
    transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    overflow: visible !important;
  }

  .ogx-composer-form.has-comment-ready .ogx-composer-box {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10) !important;
  }

  .ogx-comment-input {
    display: block !important;
    width: 100% !important;
    min-height: 42px !important;
    max-height: 260px !important;
    margin: 0 !important;
    padding: 10px 10px 6px !important;
    border: 0 !important;
    outline: 0 !important;
    border-radius: 8px !important;
    background: #eef0f3 !important;
    color: #111827 !important;
    font-size: 15px !important;
    font-weight: 400 !important;
    line-height: 1.45 !important;
    resize: none !important;
    overflow-y: hidden !important;
    box-shadow: none !important;
  }

  .ogx-comment-input::placeholder,
  .ogx-reply-compose textarea::placeholder {
    color: #6b7280 !important;
  }

  .ogx-preview-strip {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    margin: 8px 0 0 !important;
    padding: 0 !important;
  }

  .ogx-preview-strip[hidden] {
    display: none !important;
  }

  .ogx-preview-item {
    position: relative !important;
    width: 58px !important;
    height: 58px !important;
    border: 1px solid #d7dbe0 !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    background: #ffffff !important;
    display: block !important;
  }

  .ogx-preview-item img,
  .ogx-preview-item video {
    width: 100% !important;
    height: 100% !important;
    display: block !important;
    object-fit: cover !important;
  }

  .ogx-composer-actions {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    margin-top: 8px !important;
    padding: 0 !important;
    background: transparent !important;
  }

  .ogx-composer-left,
  .ogx-composer-right {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
  }

  .ogx-composer-right {
    gap: 10px !important;
  }

  .ogx-char-counter {
    color: #6b7280 !important;
    font-size: 13px !important;
    line-height: 1 !important;
  }

  .ogx-submit-btn {
    width: 36px !important;
    height: 36px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 50% !important;
    background: transparent !important;
    color: #64748b !important;
    opacity: 0.65 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    display: inline-grid !important;
    place-items: center !important;
    box-shadow: none !important;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease, opacity 0.2s ease !important;
  }

  .ogx-submit-btn svg {
    width: 18px !important;
    height: 18px !important;
    display: block !important;
    margin-left: 1px !important;
    margin-top: -1px !important;
  }

  .ogx-composer-form.has-comment-ready .ogx-submit-btn {
    background: #2563eb !important;
    color: #ffffff !important;
    opacity: 1 !important;
    cursor: pointer !important;
    pointer-events: auto !important;
  }

  .ogx-composer-form.has-comment-ready .ogx-submit-btn:hover {
    background: #1d4ed8 !important;
    transform: translateY(-1px) !important;
  }

  .ogx-emoji-menu {
    left: 0 !important;
    bottom: 42px !important;
    width: 236px !important;
    padding: 8px !important;
    grid-template-columns: repeat(7, 1fr) !important;
    gap: 4px !important;
  }

  .ogx-emoji-menu:not([hidden]) {
    display: grid !important;
  }

  .ogx-emoji-item {
    width: 28px !important;
    height: 28px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 8px !important;
    background: transparent !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 18px !important;
    line-height: 1 !important;
    transition: transform 0.15s ease !important;
  }

  .ogx-emoji-item:hover {
    background: transparent !important;
    transform: scale(1.08) !important;
  }

  .ogx-file-input {
    display: none !important;
  }

  .ogx-mention-menu[hidden] {
    display: none !important;
  }

  .ogx-comments-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .ogx-comment {
    width: 100% !important;
    display: grid !important;
    grid-template-columns: 38px minmax(0, 1fr) !important;
    column-gap: 10px !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  .ogx-avatar {
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    display: grid !important;
    place-items: center !important;
    background: #2563eb !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
  }

  .ogx-avatar img {
    width: 100% !important;
    height: 100% !important;
    display: block !important;
    object-fit: cover !important;
  }

  .ogx-comment-main {
    min-width: 0 !important;
    padding: 12px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 10px !important;
    background: #ffffff !important;
  }

  .ogx-meta {
    display: flex !important;
    align-items: center !important;
    gap: 5px !important;
    margin: 0 !important;
  }

  .ogx-username {
    color: #111827 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    line-height: 18px !important;
  }

  .ogx-submeta {
    display: flex !important;
    align-items: center !important;
    gap: 5px !important;
    margin: 2px 0 8px !important;
    color: #6b7280 !important;
    font-size: 12px !important;
    font-weight: 400 !important;
  }

  .ogx-author-label {
    color: #2563eb !important;
    font-weight: 600 !important;
  }

  .ogx-comment-text {
    margin: 0 !important;
    color: #111827 !important;
    font-size: 15px !important;
    font-weight: 400 !important;
    line-height: 1.45 !important;
    word-break: break-word !important;
  }

  .ogx-comment-text p {
    margin: 0 !important;
  }

  .ogx-comment-actions {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin: 12px 0 0 !important;
  }

  .ogx-votes,
  .ogx-votes form {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .ogx-vote-btn,
  .ogx-reply-btn,
  .ogx-replies-plus-btn {
    height: 24px !important;
    padding: 0 !important;
    color: #4b5563 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    transition: color 0.15s ease !important;
  }

  .ogx-vote-btn:hover,
  .ogx-reply-btn:hover,
  .ogx-replies-plus-btn:hover {
    color: #2563eb !important;
    background: transparent !important;
  }

  .ogx-vote-btn svg,
  .ogx-reply-btn svg {
    width: 16px !important;
    height: 16px !important;
  }

  .ogx-vote-count {
    color: #374151 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
  }

  .ogx-replies-plus-btn {
    width: 24px !important;
    border-radius: 50% !important;
  }

  .ogx-replies-minus-mark,
  .ogx-replies-plus-btn.is-open .ogx-replies-plus-mark {
    display: none !important;
  }

  .ogx-replies-plus-btn.is-open .ogx-replies-minus-mark {
    display: inline !important;
  }

  .ogx-reply-form,
  .ogx-edit-form {
    display: none !important;
    margin-top: 12px !important;
  }

  .ogx-reply-form.is-open,
  .ogx-edit-form.is-open {
    display: block !important;
  }

  .ogx-reply-compose {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    padding: 10px !important;
    border: 1px solid #d7dbe0 !important;
    border-radius: 10px !important;
    background: #eef0f3 !important;
  }

  .ogx-reply-compose:focus-within {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10) !important;
  }

  .ogx-reply-compose textarea {
    flex: 1 1 100% !important;
    width: 100% !important;
    min-height: 36px !important;
    max-height: 260px !important;
    border: 0 !important;
    outline: 0 !important;
    resize: none !important;
    overflow: hidden !important;
    background: transparent !important;
    color: #111827 !important;
    font-size: 14px !important;
    line-height: 20px !important;
  }

  .ogx-form-actions {
    margin-left: auto !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
  }

  .ogx-ghost-btn,
  .ogx-primary-btn {
    min-height: 32px !important;
    padding: 0 13px !important;
    border: 0 !important;
    border-radius: 999px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
  }

  .ogx-ghost-btn {
    background: #ffffff !important;
    color: #374151 !important;
  }

  .ogx-primary-btn {
    background: #2563eb !important;
    color: #ffffff !important;
  }

  .ogx-replies {
    display: flex !important;
    flex-direction: column !important;
    gap: 12px !important;
    margin-top: 12px !important;
  }

  .ogx-replies.is-collapsed {
    display: none !important;
  }

  .ogx-comment--reply {
    grid-template-columns: 32px minmax(0, 1fr) !important;
    margin-left: 8px !important;
  }

  .ogx-comment--reply .ogx-avatar {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    font-size: 11px !important;
  }

  .ogx-empty {
    margin: 0 !important;
    padding: 14px !important;
    border: 1px dashed #d7dbe0 !important;
    border-radius: 10px !important;
    background: #f9fafb !important;
    color: #6b7280 !important;
    font-size: 14px !important;
    text-align: center !important;
  }

  html.dark .ogx-comments-panel,
  body.dark .ogx-comments-panel,
  .dark .ogx-comments-panel,
  [data-theme="dark"] .ogx-comments-panel {
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: #111318 !important;
    color: #f8fafc !important;
  }

  html.dark .ogx-comment-count,
  html.dark .ogx-username,
  html.dark .ogx-comment-text,
  html.dark .ogx-vote-count,
  body.dark .ogx-comment-count,
  body.dark .ogx-username,
  body.dark .ogx-comment-text,
  body.dark .ogx-vote-count,
  .dark .ogx-comment-count,
  .dark .ogx-username,
  .dark .ogx-comment-text,
  .dark .ogx-vote-count,
  [data-theme="dark"] .ogx-comment-count,
  [data-theme="dark"] .ogx-username,
  [data-theme="dark"] .ogx-comment-text,
  [data-theme="dark"] .ogx-vote-count {
    color: #f8fafc !important;
  }

  html.dark .ogx-composer-box,
  html.dark .ogx-comment-input,
  html.dark .ogx-reply-compose,
  body.dark .ogx-composer-box,
  body.dark .ogx-comment-input,
  body.dark .ogx-reply-compose,
  .dark .ogx-composer-box,
  .dark .ogx-comment-input,
  .dark .ogx-reply-compose,
  [data-theme="dark"] .ogx-composer-box,
  [data-theme="dark"] .ogx-comment-input,
  [data-theme="dark"] .ogx-reply-compose {
    background: #1f232b !important;
    color: #f8fafc !important;
  }

  html.dark .ogx-comment-main,
  body.dark .ogx-comment-main,
  .dark .ogx-comment-main,
  [data-theme="dark"] .ogx-comment-main,
  html.dark .ogx-filter-menu,
  html.dark .ogx-comment-menu,
  html.dark .ogx-emoji-menu,
  body.dark .ogx-filter-menu,
  body.dark .ogx-comment-menu,
  body.dark .ogx-emoji-menu,
  .dark .ogx-filter-menu,
  .dark .ogx-comment-menu,
  .dark .ogx-emoji-menu,
  [data-theme="dark"] .ogx-filter-menu,
  [data-theme="dark"] .ogx-comment-menu,
  [data-theme="dark"] .ogx-emoji-menu {
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: #171a21 !important;
  }

  html.dark .ogx-giphy-dialog,
  body.dark .ogx-giphy-dialog,
  .dark .ogx-giphy-dialog,
  [data-theme="dark"] .ogx-giphy-dialog {
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: #111318 !important;
  }

  html.dark .ogx-giphy-title,
  body.dark .ogx-giphy-title,
  .dark .ogx-giphy-title,
  [data-theme="dark"] .ogx-giphy-title {
    color: #f8fafc !important;
  }

  html.dark .ogx-giphy-search,
  body.dark .ogx-giphy-search,
  .dark .ogx-giphy-search,
  [data-theme="dark"] .ogx-giphy-search {
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: #1b1f27 !important;
    color: #f8fafc !important;
  }

  html.dark .ogx-comment-media,
  html.dark .ogx-giphy-pick,
  body.dark .ogx-comment-media,
  body.dark .ogx-giphy-pick,
  .dark .ogx-comment-media,
  .dark .ogx-giphy-pick,
  [data-theme="dark"] .ogx-comment-media,
  [data-theme="dark"] .ogx-giphy-pick {
    background: rgba(255, 255, 255, 0.08) !important;
  }


  @media (max-width: 700px) {
    .post-show-shell .ogx-comments-panel,
    .ogx-comments-panel {
      max-width: calc(100vw - 16px) !important;
      padding: 12px !important;
      border-radius: 10px !important;
    }

    .ogx-comment {
      grid-template-columns: 34px minmax(0, 1fr) !important;
      column-gap: 8px !important;
    }

    .ogx-avatar {
      width: 34px !important;
      height: 34px !important;
      min-width: 34px !important;
    }

    .ogx-emoji-menu {
      width: 220px !important;
    }
  }
</style>


<style>
  /*
   |--------------------------------------------------------------------------
   | Fix pack: autogrow + transparent icons + GIPHY modal
   |--------------------------------------------------------------------------
   | Bu blok eski tasarım kodu değildir; tek dosyada eksik kalan davranışları
   | tamamlar ve ekrandaki beyaz icon arka planlarını sıfırlar.
   */

  .ogx-comments-panel .ogx-composer-box,
  .ogx-comments-panel .ogx-reply-compose {
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
  }

  .ogx-comments-panel textarea[data-ogx-autogrow],
  .ogx-comments-panel .ogx-comment-input,
  .ogx-comments-panel .ogx-reply-compose textarea {
    height: auto !important;
    min-height: 42px !important;
    max-height: 520px !important;
    overflow-y: hidden !important;
    resize: none !important;
    transition: height 0.08s ease !important;
  }

  .ogx-comments-panel .ogx-reply-compose textarea {
    min-height: 36px !important;
    max-height: 420px !important;
  }

  .ogx-comments-panel .ogx-filter-btn,
  .ogx-comments-panel .ogx-emoji-btn,
  .ogx-comments-panel .ogx-image-btn,
  .ogx-comments-panel .ogx-gif-btn,
  .ogx-comments-panel .ogx-more-btn,
  .ogx-comments-panel .ogx-vote-btn,
  .ogx-comments-panel .ogx-reply-btn {
    appearance: none !important;
    -webkit-appearance: none !important;
    border: 0 !important;
    background: transparent !important;
    background-color: transparent !important;
    box-shadow: none !important;
    outline: none !important;
  }

  .ogx-comments-panel .ogx-filter-btn:hover,
  .ogx-comments-panel .ogx-filter-btn:focus,
  .ogx-comments-panel .ogx-filter-btn.is-open,
  .ogx-comments-panel .ogx-emoji-btn:hover,
  .ogx-comments-panel .ogx-emoji-btn:focus,
  .ogx-comments-panel .ogx-emoji-btn.is-open,
  .ogx-comments-panel .ogx-image-btn:hover,
  .ogx-comments-panel .ogx-image-btn:focus,
  .ogx-comments-panel .ogx-gif-btn:hover,
  .ogx-comments-panel .ogx-gif-btn:focus,
  .ogx-comments-panel .ogx-more-btn:hover,
  .ogx-comments-panel .ogx-more-btn:focus,
  .ogx-comments-panel .ogx-vote-btn:hover,
  .ogx-comments-panel .ogx-reply-btn:hover {
    background: transparent !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }

  .ogx-comments-panel .ogx-gif-btn {
    min-width: 30px !important;
    height: 34px !important;
    padding: 0 4px !important;
    color: #111827 !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 0 !important;
  }

  .ogx-comments-panel .ogx-gif-btn:hover,
  .ogx-comments-panel .ogx-gif-btn:focus {
    color: #2563eb !important;
  }

  .ogx-comments-panel .ogx-submit-btn {
    background: transparent !important;
    background-color: transparent !important;
    color: #9ca3af !important;
    opacity: 0.65 !important;
  }

  .ogx-comments-panel .ogx-composer-form.has-comment-ready .ogx-submit-btn,
  .ogx-comments-panel .ogx-reply-form.has-comment-ready .ogx-submit-btn {
    background: #2563eb !important;
    background-color: #2563eb !important;
    color: #ffffff !important;
    opacity: 1 !important;
  }

  #ogx-giphy-modal[hidden] {
    display: none !important;
  }

  #ogx-giphy-modal.ogx-giphy-modal {
    position: fixed !important;
    inset: 0 !important;
    z-index: 99999 !important;
    display: flex !important;
    align-items: flex-end !important;
    justify-content: center !important;
    padding: 18px !important;
  }

  /* GIF kutusu varsayılan olarak kapalı kalsın.
     Bu kural, alttaki display:flex kuralını hidden durumunda kesin olarak ezer. */
  #ogx-giphy-modal.ogx-giphy-modal[hidden],
  #ogx-giphy-modal[hidden] {
    display: none !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }

  #ogx-giphy-modal .ogx-giphy-backdrop {
    position: absolute !important;
    inset: 0 !important;
    background: rgba(15, 23, 42, 0.45) !important;
  }

  #ogx-giphy-modal .ogx-giphy-dialog {
    position: relative !important;
    width: min(100%, 620px) !important;
    max-height: min(78vh, 620px) !important;
    overflow: hidden !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 16px !important;
    background: #ffffff !important;
    padding: 14px !important;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18) !important;
  }

  #ogx-giphy-modal .ogx-giphy-head {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    margin-bottom: 10px !important;
  }

  #ogx-giphy-modal .ogx-giphy-title {
    color: #111827 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
  }

  #ogx-giphy-modal .ogx-giphy-close {
    width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 50% !important;
    background: transparent !important;
    color: #6b7280 !important;
    font-size: 24px !important;
    line-height: 1 !important;
    cursor: pointer !important;
    box-shadow: none !important;
  }

  #ogx-giphy-modal .ogx-giphy-close:hover {
    color: #111827 !important;
    background: transparent !important;
  }

  #ogx-giphy-modal .ogx-giphy-search {
    width: 100% !important;
    height: 42px !important;
    border: 1px solid #d7dbe0 !important;
    border-radius: 10px !important;
    background: #eef0f3 !important;
    color: #111827 !important;
    padding: 0 12px !important;
    outline: none !important;
    font-size: 14px !important;
    box-shadow: none !important;
  }

  #ogx-giphy-modal .ogx-giphy-search:focus {
    border-color: #2563eb !important;
  }

  #ogx-giphy-modal .ogx-giphy-status {
    min-height: 18px !important;
    margin: 10px 0 !important;
    color: #6b7280 !important;
    font-size: 12px !important;
  }

  #ogx-giphy-modal .ogx-giphy-grid {
    max-height: 430px !important;
    overflow-y: auto !important;
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 8px !important;
  }

  #ogx-giphy-modal .ogx-giphy-pick {
    width: 100% !important;
    border: 0 !important;
    border-radius: 10px !important;
    background: #eef0f3 !important;
    padding: 0 !important;
    overflow: hidden !important;
    cursor: pointer !important;
    box-shadow: none !important;
  }

  #ogx-giphy-modal .ogx-giphy-pick img {
    width: 100% !important;
    height: 112px !important;
    display: block !important;
    object-fit: cover !important;
  }

  .ogx-comments-panel .ogx-giphy-preview-card {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    color: #4b5563 !important;
    font-size: 12px !important;
  }

  .ogx-comments-panel .ogx-giphy-preview-card img {
    width: 74px !important;
    height: 74px !important;
    border-radius: 10px !important;
    object-fit: cover !important;
  }

  .ogx-comments-panel .ogx-giphy-clear {
    border: 0 !important;
    background: transparent !important;
    color: #6b7280 !important;
    cursor: pointer !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    text-decoration: underline !important;
  }

  html.dark #ogx-giphy-modal .ogx-giphy-dialog,
  body.dark #ogx-giphy-modal .ogx-giphy-dialog,
  .dark #ogx-giphy-modal .ogx-giphy-dialog,
  [data-theme="dark"] #ogx-giphy-modal .ogx-giphy-dialog {
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: #111318 !important;
  }

  html.dark #ogx-giphy-modal .ogx-giphy-title,
  body.dark #ogx-giphy-modal .ogx-giphy-title,
  .dark #ogx-giphy-modal .ogx-giphy-title,
  [data-theme="dark"] #ogx-giphy-modal .ogx-giphy-title {
    color: #f8fafc !important;
  }

  html.dark #ogx-giphy-modal .ogx-giphy-search,
  body.dark #ogx-giphy-modal .ogx-giphy-search,
  .dark #ogx-giphy-modal .ogx-giphy-search,
  [data-theme="dark"] #ogx-giphy-modal .ogx-giphy-search {
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: #1b1f27 !important;
    color: #f8fafc !important;
  }

  @media (max-width: 520px) {
    #ogx-giphy-modal.ogx-giphy-modal {
      padding: 10px !important;
    }

    #ogx-giphy-modal .ogx-giphy-dialog {
      border-radius: 16px 16px 0 0 !important;
    }

    #ogx-giphy-modal .ogx-giphy-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
  }
</style>


<script>
(function () {
  window.ogxGrowTextarea = function (textarea) {
    if (!textarea) return;

    /* Main shadcn composer owns its sizing through its inline input handler. */
    if (textarea.classList && textarea.classList.contains('ogx3-textarea')) return;

    var minHeight = 36;
    var maxHeight = Number(textarea.getAttribute('data-ogx-max-height') || 520);

    textarea.style.setProperty('height', 'auto', 'important');
    textarea.style.setProperty('min-height', minHeight + 'px', 'important');
    textarea.style.setProperty('max-height', maxHeight + 'px', 'important');

    var nextHeight = Math.max(textarea.scrollHeight, minHeight);
    nextHeight = Math.min(nextHeight, maxHeight);

    textarea.style.setProperty('height', nextHeight + 'px', 'important');
    textarea.style.setProperty('overflow-y', textarea.scrollHeight > maxHeight ? 'auto' : 'hidden', 'important');

    var box = textarea.closest ? textarea.closest('.ogx-composer-box, .ogx-reply-compose') : null;
    if (box) {
      box.style.setProperty('height', 'auto', 'important');
      box.style.setProperty('min-height', '0', 'important');
    }
  };

  function closestIn(target, selector, root) {
    var el = target && target.closest ? target.closest(selector) : null;
    return el && (!root || root.contains(el)) ? el : null;
  }

  function grow(textarea) {
    window.ogxGrowTextarea(textarea);
  }

  function formHasFile(form) {
    if (!form) return false;
    var fileInput = form.querySelector('[data-comment-file-input]');
    if (fileInput && fileInput.files && fileInput.files.length > 0) return true;

    var imagePreview = form.querySelector('[data-ogx-preview]:not([hidden])');
    var gifPreview = form.querySelector('[data-gif-preview]:not([hidden])');

    return !!(imagePreview || gifPreview);
  }

  function refreshComposer(form) {
    if (!form) return;

    var textarea = form.querySelector('textarea[name="content"]');
    var submit = form.querySelector('[data-ogx-submit-comment]');
    var counter = form.querySelector('[data-ogx-char-count]');
    var textLength = textarea ? textarea.value.length : 0;
    var hasText = textarea ? textarea.value.trim().length > 0 : false;
    var hasFile = formHasFile(form);
    var canSend = hasText || hasFile;

    if (counter) counter.textContent = textLength;

    form.classList.toggle('has-comment-text', hasText);
    form.classList.toggle('has-comment-ready', canSend);

    if (submit) {
      submit.disabled = !canSend;
      submit.setAttribute('aria-disabled', canSend ? 'false' : 'true');
    }
  }

  function closeMenus(root, except) {
    (root || document).querySelectorAll('[data-ogx-filter], [data-comment-more], [data-comment-emoji]').forEach(function (wrap) {
      if (except && wrap === except) return;
      wrap.classList.remove('is-open');
      var trigger = wrap.querySelector('[data-ogx-filter-trigger], [data-comment-more-trigger], [data-comment-emoji-button]');
      var menu = wrap.querySelector('[data-ogx-filter-menu], [data-comment-more-menu], [data-comment-emoji-menu]');
      if (trigger) {
        trigger.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
      }
      if (menu) menu.hidden = true;
    });
  }

  function insertTextAtCursor(textarea, text) {
    if (!textarea) return;

    var start = typeof textarea.selectionStart === 'number' ? textarea.selectionStart : textarea.value.length;
    var end = typeof textarea.selectionEnd === 'number' ? textarea.selectionEnd : textarea.value.length;
    var before = textarea.value.slice(0, start);
    var after = textarea.value.slice(end);

    textarea.value = before + text + after;
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + text.length;
    if (textarea.classList.contains('ogx3-textarea') && typeof textarea.oninput === 'function') {
      textarea.oninput();
    } else {
      grow(textarea);
    }

    var composer = closestIn(textarea, '[data-ogx-composer]', null);
    if (composer) refreshComposer(composer);
  }

  function boot(root) {
    (root || document).querySelectorAll('.ogx-comments-panel textarea, [data-ogx-autogrow]').forEach(grow);
    (root || document).querySelectorAll('[data-ogx-composer]').forEach(refreshComposer);
  }

  document.addEventListener('DOMContentLoaded', function () {
    boot(document);
    setTimeout(function () { boot(document); }, 0);
  });
  boot(document);
  setTimeout(function () { boot(document); }, 0);

  document.addEventListener('input', function (event) {
    var root = closestIn(event.target, '[data-ogx-comments]');
    if (!root) return;

    var textarea = closestIn(event.target, 'textarea', root);
    if (textarea) grow(textarea);

    var composer = closestIn(event.target, '[data-ogx-composer]', root);
    if (composer) refreshComposer(composer);
  }, true);

  document.addEventListener('change', function (event) {
    var root = closestIn(event.target, '[data-ogx-comments]');
    if (!root) return;

    var input = closestIn(event.target, '[data-comment-file-input]', root);
    if (!input) return;

    var form = closestIn(input, 'form', root);
    var preview = root.querySelector(input.getAttribute('data-comment-preview-target') || '');
    if (!preview) return;

    preview.innerHTML = '';

    var file = input.files && input.files[0] ? input.files[0] : null;
    if (!file) {
      preview.hidden = true;
      if (form) refreshComposer(form);
      return;
    }

    var item = document.createElement('span');
    item.className = 'ogx3-preview-item';

    if (file.type && file.type.indexOf('video/') === 0) {
      var video = document.createElement('video');
      video.src = URL.createObjectURL(file);
      video.controls = true;
      item.appendChild(video);
    } else {
      var img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.alt = '';
      item.appendChild(img);
    }

    preview.appendChild(item);
    preview.hidden = false;

    if (form) refreshComposer(form);
  }, true);

  document.addEventListener('submit', function (event) {
    var form = closestIn(event.target, '[data-ogx-composer]', null);
    if (!form) return;

    refreshComposer(form);

    if (!form.classList.contains('has-comment-ready')) {
      event.preventDefault();
      var textarea = form.querySelector('textarea[name="content"]');
      if (textarea) textarea.focus();
    }
  }, true);

  document.addEventListener('click', function (event) {
    var root = closestIn(event.target, '[data-ogx-comments]');
    if (!root) {
      closeMenus(document, null);
      return;
    }

    var fileButton = closestIn(event.target, '[data-comment-file-button]', root);
    if (fileButton) {
      event.preventDefault();
      closeMenus(root, null);
      var input = root.querySelector(fileButton.getAttribute('data-comment-file-target') || '');
      if (input) input.click();
      return;
    }

    var emojiButton = closestIn(event.target, '[data-comment-emoji-button]', root);
    if (emojiButton) {
      event.preventDefault();
      var emojiWrap = emojiButton.closest('[data-comment-emoji]');
      var emojiMenu = emojiWrap ? emojiWrap.querySelector('[data-comment-emoji-menu]') : null;
      var emojiOpen = !(emojiWrap && emojiWrap.classList.contains('is-open'));
      closeMenus(root, emojiWrap);
      if (emojiWrap) emojiWrap.classList.toggle('is-open', emojiOpen);
      emojiButton.classList.toggle('is-open', emojiOpen);
      emojiButton.setAttribute('aria-expanded', emojiOpen ? 'true' : 'false');
      if (emojiMenu) emojiMenu.hidden = !emojiOpen;
      return;
    }

    var emojiItem = closestIn(event.target, '[data-comment-emoji-value]', root);
    if (emojiItem) {
      event.preventDefault();
      var form = closestIn(emojiItem, '[data-ogx-composer]', root) || root.querySelector('#show-comment-form');
      var textarea = form ? form.querySelector('textarea[name="content"]') : root.querySelector('textarea[name="content"]');
      insertTextAtCursor(textarea, emojiItem.getAttribute('data-comment-emoji-value') || emojiItem.textContent || '');
      closeMenus(root, null);
      return;
    }

    var menuTrigger = closestIn(event.target, '[data-ogx-filter-trigger], [data-comment-more-trigger]', root);
    if (menuTrigger) {
      event.preventDefault();
      var wrap = menuTrigger.closest('[data-ogx-filter], [data-comment-more]');
      var menu = wrap ? wrap.querySelector('[data-ogx-filter-menu], [data-comment-more-menu]') : null;
      var open = !(wrap && wrap.classList.contains('is-open'));
      closeMenus(root, wrap);
      if (wrap) wrap.classList.toggle('is-open', open);
      menuTrigger.classList.toggle('is-open', open);
      menuTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (menu) menu.hidden = !open;
      return;
    }

    var toggle = closestIn(event.target, '[data-comment-reply-toggle], [data-comment-edit-toggle]', root);
    if (toggle) {
      event.preventDefault();
      var target = toggle.getAttribute('data-comment-reply-toggle') || toggle.getAttribute('data-comment-edit-toggle');
      var box = target ? root.querySelector(target) : null;
      if (!box) return;
      box.classList.toggle('is-open');
      var textarea = box.querySelector('textarea');
      if (textarea && box.classList.contains('is-open')) {
        grow(textarea);
        setTimeout(function () { grow(textarea); }, 0);
        textarea.focus();
      }
      closeMenus(root, null);
      return;
    }

    var oldRepliesButton = closestIn(event.target, '[data-replies-toggle]', root);
    if (oldRepliesButton) {
      event.preventDefault();
      return;
    }

    var sortButton = closestIn(event.target, '[data-ogx-sort]', root);
    if (sortButton) {
      event.preventDefault();
      var mode = sortButton.getAttribute('data-ogx-sort');
      root.querySelectorAll('[data-ogx-sort]').forEach(function (item) { item.classList.toggle('is-active', item === sortButton); });
      var list = root.querySelector('[data-ogx-comments-list]');
      if (list) {
        var items = Array.from(list.querySelectorAll(':scope > [data-ogx-comment]'));
        items.forEach(function (item) { item.hidden = false; });
        items.sort(function (a, b) {
          if (mode === 'popular') return Number(b.getAttribute('data-ogx-likes') || 0) - Number(a.getAttribute('data-ogx-likes') || 0);
          return Number(b.getAttribute('data-ogx-created') || 0) - Number(a.getAttribute('data-ogx-created') || 0);
        }).forEach(function (item) { list.appendChild(item); });
      }
      closeMenus(root, null);
      return;
    }
  }, true);

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeMenus(document, null);
  }, true);

  document.addEventListener('paste', function (event) {
    var textarea = event.target && event.target.matches && event.target.matches('.ogx-comments-panel textarea') ? event.target : null;
    if (textarea) setTimeout(function () { grow(textarea); refreshComposer(closestIn(textarea, '[data-ogx-composer]', null)); }, 0);
  }, true);

  document.addEventListener('cut', function (event) {
    var textarea = event.target && event.target.matches && event.target.matches('.ogx-comments-panel textarea') ? event.target : null;
    if (textarea) setTimeout(function () { grow(textarea); refreshComposer(closestIn(textarea, '[data-ogx-composer]', null)); }, 0);
  }, true);

  document.addEventListener('click', function (event) {
    var button = event.target && event.target.closest ? event.target.closest('[data-ogx-replies-toggle]') : null;
    if (!button) return;

    var root = button.closest('[data-ogx-comments]');
    if (!root) return;

    var target = button.getAttribute('data-replies-target') || '';
    var replies = null;

    try {
      replies = target ? root.querySelector(target) : null;
    } catch (error) {
      replies = null;
    }

    if (!replies) return;

    event.preventDefault();
    event.stopPropagation();

    var willOpen = replies.classList.contains('is-collapsed');

    replies.classList.toggle('is-collapsed', !willOpen);
    button.classList.toggle('is-open', willOpen);
    button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    button.setAttribute('aria-label', willOpen ? 'Yanıtları gizle' : 'Yanıtları göster');
    button.setAttribute('title', willOpen ? 'Yanıtları gizle' : 'Yanıtları göster');
  }, true);
})();
</script>

<style>
  /* shadcn/base-nova comment composer v3 — the only active main composer skin */
  html body .post-show-shell #show-comment-form.ogx3-composer {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-field {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    grid-template-rows: auto auto auto !important;
    width: 100% !important;
    min-height: 146px !important;
    padding: 16px 18px 14px !important;
    box-sizing: border-box !important;
    border: 1px solid var(--border, #e4e4e7) !important;
    border-radius: 12px !important;
    background: var(--muted, #f4f4f5) !important;
    color: var(--foreground, #18181b) !important;
    box-shadow: none !important;
    transition: border-color 150ms ease, box-shadow 150ms ease !important;
    height: 72px !important;
    overflow: visible !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-field:focus-within {
    border-color: var(--ring, #a1a1aa) !important;
    box-shadow: 0 0 0 2px color-mix(in oklab, var(--ring, #a1a1aa) 18%, transparent) !important;
  }

  html body .post-show-shell #show-comment-form textarea.ogx3-textarea {
    all: unset !important;
    display: block !important;
    width: 100% !important;
    position: static !important;
    flex: 0 0 auto !important;
    height: auto !important;
    min-height: 72px !important;
    max-height: 360px !important;
    padding: 4px 0 10px !important;
    box-sizing: border-box !important;
    overflow-y: auto !important;
    white-space: pre-wrap !important;
    overflow-wrap: anywhere !important;
    font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    font-size: 15px !important;
    font-weight: 400 !important;
    line-height: 24px !important;
    color: var(--foreground, #18181b) !important;
    caret-color: currentColor !important;
    cursor: text !important;
    resize: none !important;
    field-sizing: fixed !important;
  }

  html body .post-show-shell #show-comment-form textarea.ogx3-textarea::placeholder {
    color: var(--muted-foreground, #71717a) !important;
    opacity: 1 !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-toolbar,
  html body .post-show-shell #show-comment-form .ogx3-tools,
  html body .post-show-shell #show-comment-form .ogx3-submit-group {
    display: flex !important;
    align-items: center !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-toolbar {
    justify-content: space-between !important;
    gap: 14px !important;
    width: 100% !important;
    min-height: 38px !important;
    margin-top: 8px !important;
    position: static !important;
    flex: 0 0 auto !important;
    clear: both !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-tools,
  html body .post-show-shell #show-comment-form .ogx3-submit-group {
    gap: 10px !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-icon-button,
  html body .post-show-shell #show-comment-form .ogx3-gif-button,
  html body .post-show-shell #show-comment-form .ogx3-submit {
    all: unset !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-sizing: border-box !important;
    cursor: pointer !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-icon-button {
    width: 30px !important;
    height: 30px !important;
    border-radius: 8px !important;
    color: var(--foreground, #18181b) !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-icon-button:hover {
    background: var(--accent, #f4f4f5) !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-icon-button svg {
    width: 20px !important;
    height: 20px !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-gif-button {
    min-width: 32px !important;
    height: 30px !important;
    border-radius: 8px !important;
    font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    color: var(--foreground, #18181b) !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-counter {
    font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    font-size: 13px !important;
    color: var(--muted-foreground, #71717a) !important;
    white-space: nowrap !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-submit {
    width: 38px !important;
    height: 38px !important;
    border-radius: 999px !important;
    background: var(--muted-foreground, #71717a) !important;
    color: var(--background, #ffffff) !important;
    opacity: .35 !important;
  }

  html body .post-show-shell #show-comment-form.has-comment-ready .ogx3-submit {
    background: var(--foreground, #18181b) !important;
    opacity: 1 !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-submit:disabled {
    cursor: not-allowed !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-submit svg {
    width: 19px !important;
    height: 19px !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-preview {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    margin: 0 0 10px !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-preview[hidden],
  html body .post-show-shell #show-comment-form .ogx3-emoji-menu[hidden],
  html body .post-show-shell #show-comment-form .ogx3-mention-menu[hidden],
  html body .post-show-shell #show-comment-form .ogx3-file-input {
    display: none !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-preview-item {
    position: relative !important;
    width: 76px !important;
    height: 76px !important;
    overflow: hidden !important;
    border: 1px solid var(--border, #e4e4e7) !important;
    border-radius: 10px !important;
    background: var(--background, #ffffff) !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-preview-item :is(img, video) {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-emoji {
    position: relative !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-emoji-menu,
  html body .post-show-shell #show-comment-form .ogx3-mention-menu {
    position: absolute !important;
    z-index: 80 !important;
    padding: 8px !important;
    border: 1px solid var(--border, #e4e4e7) !important;
    border-radius: 12px !important;
    background: var(--popover, #ffffff) !important;
    box-shadow: 0 10px 30px rgb(0 0 0 / .1) !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-emoji-menu {
    left: 0 !important;
    bottom: 38px !important;
    display: grid !important;
    grid-template-columns: repeat(7, 32px) !important;
  }

  html body .post-show-shell #show-comment-form .ogx3-emoji-item {
    all: unset !important;
    display: grid !important;
    place-items: center !important;
    width: 32px !important;
    height: 32px !important;
    border-radius: 7px !important;
    cursor: pointer !important;
  }

  @media (max-width: 640px) {
    html body .post-show-shell #show-comment-form .ogx3-field {
      min-height: 138px !important;
      padding: 14px !important;
    }

    html body .post-show-shell #show-comment-form textarea.ogx3-textarea {
      font-size: 16px !important;
    }
  }
</style>

<style>
  /* base-nova preset: final comment composer surface */
  html body .shadcn-comment-card {
    width: 100% !important;
    max-width: 680px !important;
    margin: 14px auto 0 !important;
    padding: 20px 16px 16px !important;
    overflow: visible !important;
    border: 1px solid #e4e4e7 !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    color: #18181b !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
    font-family: "Roboto", Arial, Helvetica, sans-serif !important;
  }

  html body .shadcn-comment-card > .ogx-panel-header {
    min-height: 24px !important;
    margin: 0 0 28px !important;
  }

  html body .shadcn-comment-card .ogx-comment-count {
    color: #18181b !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    line-height: 24px !important;
  }

  html body .shadcn-comment-card .ogx-filter-btn {
    width: 32px !important;
    height: 32px !important;
    border-radius: 8px !important;
    background: transparent !important;
    color: #27272a !important;
  }

  html body .shadcn-comment-card .ogx-filter-btn:is(:hover, :focus-visible, .is-open) {
    background: #f4f4f5 !important;
    color: #18181b !important;
  }

  html body .shadcn-comment-card .shadcn-comment-composer {
    min-height: 112px !important;
    padding: 16px !important;
    overflow: visible !important;
    border: 1px solid #d4d4d8 !important;
    border-radius: 10px !important;
    background: #f1f3f6 !important;
    box-shadow: none !important;
  }

  html body .shadcn-comment-card .ogx-composer-form {
    margin: 0 12px 12px !important;
  }

  html body .shadcn-comment-card .ogx-comment-input {
    min-height: 44px !important;
    padding: 6px 6px 8px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #18181b !important;
    font-size: 15px !important;
    line-height: 24px !important;
    box-shadow: none !important;
  }

  html body .shadcn-comment-card .ogx-comment-input::placeholder {
    color: #71717a !important;
    opacity: 1 !important;
  }

  html body .shadcn-comment-card .ogx-composer-actions {
    min-height: 36px !important;
    margin-top: 4px !important;
  }

  html body .shadcn-comment-card :is(.ogx-emoji-btn, .ogx-image-btn) {
    width: 32px !important;
    height: 32px !important;
    border-radius: 8px !important;
    color: #27272a !important;
  }

  html body .shadcn-comment-card :is(.ogx-emoji-btn, .ogx-image-btn):is(:hover, :focus-visible) {
    background: #e4e4e7 !important;
    color: #18181b !important;
  }

  html body .shadcn-comment-card .ogx-gif-btn {
    min-width: 32px !important;
    height: 32px !important;
    padding: 0 6px !important;
    border: 0 !important;
    border-radius: 8px !important;
    background: transparent !important;
    color: #27272a !important;
    font-size: 12px !important;
    font-weight: 600 !important;
  }

  html body .shadcn-comment-card .ogx-gif-btn:is(:hover, :focus-visible) {
    background: #e4e4e7 !important;
    color: #18181b !important;
  }

  html body .shadcn-comment-card .ogx-char-counter {
    color: #71717a !important;
    font-size: 13px !important;
    font-weight: 400 !important;
  }

  html body .shadcn-comment-card .ogx-submit-btn {
    width: 36px !important;
    height: 36px !important;
    border: 0 !important;
    border-radius: 9999px !important;
    background: #ffffff !important;
    color: #d4d4d8 !important;
    box-shadow: none !important;
  }

  html body .shadcn-comment-card .ogx-composer-form.has-comment-ready .ogx-submit-btn {
    background: #18181b !important;
    color: #ffffff !important;
  }

  html body .shadcn-comment-card > .ogx-comments-list > .ogx-empty {
    min-height: 52px !important;
    margin: 0 !important;
    padding: 14px 16px !important;
    border: 1px dashed #d4d4d8 !important;
    border-radius: 10px !important;
    background: #fafafa !important;
    color: #71717a !important;
    font-size: 14px !important;
    line-height: 22px !important;
    text-align: center !important;
  }

  @media (max-width: 640px) {
    html body .shadcn-comment-card {
      padding: 16px 10px 12px !important;
      border-left: 0 !important;
      border-right: 0 !important;
      border-radius: 0 !important;
    }

    html body .shadcn-comment-card .ogx-composer-form {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }
  }
</style>
<script>
(function () {
  var modal = document.getElementById('ogx-giphy-modal');
  var panelRoot = document.querySelector('[data-ogx-comments]');

  if (!modal || !panelRoot) return;

  var searchUrl = @json($ogxGiphySearchUrl);
  var input = document.getElementById('ogx-giphy-q');
  var grid = document.getElementById('ogx-giphy-grid');
  var status = document.getElementById('ogx-giphy-status');
  var activeTextarea = null;
  var activePreview = null;
  var debounceTimer = null;
  var GIF_PATTERN = /\[gif:(https?:\/\/(?:media\d*\.giphy\.com|i\.giphy\.com)\/[^\]\s]+)\]/i;

  // Sayfa açıldığında GIF penceresi kesin kapalı başlasın.
  modal.setAttribute('hidden', 'hidden');
  modal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';

  function closest(target, selector) {
    return target && target.closest ? target.closest(selector) : null;
  }

  function dispatchInput(textarea) {
    if (!textarea) return;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    if (window.ogxGrowTextarea) window.ogxGrowTextarea(textarea);
  }

  function insertAtCursor(textarea, text) {
    if (!textarea) return;

    var start = typeof textarea.selectionStart === 'number' ? textarea.selectionStart : textarea.value.length;
    var end = typeof textarea.selectionEnd === 'number' ? textarea.selectionEnd : textarea.value.length;
    var before = textarea.value.slice(0, start);
    var after = textarea.value.slice(end);
    var separator = before && !before.endsWith('\n') ? '\n' : '';

    textarea.value = before + separator + text + '\n' + after;
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + separator.length + text.length + 1;
    dispatchInput(textarea);
  }

  function extractGifUrl(text) {
    if (!text) return '';
    var match = String(text).match(GIF_PATTERN);
    return match ? match[1] : '';
  }

  function renderPreview(container, url) {
    if (!container) return;

    if (!url) {
      container.innerHTML = '';
      container.dataset.giphyUrl = '';
      container.hidden = true;
      return;
    }

    container.hidden = false;
    container.dataset.giphyUrl = url;
    container.innerHTML = ''
      + '<div class="ogx-giphy-preview-card">'
      + '<span>Seçilen GIF:</span>'
      + '<img src="' + url + '" alt="GIF önizlemesi" loading="lazy">'
      + '<button type="button" class="ogx-giphy-clear" data-giphy-clear>Temizle</button>'
      + '</div>';

    var form = container.closest ? container.closest('form') : null;
    if (form) {
      form.classList.add('has-comment-ready');
      var submit = form.querySelector('[data-ogx-submit-comment]');
      if (submit) {
        submit.disabled = false;
        submit.setAttribute('aria-disabled', 'false');
      }
    }
  }

  function setGiphyTriggers(open) {
    panelRoot.querySelectorAll('[data-giphy-trigger]').forEach(function (button) {
      button.classList.toggle('is-open', !!open);
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  function isGiphyOpen() {
    return !modal.hasAttribute('hidden');
  }

  function openGiphy(textarea) {
    activeTextarea = textarea || null;
    activePreview = activeTextarea ? activeTextarea.closest('form').querySelector('[data-giphy-preview], [data-gif-preview]') : null;

    if (activePreview) {
      renderPreview(activePreview, extractGifUrl(activeTextarea ? activeTextarea.value : ''));
    }

    modal.removeAttribute('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    setGiphyTriggers(true);

    if (input) {
      input.focus();
      input.select();
    }
  }

  function closeGiphy() {
    modal.setAttribute('hidden', 'hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    setGiphyTriggers(false);

    if (input) input.value = '';
    if (grid) grid.innerHTML = '';
    if (status) status.textContent = '';
  }

  function clearGifFromTextarea(textarea) {
    if (!textarea) return;
    textarea.value = textarea.value.replace(/\[gif:[^\]\s]+\]\s*/gi, '').trim();
    dispatchInput(textarea);
  }

  async function searchGiphy(query) {
    if (!grid || !status || !searchUrl) return;

    grid.innerHTML = '';
    status.textContent = query ? 'Aranıyor…' : '';

    if (!query) return;

    try {
      var url = searchUrl + '?q=' + encodeURIComponent(query) + '&limit=20';
      var response = await fetch(url, { headers: { 'Accept': 'application/json' } });
      var data = await response.json();

      if (!response.ok) {
        status.textContent = (data && data.message) ? data.message : 'GIF bulunamadı.';
        return;
      }

      var items = Array.isArray(data && data.data) ? data.data : [];
      status.textContent = items.length ? 'Bir GIF seçin:' : 'Sonuç bulunamadı.';

      items.forEach(function (item) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'ogx-giphy-pick';
        button.setAttribute('data-giphy-url', item.url || '');
        button.setAttribute('title', item.title || 'GIF');

        var image = document.createElement('img');
        image.loading = 'lazy';
        image.alt = item.title || 'GIF';
        image.src = item.preview || item.url || '';

        button.appendChild(image);
        grid.appendChild(button);
      });
    } catch (error) {
      status.textContent = 'GIF arama hatası.';
    }
  }

  panelRoot.addEventListener('click', function (event) {
    var trigger = closest(event.target, '[data-giphy-trigger]');
    if (!trigger || !panelRoot.contains(trigger)) return;

    event.preventDefault();
    event.stopPropagation();

    var form = trigger.closest('form');
    var textarea = form ? form.querySelector('textarea[name="content"]') : null;

    if (isGiphyOpen() && activeTextarea === textarea) {
      closeGiphy();
      return;
    }

    openGiphy(textarea);
  }, true);

  modal.addEventListener('click', function (event) {
    if (event.target === modal || closest(event.target, '[data-giphy-close]') || closest(event.target, '.ogx-giphy-backdrop')) {
      event.preventDefault();
      closeGiphy();
      return;
    }

    var pick = closest(event.target, '[data-giphy-url]');
    if (!pick) return;

    var url = pick.getAttribute('data-giphy-url') || '';

    if (url) {
      clearGifFromTextarea(activeTextarea);
      insertAtCursor(activeTextarea, '[gif:' + url + ']');
      renderPreview(activePreview, url);
    }

    closeGiphy();
  }, true);

  document.addEventListener('click', function (event) {
    var clearButton = closest(event.target, '[data-giphy-clear]');
    if (!clearButton) return;

    var preview = closest(clearButton, '[data-giphy-preview], [data-gif-preview]');
    var form = preview ? preview.closest('form') : null;
    var textarea = form ? form.querySelector('textarea[name="content"]') : null;

    clearGifFromTextarea(textarea);
    renderPreview(preview, '');
  }, true);

  if (input) {
    input.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () {
        searchGiphy((input.value || '').trim());
      }, 300);
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !modal.hidden) closeGiphy();
  }, true);
})();
</script>

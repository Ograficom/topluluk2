@php
    $popularComments = collect($popularComments ?? [])
        ->filter(function ($comment) {
            $rawContent = (string) ($comment->content ?? '');

            if ($rawContent === '') {
                return false;
            }

            if (preg_match('/\[(gif|img):(https?:\/\/[^\]\s]+|data:image\/[^\]\s]+)\]/i', $rawContent)) {
                return false;
            }

            return trim(strip_tags($rawContent)) !== '';
        })
        ->values();

    $popularTags = collect($popularTags ?? []);
    $mostViewedPosts = collect($mostViewedPosts ?? []);
    $mostReactedPosts = collect($mostReactedPosts ?? []);
    $commentsEnabled = $commentsEnabled ?? true;
    $tagsEnabled = $tagsEnabled ?? true;
    $trendingEnabled = $trendingEnabled ?? true;
@endphp

<style>
    .ografi-sidebar-force {
        width: 304px !important;
        max-width: 304px !important;
        min-width: 304px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        box-sizing: border-box !important;
    }

    .ografi-sidebar-force,
    .ografi-sidebar-force * {
        box-sizing: border-box !important;
    }

    .ografi-sidebar-card {
        width: 100% !important;
        background: #ffffff !important;
        border: 1px solid rgba(15, 15, 18, 0.09) !important;
        border-radius: 10px !important;
        padding: 18px 13px !important;
        box-shadow: none !important;
        overflow: visible !important;
        position: relative !important;
    }

    .ografi-sidebar-card + .ografi-sidebar-card {
        margin-top: 14px !important;
    }

    .ografi-sidebar-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        margin: 0 0 14px 0 !important;
        padding: 0 !important;
    }

    .ografi-sidebar-title {
        margin: 0 !important;
        color: #000000 !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
        line-height: 1.3 !important;
    }

    .ografi-comment-list {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    .ografi-comment-item {
        display: block !important;
        width: 100% !important;
        padding: 0 0 14px 0 !important;
        margin: 0 !important;
        color: inherit !important;
        text-decoration: none !important;
        background: transparent !important;
        border-bottom: 1px solid #e5e7eb !important;
        position: relative !important;
    }

    .ografi-comment-item + .ografi-comment-item {
        padding-top: 13px !important;
    }

    .ografi-comment-item:last-child {
        padding-bottom: 0 !important;
        border-bottom: 0 !important;
    }

    .ografi-comment-hover-box {
        display: block !important;
        width: auto !important;
        margin: -6px -13px !important;
        padding: 6px 13px !important;
        border-radius: 10px !important;
        transition: background-color 0.15s ease !important;
    }

    .ografi-comment-item:hover .ografi-comment-hover-box {
        background-color: #f4f4f5 !important;
    }

    .ografi-comment-top {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        width: 100% !important;
        min-width: 0 !important;
        position: relative !important;
    }

    .ografi-comment-avatar,
    .ografi-comment-avatar-fallback {
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        max-width: 34px !important;
        flex: 0 0 34px !important;
        border-radius: 999px !important;
    }

    .ografi-comment-avatar-link {
        display: inline-flex !important;
        width: 34px !important;
        height: 34px !important;
        min-width: 34px !important;
        text-decoration: none !important;
    }

    .ografi-comment-avatar {
        display: block !important;
        object-fit: cover !important;
        background: #f1f5f9 !important;
    }

    .ografi-comment-avatar-fallback {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #eef2ff !important;
        color: #8b9cff !important;
        font-size: 10px !important;
        font-weight: 400 !important;
        line-height: 1 !important;
        text-transform: uppercase !important;
    }

    .ografi-comment-meta {
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        gap: 2px !important;
        min-width: 0 !important;
        flex: 1 1 auto !important;
        overflow: hidden !important;
    }

    .ografi-comment-author {
        display: block !important;
        max-width: 210px !important;
        overflow: hidden !important;
        color: #000000 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    .ografi-comment-post {
        display: block !important;
        max-width: 210px !important;
        overflow: hidden !important;
        color: #000000 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        text-decoration: none !important;
    }

    .ografi-comment-body-link {
        display: block !important;
        color: inherit !important;
        text-decoration: none !important;
    }

    .ografi-comment-text {
        margin: 8px 0 0 0 !important;
        color: #000000 !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        line-height: 1.35 !important;
        word-break: break-word !important;
    }

    .ografi-comment-time {
        margin: 5px 0 0 0 !important;
        color: #64748b !important;
        font-size: 10px !important;
        font-weight: 400 !important;
        line-height: 1.2 !important;
    }

    /* Populer etiketler: sade dikey liste, ad solda / sayi sagda. */
    .ografi-tag-list {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    .ografi-tag-row {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        width: auto !important;
        margin: 0 -13px !important;
        padding: 3px 13px !important;
        border-bottom: 0 !important;
        border-radius: 8px !important;
        color: inherit !important;
        text-decoration: none !important;
        background: transparent !important;
        transition: background-color 0.15s ease !important;
    }

    .ografi-tag-row:hover,
    .ografi-tag-row:focus-visible {
        background: #f4f4f5 !important;
    }

    .ografi-tag-row__name {
        display: block !important;
        min-width: 0 !important;
        overflow: hidden !important;
        color: #0d0d10 !important;
        font-size: 14.5px !important;
        font-weight: 700 !important;
        line-height: 1.25 !important;
        letter-spacing: -0.01em !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .ografi-tag-row__count {
        display: block !important;
        flex: 0 0 auto !important;
        color: #2563eb !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 1.25 !important;
    }

    .ografi-empty-state {
        padding: 10px 0 !important;
        color: #64748b !important;
        font-size: 12px !important;
        line-height: 1.4 !important;
        text-align: left !important;
    }

    .ografi-widget-empty {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        padding: 14px 0 6px !important;
    }

    .ografi-widget-empty__svg {
        width: 100% !important;
        max-width: 220px !important;
        overflow: visible !important;
    }

    .ografi-widget-empty__line {
        fill: none !important;
        stroke: #e55361 !important;
        stroke-width: 12 !important;
        stroke-linecap: round !important;
        stroke-linejoin: round !important;
        /* Onceki "ciz - sil - tekrar" desenninde animasyonun buyuk bolumu
           (dashoffset 0'dan -1200'e giderken) cizgi tamamen gorunmez
           oluyordu - rastgele bir anda bakinca kutu bos gorunuyordu.
           Bunun yerine 1200px'lik yolun uzerinde surekli kayan kisa bir
           segment kullaniyoruz: her zaman bir kismi gorunur, hic bos an
           olmuyor. */
        stroke-dasharray: 250 950 !important;
        animation: ografiWidgetHeartbeatDraw 2.5s linear infinite !important;
    }

    @keyframes ografiWidgetHeartbeatDraw {
        0% { stroke-dashoffset: 0; }
        100% { stroke-dashoffset: -1200; }
    }

    .ografi-widget-empty__text {
        margin-top: 10px !important;
        color: #64748b !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-align: center !important;
        letter-spacing: -0.01em !important;
    }

    .dark .ografi-widget-empty__text,
    [data-theme="dark"] .ografi-widget-empty__text {
        color: #9ca3af !important;
    }

    /* Populer gonderiler: kucuk kapak resmi + baslik listesi (Haberler stili). */
    .ografi-trend-group + .ografi-trend-group {
        margin-top: 18px !important;
    }

    .ografi-trend-list {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    .ografi-trend-item {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        width: 100% !important;
        padding: 9px 0 !important;
        border-bottom: 1px solid #e5e7eb !important;
        color: inherit !important;
        text-decoration: none !important;
    }

    .ografi-trend-item:last-child {
        padding-bottom: 0 !important;
        border-bottom: 0 !important;
    }

    .ografi-trend-thumb {
        display: block !important;
        width: 56px !important;
        height: 56px !important;
        min-width: 56px !important;
        border-radius: 10px !important;
        object-fit: cover !important;
        background: #f1f5f9 !important;
        order: 2 !important;
    }

    .ografi-trend-body {
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        gap: 3px !important;
        min-width: 0 !important;
        flex: 1 1 auto !important;
        order: 1 !important;
    }

    .ografi-trend-title {
        display: -webkit-box !important;
        -webkit-line-clamp: 2 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        color: #0d0d10 !important;
        font-size: 13.5px !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
        line-height: 1.32 !important;
        transition: color 0.15s ease !important;
    }

    .ografi-trend-item:hover .ografi-trend-title,
    .ografi-trend-item:focus-visible .ografi-trend-title {
        color: #2563eb !important;
    }

    .dark .ografi-sidebar-card,
    [data-theme="dark"] .ografi-sidebar-card {
        background: #111827 !important;
    }

    .dark .ografi-sidebar-title,
    [data-theme="dark"] .ografi-sidebar-title,
    .dark .ografi-comment-author,
    [data-theme="dark"] .ografi-comment-author,
    .dark .ografi-comment-post,
    [data-theme="dark"] .ografi-comment-post,
    .dark .ografi-comment-text,
    [data-theme="dark"] .ografi-comment-text,
    .dark .ografi-tag-row__name,
    [data-theme="dark"] .ografi-tag-row__name,
    .dark .ografi-trend-title,
    [data-theme="dark"] .ografi-trend-title {
        color: #ffffff !important;
    }

    .dark .ografi-comment-item,
    [data-theme="dark"] .ografi-comment-item,
    .dark .ografi-trend-item,
    [data-theme="dark"] .ografi-trend-item {
        border-bottom-color: rgba(255, 255, 255, 0.12) !important;
    }

    .dark .ografi-tag-row__count,
    [data-theme="dark"] .ografi-tag-row__count {
        color: #60a5fa !important;
    }

    .dark .ografi-comment-time,
    [data-theme="dark"] .ografi-comment-time,
    .dark .ografi-empty-state,
    [data-theme="dark"] .ografi-empty-state {
        color: #9ca3af !important;
    }

    .dark .ografi-trend-thumb,
    [data-theme="dark"] .ografi-trend-thumb {
        background: #27272a !important;
    }

    @media (max-width: 640px) {
        .ografi-sidebar-force {
            width: calc(100vw - 32px) !important;
            max-width: calc(100vw - 32px) !important;
            min-width: calc(100vw - 32px) !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .ografi-sidebar-card {
            padding: 18px 13px !important;
            border-radius: 6px !important;
        }

        .ografi-comment-author,
        .ografi-comment-post {
            max-width: 215px !important;
        }
    }
</style>

<div class="ografi-sidebar-force">
    @if ($commentsEnabled)
    <section class="ografi-sidebar-card">
        <div class="ografi-sidebar-header">
            <h3 class="ografi-sidebar-title">
                {{ __('site.widgets.latest_comments') }}
            </h3>
        </div>

        <div class="ografi-comment-list">
            @forelse ($popularComments as $comment)
                @php
                    $commentUser = $comment->user;
                    $commentAuthor = $commentUser?->name ?? $comment->author_name ?? __('site.common.community_member');
                    $commentAvatar = $commentUser?->profile_photo_url;

                    $commentInitials = collect(preg_split('/\s+/', trim((string) $commentAuthor), -1, PREG_SPLIT_NO_EMPTY))
                        ->take(2)
                        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                        ->implode('');

                    $commentInitials = $commentInitials !== '' ? $commentInitials : 'CM';

                    $commentUrl = $comment->post?->slug
                        ? route('blog.post', $comment->post->slug) . '#comments'
                        : '#';

                    $commentTitle = \Illuminate\Support\Str::limit(
                        (string) ($comment->post?->title ?? __('site.common.untitled_post')),
                        31
                    );

                    $commentText = \Illuminate\Support\Str::limit(
                        trim(strip_tags((string) $comment->content)),
                        42
                    );

                    $commentUsername = $commentUser?->username ?? null;
                    $profileUrl = $commentUsername ? route('profile.show', $commentUsername) : '#';
                @endphp

                <div class="ografi-comment-item">
                    <div class="ografi-comment-hover-box">
                        <div class="ografi-comment-top">
                            <a href="{{ $profileUrl }}" class="ografi-comment-avatar-link">
                                @if ($commentAvatar)
                                    <img
                                        src="{{ $commentAvatar }}"
                                        alt="{{ $commentAuthor }}"
                                        class="ografi-comment-avatar"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                @else
                                    <span class="ografi-comment-avatar-fallback">
                                        {{ $commentInitials }}
                                    </span>
                                @endif
                            </a>

                            <div class="ografi-comment-meta">
                                <a href="{{ $profileUrl }}" class="ografi-comment-author">
                                    {{ $commentAuthor }}
                                </a>

                                <a href="{{ $commentUrl }}" class="ografi-comment-post">
                                    {{ $commentTitle }}
                                </a>
                            </div>
                        </div>

                        <a href="{{ $commentUrl }}" class="ografi-comment-body-link">
                            <p class="ografi-comment-text">
                                {{ $commentText }}
                            </p>

                            <div class="ografi-comment-time">
                                {{ optional($comment->created_at)->diffForHumans() ?? __('site.common.recently') }}
                            </div>
                        </a>
                    </div>
                </div>
            @empty
                <div class="ografi-widget-empty">
                    <svg class="ografi-widget-empty__svg" viewBox="0 0 800 250" aria-hidden="true">
                        <path
                            class="ografi-widget-empty__line"
                            d="M20 125 L100 125 L135 35 L180 230 L235 65 L270 150 L305 110 L340 125 L780 125"
                        />
                    </svg>
                    <div class="ografi-widget-empty__text">
                        {{ __('site.widgets.no_comments') }}
                    </div>
                </div>
            @endforelse
        </div>
    </section>
    @endif

    @if ($trendingEnabled)
    <section class="ografi-sidebar-card">
        <div class="ografi-sidebar-header">
            <h3 class="ografi-sidebar-title">
                {{ __('site.widgets.trending_posts') }}
            </h3>
        </div>

        @php
            $trendGroups = [
                ['label' => __('site.widgets.most_viewed'), 'posts' => $mostViewedPosts, 'metric' => 'views'],
                ['label' => __('site.widgets.most_reacted'), 'posts' => $mostReactedPosts, 'metric' => 'reactions'],
            ];
        @endphp

        @foreach ($trendGroups as $group)
            <div class="ografi-trend-group">
                <div class="ografi-trend-list">
                    @forelse ($group['posts'] as $trendPost)
                        <a href="{{ route('blog.post', $trendPost->slug) }}" class="ografi-trend-item">
                            @if ($trendPost->featured_image_url)
                                <img
                                    src="{{ $trendPost->featured_image_url }}"
                                    alt="{{ $trendPost->title }}"
                                    class="ografi-trend-thumb"
                                    loading="lazy"
                                    decoding="async"
                                    referrerpolicy="no-referrer"
                                    onerror="this.replaceWith(Object.assign(document.createElement('span'), {className: 'ografi-trend-thumb'}))"
                                >
                            @else
                                <span class="ografi-trend-thumb"></span>
                            @endif

                            <div class="ografi-trend-body">
                                <span class="ografi-trend-title">{{ \Illuminate\Support\Str::limit($trendPost->title, 60) }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="ografi-widget-empty">
                            <svg class="ografi-widget-empty__svg" viewBox="0 0 800 250" aria-hidden="true">
                                <path
                                    class="ografi-widget-empty__line"
                                    d="M20 125 L100 125 L135 35 L180 230 L235 65 L270 150 L305 110 L340 125 L780 125"
                                />
                            </svg>
                            <div class="ografi-widget-empty__text">
                                {{ __('site.widgets.no_posts') }}
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </section>
    @endif

    @if ($tagsEnabled)
    <section class="ografi-sidebar-card">
        <div class="ografi-sidebar-header">
            <h3 class="ografi-sidebar-title">
                {{ __('site.widgets.popular_tags') }}
            </h3>
        </div>

        <div class="ografi-tag-list">
            @forelse ($popularTags as $tag)
                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="ografi-tag-row">
                    <span class="ografi-tag-row__name">#{{ $tag->name }}</span>
                    <span class="ografi-tag-row__count">{{ number_format((int) $tag->posts_count) }}</span>
                </a>
            @empty
                <div class="ografi-widget-empty">
                    <svg class="ografi-widget-empty__svg" viewBox="0 0 800 250" aria-hidden="true">
                        <path
                            class="ografi-widget-empty__line"
                            d="M20 125 L100 125 L135 35 L180 230 L235 65 L270 150 L305 110 L340 125 L780 125"
                        />
                    </svg>
                    <div class="ografi-widget-empty__text">
                        {{ __('site.widgets.no_tags') }}
                    </div>
                </div>
            @endforelse
        </div>
    </section>
    @endif
</div>

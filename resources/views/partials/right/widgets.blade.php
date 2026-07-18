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
        border: 0 !important;
        border-radius: 6px !important;
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
        font-size: 13px !important;
        font-weight: 400 !important;
        line-height: 1.2 !important;
    }

    .ografi-sidebar-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #059669 !important;
        font-size: 18px !important;
        line-height: 1 !important;
    }

    .ografi-sidebar-icon-hash {
        color: #000000 !important;
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
        width: 29px !important;
        height: 29px !important;
        min-width: 29px !important;
        max-width: 29px !important;
        flex: 0 0 29px !important;
        border-radius: 999px !important;
    }

    .ografi-comment-avatar-link {
        display: inline-flex !important;
        width: 29px !important;
        height: 29px !important;
        min-width: 29px !important;
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
        max-height: 29px !important;
        flex: 1 1 auto !important;
        overflow: hidden !important;
    }

    .ografi-comment-author {
        display: block !important;
        max-width: 210px !important;
        overflow: hidden !important;
        color: #000000 !important;
        font-size: 9px !important;
        font-weight: 700 !important;
        line-height: 1.15 !important;
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
        font-size: 9px !important;
        font-weight: 700 !important;
        line-height: 1.15 !important;
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

    .ografi-tag-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 13px !important;
        width: 100% !important;
    }

    .ografi-tag-item {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        color: inherit !important;
        text-decoration: none !important;
        background: transparent !important;
        border: 0 !important;
    }

    .ografi-tag-name {
        display: block !important;
        min-width: 0 !important;
        overflow: hidden !important;
        color: #000000 !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .ografi-tag-count {
        display: block !important;
        flex: 0 0 auto !important;
        color: #000000 !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
    }

    .ografi-empty-state {
        padding: 10px 0 !important;
        color: #64748b !important;
        font-size: 12px !important;
        line-height: 1.4 !important;
        text-align: left !important;
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
    .dark .ografi-tag-name,
    [data-theme="dark"] .ografi-tag-name,
    .dark .ografi-tag-count,
    [data-theme="dark"] .ografi-tag-count {
        color: #ffffff !important;
    }

    .dark .ografi-sidebar-icon-hash,
    [data-theme="dark"] .ografi-sidebar-icon-hash {
        color: #ffffff !important;
    }

    .dark .ografi-comment-item,
    [data-theme="dark"] .ografi-comment-item {
        border-bottom-color: rgba(255, 255, 255, 0.12) !important;
    }

    .dark .ografi-comment-time,
    [data-theme="dark"] .ografi-comment-time,
    .dark .ografi-empty-state,
    [data-theme="dark"] .ografi-empty-state {
        color: #9ca3af !important;
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
    <section class="ografi-sidebar-card">
        <div class="ografi-sidebar-header">
            <h3 class="ografi-sidebar-title">
                {{ __('site.widgets.latest_comments') }}
            </h3>

            <iconify-icon
                icon="lucide:zap"
                class="ografi-sidebar-icon"
            ></iconify-icon>
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
                            <div class="ografi-comment-text">
                                {{ $commentText }}
                            </div>

                            <div class="ografi-comment-time">
                                {{ optional($comment->created_at)->diffForHumans() ?? __('site.common.recently') }}
                            </div>
                        </a>
                    </div>
                </div>
            @empty
                <div class="ografi-empty-state">
                    {{ __('site.widgets.no_comments') }}
                </div>
            @endforelse
        </div>
    </section>

    <section class="ografi-sidebar-card">
        <div class="ografi-sidebar-header">
            <h3 class="ografi-sidebar-title">
                {{ __('site.widgets.popular_tags') }}
            </h3>

            <iconify-icon
                icon="lucide:hash"
                class="ografi-sidebar-icon ografi-sidebar-icon-hash"
            ></iconify-icon>
        </div>

        <div class="ografi-tag-list">
            @forelse ($popularTags as $tag)
                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="ografi-tag-item">
                    <span class="ografi-tag-name">
                        #{{ $tag->name }}
                    </span>

                    <span class="ografi-tag-count">
                        {{ number_format((int) $tag->posts_count) }}
                    </span>
                </a>
            @empty
                <div class="ografi-empty-state">
                    {{ __('site.widgets.no_tags') }}
                </div>
            @endforelse
        </div>
    </section>
</div>

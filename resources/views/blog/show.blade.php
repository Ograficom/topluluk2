@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $postUrl = request()->url();
    if (\Illuminate\Support\Facades\Route::has('blog.post')) {
        try {
            $postUrl = route('blog.post', $post);
        } catch (\Throwable $e) {
            $postUrl = request()->url();
        }
    }

    $author = $post->author;
    $authorName = (string) (optional($author)->name ?? __('site.post_show.fallback_author'));
    $authorAvatar = optional($author)->profile_photo_url ?? null;
    $authorInitials = collect(preg_split('/\s+/', trim($authorName), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');
    $authorInitials = $authorInitials !== '' ? $authorInitials : 'U';
    $authorUrl = null;
    if ($author && !empty($author->username) && \Illuminate\Support\Facades\Route::has('users.show')) {
        $authorUrl = route('users.show', ['user' => $author->username]);
    }
    $featuredImage = $post->featured_image_url ?? $post->featured_image ?? null;
    // Sayfanın 500 hatasına düşmemesi için görsel ölçüsü yardımcı sınıfı güvenli çalıştırılır.
    $featuredImageWidth = 1200;
    $featuredImageHeight = 675;
    try {
        if (class_exists(\App\Support\OptimizedImage::class) && method_exists(\App\Support\OptimizedImage::class, 'dimensions')) {
            [$featuredImageWidth, $featuredImageHeight] = \App\Support\OptimizedImage::dimensions($featuredImage, [1200, 675]);
        }
    } catch (\Throwable $e) {
        $featuredImageWidth = 1200;
        $featuredImageHeight = 675;
    }
    $postPublishedDate = $post->published_at ?: $post->created_at;
    $postPublishedMoment = null;
    try {
        $postPublishedMoment = $postPublishedDate ? \Illuminate\Support\Carbon::parse($postPublishedDate) : null;
    } catch (\Throwable $e) {
        $postPublishedMoment = null;
    }

    $publishedLabel = '-';
    if ($postPublishedMoment) {
        $publishedLabel = $postPublishedMoment->greaterThanOrEqualTo(now()->subMonth())
            ? $postPublishedMoment->diffForHumans()
            : $postPublishedMoment->translatedFormat('d M Y');
    }
    $categoryName = trim((string) (optional($post->category)->name ?? ''));
    $hasCategory = $categoryName !== '';
    $categoryUrl = null;
    if ($post->category && !empty($post->category->slug) && \Illuminate\Support\Facades\Route::has('blog.category')) {
        $categoryUrl = route('blog.category', ['category' => $post->category->slug]);
    }
    $categoryAvatar = optional($post->category)->profile_image_url
        ?? optional($post->category)->profile_image
        ?? null;
    $authorUsername = trim((string) (optional($author)->username ?? ''));
    $readPostShowModelText = function ($model, array $keys): string {
        if (!$model) {
            return '';
        }

        foreach ($keys as $key) {
            $value = data_get($model, $key);

            if (($value === null || $value === '') && is_object($model) && method_exists($model, 'getAttribute') && !str_contains($key, '.')) {
                $value = $model->getAttribute($key);
            }

            if (is_array($value) || is_object($value)) {
                continue;
            }

            $value = trim(strip_tags((string) $value));
            $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    };

    $readPostShowModelCount = function ($model, array $keys = [], array $relations = []): int {
        if (!$model) {
            return 0;
        }

        foreach ($keys as $key) {
            $value = data_get($model, $key);

            if (($value === null || $value === '') && is_object($model) && method_exists($model, 'getAttribute') && !str_contains($key, '.')) {
                $value = $model->getAttribute($key);
            }

            if (is_numeric($value)) {
                return max(0, (int) $value);
            }
        }

        foreach ($relations as $relation) {
            try {
                if (!is_object($model)) {
                    continue;
                }

                if (method_exists($model, 'relationLoaded') && $model->relationLoaded($relation) && method_exists($model, 'getRelation')) {
                    $loadedRelation = $model->getRelation($relation);
                    if ($loadedRelation instanceof \Illuminate\Support\Collection || is_array($loadedRelation) || $loadedRelation instanceof \Countable) {
                        return max(0, count($loadedRelation));
                    }
                }

                if (method_exists($model, $relation)) {
                    $relationQuery = $model->{$relation}();
                    if (is_object($relationQuery) && method_exists($relationQuery, 'count')) {
                        return max(0, (int) $relationQuery->count());
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return 0;
    };

    $postShowCardAuthor = $author;
    try {
        if ($postShowCardAuthor && method_exists($postShowCardAuthor, 'fresh') && method_exists($postShowCardAuthor, 'getKey') && $postShowCardAuthor->getKey()) {
            $freshAuthor = $postShowCardAuthor->fresh();
            if ($freshAuthor) {
                $postShowCardAuthor = $freshAuthor;
            }
        }
        if ($postShowCardAuthor && method_exists($postShowCardAuthor, 'profile') && method_exists($postShowCardAuthor, 'loadMissing')) {
            $postShowCardAuthor->loadMissing('profile');
        }
    } catch (\Throwable $e) {
        $postShowCardAuthor = $author;
    }

    $postShowCardCategory = $post->category ?? null;
    try {
        if ($postShowCardCategory && method_exists($postShowCardCategory, 'fresh') && method_exists($postShowCardCategory, 'getKey') && $postShowCardCategory->getKey()) {
            $freshCategory = $postShowCardCategory->fresh();
            if ($freshCategory) {
                $postShowCardCategory = $freshCategory;
            }
        }
    } catch (\Throwable $e) {
        $postShowCardCategory = $post->category ?? null;
    }

    $authorBio = $readPostShowModelText($postShowCardAuthor, [
        'bio',
        'profile.bio',
        'profile_bio',
        'about',
        'profile.about',
        'headline',
        'profile.headline',
        'description',
        'profile.description',
    ]);
    $authorBio = $authorBio !== '' ? Str::limit($authorBio, 130) : '';
    $authorCover = optional($postShowCardAuthor)->cover_photo_url
        ?? optional($postShowCardAuthor)->cover_image_url
        ?? optional($postShowCardAuthor)->banner_url
        ?? optional($postShowCardAuthor)->cover_photo
        ?? null;
    $categoryCover = optional($postShowCardCategory)->cover_image_url
        ?? optional($postShowCardCategory)->cover_photo_url
        ?? optional($postShowCardCategory)->banner_url
        ?? optional($postShowCardCategory)->cover_image
        ?? optional($postShowCardCategory)->cover
        ?? null;
    $categoryDescription = $readPostShowModelText($postShowCardCategory, [
        'description',
        'bio',
        'excerpt',
        'summary',
        'about',
    ]);
    $categoryDescription = $categoryDescription !== '' ? Str::limit($categoryDescription, 110) : '';
    $categoryInitials = collect(preg_split('/\s+/', $categoryName, -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');
    $categoryBadgeText = $hasCategory ? ($categoryInitials !== '' ? $categoryInitials : 'AI') : 'AI';
    $siteName = trim((string) config('app.name', 'Ografi'));
    $seoTitleBase = trim((string) ($post->meta_title ?: $post->title ?: 'Gonderi'));
    $seoTitle = collect([
        $seoTitleBase,
        $hasCategory ? $categoryName : null,
        $siteName,
    ])->filter()->unique(fn ($part) => mb_strtolower((string) $part))->implode(' | ');
    $rawDescriptionSource = trim((string) (
        $post->meta_description
        ?? $post->excerpt
        ?? $post->summary
        ?? strip_tags((string) ($post->content ?? ''))
    ));
    $rawDescriptionSource = html_entity_decode($rawDescriptionSource, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $rawDescriptionSource = preg_replace('/\[(gif|img):([^\]\s]+)\]/i', ' ', $rawDescriptionSource) ?? $rawDescriptionSource;
    $rawDescriptionSource = preg_replace('/\s+/u', ' ', trim($rawDescriptionSource)) ?? trim($rawDescriptionSource);
    $description = trim((string) ($post->meta_description ?? ''));
    if ($description === '' && $rawDescriptionSource !== '') {
        $description = Str::limit($rawDescriptionSource, 155);
    }
    if ($description === '') {
        $description = Str::limit(
            trim($seoTitleBase . ($hasCategory ? ' - ' . $categoryName : '') . ' yazisini ' . ($siteName !== '' ? $siteName . ' uzerinde okuyun.' : ' okuyun.')),
            155
        );
    }

    $reactionPills = collect($reactionSummary ?? [])->filter(function ($row) {
        return (int) ($row['count'] ?? 0) > 0;
    })->values();
    $showReactionTypes = collect($reactionTypes ?? [])->values();
    // HIZ OPTIMIZASYONU:
    // Eski dosyada her sayfa açılışında bütün yorumlar badword listesiyle tekrar taranıyor
    // ve eşleşen yorumlar view içinde delete() ediliyordu. Bu hem sayfayı yavaşlatır hem de
    // listeleme ekranında beklenmeyen veritabanı işlemi çalıştırır. Moderasyon kayıt/güncelleme
    // sırasında yapılmalı; bu sayfada sadece gelen yorumlar render edilir.
    $showBlockedWordsForJs = collect(config('badwords.words', []))
        ->flatten()
        ->map(fn ($word) => trim((string) $word))
        ->filter()
        ->unique()
        ->values();

    $commentsList = collect($post->comments ?? [])->values();

    $commentsCount = $commentsList->count();
    $commentsGrouped = $commentsList->groupBy('parent_id');
    $rootComments = $commentsList->whereNull('parent_id')->values();
    $commentsDisabled = (bool) ($post->comments_disabled ?? false);
    $isNsfw = (bool) ($post->is_nsfw ?? false);

    $dashboardPreferences = session('dashboard_preferences', []);
    $showMatureContent = (bool) ($dashboardPreferences['show_mature'] ?? false);
    $blurMatureContent = (bool) ($dashboardPreferences['blur_mature'] ?? true);
    $renderNsfwBlur = $isNsfw && $blurMatureContent;

    $recommendedList = collect($recommendedPosts ?? [])->take(2);
    $postShareUrl = $postUrl;
    $postShareTitle = (string) ($post->title ?? '');
    $postShareUid = 'show_share_' . substr(md5(($post->id ?? 'post') . uniqid('', true)), 0, 8);
    $postEditedToggleId = 'show_edit_' . substr(md5(($post->id ?? 'post') . 'edit'), 0, 8);
    $postReactionMenuId = 'show_reactions_' . substr(md5(($post->id ?? 'post') . 'reactions'), 0, 8);
    $postShowStatsModalId = 'show_stats_' . substr(md5(($post->id ?? 'post') . 'stats'), 0, 8);
    $postShowViewer = auth()->user();
    $commentComposerAvatar = optional($postShowViewer)->profile_photo_url ?? null;
    $commentComposerName = (string) (optional($postShowViewer)->name ?? 'U');
    $commentComposerInitials = collect(preg_split('/\s+/', trim($commentComposerName), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');
    $commentComposerInitials = $commentComposerInitials !== '' ? $commentComposerInitials : 'U';
    $postShowIsOwnPost = $postShowViewer && (int) $postShowViewer->id === (int) ($post->author_id ?? 0);
    // Route isimleri ortamdan ortama değişebildiği için direkt route() çağrısı sayfayı 500'e düşürmesin.
    $postShowEditUrl = null;
    $postShowDeleteAction = null;
    $postShowReportUrl = null;

    if (\Illuminate\Support\Facades\Route::has('blog.post.edit')) {
        try {
            $postShowEditUrl = route('blog.post.edit', $post);
        } catch (\Throwable $e) {
            $postShowEditUrl = null;
        }
    }

    if (\Illuminate\Support\Facades\Route::has('blog.post.destroy')) {
        try {
            $postShowDeleteAction = route('blog.post.destroy', $post);
        } catch (\Throwable $e) {
            $postShowDeleteAction = null;
        }
    }

    if ($postShowViewer && !$postShowIsOwnPost && \Illuminate\Support\Facades\Route::has('blog.post.report.form')) {
        try {
            $postShowReportUrl = route('blog.post.report.form', ['post' => $post->slug]);
        } catch (\Throwable $e) {
            $postShowReportUrl = null;
        }
    }

    $postShowCanOpenMenu = (bool) (
        $postShowViewer
        && (
            $postShowReportUrl
            || ($postShowIsOwnPost && ($postShowEditUrl || $postShowDeleteAction))
        )
    );

    $postShowLoginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');
    $postShowIsFollowingAuthor = false;
    if ($postShowViewer && $author && !$postShowIsOwnPost) {
        $explicitFollowState = optional($author)->is_followed_by_viewer
            ?? optional($author)->viewer_has_followed
            ?? optional($author)->is_following
            ?? null;

        if ($explicitFollowState !== null) {
            $postShowIsFollowingAuthor = (bool) $explicitFollowState;
        } elseif (method_exists($postShowViewer, 'isFollowing')) {
            try {
                $postShowIsFollowingAuthor = (bool) $postShowViewer->isFollowing($author);
            } catch (\Throwable $e) {
                $postShowIsFollowingAuthor = false;
            }
        } elseif (method_exists($author, 'followers') && method_exists($postShowViewer, 'getKey')) {
            try {
                $postShowIsFollowingAuthor = (bool) $author->followers()->whereKey($postShowViewer->getKey())->exists();
            } catch (\Throwable $e) {
                $postShowIsFollowingAuthor = false;
            }
        }
    }

    $postShowFollowAction = null;
    if ($author && !$postShowIsOwnPost) {
        $postShowFollowRouteCandidates = [
            'users.follow',
            'user.follow',
            'users.follow.store',
            'follow.user',
            'profile.follow',
            'followers.store',
        ];
        foreach ($postShowFollowRouteCandidates as $routeName) {
            if (!\Illuminate\Support\Facades\Route::has($routeName)) {
                continue;
            }

            $routeParameterSets = [
                ['user' => $author],
                ['user' => $authorUsername !== '' ? $authorUsername : optional($author)->id],
                ['username' => $authorUsername],
                ['id' => optional($author)->id],
                $author,
            ];

            foreach ($routeParameterSets as $routeParameters) {
                try {
                    $postShowFollowAction = route($routeName, $routeParameters);
                    break 2;
                } catch (\Throwable $e) {
                    $postShowFollowAction = null;
                }
            }
        }
    }
    if (!$postShowFollowAction && $author && !$postShowIsOwnPost) {
        $authorFollowKey = $authorUsername !== '' ? $authorUsername : (string) optional($author)->id;
        if ($authorFollowKey !== '') {
            $postShowFollowAction = url('/users/' . rawurlencode($authorFollowKey) . '/follow');
        }
    }
    $postShowCanFollowAuthor = (bool) ($postShowViewer && $author && !$postShowIsOwnPost && $postShowFollowAction);
    $postShowFollowButtonLabel = $postShowIsFollowingAuthor ? 'Takip ediliyor' : 'Takip et';

    // MentionService yoksa / bozulursa sayfa çökmesin; içerik güvenli şekilde basılsın.
    $linkifyPostShowHtml = function (string $html): string {
        try {
            if (class_exists(\App\Services\MentionService::class)) {
                $service = app(\App\Services\MentionService::class);
                if (is_object($service) && method_exists($service, 'linkifyHtml')) {
                    return (string) $service->linkifyHtml($html);
                }
            }
        } catch (\Throwable $e) {
        }

        return $html;
    };

    $normalizeShowContentHeadings = function (string $html): string {
        $html = trim($html);
        if ($html === '') {
            return $html;
        }

        // DOMDocument yerine hafif regex: içerikte h1 varsa h2'ye düşürür.
        $html = preg_replace('/<\s*h1(\s[^>]*)?>/iu', '<h2$1>', $html) ?? $html;
        $html = preg_replace('/<\s*\/\s*h1\s*>/iu', '</h2>', $html) ?? $html;

        return $html;
    };
    $postContentHtml = $normalizeShowContentHeadings($linkifyPostShowHtml((string) ($post->content ?? '')));

    $normalizePostShowMediaUrl = function (?string $url): ?string {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($url, ['data:', 'http://', 'https://', '//'])) {
            return $url;
        }

        if (\Illuminate\Support\Str::startsWith($url, '/storage/')) {
            return url($url);
        }

        if (\Illuminate\Support\Str::startsWith($url, 'storage/')) {
            return url('/' . ltrim($url, '/'));
        }

        if (\Illuminate\Support\Str::startsWith($url, '/')) {
            return url($url);
        }

        if (preg_match('/\.(png|jpe?g|gif|webp|svg|mp4|webm|ogg|mov)(?:\?.*)?$/i', $url)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($url);
        }

        return $url;
    };

    $sanitizePostShowEmbedUrl = function (?string $url): ?string {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $allowedHosts = [
            'www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com',
            'www.instagram.com', 'instagram.com', 'www.tiktok.com', 'tiktok.com',
            'player.vimeo.com', 'www.dailymotion.com', 'dailymotion.com', 'player.twitch.tv',
            'www.facebook.com', 'facebook.com', 'twitframe.com', 'vine.co',
        ];

        return in_array($host, $allowedHosts, true) ? $url : null;
    };

    $buildPostShowEmbedUrlFromUrl = function (?string $value) use ($sanitizePostShowEmbedUrl): ?string {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        }

        $parts = parse_url($value);
        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '/');
        $pathParts = array_values(array_filter(explode('/', trim($path, '/'))));
        parse_str((string) ($parts['query'] ?? ''), $query);
        $parentHost = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();

        if ($host === 'youtu.be') {
            $id = $pathParts[0] ?? null;
            return $id ? $sanitizePostShowEmbedUrl('https://www.youtube.com/embed/' . rawurlencode($id)) : null;
        }

        if (str_ends_with($host, 'youtube.com') || str_ends_with($host, 'youtube-nocookie.com')) {
            $id = null;
            if (($pathParts[0] ?? '') === 'watch') {
                $id = (string) ($query['v'] ?? '');
            } elseif (in_array(($pathParts[0] ?? ''), ['shorts', 'embed'], true)) {
                $id = $pathParts[1] ?? null;
            }
            return $id ? $sanitizePostShowEmbedUrl('https://www.youtube.com/embed/' . rawurlencode($id)) : null;
        }

        if (str_ends_with($host, 'instagram.com')) {
            $kind = $pathParts[0] ?? '';
            $code = $pathParts[1] ?? '';
            if (in_array($kind, ['p', 'reel', 'tv'], true) && $code !== '') {
                return $sanitizePostShowEmbedUrl('https://www.instagram.com/' . $kind . '/' . rawurlencode($code) . '/embed');
            }
        }

        if (str_ends_with($host, 'tiktok.com')) {
            if (($pathParts[0] ?? '') === 'embed' && ($pathParts[1] ?? '') === 'v2' && !empty($pathParts[2])) {
                return $sanitizePostShowEmbedUrl('https://www.tiktok.com/embed/v2/' . rawurlencode((string) $pathParts[2]));
            }
            if (($pathParts[1] ?? '') === 'video' && !empty($pathParts[2])) {
                return $sanitizePostShowEmbedUrl('https://www.tiktok.com/embed/v2/' . rawurlencode((string) $pathParts[2]));
            }
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            $id = (($pathParts[0] ?? '') === 'video') ? ($pathParts[1] ?? null) : ($pathParts[0] ?? null);
            return $id && preg_match('/^\d+$/', $id) ? $sanitizePostShowEmbedUrl('https://player.vimeo.com/video/' . $id) : null;
        }

        if (str_ends_with($host, 'dailymotion.com')) {
            $id = (($pathParts[0] ?? '') === 'video') ? ($pathParts[1] ?? null) : null;
            return $id ? $sanitizePostShowEmbedUrl('https://www.dailymotion.com/embed/video/' . rawurlencode($id)) : null;
        }

        if ($host === 'dai.ly') {
            $id = $pathParts[0] ?? null;
            return $id ? $sanitizePostShowEmbedUrl('https://www.dailymotion.com/embed/video/' . rawurlencode($id)) : null;
        }

        if (in_array($host, ['twitch.tv', 'www.twitch.tv'], true)) {
            if (($pathParts[0] ?? '') === 'videos' && !empty($pathParts[1])) {
                return $sanitizePostShowEmbedUrl('https://player.twitch.tv/?video=v' . rawurlencode((string) $pathParts[1]) . '&parent=' . rawurlencode((string) $parentHost));
            }
            if (count($pathParts) >= 3 && ($pathParts[1] ?? '') === 'clip' && !empty($pathParts[2])) {
                return $sanitizePostShowEmbedUrl('https://player.twitch.tv/?clip=' . rawurlencode((string) $pathParts[2]) . '&parent=' . rawurlencode((string) $parentHost));
            }
        }

        if ($host === 'clips.twitch.tv') {
            $clip = $pathParts[0] ?? null;
            return $clip ? $sanitizePostShowEmbedUrl('https://player.twitch.tv/?clip=' . rawurlencode((string) $clip) . '&parent=' . rawurlencode((string) $parentHost)) : null;
        }

        if (str_ends_with($host, 'facebook.com') || str_ends_with($host, 'fb.watch')) {
            return $sanitizePostShowEmbedUrl('https://www.facebook.com/plugins/video.php?href=' . rawurlencode($value) . '&show_text=false');
        }

        if (in_array($host, ['x.com', 'www.x.com', 'mobile.x.com', 'twitter.com', 'www.twitter.com', 'mobile.twitter.com'], true) || str_ends_with($host, '.x.com') || str_ends_with($host, '.twitter.com')) {
            if (in_array('status', $pathParts, true) || in_array('statuses', $pathParts, true)) {
                return $sanitizePostShowEmbedUrl('https://twitframe.com/show?url=' . rawurlencode($value));
            }
        }

        if (str_ends_with($host, 'vine.co') && ($pathParts[0] ?? '') === 'v' && !empty($pathParts[1])) {
            return $sanitizePostShowEmbedUrl('https://vine.co/v/' . rawurlencode((string) $pathParts[1]) . '/embed/simple');
        }

        return null;
    };

    $postShowContentJson = $post->content_json ?? null;
    if (is_string($postShowContentJson)) {
        $decodedPostShowContentJson = json_decode($postShowContentJson, true);
        $postShowContentJson = is_array($decodedPostShowContentJson) ? $decodedPostShowContentJson : null;
    }

    $postShowJsonBlocks = collect(is_array($postShowContentJson) ? ($postShowContentJson['blocks'] ?? []) : []);
    $postShowJsonContentHtml = $postShowJsonBlocks
        ->map(function ($block) use ($linkifyPostShowHtml, $normalizeShowContentHeadings, $normalizePostShowMediaUrl, $buildPostShowEmbedUrlFromUrl, $sanitizePostShowEmbedUrl) {
            if (!is_array($block)) {
                return null;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            $renderTextHtml = function ($value) use ($linkifyPostShowHtml, $normalizeShowContentHeadings) {
                $html = trim((string) $value);
                if ($html === '') {
                    return '';
                }

                return $normalizeShowContentHeadings($linkifyPostShowHtml($html));
            };

            $imageUrls = function ($source) use ($normalizePostShowMediaUrl) {
                $urls = [];
                foreach ((array) $source as $entry) {
                    if (is_array($entry)) {
                        $urls[] = data_get($entry, 'file.url') ?? data_get($entry, 'url') ?? data_get($entry, 'src') ?? data_get($entry, 'image');
                    } elseif (is_string($entry)) {
                        $urls[] = $entry;
                    }
                }

                return collect($urls)->map($normalizePostShowMediaUrl)->filter()->unique()->values();
            };

            if (in_array($type, ['paragraph', 'text'], true)) {
                $html = $renderTextHtml($data['text'] ?? $data['content'] ?? '');
                if ($html === '') {
                    return null;
                }

                return '<p>' . $html . '</p>';
            }

            if ($type === 'header') {
                $level = (int) ($data['level'] ?? 2);
                $level = max(2, min(4, $level));
                $html = $renderTextHtml($data['text'] ?? '');
                if ($html === '') {
                    return null;
                }

                return '<h' . $level . '>' . $html . '</h' . $level . '>';
            }

            if ($type === 'quote') {
                $html = $renderTextHtml($data['text'] ?? $data['caption'] ?? '');
                return $html !== '' ? '<blockquote>' . $html . '</blockquote>' : null;
            }

            if ($type === 'list') {
                $style = (string) ($data['style'] ?? 'unordered');
                $tag = $style === 'ordered' ? 'ol' : 'ul';
                $items = collect($data['items'] ?? [])->map(function ($item) use ($renderTextHtml) {
                    if (is_array($item)) {
                        $item = $item['content'] ?? $item['text'] ?? implode(' ', array_filter($item));
                    }
                    $html = $renderTextHtml($item);
                    return $html !== '' ? '<li>' . $html . '</li>' : null;
                })->filter()->implode('');

                return $items !== '' ? '<' . $tag . '>' . $items . '</' . $tag . '>' : null;
            }

            if ($type === 'checklist') {
                $items = collect($data['items'] ?? [])->map(function ($item) use ($renderTextHtml) {
                    $text = is_array($item) ? ($item['text'] ?? '') : $item;
                    $checked = is_array($item) && (bool) ($item['checked'] ?? false);
                    $html = $renderTextHtml($text);
                    if ($html === '') {
                        return null;
                    }

                    return '<li><span class="ps-check-dot">' . ($checked ? '✓' : '•') . '</span><span>' . $html . '</span></li>';
                })->filter()->implode('');

                return $items !== '' ? '<ul class="ps-checklist">' . $items . '</ul>' : null;
            }

            if ($type === 'table') {
                $rows = collect($data['content'] ?? [])->map(function ($row) {
                    $cells = collect((array) $row)->map(fn ($cell) => '<td>' . e((string) $cell) . '</td>')->implode('');
                    return $cells !== '' ? '<tr>' . $cells . '</tr>' : null;
                })->filter()->implode('');

                return $rows !== '' ? '<div class="ps-table-wrap"><table>' . $rows . '</table></div>' : null;
            }

            if ($type === 'image') {
                $url = $normalizePostShowMediaUrl(data_get($data, 'file.url') ?? data_get($data, 'url') ?? data_get($data, 'src'));
                $caption = trim((string) ($data['caption'] ?? ''));
                if (!$url) {
                    return null;
                }

                return '<figure class="ps-full-media ps-full-media--image"><img src="' . e($url) . '" alt="' . e(strip_tags($caption) ?: 'Gonderi gorseli') . '" loading="lazy" decoding="async">' . ($caption !== '' ? '<figcaption>' . $renderTextHtml($caption) . '</figcaption>' : '') . '</figure>';
            }

            if (in_array($type, ['gallery', 'carousel', 'slider'], true)) {
                $images = $imageUrls($data['images'] ?? $data['items'] ?? $data['slides'] ?? []);
                if ($images->isEmpty()) {
                    return null;
                }

                return '<div class="ps-full-gallery">' . $images->map(fn ($url) => '<figure class="ps-full-media ps-full-media--gallery"><img src="' . e($url) . '" alt="Gonderi gorseli" loading="lazy" decoding="async"></figure>')->implode('') . '</div>';
            }

            if (in_array($type, ['embed', 'socialEmbed'], true)) {
                $raw = trim((string) ($data['src'] ?? $data['embed'] ?? $data['source'] ?? $data['url'] ?? ''));
                $embed = $buildPostShowEmbedUrlFromUrl($raw) ?? $sanitizePostShowEmbedUrl($raw);
                if (!$embed) {
                    return $raw !== '' ? '<p><a href="' . e($raw) . '" target="_blank" rel="nofollow noopener noreferrer">' . e($raw) . '</a></p>' : null;
                }

                return '<div class="ps-full-media ps-full-media--embed"><iframe src="' . e($embed) . '" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>';
            }

            if ($type === 'video') {
                $url = $normalizePostShowMediaUrl(data_get($data, 'file.url') ?? data_get($data, 'url') ?? data_get($data, 'src'));
                if (!$url) {
                    return null;
                }

                return '<div class="ps-full-media ps-full-media--video"><video src="' . e($url) . '" controls playsinline preload="metadata"></video></div>';
            }

            if ($type === 'raw') {
                $html = trim((string) ($data['html'] ?? ''));
                return $html !== '' ? '<div class="ps-full-raw">' . $html . '</div>' : null;
            }

            if ($type === 'delimiter') {
                return '<hr class="ps-full-delimiter">';
            }

            return null;
        })
        ->filter()
        ->implode("\n");

    $postShowFullContentHtml = trim($postShowJsonContentHtml) !== ''
        ? $postShowJsonContentHtml
        : $postContentHtml;
    $linkPreview = $post->link_preview ?? null;
    if (is_object($linkPreview)) {
        $linkPreview = (array) $linkPreview;
    }
    $linkPreview = is_array($linkPreview) ? $linkPreview : null;
    $linkPreviewUrl = trim((string) data_get($linkPreview, 'url', ''));
    $linkPreviewRawHost = $linkPreviewUrl !== '' ? (parse_url($linkPreviewUrl, PHP_URL_HOST) ?: '') : '';
    $linkPreviewHost = preg_replace('/^www\./i', '', $linkPreviewRawHost) ?: $linkPreviewRawHost;
    $linkPreviewFavicon = trim((string) (
        data_get($linkPreview, 'favicon_url')
        ?? data_get($linkPreview, 'favicon')
        ?? data_get($linkPreview, 'icon_url')
        ?? data_get($linkPreview, 'site_icon')
        ?? data_get($linkPreview, 'logo_url')
        ?? ''
    ));

    if ($linkPreviewFavicon !== '') {
        if (\Illuminate\Support\Str::startsWith($linkPreviewFavicon, '//')) {
            $linkPreviewFavicon = 'https:' . $linkPreviewFavicon;
        } elseif (\Illuminate\Support\Str::startsWith($linkPreviewFavicon, '/')) {
            $linkPreviewScheme = parse_url($linkPreviewUrl, PHP_URL_SCHEME) ?: 'https';
            $linkPreviewFavicon = $linkPreviewRawHost !== ''
                ? $linkPreviewScheme . '://' . $linkPreviewRawHost . $linkPreviewFavicon
                : url($linkPreviewFavicon);
        } elseif (!\Illuminate\Support\Str::startsWith($linkPreviewFavicon, ['http://', 'https://', 'data:image/'])) {
            $linkPreviewScheme = parse_url($linkPreviewUrl, PHP_URL_SCHEME) ?: 'https';
            $linkPreviewFavicon = $linkPreviewRawHost !== ''
                ? $linkPreviewScheme . '://' . $linkPreviewRawHost . '/' . ltrim($linkPreviewFavicon, '/')
                : '';
        }
    }

    if ($linkPreviewFavicon === '' && $linkPreviewRawHost !== '') {
        $linkPreviewFavicon = 'https://www.google.com/s2/favicons?domain=' . rawurlencode($linkPreviewRawHost) . '&sz=64';
    }

    $hasPostShowSourcePreview = $linkPreviewUrl !== '';
    $postShowSourceDisplayName = $linkPreviewHost !== ''
        ? $linkPreviewHost
        : trim((string) data_get($linkPreview, 'site_name', ''));
    $postShowSourceDisplayName = $postShowSourceDisplayName !== '' ? $postShowSourceDisplayName : 'Harici kaynak';

    $resolvePostShowQuotePost = function (?string $url) use ($post) {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        if ($path === '') {
            return null;
        }

        $parts = array_values(array_filter(explode('/', $path)));
        $slug = (string) end($parts);
        if ($slug === '' || in_array($slug, ['blog', 'posts', 'tr'], true)) {
            return null;
        }

        if ((string) ($post->slug ?? '') === $slug) {
            return null;
        }

        try {
            if (!class_exists(\App\Models\Post::class)) {
                return null;
            }

            return \App\Models\Post::query()
                ->with([
                    'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                    'category:id,name,slug,profile_image',
                ])
                ->where('slug', $slug)
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    };
    $extractPostShowQuoteImage = function ($quotePost) use ($normalizePostShowMediaUrl): ?string {
        if (!$quotePost) {
            return null;
        }

        $image = $normalizePostShowMediaUrl($quotePost->featured_image_url ?? $quotePost->featured_image ?? null);
        if ($image) {
            return $image;
        }

        $contentJson = $quotePost->content_json ?? null;
        if (is_string($contentJson)) {
            $decoded = json_decode($contentJson, true);
            $contentJson = is_array($decoded) ? $decoded : null;
        }

        if (is_array($contentJson)) {
            $image = collect($contentJson['blocks'] ?? [])
                ->flatMap(function ($block) {
                    $type = (string) data_get($block, 'type', '');
                    $data = data_get($block, 'data', []);
                    $urls = [];

                    if ($type === 'image') {
                        $urls[] = data_get($data, 'file.url') ?? data_get($data, 'url') ?? data_get($data, 'src') ?? data_get($data, 'image');
                    }

                    if (in_array($type, ['gallery', 'carousel', 'slider'], true)) {
                        foreach ((array) (data_get($data, 'images') ?? data_get($data, 'items') ?? data_get($data, 'slides') ?? []) as $entry) {
                            $urls[] = is_array($entry)
                                ? (data_get($entry, 'file.url') ?? data_get($entry, 'url') ?? data_get($entry, 'src') ?? data_get($entry, 'image'))
                                : $entry;
                        }
                    }

                    return $urls;
                })
                ->map($normalizePostShowMediaUrl)
                ->filter()
                ->first();

            if ($image) {
                return $image;
            }
        }

        $content = (string) ($quotePost->content ?? '');
        if ($content !== '' && preg_match('/<img[^>]+(?:src|data-src|data-original)=["\']([^"\']+)["\']/i', $content, $matches)) {
            return $normalizePostShowMediaUrl($matches[1] ?? null);
        }

        return null;
    };
    $postShowQuotePost = $resolvePostShowQuotePost($linkPreviewUrl);
    $postShowQuoteUrl = $linkPreviewUrl;
    $postShowQuoteTitle = trim((string) (
        $postShowQuotePost?->title
        ?? data_get($linkPreview, 'title', '')
    ));
    $postShowQuoteDescription = trim(strip_tags((string) (
        $postShowQuotePost?->excerpt
        ?? data_get($linkPreview, 'description', '')
    )));
    if ($postShowQuoteDescription === '' && $postShowQuotePost) {
        $postShowQuoteDescription = trim(strip_tags((string) ($postShowQuotePost->content ?? '')));
        $postShowQuoteDescription = preg_replace('/\s+/u', ' ', $postShowQuoteDescription) ?? $postShowQuoteDescription;
    }
    $postShowQuoteDescription = $postShowQuoteDescription !== '' ? Str::limit($postShowQuoteDescription, 190) : '';
    $postShowQuoteImage = $extractPostShowQuoteImage($postShowQuotePost)
        ?? $normalizePostShowMediaUrl((string) data_get($linkPreview, 'image_url', ''));
    $postShowQuoteAuthor = $postShowQuotePost?->author;
    $postShowQuoteAuthorName = trim((string) ($postShowQuoteAuthor?->name ?? data_get($linkPreview, 'site_name', $linkPreviewHost)));
    $postShowQuoteAuthorName = $postShowQuoteAuthorName !== '' ? $postShowQuoteAuthorName : 'Ografi';
    $postShowQuoteAuthorAvatar = $postShowQuoteAuthor?->profile_photo_url ?? null;
    $postShowQuoteCategory = $postShowQuotePost?->category;
    $postShowQuoteCategoryAvatar = $postShowQuoteCategory?->profile_image_url ?? $postShowQuoteCategory?->profile_image ?? null;
    $postShowQuoteTime = $postShowQuotePost?->published_at?->diffForHumans()
        ?? $postShowQuotePost?->created_at?->diffForHumans()
        ?? '';
    $postShowQuoteInitials = collect(preg_split('/\s+/', trim($postShowQuoteAuthorName), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->implode('');
    $postShowQuoteInitials = $postShowQuoteInitials !== '' ? $postShowQuoteInitials : 'OG';
    $hasPostShowQuoteCard = $postShowQuoteUrl !== '' && ($postShowQuoteTitle !== '' || $postShowQuoteDescription !== '' || $postShowQuoteImage);
    if ($hasPostShowQuoteCard && Str::contains(Str::lower(strip_tags($postShowFullContentHtml)), ['alinti:', 'alıntı:'])) {
        $escapedQuoteUrl = preg_quote($postShowQuoteUrl, '/');
        $escapedQuoteTitle = preg_quote($postShowQuoteTitle, '/');
        $postShowFullContentHtml = preg_replace('/<p>\s*(?:Alinti|Alıntı):.*?<\/p>/isu', '', $postShowFullContentHtml) ?? $postShowFullContentHtml;
        if ($escapedQuoteUrl !== '') {
            $postShowFullContentHtml = preg_replace('/<p>\s*(?:<a[^>]+>)?' . $escapedQuoteUrl . '(?:<\/a>)?\s*<\/p>/isu', '', $postShowFullContentHtml) ?? $postShowFullContentHtml;
        }
        if ($escapedQuoteTitle !== '') {
            $postShowFullContentHtml = preg_replace('/<p>\s*' . $escapedQuoteTitle . '\s*<\/p>/isu', '', $postShowFullContentHtml) ?? $postShowFullContentHtml;
        }
        $postShowFullContentHtml = trim($postShowFullContentHtml);
    }
    $showVerified = (bool) (
        optional($author)->is_verified
        ?? optional($author)->verification_badge
        ?? optional($author)->verification_badge_svg
    );
    $formatShowMetric = function (int $value): string {
        if ($value >= 1000000) {
            $formatted = number_format($value / 1000000, $value >= 10000000 ? 0 : 1);

            return rtrim(rtrim($formatted, '0'), '.') . 'M';
        }

        if ($value >= 1000) {
            $formatted = number_format($value / 1000, $value >= 10000 ? 0 : 1);

            return rtrim(rtrim($formatted, '0'), '.') . 'K';
        }

        return number_format($value);
    };
    $showStatsCompact = function (int $value) use ($formatShowMetric): string {
        return $formatShowMetric(max(0, $value));
    };
    $showStatsFull = function (int $value): string {
        return number_format(max(0, $value), 0, ',', '.');
    };
    $showViewsCount = $readPostShowModelCount($post, [
        'views_count',
        'view_count',
        'views',
        'impressions_count',
    ]);
    $showReactionsCount = $readPostShowModelCount($post, [
        'reactions_count',
        'reaction_count',
    ], ['reactions']);
    if ($showReactionsCount === 0) {
        $showReactionsCount = (int) $reactionPills->sum('count');
    }
    $showBookmarksCount = $readPostShowModelCount($post, [
        'bookmarks_count',
        'bookmarkers_count',
        'saved_count',
        'saves_count',
    ], ['bookmarkers', 'bookmarks']);
    $showSharesCount = $readPostShowModelCount($post, [
        'shares_count',
        'share_count',
        'shared_count',
    ], ['shares']);
    $showStatsTotalCount = max(0, $showViewsCount + $showReactionsCount + $commentsCount + $showBookmarksCount + $showSharesCount);
    $showViewCountDisplay = $showStatsCompact($showViewsCount);
    $showVoteCountDisplay = $showStatsCompact($showReactionsCount);
    $showStatsTotalDisplay = $showStatsCompact($showStatsTotalCount);
    $showStatsTotalFullDisplay = $showStatsFull($showStatsTotalCount);
    $showViewsFullDisplay = $showStatsFull($showViewsCount);
    $showReactionsFullDisplay = $showStatsFull($showReactionsCount);
    $showCommentsFullDisplay = $showStatsFull($commentsCount);
    $showBookmarksFullDisplay = $showStatsFull($showBookmarksCount);
    $showSharesFullDisplay = $showStatsFull($showSharesCount);
    $showReactionPageSize = 7;
    $showReactionItems = $reactionPills->take($showReactionPageSize)->values();
    $showReactionHiddenItems = $reactionPills->slice($showReactionPageSize)->values();
    $showReactionOverflowCount = $showReactionHiddenItems->count();
    $showReactionMoreMenuId = 'show_reaction_more_' . substr(md5(($post->id ?? 'post') . 'reaction_more'), 0, 8);
    $showDefaultReactionType = $showReactionTypes
        ->first(fn ($type) => trim((string) data_get($type, 'short_code', '')) !== '' || data_get($type, 'id'));
    $showReactionAvatarPool = $commentsList
        ->map(fn ($comment) => optional($comment->user)->profile_photo_url ?? null)
        ->filter()
        ->unique()
        ->take(2)
        ->values();
    $showReactionAvatarOverflow = max($commentsCount - $showReactionAvatarPool->count(), 0);
    $showTags = collect($post->tags ?? [])->take(8)->values();
    $renderShowReactionIcon = function ($value, $labelText = null) {
        if (!is_string($value) || trim($value) === '') {
            return e($labelText ?: '?');
        }

        $trimmedValue = trim($value);
        $resolvedValue = $trimmedValue;
        if (
            !\Illuminate\Support\Str::startsWith($resolvedValue, ['http://', 'https://', '//', '/'])
            && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $resolvedValue)
        ) {
            $resolvedValue = \Illuminate\Support\Str::startsWith($resolvedValue, 'storage/')
                ? url('/' . ltrim($resolvedValue, '/'))
                : asset('storage/' . ltrim($resolvedValue, '/'));
        }

        $hasHtml = preg_match('/<\s*(img|svg|iconify-icon)/i', $trimmedValue);
        $isImage = preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $resolvedValue)
            || \Illuminate\Support\Str::startsWith($resolvedValue, ['http://', 'https://', '/storage', '/uploads', '/']);

        if ($hasHtml) {
            return $trimmedValue;
        }

        if ($isImage) {
            return '<img src="' . e($resolvedValue) . '" alt="' . e($labelText ?: 'reaction') . '" width="28" height="28" class="ps-reaction-media" style="width:28px;height:28px;border-radius:999px;object-fit:cover;display:block;">';
        }

        return e($trimmedValue);
    };
    // HIZ OPTIMIZASYONU: Eski showContentBlocks DOMDocument ayrıştırması template içinde kullanılmıyordu; kaldırıldı.

    $postPublishedAt = $post->published_at ?: $post->created_at;

    // Düzenleme kalemi sadece gerçekten düzenleme bilgisi olan gönderilerde gösterilir.
    // updated_at tek başına kullanılmaz; çünkü görüntülenme/istatistik gibi sistem güncellemeleri de updated_at'i değiştirebilir.
    $postEditedAt = $post->edited_at ?? null;
    $postEditedReason = trim((string) ($post->edited_reason ?? ''));

    $parsePostShowDate = static function ($value) {
        try {
            return $value ? \Illuminate\Support\Carbon::parse($value) : null;
        } catch (\Throwable $e) {
            return null;
        }
    };

    $postEditedMoment = $parsePostShowDate($postEditedAt);

    $isPostEdited = (bool) (
        $postEditedMoment
        || $postEditedReason !== ''
        || (bool) ($post->is_edited ?? false)
        || (bool) ($post->edited ?? false)
        || (bool) ($post->was_edited ?? false)
    );

    $postEditedAtLabel = $isPostEdited && $postEditedMoment
        ? $postEditedMoment->translatedFormat('d.m.Y H:i')
        : null;

    $recentComments = $commentsList->sortByDesc('created_at')->take(4)->values();
    $popularTags = collect($post->tags ?? [])->take(8)->values();
    $showCategoryCollection = collect($categories ?? [])
        ->whenEmpty(fn ($collection) => $hasCategory ? collect([$post->category]) : collect())
        ->take(8)
        ->values();
    $postShowTagRouteAvailable = \Illuminate\Support\Facades\Route::has('blog.index');


    $stripSchemaText = function ($value): string {
        $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\[(gif|img|video):([^\]\s]+)\]/iu', ' ', $text) ?? $text;
        $text = preg_replace('/<(br|\/p|\/div|\/li|\/blockquote|\/h[1-6])[^>]*>/iu', "\n", $text) ?? $text;
        $text = trim(strip_tags($text));
        $text = preg_replace("/\r\n?|\n/u", "\n", $text) ?? $text;
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */u', "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    };

    $schemaNormalizeUrl = function (?string $url): ?string {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://', '//'])) {
            return \Illuminate\Support\Str::startsWith($url, '//') ? 'https:' . $url : $url;
        }

        if (\Illuminate\Support\Str::startsWith($url, '/storage/')) {
            return url($url);
        }

        if (\Illuminate\Support\Str::startsWith($url, 'storage/')) {
            return url('/' . ltrim($url, '/'));
        }

        if (\Illuminate\Support\Str::startsWith($url, '/')) {
            return url($url);
        }

        if (preg_match('/\.(png|jpe?g|gif|webp|svg|mp4|webm|ogg|mov)(?:\?.*)?$/i', $url)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($url);
        }

        return null;
    };

    $seoSiteUrl = rtrim((string) config('app.url', ''), '/');
    if ($seoSiteUrl === '' || preg_match('#^https?://(127\.0\.0\.1|localhost)(?::\d+)?#i', $seoSiteUrl)) {
        $seoSiteUrl = 'https://ografi.com';
    }

    $seoNormalizePublicUrl = function (?string $url) use ($seoSiteUrl): ?string {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $url = preg_replace('#^https?://(?:127\.0\.0\.1(?::\d+)?|localhost(?::\d+)?)#i', $seoSiteUrl, $url) ?? $url;

        if (\Illuminate\Support\Str::startsWith($url, '//')) {
            return 'https:' . $url;
        }

        if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return rtrim($seoSiteUrl, '/') . '/' . ltrim($url, '/');
    };

    $postUrl = $seoNormalizePublicUrl($postUrl) ?: $postUrl;
    $postShareUrl = $postUrl;

    $discussionForumPostText = $stripSchemaText($postShowFullContentHtml ?: ($post->content ?? $description));
    if ($discussionForumPostText === '') {
        $discussionForumPostText = $description;
    }

    $discussionForumImages = collect([$featuredImage])
        ->merge($postShowJsonBlocks->flatMap(function ($block) use ($schemaNormalizeUrl) {
            if (!is_array($block)) {
                return [];
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $urls = [];

            if ($type === 'image') {
                $urls[] = data_get($data, 'file.url') ?? data_get($data, 'url') ?? data_get($data, 'src');
            }

            if (in_array($type, ['gallery', 'carousel', 'slider'], true)) {
                foreach (($data['images'] ?? $data['items'] ?? $data['slides'] ?? []) as $entry) {
                    if (is_array($entry)) {
                        $urls[] = data_get($entry, 'file.url') ?? data_get($entry, 'url') ?? data_get($entry, 'src') ?? data_get($entry, 'image');
                    } elseif (is_string($entry)) {
                        $urls[] = $entry;
                    }
                }
            }

            return collect($urls)->map($schemaNormalizeUrl)->filter()->all();
        }))
        ->map($schemaNormalizeUrl)
        ->filter()
        ->unique()
        ->values();

    $discussionForumImages = $discussionForumImages
        ->map($seoNormalizePublicUrl)
        ->filter()
        ->unique()
        ->values();

    $seoPrimaryImage = $discussionForumImages->first();
    $seoPrimaryImageWidth = (int) ($featuredImageWidth ?? 1200);
    $seoPrimaryImageHeight = (int) ($featuredImageHeight ?? 675);
    $seoPrimaryImageWidth = $seoPrimaryImageWidth > 0 ? $seoPrimaryImageWidth : 1200;
    $seoPrimaryImageHeight = $seoPrimaryImageHeight > 0 ? $seoPrimaryImageHeight : 675;

    $buildSchemaPerson = function ($user, ?string $fallbackName = null) use ($seoNormalizePublicUrl): array {
        $name = trim((string) (optional($user)->name ?? $fallbackName ?? 'Ografi Editör'));
        $person = [
            '@type' => 'Person',
            'name' => $name !== '' ? $name : 'Ografi Editör',
        ];

        if ($user && !empty($user->username) && \Illuminate\Support\Facades\Route::has('users.show')) {
            $person['url'] = $seoNormalizePublicUrl(route('users.show', ['user' => $user->username]));
        }

        return $person;
    };

    $buildSchemaComment = function ($comment) use (&$buildSchemaComment, $commentsGrouped, $stripSchemaText, $buildSchemaPerson): array {
        $commentText = $stripSchemaText($comment->content ?? $comment->body ?? $comment->text ?? '');
        $publishedAt = $comment->created_at ?? null;
        $modifiedAt = $comment->updated_at ?? null;
        $likesCount = (int) ($comment->likes_count ?? $comment->reactions_count ?? 0);
        $commentId = $comment->id ?? spl_object_id((object) $comment);

        $schemaComment = [
            '@type' => 'Comment',
            'text' => $commentText !== '' ? $commentText : 'Yorum',
            'author' => $buildSchemaPerson($comment->user ?? null, $comment->author_name ?? null),
        ];

        if ($publishedAt) {
            try {
                $schemaComment['datePublished'] = \Illuminate\Support\Carbon::parse($publishedAt)->toIso8601String();
            } catch (\Throwable $e) {
            }
        }

        if ($modifiedAt && (string) $modifiedAt !== (string) $publishedAt) {
            try {
                $schemaComment['dateModified'] = \Illuminate\Support\Carbon::parse($modifiedAt)->toIso8601String();
            } catch (\Throwable $e) {
            }
        }

        if ($likesCount > 0) {
            $schemaComment['interactionStatistic'] = [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/LikeAction',
                'userInteractionCount' => $likesCount,
            ];
        }

        $childComments = collect($commentsGrouped->get($commentId, []))
            ->values()
            ->map(fn ($childComment) => $buildSchemaComment($childComment))
            ->values()
            ->all();

        if (!empty($childComments)) {
            $schemaComment['comment'] = $childComments;
            $schemaComment['commentCount'] = count($childComments);
        }

        return $schemaComment;
    };

    $discussionForumComments = $rootComments
        ->take(20)
        ->map(fn ($comment) => $buildSchemaComment($comment))
        ->values()
        ->all();

    $discussionForumSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'DiscussionForumPosting',
        'mainEntityOfPage' => $postUrl,
        'url' => $postUrl,
        'headline' => $seoTitleBase,
        'text' => $discussionForumPostText,
        'author' => $buildSchemaPerson($author, $authorName),
        'datePublished' => $postPublishedAt ? \Illuminate\Support\Carbon::parse($postPublishedAt)->toIso8601String() : now()->toIso8601String(),
        'commentCount' => (int) $commentsCount,
    ];

    if ($postEditedAt) {
        try {
            $discussionForumSchema['dateModified'] = \Illuminate\Support\Carbon::parse($postEditedAt)->toIso8601String();
        } catch (\Throwable $e) {
        }
    }

    if ($discussionForumImages->isNotEmpty()) {
        $discussionForumSchema['image'] = $discussionForumImages->map(fn ($imageUrl) => [
            '@type' => 'ImageObject',
            'url' => $imageUrl,
        ])->values()->all();
    }

    $discussionForumLikeCount = (int) ($post->reactions_count ?? $reactionPills->sum('count') ?? 0);
    if ($discussionForumLikeCount > 0) {
        $discussionForumSchema['interactionStatistic'] = [
            '@type' => 'InteractionCounter',
            'interactionType' => 'https://schema.org/LikeAction',
            'userInteractionCount' => $discussionForumLikeCount,
        ];
    }

    if (!empty($discussionForumComments)) {
        $discussionForumSchema['comment'] = $discussionForumComments;
    }

    $seoPublishedIso = $postPublishedAt ? \Illuminate\Support\Carbon::parse($postPublishedAt)->toIso8601String() : now()->toIso8601String();
    $seoModifiedSource = $postEditedAt ?: ($post->updated_at ?? $postPublishedAt);
    $seoModifiedIso = $seoModifiedSource ? \Illuminate\Support\Carbon::parse($seoModifiedSource)->toIso8601String() : $seoPublishedIso;
    $seoLocale = app()->getLocale() ?: 'tr';
    $seoLanguage = str_replace('_', '-', $seoLocale);
    $seoLogoUrl = $seoNormalizePublicUrl(config('seo.logo_url') ?: config('app.logo_url') ?: asset('images/ografi-logo.png') . '?v=20260714a');
    $seoOrganizationId = rtrim($seoSiteUrl, '/') . '/#organization';
    $seoWebSiteId = rtrim($seoSiteUrl, '/') . '/#website';
    $seoWebPageId = $postUrl . '#webpage';
    $seoNewsArticleId = $postUrl . '#newsarticle';
    $seoDiscussionId = $postUrl . '#discussion';
    $seoTagNames = $showTags
        ->map(fn ($tag) => trim((string) ($tag->name ?? data_get($tag, 'name', ''))))
        ->filter()
        ->unique()
        ->values();
    $seoKeywords = $seoTagNames
        ->merge($hasCategory ? [$categoryName] : [])
        ->filter()
        ->unique()
        ->values();

    // DERIN SEO: kelime sayısı, okuma süresi, sosyal profiller ve arama aksiyonu.
    $seoWordMatches = [];
    preg_match_all('/[\p{L}\p{N}]+/u', $discussionForumPostText, $seoWordMatches);
    $seoWordCount = count($seoWordMatches[0] ?? []);
    $seoWordCount = $seoWordCount > 0 ? $seoWordCount : null;
    $seoReadingTimeMinutes = $seoWordCount ? max(1, (int) ceil($seoWordCount / 200)) : null;
    $seoSameAs = collect(config('seo.same_as', []))
        ->map(fn ($url) => $seoNormalizePublicUrl((string) $url))
        ->filter()
        ->unique()
        ->values();
    $seoSearchActionUrl = rtrim($seoSiteUrl, '/') . '/search?q={search_term_string}';

    $organizationSchema = [
        '@type' => 'Organization',
        '@id' => $seoOrganizationId,
        'name' => $siteName !== '' ? $siteName : 'Ografi',
        'url' => $seoSiteUrl,
    ];

    if ($seoLogoUrl) {
        $organizationSchema['logo'] = [
            '@type' => 'ImageObject',
            'url' => $seoLogoUrl,
            'width' => 96,
            'height' => 96,
        ];
    }

    if ($seoSameAs->isNotEmpty()) {
        $organizationSchema['sameAs'] = $seoSameAs->all();
    }

    $webSiteSchema = [
        '@type' => 'WebSite',
        '@id' => $seoWebSiteId,
        'url' => $seoSiteUrl,
        'name' => $siteName !== '' ? $siteName : 'Ografi',
        'publisher' => ['@id' => $seoOrganizationId],
        'inLanguage' => $seoLanguage,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $seoSearchActionUrl,
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $newsArticleSchema = [
        '@type' => 'NewsArticle',
        '@id' => $seoNewsArticleId,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $seoWebPageId,
        ],
        'url' => $postUrl,
        'headline' => \Illuminate\Support\Str::limit($seoTitleBase, 110, ''),
        'name' => $seoTitleBase,
        'description' => $description,
        'articleBody' => \Illuminate\Support\Str::limit($discussionForumPostText, 5000),
        'datePublished' => $seoPublishedIso,
        'dateModified' => $seoModifiedIso,
        'author' => $buildSchemaPerson($author, $authorName),
        'publisher' => ['@id' => $seoOrganizationId],
        'isAccessibleForFree' => true,
        'inLanguage' => $seoLanguage,
    ];

    if ($hasCategory) {
        $newsArticleSchema['articleSection'] = $categoryName;
    }

    if ($seoKeywords->isNotEmpty()) {
        $newsArticleSchema['keywords'] = $seoKeywords->implode(', ');
        $newsArticleSchema['about'] = $seoKeywords
            ->map(fn ($keyword) => ['@type' => 'Thing', 'name' => $keyword])
            ->values()
            ->all();
        $newsArticleSchema['mentions'] = $newsArticleSchema['about'];
    }

    if ($seoWordCount) {
        $newsArticleSchema['wordCount'] = $seoWordCount;
    }

    if ($seoReadingTimeMinutes) {
        $newsArticleSchema['timeRequired'] = 'PT' . $seoReadingTimeMinutes . 'M';
    }

    if ($seoPrimaryImage) {
        $newsArticleSchema['thumbnailUrl'] = $seoPrimaryImage;
        $newsArticleSchema['primaryImageOfPage'] = [
            '@type' => 'ImageObject',
            'url' => $seoPrimaryImage,
            'width' => $seoPrimaryImageWidth,
            'height' => $seoPrimaryImageHeight,
            'name' => $seoTitleBase,
            'caption' => $seoTitleBase . ' görseli',
        ];
    }

    $newsArticleSchema['copyrightHolder'] = ['@id' => $seoOrganizationId];
    $newsArticleSchema['copyrightYear'] = (int) \Illuminate\Support\Carbon::parse($postPublishedAt ?: now())->format('Y');

    if ($discussionForumImages->isNotEmpty()) {
        $newsArticleSchema['image'] = $discussionForumImages
            ->map(fn ($imageUrl) => [
                '@type' => 'ImageObject',
                'url' => $imageUrl,
                'width' => $seoPrimaryImageWidth,
                'height' => $seoPrimaryImageHeight,
                'name' => $seoTitleBase,
                'caption' => $seoTitleBase . ' görseli',
            ])
            ->values()
            ->all();
    }

    // Publisher article comments belong to Article/NewsArticle; Google advises
    // against marking publisher article comments as DiscussionForumPosting.
    $newsArticleSchema['commentCount'] = (int) $commentsCount;
    if (!empty($discussionForumComments)) {
        $newsArticleSchema['comment'] = $discussionForumComments;
    }

    // VideoObject is emitted only when the page contains a real video/embed and a
    // truthful thumbnail is available. This keeps the schema aligned with visible content.
    $seoVideoCandidates = collect();
    $seoContentForMedia = (string) ($postShowFullContentHtml ?: ($post->content ?? ''));
    if (preg_match_all('/<(video|source|iframe)\b[^>]*(?:src|data-src)=["\']([^"\']+)["\'][^>]*>/iu', $seoContentForMedia, $seoVideoMatches, PREG_SET_ORDER)) {
        foreach ($seoVideoMatches as $match) {
            $videoUrl = $seoNormalizePublicUrl(html_entity_decode((string) ($match[2] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($videoUrl) {
                $seoVideoCandidates->push(['url' => $videoUrl, 'embed' => strtolower((string) ($match[1] ?? '')) === 'iframe']);
            }
        }
    }
    $seoVideoCandidates = $seoVideoCandidates->unique('url')->values();
    $seoVideoSchemas = $seoVideoCandidates->map(function (array $candidate, int $index) use (
        $seoTitleBase,
        $description,
        $seoPublishedIso,
        $seoModifiedIso,
        $seoPrimaryImage,
        $postUrl
    ) {
        $videoUrl = (string) $candidate['url'];
        $thumbnailUrl = $seoPrimaryImage;
        if (!$thumbnailUrl && preg_match('#(?:youtube\.com/(?:embed/|watch\?v=)|youtu\.be/)([A-Za-z0-9_-]{6,})#i', $videoUrl, $youtubeMatch)) {
            $thumbnailUrl = 'https://i.ytimg.com/vi/' . $youtubeMatch[1] . '/hqdefault.jpg';
        }
        if (!$thumbnailUrl) {
            return null;
        }

        $schema = [
            '@type' => 'VideoObject',
            '@id' => $postUrl . '#video-' . ($index + 1),
            'name' => $seoTitleBase,
            'description' => $description,
            'thumbnailUrl' => [$thumbnailUrl],
            'uploadDate' => $seoPublishedIso,
            'dateModified' => $seoModifiedIso,
        ];
        $schema[$candidate['embed'] ? 'embedUrl' : 'contentUrl'] = $videoUrl;

        return $schema;
    })->filter()->values();

    if ($seoVideoSchemas->isNotEmpty()) {
        $newsArticleSchema['video'] = $seoVideoSchemas->map(fn (array $video) => ['@id' => $video['@id']])->all();
    }

    $discussionForumSchema['@id'] = $seoDiscussionId;
    $discussionForumSchema['mainEntityOfPage'] = ['@type' => 'WebPage', '@id' => $seoWebPageId];
    $discussionForumSchema['url'] = $postUrl;
    $discussionForumSchema['dateModified'] = $discussionForumSchema['dateModified'] ?? $seoModifiedIso;

    $breadcrumbItems = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Ana Sayfa',
            'item' => $seoSiteUrl,
        ],
    ];

    if (\Illuminate\Support\Facades\Route::has('blog.index')) {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => count($breadcrumbItems) + 1,
            'name' => 'Haberler',
            'item' => $seoNormalizePublicUrl(route('blog.index')),
        ];
    }

    if ($hasCategory && $categoryUrl) {
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => count($breadcrumbItems) + 1,
            'name' => $categoryName,
            'item' => $seoNormalizePublicUrl($categoryUrl),
        ];
    }

    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => count($breadcrumbItems) + 1,
        'name' => $seoTitleBase,
        'item' => $postUrl,
    ];

    $breadcrumbSchema = [
        '@type' => 'BreadcrumbList',
        '@id' => $postUrl . '#breadcrumb',
        'itemListElement' => $breadcrumbItems,
    ];

    $webPageSchema = [
        '@type' => 'WebPage',
        '@id' => $seoWebPageId,
        'url' => $postUrl,
        'name' => $seoTitle,
        'description' => $description,
        'isPartOf' => ['@id' => $seoWebSiteId],
        'primaryImageOfPage' => $seoPrimaryImage ? [
            '@type' => 'ImageObject',
            'url' => $seoPrimaryImage,
            'width' => $seoPrimaryImageWidth,
            'height' => $seoPrimaryImageHeight,
        ] : null,
        'breadcrumb' => ['@id' => $postUrl . '#breadcrumb'],
        'inLanguage' => $seoLanguage,
    ];
    $webPageSchema = array_filter($webPageSchema, fn ($value) => $value !== null);
    $webPageSchema['potentialAction'] = [
        '@type' => 'ReadAction',
        'target' => [$postUrl],
    ];

    $discussionForumSchema['isPartOf'] = ['@id' => $seoWebPageId];
    if ($seoKeywords->isNotEmpty()) {
        $discussionForumSchema['keywords'] = $seoKeywords->implode(', ');
    }

    $seoJsonLdGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $organizationSchema,
            $webSiteSchema,
            $webPageSchema,
            $breadcrumbSchema,
            $newsArticleSchema,
            ...$seoVideoSchemas->all(),
        ],
    ];


    $renderPostShowMentionText = static function (?string $value): string {
        $safe = e((string) $value);

        return preg_replace_callback(
            '/(^|\s)(@[\p{L}\p{N}_.-]+)/u',
            function (array $matches): string {
                $prefix = $matches[1] ?? '';
                $token = $matches[2] ?? '';
                $username = ltrim($token, '@');

                if ($username !== '' && \Illuminate\Support\Facades\Route::has('users.show')) {
                    $url = route('users.show', ['user' => $username]);

                    return $prefix . '<a href="' . e($url) . '" class="ps-mention-token ps-mention-token--link">' . $token . '</a>';
                }

                return $prefix . '<span class="ps-mention-token">' . $token . '</span>';
            },
            $safe
        ) ?? $safe;
    };


    $normalizeCommentImageUrl = static function (?string $url): ?string {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($url, ['data:image/', 'http://', 'https://', '//'])) {
            return $url;
        }

        if (\Illuminate\Support\Str::startsWith($url, '/storage/')) {
            return url($url);
        }

        if (\Illuminate\Support\Str::startsWith($url, 'storage/')) {
            return url('/' . ltrim($url, '/'));
        }

        if (\Illuminate\Support\Str::startsWith($url, '/')) {
            return url($url);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($url);
    };

    $renderPostShowCommentText = static function ($comment) use ($renderPostShowMentionText, $normalizeCommentImageUrl): string {
        $rawText = (string) (($comment->content ?? null) ?? ($comment->body ?? null) ?? '');
        $html = '';
        $offset = 0;

        if (preg_match_all('/\[(?:img|image|gif):([^\]\s]+)\]/iu', $rawText, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $fullMatch) {
                $fullToken = (string) $fullMatch[0];
                $position = (int) $fullMatch[1];
                $before = substr($rawText, $offset, $position - $offset);
                if (trim($before) !== '') {
                    $html .= '<div class="ps-comment-text-line">' . nl2br($renderPostShowMentionText($before)) . '</div>';
                }

                $imageUrl = $normalizeCommentImageUrl((string) ($matches[1][$index][0] ?? ''));
                if ($imageUrl) {
                    $html .= '<figure class="ps-comment-image"><img src="' . e($imageUrl) . '" alt="Yorum görseli" loading="lazy" decoding="async"></figure>';
                }
                $offset = $position + strlen($fullToken);
            }
        }

        $remaining = substr($rawText, $offset);
        if (trim($remaining) !== '') {
            $html .= '<div class="ps-comment-text-line">' . nl2br($renderPostShowMentionText($remaining)) . '</div>';
        }

        $directImages = collect([
            $comment->image_url ?? null,
            $comment->image ?? null,
            $comment->photo_url ?? null,
            $comment->attachment_url ?? null,
            $comment->media_url ?? null,
        ])->map($normalizeCommentImageUrl)->filter()->unique()->values();

        foreach ($directImages as $imageUrl) {
            $html .= '<figure class="ps-comment-image"><img src="' . e($imageUrl) . '" alt="Yorum görseli" loading="lazy" decoding="async"></figure>';
        }

        return $html !== '' ? $html : '<div class="ps-comment-text-line"></div>';
    };

@endphp

@section('title', $seoTitle)
@section('meta_description', $description)
@section('canonical_url', $postUrl)
@section('hide_feed_header', '1')

@push('head')
<style>
  * { box-sizing: border-box; }
  body { background: #f4f4f5; color: #111; }

  .ps-layout {
    display: flex !important;
    width: min(100%, 1320px) !important;
    max-width: 1320px !important;
    margin: 0 auto !important;
    padding: 72px 16px 96px !important;
    gap: 1px !important;
    align-items: flex-start !important;
    justify-content: center !important;
  }
  .ps-sidebar-left {
    width: 200px;
    flex-shrink: 0;
    position: sticky;
    top: 72px;
    height: fit-content;
  }
  .ps-nav-list { list-style: none; margin: 0; padding: 0; }
  .ps-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: 8px;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
  }
  .ps-nav-item:hover { background: #f3f4f6; color: #111; }
  .ps-nav-item svg, .ps-nav-item iconify-icon { flex-shrink: 0; color: #6b7280; }
  .ps-section-label {
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 14px 12px 6px;
  }
  .ps-cat-badge {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 800;
    flex-shrink: 0;
    color: #fff;
    overflow: hidden;
    background: #10b981;
  }
  .ps-cat-badge img { width: 100%; height: 100%; object-fit: cover; display: block; }

  .ps-main {
    flex: 0 1 860px !important;
    width: 860px !important;
    max-width: 860px !important;
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 14px !important;
  }
  .ps-post-card, .ps-comments-section, .ps-sidebar-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
  }
  .ps-post-card-inner { padding: 18px; }
  .ps-post-meta-row { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
  .ps-post-author-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
  }
  .ps-post-author {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
  }
  .ps-post-avatar-wrap {
    position: relative;
    flex: 0 0 auto;
  }
  .ps-post-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 1px solid #e5e7eb;
    background: #f3f4f6;
    color: #6b7280;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    font-size: 13px;
    font-weight: 700;
  }
  .ps-post-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .ps-post-avatar-badge {
    position: absolute;
    right: -7px;
    bottom: -6px;
    width: 30px;
    height: 30px;
    border: 2px solid #fff;
    border-radius: 999px;
    background: linear-gradient(135deg, #fb7185 0%, #ef4444 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.02em;
    line-height: 1;
    text-transform: uppercase;
    box-shadow: none !important;
  }
  .ps-post-avatar-badge img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .ps-post-author-copy {
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .ps-post-author-name {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #000;
    font-size: 15px;
    font-weight: 700;
    line-height: 1.15;
    text-decoration: none;
    min-height: 0;
    padding: 0;
  }
  .ps-post-subline {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 3px;
    color: #6b7280;
    font-size: 12px;
    line-height: 1.2;
  }
  .ps-post-subline a,
  .ps-post-subline span {
    color: inherit;
    text-decoration: none;
  }
  .ps-post-subline .ps-post-subline-category {
    color: #111827;
    font-weight: 700;
    min-height: 0;
    padding: 0;
    display: inline-flex;
    align-items: center;
  }
  .ps-post-subline-date {
    color: #6b7280;
    font-weight: 500;
    white-space: nowrap;
  }
  .ps-post-image-inline-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    color: #7b7f86;
    flex: 0 0 auto;
  }
  .ps-post-image-inline-icon svg {
    width: 16px;
    height: 16px;
    display: block;
  }
  .ps-post-cat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #10b981;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    overflow: hidden;
  }
  .ps-post-cat-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .ps-post-cat-name { font-size: 13px; font-weight: 600; color: #374151; text-decoration: none; }
  .ps-post-time { font-size: 12px; color: #9ca3af; }
  .ps-post-dot { color: #d1d5db; }
  .ps-post-title { font-size: 18px; font-weight: 800; color: #000; line-height: 1.42; margin: 0 0 20px; }
  .ps-post-image {
    width: 100%;
    border-radius: 8px;
    display: block;
    aspect-ratio: 16/9;
    object-fit: cover;
    background: #e5e7eb;
    margin-bottom: 18px;
    overflow: hidden;
  }
  .ps-post-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .ps-post-body { font-size: 14px; line-height: 1.55; color: #000; margin-bottom: 20px; }
  .ps-post-body :where(p, ul, ol, blockquote, h2, h3, h4) { margin-top: 0; }
  .ps-post-body img, .ps-post-body iframe, .ps-post-body video { max-width: 100%; border-radius: 10px; }
  .ps-source-link {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    width: 100%;
    min-height: 68px;
    margin: 0 0 18px;
    padding: 14px 18px;
    border: 1px solid rgba(15, 23, 42, 0.04);
    border-radius: 16px;
    background: #f3f4f6;
    color: #111827;
    text-decoration: none;
    -webkit-tap-highlight-color: transparent;
    transition: background-color .14s ease, border-color .14s ease, color .14s ease;
  }
  .ps-source-link:hover,
  .ps-source-link:focus-visible {
    background: #ebeef2;
    border-color: rgba(15, 23, 42, 0.06);
    color: #111827;
    outline: none;
  }
  .ps-source-link:active {
    background: #e5e7eb;
    color: #111827;
  }
  .ps-source-copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 6px;
    min-width: 0;
    flex: 1 1 auto;
  }
  .ps-source-label {
    color: #9ca3af;
    font-size: 10px;
    font-weight: 400;
    line-height: 1;
    letter-spacing: .08em;
    text-transform: uppercase;
  }
  .ps-source-domain-row {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-width: 0;
    max-width: 100%;
  }
  .ps-source-favicon {
    display: inline-flex;
    width: 16px;
    height: 16px;
    flex: 0 0 16px;
    border-radius: 999px;
    object-fit: cover;
    background: #fff;
    box-shadow: none;
  }
  .ps-source-domain {
    overflow: hidden;
    color: #111827;
    font-size: 16px;
    font-weight: 400;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .ps-source-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    flex: 0 0 auto;
    color: #9ca3af;
    margin-top: 2px;
  }
  .ps-source-link:hover .ps-source-icon,
  .ps-source-link:focus-visible .ps-source-icon,
  .ps-source-link:active .ps-source-icon {
    color: #6b7280;
  }
  .ps-source-icon iconify-icon { font-size: 16px; }
  .ps-quote-preview {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
    margin: 0 0 18px;
    padding: 14px;
    border: 1px solid #d9dee7;
    border-radius: 12px;
    background: #ffffff;
    color: #000;
    text-decoration: none;
  }
  .ps-quote-preview-head {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
  }
  .ps-quote-preview-avatar-wrap {
    position: relative;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
  }
  .ps-quote-preview-avatar {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    object-fit: cover;
  }
  .ps-quote-preview-avatar-fallback {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef2ff;
    color: #4f46e5;
    font-size: 12px;
    font-weight: 600;
  }
  .ps-quote-preview-category {
    position: absolute;
    right: -3px;
    bottom: -3px;
    width: 22px;
    height: 22px;
    border-radius: 999px;
    object-fit: cover;
    background: #fff;
  }
  .ps-quote-preview-meta {
    display: flex;
    min-width: 0;
    flex-direction: column;
    line-height: 1.2;
  }
  .ps-quote-preview-author {
    overflow: hidden;
    color: #111827;
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .ps-quote-preview-time {
    color: #6b7280;
    font-size: 12px;
  }
  .ps-quote-preview-title {
    color: #000;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.35;
  }
  .ps-quote-preview-description {
    color: #111827;
    font-size: 15px;
    line-height: 1.5;
  }
  .ps-quote-preview-media {
    display: block;
    overflow: hidden;
    border-radius: 8px;
    background: #f3f4f6;
  }
  .ps-quote-preview-media img {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 9;
    object-fit: cover;
  }
  .ps-quote-preview-open {
    position: absolute;
    right: 14px;
    top: 14px;
    display: inline-flex;
    width: 18px;
    height: 18px;
    align-items: center;
    justify-content: center;
    color: #64748b;
  }
  .ps-quote-preview-open iconify-icon { font-size: 16px; }
  .ps-tags-row { display: flex; gap: 12px; margin-bottom: 28px; flex-wrap: wrap; }
  .ps-tag { font-size: 12px; font-weight: 700; color: #009966; text-decoration: none; }
  .ps-tag:hover { text-decoration: none; }

  .ps-actions-bar {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0;
    padding: 12px 18px;
    margin: 0 -18px;
  }
  .ps-action-btn, .ps-actions-bar :where(button, a) {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    border: none !important;
    background: transparent !important;
    cursor: pointer;
    padding: 0 !important;
    border-radius: 0 !important;
    color: #000 !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    min-width: 0 !important;
    box-shadow: none !important;
  }
  .ps-action-btn:hover, .ps-actions-bar :where(button, a):hover { background: transparent !important; color: #000 !important; }
  .ps-action-btn svg,
  .ps-action-btn iconify-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
  }
  .ps-action-sep { display: none; }
  .ps-view-count {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 5px;
    font-size: 13px;
    color: #000;
    padding: 0;
    grid-column: 2;
    grid-row: 2;
  }
  .ps-action-row {
    grid-column: 1;
    grid-row: 2;
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 0;
  }
  .ps-vote-cluster {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-width: 58px;
    height: 28px;
    padding: 0 9px;
    border-radius: 999px;
    background: #f7f7f7;
    color: #000;
  }
  .ps-vote-cluster iconify-icon,
  .ps-vote-cluster svg {
    font-size: 14px;
    width: 15px;
    height: 15px;
  }
  .ps-vote-count {
    font-size: 13px;
    line-height: 1;
    color: #000;
  }
  .ps-action-icon {
    width: 16px;
    height: 16px;
    border-radius: 0;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .ps-action-icon svg,
  .ps-action-icon iconify-icon {
    width: 18px;
    height: 18px;
  }
  .ps-action-count {
    min-width: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    margin-left: -5px;
  }
  .ps-action-btn--share .ps-action-icon { background: transparent; }
  .ps-reaction-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin: 0 0 16px;
  }
  .ps-reaction-form {
    display: inline-flex;
    margin: 0;
  }
  .ps-reaction-picker {
    position: relative;
    display: inline-flex;
  }
  .ps-reaction-trigger {
    width: 32px !important;
    min-width: 32px;
    height: 32px;
    padding: 0 !important;
    border-radius: 999px !important;
  }
  .ps-reaction-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    min-height: 28px;
    border: 0;
    border-radius: 999px;
    background: #f4f4f4;
    color: #000;
    padding: 0 10px;
    font-size: 12px;
    font-weight: 600;
  }
  .ps-reaction-pill img {
    width: 20px;
    height: 20px;
    display: block;
    object-fit: cover;
  }
  .ps-reaction-pill svg {
    width: 18px;
    height: 18px;
    display: block;
  }
  .ps-reaction-menu {
    position: absolute;
    left: 0;
    top: calc(100% + 10px);
    z-index: 45;
    width: min(208px, calc(100vw - 32px));
    max-width: calc(100vw - 32px);
    padding: 10px 12px 12px;
    border-radius: 8px;
    background: #fff;
  }
  .ps-reaction-menu[hidden] { display: none !important; }
  .ps-reaction-menu:not([hidden]) {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 12px;
    align-items: center;
    justify-content: start;
  }
  .ps-reaction-menu-title {
    flex: 0 0 100%;
    color: #667085;
    font-size: 13px;
    line-height: 1.25;
    margin-bottom: 2px;
  }
  .ps-reaction-option {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: #000;
    padding: 0;
  }
  .ps-reaction-menu .ps-reaction-form {
    width: 34px;
    height: 34px;
  }
  .ps-reaction-option--login {
    text-decoration: none !important;
  }
  .ps-reaction-option img,
  .ps-reaction-option svg {
    width: 24px;
    height: 24px;
    display: block;
    object-fit: cover;
  }


  .ps-comments-section { padding: 0; }
  .ps-comments-header { padding: 22px 20px 0; }
  .ps-comments-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
  }
  .ps-comments-title { font-size: 18px; font-weight: 800; color: #000; margin: 0; }
  .ps-comments-filter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 0;
    background: transparent;
    color: #000;
    padding: 0;
  }
  .ps-comments-filter iconify-icon { font-size: 19px; }
  .ps-comments-filter svg,
  .ps-comment-tool svg,
  .ps-comment-send svg,
  .ps-post-edited-icon {
    width: 18px;
    height: 18px;
    display: block;
  }
  .ps-post-edited-wrap {
    position: relative;
    display: inline-flex;
  }
  .ps-post-edited-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    background: transparent;
    color: inherit;
    padding: 0;
    cursor: pointer;
  }
  .ps-post-edited-popover {
    position: absolute;
    left: 0;
    top: calc(100% + 8px);
    z-index: 45;
    display: none;
    width: 230px;
    padding: 12px;
    border-radius: 8px;
    background: #fff;
    color: #111;
  }
  .ps-post-edited-wrap.is-open .ps-post-edited-popover { display: block; }
  .ps-post-edited-title {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #111;
    margin-bottom: 4px;
  }
  .ps-post-edited-detail {
    display: block;
    font-size: 12px;
    line-height: 1.35;
    color: #6b7280;
  }
  .ps-comment-form-box {
    border-radius: 8px;
    background: #f7f7f8;
    padding: 14px;
  }
  .ps-comment-textarea {
    width: 100%;
    border: none !important;
    background: transparent !important;
    font-size: 15px;
    line-height: 1.5;
    color: #111;
    resize: none;
    min-height: 30px;
    font-family: inherit;
    outline: none !important;
    box-shadow: none !important;
  }
  .ps-comment-textarea::placeholder { color: #9ca3af; }
  .ps-comment-toolbar {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 16px;
    padding-top: 0;
  }
  .ps-comment-tool, .ps-comment-send {
    background: none !important;
    border: none !important;
    cursor: pointer;
    color: #9ca3af;
    padding: 4px;
    border-radius: 5px;
    display: flex;
    align-items: center;
  }
  .ps-comment-tool:hover, .ps-comment-send:hover { color: #6b7280; background: transparent !important; }
  .ps-comment-send { margin-left: auto; }
  .ps-comments-list { padding: 24px 20px 28px; margin-top: 0; display: flex; flex-direction: column; gap: 0; }
  .ps-comment-item { display: flex; gap: 10px; padding: 0 0 22px; border-bottom: 0; }
  .ps-comment-item:last-child { border-bottom: none; }
  .ps-comment-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #e5e7eb;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #6b7280;
    overflow: hidden;
  }
  .ps-comment-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .ps-comment-body { flex: 1; min-width: 0; }
  .ps-comment-author { font-size: 14px; font-weight: 700; color: #111; }
  .ps-comment-time { font-size: 12px; color: #9ca3af; margin-top: 2px; }
  .ps-comment-text { font-size: 14px; color: #374151; line-height: 1.55; margin-top: 6px; }
  .ps-comment-text img { max-width: 260px; border-radius: 8px; display: block; margin-top: 8px; }
  .ps-comment-actions { display: flex; gap: 16px; margin-top: 18px; align-items: center; flex-wrap: wrap; }
  .ps-comment-vote, .ps-comment-reply-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    background: none !important;
    border: none !important;
    cursor: pointer;
    color: #9ca3af;
    font-size: 12px;
    padding: 0;
  }
  .ps-comment-reply-btn { font-size: 13px; font-weight: 500; }
  .ps-comment-vote:hover, .ps-comment-reply-btn:hover { color: #374151; }

  .ps-comment-card {
    display: flex;
    gap: 12px;
    width: 100%;
  }
  .ps-comment-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    line-height: 1.25;
  }
  .ps-comment-author {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
  }
  .ps-comment-role {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
  }
  .ps-comment-time {
    font-size: 12px;
    color: #9ca3af;
  }
  .ps-comment-text {
    font-size: 14px;
    color: #374151;
    line-height: 1.55;
    margin-top: 6px;
    white-space: pre-line;
  }
  .ps-comment-actions {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 18px;
    flex-wrap: wrap;
  }
  .ps-comment-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 0;
    background: transparent;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
  }
  .ps-comment-action:hover { color: #111827; }
  .ps-comment-action iconify-icon,
  .ps-comment-action svg { width: 15px; height: 15px; }
  .ps-comment-votes {
    display: inline-flex;
    align-items: center;
    gap: 14px;
  }
  .ps-comment-vote-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: #111827;
    cursor: pointer;
    padding: 0;
  }
  .ps-comment-vote-btn:hover { background: #f3f4f6; }
  .ps-comment-vote-btn svg {
    width: 16px;
    height: 16px;
    display: block;
  }
  .ps-comment-vote-count {
    font-size: 13px;
    color: #111827;
    min-width: 10px;
    text-align: center;
  }
  .ps-comment-reply-text { font-size: 14px; color: #000; }
  .ps-comment-edit-form,
  .ps-comment-reply-form {
    display: none;
    margin-top: 10px;
  }
  .ps-comment-edit-form.is-open,
  .ps-comment-reply-form.is-open { display: block; }
  .ps-comment-mini-box {
    border: 1px solid #d9dde4;
    border-radius: 8px;
    background: #f7f7f8;
    padding: 10px;
  }
  .ps-comment-mini-textarea {
    width: 100%;
    min-height: 54px;
    resize: vertical;
    border: 0 !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent;
    font: inherit;
    color: #111827;
  }
  .ps-comment-mini-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 8px;
  }
  .ps-comment-mini-btn {
    border: 0;
    border-radius: 999px;
    padding: 7px 12px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
  }
  .ps-comment-mini-btn--ghost {
    background: #f3f4f6;
    color: #374151;
  }
  .ps-comment-mini-btn--primary {
    background: #111827;
    color: #fff;
  }
  .ps-comment-delete-form,
  .ps-comment-votes form {
    display: inline;
    margin: 0;
  }

  .ps-replies { margin-left: 44px; }
  .ps-reply-form { display: none; margin-top: 12px; }
  .ps-reply-form.is-open { display: block; }

  .ps-sidebar-right { width: 260px; flex-shrink: 0; display: flex; flex-direction: column; gap: 14px; position: sticky; top: 72px; height: fit-content; }
  .ps-sidebar-card { padding: 14px; }
  .ps-sidebar-card-title { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #f3f4f6; }
  .ps-recent-comment-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 0.5px solid #f3f4f6; }
  .ps-recent-comment-item:last-child { border-bottom: none; padding-bottom: 0; }
  .ps-rc-avatar { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #6b7280; overflow: hidden; }
  .ps-rc-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .ps-rc-body { flex: 1; min-width: 0; }
  .ps-rc-name { font-size: 12px; font-weight: 700; color: #111; }
  .ps-rc-post { font-size: 11px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .ps-rc-text { font-size: 12px; color: #374151; margin-top: 3px; }
  .ps-rc-time { font-size: 11px; color: #9ca3af; margin-top: 2px; }
  .ps-tag-row { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 0.5px solid #f3f4f6; text-decoration: none; }
  .ps-tag-row:last-child { border-bottom: none; padding-bottom: 0; }
  .ps-tag-name { font-size: 13px; font-weight: 600; color: #374151; }
  .ps-tag-count { font-size: 12px; color: #9ca3af; font-weight: 500; }

  .ps-menu { position: relative; margin-left: auto; }
  .ps-menu-trigger { background: transparent; border: 0; color: #6b7280; cursor: pointer; padding: 5px; border-radius: 7px; display: inline-flex; }
  .ps-menu-trigger:hover { background: #f3f4f6; color: #111; }
  .ps-menu-panel { position: absolute; right: 0; top: calc(100% + 6px); z-index: 40; min-width: 150px; border-radius: 10px; background: #fff; padding: 6px; box-shadow: none !important; border: 1px solid #e5e7eb; display: none; }
  .ps-menu.is-open .ps-menu-panel { display: block; }
  .ps-menu-item { display: flex; width: 100%; align-items: center; gap: 8px; border: 0; background: transparent; border-radius: 8px; padding: 9px 10px; font-size: 13px; color: #374151; text-decoration: none; text-align: left; }
  .ps-menu-item:hover { background: #f3f4f6; color: #111; }

  .ps-nsfw-locker { position: relative; }
  .ps-nsfw-locker.ps-nsfw-locked .ps-post-image, .ps-nsfw-locker.ps-nsfw-locked .ps-post-body { filter: blur(16px) brightness(.75); pointer-events: none; user-select: none; }
  .ps-nsfw-overlay { position: absolute; inset: 0; z-index: 20; display: flex; align-items: center; justify-content: center; padding: 18px; background: rgba(15,23,42,.45); border-radius: 10px; }
  .ps-nsfw-card { max-width: 430px; border-radius: 14px; background: rgba(255,255,255,.96); padding: 18px; text-align: center; box-shadow: none !important; }
  .ps-nsfw-label { display: inline-flex; align-items: center; justify-content: center; padding: 7px 12px; border-radius: 999px; background: #f43f5e; color: #fff; font-weight: 800; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 10px; }
  .ps-nsfw-text { font-size: 14px; color: #475569; margin: 0 0 12px; }
  .ps-nsfw-check { display: flex; gap: 8px; align-items: flex-start; text-align: left; font-size: 13px; color: #374151; margin-bottom: 12px; }
  .ps-nsfw-button { border: 0; border-radius: 999px; padding: 9px 14px; background: #111827; color: #fff; font-weight: 700; cursor: pointer; }
  .ps-nsfw-button:disabled { opacity: .5; cursor: not-allowed; }

  @media (max-width: 900px) { .ps-sidebar-right { display: none; } }
  @media (max-width: 640px) {
    html,
    body {
      max-width: 100%;
      overflow-x: hidden;
    }

    html body.alma-app:has(.post-show-shell) .layout-main,
    html body.alma-app:has(.post-show-shell) .main-grid,
    html body.alma-app:has(.post-show-shell) .main-grid.main-grid--padded {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
      overflow-x: hidden !important;
    }

    .ps-actions-bar {
      grid-template-columns: minmax(0, 1fr) auto;
      margin-left: -12px !important;
      margin-right: -12px !important;
      padding-left: 12px !important;
      padding-right: 12px !important;
    }
    .ps-reaction-row { padding-left: 0; padding-right: 0; }
    .ps-sidebar-left { display: none; }
    .ps-layout {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      padding: 0 0 96px !important;
      margin: 0 !important;
    }
    .post-show-shell {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
    }
    .post-show-shell .ps-main {
      flex: 1 1 auto !important;
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
    }
    .ps-post-card, .ps-comments-section { border-radius: 0; }
    .ps-post-card,
    .ps-comments-section,
    .ps-post-card-inner,
    .ps-comments-list,
    .ps-comment-body {
      min-width: 0 !important;
      max-width: 100% !important;
    }
    .ps-post-card-inner { padding: 16px 12px 0; }
    .ps-post-author-row { gap: 8px; }
    .ps-post-title {
      font-size: 17px;
      line-height: 1.35;
      overflow-wrap: anywhere;
    }
    .ps-post-image,
    .ps-post-image img,
    .ps-post-body img,
    .ps-post-body iframe,
    .ps-post-body video {
      max-width: 100% !important;
    }
    .ps-post-image img,
    .ps-post-body img,
    .ps-post-body video {
      height: auto !important;
    }
    .ps-post-body {
      overflow-wrap: anywhere;
      word-break: break-word;
    }
    .ps-action-row {
      gap: 14px;
      min-width: 0;
      overflow: hidden;
    }
    .ps-vote-cluster {
      min-width: 48px;
      padding-left: 8px;
      padding-right: 8px;
    }
    .ps-comments-header { padding: 14px 14px 0; }
    .ps-comments-list { padding: 14px 12px 22px; }
    .ps-comment-item { gap: 8px; }
    .ps-comment-avatar {
      width: 30px;
      height: 30px;
      font-size: 11px;
    }
    .ps-post-avatar {
      width: 40px;
      height: 40px;
    }
    .ps-post-avatar-badge {
      width: 26px;
      height: 26px;
    }
    .ps-replies { margin-left: 16px; }
  }

  @media (max-width: 360px) {
    .ps-action-row { gap: 10px; }
    .ps-actions-bar { padding-left: 10px !important; padding-right: 10px !important; }
    .ps-post-card-inner { padding-left: 10px; padding-right: 10px; }
  }

  .ps-main > .ps-post-card,
  .ps-main > .ps-comments-section {
    width: 100% !important;
    max-width: 100% !important;
  }

  .post-show-shell {
    width: 100% !important;
    max-width: 656px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }

  .post-show-shell .ps-main {
    flex: 0 0 656px !important;
    width: 656px !important;
    max-width: 656px !important;
  }

  @media (max-width: 1180px) {
    .ps-layout {
      width: 100% !important;
      max-width: 656px !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    .ps-main {
      flex: 1 1 auto !important;
      width: 100% !important;
      max-width: 656px !important;
    }
  }

  @media (max-width: 640px) {
    .ps-layout.post-show-shell {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    .post-show-shell .ps-main {
      flex: 1 1 auto !important;
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
    }
  }

  .post-show-shell {
    font-size: 15px;
    line-height: 1.55;
  }

  .post-show-shell :where(.ps-post-author-name, .ps-post-subline-category, .ps-post-title, .ps-tag, .ps-reaction-pill, .ps-action-count, .ps-comments-title, .ps-comment-author, .ps-comment-role, .ps-comment-action, .ps-comment-mini-btn, .ps-source-domain) {
    font-weight: 500 !important;
  }

  .post-show-shell .ps-post-author-name {
    font-size: 15px;
    line-height: 1.25;
  }

  .post-show-shell .ps-post-author-name,
  .post-show-shell .ps-post-subline-category {
    font-weight: 700 !important;
  }

  .post-show-shell .ps-post-subline-date {
    font-weight: 500 !important;
  }

  .post-show-shell .ps-post-subline,
  .post-show-shell .ps-post-time,
  .post-show-shell .ps-comment-time,
  .post-show-shell .ps-comment-role,
  .post-show-shell .ps-source-label {
    font-size: 13px;
    line-height: 1.35;
  }

  .post-show-shell .ps-post-title {
    font-size: 19px;
    line-height: 1.42;
  }

  .post-show-shell .ps-post-body,
  .post-show-shell .ps-comment-text,
  .post-show-shell .ps-comment-textarea,
  .post-show-shell .ps-comment-mini-textarea {
    font-size: 15px;
    line-height: 1.6;
  }

  .post-show-shell .ps-tag,
  .post-show-shell .ps-reaction-pill,
  .post-show-shell .ps-action-btn,
  .post-show-shell .ps-view-count,
  .post-show-shell .ps-vote-count,
  .post-show-shell .ps-action-count,
  .post-show-shell .ps-comment-action,
  .post-show-shell .ps-comment-vote-count,
  .post-show-shell .ps-comment-reply-text {
    font-size: 14px;
    line-height: 1.35;
  }

  .post-show-shell .ps-comments-title {
    font-size: 19px;
    line-height: 1.35;
  }

  .post-show-shell .ps-comment-author {
    font-size: 15px;
    line-height: 1.3;
  }

  @media (max-width: 640px) {
    .post-show-shell {
      font-size: 14.5px;
    }

    .post-show-shell .ps-post-title {
      font-size: 18px;
      line-height: 1.42;
    }

    .post-show-shell .ps-post-body,
    .post-show-shell .ps-comment-text,
    .post-show-shell .ps-comment-textarea,
    .post-show-shell .ps-comment-mini-textarea {
      font-size: 14.5px;
      line-height: 1.58;
    }

    .post-show-shell .ps-post-subline,
    .post-show-shell .ps-post-time,
    .post-show-shell .ps-comment-time,
    .post-show-shell .ps-comment-role {
      font-size: 12.5px;
    }

    .post-show-shell .ps-tag,
    .post-show-shell .ps-reaction-pill,
    .post-show-shell .ps-action-btn,
    .post-show-shell .ps-view-count,
    .post-show-shell .ps-vote-count,
    .post-show-shell .ps-action-count,
    .post-show-shell .ps-comment-action,
    .post-show-shell .ps-comment-vote-count,
    .post-show-shell .ps-comment-reply-text {
      font-size: 13.5px;
    }
  }



  /* Resimdeki gibi kompakt yazar/kategori/tarih alanı */
  .post-author-mini {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      font-family: Poppins, Arial, sans-serif;
      min-width: 0;
  }

  .post-author-avatar-wrap {
      position: relative;
      width: 46px;
      height: 46px;
      flex: 0 0 46px;
  }

  .post-author-avatar {
      width: 46px;
      height: 46px;
      border-radius: 999px;
      object-fit: cover;
      display: block;
      background: #f3f4f6;
      border: 1px solid #e5e7eb;
      overflow: hidden;
  }

  .post-author-avatar-empty {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #6b7280;
      font-size: 13px;
      font-weight: 700;
  }

  .post-author-badge {
      position: absolute;
      right: -7px;
      bottom: -6px;
      width: 30px;
      height: 30px;
      border-radius: 999px;
      border: 2px solid #ffffff;
      background: linear-gradient(135deg, #fb7185 0%, #ef4444 100%);
      color: #ffffff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      font-size: 10px;
      line-height: 1;
      font-weight: 700;
      letter-spacing: -0.02em;
      text-transform: uppercase;
      text-decoration: none;
      box-shadow: none !important;
  }

  .post-author-badge img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
  }

  .post-author-info {
      display: flex;
      flex-direction: column;
      justify-content: center;
      min-width: 0;
  }

  .post-author-name {
      color: #000000;
      font-size: 15px;
      line-height: 1.15;
      font-weight: 700;
      white-space: nowrap;
      text-decoration: none;
  }

  .post-author-name:hover {
      color: #000000;
      text-decoration: none;
  }

  .post-author-meta {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-top: 3px;
      font-size: 12px;
      line-height: 1.2;
      white-space: nowrap;
  }

  .post-author-category {
      color: #020617;
      font-weight: 700;
      text-decoration: none;
  }

  .post-author-category:hover {
      color: #020617;
      text-decoration: none;
  }

  .post-author-date {
      color: #6b7280;
      font-weight: 400;
  }

  .post-author-edited-wrap {
      color: #5f6368;
      align-items: center;
  }

  .post-author-edited-button {
      width: 16px;
      height: 16px;
      color: #5f6368;
  }

  .post-author-edited-button:hover {
      color: #111827;
  }

  .post-author-edited-icon {
      width: 16px !important;
      height: 16px !important;
  }


  /* Yeni aksiyon ikon hizalama ve hover/tık efekti */
  .ps-actions-bar .ps-action-row {
    gap: 8px !important;
    align-items: center !important;
  }

  .ps-actions-bar .ps-action-row form {
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
  }

  .ps-actions-bar .ps-action-btn {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    line-height: 1 !important;
    transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease !important;
  }

  .ps-actions-bar .ps-action-btn:hover,
  .ps-actions-bar .ps-action-btn:focus-visible {
    background: #f3f4f6 !important;
    color: #2563eb !important;
    outline: none !important;
  }

  .ps-actions-bar .ps-action-btn:active,
  .ps-actions-bar .ps-action-btn.is-active,
  .ps-actions-bar .ps-action-btn.is-copied,
  .ps-actions-bar .ps-action-btn[aria-pressed="true"] {
    background: #e5efff !important;
    color: #2563eb !important;
    transform: scale(0.96);
  }

  .ps-actions-bar .ps-action-icon {
    width: 20px !important;
    height: 20px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: transparent !important;
    color: inherit !important;
  }

  .ps-actions-bar .ps-action-icon svg,
  .ps-actions-bar .ps-action-btn svg {
    width: 20px !important;
    height: 20px !important;
    display: block !important;
    flex-shrink: 0 !important;
  }

  .ps-actions-bar .ps-action-count {
    margin-left: -2px !important;
    color: #111827 !important;
    font-size: 13px !important;
    line-height: 1 !important;
  }

  .ps-actions-bar .ps-action-btn:hover + .ps-action-count,
  .ps-actions-bar .ps-action-btn:hover .ps-action-count,
  .ps-actions-bar .ps-action-btn.is-active .ps-action-count,
  .ps-actions-bar .ps-action-btn[aria-pressed="true"] .ps-action-count {
    color: #2563eb !important;
  }

  @media (max-width: 640px) {
    .ps-actions-bar .ps-action-row {
      gap: 6px !important;
    }

    .ps-actions-bar .ps-action-btn {
      width: 34px !important;
      height: 34px !important;
      min-width: 34px !important;
    }
  }

  /* Aksiyon alanı son düzenleme: sayaç boşlukları, 0 gizleme, bookmark dolu mavi, paylaş düzeltme */
  .ps-actions-bar .ps-action-row {
    gap: 14px !important;
  }

  .ps-actions-bar .ps-action-btn {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    border-radius: 999px !important;
  }

  .ps-actions-bar .ps-action-btn:hover,
  .ps-actions-bar .ps-action-btn:focus-visible,
  .ps-actions-bar .ps-action-btn:active,
  .ps-actions-bar .ps-action-btn.is-copied {
    background: #f3f4f6 !important;
    color: #2563eb !important;
  }

  .ps-actions-bar .ps-action-btn.is-active,
  .ps-actions-bar .ps-action-btn[aria-pressed="true"] {
    color: #2563eb !important;
  }

  .ps-actions-bar .ps-bookmark-btn.is-active,
  .ps-actions-bar .ps-bookmark-btn[aria-pressed="true"] {
    background: transparent !important;
    color: #2563eb !important;
    transform: none !important;
  }

  .ps-actions-bar .ps-bookmark-btn:hover,
  .ps-actions-bar .ps-bookmark-btn:focus-visible {
    background: #f3f4f6 !important;
    color: #2563eb !important;
  }

  .ps-actions-bar .ps-bookmark-btn.is-active .ps-bookmark-shape,
  .ps-actions-bar .ps-bookmark-btn[aria-pressed="true"] .ps-bookmark-shape {
    fill: currentColor !important;
    stroke: currentColor !important;
  }

  .ps-actions-bar .ps-bookmark-btn.is-active .ps-bookmark-line,
  .ps-actions-bar .ps-bookmark-btn[aria-pressed="true"] .ps-bookmark-line {
    stroke: #ffffff !important;
  }

  .ps-actions-bar .ps-action-count {
    margin-left: 2px !important;
    min-width: 14px !important;
  }

  .ps-view-count {
    gap: 7px !important;
  }

  @media (max-width: 640px) {
    .ps-actions-bar .ps-action-row {
      gap: 10px !important;
    }
  }



  /* Tepkiler alanı 2. görseldeki gibi düzgün kart görünümü */
  .ps-post-card,
  .ps-post-card-inner,
  .ps-actions-bar,
  .ps-reaction-row,
  .ps-reaction-picker {
    overflow: visible !important;
  }

  .ps-vote-cluster,
  .ps-vote-count {
    display: none !important;
  }

  .ps-actions-bar {
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
    padding: 10px 18px 14px !important;
    margin: 0 -18px !important;
    overflow: visible !important;
  }

  .ps-reaction-row {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
  }

  .ps-reaction-pill {
    height: 30px !important;
    min-height: 30px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #f5f5f5 !important;
    color: #111827 !important;
    padding: 0 10px !important;
    font-size: 13px !important;
    line-height: 1 !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    text-decoration: none !important;
  }

  .ps-reaction-pill:hover,
  .ps-reaction-pill:focus-visible {
    background: #eeeeee !important;
    color: #2563eb !important;
  }

  .ps-reaction-pill img,
  .ps-reaction-pill svg {
    width: 20px !important;
    height: 20px !important;
    display: block !important;
    object-fit: cover !important;
    flex: 0 0 auto !important;
  }

  .ps-reaction-picker {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    overflow: visible !important;
  }

  .ps-reaction-trigger {
    width: 32px !important;
    min-width: 32px !important;
    height: 32px !important;
    padding: 0 !important;
    border-radius: 999px !important;
    background: #f5f5f5 !important;
    color: #111827 !important;
  }

  .ps-reaction-trigger:hover,
  .ps-reaction-trigger[aria-expanded="true"] {
    background: #eeeeee !important;
    color: #2563eb !important;
  }

  .ps-reaction-menu {
    position: absolute !important;
    right: 0 !important;
    left: auto !important;
    top: calc(100% + 10px) !important;
    z-index: 999 !important;
    width: 208px !important;
    max-width: calc(100vw - 32px) !important;
    padding: 10px 12px 12px !important;
    border-radius: 8px !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .ps-reaction-menu[hidden] {
    display: none !important;
  }

  .ps-reaction-menu:not([hidden]) {
    display: grid !important;
    grid-template-columns: repeat(4, 34px) !important;
    gap: 12px 12px !important;
    align-items: center !important;
    justify-content: start !important;
  }

  .ps-reaction-menu-title {
    grid-column: 1 / -1 !important;
    flex: none !important;
    color: #6b7280 !important;
    font-size: 13px !important;
    line-height: 1.2 !important;
    margin: 0 0 2px !important;
    font-weight: 400 !important;
  }

  .ps-reaction-form {
    display: inline-flex !important;
    width: 34px !important;
    height: 34px !important;
    margin: 0 !important;
  }

  .ps-reaction-option {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    border: 0 !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    cursor: pointer !important;
  }

  .ps-reaction-option:hover,
  .ps-reaction-option:focus-visible {
    background: #f3f4f6 !important;
    color: #2563eb !important;
  }

  .ps-reaction-option img,
  .ps-reaction-option svg {
    width: 25px !important;
    height: 25px !important;
    display: block !important;
    object-fit: cover !important;
  }

  .ps-action-row {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    padding: 0 !important;
    overflow: visible !important;
  }

  @media (max-width: 640px) {
    .ps-actions-bar {
      padding-left: 12px !important;
      padding-right: 12px !important;
      margin-left: -12px !important;
      margin-right: -12px !important;
    }

    .ps-reaction-row {
      gap: 7px !important;
    }

    .ps-reaction-menu {
      right: auto !important;
      left: 0 !important;
      width: 208px !important;
    }
  }

  @media (prefers-color-scheme: dark) {
    .ps-reaction-pill,
    .ps-reaction-trigger {
      background: #27272a !important;
      color: #f4f4f5 !important;
    }

    .ps-reaction-pill:hover,
    .ps-reaction-trigger:hover,
    .ps-reaction-trigger[aria-expanded="true"] {
      background: #3f3f46 !important;
      color: #60a5fa !important;
    }

    .ps-reaction-menu {
      background: #18181b !important;
      border-color: #27272a !important;
      box-shadow: none !important;
    }

    .ps-reaction-menu-title {
      color: #a1a1aa !important;
    }

    .ps-reaction-option {
      color: #f4f4f5 !important;
    }

    .ps-reaction-option:hover,
    .ps-reaction-option:focus-visible {
      background: #27272a !important;
      color: #60a5fa !important;
    }
  }


  /* Tepkiler: 7'li daha fazla sistemi + görseldeki renk/boyut düzeni */
  .ps-reaction-row {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
    margin: 0 !important;
    overflow: visible !important;
  }

  .ps-reaction-pill,
  .ps-reaction-more-trigger {
    height: 30px !important;
    min-height: 30px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #f4f4f5 !important;
    color: #111827 !important;
    padding: 0 11px !important;
    font-size: 13px !important;
    line-height: 1 !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    text-decoration: none !important;
  }

  .ps-reaction-pill:hover,
  .ps-reaction-pill:focus-visible,
  .ps-reaction-more-trigger:hover,
  .ps-reaction-more-trigger:focus-visible,
  .ps-reaction-more-trigger[aria-expanded="true"] {
    background: #eeeeef !important;
    color: #2563eb !important;
  }

  .ps-reaction-pill img,
  .ps-reaction-pill svg {
    width: 22px !important;
    height: 22px !important;
    display: block !important;
    object-fit: cover !important;
    flex: 0 0 auto !important;
  }

  .ps-reaction-more-wrap,
  .ps-reaction-picker {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    overflow: visible !important;
  }

  .ps-reaction-more-menu,
  .ps-reaction-menu {
    position: absolute !important;
    right: 0 !important;
    left: auto !important;
    top: calc(100% + 10px) !important;
    z-index: 100 !important;
    width: 208px !important;
    max-width: calc(100vw - 32px) !important;
    padding: 10px 12px 14px !important;
    border-radius: 7px !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .ps-reaction-more-menu[hidden],
  .ps-reaction-menu[hidden] {
    display: none !important;
  }

  .ps-reaction-more-menu:not([hidden]),
  .ps-reaction-menu:not([hidden]) {
    display: grid !important;
    grid-template-columns: repeat(4, 34px) !important;
    gap: 12px 13px !important;
    align-items: center !important;
    justify-content: start !important;
  }

  .ps-reaction-more-title,
  .ps-reaction-menu-title {
    grid-column: 1 / -1 !important;
    color: #6b7280 !important;
    font-size: 13px !important;
    line-height: 1.2 !important;
    margin: 0 0 3px !important;
    font-weight: 400 !important;
  }

  .ps-reaction-more-page {
    display: contents;
  }

  .ps-reaction-more-page[hidden] {
    display: none !important;
  }

  .ps-reaction-more-item,
  .ps-reaction-option {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    border: 0 !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    cursor: pointer !important;
    transition: background-color 0.14s ease, color 0.14s ease, transform 0.14s ease;
  }

  .ps-reaction-more-item:hover,
  .ps-reaction-more-item:focus-visible,
  .ps-reaction-option:hover,
  .ps-reaction-option:focus-visible {
    background: #f3f4f6 !important;
    color: #2563eb !important;
  }

  .ps-reaction-more-item:active,
  .ps-reaction-option:active {
    background: #e5e7eb !important;
    color: #2563eb !important;
    transform: scale(0.94);
  }

  .ps-reaction-more-item img,
  .ps-reaction-more-item svg,
  .ps-reaction-option img,
  .ps-reaction-option svg {
    width: 27px !important;
    height: 27px !important;
    display: block !important;
    object-fit: cover !important;
  }

  .ps-reaction-more-footer {
    grid-column: 1 / -1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    margin-top: 2px !important;
  }

  .ps-reaction-more-nav {
    width: 28px !important;
    height: 28px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #f4f4f5 !important;
    color: #111827 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    padding: 0 !important;
  }

  .ps-reaction-more-nav:hover:not(:disabled) {
    background: #eeeeef !important;
    color: #2563eb !important;
  }

  .ps-reaction-more-nav:disabled {
    opacity: 0.35 !important;
    cursor: not-allowed !important;
  }

  .ps-reaction-more-counter {
    color: #6b7280 !important;
    font-size: 12px !important;
    line-height: 1 !important;
  }

  @media (max-width: 640px) {
    .ps-reaction-more-menu,
    .ps-reaction-menu {
      right: auto !important;
      left: 0 !important;
      width: 208px !important;
    }
  }


  /* FINAL: Tepki menüsü 2. görseldeki kompakt kart görünümü */
  .ps-reaction-row {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
    margin: 0 !important;
    overflow: visible !important;
  }

  .ps-reaction-pill,
  .ps-reaction-more-trigger,
  .ps-reaction-trigger {
    height: 30px !important;
    min-height: 30px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #f4f4f5 !important;
    color: #111827 !important;
    box-shadow: none !important;
  }

  .ps-reaction-pill {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    padding: 0 11px !important;
    font-size: 13px !important;
    line-height: 1 !important;
    font-weight: 500 !important;
  }

  .ps-reaction-pill:hover,
  .ps-reaction-more-trigger:hover,
  .ps-reaction-more-trigger[aria-expanded="true"],
  .ps-reaction-trigger:hover,
  .ps-reaction-trigger[aria-expanded="true"] {
    background: #eeeeef !important;
    color: #2563eb !important;
  }

  .ps-reaction-pill img,
  .ps-reaction-pill svg,
  .ps-reaction-pill .ps-reaction-media {
    width: 22px !important;
    height: 22px !important;
    display: block !important;
    object-fit: cover !important;
  }

  .ps-reaction-picker,
  .ps-reaction-more-wrap {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    overflow: visible !important;
  }

  .ps-reaction-menu,
  .ps-reaction-more-menu {
    position: absolute !important;
    right: 0 !important;
    left: auto !important;
    top: calc(100% + 10px) !important;
    z-index: 999 !important;
    width: 208px !important;
    max-width: calc(100vw - 32px) !important;
    padding: 10px 12px 14px !important;
    border-radius: 7px !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .ps-reaction-menu[hidden],
  .ps-reaction-more-menu[hidden] {
    display: none !important;
  }

  .ps-reaction-menu:not([hidden]),
  .ps-reaction-more-menu:not([hidden]) {
    display: grid !important;
    grid-template-columns: repeat(4, 34px) !important;
    gap: 12px 13px !important;
    align-items: center !important;
    justify-content: start !important;
  }

  .ps-reaction-menu-title,
  .ps-reaction-more-title {
    grid-column: 1 / -1 !important;
    color: #6b7280 !important;
    font-size: 13px !important;
    line-height: 1.2 !important;
    margin: 0 0 3px !important;
    font-weight: 400 !important;
  }

  .ps-reaction-form,
  .ps-reaction-menu .ps-reaction-form {
    width: 34px !important;
    height: 34px !important;
    margin: 0 !important;
    display: inline-flex !important;
  }

  .ps-reaction-option,
  .ps-reaction-more-item {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    border: 0 !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    cursor: pointer !important;
    transition: background-color 0.14s ease, color 0.14s ease, transform 0.14s ease !important;
  }

  .ps-reaction-option:hover,
  .ps-reaction-option:focus-visible,
  .ps-reaction-more-item:hover,
  .ps-reaction-more-item:focus-visible {
    background: #f3f4f6 !important;
    color: #2563eb !important;
  }

  .ps-reaction-option:active,
  .ps-reaction-more-item:active {
    background: #e5e7eb !important;
    color: #2563eb !important;
    transform: scale(0.94) !important;
  }

  .ps-reaction-option img,
  .ps-reaction-option svg,
  .ps-reaction-option .ps-reaction-media,
  .ps-reaction-more-item img,
  .ps-reaction-more-item svg,
  .ps-reaction-more-item .ps-reaction-media {
    width: 28px !important;
    height: 28px !important;
    max-width: 28px !important;
    max-height: 28px !important;
    display: block !important;
    object-fit: cover !important;
  }

  .ps-reaction-more-page {
    display: contents !important;
  }

  .ps-reaction-more-page[hidden] {
    display: none !important;
  }

  .ps-reaction-more-footer {
    grid-column: 1 / -1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    margin-top: 2px !important;
  }

  .ps-reaction-more-nav {
    width: 28px !important;
    height: 28px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #f4f4f5 !important;
    color: #111827 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    padding: 0 !important;
  }

  .ps-reaction-more-nav:hover:not(:disabled) {
    background: #eeeeef !important;
    color: #2563eb !important;
  }

  .ps-reaction-more-nav:disabled {
    opacity: 0.35 !important;
    cursor: not-allowed !important;
  }

  .ps-reaction-more-counter {
    color: #6b7280 !important;
    font-size: 12px !important;
    line-height: 1 !important;
  }

  @media (max-width: 640px) {
    .ps-reaction-menu,
    .ps-reaction-more-menu {
      right: auto !important;
      left: 0 !important;
      width: 208px !important;
    }
  }

  /* =========================================================
     Reaction açılır menü düzeltmesi:
     - Emojiler/görseller büyük
     - Üzerine gelince gri arka plan
     - Görseldeki gibi 4 sütun kompakt menü
     ========================================================= */

  .ps-post-card,
  .ps-post-card-inner,
  .ps-actions-bar,
  .ps-reaction-row,
  .ps-reaction-picker,
  .ps-reaction-more-wrap {
    overflow: visible !important;
  }

  .ps-reaction-menu {
    position: absolute !important;
    right: 0 !important;
    left: auto !important;
    top: calc(100% + 10px) !important;
    z-index: 9999 !important;
    width: 204px !important;
    max-width: calc(100vw - 24px) !important;
    padding: 10px !important;
    border-radius: 10px !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .ps-reaction-menu[hidden] {
    display: none !important;
  }

  .ps-reaction-menu:not([hidden]) {
    display: grid !important;
    grid-template-columns: repeat(4, 36px) !important;
    gap: 10px 12px !important;
    align-items: center !important;
    justify-content: start !important;
  }

  .ps-reaction-menu-title {
    grid-column: 1 / -1 !important;
    flex: none !important;
    margin: 0 0 2px !important;
    padding: 0 !important;
    color: #6b7280 !important;
    font-size: 13px !important;
    line-height: 1.2 !important;
    font-weight: 400 !important;
  }

  .ps-reaction-menu .ps-reaction-form {
    width: 36px !important;
    height: 36px !important;
    margin: 0 !important;
    display: inline-flex !important;
  }

  .ps-reaction-option {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 0 !important;
    border-radius: 10px !important;
    background: transparent !important;
    color: #111827 !important;
    padding: 0 !important;
    cursor: pointer !important;
    font-size: 27px !important;
    line-height: 1 !important;
    box-shadow: none !important;
    transition: background-color 0.14s ease, color 0.14s ease, transform 0.14s ease !important;
  }

  .ps-reaction-option:hover,
  .ps-reaction-option:focus-visible {
    background: #f1f2f4 !important;
    color: #111827 !important;
    outline: none !important;
  }

  .ps-reaction-option:active {
    background: #e5e7eb !important;
    transform: scale(0.94) !important;
  }

  .ps-reaction-option img,
  .ps-reaction-option svg,
  .ps-reaction-option .ps-reaction-media {
    width: 28px !important;
    height: 28px !important;
    max-width: 28px !important;
    max-height: 28px !important;
    display: block !important;
    object-fit: cover !important;
    border-radius: 8px !important;
    flex: 0 0 auto !important;
  }

  .ps-reaction-option img[src*="avatar"],
  .ps-reaction-option img[src*="profile"],
  .ps-reaction-option img[src*="storage"],
  .ps-reaction-option .ps-reaction-media {
    border-radius: 999px !important;
  }

  /* Daha fazla tepkiler menüsü de aynı hissi versin */
  .ps-reaction-more-menu {
    border-radius: 10px !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  .ps-reaction-more-item {
    border-radius: 10px !important;
    transition: background-color 0.14s ease, transform 0.14s ease !important;
  }

  .ps-reaction-more-item:hover,
  .ps-reaction-more-item:focus-visible {
    background: #f1f2f4 !important;
    outline: none !important;
  }

  .ps-reaction-more-item:active {
    background: #e5e7eb !important;
    transform: scale(0.97) !important;
  }

  .ps-reaction-more-icon,
  .ps-reaction-more-icon img,
  .ps-reaction-more-icon svg {
    width: 28px !important;
    height: 28px !important;
    max-width: 28px !important;
    max-height: 28px !important;
    object-fit: cover !important;
  }

  @media (max-width: 640px) {
    .ps-reaction-menu {
      right: 0 !important;
      left: auto !important;
      width: 204px !important;
      max-width: calc(100vw - 20px) !important;
      padding: 10px !important;
    }

    .ps-reaction-menu:not([hidden]) {
      grid-template-columns: repeat(4, 36px) !important;
      gap: 10px 12px !important;
    }
  }

  .dark .ps-reaction-menu,
  html.dark .ps-reaction-menu,
  body.dark .ps-reaction-menu,
  .dark .ps-reaction-more-menu,
  html.dark .ps-reaction-more-menu,
  body.dark .ps-reaction-more-menu {
    background: #18181b !important;
    border-color: #27272a !important;
    box-shadow: none !important;
  }

  .dark .ps-reaction-menu-title,
  html.dark .ps-reaction-menu-title,
  body.dark .ps-reaction-menu-title,
  .dark .ps-reaction-more-title,
  html.dark .ps-reaction-more-title,
  body.dark .ps-reaction-more-title {
    color: #a1a1aa !important;
  }

  .dark .ps-reaction-option,
  html.dark .ps-reaction-option,
  body.dark .ps-reaction-option {
    color: #ffffff !important;
  }

  .dark .ps-reaction-option:hover,
  .dark .ps-reaction-option:focus-visible,
  html.dark .ps-reaction-option:hover,
  html.dark .ps-reaction-option:focus-visible,
  body.dark .ps-reaction-option:hover,
  body.dark .ps-reaction-option:focus-visible,
  .dark .ps-reaction-more-item:hover,
  .dark .ps-reaction-more-item:focus-visible,
  html.dark .ps-reaction-more-item:hover,
  html.dark .ps-reaction-more-item:focus-visible,
  body.dark .ps-reaction-more-item:hover,
  body.dark .ps-reaction-more-item:focus-visible {
    background: #27272a !important;
    color: #ffffff !important;
  }

  .dark .ps-reaction-option:active,
  html.dark .ps-reaction-option:active,
  body.dark .ps-reaction-option:active,
  .dark .ps-reaction-more-item:active,
  html.dark .ps-reaction-more-item:active,
  body.dark .ps-reaction-more-item:active {
    background: #3f3f46 !important;
  }



  /* FINAL: Post-show sayfasını post-card görünümüne yaklaştır ve tüm içeriği kesmeden göster */
  .ps-layout.post-show-shell {
    width: 100% !important;
    max-width: 720px !important;
    padding: 72px 12px 96px !important;
    gap: 0 !important;
  }

  .post-show-shell .ps-sidebar-left,
  .post-show-shell .ps-sidebar-right {
    display: none !important;
  }

  .post-show-shell .ps-main {
    flex: 1 1 auto !important;
    width: 100% !important;
    max-width: 656px !important;
    margin: 0 auto !important;
    gap: 14px !important;
  }

  .post-show-shell .ps-post-card {
    width: 100% !important;
    border: 0 !important;
    border-radius: 18px !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-post-card-inner {
    padding: 16px 16px 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-post-author-row {
    align-items: center !important;
    margin-bottom: 14px !important;
  }

  .post-show-shell .post-author-avatar-wrap {
    width: 48px !important;
    height: 48px !important;
    flex-basis: 48px !important;
  }

  .post-show-shell .post-author-avatar {
    width: 48px !important;
    height: 48px !important;
  }

  .post-show-shell .post-author-name {
    font-size: 16px !important;
    line-height: 1.2 !important;
  }

  .post-show-shell .post-author-meta {
    font-size: 13px !important;
    gap: 7px !important;
  }

  .post-show-shell .ps-menu-trigger {
    width: 38px !important;
    height: 38px !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    background: transparent !important;
  }

  .post-show-shell .ps-menu-trigger:hover,
  .post-show-shell .ps-menu-trigger:focus-visible {
    background: #f3f4f6 !important;
    outline: none !important;
  }

  .post-show-shell .ps-post-title {
    margin: 0 0 14px !important;
    color: #0f172a !important;
    font-size: 20px !important;
    font-weight: 600 !important;
    line-height: 1.42 !important;
  }

  .post-show-shell .ps-post-image,
  .post-show-shell .ps-full-media,
  .post-show-shell .ps-full-gallery {
    width: 100% !important;
    margin: 0 0 14px !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    background: #f8fafc !important;
  }

  .post-show-shell .ps-post-image img,
  .post-show-shell .ps-full-media img,
  .post-show-shell .ps-full-media video,
  .post-show-shell .ps-full-media iframe {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    border: 0 !important;
    border-radius: 18px !important;
    background: #f8fafc !important;
  }

  .post-show-shell .ps-post-image img,
  .post-show-shell .ps-full-media img {
    height: auto !important;
    object-fit: contain !important;
  }

  .post-show-shell .ps-full-media--embed iframe,
  .post-show-shell .ps-full-media--video video {
    min-height: 360px !important;
    aspect-ratio: 16 / 9 !important;
  }

  .post-show-shell .ps-full-gallery {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 10px !important;
    background: transparent !important;
  }

  .post-show-shell .ps-post-body {
    margin: 0 0 14px !important;
    color: #30343a !important;
    font-size: 16px !important;
    font-weight: 400 !important;
    line-height: 1.68 !important;
    white-space: normal !important;
    overflow: visible !important;
    max-height: none !important;
    display: block !important;
  }

  .post-show-shell .ps-post-body > *:first-child {
    margin-top: 0 !important;
  }

  .post-show-shell .ps-post-body p {
    margin: 0 0 12px !important;
  }

  .post-show-shell .ps-post-body h2,
  .post-show-shell .ps-post-body h3,
  .post-show-shell .ps-post-body h4 {
    margin: 18px 0 10px !important;
    color: #0f172a !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
  }

  .post-show-shell .ps-post-body blockquote {
    margin: 12px 0 !important;
    padding: 12px 14px !important;
    border-left: 3px solid #e5e7eb !important;
    border-radius: 12px !important;
    background: #f8fafc !important;
  }

  .post-show-shell .ps-post-body ul,
  .post-show-shell .ps-post-body ol {
    margin: 0 0 12px 20px !important;
    padding: 0 !important;
  }

  .post-show-shell .ps-checklist {
    list-style: none !important;
    margin-left: 0 !important;
  }

  .post-show-shell .ps-checklist li {
    display: flex !important;
    gap: 8px !important;
    align-items: flex-start !important;
  }

  .post-show-shell .ps-check-dot {
    color: #2563eb !important;
    font-weight: 600 !important;
  }

  .post-show-shell .ps-table-wrap {
    width: 100% !important;
    overflow-x: auto !important;
    margin: 12px 0 !important;
  }

  .post-show-shell .ps-table-wrap table {
    width: 100% !important;
    border-collapse: collapse !important;
  }

  .post-show-shell .ps-table-wrap td {
    border: 1px solid #e5e7eb !important;
    padding: 8px 10px !important;
  }

  .post-show-shell figcaption {
    padding: 8px 2px 0 !important;
    color: #64748b !important;
    font-size: 13px !important;
    line-height: 1.45 !important;
  }

  .post-show-shell .ps-tags-row {
    margin: 0 0 14px !important;
  }

  .post-show-shell .ps-actions-bar {
    border-top: 0 !important;
    padding-top: 8px !important;
  }

  .post-show-shell .ps-comments-section {
    border-radius: 18px !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  @media (max-width: 640px) {
    .ps-layout.post-show-shell {
      max-width: 100% !important;
      padding: 0 0 96px !important;
    }

    .post-show-shell .ps-main {
      max-width: 100% !important;
    }

    .post-show-shell .ps-post-card,
    .post-show-shell .ps-comments-section {
      border-radius: 0 !important;
    }

    .post-show-shell .ps-post-card-inner {
      padding: 14px 12px 0 !important;
    }

    .post-show-shell .ps-post-title {
      font-size: 18px !important;
    }

    .post-show-shell .ps-post-body {
      font-size: 15px !important;
      line-height: 1.62 !important;
    }

    .post-show-shell .ps-full-media--embed iframe,
    .post-show-shell .ps-full-media--video video {
      min-height: 240px !important;
    }
  }

  html.dark .post-show-shell .ps-post-card,
  body.dark .post-show-shell .ps-post-card,
  .dark .post-show-shell .ps-post-card,
  [data-theme="dark"] .post-show-shell .ps-post-card,
  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section {
    background: #0f172a !important;
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-post-title,
  body.dark .post-show-shell .ps-post-title,
  .dark .post-show-shell .ps-post-title,
  [data-theme="dark"] .post-show-shell .ps-post-title,
  html.dark .post-show-shell .ps-post-body,
  body.dark .post-show-shell .ps-post-body,
  .dark .post-show-shell .ps-post-body,
  [data-theme="dark"] .post-show-shell .ps-post-body {
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-post-body blockquote,
  body.dark .post-show-shell .ps-post-body blockquote,
  .dark .post-show-shell .ps-post-body blockquote,
  [data-theme="dark"] .post-show-shell .ps-post-body blockquote {
    background: rgba(255,255,255,.06) !important;
    border-left-color: rgba(255,255,255,.18) !important;
  }


  /* FINAL OVERRIDE: Poppins + normal font weights + relative post date */
  .post-show-shell,
  .post-show-shell * {
    font-family: Poppins, Arial, sans-serif !important;
  }

  .post-show-shell :where(
    .ps-post-body,
    .ps-post-body *,
    .ps-post-subline,
    .ps-post-subline *,
    .ps-post-time,
    .ps-post-subline-date,
    .post-author-meta,
    .post-author-date,
    .ps-action-btn,
    .ps-action-count,
    .ps-view-count,
    .ps-reaction-pill,
    .ps-tag,
    .ps-comment-text,
    .ps-comment-time,
    .ps-comment-role,
    .ps-comment-action,
    .ps-comment-reply-text,
    .ps-source-label,
    .ps-source-domain
  ) {
    font-weight: 400 !important;
  }

  .post-show-shell :where(
    .ps-post-title,
    .ps-post-author-name,
    .post-author-name,
    .ps-post-subline-category,
    .post-author-category,
    .ps-comments-title,
    .ps-comment-author,
    .ps-sidebar-card-title,
    .ps-rc-name,
    .ps-tag-name
  ) {
    font-weight: 500 !important;
  }

  .post-show-shell .ps-post-title {
    letter-spacing: -0.01em;
  }

  .post-show-shell strong,
  .post-show-shell b {
    font-weight: 500 !important;
  }



  /* FINAL OVERRIDE: Kalın font yok - başlık dahil normal Poppins */
  .post-show-shell,
  .post-show-shell * {
    font-family: Poppins, Arial, sans-serif !important;
  }

  .post-show-shell .ps-post-title,
  .post-show-shell h1.ps-post-title,
  .post-show-shell .ps-post-body h1,
  .post-show-shell .ps-post-body h2,
  .post-show-shell .ps-post-body h3,
  .post-show-shell .ps-post-body h4,
  .post-show-shell .ps-post-body h5,
  .post-show-shell .ps-post-body h6,
  .post-show-shell .ps-comments-title,
  .post-show-shell .ps-post-author-name,
  .post-show-shell .post-author-name,
  .post-show-shell .ps-post-subline-category,
  .post-show-shell .post-author-category,
  .post-show-shell .ps-comment-author,
  .post-show-shell .ps-tag,
  .post-show-shell .ps-reaction-pill,
  .post-show-shell strong,
  .post-show-shell b {
    font-weight: 400 !important;
  }

  .post-show-shell .ps-post-title,
  .post-show-shell h1.ps-post-title {
    font-size: 19px !important;
    line-height: 1.45 !important;
    letter-spacing: 0 !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-post-title,
    .post-show-shell h1.ps-post-title {
      font-size: 18px !important;
      line-height: 1.45 !important;
      font-weight: 400 !important;
    }
  }



  /* FINAL: yorum like/dislike, cevabı ikon yap, düzenle/sil 3 nokta menüsü */
  .post-show-shell .ps-comment-actions { align-items: center !important; gap: 10px !important; overflow: visible !important; position: relative !important; }
  .post-show-shell .ps-comment-votes { display: inline-flex !important; align-items: center !important; gap: 8px !important; }
  .post-show-shell .ps-comment-votes form, .post-show-shell .ps-comment-delete-form { display: inline-flex !important; align-items: center !important; margin: 0 !important; }
  .post-show-shell .ps-comment-vote-btn, .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn, .post-show-shell .ps-comment-more-trigger { width: 32px !important; height: 32px !important; min-width: 32px !important; min-height: 32px !important; border: 0 !important; border-radius: 999px !important; background: transparent !important; color: #64748b !important; padding: 0 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; cursor: pointer !important; box-shadow: none !important; text-decoration: none !important; line-height: 1 !important; }
  .post-show-shell .ps-comment-vote-btn:hover, .post-show-shell .ps-comment-vote-btn:focus-visible, .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover, .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:focus-visible, .post-show-shell .ps-comment-more-trigger:hover, .post-show-shell .ps-comment-more-trigger:focus-visible, .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger { background: #f3f4f6 !important; color: #111827 !important; outline: none !important; }
  .post-show-shell .ps-comment-vote-btn:active, .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:active, .post-show-shell .ps-comment-more-trigger:active { background: #e5e7eb !important; color: #0f172a !important; }
  .post-show-shell .ps-comment-like-btn:hover, .post-show-shell .ps-comment-like-btn:focus-visible { color: #2563eb !important; }
  .post-show-shell .ps-comment-dislike-btn:hover, .post-show-shell .ps-comment-dislike-btn:focus-visible { color: #ef4444 !important; }
  .post-show-shell .ps-comment-vote-btn svg, .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn svg, .post-show-shell .ps-comment-more-trigger svg { width: 18px !important; height: 18px !important; display: block !important; }
  .post-show-shell .ps-comment-reply-text { display: none !important; }
  .post-show-shell .ps-comment-more { position: relative !important; display: inline-flex !important; align-items: center !important; overflow: visible !important; }
  .post-show-shell .ps-comment-more-menu { position: absolute !important; left: 0 !important; top: calc(100% + 8px) !important; z-index: 80 !important; min-width: 142px !important; padding: 6px !important; border: 1px solid #e5e7eb !important; border-radius: 12px !important; background: #ffffff !important; box-shadow: none !important; }
  .post-show-shell .ps-comment-more-menu[hidden] { display: none !important; }
  .post-show-shell .ps-comment-more-menu:not([hidden]) { display: flex !important; flex-direction: column !important; gap: 2px !important; }
  .post-show-shell .ps-comment-more-item { width: 100% !important; min-height: 36px !important; border: 0 !important; border-radius: 9px !important; background: transparent !important; color: #334155 !important; padding: 8px 10px !important; display: flex !important; align-items: center !important; justify-content: flex-start !important; gap: 9px !important; cursor: pointer !important; text-align: left !important; font-family: Poppins, Arial, sans-serif !important; font-size: 13px !important; font-weight: 400 !important; line-height: 1.2 !important; text-decoration: none !important; }
  .post-show-shell .ps-comment-more-item:hover, .post-show-shell .ps-comment-more-item:focus-visible { background: #f3f4f6 !important; color: #0f172a !important; outline: none !important; }
  .post-show-shell .ps-comment-more-item svg { width: 16px !important; height: 16px !important; display: block !important; flex: 0 0 16px !important; }
  .post-show-shell .ps-comment-more-item--danger { color: #dc2626 !important; }
  .post-show-shell .ps-comment-more-item--danger:hover, .post-show-shell .ps-comment-more-item--danger:focus-visible { background: #fef2f2 !important; color: #b91c1c !important; }
  html.dark .post-show-shell .ps-comment-more-menu, body.dark .post-show-shell .ps-comment-more-menu, .dark .post-show-shell .ps-comment-more-menu, [data-theme="dark"] .post-show-shell .ps-comment-more-menu { background: #111827 !important; border-color: rgba(255,255,255,0.12) !important; box-shadow: none !important; }
  html.dark .post-show-shell .ps-comment-vote-btn, html.dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn, html.dark .post-show-shell .ps-comment-more-trigger, html.dark .post-show-shell .ps-comment-more-item, body.dark .post-show-shell .ps-comment-vote-btn, body.dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn, body.dark .post-show-shell .ps-comment-more-trigger, body.dark .post-show-shell .ps-comment-more-item, .dark .post-show-shell .ps-comment-vote-btn, .dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn, .dark .post-show-shell .ps-comment-more-trigger, .dark .post-show-shell .ps-comment-more-item, [data-theme="dark"] .post-show-shell .ps-comment-vote-btn, [data-theme="dark"] .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn, [data-theme="dark"] .post-show-shell .ps-comment-more-trigger, [data-theme="dark"] .post-show-shell .ps-comment-more-item { color: #e5e7eb !important; }
  html.dark .post-show-shell .ps-comment-vote-btn:hover, html.dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover, html.dark .post-show-shell .ps-comment-more-trigger:hover, html.dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger, html.dark .post-show-shell .ps-comment-more-item:hover, body.dark .post-show-shell .ps-comment-vote-btn:hover, body.dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover, body.dark .post-show-shell .ps-comment-more-trigger:hover, body.dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger, body.dark .post-show-shell .ps-comment-more-item:hover, .dark .post-show-shell .ps-comment-vote-btn:hover, .dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover, .dark .post-show-shell .ps-comment-more-trigger:hover, .dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger, .dark .post-show-shell .ps-comment-more-item:hover, [data-theme="dark"] .post-show-shell .ps-comment-vote-btn:hover, [data-theme="dark"] .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover, [data-theme="dark"] .post-show-shell .ps-comment-more-trigger:hover, [data-theme="dark"] .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger, [data-theme="dark"] .post-show-shell .ps-comment-more-item:hover { background: rgba(255,255,255,0.10) !important; color: #ffffff !important; }



  /* FINAL: yıldız yerine tepki ikonları + yorum 3 nokta menüsü kesilmesin */
  .post-show-shell .ps-reaction-row {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
  }

  .post-show-shell .ps-reaction-pill span:first-child,
  .post-show-shell .ps-reaction-more-item {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
  }

  .post-show-shell .ps-reaction-pill img,
  .post-show-shell .ps-reaction-pill svg,
  .post-show-shell .ps-reaction-media {
    width: 22px !important;
    height: 22px !important;
    min-width: 22px !important;
    min-height: 22px !important;
    border-radius: 999px !important;
    object-fit: cover !important;
    display: block !important;
  }

  .post-show-shell .ps-reaction-pill {
    min-width: 42px !important;
    height: 32px !important;
    min-height: 32px !important;
    padding: 0 10px !important;
    gap: 6px !important;
    background: transparent !important;
    color: #111827 !important;
    border-radius: 999px !important;
  }

  .post-show-shell .ps-reaction-pill:hover,
  .post-show-shell .ps-reaction-pill:focus-visible,
  .post-show-shell .ps-reaction-trigger[aria-expanded="true"],
  .post-show-shell .ps-reaction-more-trigger[aria-expanded="true"] {
    background: #f3f4f6 !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-post-card,
  .post-show-shell .ps-post-card-inner,
  .post-show-shell .ps-comments-section,
  .post-show-shell .ps-comments-list,
  .post-show-shell .ps-comment-item,
  .post-show-shell .ps-comment-card,
  .post-show-shell .ps-comment-body,
  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-replies {
    overflow: visible !important;
  }

  .post-show-shell .ps-comments-section {
    padding-bottom: 28px !important;
  }

  .post-show-shell .ps-comment-more {
    position: relative !important;
    z-index: 100 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-more.is-open {
    z-index: 9999 !important;
  }

  .post-show-shell .ps-comment-more-menu {
    right: auto !important;
    left: 50% !important;
    top: calc(100% + 8px) !important;
    transform: translateX(-50%) !important;
    z-index: 99999 !important;
    min-width: 150px !important;
    max-width: calc(100vw - 24px) !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-more-menu::before {
    content: "";
    position: absolute;
    top: -6px;
    left: 50%;
    width: 12px;
    height: 12px;
    background: inherit;
    border-left: 1px solid inherit;
    border-top: 1px solid inherit;
    transform: translateX(-50%) rotate(45deg);
  }

  .post-show-shell .ps-comment-more-item {
    position: relative !important;
    z-index: 1 !important;
    background: transparent !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comment-more-menu {
      left: auto !important;
      right: -10px !important;
      transform: none !important;
    }

    .post-show-shell .ps-comment-more-menu::before {
      left: auto !important;
      right: 20px !important;
      transform: rotate(45deg) !important;
    }
  }

  html.dark .post-show-shell .ps-reaction-pill:hover,
  html.dark .post-show-shell .ps-reaction-pill:focus-visible,
  html.dark .post-show-shell .ps-reaction-trigger[aria-expanded="true"],
  html.dark .post-show-shell .ps-reaction-more-trigger[aria-expanded="true"],
  body.dark .post-show-shell .ps-reaction-pill:hover,
  body.dark .post-show-shell .ps-reaction-pill:focus-visible,
  body.dark .post-show-shell .ps-reaction-trigger[aria-expanded="true"],
  body.dark .post-show-shell .ps-reaction-more-trigger[aria-expanded="true"],
  .dark .post-show-shell .ps-reaction-pill:hover,
  .dark .post-show-shell .ps-reaction-pill:focus-visible,
  .dark .post-show-shell .ps-reaction-trigger[aria-expanded="true"],
  .dark .post-show-shell .ps-reaction-more-trigger[aria-expanded="true"],
  [data-theme="dark"] .post-show-shell .ps-reaction-pill:hover,
  [data-theme="dark"] .post-show-shell .ps-reaction-pill:focus-visible,
  [data-theme="dark"] .post-show-shell .ps-reaction-trigger[aria-expanded="true"],
  [data-theme="dark"] .post-show-shell .ps-reaction-more-trigger[aria-expanded="true"] {
    background: rgba(255,255,255,.10) !important;
    color: #ffffff !important;
  }



  /* FINAL OVERRIDE: YouTube benzeri sade yorum ağacı */
  .post-show-shell .ps-comments-section {
    background: #ffffff !important;
    border-radius: 10px !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comments-list,
  .post-show-shell .ps-comment-item,
  .post-show-shell .ps-comment-body,
  .post-show-shell .ps-replies {
    overflow: visible !important;
  }

  .post-show-shell .ps-comments-list {
    padding: 18px 20px 30px !important;
    gap: 0 !important;
  }

  .post-show-shell .ps-comment-item {
    position: relative !important;
    display: flex !important;
    gap: 12px !important;
    padding: 0 0 18px !important;
    margin: 0 !important;
    border: 0 !important;
  }

  .post-show-shell .ps-comment-avatar {
    width: 40px !important;
    height: 40px !important;
    min-width: 40px !important;
    border-radius: 999px !important;
    border: 0 !important;
    background: #0891a6 !important;
    color: #ffffff !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 16px !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-replies .ps-comment-avatar {
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
    font-size: 11px !important;
  }

  .post-show-shell .ps-comment-body {
    flex: 1 1 auto !important;
    min-width: 0 !important;
  }

  .post-show-shell .ps-comment-meta {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    flex-wrap: wrap !important;
    min-height: 20px !important;
    line-height: 1.25 !important;
  }

  .post-show-shell .ps-comment-author {
    color: #111827 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 13px !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-comment-time,
  .post-show-shell .ps-comment-role {
    color: #6b7280 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-role {
    color: #6b7280 !important;
  }

  .post-show-shell .ps-comment-text {
    margin-top: 3px !important;
    color: #111827 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 1.45 !important;
    white-space: pre-line !important;
  }

  .post-show-shell .ps-comment-actions {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    margin-top: 10px !important;
    flex-wrap: nowrap !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 9px !important;
  }

  .post-show-shell .ps-comment-votes form,
  .post-show-shell .ps-comment-delete-form {
    display: inline-flex !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-more-trigger {
    width: auto !important;
    min-width: 22px !important;
    height: 24px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #111827 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 2px !important;
    box-shadow: none !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: none !important;
    transform: none !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-vote-btn:focus-visible,
  .post-show-shell .ps-comment-reply-icon-btn:focus-visible,
  .post-show-shell .ps-comment-more-trigger:focus-visible {
    background: #f1f3f4 !important;
    color: #111827 !important;
    outline: none !important;
  }

  .post-show-shell .ps-comment-vote-btn svg,
  .post-show-shell .ps-comment-more-trigger svg {
    width: 17px !important;
    height: 17px !important;
    display: block !important;
  }

  .post-show-shell .ps-comment-vote-count {
    color: #374151 !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
    min-width: 0 !important;
  }

  .post-show-shell .ps-comment-reply-icon-btn {
    padding: 0 8px !important;
    color: #111827 !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
  }

  .post-show-shell .ps-comment-more {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    margin-left: 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-more-trigger {
    width: 30px !important;
    min-width: 30px !important;
    height: 30px !important;
    padding: 0 !important;
  }

  .post-show-shell .ps-comment-more-menu {
    position: absolute !important;
    right: 0 !important;
    top: calc(100% + 8px) !important;
    z-index: 9999 !important;
    min-width: 150px !important;
    padding: 8px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-more-menu[hidden] {
    display: none !important;
  }

  .post-show-shell .ps-comment-more-item {
    width: 100% !important;
    min-height: 38px !important;
    padding: 8px 10px !important;
    border: 0 !important;
    border-radius: 9px !important;
    background: transparent !important;
    color: #374151 !important;
    display: flex !important;
    align-items: center !important;
    gap: 9px !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    text-align: left !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-comment-more-item:hover,
  .post-show-shell .ps-comment-more-item:focus-visible {
    background: #f3f4f6 !important;
    color: #111827 !important;
    outline: none !important;
  }

  .post-show-shell .ps-comment-more-item--danger {
    color: #b42318 !important;
  }

  .post-show-shell .ps-comment-more-item svg {
    width: 17px !important;
    height: 17px !important;
  }

  .post-show-shell .ps-replies {
    position: relative !important;
    margin: 16px 0 0 -22px !important;
    padding-left: 42px !important;
    border-left: 1px solid #e5e7eb !important;
  }

  .post-show-shell .ps-replies::before,
  .post-show-shell .ps-replies .ps-comment-item::before {
    content: "";
    position: absolute;
    pointer-events: none;
  }

  .post-show-shell .ps-replies .ps-comment-item {
    gap: 10px !important;
    padding: 0 0 18px !important;
  }

  .post-show-shell .ps-replies .ps-comment-item::before {
    left: -42px;
    top: 13px;
    width: 30px;
    height: 28px;
    border-left: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    border-bottom-left-radius: 12px;
  }

  .post-show-shell .ps-replies.is-collapsed .ps-comment-item:nth-of-type(n+3) {
    display: none !important;
  }

  .post-show-shell .ps-replies-toggle {
    margin: -2px 0 10px 0 !important;
    padding: 0 4px !important;
    border: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-replies-toggle svg {
    width: 18px !important;
    height: 18px !important;
    transition: none !important;
  }

  .post-show-shell .ps-replies.is-collapsed .ps-replies-toggle svg {
    transform: rotate(180deg) !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comments-list {
      padding: 16px 12px 28px !important;
    }

    .post-show-shell .ps-comment-avatar {
      width: 36px !important;
      height: 36px !important;
      min-width: 36px !important;
      font-size: 14px !important;
    }

    .post-show-shell .ps-replies {
      margin-left: -20px !important;
      padding-left: 36px !important;
    }

    .post-show-shell .ps-replies .ps-comment-item::before {
      left: -36px !important;
      width: 26px !important;
    }
  }

  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section {
    background: #0f172a !important;
  }

  html.dark .post-show-shell .ps-comment-author,
  html.dark .post-show-shell .ps-comment-text,
  html.dark .post-show-shell .ps-comment-vote-btn,
  html.dark .post-show-shell .ps-comment-reply-icon-btn,
  html.dark .post-show-shell .ps-comment-more-trigger,
  html.dark .post-show-shell .ps-replies-toggle,
  body.dark .post-show-shell .ps-comment-author,
  body.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-vote-btn,
  body.dark .post-show-shell .ps-comment-reply-icon-btn,
  body.dark .post-show-shell .ps-comment-more-trigger,
  body.dark .post-show-shell .ps-replies-toggle,
  .dark .post-show-shell .ps-comment-author,
  .dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-vote-btn,
  .dark .post-show-shell .ps-comment-reply-icon-btn,
  .dark .post-show-shell .ps-comment-more-trigger,
  .dark .post-show-shell .ps-replies-toggle,
  [data-theme="dark"] .post-show-shell .ps-comment-author,
  [data-theme="dark"] .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn,
  [data-theme="dark"] .post-show-shell .ps-comment-reply-icon-btn,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger,
  [data-theme="dark"] .post-show-shell .ps-replies-toggle {
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-comment-time,
  html.dark .post-show-shell .ps-comment-role,
  html.dark .post-show-shell .ps-comment-vote-count,
  body.dark .post-show-shell .ps-comment-time,
  body.dark .post-show-shell .ps-comment-role,
  body.dark .post-show-shell .ps-comment-vote-count,
  .dark .post-show-shell .ps-comment-time,
  .dark .post-show-shell .ps-comment-role,
  .dark .post-show-shell .ps-comment-vote-count,
  [data-theme="dark"] .post-show-shell .ps-comment-time,
  [data-theme="dark"] .post-show-shell .ps-comment-role,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-count {
    color: #94a3b8 !important;
  }

  html.dark .post-show-shell .ps-replies,
  html.dark .post-show-shell .ps-replies .ps-comment-item::before,
  body.dark .post-show-shell .ps-replies,
  body.dark .post-show-shell .ps-replies .ps-comment-item::before,
  .dark .post-show-shell .ps-replies,
  .dark .post-show-shell .ps-replies .ps-comment-item::before,
  [data-theme="dark"] .post-show-shell .ps-replies,
  [data-theme="dark"] .post-show-shell .ps-replies .ps-comment-item::before {
    border-color: rgba(255, 255, 255, .14) !important;
  }

  html.dark .post-show-shell .ps-comment-vote-btn:hover,
  html.dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  html.dark .post-show-shell .ps-comment-more-trigger:hover,
  body.dark .post-show-shell .ps-comment-vote-btn:hover,
  body.dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  body.dark .post-show-shell .ps-comment-more-trigger:hover,
  .dark .post-show-shell .ps-comment-vote-btn:hover,
  .dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  .dark .post-show-shell .ps-comment-more-trigger:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-reply-icon-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger:hover {
    background: rgba(255, 255, 255, .08) !important;
  }


  /* @bahsetme: yazarken bosluga kadar mavi goster */
  .post-show-shell .ps-mention-token {
    color: #2563eb !important;
    font-weight: 400 !important;
    text-decoration: none !important;
  }

  .post-show-shell .ps-mention-live-wrap {
    position: relative !important;
    display: block !important;
    width: 100% !important;
  }

  .post-show-shell .ps-mention-live-layer,
  .post-show-shell .ps-mention-live-input {
    width: 100% !important;
    min-height: inherit !important;
    margin: 0 !important;
    border: 0 !important;
    background: transparent !important;
    font: inherit !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: inherit !important;
    line-height: inherit !important;
    letter-spacing: inherit !important;
    white-space: pre-wrap !important;
    overflow-wrap: anywhere !important;
    word-break: break-word !important;
  }

  .post-show-shell .ps-mention-live-layer {
    position: absolute !important;
    inset: 0 !important;
    z-index: 1 !important;
    pointer-events: none !important;
    color: #111827 !important;
    overflow: hidden !important;
  }

  .post-show-shell .ps-mention-live-input {
    position: relative !important;
    z-index: 2 !important;
    color: #111827 !important;
    caret-color: #111827 !important;
    resize: none !important;
  }

  .post-show-shell .ps-mention-live-wrap.has-value .ps-mention-live-input {
    color: transparent !important;
  }

  .post-show-shell .ps-mention-live-wrap.has-value .ps-mention-live-input::selection {
    color: #ffffff !important;
    background: #2563eb !important;
  }

  .post-show-shell .ps-mention-live-layer .ps-mention-token {
    color: #2563eb !important;
    font-weight: 400 !important;
  }

  html.dark .post-show-shell .ps-mention-live-layer,
  body.dark .post-show-shell .ps-mention-live-layer,
  .dark .post-show-shell .ps-mention-live-layer,
  [data-theme="dark"] .post-show-shell .ps-mention-live-layer {
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-mention-live-input,
  body.dark .post-show-shell .ps-mention-live-input,
  .dark .post-show-shell .ps-mention-live-input,
  [data-theme="dark"] .post-show-shell .ps-mention-live-input {
    color: #e5e7eb !important;
    caret-color: #ffffff !important;
  }

  html.dark .post-show-shell .ps-mention-live-wrap.has-value .ps-mention-live-input,
  body.dark .post-show-shell .ps-mention-live-wrap.has-value .ps-mention-live-input,
  .dark .post-show-shell .ps-mention-live-wrap.has-value .ps-mention-live-input,
  [data-theme="dark"] .post-show-shell .ps-mention-live-wrap.has-value .ps-mention-live-input {
    color: transparent !important;
  }

  html.dark .post-show-shell .ps-mention-token,
  body.dark .post-show-shell .ps-mention-token,
  .dark .post-show-shell .ps-mention-token,
  [data-theme="dark"] .post-show-shell .ps-mention-token {
    color: #60a5fa !important;
  }



  /* Mention link + inline Yanıtları gizle button */
  .post-show-shell .ps-mention-token--link {
    color: #2563eb !important;
    text-decoration: none !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-mention-token--link:hover,
  .post-show-shell .ps-mention-token--link:focus-visible {
    color: #1d4ed8 !important;
    text-decoration: underline !important;
    outline: none !important;
  }

  .post-show-shell .ps-replies-toggle--inline {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    min-height: 32px !important;
    padding: 0 10px !important;
    margin: 0 !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #475569 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
    cursor: pointer !important;
    white-space: nowrap !important;
  }

  .post-show-shell .ps-replies-toggle--inline:hover,
  .post-show-shell .ps-replies-toggle--inline:focus-visible {
    background: #f3f4f6 !important;
    color: #111827 !important;
    outline: none !important;
  }

  .post-show-shell .ps-replies-toggle--inline svg {
    width: 16px !important;
    height: 16px !important;
    transition: none !important;
  }

  .post-show-shell .ps-replies.is-collapsed > .ps-comment-item {
    display: none !important;
  }

  html.dark .post-show-shell .ps-mention-token--link,
  body.dark .post-show-shell .ps-mention-token--link,
  .dark .post-show-shell .ps-mention-token--link,
  [data-theme="dark"] .post-show-shell .ps-mention-token--link {
    color: #93c5fd !important;
  }

  html.dark .post-show-shell .ps-replies-toggle--inline,
  body.dark .post-show-shell .ps-replies-toggle--inline,
  .dark .post-show-shell .ps-replies-toggle--inline,
  [data-theme="dark"] .post-show-shell .ps-replies-toggle--inline {
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-replies-toggle--inline:hover,
  body.dark .post-show-shell .ps-replies-toggle--inline:hover,
  .dark .post-show-shell .ps-replies-toggle--inline:hover,
  [data-theme="dark"] .post-show-shell .ps-replies-toggle--inline:hover {
    background: rgba(255,255,255,0.10) !important;
    color: #ffffff !important;
  }



  /* FINAL FIX: Yanıtları gizle tıklanınca tüm alt yanıtlar gizlensin */
  .post-show-shell .ps-replies.is-collapsed > .ps-comment-item,
  .post-show-shell .ps-replies.is-collapsed > article.ps-comment-item {
    display: none !important;
  }

  .post-show-shell .ps-replies.is-collapsed {
    min-height: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
  }



  /* FINAL: modern yorum SVG ikon paketi + orantılı yuvarlak hover */
  .post-show-shell .ps-comments-section,
  .post-show-shell .ps-comments-list,
  .post-show-shell .ps-comment-item,
  .post-show-shell .ps-comment-body,
  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-replies {
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-actions {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    margin-top: 9px !important;
    flex-wrap: wrap !important;
    position: relative !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
  }

  .post-show-shell .ps-comment-votes form,
  .post-show-shell .ps-comment-delete-form {
    display: inline-flex !important;
    align-items: center !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-more-trigger {
    width: 32px !important;
    min-width: 32px !important;
    height: 32px !important;
    min-height: 32px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #475569 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: none !important;
    text-decoration: none !important;
    line-height: 1 !important;
    cursor: pointer !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    transition: none !important;
    transform: none !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-vote-btn:focus-visible,
  .post-show-shell .ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-reply-icon-btn:focus-visible,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-more-trigger:focus-visible,
  .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    outline: none !important;
    border-radius: 999px !important;
  }

  .post-show-shell .ps-comment-vote-btn:active,
  .post-show-shell .ps-comment-reply-icon-btn:active,
  .post-show-shell .ps-comment-more-trigger:active {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    transform: none !important;
  }

  .post-show-shell .ps-comment-like-btn:hover,
  .post-show-shell .ps-comment-like-btn:focus-visible {
    color: #2563eb !important;
  }

  .post-show-shell .ps-comment-dislike-btn:hover,
  .post-show-shell .ps-comment-dislike-btn:focus-visible {
    color: #dc2626 !important;
  }

  .post-show-shell .ps-modern-icon,
  .post-show-shell .ps-comment-vote-btn svg,
  .post-show-shell .ps-comment-reply-icon-btn svg,
  .post-show-shell .ps-comment-more-trigger svg {
    width: 19px !important;
    height: 19px !important;
    display: block !important;
    fill: none !important;
    stroke: currentColor !important;
    stroke-width: 1.8 !important;
    stroke-linecap: round !important;
    stroke-linejoin: round !important;
    flex: 0 0 auto !important;
  }

  .post-show-shell .ps-modern-icon--dots {
    width: 20px !important;
    height: 20px !important;
    stroke-width: 3.2 !important;
  }

  .post-show-shell .ps-modern-icon--comment {
    width: 18.5px !important;
    height: 18.5px !important;
  }

  .post-show-shell .ps-comment-vote-count {
    min-width: 0 !important;
    padding: 0 3px !important;
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
  }

  .post-show-shell .ps-replies-toggle--inline {
    height: 32px !important;
    min-height: 32px !important;
    padding: 0 10px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #475569 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
    box-shadow: none !important;
    transition: none !important;
    transform: none !important;
  }

  .post-show-shell .ps-replies-toggle--inline:hover,
  .post-show-shell .ps-replies-toggle--inline:focus-visible {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    outline: none !important;
  }

  .post-show-shell .ps-replies-toggle--inline:active {
    background: #e2e8f0 !important;
  }

  .post-show-shell .ps-replies-toggle--inline svg {
    width: 15px !important;
    height: 15px !important;
    stroke-width: 2 !important;
  }

  .post-show-shell .ps-comment-more {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    overflow: visible !important;
    z-index: 30 !important;
  }

  .post-show-shell .ps-comment-more-menu {
    z-index: 9999 !important;
    overflow: visible !important;
    border-radius: 14px !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-more-item {
    border-radius: 10px !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comment-actions {
      gap: 5px !important;
    }

    .post-show-shell .ps-comment-vote-btn,
    .post-show-shell .ps-comment-reply-icon-btn,
    .post-show-shell .ps-comment-more-trigger {
      width: 31px !important;
      min-width: 31px !important;
      height: 31px !important;
      min-height: 31px !important;
    }

    .post-show-shell .ps-replies-toggle--inline {
      height: 31px !important;
      min-height: 31px !important;
      padding-left: 9px !important;
      padding-right: 9px !important;
    }
  }

  html.dark .post-show-shell .ps-comment-vote-btn,
  html.dark .post-show-shell .ps-comment-reply-icon-btn,
  html.dark .post-show-shell .ps-comment-more-trigger,
  html.dark .post-show-shell .ps-replies-toggle--inline,
  body.dark .post-show-shell .ps-comment-vote-btn,
  body.dark .post-show-shell .ps-comment-reply-icon-btn,
  body.dark .post-show-shell .ps-comment-more-trigger,
  body.dark .post-show-shell .ps-replies-toggle--inline,
  .dark .post-show-shell .ps-comment-vote-btn,
  .dark .post-show-shell .ps-comment-reply-icon-btn,
  .dark .post-show-shell .ps-comment-more-trigger,
  .dark .post-show-shell .ps-replies-toggle--inline,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn,
  [data-theme="dark"] .post-show-shell .ps-comment-reply-icon-btn,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger,
  [data-theme="dark"] .post-show-shell .ps-replies-toggle--inline {
    color: #e5e7eb !important;
    background: transparent !important;
  }

  html.dark .post-show-shell .ps-comment-vote-btn:hover,
  html.dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  html.dark .post-show-shell .ps-comment-more-trigger:hover,
  html.dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  html.dark .post-show-shell .ps-replies-toggle--inline:hover,
  body.dark .post-show-shell .ps-comment-vote-btn:hover,
  body.dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  body.dark .post-show-shell .ps-comment-more-trigger:hover,
  body.dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  body.dark .post-show-shell .ps-replies-toggle--inline:hover,
  .dark .post-show-shell .ps-comment-vote-btn:hover,
  .dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  .dark .post-show-shell .ps-comment-more-trigger:hover,
  .dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  .dark .post-show-shell .ps-replies-toggle--inline:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-reply-icon-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  [data-theme="dark"] .post-show-shell .ps-replies-toggle--inline:hover {
    background: rgba(255,255,255,0.10) !important;
    color: #ffffff !important;
  }

  html.dark .post-show-shell .ps-comment-vote-btn:active,
  html.dark .post-show-shell .ps-comment-reply-icon-btn:active,
  html.dark .post-show-shell .ps-comment-more-trigger:active,
  html.dark .post-show-shell .ps-replies-toggle--inline:active,
  body.dark .post-show-shell .ps-comment-vote-btn:active,
  body.dark .post-show-shell .ps-comment-reply-icon-btn:active,
  body.dark .post-show-shell .ps-comment-more-trigger:active,
  body.dark .post-show-shell .ps-replies-toggle--inline:active,
  .dark .post-show-shell .ps-comment-vote-btn:active,
  .dark .post-show-shell .ps-comment-reply-icon-btn:active,
  .dark .post-show-shell .ps-comment-more-trigger:active,
  .dark .post-show-shell .ps-replies-toggle--inline:active,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn:active,
  [data-theme="dark"] .post-show-shell .ps-comment-reply-icon-btn:active,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger:active,
  [data-theme="dark"] .post-show-shell .ps-replies-toggle--inline:active {
    background: rgba(255,255,255,0.16) !important;
  }



  /* Comment image upload: main comment and replies */
  .post-show-shell .ps-comment-form-box,
  .post-show-shell .ps-comment-mini-box {
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-image-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
    padding: 8px 10px;
    border-radius: 12px;
    background: #f8fafc;
    color: #475569;
    font-size: 13px;
    line-height: 1.3;
  }

  .post-show-shell .ps-comment-image-preview[hidden] {
    display: none !important;
  }

  .post-show-shell .ps-comment-image-preview img {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    object-fit: cover;
    display: block;
    flex: 0 0 52px;
  }

  .post-show-shell .ps-comment-image-preview--mini img {
    width: 44px;
    height: 44px;
    flex-basis: 44px;
  }

  .post-show-shell .ps-comment-mini-image-btn,
  .post-show-shell .ps-comment-tool {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    min-height: 32px !important;
    border-radius: 999px !important;
    border: 0 !important;
    background: transparent !important;
    color: #64748b !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-comment-mini-image-btn:hover,
  .post-show-shell .ps-comment-mini-image-btn:focus-visible,
  .post-show-shell .ps-comment-tool:hover,
  .post-show-shell .ps-comment-tool:focus-visible {
    background: #f3f4f6 !important;
    color: #111827 !important;
    outline: none !important;
  }

  .post-show-shell .ps-comment-mini-image-btn svg,
  .post-show-shell .ps-comment-tool svg {
    width: 18px !important;
    height: 18px !important;
    display: block !important;
  }

  .post-show-shell .ps-comment-image {
    margin: 8px 0 0 !important;
    max-width: min(320px, 100%) !important;
  }

  .post-show-shell .ps-comment-image img {
    width: auto !important;
    max-width: 100% !important;
    max-height: 360px !important;
    border-radius: 12px !important;
    object-fit: contain !important;
    display: block !important;
    background: #f8fafc !important;
  }

  .post-show-shell .ps-comment-mini-actions {
    align-items: center !important;
  }

  .post-show-shell .ps-comment-mini-image-btn {
    margin-right: auto !important;
  }

  html.dark .post-show-shell .ps-comment-image-preview,
  body.dark .post-show-shell .ps-comment-image-preview,
  .dark .post-show-shell .ps-comment-image-preview,
  [data-theme="dark"] .post-show-shell .ps-comment-image-preview {
    background: rgba(255,255,255,0.08) !important;
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-comment-mini-image-btn:hover,
  html.dark .post-show-shell .ps-comment-tool:hover,
  body.dark .post-show-shell .ps-comment-mini-image-btn:hover,
  body.dark .post-show-shell .ps-comment-tool:hover,
  .dark .post-show-shell .ps-comment-mini-image-btn:hover,
  .dark .post-show-shell .ps-comment-tool:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-image-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-tool:hover {
    background: rgba(255,255,255,0.10) !important;
    color: #ffffff !important;
  }



  /* FINAL DESIGN: yorum alanı referans görseldeki modern cam/kart görünüme yaklaştırıldı */
  .post-show-shell .ps-comments-section {
    position: relative !important;
    overflow: visible !important;
    padding: 0 !important;
    border-radius: 28px !important;
    border: 1px solid rgba(226, 232, 240, .9) !important;
    background: rgba(255, 255, 255, .94) !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-header {
    padding: 22px 28px 0 !important;
  }

  .post-show-shell .ps-comments-top {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    margin-bottom: 18px !important;
  }

  .post-show-shell .ps-comments-title {
    margin: 0 !important;
    color: #111827 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 15px !important;
    line-height: 1.3 !important;
    font-weight: 500 !important;
    letter-spacing: -.01em !important;
  }

  .post-show-shell .ps-comments-sort {
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    padding: 3px !important;
    border-radius: 999px !important;
    background: #f8fafc !important;
    border: 1px solid #eef2f7 !important;
    flex: 0 0 auto !important;
  }

  .post-show-shell .ps-comments-sort-btn {
    height: 24px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    padding: 0 9px !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 10px !important;
    line-height: 1 !important;
    font-weight: 400 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    transition: none !important;
  }

  .post-show-shell .ps-comments-sort-btn:hover,
  .post-show-shell .ps-comments-sort-btn.is-active {
    background: #ffffff !important;
    color: #111827 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-form-box {
    position: relative !important;
    min-height: 70px !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    border: 1px solid #f1f5f9 !important;
    border-radius: 20px !important;
    background: #ffffff !important;
    padding: 12px 12px 12px 58px !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box::before {
    content: "";
    position: absolute;
    left: 18px;
    top: 50%;
    width: 28px;
    height: 28px;
    border-radius: 999px;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #075985, #22d3ee);
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-textarea {
    min-height: 28px !important;
    height: 28px !important;
    flex: 1 1 auto !important;
    border: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    line-height: 1.45 !important;
    font-weight: 400 !important;
    padding: 4px 0 !important;
    resize: none !important;
  }

  .post-show-shell .ps-comment-textarea::placeholder {
    color: #94a3b8 !important;
  }

  .post-show-shell .ps-comment-toolbar {
    position: static !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin: 0 !important;
    padding: 0 !important;
    flex: 0 0 auto !important;
  }

  .post-show-shell .ps-comment-tool,
  .post-show-shell .ps-comment-send,
  .post-show-shell .ps-comment-mini-image-btn {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    border: 0 !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    background: #f8fafc !important;
    color: #64748b !important;
    cursor: pointer !important;
    box-shadow: none !important;
    transition: none !important;
  }

  .post-show-shell .ps-comment-send {
    background: #4f46e5 !important;
    color: #ffffff !important;
  }

  .post-show-shell .ps-comment-tool:hover,
  .post-show-shell .ps-comment-mini-image-btn:hover {
    background: #eef2f7 !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-send:hover {
    background: #4338ca !important;
    color: #ffffff !important;
  }

  .post-show-shell .ps-comment-tool svg,
  .post-show-shell .ps-comment-send svg,
  .post-show-shell .ps-comment-mini-image-btn svg {
    width: 18px !important;
    height: 18px !important;
  }

  .post-show-shell .ps-comments-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 22px !important;
    padding: 24px 28px 30px !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-item {
    position: relative !important;
    display: flex !important;
    gap: 12px !important;
    align-items: flex-start !important;
    padding: 0 !important;
    border: 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-avatar {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    border-radius: 999px !important;
    border: 0 !important;
    background: linear-gradient(135deg, #f59e0b, #ef4444) !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    overflow: hidden !important;
  }

  .post-show-shell .ps-comment-body {
    min-width: 0 !important;
    flex: 1 1 auto !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-meta {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    margin: 0 0 4px !important;
    line-height: 1.2 !important;
  }

  .post-show-shell .ps-comment-author {
    color: #111827 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
  }

  .post-show-shell .ps-comment-role,
  .post-show-shell .ps-comment-time {
    color: #94a3b8 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 10.5px !important;
    font-weight: 400 !important;
    line-height: 1.2 !important;
  }

  .post-show-shell .ps-comment-text {
    margin: 0 !important;
    color: #334155 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    line-height: 1.56 !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-image img,
  .post-show-shell .ps-comment-text img {
    max-width: min(330px, 100%) !important;
    border-radius: 16px !important;
    margin-top: 10px !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-actions {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-top: 8px !important;
    flex-wrap: wrap !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
  }

  .post-show-shell .ps-comment-votes form,
  .post-show-shell .ps-comment-delete-form {
    display: inline-flex !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-more-trigger,
  .post-show-shell .ps-comment-action {
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: transparent !important;
    color: #475569 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    transition: none !important;
    text-decoration: none !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-action:hover {
    background: #f1f5f9 !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-vote-btn:active,
  .post-show-shell .ps-comment-reply-icon-btn:active,
  .post-show-shell .ps-comment-more-trigger:active,
  .post-show-shell .ps-comment-action:active {
    background: #e2e8f0 !important;
  }

  .post-show-shell .ps-modern-icon,
  .post-show-shell .ps-comment-vote-btn svg,
  .post-show-shell .ps-comment-reply-icon-btn svg,
  .post-show-shell .ps-comment-more-trigger svg,
  .post-show-shell .ps-comment-action svg {
    width: 15px !important;
    height: 15px !important;
    fill: none !important;
    stroke: currentColor !important;
    stroke-width: 1.85 !important;
    stroke-linecap: round !important;
    stroke-linejoin: round !important;
  }

  .post-show-shell .ps-comment-vote-count {
    min-width: auto !important;
    margin: 0 3px !important;
    color: #64748b !important;
    font-size: 10.5px !important;
    line-height: 1 !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-replies {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
    margin: 18px 0 0 0 !important;
    padding-left: 42px !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-replies::before {
    content: "" !important;
    position: absolute !important;
    left: 15px !important;
    top: 0 !important;
    bottom: 8px !important;
    width: 1px !important;
    border-left: 1px solid #e2e8f0 !important;
  }

  .post-show-shell .ps-replies > .ps-comment-item::before {
    content: "" !important;
    position: absolute !important;
    left: -27px !important;
    top: 17px !important;
    width: 20px !important;
    height: 18px !important;
    border-left: 1px solid #e2e8f0 !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-bottom-left-radius: 13px !important;
  }

  .post-show-shell .ps-replies-toggle {
    height: 26px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #334155 !important;
    padding: 0 8px !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-replies-toggle:hover {
    background: #f1f5f9 !important;
  }

  .post-show-shell .ps-replies-toggle svg {
    width: 14px !important;
    height: 14px !important;
  }

  .post-show-shell .ps-comment-more {
    position: relative !important;
    display: inline-flex !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-more-menu {
    position: absolute !important;
    right: 0 !important;
    top: calc(100% + 8px) !important;
    z-index: 1000 !important;
    min-width: 156px !important;
    padding: 8px !important;
    border-radius: 16px !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-more-menu[hidden] {
    display: none !important;
  }

  .post-show-shell .ps-comment-more-item {
    width: 100% !important;
    min-height: 34px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    border: 0 !important;
    border-radius: 11px !important;
    background: transparent !important;
    color: #334155 !important;
    padding: 0 10px !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    cursor: pointer !important;
    text-align: left !important;
  }

  .post-show-shell .ps-comment-more-item:hover {
    background: #f1f5f9 !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-more-item svg {
    width: 15px !important;
    height: 15px !important;
  }

  .post-show-shell .ps-comment-mini-box {
    margin-top: 12px !important;
    border: 1px solid #eef2f7 !important;
    border-radius: 18px !important;
    background: #ffffff !important;
    padding: 12px !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mini-textarea {
    min-height: 44px !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    font-weight: 400 !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comments-section {
      border-radius: 0 !important;
      border-left: 0 !important;
      border-right: 0 !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-comments-header {
      padding: 18px 14px 0 !important;
    }

    .post-show-shell .ps-comments-list {
      padding: 20px 14px 26px !important;
      gap: 20px !important;
    }

    .post-show-shell .ps-comment-form-box {
      padding-left: 52px !important;
      border-radius: 18px !important;
    }

    .post-show-shell .ps-comments-sort-btn {
      font-size: 9.5px !important;
      padding-left: 7px !important;
      padding-right: 7px !important;
    }

    .post-show-shell .ps-replies {
      padding-left: 34px !important;
    }
  }

  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section {
    background: rgba(17, 24, 39, .94) !important;
    border-color: rgba(255,255,255,.09) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-comment-form-box,
  body.dark .post-show-shell .ps-comment-form-box,
  .dark .post-show-shell .ps-comment-form-box,
  [data-theme="dark"] .post-show-shell .ps-comment-form-box,
  html.dark .post-show-shell .ps-comment-mini-box,
  body.dark .post-show-shell .ps-comment-mini-box,
  .dark .post-show-shell .ps-comment-mini-box,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-box,
  html.dark .post-show-shell .ps-comment-more-menu,
  body.dark .post-show-shell .ps-comment-more-menu,
  .dark .post-show-shell .ps-comment-more-menu,
  [data-theme="dark"] .post-show-shell .ps-comment-more-menu {
    background: #0f172a !important;
    border-color: rgba(255,255,255,.10) !important;
  }

  html.dark .post-show-shell .ps-comments-title,
  body.dark .post-show-shell .ps-comments-title,
  .dark .post-show-shell .ps-comments-title,
  [data-theme="dark"] .post-show-shell .ps-comments-title,
  html.dark .post-show-shell .ps-comment-author,
  body.dark .post-show-shell .ps-comment-author,
  .dark .post-show-shell .ps-comment-author,
  [data-theme="dark"] .post-show-shell .ps-comment-author {
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text {
    color: #e2e8f0 !important;
  }

  html.dark .post-show-shell .ps-comment-vote-btn:hover,
  html.dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  html.dark .post-show-shell .ps-comment-more-trigger:hover,
  html.dark .post-show-shell .ps-comment-action:hover,
  body.dark .post-show-shell .ps-comment-vote-btn:hover,
  body.dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  body.dark .post-show-shell .ps-comment-more-trigger:hover,
  body.dark .post-show-shell .ps-comment-action:hover,
  .dark .post-show-shell .ps-comment-vote-btn:hover,
  .dark .post-show-shell .ps-comment-reply-icon-btn:hover,
  .dark .post-show-shell .ps-comment-more-trigger:hover,
  .dark .post-show-shell .ps-comment-action:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-reply-icon-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-action:hover {
    background: rgba(255,255,255,.10) !important;
    color: #ffffff !important;
  }



  /* FINAL OVERRIDE: yorum alanı gölgesiz, Türkçe başlık, animasyonsuz, kullanıcı profil avatarı */
  .post-show-shell .ps-comments-section,
  .post-show-shell .ps-comment-form-box,
  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-more-menu,
  .post-show-shell .ps-comments-sort-btn:hover,
  .post-show-shell .ps-comments-sort-btn.is-active,
  .post-show-shell .ps-comment-send,
  .post-show-shell .ps-menu-panel,
  .post-show-shell .ps-nsfw-card,
  .post-show-shell .ps-comment-avatar,
  .post-show-shell .post-author-badge,
  .post-show-shell .ps-post-avatar-badge {
    box-shadow: none !important;
  }

  .post-show-shell *,
  .post-show-shell *::before,
  .post-show-shell *::after {
    animation: none !important;
    transition: none !important;
  }

  .post-show-shell .ps-comments-section {
    background: #ffffff !important;
    border: 1px solid #eef2f7 !important;
  }

  .post-show-shell .ps-comments-sort-btn:hover,
  .post-show-shell .ps-comments-sort-btn.is-active {
    background: #ffffff !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-form-box {
    padding-left: 12px !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box::before {
    content: none !important;
    display: none !important;
  }

  .post-show-shell .ps-comment-composer-avatar {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: #f1f5f9 !important;
    color: #475569 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
    flex: 0 0 32px !important;
  }

  .post-show-shell .ps-comment-composer-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
  }

  .post-show-shell .ps-comment-composer-avatar span {
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-textarea {
    padding-left: 0 !important;
  }

  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section {
    background: #111827 !important;
    border-color: rgba(255,255,255,.10) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-comment-composer-avatar,
  body.dark .post-show-shell .ps-comment-composer-avatar,
  .dark .post-show-shell .ps-comment-composer-avatar,
  [data-theme="dark"] .post-show-shell .ps-comment-composer-avatar {
    background: rgba(255,255,255,.10) !important;
    color: #e5e7eb !important;
  }



  /* FINAL OVERRIDE: etiket üst çizgisi + mavi yorum renkleri */
  .post-show-shell .ps-tags-row {
    width: 100% !important;
    margin: 2px 0 16px !important;
    padding-top: 10px !important;
    border-top: 1px solid #e5e7eb !important;
    gap: 10px 14px !important;
  }

  .post-show-shell .ps-tag,
  .post-show-shell .ps-tags-row .ps-tag {
    color: #2563eb !important;
    font-size: 13.5px !important;
    line-height: 1.35 !important;
    font-weight: 400 !important;
    text-decoration: none !important;
  }

  .post-show-shell .ps-tag:hover,
  .post-show-shell .ps-tag:focus-visible {
    color: #1d4ed8 !important;
    text-decoration: none !important;
    outline: none !important;
  }

  .post-show-shell .ps-comments-sort {
    background: #eff6ff !important;
    border-color: #dbeafe !important;
  }

  .post-show-shell .ps-comments-sort-btn:hover,
  .post-show-shell .ps-comments-sort-btn:focus-visible,
  .post-show-shell .ps-comments-sort-btn.is-active {
    background: #ffffff !important;
    color: #2563eb !important;
    box-shadow: none !important;
    outline: none !important;
  }

  .post-show-shell .ps-comment-send,
  .post-show-shell .ps-comment-tool:hover,
  .post-show-shell .ps-comment-tool:focus-visible,
  .post-show-shell .ps-comment-send:hover,
  .post-show-shell .ps-comment-send:focus-visible {
    color: #2563eb !important;
  }

  .post-show-shell .ps-comment-send {
    background: #eff6ff !important;
    border-radius: 999px !important;
  }

  .post-show-shell .ps-comment-send:hover,
  .post-show-shell .ps-comment-send:focus-visible {
    background: #dbeafe !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-vote-btn:focus-visible,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:focus-visible,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-more-trigger:focus-visible,
  .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  .post-show-shell .ps-replies-toggle--inline:hover,
  .post-show-shell .ps-replies-toggle--inline:focus-visible {
    background: #eff6ff !important;
    color: #2563eb !important;
    outline: none !important;
  }

  .post-show-shell .ps-comment-vote-btn:active,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:active,
  .post-show-shell .ps-comment-more-trigger:active,
  .post-show-shell .ps-replies-toggle--inline:active {
    background: #dbeafe !important;
    color: #1d4ed8 !important;
  }

  .post-show-shell .ps-mention-token--link,
  .post-show-shell .ps-comment-text a,
  .post-show-shell .ps-comment-reply-text,
  .post-show-shell .ps-replies-toggle--inline {
    color: #2563eb !important;
  }

  .post-show-shell .ps-comment-more-item:hover,
  .post-show-shell .ps-comment-more-item:focus-visible {
    background: #eff6ff !important;
    color: #2563eb !important;
  }

  html.dark .post-show-shell .ps-tags-row,
  body.dark .post-show-shell .ps-tags-row,
  .dark .post-show-shell .ps-tags-row,
  [data-theme="dark"] .post-show-shell .ps-tags-row {
    border-top-color: rgba(255, 255, 255, .12) !important;
  }

  html.dark .post-show-shell .ps-tag,
  body.dark .post-show-shell .ps-tag,
  .dark .post-show-shell .ps-tag,
  [data-theme="dark"] .post-show-shell .ps-tag,
  html.dark .post-show-shell .ps-mention-token--link,
  body.dark .post-show-shell .ps-mention-token--link,
  .dark .post-show-shell .ps-mention-token--link,
  [data-theme="dark"] .post-show-shell .ps-mention-token--link {
    color: #60a5fa !important;
  }

  html.dark .post-show-shell .ps-comments-sort,
  body.dark .post-show-shell .ps-comments-sort,
  .dark .post-show-shell .ps-comments-sort,
  [data-theme="dark"] .post-show-shell .ps-comments-sort {
    background: rgba(37, 99, 235, .14) !important;
    border-color: rgba(96, 165, 250, .20) !important;
  }

  html.dark .post-show-shell .ps-comments-sort-btn:hover,
  html.dark .post-show-shell .ps-comments-sort-btn:focus-visible,
  html.dark .post-show-shell .ps-comments-sort-btn.is-active,
  body.dark .post-show-shell .ps-comments-sort-btn:hover,
  body.dark .post-show-shell .ps-comments-sort-btn:focus-visible,
  body.dark .post-show-shell .ps-comments-sort-btn.is-active,
  .dark .post-show-shell .ps-comments-sort-btn:hover,
  .dark .post-show-shell .ps-comments-sort-btn:focus-visible,
  .dark .post-show-shell .ps-comments-sort-btn.is-active,
  [data-theme="dark"] .post-show-shell .ps-comments-sort-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comments-sort-btn:focus-visible,
  [data-theme="dark"] .post-show-shell .ps-comments-sort-btn.is-active {
    background: rgba(96, 165, 250, .16) !important;
    color: #93c5fd !important;
  }

  html.dark .post-show-shell .ps-comment-vote-btn:hover,
  html.dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  html.dark .post-show-shell .ps-comment-more-trigger:hover,
  html.dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  html.dark .post-show-shell .ps-replies-toggle--inline:hover,
  body.dark .post-show-shell .ps-comment-vote-btn:hover,
  body.dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  body.dark .post-show-shell .ps-comment-more-trigger:hover,
  body.dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  body.dark .post-show-shell .ps-replies-toggle--inline:hover,
  .dark .post-show-shell .ps-comment-vote-btn:hover,
  .dark .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  .dark .post-show-shell .ps-comment-more-trigger:hover,
  .dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  .dark .post-show-shell .ps-replies-toggle--inline:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  [data-theme="dark"] .post-show-shell .ps-replies-toggle--inline:hover {
    background: rgba(37, 99, 235, .18) !important;
    color: #93c5fd !important;
  }



  /* FINAL OVERRIDE: yorum fontu ve ikonları post meta boyutuyla eşitle */
  .post-show-shell {
    --ps-meta-like-font-size: 12.5px;
    --ps-meta-like-line-height: 1.35;
    --ps-meta-like-icon-size: 16px;
    --ps-meta-like-action-size: 30px;
  }

  .post-show-shell .ps-comment-author,
  .post-show-shell .ps-comment-role,
  .post-show-shell .ps-comment-time,
  .post-show-shell .ps-comment-text,
  .post-show-shell .ps-comment-text-line,
  .post-show-shell .ps-comment-action,
  .post-show-shell .ps-comment-reply-text,
  .post-show-shell .ps-comment-vote-count,
  .post-show-shell .ps-replies-toggle--inline,
  .post-show-shell .ps-comment-more-item,
  .post-show-shell .ps-comment-mini-textarea,
  .post-show-shell .ps-comment-mini-btn {
    font-family: Poppins, Arial, sans-serif !important;
    font-size: var(--ps-meta-like-font-size) !important;
    line-height: var(--ps-meta-like-line-height) !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-author {
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-role,
  .post-show-shell .ps-comment-time,
  .post-show-shell .ps-comment-vote-count {
    color: #64748b !important;
  }

  .post-show-shell .ps-comment-text,
  .post-show-shell .ps-comment-text-line {
    color: #334155 !important;
  }

  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-comment-actions--reply {
    gap: 7px !important;
    margin-top: 7px !important;
    align-items: center !important;
  }

  .post-show-shell .ps-comment-votes {
    gap: 3px !important;
    align-items: center !important;
  }

  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-more-trigger,
  .post-show-shell .ps-comment-action {
    width: var(--ps-meta-like-action-size) !important;
    height: var(--ps-meta-like-action-size) !important;
    min-width: var(--ps-meta-like-action-size) !important;
    min-height: var(--ps-meta-like-action-size) !important;
    padding: 0 !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: transparent !important;
    color: #475569 !important;
    box-shadow: none !important;
    transform: none !important;
    transition: none !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-vote-btn:focus-visible,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:focus-visible,
  .post-show-shell .ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-reply-icon-btn:focus-visible,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-more-trigger:focus-visible,
  .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  .post-show-shell .ps-comment-action:hover,
  .post-show-shell .ps-comment-action:focus-visible {
    background: #eff6ff !important;
    color: #2563eb !important;
    border-radius: 999px !important;
    outline: none !important;
  }

  .post-show-shell .ps-comment-vote-btn:active,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:active,
  .post-show-shell .ps-comment-reply-icon-btn:active,
  .post-show-shell .ps-comment-more-trigger:active,
  .post-show-shell .ps-comment-action:active {
    background: #dbeafe !important;
    color: #1d4ed8 !important;
    transform: none !important;
  }

  .post-show-shell .ps-modern-icon,
  .post-show-shell .ps-comment-vote-btn svg,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn svg,
  .post-show-shell .ps-comment-reply-icon-btn svg,
  .post-show-shell .ps-comment-more-trigger svg,
  .post-show-shell .ps-comment-action svg {
    width: var(--ps-meta-like-icon-size) !important;
    height: var(--ps-meta-like-icon-size) !important;
    min-width: var(--ps-meta-like-icon-size) !important;
    min-height: var(--ps-meta-like-icon-size) !important;
    display: block !important;
    fill: none !important;
    stroke: currentColor !important;
    stroke-width: 1.7 !important;
    stroke-linecap: round !important;
    stroke-linejoin: round !important;
  }

  .post-show-shell .ps-comment-vote-count {
    min-width: 12px !important;
    margin: 0 2px !important;
    text-align: center !important;
  }

  .post-show-shell .ps-replies-toggle--inline {
    min-height: var(--ps-meta-like-action-size) !important;
    padding: 0 10px !important;
    border-radius: 999px !important;
    font-size: var(--ps-meta-like-font-size) !important;
    line-height: 1 !important;
  }

  html.dark .post-show-shell .ps-comment-author,
  body.dark .post-show-shell .ps-comment-author,
  .dark .post-show-shell .ps-comment-author,
  [data-theme="dark"] .post-show-shell .ps-comment-author {
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-comment-text,
  html.dark .post-show-shell .ps-comment-text-line,
  body.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text-line,
  .dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text-line,
  [data-theme="dark"] .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text-line {
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-comment-role,
  html.dark .post-show-shell .ps-comment-time,
  html.dark .post-show-shell .ps-comment-vote-count,
  body.dark .post-show-shell .ps-comment-role,
  body.dark .post-show-shell .ps-comment-time,
  body.dark .post-show-shell .ps-comment-vote-count,
  .dark .post-show-shell .ps-comment-role,
  .dark .post-show-shell .ps-comment-time,
  .dark .post-show-shell .ps-comment-vote-count,
  [data-theme="dark"] .post-show-shell .ps-comment-role,
  [data-theme="dark"] .post-show-shell .ps-comment-time,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-count {
    color: #94a3b8 !important;
  }



  /* FINAL OVERRIDE: yorumdaki Yazar etiketini daha belirgin yap */
  .post-show-shell .ps-comment-meta .ps-comment-role {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 18px !important;
    padding: 2px 7px !important;
    border-radius: 999px !important;
    background: #eaf2ff !important;
    color: #2563eb !important;
    border: 1px solid rgba(37, 99, 235, 0.18) !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 11px !important;
    line-height: 1 !important;
    font-weight: 500 !important;
    letter-spacing: 0 !important;
    white-space: nowrap !important;
  }

  html.dark .post-show-shell .ps-comment-meta .ps-comment-role,
  body.dark .post-show-shell .ps-comment-meta .ps-comment-role,
  .dark .post-show-shell .ps-comment-meta .ps-comment-role,
  [data-theme="dark"] .post-show-shell .ps-comment-meta .ps-comment-role {
    background: rgba(37, 99, 235, 0.18) !important;
    color: #93c5fd !important;
    border-color: rgba(147, 197, 253, 0.22) !important;
  }



  /* FINAL OVERRIDE: yorum yazma kutusu belirgin, mavi, gölgesiz ve animasyonsuz */
  .post-show-shell #show-comment-form {
    margin-top: 8px !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box {
    min-height: 76px !important;
    padding: 13px 13px !important;
    gap: 12px !important;
    border: 1.5px solid #bfdbfe !important;
    border-radius: 22px !important;
    background: #f8fbff !important;
    box-shadow: none !important;
    outline: none !important;
    transition: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box:focus-within {
    border-color: #3b82f6 !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-composer-avatar {
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    flex: 0 0 38px !important;
    border: 1px solid #dbeafe !important;
    background: #eff6ff !important;
    color: #2563eb !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea {
    min-height: 38px !important;
    height: auto !important;
    max-height: 120px !important;
    padding: 8px 0 !important;
    color: #0f172a !important;
    font-size: 13px !important;
    line-height: 1.45 !important;
    font-weight: 400 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea::placeholder {
    color: #64748b !important;
  }

  .post-show-shell #show-comment-form .ps-comment-toolbar {
    gap: 7px !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool,
  .post-show-shell #show-comment-form .ps-comment-send {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    min-height: 36px !important;
    border-radius: 999px !important;
    background: #eff6ff !important;
    color: #2563eb !important;
    border: 1px solid #dbeafe !important;
    box-shadow: none !important;
    transition: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool:hover,
  .post-show-shell #show-comment-form .ps-comment-tool:focus-visible,
  .post-show-shell #show-comment-form .ps-comment-send:hover,
  .post-show-shell #show-comment-form .ps-comment-send:focus-visible {
    background: #dbeafe !important;
    color: #1d4ed8 !important;
    outline: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool:active,
  .post-show-shell #show-comment-form .ps-comment-send:active {
    background: #bfdbfe !important;
    color: #1e40af !important;
    transform: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool svg,
  .post-show-shell #show-comment-form .ps-comment-send svg {
    width: 18px !important;
    height: 18px !important;
  }

  .post-show-shell #show-comment-image-preview {
    margin-top: 9px !important;
    border: 1px solid #dbeafe !important;
    background: #f8fbff !important;
    color: #2563eb !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  body.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  .dark .post-show-shell #show-comment-form .ps-comment-form-box,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-form-box {
    background: rgba(37, 99, 235, .08) !important;
    border-color: rgba(96, 165, 250, .42) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-form-box:focus-within,
  body.dark .post-show-shell #show-comment-form .ps-comment-form-box:focus-within,
  .dark .post-show-shell #show-comment-form .ps-comment-form-box:focus-within,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-form-box:focus-within {
    background: rgba(15, 23, 42, .92) !important;
    border-color: rgba(96, 165, 250, .78) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-textarea,
  body.dark .post-show-shell #show-comment-form .ps-comment-textarea,
  .dark .post-show-shell #show-comment-form .ps-comment-textarea,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-textarea {
    color: #ffffff !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-textarea::placeholder,
  body.dark .post-show-shell #show-comment-form .ps-comment-textarea::placeholder,
  .dark .post-show-shell #show-comment-form .ps-comment-textarea::placeholder,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-textarea::placeholder {
    color: #cbd5e1 !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-composer-avatar,
  body.dark .post-show-shell #show-comment-form .ps-comment-composer-avatar,
  .dark .post-show-shell #show-comment-form .ps-comment-composer-avatar,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-composer-avatar,
  html.dark .post-show-shell #show-comment-form .ps-comment-tool,
  html.dark .post-show-shell #show-comment-form .ps-comment-send,
  body.dark .post-show-shell #show-comment-form .ps-comment-tool,
  body.dark .post-show-shell #show-comment-form .ps-comment-send,
  .dark .post-show-shell #show-comment-form .ps-comment-tool,
  .dark .post-show-shell #show-comment-form .ps-comment-send,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-tool,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-send {
    background: rgba(37, 99, 235, .16) !important;
    border-color: rgba(96, 165, 250, .30) !important;
    color: #93c5fd !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-tool:hover,
  html.dark .post-show-shell #show-comment-form .ps-comment-send:hover,
  body.dark .post-show-shell #show-comment-form .ps-comment-tool:hover,
  body.dark .post-show-shell #show-comment-form .ps-comment-send:hover,
  .dark .post-show-shell #show-comment-form .ps-comment-tool:hover,
  .dark .post-show-shell #show-comment-form .ps-comment-send:hover,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-tool:hover,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-send:hover {
    background: rgba(59, 130, 246, .26) !important;
    color: #ffffff !important;
  }



  /* Yasaklı kelime uyarısı */
  .ps-comment-badword-warning {
    display: none;
    margin-top: 8px;
    color: #dc2626;
    font-size: 12px;
    line-height: 1.35;
    font-family: Poppins, Arial, sans-serif;
    font-weight: 400;
  }

  .ps-comment-badword-warning.is-visible {
    display: block;
  }

  .ps-comment-textarea.has-blocked-word,
  .ps-comment-mini-textarea.has-blocked-word {
    color: #dc2626 !important;
    text-decoration: line-through;
    text-decoration-thickness: 1px;
    text-decoration-color: #dc2626;
  }



  /* FINAL OVERRIDE: modern yorum kutusu + bağlantılı yanıt ağacı */
  .post-show-shell .ps-comments-section {
    background: #17181f !important;
    border: 1px solid rgba(255, 255, 255, .06) !important;
    border-radius: 18px !important;
    overflow: visible !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-header {
    padding: 14px !important;
  }

  .post-show-shell .ps-comments-top {
    margin: 0 0 12px !important;
  }

  .post-show-shell .ps-comments-title {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 28px !important;
    padding: 0 12px !important;
    border-radius: 999px !important;
    background: rgba(255, 255, 255, .08) !important;
    color: #f4f7fb !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 500 !important;
    letter-spacing: .01em !important;
  }

  .post-show-shell .ps-comments-title::before {
    content: "" !important;
    width: 10px !important;
    height: 10px !important;
    margin-right: 7px !important;
    border-radius: 999px !important;
    background: #6b7280 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-sort {
    background: rgba(255, 255, 255, .06) !important;
    border: 1px solid rgba(255, 255, 255, .08) !important;
    border-radius: 999px !important;
    padding: 3px !important;
  }

  .post-show-shell .ps-comments-sort-btn {
    min-height: 24px !important;
    padding: 0 10px !important;
    border-radius: 999px !important;
    color: #aeb5c3 !important;
    font-size: 11px !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-comments-sort-btn:hover,
  .post-show-shell .ps-comments-sort-btn.is-active {
    background: rgba(255, 255, 255, .10) !important;
    color: #ffffff !important;
  }

  .post-show-shell .ps-comment-form-box {
    position: relative !important;
    display: grid !important;
    grid-template-columns: 40px minmax(0, 1fr) auto !important;
    grid-template-areas:
      "avatar input toolbar"
      "suggestions suggestions suggestions" !important;
    gap: 10px !important;
    align-items: center !important;
    width: 100% !important;
    padding: 12px !important;
    border: 1px solid rgba(255, 255, 255, .07) !important;
    border-radius: 18px !important;
    background: #202126 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-composer-avatar {
    grid-area: avatar !important;
    width: 40px !important;
    height: 40px !important;
    border-radius: 999px !important;
    background: linear-gradient(135deg, #60a5fa, #7c3aed) !important;
    color: #fff !important;
    overflow: hidden !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-composer-avatar img,
  .post-show-shell .ps-comment-mini-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
  }

  .post-show-shell .ps-comment-textarea {
    grid-area: input !important;
    width: 100% !important;
    min-height: 40px !important;
    max-height: 96px !important;
    padding: 10px 14px !important;
    border: 1px solid rgba(255, 255, 255, .06) !important;
    border-radius: 999px !important;
    background: #2b2d34 !important;
    color: #f8fafc !important;
    font-size: 13px !important;
    line-height: 1.35 !important;
    resize: none !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-textarea::placeholder {
    color: #8e96a6 !important;
  }

  .post-show-shell .ps-comment-toolbar {
    grid-area: toolbar !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .post-show-shell .ps-comment-tool {
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    min-height: 38px !important;
    border-radius: 999px !important;
    background: rgba(96, 165, 250, .13) !important;
    color: #93c5fd !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
  }

  .post-show-shell .ps-comment-tool:hover {
    background: rgba(96, 165, 250, .22) !important;
    color: #bfdbfe !important;
  }

  .post-show-shell .ps-comment-send {
    width: auto !important;
    min-width: 76px !important;
    height: 38px !important;
    min-height: 38px !important;
    padding: 0 14px !important;
    gap: 7px !important;
    border-radius: 999px !important;
    background: #2f333c !important;
    color: #f9fafb !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
  }

  .post-show-shell .ps-comment-send svg {
    width: 16px !important;
    height: 16px !important;
  }

  .post-show-shell .ps-comment-send:hover,
  .post-show-shell .ps-comment-send:focus-visible {
    background: #2563eb !important;
    color: #ffffff !important;
  }

  .post-show-shell .ps-comment-suggestions {
    grid-area: suggestions !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 8px !important;
    margin-top: 4px !important;
  }

  .post-show-shell .ps-comment-suggestions-title {
    flex: 0 0 100% !important;
    color: #858d9d !important;
    font-size: 10px !important;
    font-weight: 500 !important;
    letter-spacing: .08em !important;
    text-transform: uppercase !important;
  }

  .post-show-shell .ps-comment-suggestions button {
    border: 1px solid rgba(255,255,255,.08) !important;
    border-radius: 999px !important;
    background: #24262d !important;
    color: #edf2ff !important;
    padding: 7px 11px !important;
    font-size: 12px !important;
    line-height: 1.2 !important;
    font-weight: 400 !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-comment-suggestions button:hover {
    background: #2f333c !important;
  }

  .post-show-shell .ps-comments-list {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 18px !important;
    padding: 18px 18px 26px !important;
    margin: 0 !important;
    background: #17181f !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-item {
    position: relative !important;
    display: grid !important;
    grid-template-columns: 44px minmax(0, 1fr) !important;
    gap: 12px !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    border: 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-avatar {
    position: relative !important;
    z-index: 3 !important;
    width: 44px !important;
    height: 44px !important;
    border: 3px solid #17181f !important;
    border-radius: 999px !important;
    background: linear-gradient(135deg, #60a5fa, #ef4444) !important;
    color: #ffffff !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-body {
    min-width: 0 !important;
    background: transparent !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-card,
  .post-show-shell .ps-comment-body > .ps-comment-meta,
  .post-show-shell .ps-comment-body > .ps-comment-text {
    max-width: 100% !important;
  }

  .post-show-shell .ps-comment-meta {
    display: flex !important;
    align-items: center !important;
    gap: 7px !important;
    margin-bottom: 5px !important;
  }

  .post-show-shell .ps-comment-author {
    color: #f8fafc !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    line-height: 1.25 !important;
  }

  .post-show-shell .ps-comment-role {
    display: inline-flex !important;
    align-items: center !important;
    height: 20px !important;
    padding: 0 7px !important;
    border-radius: 999px !important;
    background: rgba(37, 99, 235, .18) !important;
    color: #93c5fd !important;
    font-size: 11px !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-comment-time {
    color: #858d9d !important;
    font-size: 12px !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-text {
    width: fit-content !important;
    max-width: min(100%, 620px) !important;
    margin-top: 0 !important;
    padding: 12px 14px !important;
    border: 1px solid rgba(255, 255, 255, .06) !important;
    border-radius: 16px !important;
    background: #202126 !important;
    color: #e5e7eb !important;
    font-size: 14px !important;
    line-height: 1.55 !important;
    white-space: normal !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-text-line {
    color: #e5e7eb !important;
    font-size: 14px !important;
    line-height: 1.55 !important;
  }

  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-comment-actions--reply {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-top: 8px !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
  }

  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-more-trigger,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn {
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    min-height: 30px !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #9aa3b2 !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger {
    background: rgba(255,255,255,.08) !important;
    color: #ffffff !important;
  }

  .post-show-shell .ps-comment-vote-count {
    color: #9aa3b2 !important;
    font-size: 12px !important;
  }

  .post-show-shell .ps-comment-mini-box {
    width: 100% !important;
    border: 1px solid rgba(255,255,255,.07) !important;
    border-radius: 18px !important;
    background: #202126 !important;
    padding: 10px !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mini-box--reply {
    display: grid !important;
    grid-template-columns: 32px minmax(0, 1fr) auto !important;
    gap: 10px !important;
    align-items: center !important;
    margin-top: 10px !important;
  }

  .post-show-shell .ps-comment-mini-avatar {
    width: 32px !important;
    height: 32px !important;
    border-radius: 999px !important;
    background: linear-gradient(135deg, #60a5fa, #7c3aed) !important;
    color: #ffffff !important;
    overflow: hidden !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 12px !important;
    font-weight: 600 !important;
  }

  .post-show-shell .ps-comment-mini-textarea {
    min-height: 38px !important;
    max-height: 92px !important;
    padding: 10px 13px !important;
    border: 1px solid rgba(255,255,255,.06) !important;
    border-radius: 999px !important;
    background: #2b2d34 !important;
    color: #f8fafc !important;
    font-size: 13px !important;
    line-height: 1.35 !important;
    resize: none !important;
  }

  .post-show-shell .ps-comment-mini-textarea::placeholder {
    color: #8e96a6 !important;
  }

  .post-show-shell .ps-comment-mini-actions {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 7px !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-mini-btn,
  .post-show-shell .ps-comment-mini-image-btn {
    height: 34px !important;
    min-height: 34px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: rgba(255,255,255,.08) !important;
    color: #f8fafc !important;
    padding: 0 12px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-comment-mini-btn--primary {
    background: #2563eb !important;
    color: #ffffff !important;
  }

  .post-show-shell .ps-comment-mini-btn--ghost {
    color: #b8c0ce !important;
  }

  .post-show-shell .ps-replies {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 15px !important;
    margin: 16px 0 0 6px !important;
    padding: 0 0 0 34px !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-replies::before {
    content: "" !important;
    position: absolute !important;
    left: 15px !important;
    top: -16px !important;
    bottom: 22px !important;
    width: 2px !important;
    border-radius: 999px !important;
    background: rgba(135, 143, 160, .38) !important;
  }

  .post-show-shell .ps-replies > .ps-comment-item {
    grid-template-columns: 40px minmax(0, 1fr) !important;
    gap: 10px !important;
  }

  .post-show-shell .ps-replies > .ps-comment-item::before {
    content: "" !important;
    position: absolute !important;
    left: -19px !important;
    top: 19px !important;
    width: 20px !important;
    height: 20px !important;
    border-left: 2px solid rgba(135, 143, 160, .38) !important;
    border-bottom: 2px solid rgba(135, 143, 160, .38) !important;
    border-bottom-left-radius: 18px !important;
    background: transparent !important;
  }

  .post-show-shell .ps-replies .ps-comment-avatar {
    width: 40px !important;
    height: 40px !important;
    border-color: #17181f !important;
  }

  .post-show-shell .ps-replies .ps-comment-text {
    background: #1d1f26 !important;
  }

  .post-show-shell .ps-replies-toggle--inline {
    width: auto !important;
    height: 30px !important;
    min-width: 0 !important;
    padding: 0 10px !important;
    border-radius: 999px !important;
    background: rgba(255,255,255,.06) !important;
    color: #93c5fd !important;
    font-size: 12px !important;
  }

  .post-show-shell .ps-replies-toggle--inline:hover {
    background: rgba(96, 165, 250, .16) !important;
    color: #bfdbfe !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comments-section {
      border-radius: 16px !important;
    }

    .post-show-shell .ps-comments-header {
      padding: 10px !important;
    }

    .post-show-shell .ps-comment-form-box {
      grid-template-columns: 34px minmax(0, 1fr) auto !important;
      gap: 8px !important;
      padding: 10px !important;
      border-radius: 16px !important;
    }

    .post-show-shell .ps-comment-composer-avatar,
    .post-show-shell .ps-comment-avatar {
      width: 36px !important;
      height: 36px !important;
    }

    .post-show-shell .ps-comment-textarea {
      min-height: 38px !important;
      padding: 9px 12px !important;
    }

    .post-show-shell .ps-comment-tool {
      width: 36px !important;
      height: 36px !important;
      min-width: 36px !important;
    }

    .post-show-shell .ps-comment-send {
      min-width: 44px !important;
      width: 44px !important;
      padding: 0 !important;
    }

    .post-show-shell .ps-comment-send span {
      display: none !important;
    }

    .post-show-shell .ps-comment-suggestions button {
      font-size: 11.5px !important;
      padding: 7px 9px !important;
    }

    .post-show-shell .ps-comments-list {
      gap: 16px !important;
      padding: 14px 12px 22px !important;
    }

    .post-show-shell .ps-comment-item {
      grid-template-columns: 36px minmax(0, 1fr) !important;
      gap: 9px !important;
    }

    .post-show-shell .ps-comment-text {
      max-width: 100% !important;
      padding: 10px 12px !important;
      border-radius: 15px !important;
      font-size: 13px !important;
    }

    .post-show-shell .ps-comment-mini-box--reply {
      grid-template-columns: 30px minmax(0, 1fr) !important;
      grid-template-areas:
        "avatar input"
        "actions actions" !important;
    }

    .post-show-shell .ps-comment-mini-avatar { grid-area: avatar !important; }
    .post-show-shell .ps-comment-mini-textarea { grid-area: input !important; }
    .post-show-shell .ps-comment-mini-actions { grid-area: actions !important; justify-content: flex-end !important; }

    .post-show-shell .ps-replies {
      margin-left: 0 !important;
      padding-left: 24px !important;
    }

    .post-show-shell .ps-replies::before {
      left: 10px !important;
    }

    .post-show-shell .ps-replies > .ps-comment-item::before {
      left: -14px !important;
      width: 15px !important;
    }
  }



  /* COMMENT FIX: temiz yorum görünümü, cevap çizgisi ve light/dark uyumu */
  .post-show-shell .ps-comments-section { overflow: visible !important; }
  .post-show-shell .ps-comments-list {
    background: #ffffff !important;
    overflow: visible !important;
  }
  .post-show-shell .ps-comment-item { overflow: visible !important; }
  .post-show-shell .ps-comment-meta { min-width: 0 !important; }
  .post-show-shell .ps-comment-author { color: #111827 !important; font-weight: 600 !important; }
  .post-show-shell .ps-comment-time { color: #6b7280 !important; }
  .post-show-shell .ps-comment-text {
    width: fit-content !important;
    max-width: min(100%, 640px) !important;
    background: #f6f7f9 !important;
    border: 1px solid #eef0f3 !important;
    color: #1f2937 !important;
    box-shadow: none !important;
    white-space: normal !important;
    word-break: break-word !important;
  }
  .post-show-shell .ps-comment-text-line { color: inherit !important; }
  .post-show-shell .ps-comment-actions form,
  .post-show-shell .ps-comment-delete-form { margin: 0 !important; display: inline-flex !important; }
  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-form-box { overflow: visible !important; }
  .post-show-shell .ps-replies {
    position: relative !important;
    margin-top: 14px !important;
    margin-left: 0 !important;
    padding-left: 32px !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 14px !important;
    overflow: visible !important;
  }
  .post-show-shell .ps-replies.is-collapsed { display: none !important; }
  .post-show-shell .ps-replies::before {
    content: "" !important;
    position: absolute !important;
    left: 15px !important;
    top: -6px !important;
    bottom: 18px !important;
    width: 2px !important;
    border-radius: 999px !important;
    background: #dbe3ee !important;
  }
  .post-show-shell .ps-replies > .ps-comment-item::before {
    content: "" !important;
    position: absolute !important;
    left: -17px !important;
    top: 17px !important;
    width: 17px !important;
    height: 2px !important;
    border-radius: 999px !important;
    background: #dbe3ee !important;
  }
  .post-show-shell .ps-replies .ps-comment-avatar { width: 34px !important; height: 34px !important; border-width: 2px !important; }
  .post-show-shell .ps-replies .ps-comment-item { grid-template-columns: 34px minmax(0,1fr) !important; gap: 10px !important; }

  html.dark .post-show-shell .ps-comments-list,
  body.dark .post-show-shell .ps-comments-list,
  .dark .post-show-shell .ps-comments-list,
  [data-theme="dark"] .post-show-shell .ps-comments-list { background: #17181f !important; }
  html.dark .post-show-shell .ps-comment-author,
  body.dark .post-show-shell .ps-comment-author,
  .dark .post-show-shell .ps-comment-author,
  [data-theme="dark"] .post-show-shell .ps-comment-author { color: #f8fafc !important; }
  html.dark .post-show-shell .ps-comment-time,
  body.dark .post-show-shell .ps-comment-time,
  .dark .post-show-shell .ps-comment-time,
  [data-theme="dark"] .post-show-shell .ps-comment-time { color: #858d9d !important; }
  html.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text { background: #202126 !important; border-color: rgba(255,255,255,.06) !important; color: #e5e7eb !important; }
  html.dark .post-show-shell .ps-replies::before,
  html.dark .post-show-shell .ps-replies > .ps-comment-item::before,
  body.dark .post-show-shell .ps-replies::before,
  body.dark .post-show-shell .ps-replies > .ps-comment-item::before,
  .dark .post-show-shell .ps-replies::before,
  .dark .post-show-shell .ps-replies > .ps-comment-item::before,
  [data-theme="dark"] .post-show-shell .ps-replies::before,
  [data-theme="dark"] .post-show-shell .ps-replies > .ps-comment-item::before { background: rgba(148,163,184,.35) !important; }


  /* Ografi modern yorum tasarımı - resimdeki görünüm */
  .post-show-shell .ps-comments-section {
    overflow: visible !important;
    border-radius: 14px !important;
    background: #ffffff !important;
    border: 1px solid rgba(15, 23, 42, .08) !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-header {
    padding: 22px 22px 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comments-top {
    margin-bottom: 22px !important;
    align-items: center !important;
  }

  .post-show-shell .ps-comments-title {
    display: inline-flex !important;
    align-items: center !important;
    gap: 9px !important;
    margin: 0 !important;
    color: #171717 !important;
    font-size: 24px !important;
    line-height: 1.1 !important;
    font-weight: 800 !important;
    letter-spacing: -0.025em !important;
  }

  .post-show-shell .ps-comments-count-badge {
    min-width: 24px !important;
    height: 24px !important;
    padding: 0 7px !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(180deg, #b8bcc4, #898e98) !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-sort {
    display: inline-flex !important;
    align-items: center !important;
    overflow: hidden !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
    background: #f8fafc !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-sort-btn {
    min-height: 34px !important;
    padding: 0 13px !important;
    border: 0 !important;
    border-right: 1px solid #e5e7eb !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #4b5563 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-sort-btn:last-child { border-right: 0 !important; }
  .post-show-shell .ps-comments-sort-btn.is-active {
    background: #ffffff !important;
    color: #111827 !important;
  }

  .post-show-shell #show-comment-form { position: relative !important; z-index: 20 !important; }

  .post-show-shell #show-comment-form .ps-comment-form-box {
    position: relative !important;
    display: grid !important;
    grid-template-columns: 1fr auto !important;
    grid-template-areas: "input tools" !important;
    gap: 12px !important;
    align-items: center !important;
    min-height: 112px !important;
    padding: 20px 16px 18px !important;
    overflow: visible !important;
    border: 2px solid #2388ff !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box::before {
    content: "" !important;
    position: absolute !important;
    left: 34px !important;
    right: 118px !important;
    top: 58px !important;
    height: 1px !important;
    background: #e5e7eb !important;
    pointer-events: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-composer-avatar,
  .post-show-shell #show-comment-form .ps-comment-suggestions { display: none !important; }

  .post-show-shell #show-comment-form .ps-mention-live-wrap {
    grid-area: input !important;
    width: 100% !important;
    min-width: 0 !important;
    margin: 0 !important;
    align-self: stretch !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea,
  .post-show-shell #show-comment-form .ps-mention-live-layer,
  .post-show-shell #show-comment-form .ps-mention-live-input {
    min-height: 72px !important;
    padding: 4px 16px !important;
    border: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    font-size: 18px !important;
    line-height: 1.55 !important;
    font-weight: 400 !important;
    resize: none !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea::placeholder { color: #9ca3af !important; }
  .post-show-shell #show-comment-form .ps-mention-live-layer .ps-mention-token,
  .post-show-shell .ps-mention-token {
    display: inline !important;
    padding: 2px 6px !important;
    border-radius: 10px !important;
    background: #dbeeff !important;
    color: #1683f7 !important;
    font-weight: 600 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-toolbar {
    grid-area: tools !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 12px !important;
    margin: 0 !important;
    padding: 0 !important;
    align-self: center !important;
    position: relative !important;
    z-index: 4 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool.ps-comment-mention-button {
    width: 38px !important;
    height: 38px !important;
    border-radius: 999px !important;
    color: #6b7280 !important;
    background: transparent !important;
    font-size: 26px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
    padding: 0 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool.ps-comment-mention-button:hover {
    background: #f3f4f6 !important;
    color: #2563eb !important;
  }

  .post-show-shell #show-comment-form .ps-comment-send {
    height: 48px !important;
    min-width: 82px !important;
    padding: 0 18px !important;
    border-radius: 12px !important;
    background: linear-gradient(180deg, #2388ff, #1374f2) !important;
    color: #ffffff !important;
    box-shadow: none !important;
    font-size: 16px !important;
    font-weight: 800 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-send:hover { background: #0f6fe8 !important; color: #fff !important; }

  .post-show-shell .ps-comment-mention-menu {
    position: absolute !important;
    left: 84px !important;
    top: 78px !important;
    z-index: 75 !important;
    width: min(320px, calc(100vw - 72px)) !important;
    max-height: 260px !important;
    overflow: auto !important;
    padding: 8px !important;
    border-radius: 14px !important;
    border: 1px solid #e5e7eb !important;
    background: rgba(255,255,255,.98) !important;
    box-shadow: none !important;
    backdrop-filter: blur(10px) !important;
  }
  .post-show-shell .ps-comment-mention-menu[hidden] { display: none !important; }
  .post-show-shell .ps-comment-mention-option {
    width: 100% !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 9px 10px !important;
    border: 0 !important;
    border-radius: 11px !important;
    background: transparent !important;
    color: #111827 !important;
    cursor: pointer !important;
    text-align: left !important;
  }
  .post-show-shell .ps-comment-mention-option:hover,
  .post-show-shell .ps-comment-mention-option.is-active { background: #e8f4ff !important; }
  .post-show-shell .ps-comment-mention-avatar {
    position: relative !important;
    width: 34px !important;
    height: 34px !important;
    flex: 0 0 34px !important;
    border-radius: 999px !important;
    overflow: visible !important;
    background: #eef2ff !important;
    color: #2563eb !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 12px !important;
    font-weight: 800 !important;
  }
  .post-show-shell .ps-comment-mention-avatar img {
    width: 34px !important;
    height: 34px !important;
    border-radius: 999px !important;
    object-fit: cover !important;
    display: block !important;
  }
  .post-show-shell .ps-comment-mention-avatar::after,
  .post-show-shell .ps-comment-avatar::after {
    content: "" !important;
    position: absolute !important;
    right: -1px !important;
    bottom: -1px !important;
    width: 10px !important;
    height: 10px !important;
    border-radius: 999px !important;
    border: 2px solid #ffffff !important;
    background: #22c55e !important;
  }
  .post-show-shell .ps-comment-mention-name {
    display: block !important;
    min-width: 0 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    font-size: 15px !important;
    font-weight: 800 !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comments-list {
    padding: 24px 22px 28px !important;
    background: #ffffff !important;
    gap: 0 !important;
  }

  .post-show-shell .ps-comment-item {
    position: relative !important;
    display: flex !important;
    gap: 14px !important;
    padding: 20px 0 24px !important;
    border-bottom: 1px solid #e5e7eb !important;
    overflow: visible !important;
  }
  .post-show-shell .ps-comment-item:last-child { border-bottom: 0 !important; }
  .post-show-shell .ps-comment-avatar {
    position: relative !important;
    width: 38px !important;
    height: 38px !important;
    flex: 0 0 38px !important;
    border-radius: 999px !important;
    border: 1px solid #eef2f7 !important;
    background: #fff7ed !important;
    color: #f97316 !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    overflow: visible !important;
  }
  .post-show-shell .ps-comment-avatar img {
    width: 100% !important;
    height: 100% !important;
    border-radius: 999px !important;
    object-fit: cover !important;
  }
  .post-show-shell .ps-comment-body { min-width: 0 !important; flex: 1 1 auto !important; }
  .post-show-shell .ps-comment-meta { gap: 7px !important; padding-right: 46px !important; }
  .post-show-shell .ps-comment-author {
    color: #1f2937 !important;
    font-size: 15px !important;
    font-weight: 800 !important;
    line-height: 1.25 !important;
  }
  .post-show-shell .ps-comment-role {
    border-radius: 999px !important;
    padding: 2px 7px !important;
    background: #eff6ff !important;
    color: #2563eb !important;
    font-size: 11px !important;
    font-weight: 700 !important;
  }
  .post-show-shell .ps-comment-time {
    color: #9ca3af !important;
    font-size: 13px !important;
    font-weight: 500 !important;
  }
  .post-show-shell .ps-comment-text {
    margin-top: 10px !important;
    color: #4b5563 !important;
    font-size: 15px !important;
    line-height: 1.65 !important;
    white-space: normal !important;
  }
  .post-show-shell .ps-comment-actions {
    margin-top: 14px !important;
    gap: 10px !important;
    color: #9ca3af !important;
  }
  .post-show-shell .ps-comment-votes { gap: 6px !important; }
  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn {
    width: 28px !important;
    height: 28px !important;
    border-radius: 999px !important;
    color: #7b818c !important;
  }
  .post-show-shell .ps-comment-vote-count {
    min-width: 28px !important;
    height: 28px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 9px !important;
    border-radius: 10px !important;
    border: 1px solid #e5e7eb !important;
    background: #f9fafb !important;
    color: #374151 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
  }
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn::after {
    content: "Yanıtla" !important;
    margin-left: 5px !important;
    color: #374151 !important;
    font-size: 13px !important;
    font-weight: 800 !important;
  }
  .post-show-shell .ps-comment-more {
    position: absolute !important;
    right: 0 !important;
    top: 18px !important;
  }
  .post-show-shell .ps-comment-more-trigger {
    color: #6b7280 !important;
    width: 30px !important;
    height: 30px !important;
  }

  .post-show-shell .ps-replies {
    position: relative !important;
    margin: 18px 0 0 8px !important;
    padding-left: 24px !important;
    border-left: 2px solid #e8eef7 !important;
  }
  .post-show-shell .ps-replies::before,
  .post-show-shell .ps-replies > .ps-comment-item::before { display: none !important; }
  .post-show-shell .ps-replies > .ps-comment-item {
    padding-top: 14px !important;
    padding-bottom: 16px !important;
    border-bottom: 0 !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comments-header { padding: 16px 14px 0 !important; }
    .post-show-shell .ps-comments-title { font-size: 20px !important; }
    .post-show-shell .ps-comments-sort-btn { padding: 0 10px !important; font-size: 12px !important; }
    .post-show-shell #show-comment-form .ps-comment-form-box {
      grid-template-columns: 1fr !important;
      grid-template-areas: "input" "tools" !important;
      min-height: 126px !important;
      padding: 14px !important;
    }
    .post-show-shell #show-comment-form .ps-comment-toolbar { justify-content: flex-end !important; }
    .post-show-shell .ps-comment-mention-menu { left: 14px !important; top: 72px !important; width: calc(100vw - 56px) !important; }
    .post-show-shell .ps-comments-list { padding: 18px 14px 24px !important; }
    .post-show-shell .ps-comment-item { gap: 10px !important; }
  }

  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section,
  html.dark .post-show-shell .ps-comments-list,
  body.dark .post-show-shell .ps-comments-list,
  .dark .post-show-shell .ps-comments-list,
  [data-theme="dark"] .post-show-shell .ps-comments-list,
  html.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  body.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  .dark .post-show-shell #show-comment-form .ps-comment-form-box,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-form-box {
    background: #111318 !important;
    border-color: rgba(96, 165, 250, .85) !important;
  }
  html.dark .post-show-shell .ps-comments-title,
  body.dark .post-show-shell .ps-comments-title,
  .dark .post-show-shell .ps-comments-title,
  [data-theme="dark"] .post-show-shell .ps-comments-title,
  html.dark .post-show-shell .ps-comment-author,
  body.dark .post-show-shell .ps-comment-author,
  .dark .post-show-shell .ps-comment-author,
  [data-theme="dark"] .post-show-shell .ps-comment-author { color: #f8fafc !important; }
  html.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text { color: #d1d5db !important; }
  html.dark .post-show-shell .ps-comment-mention-menu,
  body.dark .post-show-shell .ps-comment-mention-menu,
  .dark .post-show-shell .ps-comment-mention-menu,
  [data-theme="dark"] .post-show-shell .ps-comment-mention-menu { background: #171a22 !important; border-color: #2b3444 !important; }


  /* VIDEO STYLE OVERRIDE: koyu, kompakt, altta yanıt kutulu yorum paneli */
  .post-show-shell .ps-comments-section {
    width: min(100%, 560px) !important;
    max-width: 560px !important;
    margin: 0 auto 28px !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: visible !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 18px !important;
    background: #111214 !important;
    box-shadow: none !important;
    color: #f6f7fb !important;
  }

  .post-show-shell .ps-comments-header {
    display: contents !important;
    padding: 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comments-top {
    order: 1 !important;
    min-height: 38px !important;
    margin: 0 !important;
    padding: 0 11px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 10px !important;
    border-bottom: 1px solid rgba(255,255,255,.09) !important;
    border-radius: 18px 18px 0 0 !important;
    background: #2a2b2f !important;
  }

  .post-show-shell .ps-comments-title {
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    margin: 0 !important;
    padding: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #d9dce3 !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 600 !important;
    letter-spacing: 0 !important;
  }

  .post-show-shell .ps-comments-title::before {
    content: "" !important;
    width: 11px !important;
    height: 11px !important;
    margin: 0 !important;
    border-radius: 999px !important;
    background: #6b7280 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-count-badge {
    display: none !important;
  }

  .post-show-shell .ps-comments-sort {
    position: static !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 9px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    padding: 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comments-sort::before {
    content: "✓" !important;
    color: #a5abb7 !important;
    font-size: 17px !important;
    line-height: 1 !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-comments-sort::after {
    content: "×" !important;
    color: #8c929f !important;
    font-size: 18px !important;
    line-height: 1 !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-comments-sort-btn {
    display: none !important;
  }

  .post-show-shell .ps-comments-list {
    order: 2 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 0 !important;
    padding: 14px 12px 4px !important;
    margin: 0 !important;
    overflow: visible !important;
    background: #111214 !important;
    color: #f6f7fb !important;
  }

  .post-show-shell .ps-comment-item {
    position: relative !important;
    display: flex !important;
    gap: 10px !important;
    width: 100% !important;
    padding: 0 0 13px !important;
    margin: 0 !important;
    border: 0 !important;
    overflow: visible !important;
    background: transparent !important;
  }

  .post-show-shell .ps-comment-avatar {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    flex: 0 0 32px !important;
    border: 2px solid rgba(255,255,255,.08) !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    background: #2f3137 !important;
    color: #facc15 !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-avatar::after,
  .post-show-shell .ps-comment-mention-avatar::after {
    display: none !important;
  }

  .post-show-shell .ps-comment-body {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    overflow: visible !important;
    background: transparent !important;
  }

  .post-show-shell .ps-comment-meta {
    display: flex !important;
    align-items: center !important;
    gap: 7px !important;
    min-height: 18px !important;
    margin: 0 0 5px !important;
    padding: 0 28px 0 0 !important;
  }

  .post-show-shell .ps-comment-author {
    color: #f2f4f8 !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 700 !important;
  }

  .post-show-shell .ps-comment-time {
    order: 8 !important;
    color: #727783 !important;
    font-size: 10px !important;
    line-height: 1 !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-comment-role {
    height: 17px !important;
    padding: 0 6px !important;
    border-radius: 999px !important;
    background: rgba(214,255,0,.13) !important;
    color: #d7ff00 !important;
    font-size: 10px !important;
    line-height: 1 !important;
    font-weight: 600 !important;
  }

  .post-show-shell .ps-comment-text {
    width: auto !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    color: #d9dde6 !important;
    font-size: 12.5px !important;
    line-height: 1.55 !important;
    white-space: normal !important;
    word-break: break-word !important;
  }

  .post-show-shell .ps-comment-text-line {
    color: inherit !important;
    font-size: inherit !important;
    line-height: inherit !important;
  }

  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-comment-actions--reply {
    display: flex !important;
    align-items: center !important;
    gap: 7px !important;
    margin: 8px 0 0 !important;
    color: #838894 !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
  }

  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-more-trigger {
    width: 22px !important;
    height: 22px !important;
    min-width: 22px !important;
    min-height: 22px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #858a96 !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-more-trigger:hover {
    background: rgba(255,255,255,.08) !important;
    color: #f7f9ff !important;
  }

  .post-show-shell .ps-comment-vote-btn svg,
  .post-show-shell .ps-comment-action svg,
  .post-show-shell .ps-comment-more-trigger svg {
    width: 15px !important;
    height: 15px !important;
  }

  .post-show-shell .ps-comment-vote-count {
    min-width: 0 !important;
    height: auto !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #858a96 !important;
    font-size: 11px !important;
    line-height: 1 !important;
    font-weight: 600 !important;
  }

  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn::after {
    content: "Yanıtla" !important;
    margin-left: 5px !important;
    color: #8f95a1 !important;
    font-size: 11px !important;
    line-height: 1 !important;
    font-weight: 600 !important;
  }

  .post-show-shell .ps-comment-more {
    position: absolute !important;
    right: 0 !important;
    top: 0 !important;
  }

  .post-show-shell .ps-comment-more-menu {
    background: #24262b !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    color: #f5f7fb !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form {
    order: 3 !important;
    position: relative !important;
    z-index: 25 !important;
    margin: 0 9px 9px !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box {
    position: relative !important;
    display: grid !important;
    grid-template-columns: 34px minmax(0, 1fr) auto !important;
    grid-template-areas: "avatar input tools" !important;
    align-items: center !important;
    gap: 7px !important;
    min-height: 40px !important;
    width: 100% !important;
    padding: 4px 5px !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    border-radius: 999px !important;
    background: #1f2024 !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box::before {
    display: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-composer-avatar {
    grid-area: avatar !important;
    display: inline-flex !important;
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    border: 2px solid #ff8fcf !important;
    border-radius: 999px !important;
    background: #2f3137 !important;
    color: #f8fafc !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    overflow: hidden !important;
  }

  .post-show-shell #show-comment-form .ps-mention-live-wrap {
    grid-area: input !important;
    min-width: 0 !important;
    width: 100% !important;
    height: 30px !important;
    margin: 0 !important;
    display: block !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea,
  .post-show-shell #show-comment-form .ps-mention-live-layer,
  .post-show-shell #show-comment-form .ps-mention-live-input {
    width: 100% !important;
    min-height: 30px !important;
    height: 30px !important;
    max-height: 86px !important;
    padding: 7px 4px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #f2f5fb !important;
    font-size: 12px !important;
    line-height: 16px !important;
    font-weight: 400 !important;
    resize: none !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea::placeholder {
    color: #777d89 !important;
  }

  .post-show-shell #show-comment-form .ps-mention-live-layer .ps-mention-token,
  .post-show-shell .ps-mention-token {
    display: inline !important;
    padding: 1px 5px !important;
    border-radius: 8px !important;
    background: rgba(214,255,0,.14) !important;
    color: #d7ff00 !important;
    font-weight: 600 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-toolbar {
    grid-area: tools !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    margin: 0 !important;
    padding: 0 !important;
    position: static !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool.ps-comment-mention-button {
    display: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-send {
    height: 30px !important;
    min-height: 30px !important;
    min-width: 54px !important;
    width: auto !important;
    padding: 0 13px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #353840 !important;
    color: #d6dae3 !important;
    box-shadow: none !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 700 !important;
  }

  .post-show-shell #show-comment-form.has-comment-text .ps-comment-send,
  .post-show-shell #show-comment-form:focus-within .ps-comment-send {
    background: #d7ff00 !important;
    color: #101113 !important;
  }

  .post-show-shell .ps-comment-mention-menu {
    left: 39px !important;
    top: auto !important;
    bottom: calc(100% + 8px) !important;
    width: min(260px, calc(100vw - 48px)) !important;
    max-height: 240px !important;
    padding: 7px !important;
    border-radius: 14px !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    background: #24262b !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mention-option {
    gap: 9px !important;
    padding: 7px 8px !important;
    border-radius: 11px !important;
    color: #f6f7fb !important;
    background: transparent !important;
  }

  .post-show-shell .ps-comment-mention-option:hover,
  .post-show-shell .ps-comment-mention-option.is-active {
    background: rgba(214,255,0,.12) !important;
  }

  .post-show-shell .ps-comment-mention-avatar,
  .post-show-shell .ps-comment-mention-avatar img {
    width: 30px !important;
    height: 30px !important;
    flex-basis: 30px !important;
  }

  .post-show-shell .ps-comment-mention-name {
    color: #f6f7fb !important;
    font-size: 12px !important;
    font-weight: 700 !important;
  }

  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-mini-box--reply {
    display: grid !important;
    grid-template-columns: 30px minmax(0,1fr) auto !important;
    gap: 7px !important;
    align-items: center !important;
    margin: 9px 0 0 !important;
    padding: 4px 5px !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    border-radius: 999px !important;
    background: #1f2024 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mini-avatar {
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    border: 2px solid #ff8fcf !important;
    border-radius: 999px !important;
    overflow: hidden !important;
  }

  .post-show-shell .ps-comment-mini-textarea {
    min-height: 30px !important;
    height: 30px !important;
    padding: 7px 4px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #f2f5fb !important;
    font-size: 12px !important;
    line-height: 16px !important;
    resize: none !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mini-textarea::placeholder {
    color: #777d89 !important;
  }

  .post-show-shell .ps-comment-mini-actions {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-mini-image-btn,
  .post-show-shell .ps-comment-mini-btn--ghost {
    display: none !important;
  }

  .post-show-shell .ps-comment-mini-btn--primary,
  .post-show-shell .ps-comment-mini-btn {
    height: 30px !important;
    min-height: 30px !important;
    padding: 0 12px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #353840 !important;
    color: #d6dae3 !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 700 !important;
  }

  .post-show-shell .ps-comment-mini-box:focus-within .ps-comment-mini-btn--primary {
    background: #d7ff00 !important;
    color: #101113 !important;
  }

  .post-show-shell .ps-replies {
    margin: 10px 0 0 2px !important;
    padding-left: 23px !important;
    border-left: 1px solid rgba(255,255,255,.13) !important;
    gap: 9px !important;
  }

  .post-show-shell .ps-replies > .ps-comment-item {
    padding: 0 0 10px !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comments-section {
      width: 100% !important;
      border-radius: 16px !important;
    }
    .post-show-shell .ps-comments-top {
      min-height: 36px !important;
      border-radius: 16px 16px 0 0 !important;
    }
    .post-show-shell .ps-comments-list {
      padding: 12px 10px 4px !important;
    }
    .post-show-shell #show-comment-form {
      margin: 0 8px 8px !important;
    }
    .post-show-shell #show-comment-form .ps-comment-form-box,
    .post-show-shell .ps-comment-mini-box,
    .post-show-shell .ps-comment-mini-box--reply {
      grid-template-columns: 30px minmax(0,1fr) auto !important;
      grid-template-areas: "avatar input tools" !important;
    }
    .post-show-shell #show-comment-form .ps-comment-send span {
      display: inline !important;
    }
  }


  /* FINAL OVERRIDE: tam genişlik, beyaz arka plan, gölgesiz yorum alanı */
  .post-show-shell .ps-comments-section {
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
    margin: 0 0 28px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: #ffffff !important;
    box-shadow: none !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comments-header,
  .post-show-shell .ps-comments-list,
  .post-show-shell #show-comment-form,
  .post-show-shell .ps-comment-item,
  .post-show-shell .ps-comment-body,
  .post-show-shell .ps-comment-card,
  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-mini-box--reply,
  .post-show-shell .ps-comment-form-box {
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-top {
    border-radius: 0 !important;
    border-bottom: 1px solid #eef0f3 !important;
    background: #ffffff !important;
    color: #111827 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-title,
  .post-show-shell .ps-comment-author {
    color: #111827 !important;
  }

  .post-show-shell .ps-comments-title::before {
    background: #d1d5db !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-sort::before,
  .post-show-shell .ps-comments-sort::after {
    color: #6b7280 !important;
  }

  .post-show-shell .ps-comments-list {
    width: 100% !important;
    background: #ffffff !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-item {
    background: transparent !important;
    border-bottom: 1px solid #f1f3f5 !important;
  }

  .post-show-shell .ps-comment-item:last-child {
    border-bottom: 0 !important;
  }

  .post-show-shell .ps-comment-text,
  .post-show-shell .ps-comment-text-line {
    color: #4b5563 !important;
  }

  .post-show-shell .ps-comment-time,
  .post-show-shell .ps-comment-vote-count,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn::after {
    color: #6b7280 !important;
  }

  .post-show-shell .ps-comment-role {
    background: #eff6ff !important;
    color: #2563eb !important;
  }

  .post-show-shell .ps-comment-avatar,
  .post-show-shell #show-comment-form .ps-comment-composer-avatar,
  .post-show-shell .ps-comment-mini-avatar {
    border-color: #eef2f7 !important;
    background: #f9fafb !important;
    color: #f97316 !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form {
    width: 100% !important;
    margin: 0 !important;
    padding: 10px 12px 12px !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box,
  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-mini-box--reply {
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea,
  .post-show-shell #show-comment-form .ps-mention-live-layer,
  .post-show-shell #show-comment-form .ps-mention-live-input,
  .post-show-shell .ps-comment-mini-textarea {
    color: #111827 !important;
    background: transparent !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea::placeholder,
  .post-show-shell .ps-comment-mini-textarea::placeholder {
    color: #9ca3af !important;
  }

  .post-show-shell #show-comment-form .ps-comment-send,
  .post-show-shell .ps-comment-mini-btn--primary,
  .post-show-shell .ps-comment-mini-btn {
    background: #f3f4f6 !important;
    color: #374151 !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form.has-comment-text .ps-comment-send,
  .post-show-shell #show-comment-form:focus-within .ps-comment-send,
  .post-show-shell .ps-comment-mini-box:focus-within .ps-comment-mini-btn--primary {
    background: #2563eb !important;
    color: #ffffff !important;
  }

  .post-show-shell .ps-comment-mention-menu,
  .post-show-shell .ps-comment-more-menu {
    background: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    color: #111827 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mention-option,
  .post-show-shell .ps-comment-mention-name {
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-mention-option:hover,
  .post-show-shell .ps-comment-mention-option.is-active {
    background: #eff6ff !important;
  }

  .post-show-shell .ps-replies {
    border-left-color: #e5e7eb !important;
  }

  @media (max-width: 960px) {
    .post-show-shell .ps-comments-section {
      width: 100vw !important;
      max-width: 100vw !important;
      margin-left: calc(50% - 50vw) !important;
      margin-right: calc(50% - 50vw) !important;
      border-radius: 0 !important;
    }
  }


  /* FINAL FIX: yorum alanında siyah arka plan kalmasın */
  .post-show-shell .ps-comments-section,
  .post-show-shell .ps-comments-section *,
  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section,
  html.dark .post-show-shell .ps-comments-section *,
  body.dark .post-show-shell .ps-comments-section *,
  .dark .post-show-shell .ps-comments-section *,
  [data-theme="dark"] .post-show-shell .ps-comments-section * {
    box-shadow: none !important;
  }

  .post-show-shell .ps-comments-section,
  .post-show-shell .ps-comments-header,
  .post-show-shell .ps-comments-top,
  .post-show-shell .ps-comments-list,
  .post-show-shell .ps-comment-item,
  .post-show-shell .ps-comment-card,
  .post-show-shell .ps-comment-body,
  .post-show-shell .ps-comment-meta,
  .post-show-shell .ps-comment-content,
  .post-show-shell .ps-comment-text,
  .post-show-shell .ps-comment-text-line,
  .post-show-shell .ps-replies,
  .post-show-shell .ps-replies .ps-comment-item,
  .post-show-shell .ps-comment-actions,
  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section,
  html.dark .post-show-shell .ps-comments-header,
  body.dark .post-show-shell .ps-comments-header,
  .dark .post-show-shell .ps-comments-header,
  [data-theme="dark"] .post-show-shell .ps-comments-header,
  html.dark .post-show-shell .ps-comments-top,
  body.dark .post-show-shell .ps-comments-top,
  .dark .post-show-shell .ps-comments-top,
  [data-theme="dark"] .post-show-shell .ps-comments-top,
  html.dark .post-show-shell .ps-comments-list,
  body.dark .post-show-shell .ps-comments-list,
  .dark .post-show-shell .ps-comments-list,
  [data-theme="dark"] .post-show-shell .ps-comments-list,
  html.dark .post-show-shell .ps-comment-item,
  body.dark .post-show-shell .ps-comment-item,
  .dark .post-show-shell .ps-comment-item,
  [data-theme="dark"] .post-show-shell .ps-comment-item,
  html.dark .post-show-shell .ps-comment-card,
  body.dark .post-show-shell .ps-comment-card,
  .dark .post-show-shell .ps-comment-card,
  [data-theme="dark"] .post-show-shell .ps-comment-card,
  html.dark .post-show-shell .ps-comment-body,
  body.dark .post-show-shell .ps-comment-body,
  .dark .post-show-shell .ps-comment-body,
  [data-theme="dark"] .post-show-shell .ps-comment-body,
  html.dark .post-show-shell .ps-comment-content,
  body.dark .post-show-shell .ps-comment-content,
  .dark .post-show-shell .ps-comment-content,
  [data-theme="dark"] .post-show-shell .ps-comment-content,
  html.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text,
  html.dark .post-show-shell .ps-comment-text-line,
  body.dark .post-show-shell .ps-comment-text-line,
  .dark .post-show-shell .ps-comment-text-line,
  [data-theme="dark"] .post-show-shell .ps-comment-text-line,
  html.dark .post-show-shell .ps-replies,
  body.dark .post-show-shell .ps-replies,
  .dark .post-show-shell .ps-replies,
  [data-theme="dark"] .post-show-shell .ps-replies {
    background: transparent !important;
    background-color: transparent !important;
    background-image: none !important;
  }

  .post-show-shell .ps-comments-section,
  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section,
  .post-show-shell .ps-comments-header,
  html.dark .post-show-shell .ps-comments-header,
  body.dark .post-show-shell .ps-comments-header,
  .dark .post-show-shell .ps-comments-header,
  [data-theme="dark"] .post-show-shell .ps-comments-header,
  .post-show-shell .ps-comments-top,
  html.dark .post-show-shell .ps-comments-top,
  body.dark .post-show-shell .ps-comments-top,
  .dark .post-show-shell .ps-comments-top,
  [data-theme="dark"] .post-show-shell .ps-comments-top,
  .post-show-shell .ps-comments-list,
  html.dark .post-show-shell .ps-comments-list,
  body.dark .post-show-shell .ps-comments-list,
  .dark .post-show-shell .ps-comments-list,
  [data-theme="dark"] .post-show-shell .ps-comments-list,
  .post-show-shell #show-comment-form,
  html.dark .post-show-shell #show-comment-form,
  body.dark .post-show-shell #show-comment-form,
  .dark .post-show-shell #show-comment-form,
  [data-theme="dark"] .post-show-shell #show-comment-form {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-text,
  .post-show-shell .ps-comment-text-line,
  .post-show-shell .ps-comment-content,
  html.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text,
  html.dark .post-show-shell .ps-comment-text-line,
  body.dark .post-show-shell .ps-comment-text-line,
  .dark .post-show-shell .ps-comment-text-line,
  [data-theme="dark"] .post-show-shell .ps-comment-text-line {
    display: block !important;
    width: auto !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    background-color: transparent !important;
    background-image: none !important;
    color: #374151 !important;
    white-space: normal !important;
  }

  .post-show-shell .ps-comment-author,
  .post-show-shell .ps-comments-title,
  html.dark .post-show-shell .ps-comment-author,
  body.dark .post-show-shell .ps-comment-author,
  .dark .post-show-shell .ps-comment-author,
  [data-theme="dark"] .post-show-shell .ps-comment-author,
  html.dark .post-show-shell .ps-comments-title,
  body.dark .post-show-shell .ps-comments-title,
  .dark .post-show-shell .ps-comments-title,
  [data-theme="dark"] .post-show-shell .ps-comments-title {
    color: #111827 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box,
  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-mini-box--reply,
  html.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  body.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  .dark .post-show-shell #show-comment-form .ps-comment-form-box,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-form-box,
  html.dark .post-show-shell .ps-comment-mini-box,
  body.dark .post-show-shell .ps-comment-mini-box,
  .dark .post-show-shell .ps-comment-mini-box,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-box,
  html.dark .post-show-shell .ps-comment-mini-box--reply,
  body.dark .post-show-shell .ps-comment-mini-box--reply,
  .dark .post-show-shell .ps-comment-mini-box--reply,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-box--reply {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    border-color: #e5e7eb !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea,
  .post-show-shell #show-comment-form .ps-mention-live-layer,
  .post-show-shell #show-comment-form .ps-mention-live-input,
  .post-show-shell .ps-comment-mini-textarea,
  html.dark .post-show-shell #show-comment-form .ps-comment-textarea,
  body.dark .post-show-shell #show-comment-form .ps-comment-textarea,
  .dark .post-show-shell #show-comment-form .ps-comment-textarea,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-textarea,
  html.dark .post-show-shell .ps-comment-mini-textarea,
  body.dark .post-show-shell .ps-comment-mini-textarea,
  .dark .post-show-shell .ps-comment-mini-textarea,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-textarea {
    background: transparent !important;
    background-color: transparent !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-comment-more-menu,
  .post-show-shell .ps-comment-mention-menu,
  html.dark .post-show-shell .ps-comment-more-menu,
  body.dark .post-show-shell .ps-comment-more-menu,
  .dark .post-show-shell .ps-comment-more-menu,
  [data-theme="dark"] .post-show-shell .ps-comment-more-menu,
  html.dark .post-show-shell .ps-comment-mention-menu,
  body.dark .post-show-shell .ps-comment-mention-menu,
  .dark .post-show-shell .ps-comment-mention-menu,
  [data-theme="dark"] .post-show-shell .ps-comment-mention-menu {
    background: #ffffff !important;
    background-color: #ffffff !important;
    background-image: none !important;
    border-color: #e5e7eb !important;
    color: #111827 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-action,
  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-more-trigger,
  .post-show-shell .ps-comment-time,
  html.dark .post-show-shell .ps-comment-action,
  body.dark .post-show-shell .ps-comment-action,
  .dark .post-show-shell .ps-comment-action,
  [data-theme="dark"] .post-show-shell .ps-comment-action,
  html.dark .post-show-shell .ps-comment-vote-btn,
  body.dark .post-show-shell .ps-comment-vote-btn,
  .dark .post-show-shell .ps-comment-vote-btn,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn,
  html.dark .post-show-shell .ps-comment-time,
  body.dark .post-show-shell .ps-comment-time,
  .dark .post-show-shell .ps-comment-time,
  [data-theme="dark"] .post-show-shell .ps-comment-time {
    background: transparent !important;
    color: #6b7280 !important;
  }


  /* FINAL: hover kart sadece ad/kategori metninde + 1.5 sn skeleton gecikme + daha kucuk kart */
  .post-show-shell .ps-hover-zone--avatar > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-badge > .ps-hover-card {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }

  .post-show-shell .ps-hover-zone--avatar,
  .post-show-shell .ps-hover-zone--category-badge {
    cursor: default !important;
  }

  .post-show-shell .ps-hover-zone--author-name,
  .post-show-shell .ps-hover-zone--category-name {
    cursor: pointer !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card {
    width: min(258px, calc(100vw - 28px)) !important;
    border-radius: 14px !important;
    padding-bottom: 10px !important;
    overflow: hidden !important;
    isolation: isolate !important;
    transition: opacity .12s ease, visibility .12s ease, transform .12s ease !important;
  }

  .post-show-shell .ps-hover-zone--author-name:hover > .ps-hover-card,
  .post-show-shell .ps-hover-zone--author-name:focus-within > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name:hover > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name:focus-within > .ps-hover-card {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translate3d(0, 0, 0) !important;
    pointer-events: auto !important;
  }

  .post-show-shell .ps-hover-zone--author-name:hover > .ps-hover-card > *,
  .post-show-shell .ps-hover-zone--author-name:focus-within > .ps-hover-card > *,
  .post-show-shell .ps-hover-zone--category-name:hover > .ps-hover-card > *,
  .post-show-shell .ps-hover-zone--category-name:focus-within > .ps-hover-card > * {
    opacity: 0;
    animation: psHoverCardContentReveal .001s linear 1.5s forwards;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 5;
    display: block;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    background:
      radial-gradient(circle at 33px 76px, #e9edf3 0 20px, transparent 21px),
      linear-gradient(#e9edf3, #e9edf3) 64px 64px / 118px 11px no-repeat,
      linear-gradient(#eef2f6, #eef2f6) 64px 82px / 88px 9px no-repeat,
      linear-gradient(#eef2f6, #eef2f6) 13px 122px / 222px 10px no-repeat,
      linear-gradient(#eef2f6, #eef2f6) 13px 142px / 185px 10px no-repeat,
      linear-gradient(#f7f9fc, #f7f9fc) 12px 173px / 108px 30px no-repeat,
      linear-gradient(#f7f9fc, #f7f9fc) 132px 173px / 108px 30px no-repeat,
      linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%) 0 0 / 100% 58px no-repeat,
      #ffffff;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::after,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 6;
    display: block;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    background: linear-gradient(100deg, transparent 0%, rgba(255,255,255,.78) 48%, transparent 100%);
    transform: translateX(-120%);
  }

  .post-show-shell .ps-hover-zone--author-name:hover > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--author-name:focus-within > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--category-name:hover > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--category-name:focus-within > .ps-hover-card::before {
    opacity: 1;
    visibility: visible;
    animation: psHoverCardSkeletonOff .001s linear 1.5s forwards;
  }

  .post-show-shell .ps-hover-zone--author-name:hover > .ps-hover-card::after,
  .post-show-shell .ps-hover-zone--author-name:focus-within > .ps-hover-card::after,
  .post-show-shell .ps-hover-zone--category-name:hover > .ps-hover-card::after,
  .post-show-shell .ps-hover-zone--category-name:focus-within > .ps-hover-card::after {
    opacity: 1;
    visibility: visible;
    animation: psHoverCardShimmer .9s ease-in-out 0s infinite, psHoverCardSkeletonOff .001s linear 1.5s forwards;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-cover,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-cover {
    height: 58px !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-main,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-main {
    gap: 8px !important;
    margin-top: -18px !important;
    padding: 0 11px !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-avatar,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-avatar {
    width: 46px !important;
    height: 46px !important;
    flex-basis: 46px !important;
    border-width: 2px !important;
    font-size: 12px !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-content,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-content {
    transform: translateY(5px) !important;
    padding-bottom: 0 !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-title,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-title {
    max-width: 168px !important;
    font-size: 13px !important;
    line-height: 1.18 !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-subtitle,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-subtitle {
    max-width: 168px !important;
    font-size: 11px !important;
    line-height: 1.2 !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-description,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-description {
    min-height: 28px !important;
    padding: 11px 11px 0 !important;
    font-size: 11.5px !important;
    line-height: 1.38 !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-actions,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-actions {
    width: calc(100% - 22px) !important;
    margin: 10px 11px 0 !important;
    gap: 7px !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-follow,
  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-link,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-follow,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-link {
    min-height: 30px !important;
    padding: 8px 8px !important;
    border-radius: 9px !important;
    font-size: 11.5px !important;
  }

  @keyframes psHoverCardContentReveal {
    to { opacity: 1; }
  }

  @keyframes psHoverCardSkeletonOff {
    to { opacity: 0; visibility: hidden; }
  }

  @keyframes psHoverCardShimmer {
    from { transform: translateX(-120%); }
    to { transform: translateX(120%); }
  }

  html.dark .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::before,
  body.dark .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::before,
  .dark .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::before,
  [data-theme="dark"] .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::before,
  html.dark .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::before,
  body.dark .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::before,
  .dark .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::before,
  [data-theme="dark"] .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::before {
    background:
      radial-gradient(circle at 33px 76px, #1e293b 0 20px, transparent 21px),
      linear-gradient(#1e293b, #1e293b) 64px 64px / 118px 11px no-repeat,
      linear-gradient(#223047, #223047) 64px 82px / 88px 9px no-repeat,
      linear-gradient(#223047, #223047) 13px 122px / 222px 10px no-repeat,
      linear-gradient(#223047, #223047) 13px 142px / 185px 10px no-repeat,
      linear-gradient(#172033, #172033) 12px 173px / 108px 30px no-repeat,
      linear-gradient(#172033, #172033) 132px 173px / 108px 30px no-repeat,
      linear-gradient(135deg, #1e293b 0%, #111827 100%) 0 0 / 100% 58px no-repeat,
      #0f172a;
  }

  html.dark .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::after,
  body.dark .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::after,
  .dark .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::after,
  [data-theme="dark"] .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::after,
  html.dark .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::after,
  body.dark .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::after,
  .dark .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::after,
  [data-theme="dark"] .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::after {
    background: linear-gradient(100deg, transparent 0%, rgba(255,255,255,.12) 48%, transparent 100%);
  }

</style>

<!-- SEO / Open Graph / NewsArticle Schema -->
{{-- meta[name=description] is already rendered once in layouts/app.blade.php from the
     same $description via @section('meta_description', ...); do not duplicate it here. --}}
<meta name="author" content="{{ e($authorName) }}">
<meta property="og:type" content="article">
<meta property="og:site_name" content="{{ e($siteName !== '' ? $siteName : 'Ografi') }}">
<meta property="og:locale" content="{{ e(str_replace('-', '_', $seoLanguage)) }}">
<meta property="og:title" content="{{ e($seoTitle) }}">
<meta property="og:description" content="{{ e($description) }}">
<meta property="og:url" content="{{ e($postUrl) }}">
<meta property="article:published_time" content="{{ e($seoPublishedIso) }}">
<meta property="article:modified_time" content="{{ e($seoModifiedIso) }}">
@if($author && $author->name)
<meta property="article:author" content="{{ e($author->name) }}">
@endif
@if($hasCategory)
<meta property="article:section" content="{{ e($categoryName) }}">
@endif
@foreach($seoTagNames as $seoTagName)
<meta property="article:tag" content="{{ e($seoTagName) }}">
@endforeach
@if($seoPrimaryImage)
<meta property="og:image" content="{{ e($seoPrimaryImage) }}">
<meta property="og:image:secure_url" content="{{ e($seoPrimaryImage) }}">
<meta property="og:image:width" content="{{ $seoPrimaryImageWidth }}">
<meta property="og:image:height" content="{{ $seoPrimaryImageHeight }}">
<meta property="og:image:alt" content="{{ e($seoTitleBase) }}">
@endif
<meta name="twitter:card" content="{{ $seoPrimaryImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ e($seoTitle) }}">
<meta name="twitter:description" content="{{ e($description) }}">
@if($seoPrimaryImage)
<meta name="twitter:image" content="{{ e($seoPrimaryImage) }}">
<meta name="twitter:image:alt" content="{{ e($seoTitleBase) }}">
<link rel="image_src" href="{{ e($seoPrimaryImage) }}">
@endif
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="bingbot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="language" content="{{ e($seoLanguage) }}">
<meta name="theme-color" content="#2563eb">
@if($seoKeywords->isNotEmpty())
<meta name="keywords" content="{{ e($seoKeywords->implode(', ')) }}">
<meta name="news_keywords" content="{{ e($seoKeywords->implode(', ')) }}">
@endif
@if($seoReadingTimeMinutes)
<meta name="twitter:label1" content="Okuma süresi">
<meta name="twitter:data1" content="{{ $seoReadingTimeMinutes }} dk">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<script type="application/ld+json">
{!! json_encode($seoJsonLdGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

<script>
  (function () {
    const escapeHtml = function (value) {
      return String(value || '').replace(/[&<>"']/g, function (char) {
        return {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        }[char];
      });
    };

    const paintMentions = function (value) {
      const safe = escapeHtml(value);
      return safe.replace(/(^|\s)(@[\p{L}\p{N}_.-]+)/gu, function (_, prefix, token) {
        return prefix + '<span class="ps-mention-token">' + token + '</span>';
      });
    };

    const syncMentionLayer = function (textarea) {
      const wrap = textarea.closest('.ps-mention-live-wrap');
      const layer = wrap ? wrap.querySelector('.ps-mention-live-layer') : null;
      if (!wrap || !layer) return;

      const value = textarea.value || '';
      wrap.classList.toggle('has-value', value.length > 0);
      layer.innerHTML = paintMentions(value) + (value.endsWith('\n') ? '<br>' : '');
      layer.scrollTop = textarea.scrollTop;
      layer.scrollLeft = textarea.scrollLeft;
    };

    const initMentionTextarea = function (textarea) {
      if (!textarea || textarea.dataset.mentionPaintReady === 'true') return;
      if (textarea.classList.contains('ogx3-textarea') || textarea.closest('[data-ogx-composer]')) return;
      textarea.dataset.mentionPaintReady = 'true';
      textarea.classList.add('ps-mention-live-input');

      const wrap = document.createElement('div');
      wrap.className = 'ps-mention-live-wrap';

      const layer = document.createElement('div');
      layer.className = 'ps-mention-live-layer';
      layer.setAttribute('aria-hidden', 'true');

      textarea.parentNode.insertBefore(wrap, textarea);
      wrap.appendChild(layer);
      wrap.appendChild(textarea);

      ['input', 'change', 'keyup'].forEach(function (eventName) {
        textarea.addEventListener(eventName, function () { syncMentionLayer(textarea); });
      });
      textarea.addEventListener('scroll', function () { syncMentionLayer(textarea); });

      syncMentionLayer(textarea);
    };

    const initAllMentionTextareas = function () {
      document.querySelectorAll('.post-show-shell textarea[name="content"]:not(.ogx3-textarea), .post-show-shell textarea[data-mentionable="users"]:not(.ogx3-textarea)').forEach(initMentionTextarea);
    };

    initAllMentionTextareas();

    document.addEventListener('click', function () {
      window.setTimeout(initAllMentionTextareas, 0);
    });
  })();

</script>


<script>
  (function () {
    const formsSelector = '#show-comment-form, .ps-comment-reply-form, .ps-comment-edit-form';

    document.addEventListener('submit', function (event) {
      const form = event.target.closest(formsSelector);
      if (!form) return;

      const textarea = form.querySelector('textarea[name="content"]');
      const fileInput = form.querySelector('[data-comment-file-input], input[type="file"][name="image"]');
      const hasFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);

      if (textarea) {
        textarea.value = String(textarea.value || '')
          .replace(/[ \t]{2,}/g, ' ')
          .replace(/\n{3,}/g, '\n\n')
          .trim();
      }

      const hasText = !!(textarea && textarea.value.trim() !== '');

      if (!hasText && !hasFile) {
        event.preventDefault();
        if (textarea) textarea.focus();
      }
    }, true);
  })();
</script>


<style>
  /* FINAL FIX: yorum aksiyon ikonları ve bağlantıları arasında temiz boşluk */
  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-comment-actions--reply {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 0 !important;
    column-gap: 0 !important;
    row-gap: 8px !important;
    margin-top: 10px !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-right: 14px !important;
    flex: 0 0 auto !important;
  }

  .post-show-shell .ps-comment-votes form {
    display: inline-flex !important;
    align-items: center !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-vote-btn {
    width: 20px !important;
    height: 20px !important;
    min-width: 20px !important;
    min-height: 20px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    margin: 0 !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-vote-btn svg {
    width: 14px !important;
    height: 14px !important;
    display: block !important;
  }

  .post-show-shell .ps-comment-vote-count {
    display: inline-flex !important;
    align-items: center !important;
    min-width: 10px !important;
    margin-left: -3px !important;
    margin-right: 4px !important;
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    line-height: 1 !important;
  }

  .post-show-shell .ps-comment-action,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-toggle-replies {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    min-width: auto !important;
    width: auto !important;
    height: 22px !important;
    min-height: 22px !important;
    padding: 0 2px !important;
    margin: 0 14px 0 0 !important;
    border: 0 !important;
    border-radius: 6px !important;
    background: transparent !important;
    color: #64748b !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
    text-decoration: none !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn::after {
    content: "Yanıtla" !important;
    display: inline-flex !important;
    margin-left: 4px !important;
    color: inherit !important;
    font-size: 12px !important;
    font-weight: 700 !important;
  }

  .post-show-shell .ps-comment-action svg,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn svg,
  .post-show-shell .ps-comment-toggle-replies svg {
    width: 14px !important;
    height: 14px !important;
    display: block !important;
    flex: 0 0 auto !important;
  }

  .post-show-shell .ps-comment-toggle-replies,
  .post-show-shell .ps-comment-toggle-replies * {
    color: #60a5fa !important;
  }

  .post-show-shell .ps-comment-actions > *:last-child,
  .post-show-shell .ps-comment-actions--reply > *:last-child {
    margin-right: 0 !important;
  }

  .post-show-shell .ps-comment-action:hover,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-toggle-replies:hover {
    background: #f3f4f6 !important;
    color: #111827 !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comment-votes {
      gap: 7px !important;
      margin-right: 12px !important;
    }

    .post-show-shell .ps-comment-action,
    .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn,
    .post-show-shell .ps-comment-toggle-replies {
      margin-right: 12px !important;
      font-size: 12px !important;
    }
  }
</style>

@endpush


@push('head')
<style>
  /* OGRAFI MODERN COMMENT COMPOSER: sade, hizalı, esnek ve mobil uyumlu */
  .post-show-shell .ps-comments-section {
    border-radius: 18px !important;
    background: #ffffff !important;
    border: 1px solid #eef2f7 !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comments-header {
    padding: 18px 18px 0 !important;
  }

  .post-show-shell .ps-comments-top {
    margin-bottom: 14px !important;
    gap: 12px !important;
  }

  .post-show-shell .ps-comments-title {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    color: #0f172a !important;
    font-size: 18px !important;
    font-weight: 600 !important;
    letter-spacing: -0.02em !important;
  }

  .post-show-shell .ps-comments-count-badge {
    min-width: 24px !important;
    height: 24px !important;
    padding: 0 8px !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: #eff6ff !important;
    color: #2563eb !important;
    font-size: 12px !important;
    font-weight: 600 !important;
  }

  .post-show-shell .ps-comments-sort {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 3px !important;
    border-radius: 999px !important;
    background: #f8fafc !important;
    border: 1px solid #edf2f7 !important;
  }

  .post-show-shell .ps-comments-sort-btn {
    height: 28px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    padding: 0 10px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-comments-sort-btn.is-active,
  .post-show-shell .ps-comments-sort-btn:hover {
    background: #ffffff !important;
    color: #0f172a !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form {
    position: relative !important;
    z-index: 25 !important;
    margin: 0 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box--modern {
    display: grid !important;
    grid-template-columns: 42px minmax(0, 1fr) auto !important;
    grid-template-areas:
      "avatar main tools" !important;
    align-items: start !important;
    gap: 10px !important;
    width: 100% !important;
    min-height: 64px !important;
    padding: 10px !important;
    border: 1px solid #e8eef5 !important;
    border-radius: 18px !important;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%) !important;
    box-shadow: none !important;
    overflow: visible !important;
    transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box--modern:focus-within {
    border-color: rgba(37, 99, 235, .42) !important;
    box-shadow: none !important;
    transform: translateY(-1px) !important;
  }

  .post-show-shell #show-comment-form .ps-comment-composer-avatar {
    grid-area: avatar !important;
    width: 42px !important;
    height: 42px !important;
    border-radius: 999px !important;
    border: 2px solid #ffffff !important;
    background: #eef2f7 !important;
    color: #475569 !important;
    box-shadow: none !important;
    overflow: hidden !important;
  }

  .post-show-shell .ps-comment-compose-main {
    grid-area: main !important;
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 7px !important;
  }

  .post-show-shell .ps-comment-compose-topline {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 10px !important;
    padding: 0 2px !important;
  }

  .post-show-shell .ps-comment-compose-kicker,
  .post-show-shell .ps-comment-compose-counter {
    color: #94a3b8 !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
  }

  .post-show-shell .ps-comment-compose-counter.is-warning { color: #f59e0b !important; }
  .post-show-shell .ps-comment-compose-counter.is-danger { color: #ef4444 !important; }

  .post-show-shell #show-comment-form .ps-comment-textarea {
    width: 100% !important;
    min-height: 38px !important;
    max-height: 220px !important;
    height: auto !important;
    padding: 9px 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #0f172a !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 1.55 !important;
    resize: none !important;
    outline: none !important;
    box-shadow: none !important;
    overflow-y: hidden !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea::placeholder {
    color: #94a3b8 !important;
  }

  .post-show-shell .ps-comment-suggestions {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-suggestions-title {
    color: #94a3b8 !important;
    font-size: 11px !important;
    font-weight: 500 !important;
  }

  .post-show-shell .ps-comment-suggestions button {
    height: 26px !important;
    border: 1px solid #e8eef5 !important;
    border-radius: 999px !important;
    background: #ffffff !important;
    color: #64748b !important;
    padding: 0 9px !important;
    font-size: 11.5px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-comment-suggestions button:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
  }

  .post-show-shell #show-comment-form .ps-comment-toolbar {
    grid-area: tools !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 6px !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool,
  .post-show-shell #show-comment-form .ps-comment-send {
    width: 38px !important;
    min-width: 38px !important;
    height: 38px !important;
    min-height: 38px !important;
    border: 0 !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    box-shadow: none !important;
    cursor: pointer !important;
    transition: background .14s ease, color .14s ease, transform .14s ease !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool {
    background: #f1f5f9 !important;
    color: #64748b !important;
    font-size: 15px !important;
    font-weight: 600 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
  }

  .post-show-shell #show-comment-form .ps-comment-send {
    width: auto !important;
    min-width: 74px !important;
    padding: 0 14px !important;
    background: #2563eb !important;
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 600 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-send:hover {
    background: #1d4ed8 !important;
    transform: translateY(-1px) !important;
  }

  .post-show-shell #show-comment-form .ps-comment-send:disabled,
  .post-show-shell #show-comment-form .ps-comment-send.is-disabled {
    opacity: .55 !important;
    cursor: not-allowed !important;
    transform: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-tool svg,
  .post-show-shell #show-comment-form .ps-comment-send svg {
    width: 18px !important;
    height: 18px !important;
    display: block !important;
  }

  .post-show-shell .ps-comment-image-preview,
  .post-show-shell .ps-comment-gif-preview {
    width: 100% !important;
    margin: 2px 0 0 !important;
    padding: 8px !important;
    border: 1px dashed #cbd5e1 !important;
    border-radius: 14px !important;
    background: #f8fafc !important;
    color: #64748b !important;
    font-size: 12px !important;
  }

  .post-show-shell .ps-comment-image-preview img {
    max-width: 180px !important;
    max-height: 120px !important;
    border-radius: 12px !important;
    object-fit: cover !important;
    display: block !important;
  }

  .post-show-shell .ps-comments-list {
    padding: 18px !important;
    gap: 18px !important;
    background: transparent !important;
  }

  .post-show-shell .ps-comment-item {
    display: grid !important;
    grid-template-columns: 40px minmax(0, 1fr) !important;
    align-items: start !important;
    gap: 10px !important;
    padding: 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-avatar {
    width: 40px !important;
    height: 40px !important;
    border-radius: 999px !important;
    border: 2px solid #ffffff !important;
    background: #eef2f7 !important;
    color: #475569 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-body {
    min-width: 0 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-meta {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
    margin: 0 0 5px !important;
  }

  .post-show-shell .ps-comment-author {
    color: #0f172a !important;
    font-size: 13px !important;
    font-weight: 600 !important;
  }

  .post-show-shell .ps-comment-role {
    height: 20px !important;
    padding: 0 7px !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    align-items: center !important;
    background: #eff6ff !important;
    color: #2563eb !important;
    font-size: 11px !important;
    font-weight: 600 !important;
  }

  .post-show-shell .ps-comment-time {
    color: #94a3b8 !important;
    font-size: 12px !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-text {
    width: fit-content !important;
    max-width: min(100%, 650px) !important;
    padding: 10px 13px !important;
    border: 1px solid #eef2f7 !important;
    border-radius: 16px !important;
    border-top-left-radius: 6px !important;
    background: #f8fafc !important;
    color: #1e293b !important;
    font-size: 13.5px !important;
    font-weight: 400 !important;
    line-height: 1.55 !important;
    box-shadow: none !important;
    white-space: normal !important;
    word-break: break-word !important;
  }

  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-comment-actions--reply {
    margin-top: 8px !important;
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    margin-right: 4px !important;
  }

  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-more-trigger,
  .post-show-shell .ps-replies-toggle--inline {
    height: 28px !important;
    min-height: 28px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    padding: 0 8px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-comment-vote-btn { width: 28px !important; min-width: 28px !important; padding: 0 !important; }
  .post-show-shell .ps-comment-more-trigger { width: 28px !important; min-width: 28px !important; padding: 0 !important; }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  .post-show-shell .ps-replies-toggle--inline:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
  }

  .post-show-shell .ps-comment-more {
    position: relative !important;
    z-index: 40 !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-more.is-open { z-index: 200 !important; }

  .post-show-shell .ps-comment-more-menu {
    position: absolute !important;
    right: 0 !important;
    left: auto !important;
    top: calc(100% + 8px) !important;
    z-index: 220 !important;
    min-width: 156px !important;
    padding: 6px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 14px !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-more-menu[hidden] { display: none !important; }
  .post-show-shell .ps-comment-more-menu:not([hidden]) { display: flex !important; flex-direction: column !important; gap: 2px !important; }

  .post-show-shell .ps-comment-more-item {
    width: 100% !important;
    min-height: 36px !important;
    border: 0 !important;
    border-radius: 10px !important;
    background: transparent !important;
    color: #334155 !important;
    padding: 8px 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 9px !important;
    font-family: Poppins, Arial, sans-serif !important;
    font-size: 12.5px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-comment-more-item:hover { background: #f1f5f9 !important; color: #0f172a !important; }
  .post-show-shell .ps-comment-more-item--danger { color: #dc2626 !important; }
  .post-show-shell .ps-comment-more-item--danger:hover { background: #fef2f2 !important; color: #b91c1c !important; }

  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-mini-box--reply {
    border-radius: 16px !important;
    border: 1px solid #e8eef5 !important;
    background: #fbfdff !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mini-textarea {
    min-height: 38px !important;
    max-height: 180px !important;
    border: 0 !important;
    background: transparent !important;
    color: #0f172a !important;
    resize: none !important;
    overflow-y: hidden !important;
  }

  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section {
    background: #111318 !important;
    border-color: rgba(255,255,255,.10) !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  body.dark .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  .dark .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  html.dark .post-show-shell .ps-comment-mini-box,
  body.dark .post-show-shell .ps-comment-mini-box,
  .dark .post-show-shell .ps-comment-mini-box,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-box {
    background: #171a21 !important;
    border-color: rgba(255,255,255,.10) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-textarea,
  body.dark .post-show-shell #show-comment-form .ps-comment-textarea,
  .dark .post-show-shell #show-comment-form .ps-comment-textarea,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-textarea,
  html.dark .post-show-shell .ps-comment-mini-textarea,
  body.dark .post-show-shell .ps-comment-mini-textarea,
  .dark .post-show-shell .ps-comment-mini-textarea,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-textarea {
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-comments-title,
  body.dark .post-show-shell .ps-comments-title,
  .dark .post-show-shell .ps-comments-title,
  [data-theme="dark"] .post-show-shell .ps-comments-title,
  html.dark .post-show-shell .ps-comment-author,
  body.dark .post-show-shell .ps-comment-author,
  .dark .post-show-shell .ps-comment-author,
  [data-theme="dark"] .post-show-shell .ps-comment-author {
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text {
    background: #1b1f27 !important;
    border-color: rgba(255,255,255,.08) !important;
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-comment-tool,
  body.dark .post-show-shell .ps-comment-tool,
  .dark .post-show-shell .ps-comment-tool,
  [data-theme="dark"] .post-show-shell .ps-comment-tool,
  html.dark .post-show-shell .ps-comments-sort,
  body.dark .post-show-shell .ps-comments-sort,
  .dark .post-show-shell .ps-comments-sort,
  [data-theme="dark"] .post-show-shell .ps-comments-sort,
  html.dark .post-show-shell .ps-comment-suggestions button,
  body.dark .post-show-shell .ps-comment-suggestions button,
  .dark .post-show-shell .ps-comment-suggestions button,
  [data-theme="dark"] .post-show-shell .ps-comment-suggestions button {
    background: rgba(255,255,255,.06) !important;
    border-color: rgba(255,255,255,.08) !important;
    color: #cbd5e1 !important;
  }

  html.dark .post-show-shell .ps-comment-more-menu,
  body.dark .post-show-shell .ps-comment-more-menu,
  .dark .post-show-shell .ps-comment-more-menu,
  [data-theme="dark"] .post-show-shell .ps-comment-more-menu {
    background: #171a21 !important;
    border-color: rgba(255,255,255,.10) !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comments-header { padding: 12px 10px 0 !important; }
    .post-show-shell .ps-comments-top { align-items: flex-start !important; }
    .post-show-shell .ps-comments-sort { max-width: 100% !important; overflow-x: auto !important; }
    .post-show-shell #show-comment-form .ps-comment-form-box--modern {
      grid-template-columns: 36px minmax(0, 1fr) !important;
      grid-template-areas:
        "avatar main"
        "tools tools" !important;
      gap: 8px !important;
      padding: 10px !important;
      border-radius: 16px !important;
    }
    .post-show-shell #show-comment-form .ps-comment-composer-avatar { width: 36px !important; height: 36px !important; }
    .post-show-shell #show-comment-form .ps-comment-toolbar { justify-content: flex-end !important; }
    .post-show-shell #show-comment-form .ps-comment-send { min-width: 68px !important; }
    .post-show-shell .ps-comments-list { padding: 14px 10px 22px !important; gap: 16px !important; }
    .post-show-shell .ps-comment-item { grid-template-columns: 36px minmax(0, 1fr) !important; gap: 9px !important; }
    .post-show-shell .ps-comment-avatar { width: 36px !important; height: 36px !important; }
    .post-show-shell .ps-comment-text { font-size: 13px !important; padding: 9px 12px !important; }
    .post-show-shell .ps-comment-more-menu { right: 0 !important; min-width: 150px !important; }
  }

/* =========================================================
   HOTFIX: üst boşluk + gerçekten görünen dalgalı yükleme
   Bu blok en sonda durmalı; önceki 56/72px kurallarını ezer.
   ========================================================= */
html body:has(.post-show-shell) .layout-main,
html body:has(.post-show-shell) .main-grid,
html body:has(.post-show-shell) .main-grid.main-grid--padded,
html body.alma-app:has(.post-show-shell) .layout-main,
html body.alma-app:has(.post-show-shell) .main-grid,
html body.alma-app:has(.post-show-shell) .main-grid.main-grid--padded {
  padding-top: 0 !important;
  margin-top: 0 !important;
}

.ps-layout.post-show-shell,
.post-show-shell.ps-layout {
  padding: 24px 12px 96px !important;
  margin-top: 0 !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: flex-start !important;
  gap: 10px !important;
}

.post-show-shell .ps-main {
  width: 100% !important;
  max-width: 656px !important;
  margin: 0 auto !important;
}

.post-show-shell .ps-wave-loader {
  display: block !important;
  width: 100% !important;
  max-width: 656px !important;
  max-height: 0 !important;
  min-height: 0 !important;
  opacity: 0 !important;
  visibility: hidden !important;
  margin: 0 auto !important;
  padding: 0 16px !important;
  border-radius: 18px !important;
  background: #ffffff !important;
  overflow: hidden !important;
  pointer-events: none !important;
  transform: translateY(-4px) !important;
  transition: opacity .24s ease, transform .24s ease, max-height .24s ease, padding .24s ease, margin .24s ease !important;
}

.post-show-shell.is-loading .ps-wave-loader {
  max-height: 240px !important;
  min-height: 214px !important;
  opacity: 1 !important;
  visibility: visible !important;
  margin: 0 auto 10px !important;
  padding: 14px 16px !important;
  transform: translateY(0) !important;
}

.post-show-shell.is-loading .ps-main {
  opacity: .36 !important;
  transition: opacity .24s ease !important;
}

.post-show-shell.is-loaded .ps-main {
  opacity: 1 !important;
}

.post-show-shell .ps-wave-avatar,
.post-show-shell .ps-wave-line,
.post-show-shell .ps-wave-media {
  position: relative !important;
  overflow: hidden !important;
  background: #eceff3 !important;
}

.post-show-shell .ps-wave-avatar::after,
.post-show-shell .ps-wave-line::after,
.post-show-shell .ps-wave-media::after {
  content: "" !important;
  position: absolute !important;
  top: 0 !important;
  bottom: 0 !important;
  left: -45% !important;
  width: 45% !important;
  background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.82) 50%, transparent 100%) !important;
  animation: psWaveLoadingFixed 1.05s ease-in-out infinite !important;
}

@keyframes psWaveLoadingFixed {
  0% { left: -45%; }
  100% { left: 115%; }
}

@media (max-width: 640px) {
  .ps-layout.post-show-shell,
  .post-show-shell.ps-layout {
    padding: 8px 0 96px !important;
    gap: 8px !important;
  }

  .post-show-shell.is-loading .ps-wave-loader {
    width: calc(100% - 16px) !important;
    max-width: calc(100% - 16px) !important;
    min-height: 178px !important;
    margin: 0 8px 8px !important;
    padding: 12px !important;
    border-radius: 14px !important;
  }

  .post-show-shell .ps-wave-media {
    height: 96px !important;
  }
}

html.dark .post-show-shell .ps-wave-loader,
body.dark .post-show-shell .ps-wave-loader,
.dark .post-show-shell .ps-wave-loader,
[data-theme="dark"] .post-show-shell .ps-wave-loader {
  background: #0f172a !important;
}

html.dark .post-show-shell .ps-wave-avatar,
html.dark .post-show-shell .ps-wave-line,
html.dark .post-show-shell .ps-wave-media,
body.dark .post-show-shell .ps-wave-avatar,
body.dark .post-show-shell .ps-wave-line,
body.dark .post-show-shell .ps-wave-media,
.dark .post-show-shell .ps-wave-avatar,
.dark .post-show-shell .ps-wave-line,
.dark .post-show-shell .ps-wave-media,
[data-theme="dark"] .post-show-shell .ps-wave-avatar,
[data-theme="dark"] .post-show-shell .ps-wave-line,
[data-theme="dark"] .post-show-shell .ps-wave-media {
  background: rgba(255,255,255,.10) !important;
}

html.dark .post-show-shell .ps-wave-avatar::after,
html.dark .post-show-shell .ps-wave-line::after,
html.dark .post-show-shell .ps-wave-media::after,
body.dark .post-show-shell .ps-wave-avatar::after,
body.dark .post-show-shell .ps-wave-line::after,
body.dark .post-show-shell .ps-wave-media::after,
.dark .post-show-shell .ps-wave-avatar::after,
.dark .post-show-shell .ps-wave-line::after,
.dark .post-show-shell .ps-wave-media::after,
[data-theme="dark"] .post-show-shell .ps-wave-avatar::after,
[data-theme="dark"] .post-show-shell .ps-wave-line::after,
[data-theme="dark"] .post-show-shell .ps-wave-media::after {
  background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.22) 50%, transparent 100%) !important;
}


  /* Kalem ikonunun gerçekten görünmesi için son güvenlik düzeltmesi */
  .post-show-shell .post-author-meta {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    white-space: normal !important;
    overflow: visible !important;
  }

  .post-show-shell .post-author-date,
  .post-show-shell .post-author-category,
  .post-show-shell .post-author-edited-wrap {
    flex: 0 0 auto !important;
  }

  .post-show-shell .post-author-edited-wrap,
  .post-show-shell [data-post-edit-details] {
    display: inline-flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    position: relative !important;
    z-index: 10 !important;
  }

  .post-show-shell .post-author-edited-button {
    display: inline-flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    color: #64748b !important;
  }

  .post-show-shell .post-author-edited-button svg,
  .post-show-shell svg.post-author-edited-icon,
  .post-show-shell svg.ps-post-edited-icon {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    overflow: visible !important;
    color: currentColor !important;
  }

</style>
@endpush

@push('head')
<style>
/* Last readable post text scale */
.post-show-shell .ps-post-body,
.post-show-shell .ps-post-body :where(p, div, li, td, th, blockquote, .ce-paragraph, .cdx-block) {
  font-size: 17px !important;
  line-height: 1.72 !important;
}

.post-show-shell .ps-tags-row,
.post-show-shell .ps-tag {
  font-size: 15px !important;
  line-height: 1.35 !important;
}

@media (max-width: 640px) {
  .post-show-shell .ps-post-body,
  .post-show-shell .ps-post-body :where(p, div, li, td, th, blockquote, .ce-paragraph, .cdx-block) {
    font-size: 16px !important;
    line-height: 1.68 !important;
  }

  .post-show-shell .ps-tags-row,
  .post-show-shell .ps-tag {
    font-size: 15px !important;
  }
}
</style>
@endpush

@push('head')
<style>
/* Final readable post text scale */
.post-show-shell .ps-post-body,
.post-show-shell .ps-post-body :where(p, div, li, td, th, blockquote, .ce-paragraph, .cdx-block) {
  font-size: 17px !important;
  line-height: 1.72 !important;
}

.post-show-shell .ps-tags-row,
.post-show-shell .ps-tag {
  font-size: 15px !important;
  line-height: 1.35 !important;
}

@media (max-width: 640px) {
  .post-show-shell .ps-post-body,
  .post-show-shell .ps-post-body :where(p, div, li, td, th, blockquote, .ce-paragraph, .cdx-block) {
    font-size: 16px !important;
    line-height: 1.68 !important;
  }

  .post-show-shell .ps-tags-row,
  .post-show-shell .ps-tag {
    font-size: 15px !important;
  }
}
</style>
@endpush


@push('head')
<style>
/* ============================================================
   OGRAFI FINAL REQUEST UPDATE — GLOBAL WAVE + HASHTAG + EDITORJS
   - Bütün ana bloklara dalgalı yükleme efekti
   - Hashtag yazıları büyütüldü
   - Açıklama/post body 16px sabitlendi
   - EditorJS blok/plugin çıktıları düzgün görünsün
   ============================================================ */

@keyframes psGlobalWaveSweep {
  0% { transform: translateX(-115%); }
  100% { transform: translateX(115%); }
}

@keyframes psGlobalWavePulse {
  0%, 100% { opacity: .72; }
  50% { opacity: 1; }
}

/* Header altı boşluk: son override her zaman 24px kalsın */
.ps-layout.post-show-shell,
.post-show-shell.ps-layout {
  padding-top: 24px !important;
}

/* Dalgalı yükleme: sayfadaki bütün temel kolon/kart/alanlarda görünür */
.post-show-shell.is-loading {
  --ps-wave-base: #eef1f5;
  --ps-wave-soft: rgba(248, 250, 252, .82);
  --ps-wave-line: rgba(255, 255, 255, .76);
}

.post-show-shell.is-loading :where(
  .ps-sidebar-left,
  .ps-nav-item,
  .ps-post-card,
  .ps-post-card-inner,
  .ps-post-author,
  .ps-post-title,
  .ps-post-image,
  .ps-post-body,
  .ps-source-link,
  .ps-tags-row,
  .ps-reaction-row,
  .ps-action-row,
  .ps-comments-section,
  .ps-comments-header,
  .ps-comment-form-box,
  .ps-comments-list,
  .ps-comment-item,
  .ps-comment-card,
  .ps-sidebar-card,
  .ps-recent-comment-item,
  .ps-tag-row
) {
  position: relative !important;
  overflow: hidden !important;
}

.post-show-shell.is-loading :where(
  .ps-nav-item,
  .ps-post-card,
  .ps-post-author,
  .ps-post-title,
  .ps-post-image,
  .ps-post-body,
  .ps-source-link,
  .ps-tags-row,
  .ps-reaction-row,
  .ps-action-row,
  .ps-comments-header,
  .ps-comment-form-box,
  .ps-comment-item,
  .ps-sidebar-card,
  .ps-recent-comment-item,
  .ps-tag-row
)::after {
  content: "" !important;
  position: absolute !important;
  inset: 0 !important;
  z-index: 7 !important;
  pointer-events: none !important;
  border-radius: inherit !important;
  background:
    linear-gradient(105deg,
      transparent 0%,
      transparent 28%,
      var(--ps-wave-line) 48%,
      transparent 68%,
      transparent 100%) !important;
  transform: translateX(-115%);
  animation: psGlobalWaveSweep 1.25s ease-in-out infinite !important;
}

.post-show-shell.is-loading :where(
  .ps-post-card,
  .ps-comments-section,
  .ps-sidebar-card
) {
  background-image: linear-gradient(90deg, var(--ps-wave-soft), #fff, var(--ps-wave-soft)) !important;
  background-size: 220% 100% !important;
  animation: psGlobalWavePulse 1.45s ease-in-out infinite !important;
}

.post-show-shell.is-loading .ps-wave-loader {
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
}

.post-show-shell.is-loaded .ps-wave-loader {
  opacity: 0 !important;
  visibility: hidden !important;
  transition: opacity .28s ease, visibility .28s ease !important;
}

/* Hashtag yazıları biraz daha büyük ve okunaklı */
.post-show-shell .ps-tags-row {
  gap: 10px !important;
}

.post-show-shell .ps-tag,
.post-show-shell .ps-tags-row .ps-tag {
  font-size: 15px !important;
  line-height: 1.35 !important;
  font-weight: 600 !important;
  letter-spacing: -0.01em !important;
}

.post-show-shell .ps-tag-name {
  font-size: 14px !important;
  line-height: 1.35 !important;
}

/* Açıklama / içerik yazısı 16px sabit */
.post-show-shell .ps-post-body,
.post-show-shell .ps-post-body p,
.post-show-shell .ps-post-body .ce-paragraph,
.post-show-shell .ps-post-body .cdx-block {
  font-size: 16px !important;
  line-height: 1.72 !important;
}

/* EditorJS genel blok düzeni */
.post-show-shell .ps-post-body :where(.ce-block, .cdx-block, .ce-block__content, .ce-paragraph) {
  width: 100% !important;
  max-width: 100% !important;
  box-sizing: border-box !important;
}

.post-show-shell .ps-post-body :where(.ce-block, .cdx-block) {
  margin: 0 0 16px !important;
}

.post-show-shell .ps-post-body :where(.ce-paragraph, p) {
  margin: 0 0 14px !important;
  color: inherit !important;
  word-break: break-word !important;
}

.post-show-shell .ps-post-body :where(h2, h3, h4, h5, h6, .ce-header) {
  margin: 22px 0 12px !important;
  color: #0f172a !important;
  font-weight: 700 !important;
  line-height: 1.28 !important;
}

.post-show-shell .ps-post-body h2,
.post-show-shell .ps-post-body .ce-header[data-level="2"] { font-size: 24px !important; }
.post-show-shell .ps-post-body h3,
.post-show-shell .ps-post-body .ce-header[data-level="3"] { font-size: 21px !important; }
.post-show-shell .ps-post-body h4,
.post-show-shell .ps-post-body .ce-header[data-level="4"] { font-size: 18px !important; }

/* EditorJS image/gallery/embed/video plugin çıktıları */
.post-show-shell .ps-post-body :where(.image-tool, .ps-full-media, .ps-full-gallery, figure) {
  width: 100% !important;
  max-width: 100% !important;
  margin: 16px 0 !important;
  border-radius: 14px !important;
  overflow: hidden !important;
}

.post-show-shell .ps-post-body :where(.image-tool__image, .ps-full-media, .ps-full-gallery) {
  background: #f8fafc !important;
}

.post-show-shell .ps-post-body :where(img, video, iframe, .image-tool__image-picture) {
  max-width: 100% !important;
  border-radius: 14px !important;
}

.post-show-shell .ps-post-body :where(.image-tool__image-picture, img, video) {
  height: auto !important;
  display: block !important;
  object-fit: cover !important;
}

.post-show-shell .ps-post-body :where(.image-tool__caption, figcaption) {
  padding: 8px 2px 0 !important;
  color: #64748b !important;
  font-size: 13px !important;
  line-height: 1.45 !important;
  text-align: center !important;
}

.post-show-shell .ps-post-body :where(.embed-tool, .ps-full-media--embed, .ps-full-media--video) {
  width: 100% !important;
  border-radius: 14px !important;
  overflow: hidden !important;
  background: #f8fafc !important;
}

.post-show-shell .ps-post-body :where(.embed-tool iframe, .ps-full-media--embed iframe, .ps-full-media--video video) {
  width: 100% !important;
  min-height: 360px !important;
  border: 0 !important;
  display: block !important;
}

/* EditorJS list/checklist/table/quote/code plugin çıktıları */
.post-show-shell .ps-post-body :where(ul, ol, .cdx-list) {
  margin: 12px 0 16px !important;
  padding-left: 1.35rem !important;
}

.post-show-shell .ps-post-body :where(li, .cdx-list__item) {
  margin: 6px 0 !important;
  line-height: 1.65 !important;
}

.post-show-shell .ps-post-body :where(.cdx-checklist, .ps-checklist) {
  list-style: none !important;
  padding-left: 0 !important;
  margin: 14px 0 18px !important;
}

.post-show-shell .ps-post-body :where(.cdx-checklist__item, .ps-checklist li) {
  display: flex !important;
  align-items: flex-start !important;
  gap: 10px !important;
  margin: 8px 0 !important;
}

.post-show-shell .ps-post-body :where(.cdx-checklist__item-checkbox, .ps-check-dot) {
  flex: 0 0 20px !important;
  width: 20px !important;
  height: 20px !important;
  border-radius: 6px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  margin-top: 3px !important;
  background: #eef2ff !important;
  color: #2563eb !important;
}

.post-show-shell .ps-post-body :where(.tc-wrap, .ps-table-wrap, .ce-table) {
  width: 100% !important;
  max-width: 100% !important;
  overflow-x: auto !important;
  -webkit-overflow-scrolling: touch !important;
  margin: 16px 0 !important;
  border: 1px solid #e5e7eb !important;
  border-radius: 14px !important;
  background: #fff !important;
}

.post-show-shell .ps-post-body table {
  width: 100% !important;
  min-width: 520px !important;
  border-collapse: collapse !important;
  font-size: 15px !important;
}

.post-show-shell .ps-post-body :where(th, td) {
  border: 1px solid #e5e7eb !important;
  padding: 10px 12px !important;
  text-align: left !important;
  vertical-align: top !important;
}

.post-show-shell .ps-post-body :where(blockquote, .cdx-quote) {
  margin: 16px 0 !important;
  padding: 14px 16px !important;
  border-left: 4px solid #2563eb !important;
  border-radius: 12px !important;
  background: #f8fafc !important;
  color: #1e293b !important;
}

.post-show-shell .ps-post-body :where(pre, .cdx-code) {
  width: 100% !important;
  overflow-x: auto !important;
  margin: 16px 0 !important;
  padding: 14px !important;
  border-radius: 14px !important;
  background: #0f172a !important;
  color: #f8fafc !important;
  font-size: 14px !important;
  line-height: 1.65 !important;
}

.post-show-shell .ps-post-body code {
  border-radius: 6px !important;
  background: #f1f5f9 !important;
  color: #0f172a !important;
  padding: 2px 5px !important;
  font-size: .92em !important;
}

.post-show-shell .ps-post-body pre code,
.post-show-shell .ps-post-body .cdx-code code {
  background: transparent !important;
  color: inherit !important;
  padding: 0 !important;
}

.post-show-shell .ps-post-body :where(.cdx-warning, .link-tool, .attaches-tool) {
  width: 100% !important;
  margin: 16px 0 !important;
  padding: 14px !important;
  border: 1px solid #e5e7eb !important;
  border-radius: 14px !important;
  background: #f8fafc !important;
  box-sizing: border-box !important;
}

.post-show-shell .ps-post-body :where(.link-tool__content, .attaches-tool__content) {
  display: block !important;
  color: inherit !important;
  text-decoration: none !important;
}

.post-show-shell .ps-post-body :where(.delimiter, .ps-full-delimiter) {
  width: 72px !important;
  height: 1px !important;
  margin: 26px auto !important;
  border: 0 !important;
  background: #cbd5e1 !important;
}

/* EditorJS araç kutusu/ayarları yanlışlıkla içerikte görünürse akışı bozmasın */
.post-show-shell .ps-post-body :where(.ce-toolbar, .ce-settings, .ce-conversion-toolbar, .ce-inline-toolbar) {
  display: none !important;
}

@media (max-width: 640px) {
  .ps-layout.post-show-shell,
  .post-show-shell.ps-layout {
    padding-top: 12px !important;
  }

  .post-show-shell .ps-post-body,
  .post-show-shell .ps-post-body p,
  .post-show-shell .ps-post-body .ce-paragraph,
  .post-show-shell .ps-post-body .cdx-block {
    font-size: 16px !important;
    line-height: 1.68 !important;
  }

  .post-show-shell .ps-tag,
  .post-show-shell .ps-tags-row .ps-tag {
    font-size: 14px !important;
  }

  .post-show-shell .ps-post-body :where(.embed-tool iframe, .ps-full-media--embed iframe, .ps-full-media--video video) {
    min-height: 220px !important;
  }

  .post-show-shell .ps-post-body table {
    min-width: 460px !important;
  }
}

html.dark .post-show-shell.is-loading,
body.dark .post-show-shell.is-loading,
.dark .post-show-shell.is-loading,
[data-theme="dark"] .post-show-shell.is-loading {
  --ps-wave-base: rgba(255,255,255,.08);
  --ps-wave-soft: rgba(15, 23, 42, .84);
  --ps-wave-line: rgba(255,255,255,.18);
}

html.dark .post-show-shell.is-loading :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card),
body.dark .post-show-shell.is-loading :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card),
.dark .post-show-shell.is-loading :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card),
[data-theme="dark"] .post-show-shell.is-loading :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card) {
  background-image: linear-gradient(90deg, rgba(15, 23, 42, .92), rgba(30, 41, 59, .86), rgba(15, 23, 42, .92)) !important;
}

html.dark .post-show-shell .ps-post-body :where(h2, h3, h4, h5, h6, .ce-header),
body.dark .post-show-shell .ps-post-body :where(h2, h3, h4, h5, h6, .ce-header),
.dark .post-show-shell .ps-post-body :where(h2, h3, h4, h5, h6, .ce-header),
[data-theme="dark"] .post-show-shell .ps-post-body :where(h2, h3, h4, h5, h6, .ce-header) {
  color: #f8fafc !important;
}

html.dark .post-show-shell .ps-post-body :where(.image-tool__image, .embed-tool, .ps-full-media, .ps-full-gallery, .tc-wrap, .ps-table-wrap, .ce-table, blockquote, .cdx-quote, .cdx-warning, .link-tool, .attaches-tool),
body.dark .post-show-shell .ps-post-body :where(.image-tool__image, .embed-tool, .ps-full-media, .ps-full-gallery, .tc-wrap, .ps-table-wrap, .ce-table, blockquote, .cdx-quote, .cdx-warning, .link-tool, .attaches-tool),
.dark .post-show-shell .ps-post-body :where(.image-tool__image, .embed-tool, .ps-full-media, .ps-full-gallery, .tc-wrap, .ps-table-wrap, .ce-table, blockquote, .cdx-quote, .cdx-warning, .link-tool, .attaches-tool),
[data-theme="dark"] .post-show-shell .ps-post-body :where(.image-tool__image, .embed-tool, .ps-full-media, .ps-full-gallery, .tc-wrap, .ps-table-wrap, .ce-table, blockquote, .cdx-quote, .cdx-warning, .link-tool, .attaches-tool) {
  background: #111827 !important;
  border-color: rgba(255,255,255,.10) !important;
  color: #e5e7eb !important;
}

html.dark .post-show-shell .ps-post-body :where(th, td),
body.dark .post-show-shell .ps-post-body :where(th, td),
.dark .post-show-shell .ps-post-body :where(th, td),
[data-theme="dark"] .post-show-shell .ps-post-body :where(th, td) {
  border-color: rgba(255,255,255,.10) !important;
}

html.dark .post-show-shell .ps-post-body code,
body.dark .post-show-shell .ps-post-body code,
.dark .post-show-shell .ps-post-body code,
[data-theme="dark"] .post-show-shell .ps-post-body code {
  background: rgba(255,255,255,.08) !important;
  color: #f8fafc !important;
}
</style>
@endpush


@push('head')
<style>
/* =========================================================
   FINAL FIX: 3 nokta menüsü belirgin, gölgesiz ve temiz hover
   ========================================================= */
.post-show-shell .ps-menu {
  z-index: 120 !important;
  overflow: visible !important;
}

.post-show-shell .ps-menu-panel,
.post-show-shell .ps-comment-more-menu {
  z-index: 160 !important;
  min-width: 168px !important;
  padding: 7px !important;
  border-radius: 12px !important;
  background: #ffffff !important;
  border: 1px solid #cfd8e3 !important;
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
}

.post-show-shell .ps-menu-item,
.post-show-shell .ps-comment-more-item {
  min-height: 38px !important;
  width: 100% !important;
  padding: 9px 11px !important;
  border-radius: 9px !important;
  background: transparent !important;
  border: 1px solid transparent !important;
  color: #1f2937 !important;
  font-family: Roboto, Arial, sans-serif !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  line-height: 1.2 !important;
  text-decoration: none !important;
  display: flex !important;
  align-items: center !important;
  justify-content: flex-start !important;
  gap: 9px !important;
  cursor: pointer !important;
  transition: background-color .14s ease, color .14s ease, border-color .14s ease !important;
}

.post-show-shell .ps-menu-item svg,
.post-show-shell .ps-comment-more-item svg {
  width: 16px !important;
  height: 16px !important;
  flex: 0 0 16px !important;
  color: currentColor !important;
  stroke: currentColor !important;
}

.post-show-shell .ps-menu-item:hover,
.post-show-shell .ps-menu-item:focus-visible,
.post-show-shell .ps-comment-more-item:hover,
.post-show-shell .ps-comment-more-item:focus-visible {
  background: #eef4ff !important;
  border-color: #dbeafe !important;
  color: #2563eb !important;
  outline: none !important;
}

.post-show-shell .ps-menu-panel form .ps-menu-item,
.post-show-shell .ps-comment-more-item--danger {
  color: #dc2626 !important;
  font-weight: 700 !important;
}

.post-show-shell .ps-menu-panel form .ps-menu-item:hover,
.post-show-shell .ps-menu-panel form .ps-menu-item:focus-visible,
.post-show-shell .ps-comment-more-item--danger:hover,
.post-show-shell .ps-comment-more-item--danger:focus-visible {
  background: #fef2f2 !important;
  border-color: #fecaca !important;
  color: #b91c1c !important;
  outline: none !important;
}

.post-show-shell .ps-menu-trigger {
  color: #475569 !important;
  background: transparent !important;
  box-shadow: none !important;
  filter: none !important;
}

.post-show-shell .ps-menu-trigger:hover,
.post-show-shell .ps-menu-trigger:focus-visible,
.post-show-shell .ps-menu.is-open .ps-menu-trigger {
  color: #2563eb !important;
  background: #eef4ff !important;
  outline: none !important;
}

html.dark .post-show-shell .ps-menu-panel,
body.dark .post-show-shell .ps-menu-panel,
.dark .post-show-shell .ps-menu-panel,
[data-theme="dark"] .post-show-shell .ps-menu-panel,
html.dark .post-show-shell .ps-comment-more-menu,
body.dark .post-show-shell .ps-comment-more-menu,
.dark .post-show-shell .ps-comment-more-menu,
[data-theme="dark"] .post-show-shell .ps-comment-more-menu {
  background: #111827 !important;
  border-color: rgba(148, 163, 184, .28) !important;
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
}

html.dark .post-show-shell .ps-menu-item,
body.dark .post-show-shell .ps-menu-item,
.dark .post-show-shell .ps-menu-item,
[data-theme="dark"] .post-show-shell .ps-menu-item,
html.dark .post-show-shell .ps-comment-more-item,
body.dark .post-show-shell .ps-comment-more-item,
.dark .post-show-shell .ps-comment-more-item,
[data-theme="dark"] .post-show-shell .ps-comment-more-item {
  color: #e5e7eb !important;
}

html.dark .post-show-shell .ps-menu-item:hover,
html.dark .post-show-shell .ps-menu-item:focus-visible,
body.dark .post-show-shell .ps-menu-item:hover,
body.dark .post-show-shell .ps-menu-item:focus-visible,
.dark .post-show-shell .ps-menu-item:hover,
.dark .post-show-shell .ps-menu-item:focus-visible,
[data-theme="dark"] .post-show-shell .ps-menu-item:hover,
[data-theme="dark"] .post-show-shell .ps-menu-item:focus-visible,
html.dark .post-show-shell .ps-comment-more-item:hover,
html.dark .post-show-shell .ps-comment-more-item:focus-visible,
body.dark .post-show-shell .ps-comment-more-item:hover,
body.dark .post-show-shell .ps-comment-more-item:focus-visible,
.dark .post-show-shell .ps-comment-more-item:hover,
.dark .post-show-shell .ps-comment-more-item:focus-visible,
[data-theme="dark"] .post-show-shell .ps-comment-more-item:hover,
[data-theme="dark"] .post-show-shell .ps-comment-more-item:focus-visible {
  background: rgba(37, 99, 235, .18) !important;
  border-color: rgba(96, 165, 250, .30) !important;
  color: #93c5fd !important;
}

html.dark .post-show-shell .ps-menu-panel form .ps-menu-item,
body.dark .post-show-shell .ps-menu-panel form .ps-menu-item,
.dark .post-show-shell .ps-menu-panel form .ps-menu-item,
[data-theme="dark"] .post-show-shell .ps-menu-panel form .ps-menu-item,
html.dark .post-show-shell .ps-comment-more-item--danger,
body.dark .post-show-shell .ps-comment-more-item--danger,
.dark .post-show-shell .ps-comment-more-item--danger,
[data-theme="dark"] .post-show-shell .ps-comment-more-item--danger {
  color: #fca5a5 !important;
}

html.dark .post-show-shell .ps-menu-panel form .ps-menu-item:hover,
html.dark .post-show-shell .ps-menu-panel form .ps-menu-item:focus-visible,
body.dark .post-show-shell .ps-menu-panel form .ps-menu-item:hover,
body.dark .post-show-shell .ps-menu-panel form .ps-menu-item:focus-visible,
.dark .post-show-shell .ps-menu-panel form .ps-menu-item:hover,
.dark .post-show-shell .ps-menu-panel form .ps-menu-item:focus-visible,
[data-theme="dark"] .post-show-shell .ps-menu-panel form .ps-menu-item:hover,
[data-theme="dark"] .post-show-shell .ps-menu-panel form .ps-menu-item:focus-visible,
html.dark .post-show-shell .ps-comment-more-item--danger:hover,
html.dark .post-show-shell .ps-comment-more-item--danger:focus-visible,
body.dark .post-show-shell .ps-comment-more-item--danger:hover,
body.dark .post-show-shell .ps-comment-more-item--danger:focus-visible,
.dark .post-show-shell .ps-comment-more-item--danger:hover,
.dark .post-show-shell .ps-comment-more-item--danger:focus-visible,
[data-theme="dark"] .post-show-shell .ps-comment-more-item--danger:hover,
[data-theme="dark"] .post-show-shell .ps-comment-more-item--danger:focus-visible {
  background: rgba(220, 38, 38, .16) !important;
  border-color: rgba(248, 113, 113, .32) !important;
  color: #fecaca !important;
}
</style>
@endpush

@push('scripts')
<script>
  (function () {
    const shell = document.querySelector('.post-show-shell');
    if (!shell) return;

    const fitTextarea = function (textarea) {
      if (!textarea) return;
      textarea.style.height = 'auto';
      textarea.style.height = Math.max(textarea.scrollHeight, 36) + 'px';
      textarea.style.overflowY = 'hidden';
    };

    const updateCounter = function (textarea) {
      const composer = textarea.closest('[data-og-comment-composer]');
      const counter = composer ? composer.querySelector('[data-comment-counter]') : null;
      if (!counter) return;
      const max = Number(textarea.getAttribute('maxlength') || 500);
      const len = textarea.value.length;
      counter.textContent = len + '/' + max;
      counter.classList.toggle('is-warning', len >= Math.floor(max * 0.8) && len < max);
      counter.classList.toggle('is-danger', len >= max);
    };

    const bindTextarea = function (textarea) {
      if (!textarea || textarea.dataset.ogModernBound === '1') return;
      textarea.dataset.ogModernBound = '1';
      textarea.setAttribute('rows', '1');
      ['input', 'change', 'focus'].forEach(function (eventName) {
        textarea.addEventListener(eventName, function () {
          fitTextarea(textarea);
          updateCounter(textarea);
        });
      });
      textarea.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
          const form = textarea.closest('form');
          if (form) form.requestSubmit ? form.requestSubmit() : form.submit();
        }
      });
      fitTextarea(textarea);
      updateCounter(textarea);
    };

    const bindAllTextareas = function (root) {
      (root || document).querySelectorAll('.post-show-shell .ps-comment-textarea, .post-show-shell .ps-comment-mini-textarea').forEach(bindTextarea);
    };

    bindAllTextareas(document);

    document.addEventListener('input', function (event) {
      if (event.target.matches('.post-show-shell .ps-comment-textarea, .post-show-shell .ps-comment-mini-textarea')) {
        fitTextarea(event.target);
        updateCounter(event.target);
      }
    }, true);

    document.addEventListener('click', function (event) {
      const suggestion = event.target.closest('[data-comment-suggestion]');
      if (suggestion) {
        event.preventDefault();
        const form = suggestion.closest('form');
        const textarea = form ? form.querySelector('textarea[name="content"]') : null;
        if (!textarea) return;
        const text = suggestion.getAttribute('data-comment-suggestion') || suggestion.textContent.trim();
        const current = textarea.value.trim();
        textarea.value = current ? current + ' ' + text : text;
        textarea.focus();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        return;
      }

      const clear = event.target.closest('[data-comment-clear]');
      if (clear) {
        event.preventDefault();
        const form = clear.closest('form');
        const textarea = form ? form.querySelector('textarea[name="content"]') : null;
        const file = form ? form.querySelector('[data-comment-file-input]') : null;
        const preview = form ? form.querySelector('.ps-comment-image-preview') : null;
        if (textarea) {
          textarea.value = '';
          textarea.focus();
          textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (file) file.value = '';
        if (preview) {
          preview.hidden = true;
          preview.innerHTML = '';
        }
        return;
      }

      const fileButton = event.target.closest('[data-comment-file-button]');
      if (fileButton) {
        event.preventDefault();
        const target = fileButton.getAttribute('data-comment-file-target');
        const input = target ? document.querySelector(target) : null;
        if (input) input.click();
      }
    }, true);

    document.addEventListener('change', function (event) {
      const input = event.target.closest('[data-comment-file-input]');
      if (!input) return;
      const target = input.getAttribute('data-comment-preview-target');
      const preview = target ? document.querySelector(target) : null;
      if (!preview) return;
      const file = input.files && input.files[0];
      if (!file) {
        preview.hidden = true;
        preview.innerHTML = '';
        return;
      }
      const url = URL.createObjectURL(file);
      preview.innerHTML = '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><img src="' + url + '" alt="Önizleme"><span>' + file.name.replace(/[<>]/g, '') + '</span></div>';
      preview.hidden = false;
    }, true);

    const closeCommentMenus = function (except) {
      document.querySelectorAll('.post-show-shell [data-comment-more].is-open').forEach(function (wrap) {
        if (except && wrap === except) return;
        wrap.classList.remove('is-open');
        const menu = wrap.querySelector('[data-comment-more-menu]');
        const trigger = wrap.querySelector('[data-comment-more-trigger]');
        if (menu) menu.hidden = true;
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      });
    };

    document.addEventListener('click', function (event) {
      const trigger = event.target.closest('.post-show-shell [data-comment-more-trigger]');
      if (trigger) {
        event.preventDefault();
        event.stopPropagation();
        const wrap = trigger.closest('[data-comment-more]');
        const menu = wrap ? wrap.querySelector('[data-comment-more-menu]') : null;
        if (!wrap || !menu) return;
        const open = !wrap.classList.contains('is-open');
        closeCommentMenus(wrap);
        wrap.classList.toggle('is-open', open);
        menu.hidden = !open;
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        return;
      }

      if (!event.target.closest('.post-show-shell [data-comment-more]')) {
        closeCommentMenus();
      }
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeCommentMenus();
    });

    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) bindAllTextareas(node);
        });
      });
    });
    observer.observe(shell, { childList: true, subtree: true });
  })();
</script>
@endpush

@section('content')
<div class="ps-layout post-show-shell is-loaded">


  <main class="ps-main">
    @include('partials.ads.slot', [
      'slotKey' => 'ads_feed_inline',
      'wrapperClass' => 'alma-ad-slot--cover',
    ])

    <article class="ps-post-card" itemscope itemtype="https://schema.org/NewsArticle">
      <meta itemprop="url" content="{{ e($postUrl) }}">
      <meta itemprop="mainEntityOfPage" content="{{ e($postUrl) }}">
      <meta itemprop="headline" content="{{ e(\Illuminate\Support\Str::limit($seoTitleBase, 110, '')) }}">
      <meta itemprop="description" content="{{ e($description) }}">
      <meta itemprop="datePublished" content="{{ e($seoPublishedIso) }}">
      <meta itemprop="dateModified" content="{{ e($seoModifiedIso) }}">
      @if($seoWordCount)
        <meta itemprop="wordCount" content="{{ $seoWordCount }}">
      @endif
      @if($seoReadingTimeMinutes)
        <meta itemprop="timeRequired" content="PT{{ $seoReadingTimeMinutes }}M">
      @endif
      <span itemprop="author" itemscope itemtype="https://schema.org/Person" hidden>
        <meta itemprop="name" content="{{ e($authorName ?: 'Ografi Editör') }}">
        @if($authorUrl)
          <meta itemprop="url" content="{{ e($seoNormalizePublicUrl($authorUrl)) }}">
        @endif
      </span>
      <span itemprop="publisher" itemscope itemtype="https://schema.org/Organization" hidden>
        <meta itemprop="name" content="{{ e($siteName !== '' ? $siteName : 'Ografi') }}">
        <meta itemprop="url" content="{{ e($seoSiteUrl) }}">
        @if($seoLogoUrl)
          <span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
            <meta itemprop="url" content="{{ e($seoLogoUrl) }}">
            <meta itemprop="width" content="96">
            <meta itemprop="height" content="96">
          </span>
        @endif
      </span>
      @if($seoPrimaryImage)
        <span itemprop="image" itemscope itemtype="https://schema.org/ImageObject" hidden>
          <meta itemprop="url" content="{{ e($seoPrimaryImage) }}">
          <meta itemprop="width" content="{{ $seoPrimaryImageWidth }}">
          <meta itemprop="height" content="{{ $seoPrimaryImageHeight }}">
        </span>
      @endif
      <div class="ps-wave-loader" data-ps-wave-loader aria-hidden="true">
        <div class="ps-wave-loader-inner">
          <span class="ps-wave-avatar"></span>
          <span class="ps-wave-copy">
            <span class="ps-wave-line ps-wave-line--wide"></span>
            <span class="ps-wave-line ps-wave-line--short"></span>
          </span>
        </div>
        <div class="ps-wave-media"></div>
      </div>
      <div class="ps-post-card-inner">
        <div class="ps-post-author-row">
          <div class="post-author-mini">
            <div class="post-author-avatar-wrap">
              <span class="ps-hover-zone ps-hover-zone--avatar" tabindex="0">
                @if($authorAvatar)
                  <img
                    src="{{ $authorAvatar }}"
                    alt="{{ $authorName }}"
                    class="post-author-avatar"
                  >
                @else
                  <span class="post-author-avatar post-author-avatar-empty">
                    {{ $authorInitials }}
                  </span>
                @endif

                <span class="ps-hover-card ps-hover-card--user" role="tooltip">
                  <span class="ps-hover-card-cover">
                    @if($authorCover)
                      <img src="{{ $authorCover }}" alt="{{ $authorName }} kapak görseli">
                    @endif
                  </span>
                  <span class="ps-hover-card-main">
                    <span class="ps-hover-card-avatar">
                      @if($authorAvatar)
                        <img src="{{ $authorAvatar }}" alt="{{ $authorName }}">
                      @else
                        {{ $authorInitials }}
                      @endif
                    </span>
                    <span class="ps-hover-card-content">
                      <span class="ps-hover-card-title">{{ $authorName }}</span>
                      @if($authorUsername !== '')
                        <span class="ps-hover-card-subtitle">{{ '@' . $authorUsername }}</span>
                      @endif
                    </span>
                  </span>
                  @if($authorBio !== '')
                    <span class="ps-hover-card-description">{{ $authorBio }}</span>
                  @endif
                  <span class="ps-hover-card-actions">
                    @if($postShowCanFollowAuthor)
                      <form method="POST" action="{{ $postShowFollowAction }}" class="ps-hover-card-follow-form">
                        @csrf
                        <button
                          type="submit"
                          class="ps-hover-card-follow"
                          @if($postShowIsFollowingAuthor) disabled aria-disabled="true" @endif
                        >
                          {{ $postShowFollowButtonLabel }}
                        </button>
                      </form>
                    @elseif(!$postShowViewer && $author)
                      <a href="{{ $postShowLoginUrl }}" class="ps-hover-card-follow ps-hover-card-follow--login">Takip et</a>
                    @endif
                    @if($authorUrl)
                      <a href="{{ $authorUrl }}" class="ps-hover-card-link">Profili görüntüle</a>
                    @endif
                  </span>
                </span>
              </span>

              @if($hasCategory)
                <span class="ps-hover-zone ps-hover-zone--category-badge" tabindex="0">
                  @if($categoryUrl)
                    <a href="{{ $categoryUrl }}" class="post-author-badge" aria-label="{{ $categoryName }}">
                      @if($categoryAvatar)
                        <img src="{{ $categoryAvatar }}" alt="{{ $categoryName }}">
                      @else
                        {{ $categoryBadgeText }}
                      @endif
                    </a>
                  @else
                    <span class="post-author-badge" aria-label="{{ $categoryName }}">
                      @if($categoryAvatar)
                        <img src="{{ $categoryAvatar }}" alt="{{ $categoryName }}">
                      @else
                        {{ $categoryBadgeText }}
                      @endif
                    </span>
                  @endif

                  <span class="ps-hover-card ps-hover-card--category" role="tooltip">
                    <span class="ps-hover-card-cover ps-hover-card-cover--category">
                      @if($categoryCover)
                        <img src="{{ $categoryCover }}" alt="{{ $categoryName }} kapak görseli">
                      @endif
                    </span>
                    <span class="ps-hover-card-main">
                      <span class="ps-hover-card-avatar ps-hover-card-avatar--category">
                        @if($categoryAvatar)
                          <img src="{{ $categoryAvatar }}" alt="{{ $categoryName }}">
                        @else
                          {{ $categoryBadgeText }}
                        @endif
                      </span>
                      <span class="ps-hover-card-content">
                        <span class="ps-hover-card-title">{{ $categoryName }}</span>
                        <span class="ps-hover-card-subtitle">Kategori</span>
                      </span>
                    </span>
                    @if($categoryDescription !== '')
                      <span class="ps-hover-card-description">{{ $categoryDescription }}</span>
                    @endif
                    @if($categoryUrl)
                      <span class="ps-hover-card-link">Kategoriyi görüntüle</span>
                    @endif
                  </span>
                </span>
              @endif
            </div>

            <div class="post-author-info">
              <span class="ps-hover-zone ps-hover-zone--inline ps-hover-zone--author-name" tabindex="0">
                @if($authorUrl)
                  <a href="{{ $authorUrl }}" class="post-author-name">{{ $authorName }}</a>
                @else
                  <span class="post-author-name">{{ $authorName }}</span>
                @endif

                <span class="ps-hover-card ps-hover-card--user ps-hover-card--inline" role="tooltip">
                  <span class="ps-hover-card-cover">
                    @if($authorCover)
                      <img src="{{ $authorCover }}" alt="{{ $authorName }} kapak görseli">
                    @endif
                  </span>
                  <span class="ps-hover-card-main">
                    <span class="ps-hover-card-avatar">
                      @if($authorAvatar)
                        <img src="{{ $authorAvatar }}" alt="{{ $authorName }}">
                      @else
                        {{ $authorInitials }}
                      @endif
                    </span>
                    <span class="ps-hover-card-content">
                      <span class="ps-hover-card-title">{{ $authorName }}</span>
                      @if($authorUsername !== '')
                        <span class="ps-hover-card-subtitle">{{ '@' . $authorUsername }}</span>
                      @endif
                    </span>
                  </span>
                  @if($authorBio !== '')
                    <span class="ps-hover-card-description">{{ $authorBio }}</span>
                  @endif
                  <span class="ps-hover-card-actions">
                    @if($postShowCanFollowAuthor)
                      <form method="POST" action="{{ $postShowFollowAction }}" class="ps-hover-card-follow-form">
                        @csrf
                        <button
                          type="submit"
                          class="ps-hover-card-follow"
                          @if($postShowIsFollowingAuthor) disabled aria-disabled="true" @endif
                        >
                          {{ $postShowFollowButtonLabel }}
                        </button>
                      </form>
                    @elseif(!$postShowViewer && $author)
                      <a href="{{ $postShowLoginUrl }}" class="ps-hover-card-follow ps-hover-card-follow--login">Takip et</a>
                    @endif
                    @if($authorUrl)
                      <a href="{{ $authorUrl }}" class="ps-hover-card-link">Profili görüntüle</a>
                    @endif
                  </span>
                </span>
              </span>

              <div class="post-author-meta">
                @if($hasCategory)
                  <span class="ps-hover-zone ps-hover-zone--inline ps-hover-zone--category-name" tabindex="0">
                    @if($categoryUrl)
                      <a href="{{ $categoryUrl }}" class="post-author-category">{{ $categoryName }}</a>
                    @else
                      <span class="post-author-category">{{ $categoryName }}</span>
                    @endif

                    <span class="ps-hover-card ps-hover-card--category ps-hover-card--inline" role="tooltip">
                      <span class="ps-hover-card-cover ps-hover-card-cover--category">
                        @if($categoryCover)
                          <img src="{{ $categoryCover }}" alt="{{ $categoryName }} kapak görseli">
                        @endif
                      </span>
                      <span class="ps-hover-card-main">
                        <span class="ps-hover-card-avatar ps-hover-card-avatar--category">
                          @if($categoryAvatar)
                            <img src="{{ $categoryAvatar }}" alt="{{ $categoryName }}">
                          @else
                            {{ $categoryBadgeText }}
                          @endif
                        </span>
                        <span class="ps-hover-card-content">
                          <span class="ps-hover-card-title">{{ $categoryName }}</span>
                          <span class="ps-hover-card-subtitle">Kategori</span>
                        </span>
                      </span>
                      @if($categoryDescription !== '')
                        <span class="ps-hover-card-description">{{ $categoryDescription }}</span>
                      @endif
                      @if($categoryUrl)
                        <span class="ps-hover-card-link">Kategoriyi görüntüle</span>
                      @endif
                    </span>
                  </span>
                @endif

                <span class="post-author-date">{{ $publishedLabel }}</span>

                @if($isPostEdited)
                  <span class="ps-post-edited-wrap post-author-edited-wrap" data-post-edit-details>
                    <button
                      type="button"
                      class="ps-post-edited-button post-author-edited-button"
                      data-post-edit-details-trigger
                      aria-expanded="false"
                      aria-controls="{{ $postEditedToggleId }}"
                      aria-label="Düzenleme ayrıntıları"
                      title="Düzenlendi"
                    >
                      <svg class="ps-post-edited-icon post-author-edited-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"/>
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                      </svg>
                    </button>
                    <span id="{{ $postEditedToggleId }}" class="ps-post-edited-popover" data-post-edit-details-panel>
                      <span class="ps-post-edited-title">Düzenleme ayrıntıları</span>
                      @if($postEditedAtLabel)
                        <span class="ps-post-edited-detail">Düzenlendi: {{ $postEditedAtLabel }}</span>
                      @else
                        <span class="ps-post-edited-detail">Bu gönderi düzenlendi.</span>
                      @endif
                      @if($postEditedReason !== '')
                        <span class="ps-post-edited-detail">Neden: {{ $postEditedReason }}</span>
                      @endif
                    </span>
                  </span>
                @endif
              </div>
            </div>
          </div>
          @if($postShowCanOpenMenu)
            <div class="ps-menu" data-ps-menu>
              <button type="button" class="ps-menu-trigger" data-ps-menu-trigger aria-label="Diğer işlemler">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M6 12a2 2 0 1 1-4 0a2 2 0 0 1 4 0m8 0a2 2 0 1 1-4 0a2 2 0 0 1 4 0m8 0a2 2 0 1 1-4 0a2 2 0 0 1 4 0"/></svg>
              </button>
              <div class="ps-menu-panel shadcn-menu shadcn-menu--compact" style="width: 152px !important; min-width: 152px !important; max-width: min(152px, calc(100vw - 24px)) !important; box-sizing: border-box !important; padding: 8px !important; overflow: hidden !important; border: 1px solid #e4e4e7 !important; border-radius: 16px !important; background: #ffffff !important; color: #18181b !important; box-shadow: 0 1px 2px rgba(0,0,0,.05), 0 8px 24px rgba(15,23,42,.08) !important; filter: none !important;">
                @if($postShowReportUrl)
                  <a href="{{ $postShowReportUrl }}" class="ps-menu-item"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 15s1-1 4-1s5 2 8 2s4-1 4-1V3s-1 1-4 1s-5-2-8-2s-4 1-4 1zM4 22v-7"/></svg>Bildir</a>
                @endif
                @if($postShowIsOwnPost)
                  @if($postShowEditUrl)
                    <a href="{{ $postShowEditUrl }}" class="ps-menu-item"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1l1-4z"/></svg>Düzenle</a>
                  @endif
                  @if($postShowDeleteAction)
                    <form method="POST" action="{{ $postShowDeleteAction }}" onsubmit="return confirm('Bu gönderi silinsin mi?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="ps-menu-item"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-6 5v6m4-6v6"/></svg>Sil</button>
                    </form>
                  @endif
                @endif
              </div>
            </div>
          @endif
        </div>

        <h1 class="ps-post-title" itemprop="headline">{{ $post->title ?: $seoTitleBase }}</h1>

        <div class="{{ $renderNsfwBlur ? 'ps-nsfw-locker ps-nsfw-locked' : '' }}" data-nsfw-locker>
          @if($renderNsfwBlur)
            <div class="ps-nsfw-overlay" data-nsfw-overlay>
              <div class="ps-nsfw-card">
                <span class="ps-nsfw-label">NSFW İçerik</span>
                <p class="ps-nsfw-text">Bu gönderi yetişkinlere yönelik hassas medya içeriyor.</p>
                <label class="ps-nsfw-check"><input type="checkbox" data-nsfw-checkbox> <span>18 yaşından büyük olduğumu ve içeriği görüntülemek istediğimi kabul ediyorum.</span></label>
                <button type="button" class="ps-nsfw-button" data-nsfw-reveal disabled>İçeriği Göster</button>
              </div>
            </div>
          @endif

          @if($featuredImage)
            <div class="ps-post-image">
              <img alt="{{ $post->title }}" src="{{ $featuredImage }}" width="{{ $featuredImageWidth }}" height="{{ $featuredImageHeight }}" loading="eager" fetchpriority="high" decoding="async">
            </div>
          @endif

          <div class="ps-post-body" itemprop="articleBody">
            {!! $postShowFullContentHtml !!}
          </div>

          @if($hasPostShowSourcePreview)
            <a
              class="ps-source-link"
              href="{{ $linkPreviewUrl }}"
              target="_blank"
              rel="nofollow noopener noreferrer"
              aria-label="Kaynağı aç: {{ $postShowSourceDisplayName }}"
            >
              <span class="ps-source-copy">
                <span class="ps-source-label">Kaynak</span>
                <span class="ps-source-domain-row">
                  @if($linkPreviewFavicon !== '')
                    <img
                      class="ps-source-favicon"
                      src="{{ $linkPreviewFavicon }}"
                      alt=""
                      loading="lazy"
                      decoding="async"
                      referrerpolicy="no-referrer"
                      onerror="this.style.display='none'"
                    >
                  @endif
                  <span class="ps-source-domain">{{ $postShowSourceDisplayName }}</span>
                </span>
              </span>
              <span class="ps-source-icon" aria-hidden="true">
                <iconify-icon icon="lucide:arrow-up-right"></iconify-icon>
              </span>
            </a>
          @endif
        </div>

        @if($showTags->isNotEmpty())
          <div class="ps-tags-row">
            @foreach($showTags as $tag)
              @if($postShowTagRouteAvailable)
                <a class="ps-tag" href="{{ route('blog.index', ['tag' => $tag->slug]) }}">#{{ $tag->name }}</a>
              @else
                <span class="ps-tag">#{{ $tag->name }}</span>
              @endif
            @endforeach
          </div>
        @endif

        <div class="ps-actions-bar">
          <div class="ps-reaction-row">
            @if($showReactionItems->isNotEmpty())
              @foreach($showReactionItems as $reaction)
                @php
                  $reactionIcon = $reaction['gif_url'] ?? $reaction['icon'] ?? $reaction['emoji'] ?? $reaction['short_code'] ?? null;
                  $reactionLabel = (string) ($reaction['label'] ?? 'Tepki');
                  $reactionCount = (int) ($reaction['count'] ?? 0);
                  $reactionShortCode = trim((string) ($reaction['short_code'] ?? ''));
                  $reactionTypeId = $reaction['type_id'] ?? null;
                @endphp
                <button type="button" class="ps-reaction-pill" aria-label="{{ $reactionLabel }}">
                  <span>{!! $reactionIcon ? $renderShowReactionIcon($reactionIcon, $reactionLabel) : '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 21a9 9 0 1 0 0-18a9 9 0 0 0 0 18M8.75 10h.01m6.49 0h.01M8.5 14.25a4.5 4.5 0 0 0 7 0"/></svg>' !!}</span>
                  <span>{{ $reactionCount }}</span>
                </button>
              @endforeach

              @if($showReactionOverflowCount > 0)
                <span class="ps-reaction-more-wrap" data-reaction-more>
                  <button
                    type="button"
                    class="ps-reaction-pill ps-reaction-more-trigger"
                    data-reaction-more-trigger
                    aria-expanded="false"
                    aria-controls="{{ $showReactionMoreMenuId }}"
                  >
                    <span>Daha fazla {{ $showReactionOverflowCount }}</span>
                  </button>

                  <span id="{{ $showReactionMoreMenuId }}" class="ps-reaction-more-menu" data-reaction-more-menu hidden>
                    <span class="ps-reaction-more-title">Tepkiler</span>

                    @foreach($showReactionHiddenItems->chunk($showReactionPageSize)->values() as $pageIndex => $reactionPage)
                      <span class="ps-reaction-more-page" data-reaction-more-page="{{ $pageIndex }}" @if($pageIndex !== 0) hidden @endif>
                        @foreach($reactionPage as $reaction)
                          @php
                            $reactionIcon = $reaction['gif_url'] ?? $reaction['icon'] ?? $reaction['emoji'] ?? $reaction['short_code'] ?? null;
                            $reactionLabel = (string) ($reaction['label'] ?? 'Tepki');
                            $reactionCount = (int) ($reaction['count'] ?? 0);
                          @endphp

                          @if($reactionCount > 0)
                            <span class="ps-reaction-more-item" title="{{ $reactionLabel }}" aria-label="{{ $reactionLabel }}">
                              {!! $reactionIcon ? $renderShowReactionIcon($reactionIcon, $reactionLabel) : '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 21a9 9 0 1 0 0-18a9 9 0 0 0 0 18M8.75 10h.01m6.49 0h.01M8.5 14.25a4.5 4.5 0 0 0 7 0"/></svg>' !!}
                            </span>
                          @endif
                        @endforeach
                      </span>
                    @endforeach

                    @if($showReactionHiddenItems->chunk($showReactionPageSize)->count() > 1)
                      <span class="ps-reaction-more-footer">
                        <button type="button" class="ps-reaction-more-nav" data-reaction-more-prev disabled aria-label="Önceki tepkiler">‹</button>
                        <span class="ps-reaction-more-counter" data-reaction-more-counter>1 / {{ $showReactionHiddenItems->chunk($showReactionPageSize)->count() }}</span>
                        <button type="button" class="ps-reaction-more-nav" data-reaction-more-next aria-label="Sonraki tepkiler">›</button>
                      </span>
                    @endif
                  </span>
                </span>
              @endif
            @endif

            @if($showReactionTypes->isNotEmpty() && \Illuminate\Support\Facades\Route::has('blog.post.reaction'))
              <span class="ps-reaction-picker" data-post-reaction-picker>
                <button type="button" class="ps-reaction-pill ps-reaction-trigger" data-post-reaction-trigger aria-expanded="false" aria-controls="{{ $postReactionMenuId }}" aria-label="Tepki ekle">
                  <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.5 3v5M21 5.5h-5m4.86 4.94a9 9 0 1 1-7.3-7.3m1.62 12.04a4.5 4.5 0 0 1-6.36 0M9.75 9.75c0 .41-.17.75-.38.75S9 10.16 9 9.75S9.17 9 9.37 9s.38.34.38.75m5.25 0c0 .41-.17.75-.38.75s-.37-.34-.37-.75s.17-.75.37-.75s.38.34.38.75"/></svg>
                </button>
                <span id="{{ $postReactionMenuId }}" class="ps-reaction-menu" data-post-reaction-menu hidden>
                  <span class="ps-reaction-menu-title">Tepkiler</span>
                  @foreach($showReactionTypes as $reactionType)
                    @php
                      $reactionTypeId = data_get($reactionType, 'id');
                      $reactionTypeShortCode = trim((string) data_get($reactionType, 'short_code', ''));
                      $reactionTypeLabel = (string) (data_get($reactionType, 'label') ?: 'Tepki');
                      $reactionTypeIcon = data_get($reactionType, 'gif_url') ?: data_get($reactionType, 'emoji');
                    @endphp
                    @if($reactionTypeId || $reactionTypeShortCode !== '')
                      @auth
                        <form method="POST" action="{{ route('blog.post.reaction', $post) }}" class="ps-reaction-form">
                          @csrf
                          @if($reactionTypeId)
                            <input type="hidden" name="reaction_type_id" value="{{ $reactionTypeId }}">
                          @else
                            <input type="hidden" name="short_code" value="{{ $reactionTypeShortCode }}">
                          @endif
                          <button type="submit" class="ps-reaction-option" aria-label="{{ $reactionTypeLabel }}" title="{{ $reactionTypeLabel }}">
                            {!! $reactionTypeIcon ? $renderShowReactionIcon($reactionTypeIcon, $reactionTypeLabel) : '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 21a9 9 0 1 0 0-18a9 9 0 0 0 0 18M8.75 10h.01m6.49 0h.01M8.5 14.25a4.5 4.5 0 0 0 7 0"/></svg>' !!}
                          </button>
                        </form>
                      @else
                        <a href="{{ $postShowLoginUrl }}" class="ps-reaction-option ps-reaction-option--login" aria-label="{{ $reactionTypeLabel }} için giriş yap" title="Tepki vermek için giriş yap">
                          {!! $reactionTypeIcon ? $renderShowReactionIcon($reactionTypeIcon, $reactionTypeLabel) : '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 21a9 9 0 1 0 0-18a9 9 0 0 0 0 18M8.75 10h.01m6.49 0h.01M8.5 14.25a4.5 4.5 0 0 0 7 0"/></svg>' !!}
                        </a>
                      @endauth
                    @endif
                  @endforeach
                </span>
              </span>
            @endif
          </div>

          <div class="ps-action-row">
            <span class="ps-vote-cluster">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 19V5m-7 7l7-7l7 7"/></svg>
              @if($showVoteCountDisplay !== '0')
                <span class="ps-vote-count">{{ $showVoteCountDisplay }}</span>
              @endif
              <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14m7-7l-7 7l-7-7"/></svg>
            </span>

            <a class="ps-action-btn" href="#comments" aria-label="Yorumlar">
              <span class="ps-action-icon">
                <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="w-5 h-5 group-hover:text-primary text-foreground" aria-hidden="true">
                  <g fill="none" stroke="currentColor" stroke-linejoin="round">
                    <path stroke-width="1.5" d="M14.17 20.89c4.184-.277 7.516-3.657 7.79-7.9c.053-.83.053-1.69 0-2.52c-.274-4.242-3.606-7.62-7.79-7.899a33 33 0 0 0-4.34 0c-4.184.278-7.516 3.657-7.79 7.9a20 20 0 0 0 0 2.52c.1 1.545.783 2.976 1.588 4.184c.467.845.159 1.9-.328 2.823c-.35.665-.526.997-.385 1.237c.14.24.455.248 1.084.263c1.245.03 2.084-.322 2.75-.813c.377-.279.566-.418.696-.434s.387.09.899.3c.46.19.995.307 1.485.34c1.425.094 2.914.094 4.342 0Z"></path>
                    <path stroke-linecap="round" stroke-width="2" d="M11.995 12h.01m3.986 0H16m-8 0h.009"></path>
                  </g>
                </svg>
              </span>
              @if($commentsCount > 0)
                <span class="ps-action-count">{{ $commentsCount }}</span>
              @endif
            </a>

            @auth
              @if(\Illuminate\Support\Facades\Route::has('blog.post.bookmark'))
                <form method="POST" action="{{ route('blog.post.bookmark', $post) }}">
                  @csrf
                  <button type="submit" class="ps-action-btn ps-bookmark-btn {{ (bool) ($post->is_bookmarked ?? false) ? 'is-active' : '' }}" aria-label="Kaydet" aria-pressed="{{ (bool) ($post->is_bookmarked ?? false) ? 'true' : 'false' }}">
                    <span class="ps-action-icon">
                      <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="w-5 h-5 group-hover:text-primary" aria-hidden="true">
                        <g fill="none" stroke="currentColor" stroke-width="1.5">
                          <path class="ps-bookmark-shape" d="M21 16.09v-4.992c0-4.29 0-6.433-1.318-7.766C18.364 2 16.242 2 12 2S5.636 2 4.318 3.332S3 6.81 3 11.098v4.993c0 3.096 0 4.645.734 5.321c.35.323.792.526 1.263.58c.987.113 2.14-.907 4.445-2.946c1.02-.901 1.529-1.352 2.118-1.47c.29-.06.59-.06.88 0c.59.118 1.099.569 2.118 1.47c2.305 2.039 3.458 3.059 4.445 2.945c.47-.053.913-.256 1.263-.579c.734-.676.734-2.224.734-5.321Z"></path>
                          <path class="ps-bookmark-line" stroke-linecap="round" d="M15 6H9"></path>
                        </g>
                      </svg>
                    </span>
                    
                  </button>
                </form>
              @endif
            @else
              <a class="ps-action-btn ps-bookmark-btn" href="{{ \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#' }}" aria-label="Kaydet">
                <span class="ps-action-icon">
                  <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="w-5 h-5 group-hover:text-primary" aria-hidden="true">
                    <g fill="none" stroke="currentColor" stroke-width="1.5">
                      <path class="ps-bookmark-shape" d="M21 16.09v-4.992c0-4.29 0-6.433-1.318-7.766C18.364 2 16.242 2 12 2S5.636 2 4.318 3.332S3 6.81 3 11.098v4.993c0 3.096 0 4.645.734 5.321c.35.323.792.526 1.263.58c.987.113 2.14-.907 4.445-2.946c1.02-.901 1.529-1.352 2.118-1.47c.29-.06.59-.06.88 0c.59.118 1.099.569 2.118 1.47c2.305 2.039 3.458 3.059 4.445 2.945c.47-.053.913-.256 1.263-.579c.734-.676.734-2.224.734-5.321Z"></path>
                      <path class="ps-bookmark-line" stroke-linecap="round" d="M15 6H9"></path>
                    </g>
                  </svg>
                </span>
                
              </a>
            @endauth

            <button type="button" class="ps-action-btn ps-action-btn--share" data-copy-post-link data-url="{{ $postShareUrl }}" data-title="{{ e($postShareTitle) }}" aria-label="Paylaş">
              <span class="ps-action-icon">
                <svg viewBox="0 0 256 256" width="1.2em" height="1.2em" class="h-5 w-5 group-hover:text-primary text-foreground" aria-hidden="true">
                  <path fill="currentColor" d="m229.66 109.66l-48 48a8 8 0 0 1-11.32-11.32L204.69 112H128a88.1 88.1 0 0 0-88 88a8 8 0 0 1-16 0A104.11 104.11 0 0 1 128 96h76.69l-34.35-34.34a8 8 0 0 1 11.32-11.32l48 48a8 8 0 0 1 0 11.32"></path>
                </svg>
              </span>
            </button>
          </div>

          <button
            type="button"
            class="ps-view-count"
            data-show-stats-trigger
            aria-haspopup="dialog"
            aria-expanded="false"
            aria-controls="{{ $postShowStatsModalId }}"
            aria-label="İstatistikleri göster"
          >
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
            @if($showViewCountDisplay !== '0')
              <span>{{ $showViewCountDisplay }}</span>
            @endif
          </button>
        </div>

        <div
          id="{{ $postShowStatsModalId }}"
          class="ps-show-stats-modal"
          data-show-stats-modal
          hidden
          role="dialog"
          aria-modal="true"
          aria-hidden="true"
          aria-labelledby="{{ $postShowStatsModalId }}_title"
        >
          <div class="ps-show-stats-backdrop" data-show-stats-backdrop></div>
          <div class="ps-show-stats-panel" role="document">
            <div class="ps-show-stats-head">
              <strong id="{{ $postShowStatsModalId }}_title" class="ps-show-stats-title">{{ $showStatsTotalDisplay }} etkileşim</strong>
              <button type="button" class="ps-show-stats-close" data-show-stats-close aria-label="İstatistik penceresini kapat">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
              </button>
            </div>

            <div class="ps-show-stats-grid">
              <div class="ps-show-stats-item">
                <strong>{{ $showViewsFullDisplay }}</strong>
                <span>akıştaki izlenimler</span>
              </div>
              <div class="ps-show-stats-item">
                <strong>{{ $showSharesFullDisplay }}</strong>
                <span>paylaşımlar</span>
              </div>
              <div class="ps-show-stats-item">
                <strong>{{ $showReactionsFullDisplay }}</strong>
                <span>gönderilere verilen tepkiler</span>
              </div>
              <div class="ps-show-stats-item">
                <strong>{{ $showCommentsFullDisplay }}</strong>
                <span>yorumlar</span>
              </div>
              <div class="ps-show-stats-item">
                <strong>{{ $showBookmarksFullDisplay }}</strong>
                <span>yer işaretleri</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </article>



    <div class="ps-mobile-long-actions" data-mobile-long-actions aria-hidden="true">
      @if($showReactionTypes->isNotEmpty() && $showDefaultReactionType && \Illuminate\Support\Facades\Route::has('blog.post.reaction'))
        @php
          $mobileReactionTypeId = data_get($showDefaultReactionType, 'id');
          $mobileReactionShortCode = trim((string) data_get($showDefaultReactionType, 'short_code', ''));
        @endphp
        @auth
          <form method="POST" action="{{ route('blog.post.reaction', $post) }}" class="ps-mobile-long-action-form">
            @csrf
            @if($mobileReactionTypeId)
              <input type="hidden" name="reaction_type_id" value="{{ $mobileReactionTypeId }}">
            @elseif($mobileReactionShortCode !== '')
              <input type="hidden" name="short_code" value="{{ $mobileReactionShortCode }}">
            @endif
            <button type="submit" class="ps-mobile-long-action ps-mobile-long-action--like" aria-label="Tepki ver">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M19.5 5.7c-1.7-1.7-4.5-1.7-6.2 0L12 7l-1.3-1.3c-1.7-1.7-4.5-1.7-6.2 0s-1.7 4.5 0 6.2L12 19.4l7.5-7.5c1.7-1.7 1.7-4.5 0-6.2Z"/></svg>
              <span>{{ $showVoteCountDisplay }}</span>
            </button>
          </form>
        @else
          <a href="{{ \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#' }}" class="ps-mobile-long-action ps-mobile-long-action--like" aria-label="Tepki vermek için giriş yap">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M19.5 5.7c-1.7-1.7-4.5-1.7-6.2 0L12 7l-1.3-1.3c-1.7-1.7-4.5-1.7-6.2 0s-1.7 4.5 0 6.2L12 19.4l7.5-7.5c1.7-1.7 1.7-4.5 0-6.2Z"/></svg>
            <span>{{ $showVoteCountDisplay }}</span>
          </a>
        @endauth
      @else
        <span class="ps-mobile-long-action ps-mobile-long-action--like" aria-label="Tepki sayısı">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M19.5 5.7c-1.7-1.7-4.5-1.7-6.2 0L12 7l-1.3-1.3c-1.7-1.7-4.5-1.7-6.2 0s-1.7 4.5 0 6.2L12 19.4l7.5-7.5c1.7-1.7 1.7-4.5 0-6.2Z"/></svg>
          <span>{{ $showVoteCountDisplay }}</span>
        </span>
      @endif

      <a class="ps-mobile-long-action" href="#comments" aria-label="Yorumlar">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.7" d="M14.17 20.89c4.184-.277 7.516-3.657 7.79-7.9c.053-.83.053-1.69 0-2.52c-.274-4.242-3.606-7.62-7.79-7.899a33 33 0 0 0-4.34 0c-4.184.278-7.516 3.657-7.79 7.9a20 20 0 0 0 0 2.52c.1 1.545.783 2.976 1.588 4.184c.467.845.159 1.9-.328 2.823c-.35.665-.526.997-.385 1.237c.14.24.455.248 1.084.263c1.245.03 2.084-.322 2.75-.813c.377-.279.566-.418.696-.434s.387.09.899.3c.46.19.995.307 1.485.34c1.425.094 2.914.094 4.342 0Z"/><path fill="currentColor" d="M8 12a1 1 0 1 0 0-2a1 1 0 0 0 0 2m4 0a1 1 0 1 0 0-2a1 1 0 0 0 0 2m4 0a1 1 0 1 0 0-2a1 1 0 0 0 0 2"/></svg>
        <span>{{ $commentsCount }}</span>
      </a>

      <button type="button" class="ps-mobile-long-action ps-mobile-long-action--accessibility" data-mobile-accessibility-trigger aria-label="Erişilebilirlik menüsünü aç" aria-expanded="false" aria-controls="ps-mobile-accessibility-menu">
        <svg class="ps-accessibility-trigger-icon ps-accessibility-trigger-icon--person" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 15" aria-hidden="true" focusable="false">
          <path fill="currentColor" fill-rule="evenodd" d="M.877 7.5a6.623 6.623 0 1 1 13.246 0a6.623 6.623 0 0 1-13.246 0ZM7.5 1.827a5.673 5.673 0 1 0 0 11.346a5.673 5.673 0 0 0 0-11.346ZM7.125 9c-.055.127-.793 2.96-.793 2.96a.5.5 0 1 1-.966-.26s.88-2.827.88-3.43V6.801l-1.958-.525a.5.5 0 1 1 .258-.966s1.654.563 2.3.563h1.309c.645 0 2.298-.563 2.298-.563a.5.5 0 1 1 .26.966l-1.966.527V8.27c0 .603.88 3.427.88 3.427a.5.5 0 0 1-.966.259S7.92 9.127 7.869 9c-.05-.127-.219-.127-.219-.127h-.307s-.173 0-.218.127ZM7.5 5.12a1.125 1.125 0 1 0 0-2.25a1.125 1.125 0 0 0 0 2.25Z" clip-rule="evenodd"/>
        </svg>
        <svg class="ps-accessibility-trigger-icon ps-accessibility-trigger-icon--pause" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="currentColor" d="M8.25 5A1.25 1.25 0 0 1 9.5 6.25v11.5a1.25 1.25 0 1 1-2.5 0V6.25A1.25 1.25 0 0 1 8.25 5Zm7.5 0A1.25 1.25 0 0 1 17 6.25v11.5a1.25 1.25 0 1 1-2.5 0V6.25A1.25 1.25 0 0 1 15.75 5Z"/>
        </svg>
      </button>

      <button type="button" class="ps-mobile-long-action" data-copy-post-link data-url="{{ $postShareUrl }}" data-title="{{ e($postShareTitle) }}" aria-label="Paylaş">
        <svg viewBox="0 0 256 256" aria-hidden="true"><path fill="currentColor" d="m229.66 109.66l-48 48a8 8 0 0 1-11.32-11.32L204.69 112H128a88.1 88.1 0 0 0-88 88a8 8 0 0 1-16 0A104.11 104.11 0 0 1 128 96h76.69l-34.35-34.34a8 8 0 0 1 11.32-11.32l48 48a8 8 0 0 1 0 11.32"/></svg>
      </button>
    </div>

    <div class="ps-mobile-accessibility-backdrop" data-mobile-accessibility-backdrop hidden></div>
    <div class="ps-mobile-accessibility-menu" id="ps-mobile-accessibility-menu" data-mobile-accessibility-menu hidden aria-hidden="true">
      <button type="button" class="ps-mobile-accessibility-item" data-mobile-a11y-read>
        <span class="ps-mobile-accessibility-item-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 10v4h3l4 3.5v-11L7 10H4Zm10.5-.5a4.5 4.5 0 0 1 0 5m2.4-7.4a8 8 0 0 1 0 9.8"/></svg>
        </span>
        <span>Sesli oku</span>
      </button>
      <button type="button" class="ps-mobile-accessibility-item" data-mobile-a11y-font-plus>
        <span class="ps-mobile-accessibility-item-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 18 9.2 6h1.6L16 18m-9.8-3.2h7.6M18 7v6m-3-3h6"/></svg>
        </span>
        <span>Yazı büyüt</span>
      </button>
      <button type="button" class="ps-mobile-accessibility-item" data-mobile-a11y-font-minus>
        <span class="ps-mobile-accessibility-item-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 18 9.2 6h1.6L16 18m-9.8-3.2h7.6M18 10h5"/></svg>
        </span>
        <span>Yazı küçült</span>
      </button>
      <button type="button" class="ps-mobile-accessibility-item" data-mobile-a11y-color>
        <span class="ps-mobile-accessibility-item-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3.2c3.8 0 7 2.65 7 6.15 0 2.2-1.25 3.15-2.95 3.15h-1.2c-.9 0-1.5.6-1.5 1.45 0 .35.15.7.35 1 .3.45.45.9.45 1.35 0 1.15-1.05 2.1-2.45 2.1C7.85 18.4 5 15.55 5 11.8 5 7.05 8.15 3.2 12 3.2Z"/><path fill="currentColor" d="M8.2 10.1a1 1 0 1 0 0-2a1 1 0 0 0 0 2m3-2.2a1 1 0 1 0 0-2a1 1 0 0 0 0 2m3.4 2.2a1 1 0 1 0 0-2a1 1 0 0 0 0 2"/></svg>
        </span>
        <span>Rengi değiştir</span>
      </button>
    </div>



    <div class="ps-reading-player-wrap" data-reading-player hidden aria-hidden="true">
      <div class="ps-reading-player" role="region" aria-label="Sesli okuma oynatıcısı">
        <div class="ps-reading-progress-track" aria-hidden="true">
          <span class="ps-reading-progress-fill" data-reading-progress></span>
        </div>
        <div class="ps-reading-controls">
          <button type="button" class="ps-reading-control" data-reading-shuffle aria-label="Karıştır">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h2.7c2.2 0 3.2 1.2 4.5 3.1l1.7 2.5c1.3 1.9 2.3 3.1 4.5 3.1H20M17 4l3 3l-3 3M4 17h2.7c1.2 0 2-.35 2.7-1M17 14l3 3l-3 3"/></svg>
          </button>
          <button type="button" class="ps-reading-control" data-reading-restart aria-label="Başa sar">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 6.5a1 1 0 0 1 2 0v11a1 1 0 1 1-2 0v-11Zm3.65 5.68a1 1 0 0 1 0-1.36l7.1-6.7c.64-.6 1.7-.15 1.7.73v13.3c0 .88-1.06 1.33-1.7.73l-7.1-6.7Z"/></svg>
          </button>
          <button type="button" class="ps-reading-control ps-reading-control--play" data-reading-toggle aria-label="Duraklat">
            <svg class="ps-reading-icon-pause" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8 5a1.5 1.5 0 0 1 1.5 1.5v11a1.5 1.5 0 0 1-3 0v-11A1.5 1.5 0 0 1 8 5Zm8 0a1.5 1.5 0 0 1 1.5 1.5v11a1.5 1.5 0 0 1-3 0v-11A1.5 1.5 0 0 1 16 5Z"/></svg>
            <svg class="ps-reading-icon-play" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8 5.14v13.72c0 .82.9 1.32 1.6.88l10.8-6.86a1.05 1.05 0 0 0 0-1.76L9.6 4.26c-.7-.44-1.6.06-1.6.88Z"/></svg>
          </button>
          <button type="button" class="ps-reading-control" data-reading-skip aria-label="İleri al">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15 6.5a1 1 0 1 1 2 0v11a1 1 0 1 1-2 0v-11Zm-10.45-.65c0-.88 1.06-1.33 1.7-.73l7.1 6.7a1 1 0 0 1 0 1.36l-7.1 6.7c-.64.6-1.7.15-1.7-.73V5.85Z"/></svg>
          </button>
          <button type="button" class="ps-reading-control" data-reading-collapse aria-label="Küçült">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H5a1 1 0 0 0-1 1v3m12-4h3a1 1 0 0 1 1 1v3M8 20H5a1 1 0 0 1-1-1v-3m12 4h3a1 1 0 0 0 1-1v-3"/></svg>
          </button>
        </div>
      </div>
      <button type="button" class="ps-reading-more-close" data-reading-player-close aria-label="Sesli okumayı kapat">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M5 10a2 2 0 1 0 0 4a2 2 0 0 0 0-4Zm7 0a2 2 0 1 0 0 4a2 2 0 0 0 0-4Zm7 0a2 2 0 1 0 0 4a2 2 0 0 0 0-4Z"/></svg>
      </button>
    </div>

    @include('partials.ads.slot', [
      'slotKey' => 'ads_feed_inline',
      'wrapperClass' => 'alma-ad-slot--cover',
    ])

    @include('blog.partials.post-comments')

  </main>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    document.querySelectorAll('[data-ps-menu-trigger]').forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        const menu = button.closest('[data-ps-menu]');
        if (!menu) return;
        document.querySelectorAll('[data-ps-menu].is-open').forEach(function (openMenu) {
          if (openMenu !== menu) openMenu.classList.remove('is-open');
        });
        menu.classList.toggle('is-open');
      });
    });

    document.addEventListener('click', function (event) {
      if (!event.target.closest('[data-ps-menu]')) {
        document.querySelectorAll('[data-ps-menu].is-open').forEach(function (menu) {
          menu.classList.remove('is-open');
        });
      }

      const reactionTrigger = event.target.closest('[data-post-reaction-trigger]');
      if (reactionTrigger) {
        event.preventDefault();
        const picker = reactionTrigger.closest('[data-post-reaction-picker]');
        if (!picker) return;
        document.querySelectorAll('[data-post-reaction-picker].is-open').forEach(function (openPicker) {
          if (openPicker !== picker) {
            openPicker.classList.remove('is-open');
            const openMenu = openPicker.querySelector('[data-post-reaction-menu]');
            if (openMenu) openMenu.hidden = true;
            const openButton = openPicker.querySelector('[data-post-reaction-trigger]');
            if (openButton) openButton.setAttribute('aria-expanded', 'false');
          }
        });
        const open = !picker.classList.contains('is-open');
        picker.classList.toggle('is-open', open);
        const menu = picker.querySelector('[data-post-reaction-menu]');
        if (menu) menu.hidden = !open;
        reactionTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        return;
      }

      if (!event.target.closest('[data-post-reaction-picker]')) {
        document.querySelectorAll('[data-post-reaction-picker].is-open').forEach(function (picker) {
          picker.classList.remove('is-open');
          const menu = picker.querySelector('[data-post-reaction-menu]');
          if (menu) menu.hidden = true;
          const button = picker.querySelector('[data-post-reaction-trigger]');
          if (button) button.setAttribute('aria-expanded', 'false');
        });
      }

      const editDetailsTrigger = event.target.closest('[data-post-edit-details-trigger]');
      if (editDetailsTrigger) {
        event.preventDefault();
        const wrap = editDetailsTrigger.closest('[data-post-edit-details]');
        if (!wrap) return;
        document.querySelectorAll('[data-post-edit-details].is-open').forEach(function (openWrap) {
          if (openWrap !== wrap) {
            openWrap.classList.remove('is-open');
            const openButton = openWrap.querySelector('[data-post-edit-details-trigger]');
            if (openButton) openButton.setAttribute('aria-expanded', 'false');
          }
        });
        const open = !wrap.classList.contains('is-open');
        wrap.classList.toggle('is-open', open);
        editDetailsTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        return;
      }

      if (!event.target.closest('[data-post-edit-details]')) {
        document.querySelectorAll('[data-post-edit-details].is-open').forEach(function (wrap) {
          wrap.classList.remove('is-open');
          const button = wrap.querySelector('[data-post-edit-details-trigger]');
          if (button) button.setAttribute('aria-expanded', 'false');
        });
      }
    });



    const closeCommentMoreMenus = function (exceptWrap) {
      document.querySelectorAll('[data-comment-more].is-open').forEach(function (wrap) {
        if (exceptWrap && wrap === exceptWrap) return;
        wrap.classList.remove('is-open');
        const menu = wrap.querySelector('[data-comment-more-menu]');
        const trigger = wrap.querySelector('[data-comment-more-trigger]');
        if (menu) {
          menu.hidden = true;
          menu.style.top = '';
          menu.style.bottom = '';
        }
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      });
    };

    document.addEventListener('click', function (event) {
      const commentMoreTrigger = event.target.closest('[data-comment-more-trigger]');
      if (commentMoreTrigger) {
        event.preventDefault();
        event.stopPropagation();
        const wrap = commentMoreTrigger.closest('[data-comment-more]');
        if (!wrap) return;
        const menu = wrap.querySelector('[data-comment-more-menu]');
        const open = !wrap.classList.contains('is-open');
        closeCommentMoreMenus(wrap);
        wrap.classList.toggle('is-open', open);
        if (menu) {
          menu.hidden = !open;
          if (open) {
            menu.classList.remove('is-above');
            window.requestAnimationFrame(function () {
              const rect = menu.getBoundingClientRect();
              const viewportBottom = window.innerHeight || document.documentElement.clientHeight;
              if (rect.bottom > viewportBottom - 12) {
                menu.style.top = 'auto';
                menu.style.bottom = 'calc(100% + 8px)';
              } else {
                menu.style.top = 'calc(100% + 8px)';
                menu.style.bottom = 'auto';
              }
            });
          } else {
            menu.style.top = '';
            menu.style.bottom = '';
          }
        }
        commentMoreTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        return;
      }

      if (!event.target.closest('[data-comment-more]')) {
        closeCommentMoreMenus();
      }
    });



    const syncRepliesToggle = function (wrap) {
      if (!wrap) return;
      const items = wrap.querySelectorAll(':scope > .ps-comment-item');
      const hiddenCount = items.length;
      const collapsed = wrap.classList.contains('is-collapsed');
      const targetSelector = wrap.id ? ('#' + CSS.escape(wrap.id)) : '';
      const toggles = targetSelector
        ? document.querySelectorAll('[data-replies-toggle][data-replies-target="' + targetSelector + '"]')
        : wrap.querySelectorAll(':scope > [data-replies-toggle]');

      toggles.forEach(function (toggle) {
        const label = toggle.querySelector('[data-replies-toggle-label]');
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        if (label) {
          label.textContent = collapsed ? ('Daha fazla yanıt göster' + (hiddenCount > 0 ? ' (' + hiddenCount + ')' : '')) : 'Yanıtları gizle';
        }
      });
    };

    document.querySelectorAll('.ps-replies').forEach(function (wrap) {
      const items = wrap.querySelectorAll(':scope > .ps-comment-item');
      wrap.classList.remove('is-collapsed');
      syncRepliesToggle(wrap);
    });

    document.addEventListener('click', function (event) {
      const toggle = event.target.closest('[data-replies-toggle]');
      if (!toggle) return;

      const targetSelector = toggle.getAttribute('data-replies-target');
      const wrap = targetSelector ? document.querySelector(targetSelector) : toggle.closest('.ps-replies');
      if (!wrap) return;

      event.preventDefault();
      wrap.classList.toggle('is-collapsed');
      syncRepliesToggle(wrap);
    });

    const markShareCopied = function (button) {
      button.classList.add('is-copied');
      window.setTimeout(function () {
        button.classList.remove('is-copied');
      }, 1200);
    };

    const fallbackCopyPostLink = function (url, button) {
      if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
        return navigator.clipboard.writeText(url).then(function () {
          markShareCopied(button);
        }).catch(function () {
          window.prompt('Linki kopyala:', url);
        });
      }

      const textarea = document.createElement('textarea');
      textarea.value = url;
      textarea.setAttribute('readonly', 'readonly');
      textarea.style.position = 'fixed';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();

      try {
        document.execCommand('copy');
        markShareCopied(button);
      } catch (error) {
        window.prompt('Linki kopyala:', url);
      } finally {
        textarea.remove();
      }
    };

    document.addEventListener('click', function (event) {
      const shareButton = event.target.closest('[data-copy-post-link]');
      if (!shareButton) return;

      event.preventDefault();
      event.stopPropagation();

      const url = shareButton.getAttribute('data-url') || window.location.href;
      const title = shareButton.getAttribute('data-title') || document.title || 'Paylaş';

      if (navigator.share) {
        navigator.share({ title: title, url: url }).then(function () {
          markShareCopied(shareButton);
        }).catch(function () {
          fallbackCopyPostLink(url, shareButton);
        });
        return;
      }

      fallbackCopyPostLink(url, shareButton);
    });

    const locker = document.querySelector('[data-nsfw-locker]');
    if (locker) {
      const checkbox = locker.querySelector('[data-nsfw-checkbox]');
      const revealButton = locker.querySelector('[data-nsfw-reveal]');
      const overlay = locker.querySelector('[data-nsfw-overlay]');
      if (checkbox && revealButton && overlay) {
        checkbox.addEventListener('change', function () { revealButton.disabled = !checkbox.checked; });
        revealButton.addEventListener('click', function () {
          if (!checkbox.checked) return;
          locker.classList.remove('ps-nsfw-locked');
          overlay.remove();
        });
      }
    }
  })();

  (function () {
    const blockedWordsRaw = @json($showBlockedWordsForJs ?? collect());
    const normalizeBlockedWordText = function (value) {
      let text = String(value || '').toLocaleLowerCase('tr-TR');
      const map = {
        'â': 'a', 'î': 'i', 'û': 'u',
        'ı': 'i', 'İ': 'i', 'ğ': 'g', 'ü': 'u', 'ş': 's', 'ö': 'o', 'ç': 'c',
        '0': 'o', '1': 'i', '3': 'e', '4': 'a', '5': 's', '7': 't',
        '@': 'a', '$': 's'
      };
      text = text.replace(/[âîûıİğüşöç013457@$]/g, function (char) { return map[char] || char; });
      text = text.replace(/(.)\1{2,}/gu, '$1$1');
      text = text.replace(/[^a-z0-9]+/giu, '');
      return text.trim();
    };

    const blockedWords = Array.from(new Set((blockedWordsRaw || [])
      .map(function (word) { return normalizeBlockedWordText(word); })
      .filter(Boolean)
    ));

    const escapeHtml = function (value) {
      return String(value || '').replace(/[&<>"']/g, function (char) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
      });
    };

    const ensureBadWordWarning = function (form) {
      if (!form) return null;
      let warning = form.querySelector('[data-comment-badword-warning]');
      if (warning) return warning;

      warning = document.createElement('div');
      warning.className = 'ps-comment-badword-warning';
      warning.setAttribute('data-comment-badword-warning', 'true');
      warning.setAttribute('role', 'alert');
      warning.textContent = 'Yasaklı kelime silindi. Lütfen yorumunu uygun şekilde yaz.';

      const textarea = form.querySelector('textarea[name="content"]');
      if (textarea && textarea.parentNode) {
        textarea.parentNode.insertBefore(warning, textarea.nextSibling);
      } else {
        form.appendChild(warning);
      }

      return warning;
    };

    const showBadWordWarning = function (form, message) {
      const warning = ensureBadWordWarning(form);
      if (!warning) return;
      warning.textContent = message || 'Yasaklı kelime silindi. Lütfen yorumunu uygun şekilde yaz.';
      warning.classList.add('is-visible');
      window.clearTimeout(warning._hideTimer);
      warning._hideTimer = window.setTimeout(function () {
        warning.classList.remove('is-visible');
      }, 2600);
    };

    const containsBlockedWord = function (value) {
      if (!blockedWords.length) return false;
      const normalized = normalizeBlockedWordText(value);
      if (!normalized) return false;
      return blockedWords.some(function (word) { return word && normalized.includes(word); });
    };

    const removeBlockedWordsFromText = function (value) {
      if (!blockedWords.length) return { text: value, changed: false };

      const original = String(value || '');
      let changed = false;
      const parts = original.match(/\S+|\s+/gu) || [];
      const words = [];
      parts.forEach(function (part, index) {
        if (/\S/u.test(part)) {
          words.push({ index: index, value: part });
        }
      });

      const removeIndexes = new Set();

      words.forEach(function (word, position) {
        const single = normalizeBlockedWordText(word.value);
        if (single && blockedWords.some(function (blocked) { return single.includes(blocked) || blocked.includes(single); })) {
          removeIndexes.add(word.index);
          changed = true;
        }

        for (let length = 2; length <= 5; length += 1) {
          const slice = words.slice(position, position + length);
          if (slice.length !== length) continue;
          const phrase = normalizeBlockedWordText(slice.map(function (item) { return item.value; }).join(''));
          if (phrase && blockedWords.some(function (blocked) { return phrase.includes(blocked) || blocked.includes(phrase); })) {
            slice.forEach(function (item) { removeIndexes.add(item.index); });
            changed = true;
          }
        }
      });

      if (!changed) {
        return { text: original, changed: false };
      }

      const cleaned = parts
        .filter(function (_, index) { return !removeIndexes.has(index); })
        .join('')
        .replace(/[ \t]{2,}/g, ' ')
        .replace(/\n{3,}/g, '\n\n')
        .trimStart();

      return { text: cleaned, changed: true };
    };

    const sanitizeCommentTextarea = function (textarea, mode) {
      if (!textarea) return false;
      const form = textarea.closest('form');
      const result = removeBlockedWordsFromText(textarea.value);

      if (result.changed) {
        textarea.value = result.text;
        textarea.classList.add('has-blocked-word');
        showBadWordWarning(form, 'Yasaklı kelime yorumdan otomatik silindi.');
        window.setTimeout(function () { textarea.classList.remove('has-blocked-word'); }, 650);
        return true;
      }

      if (containsBlockedWord(textarea.value)) {
        textarea.classList.add('has-blocked-word');
        showBadWordWarning(form, mode === 'submit' ? 'Yasaklı kelime var. Paylaşmadan önce silmelisin.' : 'Yasaklı kelime kullanılamaz.');
        return true;
      }

      textarea.classList.remove('has-blocked-word');
      return false;
    };

    document.addEventListener('input', function (event) {
      const textarea = event.target.closest('#show-comment-form textarea[name="content"], .ps-comment-reply-form textarea[name="content"], .ps-comment-edit-form textarea[name="content"]');
      if (!textarea) return;
      sanitizeCommentTextarea(textarea, 'input');
    });

    document.addEventListener('paste', function (event) {
      const textarea = event.target.closest('#show-comment-form textarea[name="content"], .ps-comment-reply-form textarea[name="content"], .ps-comment-edit-form textarea[name="content"]');
      if (!textarea) return;
      window.setTimeout(function () { sanitizeCommentTextarea(textarea, 'paste'); }, 0);
    });

    const renderFilePreview = function (input) {
      const previewSelector = input.getAttribute('data-comment-preview-target');
      const preview = previewSelector ? document.querySelector(previewSelector) : null;
      if (!preview) return;

      const file = input.files && input.files.length ? input.files[0] : null;
      if (!file) {
        preview.hidden = true;
        preview.innerHTML = '';
        return;
      }

      const safeName = file.name || 'Seçilen fotoğraf';
      const objectUrl = URL.createObjectURL(file);
      preview.innerHTML = '<img src="' + objectUrl + '" alt="Seçilen fotoğraf önizleme"><span>' + safeName.replace(/[&<>"']/g, function (char) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[char]; }) + '</span>';
      preview.hidden = false;
    };

    document.addEventListener('click', function (event) {
      const button = event.target.closest('[data-comment-file-button]');
      if (!button) return;

      event.preventDefault();
      const target = button.getAttribute('data-comment-file-target');
      const input = target ? document.querySelector(target) : null;
      if (input) input.click();
    });

    document.addEventListener('change', function (event) {
      const input = event.target.closest('[data-comment-file-input]');
      if (input) renderFilePreview(input);
    });

    document.addEventListener('submit', function (event) {
      const form = event.target.closest('#show-comment-form, .ps-comment-reply-form');
      if (!form) return;

      const textarea = form.querySelector('textarea[name="content"]');

      if (textarea) {
        const result = removeBlockedWordsFromText(textarea.value);

        if (result.changed) {
          textarea.value = result.text;
          textarea.classList.add('has-blocked-word');
          showBadWordWarning(form, 'Yasaklı kelime silindi, kalan yorum paylaşılıyor.');
          window.setTimeout(function () { textarea.classList.remove('has-blocked-word'); }, 650);
        }

        if (containsBlockedWord(textarea.value)) {
          const secondPass = removeBlockedWordsFromText(textarea.value);
          if (secondPass.changed) {
            textarea.value = secondPass.text;
            textarea.classList.add('has-blocked-word');
            showBadWordWarning(form, 'Yasaklı kelime silindi, kalan yorum paylaşılıyor.');
            window.setTimeout(function () { textarea.classList.remove('has-blocked-word'); }, 650);
          }
        }
      }

      const fileInput = form.querySelector('[data-comment-file-input]');
      const hasText = textarea && textarea.value.trim() !== '';
      const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

      if (!hasText && !hasFile) {
        event.preventDefault();
        if (textarea) textarea.focus();
      }
    });
  })();


  document.addEventListener('click', function (event) {
    const replyToggle = event.target.closest('[data-comment-reply-toggle]');
    if (replyToggle) {
      event.preventDefault();
      const target = document.querySelector(replyToggle.getAttribute('data-comment-reply-toggle'));
      if (target) target.classList.toggle('is-open');
    }

    const editToggle = event.target.closest('[data-comment-edit-toggle]');
    if (editToggle) {
      event.preventDefault();
      const target = document.querySelector(editToggle.getAttribute('data-comment-edit-toggle'));
      if (target) target.classList.toggle('is-open');
    }
  });



    document.addEventListener('click', function (event) {
      const moreTrigger = event.target.closest('[data-reaction-more-trigger]');

      document.querySelectorAll('[data-reaction-more]').forEach(function (wrap) {
        if (!moreTrigger || !wrap.contains(moreTrigger)) {
          const menu = wrap.querySelector('[data-reaction-more-menu]');
          const trigger = wrap.querySelector('[data-reaction-more-trigger]');
          if (menu) menu.hidden = true;
          if (trigger) trigger.setAttribute('aria-expanded', 'false');
        }
      });

      if (moreTrigger) {
        event.preventDefault();
        const wrap = moreTrigger.closest('[data-reaction-more]');
        const menu = wrap ? wrap.querySelector('[data-reaction-more-menu]') : null;
        if (menu) {
          const willOpen = menu.hidden;
          menu.hidden = !willOpen;
          moreTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        }
        return;
      }

      const nextButtonClick = event.target.closest('[data-reaction-more-next]');
      const prevButtonClick = event.target.closest('[data-reaction-more-prev]');

      if (!nextButtonClick && !prevButtonClick) return;

      event.preventDefault();
      const wrap = event.target.closest('[data-reaction-more]');
      if (!wrap) return;

      const pages = Array.from(wrap.querySelectorAll('[data-reaction-more-page]'));
      if (!pages.length) return;

      let currentIndex = pages.findIndex(function (page) {
        return !page.hidden;
      });

      if (currentIndex < 0) currentIndex = 0;

      const nextIndex = nextButtonClick
        ? Math.min(currentIndex + 1, pages.length - 1)
        : Math.max(currentIndex - 1, 0);

      pages.forEach(function (page, index) {
        page.hidden = index !== nextIndex;
      });

      const prevButton = wrap.querySelector('[data-reaction-more-prev]');
      const nextButton = wrap.querySelector('[data-reaction-more-next]');
      const counter = wrap.querySelector('[data-reaction-more-counter]');

      if (prevButton) prevButton.disabled = nextIndex === 0;
      if (nextButton) nextButton.disabled = nextIndex === pages.length - 1;
      if (counter) counter.textContent = (nextIndex + 1) + ' / ' + pages.length;
    });
</script>


<script>
  (function () {
    const ready = function (callback) {
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', callback);
      else callback();
    };

    ready(function () {
      const form = document.getElementById('show-comment-form');
      if (!form) return;

      const textarea = form.querySelector('textarea[name="content"]');
      const menu = form.querySelector('[data-comment-mention-menu]');
      const openButton = form.querySelector('[data-comment-open-mentions]');
      if (!textarea || !menu) return;

      const normalize = function (value) {
        return String(value || '').replace(/^@+/, '').trim();
      };

      const initials = function (name) {
        return normalize(name).split(/\s+/).filter(Boolean).slice(0, 2).map(function (part) { return part.charAt(0).toUpperCase(); }).join('') || 'U';
      };

      const usersMap = new Map();
      document.querySelectorAll('.post-show-shell .ps-comment-item').forEach(function (item) {
        const author = item.querySelector('.ps-comment-author');
        if (!author) return;
        const name = normalize(author.textContent);
        if (!name) return;
        const avatarImg = item.querySelector('.ps-comment-avatar img');
        const avatarText = item.querySelector('.ps-comment-avatar');
        const key = name.toLowerCase();
        if (!usersMap.has(key)) {
          usersMap.set(key, {
            name: name,
            avatar: avatarImg ? avatarImg.getAttribute('src') : '',
            initials: avatarImg ? '' : initials(avatarText ? avatarText.textContent : name)
          });
        }
      });

      const users = Array.from(usersMap.values()).slice(0, 8);
      if (!users.length) {
        menu.hidden = true;
        if (openButton) openButton.hidden = true;
        return;
      }

      const render = function (query) {
        const q = normalize(query).toLowerCase();
        const filtered = users.filter(function (user) {
          return !q || user.name.toLowerCase().includes(q);
        }).slice(0, 6);

        if (!filtered.length) {
          menu.hidden = true;
          menu.innerHTML = '';
          return;
        }

        menu.innerHTML = filtered.map(function (user, index) {
          const avatar = user.avatar
            ? '<img src="' + user.avatar.replace(/"/g, '&quot;') + '" alt="">'
            : '<span>' + user.initials + '</span>';
          return '<button type="button" class="ps-comment-mention-option' + (index === 0 ? ' is-active' : '') + '" data-mention-name="' + user.name.replace(/"/g, '&quot;') + '"><span class="ps-comment-mention-avatar">' + avatar + '</span><span class="ps-comment-mention-name">' + user.name + '</span></button>';
        }).join('');
        menu.hidden = false;
      };

      const currentMentionQuery = function () {
        const value = textarea.value || '';
        const cursor = textarea.selectionStart || value.length;
        const before = value.slice(0, cursor);
        const match = before.match(/(^|\s)@([\p{L}\p{N}_.-]*)$/u);
        return match ? match[2] : null;
      };

      const insertMention = function (name) {
        const value = textarea.value || '';
        const cursor = textarea.selectionStart || value.length;
        const before = value.slice(0, cursor);
        const after = value.slice(cursor);
        const replaced = before.replace(/(^|\s)@([\p{L}\p{N}_.-]*)$/u, function (_, prefix) {
          return prefix + '@' + normalize(name) + ' ';
        });
        textarea.value = replaced + after;
        textarea.focus();
        const nextCursor = replaced.length;
        textarea.setSelectionRange(nextCursor, nextCursor);
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        menu.hidden = true;
      };

      textarea.addEventListener('input', function () {
        const query = currentMentionQuery();
        if (query === null) {
          menu.hidden = true;
          return;
        }
        render(query);
      });

      textarea.addEventListener('keydown', function (event) {
        if (menu.hidden) return;
        if (event.key === 'Escape') {
          menu.hidden = true;
          return;
        }
        if (event.key === 'Enter') {
          const active = menu.querySelector('.ps-comment-mention-option.is-active') || menu.querySelector('.ps-comment-mention-option');
          if (active) {
            event.preventDefault();
            insertMention(active.getAttribute('data-mention-name') || active.textContent);
          }
        }
      });

      if (openButton) {
        openButton.addEventListener('click', function () {
          textarea.focus();
          const cursor = textarea.selectionStart || textarea.value.length;
          const before = textarea.value.slice(0, cursor);
          const needsSpace = before.length && !/\s$/.test(before);
          textarea.value = before + (needsSpace ? ' @' : '@') + textarea.value.slice(cursor);
          const nextCursor = before.length + (needsSpace ? 2 : 1);
          textarea.setSelectionRange(nextCursor, nextCursor);
          textarea.dispatchEvent(new Event('input', { bubbles: true }));
          render('');
        });
      }

      menu.addEventListener('click', function (event) {
        const option = event.target.closest('[data-mention-name]');
        if (!option) return;
        event.preventDefault();
        insertMention(option.getAttribute('data-mention-name'));
      });

      document.addEventListener('click', function (event) {
        if (!event.target.closest('#show-comment-form')) menu.hidden = true;
      });
    });
  })();
</script>


<script>
  (function () {
    const refreshVideoCommentSend = function (form) {
      if (!form) return;
      const textarea = form.querySelector('textarea[name="content"]');
      const hasText = !!(textarea && textarea.value.trim().length > 0);
      form.classList.toggle('has-comment-text', hasText);
    };

    const bindForm = function (form) {
      if (!form || form.dataset.videoCommentBound === '1') return;
      form.dataset.videoCommentBound = '1';
      refreshVideoCommentSend(form);
      form.addEventListener('input', function () { refreshVideoCommentSend(form); });
      form.addEventListener('change', function () { refreshVideoCommentSend(form); });
    };

    const bindAll = function () {
      document.querySelectorAll('#show-comment-form, .ps-comment-reply-form, .ps-comment-edit-form').forEach(bindForm);
    };

    bindAll();
    document.addEventListener('click', function () { window.setTimeout(bindAll, 0); });
  })();
</script>

<style>
  /* FINAL FIX: yorum aksiyon ikonları ve bağlantıları arasında temiz boşluk */
  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-comment-actions--reply {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 0 !important;
    column-gap: 0 !important;
    row-gap: 8px !important;
    margin-top: 10px !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-right: 14px !important;
    flex: 0 0 auto !important;
  }

  .post-show-shell .ps-comment-votes form {
    display: inline-flex !important;
    align-items: center !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-vote-btn {
    width: 20px !important;
    height: 20px !important;
    min-width: 20px !important;
    min-height: 20px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    margin: 0 !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-vote-btn svg {
    width: 14px !important;
    height: 14px !important;
    display: block !important;
  }

  .post-show-shell .ps-comment-vote-count {
    display: inline-flex !important;
    align-items: center !important;
    min-width: 10px !important;
    margin-left: -3px !important;
    margin-right: 4px !important;
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    line-height: 1 !important;
  }

  .post-show-shell .ps-comment-action,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn,
  .post-show-shell .ps-comment-toggle-replies {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    min-width: auto !important;
    width: auto !important;
    height: 22px !important;
    min-height: 22px !important;
    padding: 0 2px !important;
    margin: 0 14px 0 0 !important;
    border: 0 !important;
    border-radius: 6px !important;
    background: transparent !important;
    color: #64748b !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    line-height: 1 !important;
    text-decoration: none !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn::after {
    content: "Yanıtla" !important;
    display: inline-flex !important;
    margin-left: 4px !important;
    color: inherit !important;
    font-size: 12px !important;
    font-weight: 700 !important;
  }

  .post-show-shell .ps-comment-action svg,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn svg,
  .post-show-shell .ps-comment-toggle-replies svg {
    width: 14px !important;
    height: 14px !important;
    display: block !important;
    flex: 0 0 auto !important;
  }

  .post-show-shell .ps-comment-toggle-replies,
  .post-show-shell .ps-comment-toggle-replies * {
    color: #60a5fa !important;
  }

  .post-show-shell .ps-comment-actions > *:last-child,
  .post-show-shell .ps-comment-actions--reply > *:last-child {
    margin-right: 0 !important;
  }

  .post-show-shell .ps-comment-action:hover,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover,
  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-toggle-replies:hover {
    background: #f3f4f6 !important;
    color: #111827 !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comment-votes {
      gap: 7px !important;
      margin-right: 12px !important;
    }

    .post-show-shell .ps-comment-action,
    .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn,
    .post-show-shell .ps-comment-toggle-replies {
      margin-right: 12px !important;
      font-size: 12px !important;
    }
  }

  /* FINAL FIX: yorum aksiyon renkleri ve boşlukları */
  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-comment-actions--reply {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 0 !important;
    column-gap: 0 !important;
    row-gap: 6px !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-right: 18px !important;
    color: #64748b !important;
  }

  .post-show-shell .ps-comment-vote-btn {
    width: 20px !important;
    height: 20px !important;
    color: #64748b !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 999px !important;
    padding: 0 !important;
  }

  .post-show-shell .ps-comment-vote-btn svg,
  .post-show-shell .ps-comment-vote-btn iconify-icon {
    width: 15px !important;
    height: 15px !important;
    color: #64748b !important;
    stroke: currentColor !important;
  }

  .post-show-shell .ps-comment-vote-count {
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    line-height: 1 !important;
    margin: 0 2px 0 -4px !important;
    min-width: 8px !important;
  }

  .post-show-shell .ps-comment-action,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn {
    color: #334155 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    margin-right: 16px !important;
    padding: 2px 0 !important;
    background: transparent !important;
    text-decoration: none !important;
    white-space: nowrap !important;
  }

  .post-show-shell .ps-comment-toggle-replies {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    color: #2563eb !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    padding: 2px 0 !important;
    background: transparent !important;
    text-decoration: none !important;
    white-space: nowrap !important;
  }

  .post-show-shell .ps-comment-toggle-replies * {
    color: #2563eb !important;
  }

  .post-show-shell .ps-comment-toggle-replies::before {
    content: "" !important;
    display: inline-block !important;
    width: 1px !important;
    height: 13px !important;
    margin-right: 8px !important;
    background: #e2e8f0 !important;
    vertical-align: middle !important;
  }

  .post-show-shell .ps-comment-action:hover,
  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn:hover {
    color: #111827 !important;
    background: transparent !important;
  }

  .post-show-shell .ps-comment-toggle-replies:hover,
  .post-show-shell .ps-comment-toggle-replies:hover * {
    color: #1d4ed8 !important;
    background: transparent !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover {
    color: #334155 !important;
    background: #f1f5f9 !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover svg,
  .post-show-shell .ps-comment-vote-btn:hover iconify-icon {
    color: #334155 !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-comment-votes {
      gap: 7px !important;
      margin-right: 14px !important;
    }

    .post-show-shell .ps-comment-action,
    .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn {
      margin-right: 14px !important;
      font-size: 12px !important;
    }

    .post-show-shell .ps-comment-toggle-replies {
      font-size: 12px !important;
    }

    .post-show-shell .ps-comment-toggle-replies::before {
      margin-right: 7px !important;
    }
  }

</style>

@endpush


@push('head')
<style>
  /* OGRAFI POST SHOW FINAL FIX: hizalama + hızlı tekil override */
  .post-show-shell,
  .post-show-shell * { box-sizing: border-box !important; }

  .post-show-shell .ps-post-author-row,
  .post-show-shell .ps-comments-top,
  .post-show-shell .ps-comment-card,
  .post-show-shell .ps-comment-meta,
  .post-show-shell .ps-comment-actions { align-items: center !important; }

  .post-show-shell .ps-post-author-row { position: relative !important; overflow: visible !important; }

  .post-show-shell .ps-menu,
  .post-show-shell .ps-comment-more,
  .post-show-shell .ps-reaction-more-wrap,
  .post-show-shell .ps-reaction-picker {
    position: relative !important;
    overflow: visible !important;
    z-index: 30 !important;
  }

  .post-show-shell .ps-menu.is-open,
  .post-show-shell .ps-comment-more.is-open,
  .post-show-shell .ps-reaction-picker.is-open { z-index: 9999 !important; }

  .post-show-shell .ps-menu-trigger,
  .post-show-shell .ps-comment-more-trigger {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    min-height: 34px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    cursor: pointer !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-menu-trigger:hover,
  .post-show-shell .ps-menu-trigger:focus-visible,
  .post-show-shell .ps-menu.is-open .ps-menu-trigger,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-more-trigger:focus-visible,
  .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    outline: none !important;
  }

  .post-show-shell .ps-menu-panel,
  .post-show-shell .ps-comment-more-menu {
    position: absolute !important;
    top: calc(100% + 8px) !important;
    right: 0 !important;
    left: auto !important;
    z-index: 10000 !important;
    min-width: 168px !important;
    width: max-content !important;
    max-width: calc(100vw - 28px) !important;
    padding: 7px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 14px !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-menu-panel { display: none !important; }
  .post-show-shell .ps-menu.is-open .ps-menu-panel {
    display: flex !important;
    flex-direction: column !important;
    gap: 3px !important;
  }
  .post-show-shell .ps-comment-more-menu[hidden] { display: none !important; }
  .post-show-shell .ps-comment-more-menu:not([hidden]) {
    display: flex !important;
    flex-direction: column !important;
    gap: 3px !important;
  }

  .post-show-shell .ps-menu-item,
  .post-show-shell .ps-comment-more-item {
    width: 100% !important;
    min-height: 38px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 9px !important;
    border: 0 !important;
    border-radius: 10px !important;
    background: transparent !important;
    color: #334155 !important;
    padding: 9px 10px !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.2 !important;
    text-align: left !important;
    text-decoration: none !important;
    cursor: pointer !important;
    white-space: nowrap !important;
  }

  .post-show-shell .ps-menu-item:hover,
  .post-show-shell .ps-comment-more-item:hover,
  .post-show-shell .ps-menu-item:focus-visible,
  .post-show-shell .ps-comment-more-item:focus-visible {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    outline: none !important;
  }

  .post-show-shell .ps-comment-form-box,
  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-mini-box--reply,
  .post-show-shell .ps-comment-reply-form,
  .post-show-shell .ps-comment-edit-form {
    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-form-box {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    display: grid !important;
    grid-template-columns: 34px minmax(0, 1fr) 42px !important;
    grid-template-rows: auto auto !important;
    align-items: start !important;
    column-gap: 10px !important;
    row-gap: 8px !important;
    min-height: 58px !important;
    padding: 8px 10px !important;
    border: 1px solid #dbe4f0 !important;
    border-radius: 22px !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-form-box:focus-within,
  .post-show-shell .ps-comment-form-box.is-growing {
    border-color: #2563eb !important;
    border-radius: 18px !important;
  }

  .post-show-shell .ps-comment-form-box .ps-comment-avatar,
  .post-show-shell .ps-comment-form-box .ps-comment-mini-avatar,
  .post-show-shell .ps-comment-form-box > :first-child {
    grid-column: 1 !important;
    grid-row: 1 !important;
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    min-height: 34px !important;
    align-self: start !important;
    margin: 1px 0 0 !important;
  }

  .post-show-shell .ps-comment-form-box textarea.ps-comment-textarea,
  .post-show-shell textarea.ps-comment-mini-textarea {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    height: auto !important;
    min-height: 36px !important;
    max-height: none !important;
    overflow: hidden !important;
    resize: none !important;
    display: block !important;
    padding: 8px 2px !important;
    margin: 0 !important;
    border: 0 !important;
    outline: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    color: #0f172a !important;
    font-size: 14px !important;
    line-height: 1.45 !important;
    white-space: pre-wrap !important;
    word-break: break-word !important;
    overflow-wrap: anywhere !important;
    scrollbar-width: none !important;
  }

  .post-show-shell .ps-comment-form-box textarea.ps-comment-textarea {
    grid-column: 2 !important;
    grid-row: 1 !important;
  }

  .post-show-shell textarea.ps-comment-textarea::-webkit-scrollbar,
  .post-show-shell textarea.ps-comment-mini-textarea::-webkit-scrollbar {
    width: 0 !important;
    height: 0 !important;
    display: none !important;
  }

  .post-show-shell .ps-comment-form-box .ps-comment-toolbar {
    grid-column: 3 !important;
    grid-row: 1 !important;
    width: 42px !important;
    height: 42px !important;
    margin: 0 !important;
    padding: 0 !important;
    display: flex !important;
    align-items: start !important;
    justify-content: center !important;
  }

  .post-show-shell .ps-comment-form-box .ps-comment-tool { display: none !important; }

  .post-show-shell .ps-comment-send,
  .post-show-shell .ps-comment-mini-btn--primary {
    width: 42px !important;
    min-width: 42px !important;
    height: 42px !important;
    min-height: 42px !important;
    border-radius: 999px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0 !important;
    line-height: 0 !important;
    color: #ffffff !important;
    background: #2563eb !important;
    border: 0 !important;
    box-shadow: none !important;
    flex: 0 0 42px !important;
  }

  .post-show-shell .ps-comment-send::before,
  .post-show-shell .ps-comment-mini-btn--primary::before {
    content: "" !important;
    width: 18px !important;
    height: 18px !important;
    display: block !important;
    background: currentColor !important;
    clip-path: polygon(10% 8%, 92% 50%, 10% 92%, 22% 56%, 52% 50%, 22% 44%) !important;
  }

  .post-show-shell .ps-comment-send:hover,
  .post-show-shell .ps-comment-mini-btn--primary:hover {
    background: #1d4ed8 !important;
    color: #ffffff !important;
  }

  .post-show-shell .ps-comment-form-box .ps-comment-image-preview,
  .post-show-shell .ps-comment-form-box [data-gif-preview],
  .post-show-shell .ps-comment-form-box #show-comment-gif-preview {
    grid-column: 2 / 4 !important;
    grid-row: 2 !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  html.dark .post-show-shell .ps-menu-panel,
  body.dark .post-show-shell .ps-menu-panel,
  .dark .post-show-shell .ps-menu-panel,
  [data-theme="dark"] .post-show-shell .ps-menu-panel,
  html.dark .post-show-shell .ps-comment-more-menu,
  body.dark .post-show-shell .ps-comment-more-menu,
  .dark .post-show-shell .ps-comment-more-menu,
  [data-theme="dark"] .post-show-shell .ps-comment-more-menu {
    background: #111827 !important;
    border-color: rgba(255,255,255,.12) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-menu-item,
  body.dark .post-show-shell .ps-menu-item,
  .dark .post-show-shell .ps-menu-item,
  [data-theme="dark"] .post-show-shell .ps-menu-item,
  html.dark .post-show-shell .ps-comment-more-item,
  body.dark .post-show-shell .ps-comment-more-item,
  .dark .post-show-shell .ps-comment-more-item,
  [data-theme="dark"] .post-show-shell .ps-comment-more-item { color: #e5e7eb !important; }

  html.dark .post-show-shell .ps-menu-item:hover,
  body.dark .post-show-shell .ps-menu-item:hover,
  .dark .post-show-shell .ps-menu-item:hover,
  [data-theme="dark"] .post-show-shell .ps-menu-item:hover,
  html.dark .post-show-shell .ps-comment-more-item:hover,
  body.dark .post-show-shell .ps-comment-more-item:hover,
  .dark .post-show-shell .ps-comment-more-item:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-more-item:hover {
    background: rgba(255,255,255,.10) !important;
    color: #ffffff !important;
  }

  @media (max-width: 768px) {
    .post-show-shell .ps-layout { padding-left: 0 !important; padding-right: 0 !important; }
    .post-show-shell .ps-main,
    .post-show-shell .ps-post-card,
    .post-show-shell .ps-comments-section { width: 100% !important; max-width: 100% !important; }
    .post-show-shell .ps-comment-form-box {
      grid-template-columns: 34px minmax(0, 1fr) 40px !important;
      column-gap: 8px !important;
      padding: 7px 8px !important;
    }
    .post-show-shell .ps-comment-send,
    .post-show-shell .ps-comment-mini-btn--primary,
    .post-show-shell .ps-comment-form-box .ps-comment-toolbar {
      width: 40px !important;
      min-width: 40px !important;
      height: 40px !important;
      min-height: 40px !important;
      flex-basis: 40px !important;
    }
    .post-show-shell .ps-menu-panel,
    .post-show-shell .ps-comment-more-menu { max-width: calc(100vw - 18px) !important; }
  }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    'use strict';
    if (window.__ografiPostShowFinalBound) return;
    window.__ografiPostShowFinalBound = true;

    var shellSelector = '.post-show-shell';
    var textSelector = shellSelector + ' textarea.ps-comment-textarea, ' + shellSelector + ' textarea.ps-comment-mini-textarea';

    function closePostMenus(except) {
      document.querySelectorAll(shellSelector + ' [data-ps-menu].is-open').forEach(function (menu) {
        if (except && menu === except) return;
        menu.classList.remove('is-open');
        var trigger = menu.querySelector('[data-ps-menu-trigger]');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      });
    }

    function closeCommentMenus(except) {
      document.querySelectorAll(shellSelector + ' [data-comment-more].is-open').forEach(function (wrap) {
        if (except && wrap === except) return;
        wrap.classList.remove('is-open');
        var trigger = wrap.querySelector('[data-comment-more-trigger]');
        var menu = wrap.querySelector('[data-comment-more-menu]');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
        if (menu) {
          menu.hidden = true;
          menu.style.top = '';
          menu.style.bottom = '';
        }
      });
    }

    function placeMenu(menu) {
      if (!menu) return;
      menu.style.top = 'calc(100% + 8px)';
      menu.style.bottom = 'auto';
      requestAnimationFrame(function () {
        var rect = menu.getBoundingClientRect();
        var bottom = window.innerHeight || document.documentElement.clientHeight;
        if (rect.bottom > bottom - 12) {
          menu.style.top = 'auto';
          menu.style.bottom = 'calc(100% + 8px)';
        }
      });
    }

    document.addEventListener('click', function (event) {
      var postTrigger = event.target.closest(shellSelector + ' [data-ps-menu-trigger]');
      if (postTrigger) {
        event.preventDefault();
        event.stopPropagation();
        var postMenu = postTrigger.closest('[data-ps-menu]');
        if (!postMenu) return;
        var openPost = !postMenu.classList.contains('is-open');
        closePostMenus(postMenu);
        closeCommentMenus();
        postMenu.classList.toggle('is-open', openPost);
        postTrigger.setAttribute('aria-expanded', openPost ? 'true' : 'false');
        if (openPost) placeMenu(postMenu.querySelector('.ps-menu-panel'));
        return;
      }

      var commentTrigger = event.target.closest(shellSelector + ' [data-comment-more-trigger]');
      if (commentTrigger) {
        event.preventDefault();
        event.stopPropagation();
        var commentWrap = commentTrigger.closest('[data-comment-more]');
        if (!commentWrap) return;
        var commentMenu = commentWrap.querySelector('[data-comment-more-menu]');
        var openComment = !commentWrap.classList.contains('is-open');
        closeCommentMenus(commentWrap);
        closePostMenus();
        commentWrap.classList.toggle('is-open', openComment);
        commentTrigger.setAttribute('aria-expanded', openComment ? 'true' : 'false');
        if (commentMenu) {
          commentMenu.hidden = !openComment;
          if (openComment) placeMenu(commentMenu);
        }
        return;
      }

      if (!event.target.closest(shellSelector + ' [data-ps-menu]')) closePostMenus();
      if (!event.target.closest(shellSelector + ' [data-comment-more]')) closeCommentMenus();
    }, true);

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') return;
      closePostMenus();
      closeCommentMenus();
    });

    function growTextarea(textarea) {
      if (!textarea) return;
      textarea.setAttribute('rows', '1');
      textarea.style.setProperty('height', 'auto', 'important');
      textarea.style.setProperty('overflow-y', 'hidden', 'important');
      textarea.style.setProperty('resize', 'none', 'important');
      textarea.style.setProperty('max-height', 'none', 'important');
      var nextHeight = Math.max(textarea.scrollHeight, 36);
      textarea.style.setProperty('height', nextHeight + 'px', 'important');
      var box = textarea.closest('.ps-comment-form-box, .ps-comment-mini-box, .ps-comment-mini-box--reply');
      if (box) {
        box.classList.toggle('is-growing', nextHeight > 46 || textarea.value.indexOf('\n') !== -1 || textarea.value.length > 70);
        box.style.setProperty('height', 'auto', 'important');
        box.style.setProperty('max-height', 'none', 'important');
        box.style.setProperty('overflow', 'visible', 'important');
      }
    }

    function bindTextareas(root) {
      (root || document).querySelectorAll(textSelector).forEach(function (textarea) {
        if (textarea.classList.contains('ps-comment-textarea')) textarea.setAttribute('placeholder', 'Yorumunu yaz...');
        if (textarea.dataset.ografiFinalGrowBound !== '1') {
          textarea.dataset.ografiFinalGrowBound = '1';
          textarea.addEventListener('input', function () { growTextarea(textarea); });
          textarea.addEventListener('focus', function () { growTextarea(textarea); });
          textarea.addEventListener('paste', function () { requestAnimationFrame(function () { growTextarea(textarea); }); });
        }
        growTextarea(textarea);
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () { bindTextareas(document); }, { once: true });
    } else {
      bindTextareas(document);
    }

    document.addEventListener('input', function (event) {
      if (event.target && event.target.matches(textSelector)) growTextarea(event.target);
    }, true);

    document.addEventListener('click', function (event) {
      if (!event.target.closest('[data-comment-reply-toggle], [data-comment-edit-toggle]')) return;
      requestAnimationFrame(function () { bindTextareas(document); });
    }, true);
  })();
</script>
@endpush


@push('head')
<style>
  /* FINAL FIX: Siyah yorum kutusu sadece dark mode'da çalışsın. Light mode tamamen açık kalsın. */
  html:not(.dark) .post-show-shell .ps-comments-section,
  body:not(.dark) .post-show-shell .ps-comments-section,
  html:not(.dark) .post-show-shell #show-comment-form .ps-comment-form-box,
  body:not(.dark) .post-show-shell #show-comment-form .ps-comment-form-box,
  html:not(.dark) .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  body:not(.dark) .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  html:not(.dark) .post-show-shell .ps-comment-mini-box,
  body:not(.dark) .post-show-shell .ps-comment-mini-box,
  html:not(.dark) .post-show-shell .ps-comment-mini-box--reply,
  body:not(.dark) .post-show-shell .ps-comment-mini-box--reply {
    background: #ffffff !important;
    border-color: #e8eef5 !important;
    color: #0f172a !important;
  }

  html:not(.dark) .post-show-shell .ps-comment-text,
  body:not(.dark) .post-show-shell .ps-comment-text {
    background: #f8fafc !important;
    border-color: #eef2f7 !important;
    color: #1e293b !important;
  }

  html:not(.dark) .post-show-shell textarea.ps-comment-textarea,
  body:not(.dark) .post-show-shell textarea.ps-comment-textarea,
  html:not(.dark) .post-show-shell textarea.ps-comment-mini-textarea,
  body:not(.dark) .post-show-shell textarea.ps-comment-mini-textarea {
    background: transparent !important;
    color: #0f172a !important;
  }

  html.dark .post-show-shell .ps-comments-section,
  body.dark .post-show-shell .ps-comments-section,
  .dark .post-show-shell .ps-comments-section,
  [data-theme="dark"] .post-show-shell .ps-comments-section {
    background: #050607 !important;
    border-color: rgba(255,255,255,.08) !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  body.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  .dark .post-show-shell #show-comment-form .ps-comment-form-box,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-form-box,
  html.dark .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  body.dark .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  .dark .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-form-box--modern,
  html.dark .post-show-shell .ps-comment-mini-box,
  body.dark .post-show-shell .ps-comment-mini-box,
  .dark .post-show-shell .ps-comment-mini-box,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-box,
  html.dark .post-show-shell .ps-comment-mini-box--reply,
  body.dark .post-show-shell .ps-comment-mini-box--reply,
  .dark .post-show-shell .ps-comment-mini-box--reply,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-box--reply {
    background: #0b0d10 !important;
    border-color: rgba(255,255,255,.10) !important;
    color: #f8fafc !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text {
    background: #111318 !important;
    border-color: rgba(255,255,255,.08) !important;
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell textarea.ps-comment-textarea,
  body.dark .post-show-shell textarea.ps-comment-textarea,
  .dark .post-show-shell textarea.ps-comment-textarea,
  [data-theme="dark"] .post-show-shell textarea.ps-comment-textarea,
  html.dark .post-show-shell textarea.ps-comment-mini-textarea,
  body.dark .post-show-shell textarea.ps-comment-mini-textarea,
  .dark .post-show-shell textarea.ps-comment-mini-textarea,
  [data-theme="dark"] .post-show-shell textarea.ps-comment-mini-textarea {
    background: transparent !important;
    color: #f8fafc !important;
  }

  html.dark .post-show-shell textarea.ps-comment-textarea::placeholder,
  body.dark .post-show-shell textarea.ps-comment-textarea::placeholder,
  .dark .post-show-shell textarea.ps-comment-textarea::placeholder,
  [data-theme="dark"] .post-show-shell textarea.ps-comment-textarea::placeholder,
  html.dark .post-show-shell textarea.ps-comment-mini-textarea::placeholder,
  body.dark .post-show-shell textarea.ps-comment-mini-textarea::placeholder,
  .dark .post-show-shell textarea.ps-comment-mini-textarea::placeholder,
  [data-theme="dark"] .post-show-shell textarea.ps-comment-mini-textarea::placeholder {
    color: #94a3b8 !important;
  }
</style>
@endpush


@push('head')
<style>
  /* ==========================================================
     OGRAFI COMMENTS - SADE MODERN V3
     Eski yorum görünümünü sakin, modern ve hafif yapar.
     Gölge yok, animasyon yok denecek kadar az, fontlar normal.
     ========================================================== */

  .post-show-shell .ps-comments-section {
    margin-top: 16px !important;
    padding: 0 !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    overflow: visible !important;
    font-family: Poppins, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
  }

  .post-show-shell .ps-comments-header {
    padding: 0 !important;
    margin: 0 0 14px !important;
  }

  .post-show-shell .ps-comments-top {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    margin: 0 0 12px !important;
    padding: 0 !important;
  }

  .post-show-shell .ps-comments-title {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin: 0 !important;
    color: #111827 !important;
    font-size: 18px !important;
    line-height: 1.2 !important;
    font-weight: 500 !important;
    letter-spacing: 0 !important;
  }

  .post-show-shell .ps-comments-count-badge {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 30px !important;
    height: 24px !important;
    padding: 0 9px !important;
    border-radius: 999px !important;
    background: #f1f5f9 !important;
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
  }

  .post-show-shell .ps-comments-sort {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    padding: 3px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 999px !important;
    background: #ffffff !important;
    box-shadow: none !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
  }

  .post-show-shell .ps-comments-sort-btn {
    min-height: 30px !important;
    padding: 0 11px !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    transform: none !important;
    transition: background-color .12s ease, color .12s ease, border-color .12s ease !important;
  }

  .post-show-shell .ps-comments-sort-btn:hover,
  .post-show-shell .ps-comments-sort-btn:focus-visible {
    background: #f8fafc !important;
    color: #111827 !important;
    outline: none !important;
  }

  .post-show-shell .ps-comments-sort-btn.is-active {
    background: #eff6ff !important;
    color: #2563eb !important;
    box-shadow: none !important;
  }

  /* Ana yorum yazma kutusu */
  .post-show-shell #show-comment-form {
    margin: 0 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box {
    position: relative !important;
    display: grid !important;
    grid-template-columns: 42px minmax(0, 1fr) !important;
    gap: 12px !important;
    padding: 14px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 18px !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
  }

  .post-show-shell #show-comment-form .ps-comment-form-box::before,
  .post-show-shell #show-comment-form .ps-comment-form-box::after {
    display: none !important;
    content: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-composer-avatar,
  .post-show-shell .ps-comment-composer-avatar {
    width: 42px !important;
    height: 42px !important;
    min-width: 42px !important;
    min-height: 42px !important;
    border-radius: 50% !important;
    border: 1px solid #e5e7eb !important;
    background: #f8fafc !important;
    color: #334155 !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    box-shadow: none !important;
    overflow: hidden !important;
  }

  .post-show-shell #show-comment-form .ps-comment-composer-avatar img,
  .post-show-shell .ps-comment-composer-avatar img {
    width: 100% !important;
    height: 100% !important;
    display: block !important;
    object-fit: cover !important;
  }

  .post-show-shell .ps-comment-compose-main {
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 9px !important;
  }

  .post-show-shell .ps-comment-compose-topline {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 10px !important;
    min-height: 24px !important;
  }

  .post-show-shell .ps-comment-compose-kicker {
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
  }

  .post-show-shell .ps-comment-compose-kicker::before {
    display: none !important;
    content: none !important;
  }

  .post-show-shell .ps-comment-compose-counter {
    min-width: auto !important;
    min-height: 24px !important;
    padding: 0 8px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 999px !important;
    background: #f8fafc !important;
    color: #94a3b8 !important;
    font-size: 11px !important;
    font-weight: 400 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea,
  .post-show-shell textarea.ps-comment-textarea {
    width: 100% !important;
    min-height: 78px !important;
    max-height: 260px !important;
    padding: 12px 13px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 14px !important;
    background: #f8fafc !important;
    color: #111827 !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    font-weight: 400 !important;
    resize: none !important;
    outline: none !important;
    box-shadow: none !important;
    transition: border-color .12s ease, background-color .12s ease !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea:focus,
  .post-show-shell textarea.ps-comment-textarea:focus {
    border-color: #bfdbfe !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-textarea::placeholder,
  .post-show-shell textarea.ps-comment-textarea::placeholder {
    color: #94a3b8 !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-suggestions {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-suggestions-title {
    color: #94a3b8 !important;
    font-size: 12px !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-suggestions button {
    height: 28px !important;
    padding: 0 10px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 999px !important;
    background: #ffffff !important;
    color: #64748b !important;
    font-size: 12px !important;
    font-weight: 400 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    transform: none !important;
    transition: background-color .12s ease, color .12s ease, border-color .12s ease !important;
  }

  .post-show-shell .ps-comment-suggestions button:hover,
  .post-show-shell .ps-comment-suggestions button:focus-visible {
    background: #f8fafc !important;
    border-color: #dbe3ef !important;
    color: #334155 !important;
    outline: none !important;
    transform: none !important;
  }

  .post-show-shell #show-comment-form .ps-comment-toolbar {
    grid-column: 1 / -1 !important;
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    margin: 0 !important;
    padding: 0 0 0 54px !important;
    border: 0 !important;
  }

  .post-show-shell #show-comment-form .ps-comment-toolbar::after {
    content: "Ctrl + Enter" !important;
    margin-left: auto !important;
    color: #cbd5e1 !important;
    font-size: 11px !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-tool,
  .post-show-shell .ps-comment-send,
  .post-show-shell .ps-comment-mini-btn,
  .post-show-shell .ps-comment-mini-image-btn {
    min-width: 34px !important;
    height: 34px !important;
    min-height: 34px !important;
    padding: 0 11px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 999px !important;
    background: #ffffff !important;
    color: #64748b !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 400 !important;
    box-shadow: none !important;
    transform: none !important;
    transition: background-color .12s ease, color .12s ease, border-color .12s ease !important;
  }

  .post-show-shell .ps-comment-tool svg,
  .post-show-shell .ps-comment-send svg,
  .post-show-shell .ps-comment-mini-image-btn svg {
    width: 16px !important;
    height: 16px !important;
  }

  .post-show-shell .ps-comment-tool:hover,
  .post-show-shell .ps-comment-tool:focus-visible,
  .post-show-shell .ps-comment-mini-btn:hover,
  .post-show-shell .ps-comment-mini-btn:focus-visible,
  .post-show-shell .ps-comment-mini-image-btn:hover,
  .post-show-shell .ps-comment-mini-image-btn:focus-visible {
    background: #f8fafc !important;
    color: #334155 !important;
    border-color: #dbe3ef !important;
    outline: none !important;
    transform: none !important;
  }

  .post-show-shell .ps-comment-send,
  .post-show-shell .ps-comment-mini-btn--primary {
    margin-left: auto !important;
    min-width: 86px !important;
    border-color: #2563eb !important;
    background: #2563eb !important;
    color: #ffffff !important;
    font-weight: 500 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-send:hover,
  .post-show-shell .ps-comment-send:focus-visible,
  .post-show-shell .ps-comment-mini-btn--primary:hover,
  .post-show-shell .ps-comment-mini-btn--primary:focus-visible {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
    outline: none !important;
    transform: none !important;
  }

  .post-show-shell .ps-comment-image-preview,
  .post-show-shell .ps-comment-gif-preview {
    margin: 0 !important;
    padding: 8px !important;
    border: 1px dashed #dbe3ef !important;
    border-radius: 12px !important;
    background: #f8fafc !important;
    box-shadow: none !important;
  }

  /* Yorum listesi */
  .post-show-shell .ps-comments-list {
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
    background: transparent !important;
  }

  .post-show-shell .ps-comment-item {
    position: relative !important;
    display: grid !important;
    grid-template-columns: 42px minmax(0, 1fr) !important;
    gap: 12px !important;
    padding: 14px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 18px !important;
    background: #ffffff !important;
    box-shadow: none !important;
    overflow: visible !important;
    transform: none !important;
  }

  .post-show-shell .ps-comment-item::before,
  .post-show-shell .ps-comment-item::after {
    display: none !important;
    content: none !important;
  }

  .post-show-shell .ps-comment-avatar {
    width: 42px !important;
    height: 42px !important;
    min-width: 42px !important;
    min-height: 42px !important;
    border-radius: 50% !important;
    border: 1px solid #e5e7eb !important;
    background: #f8fafc !important;
    color: #475569 !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    box-shadow: none !important;
    overflow: hidden !important;
  }

  .post-show-shell .ps-comment-avatar img {
    width: 100% !important;
    height: 100% !important;
    display: block !important;
    object-fit: cover !important;
  }

  .post-show-shell .ps-comment-body {
    min-width: 0 !important;
  }

  .post-show-shell .ps-comment-meta {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
    margin: 0 0 6px !important;
    line-height: 1.3 !important;
  }

  .post-show-shell .ps-comment-author {
    color: #111827 !important;
    font-size: 13px !important;
    line-height: 1.3 !important;
    font-weight: 500 !important;
    letter-spacing: 0 !important;
  }

  .post-show-shell .ps-comment-role {
    min-height: 20px !important;
    padding: 0 7px !important;
    border-radius: 999px !important;
    background: #ecfdf5 !important;
    color: #059669 !important;
    font-size: 11px !important;
    line-height: 1 !important;
    font-weight: 400 !important;
    display: inline-flex !important;
    align-items: center !important;
  }

  .post-show-shell .ps-comment-time {
    color: #94a3b8 !important;
    font-size: 12px !important;
    line-height: 1.3 !important;
    font-weight: 400 !important;
    background: transparent !important;
  }

  .post-show-shell .ps-comment-text {
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    color: #334155 !important;
    font-size: 14px !important;
    line-height: 1.65 !important;
    font-weight: 400 !important;
    white-space: normal !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-text-line {
    margin: 0 !important;
  }

  .post-show-shell .ps-comment-text-line + .ps-comment-text-line {
    margin-top: 6px !important;
  }

  .post-show-shell .ps-comment-image {
    margin: 8px 0 0 !important;
  }

  .post-show-shell .ps-comment-image img {
    max-width: min(280px, 100%) !important;
    border-radius: 12px !important;
    border: 1px solid #e5e7eb !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-actions,
  .post-show-shell .ps-comment-actions--reply {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
    margin: 10px 0 0 !important;
  }

  .post-show-shell .ps-comment-votes {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-vote-btn,
  .post-show-shell .ps-comment-action,
  .post-show-shell .ps-comment-more-trigger,
  .post-show-shell .ps-replies-toggle {
    width: auto !important;
    min-width: 30px !important;
    height: 30px !important;
    min-height: 30px !important;
    padding: 0 8px !important;
    margin: 0 !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 400 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    box-shadow: none !important;
    transform: none !important;
    transition: background-color .12s ease, color .12s ease !important;
  }

  .post-show-shell .ps-comment-vote-btn svg,
  .post-show-shell .ps-comment-action svg,
  .post-show-shell .ps-comment-more-trigger svg,
  .post-show-shell .ps-replies-toggle svg {
    width: 16px !important;
    height: 16px !important;
    display: block !important;
  }

  .post-show-shell .ps-comment-vote-btn:hover,
  .post-show-shell .ps-comment-vote-btn:focus-visible,
  .post-show-shell .ps-comment-action:hover,
  .post-show-shell .ps-comment-action:focus-visible,
  .post-show-shell .ps-comment-more-trigger:hover,
  .post-show-shell .ps-comment-more-trigger:focus-visible,
  .post-show-shell .ps-replies-toggle:hover,
  .post-show-shell .ps-replies-toggle:focus-visible,
  .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger {
    background: #f1f5f9 !important;
    color: #334155 !important;
    outline: none !important;
    transform: none !important;
  }

  .post-show-shell .ps-comment-vote-count {
    margin: 0 !important;
    color: #64748b !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-action.ps-comment-reply-icon-btn::after {
    content: "Yanıtla" !important;
    margin-left: 3px !important;
    color: inherit !important;
    font-size: 12px !important;
    font-weight: 400 !important;
  }

  .post-show-shell .ps-comment-more {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-comment-more-menu {
    position: absolute !important;
    top: calc(100% + 6px) !important;
    right: 0 !important;
    left: auto !important;
    z-index: 90 !important;
    min-width: 158px !important;
    padding: 6px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    box-shadow: none !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
  }

  .post-show-shell .ps-comment-more-menu[hidden] {
    display: none !important;
  }

  .post-show-shell .ps-comment-more-menu:not([hidden]) {
    display: flex !important;
    flex-direction: column !important;
    gap: 2px !important;
  }

  .post-show-shell .ps-comment-more-item {
    width: 100% !important;
    min-height: 34px !important;
    padding: 0 9px !important;
    border: 0 !important;
    border-radius: 9px !important;
    background: transparent !important;
    color: #475569 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 8px !important;
    font-size: 12px !important;
    line-height: 1 !important;
    font-weight: 400 !important;
    text-align: left !important;
    text-decoration: none !important;
    cursor: pointer !important;
  }

  .post-show-shell .ps-comment-more-item:hover,
  .post-show-shell .ps-comment-more-item:focus-visible {
    background: #f8fafc !important;
    color: #111827 !important;
    outline: none !important;
  }

  .post-show-shell .ps-comment-more-item--danger {
    color: #dc2626 !important;
  }

  .post-show-shell .ps-comment-more-item--danger:hover,
  .post-show-shell .ps-comment-more-item--danger:focus-visible {
    background: #fef2f2 !important;
    color: #b91c1c !important;
  }

  .post-show-shell .ps-comment-more-item svg {
    width: 15px !important;
    height: 15px !important;
    flex: 0 0 15px !important;
  }

  /* Yanıt ve düzenleme */
  .post-show-shell .ps-comment-edit-form,
  .post-show-shell .ps-comment-reply-form {
    display: none !important;
    margin: 10px 0 0 !important;
  }

  .post-show-shell .ps-comment-edit-form.is-open,
  .post-show-shell .ps-comment-reply-form.is-open {
    display: block !important;
  }

  .post-show-shell .ps-comment-mini-box,
  .post-show-shell .ps-comment-mini-box--reply {
    display: grid !important;
    grid-template-columns: 34px minmax(0, 1fr) !important;
    gap: 9px !important;
    padding: 10px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 14px !important;
    background: #f8fafc !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mini-avatar {
    width: 34px !important;
    height: 34px !important;
    border-radius: 50% !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    overflow: hidden !important;
  }

  .post-show-shell .ps-comment-mini-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
  }

  .post-show-shell .ps-comment-mini-textarea {
    grid-column: 2 !important;
    width: 100% !important;
    min-height: 70px !important;
    max-height: 220px !important;
    padding: 10px 11px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    color: #111827 !important;
    font-size: 13px !important;
    line-height: 1.55 !important;
    font-weight: 400 !important;
    resize: none !important;
    outline: none !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mini-textarea:focus {
    border-color: #bfdbfe !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-comment-mini-actions {
    grid-column: 2 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    flex-wrap: wrap !important;
    gap: 7px !important;
    margin: 0 !important;
  }

  .post-show-shell .ps-replies {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 8px !important;
    margin: 12px 0 0 !important;
    padding: 0 0 0 16px !important;
    border-left: 1px solid #e5e7eb !important;
  }

  .post-show-shell .ps-replies::before {
    display: none !important;
    content: none !important;
  }

  .post-show-shell .ps-replies[hidden] {
    display: none !important;
  }

  .post-show-shell .ps-replies > .ps-comment-item {
    grid-template-columns: 36px minmax(0, 1fr) !important;
    gap: 10px !important;
    padding: 12px !important;
    border-radius: 14px !important;
    background: #fbfdff !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-replies > .ps-comment-item .ps-comment-avatar {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    min-height: 36px !important;
  }

  .post-show-shell .ps-replies-toggle {
    color: #2563eb !important;
  }

  .post-show-shell .ps-comments-list > .text-sm.text-gray-500 {
    padding: 16px !important;
    border: 1px dashed #cbd5e1 !important;
    border-radius: 16px !important;
    background: #f8fafc !important;
    color: #64748b !important;
    text-align: center !important;
    font-size: 13px !important;
    font-weight: 400 !important;
  }

  /* Dark mode: sadece dark'ta koyu yorum kutusu */
  html.dark .post-show-shell .ps-comments-title,
  body.dark .post-show-shell .ps-comments-title,
  .dark .post-show-shell .ps-comments-title,
  [data-theme="dark"] .post-show-shell .ps-comments-title,
  html.dark .post-show-shell .ps-comment-author,
  body.dark .post-show-shell .ps-comment-author,
  .dark .post-show-shell .ps-comment-author,
  [data-theme="dark"] .post-show-shell .ps-comment-author {
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-comments-count-badge,
  body.dark .post-show-shell .ps-comments-count-badge,
  .dark .post-show-shell .ps-comments-count-badge,
  [data-theme="dark"] .post-show-shell .ps-comments-count-badge,
  html.dark .post-show-shell .ps-comment-compose-counter,
  body.dark .post-show-shell .ps-comment-compose-counter,
  .dark .post-show-shell .ps-comment-compose-counter,
  [data-theme="dark"] .post-show-shell .ps-comment-compose-counter {
    background: #151922 !important;
    border-color: rgba(255,255,255,.09) !important;
    color: #94a3b8 !important;
  }

  html.dark .post-show-shell .ps-comments-sort,
  body.dark .post-show-shell .ps-comments-sort,
  .dark .post-show-shell .ps-comments-sort,
  [data-theme="dark"] .post-show-shell .ps-comments-sort,
  html.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  body.dark .post-show-shell #show-comment-form .ps-comment-form-box,
  .dark .post-show-shell #show-comment-form .ps-comment-form-box,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-form-box,
  html.dark .post-show-shell .ps-comment-item,
  body.dark .post-show-shell .ps-comment-item,
  .dark .post-show-shell .ps-comment-item,
  [data-theme="dark"] .post-show-shell .ps-comment-item,
  html.dark .post-show-shell .ps-comment-more-menu,
  body.dark .post-show-shell .ps-comment-more-menu,
  .dark .post-show-shell .ps-comment-more-menu,
  [data-theme="dark"] .post-show-shell .ps-comment-more-menu {
    background: #0f1115 !important;
    border-color: rgba(255,255,255,.08) !important;
    color: #e5e7eb !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-replies > .ps-comment-item,
  body.dark .post-show-shell .ps-replies > .ps-comment-item,
  .dark .post-show-shell .ps-replies > .ps-comment-item,
  [data-theme="dark"] .post-show-shell .ps-replies > .ps-comment-item {
    background: #111318 !important;
    border-color: rgba(255,255,255,.08) !important;
  }

  html.dark .post-show-shell #show-comment-form .ps-comment-textarea,
  body.dark .post-show-shell #show-comment-form .ps-comment-textarea,
  .dark .post-show-shell #show-comment-form .ps-comment-textarea,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-textarea,
  html.dark .post-show-shell .ps-comment-mini-textarea,
  body.dark .post-show-shell .ps-comment-mini-textarea,
  .dark .post-show-shell .ps-comment-mini-textarea,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-textarea,
  html.dark .post-show-shell .ps-comment-mini-box,
  body.dark .post-show-shell .ps-comment-mini-box,
  .dark .post-show-shell .ps-comment-mini-box,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-box,
  html.dark .post-show-shell .ps-comment-tool,
  body.dark .post-show-shell .ps-comment-tool,
  .dark .post-show-shell .ps-comment-tool,
  [data-theme="dark"] .post-show-shell .ps-comment-tool,
  html.dark .post-show-shell .ps-comment-mini-btn,
  body.dark .post-show-shell .ps-comment-mini-btn,
  .dark .post-show-shell .ps-comment-mini-btn,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-btn,
  html.dark .post-show-shell .ps-comment-mini-image-btn,
  body.dark .post-show-shell .ps-comment-mini-image-btn,
  .dark .post-show-shell .ps-comment-mini-image-btn,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-image-btn,
  html.dark .post-show-shell .ps-comment-suggestions button,
  body.dark .post-show-shell .ps-comment-suggestions button,
  .dark .post-show-shell .ps-comment-suggestions button,
  [data-theme="dark"] .post-show-shell .ps-comment-suggestions button {
    background: #151922 !important;
    border-color: rgba(255,255,255,.08) !important;
    color: #e5e7eb !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-comment-text,
  body.dark .post-show-shell .ps-comment-text,
  .dark .post-show-shell .ps-comment-text,
  [data-theme="dark"] .post-show-shell .ps-comment-text {
    color: #d1d5db !important;
    background: transparent !important;
  }

  html.dark .post-show-shell .ps-comment-compose-kicker,
  body.dark .post-show-shell .ps-comment-compose-kicker,
  .dark .post-show-shell .ps-comment-compose-kicker,
  [data-theme="dark"] .post-show-shell .ps-comment-compose-kicker,
  html.dark .post-show-shell .ps-comment-time,
  body.dark .post-show-shell .ps-comment-time,
  .dark .post-show-shell .ps-comment-time,
  [data-theme="dark"] .post-show-shell .ps-comment-time,
  html.dark .post-show-shell .ps-comment-suggestions-title,
  body.dark .post-show-shell .ps-comment-suggestions-title,
  .dark .post-show-shell .ps-comment-suggestions-title,
  [data-theme="dark"] .post-show-shell .ps-comment-suggestions-title,
  html.dark .post-show-shell #show-comment-form .ps-comment-toolbar::after,
  body.dark .post-show-shell #show-comment-form .ps-comment-toolbar::after,
  .dark .post-show-shell #show-comment-form .ps-comment-toolbar::after,
  [data-theme="dark"] .post-show-shell #show-comment-form .ps-comment-toolbar::after {
    color: #94a3b8 !important;
  }

  html.dark .post-show-shell .ps-comment-vote-btn,
  body.dark .post-show-shell .ps-comment-vote-btn,
  .dark .post-show-shell .ps-comment-vote-btn,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn,
  html.dark .post-show-shell .ps-comment-action,
  body.dark .post-show-shell .ps-comment-action,
  .dark .post-show-shell .ps-comment-action,
  [data-theme="dark"] .post-show-shell .ps-comment-action,
  html.dark .post-show-shell .ps-comment-more-trigger,
  body.dark .post-show-shell .ps-comment-more-trigger,
  .dark .post-show-shell .ps-comment-more-trigger,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger {
    color: #94a3b8 !important;
  }

  html.dark .post-show-shell .ps-comment-vote-btn:hover,
  body.dark .post-show-shell .ps-comment-vote-btn:hover,
  .dark .post-show-shell .ps-comment-vote-btn:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-vote-btn:hover,
  html.dark .post-show-shell .ps-comment-action:hover,
  body.dark .post-show-shell .ps-comment-action:hover,
  .dark .post-show-shell .ps-comment-action:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-action:hover,
  html.dark .post-show-shell .ps-comment-more-trigger:hover,
  body.dark .post-show-shell .ps-comment-more-trigger:hover,
  .dark .post-show-shell .ps-comment-more-trigger:hover,
  [data-theme="dark"] .post-show-shell .ps-comment-more-trigger:hover,
  html.dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  body.dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  .dark .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger,
  [data-theme="dark"] .post-show-shell .ps-comment-more.is-open .ps-comment-more-trigger {
    background: rgba(255,255,255,.06) !important;
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-comment-send,
  body.dark .post-show-shell .ps-comment-send,
  .dark .post-show-shell .ps-comment-send,
  [data-theme="dark"] .post-show-shell .ps-comment-send,
  html.dark .post-show-shell .ps-comment-mini-btn--primary,
  body.dark .post-show-shell .ps-comment-mini-btn--primary,
  .dark .post-show-shell .ps-comment-mini-btn--primary,
  [data-theme="dark"] .post-show-shell .ps-comment-mini-btn--primary {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
  }

  @media (max-width: 760px) {
    .post-show-shell .ps-comments-top {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 10px !important;
    }

    .post-show-shell .ps-comments-sort {
      width: 100% !important;
      justify-content: space-between !important;
    }

    .post-show-shell #show-comment-form .ps-comment-form-box,
    .post-show-shell .ps-comment-item {
      grid-template-columns: 1fr !important;
      padding: 12px !important;
      border-radius: 16px !important;
    }

    .post-show-shell #show-comment-form .ps-comment-toolbar {
      padding-left: 0 !important;
    }

    .post-show-shell #show-comment-form .ps-comment-toolbar::after {
      width: 100% !important;
      margin-left: 0 !important;
      order: 10 !important;
    }

    .post-show-shell .ps-comment-mini-box,
    .post-show-shell .ps-comment-mini-box--reply {
      grid-template-columns: 1fr !important;
    }

    .post-show-shell .ps-comment-mini-textarea,
    .post-show-shell .ps-comment-mini-actions {
      grid-column: 1 !important;
    }

    .post-show-shell .ps-replies {
      padding-left: 10px !important;
    }
  }
</style>

<style>
/* ============================================================
   OGRAFI FINAL MASTER OVERRIDE — v3
   Başlık, resim, kaynak, tepkiler, aksiyon bar düzeltmeleri
   ============================================================ */

/* 1. BAŞLIK — kalın */
.post-show-shell .ps-post-title {
  font-weight: 800 !important;
  font-size: 20px !important;
  line-height: 1.42 !important;
  color: #0f172a !important;
  margin: 0 0 14px !important;
}

/* 2. RESİM — sağ/sol daralt, ortala, biraz genişlet */
.post-show-shell .ps-post-image,
.post-show-shell .ps-full-media,
.post-show-shell .ps-full-gallery {
  width: calc(100% - 20px) !important;
  margin-left: 10px !important;
  margin-right: 10px !important;
  margin-bottom: 14px !important;
  border-radius: 14px !important;
  overflow: hidden !important;
  display: block !important;
}

.post-show-shell .ps-post-image img,
.post-show-shell .ps-full-media img,
.post-show-shell .ps-full-media video,
.post-show-shell .ps-full-media iframe {
  width: 100% !important;
  height: auto !important;
  display: block !important;
  border-radius: 14px !important;
  object-fit: cover !important;
}

/* 3. YAZI — font-size 16px */
.post-show-shell .ps-post-body {
  font-size: 16px !important;
  line-height: 1.7 !important;
  color: #1e293b !important;
  margin: 0 0 14px !important;
}

/* 4. KAYNAK KUTUSU — sağ/sol daralt */
.post-show-shell .ps-source-link {
  margin-left: 10px !important;
  margin-right: 10px !important;
  width: calc(100% - 20px) !important;
  box-sizing: border-box !important;
}

/* 5. AKSİYON BAR — tepkiler + ikonlar + view aynı satırda */
.post-show-shell .ps-actions-bar {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: space-between !important;
  flex-wrap: nowrap !important;
  gap: 8px !important;
  padding: 10px 0 6px !important;
  margin: 0 !important;
  overflow: visible !important;
}

.post-show-shell .ps-reaction-row {
  flex: 1 1 auto !important;
  min-width: 0 !important;
  display: flex !important;
  align-items: center !important;
  gap: 6px !important;
  flex-wrap: wrap !important;
  overflow: visible !important;
}

.post-show-shell .ps-action-row {
  flex: 0 0 auto !important;
  display: flex !important;
  align-items: center !important;
  gap: 4px !important;
  overflow: visible !important;
  grid-column: unset !important;
  grid-row: unset !important;
}

.post-show-shell .ps-view-count {
  flex: 0 0 auto !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 4px !important;
  font-size: 12px !important;
  color: #6b7280 !important;
  grid-column: unset !important;
  grid-row: unset !important;
  white-space: nowrap !important;
}

/* 6. TEPKİ PİLLS — eklenmiş tepki gri arka plan */
.post-show-shell .ps-reaction-pill {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 5px !important;
  height: 32px !important;
  min-height: 32px !important;
  padding: 0 10px !important;
  border-radius: 999px !important;
  border: 0 !important;
  background: #f3f4f6 !important;
  color: #111827 !important;
  font-size: 13px !important;
  font-weight: 500 !important;
  cursor: pointer !important;
  box-shadow: none !important;
}

.post-show-shell .ps-reaction-pill.is-active,
.post-show-shell .ps-reaction-pill[aria-pressed="true"],
.post-show-shell .ps-reaction-pill--active {
  background: #e5e7eb !important;
  color: #111827 !important;
}

.post-show-shell .ps-reaction-pill:hover,
.post-show-shell .ps-reaction-pill:focus-visible {
  background: #e5e7eb !important;
}

.post-show-shell .ps-reaction-pill img,
.post-show-shell .ps-reaction-pill svg {
  width: 20px !important;
  height: 20px !important;
  display: block !important;
  object-fit: cover !important;
}

/* 7. TEPKİ TETİKLEYİCİ (+ emoji) */
.post-show-shell .ps-reaction-trigger {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 32px !important;
  height: 32px !important;
  min-width: 32px !important;
  padding: 0 !important;
  border-radius: 999px !important;
  border: 0 !important;
  background: #f3f4f6 !important;
  color: #6b7280 !important;
  cursor: pointer !important;
}

.post-show-shell .ps-reaction-trigger:hover,
.post-show-shell .ps-reaction-trigger[aria-expanded="true"] {
  background: #e5e7eb !important;
  color: #111827 !important;
}

/* 8. TEPKİ MENÜSÜ — resim 4 gibi: beyaz kart, 4 sütun grid, büyük ikonlar */
.post-show-shell .ps-reaction-menu,
.post-show-shell .ps-reaction-more-menu {
  position: absolute !important;
  left: 0 !important;
  right: auto !important;
  top: calc(100% + 8px) !important;
  z-index: 9999 !important;
  min-width: 230px !important;
  width: auto !important;
  max-width: calc(100vw - 24px) !important;
  padding: 12px !important;
  border-radius: 14px !important;
  border: 1px solid #e5e7eb !important;
  background: #ffffff !important;
  box-shadow: none !important;
  overflow: visible !important;
}

.post-show-shell .ps-reaction-menu[hidden],
.post-show-shell .ps-reaction-more-menu[hidden] {
  display: none !important;
}

.post-show-shell .ps-reaction-menu:not([hidden]),
.post-show-shell .ps-reaction-more-menu:not([hidden]) {
  display: grid !important;
  grid-template-columns: repeat(4, 44px) !important;
  gap: 8px !important;
  align-items: center !important;
  justify-content: start !important;
}

.post-show-shell .ps-reaction-menu-title,
.post-show-shell .ps-reaction-more-title {
  grid-column: 1 / -1 !important;
  color: #6b7280 !important;
  font-size: 12px !important;
  font-weight: 500 !important;
  line-height: 1.2 !important;
  margin: 0 0 4px !important;
  text-transform: uppercase !important;
  letter-spacing: 0.04em !important;
}

.post-show-shell .ps-reaction-menu .ps-reaction-form,
.post-show-shell .ps-reaction-more-menu .ps-reaction-form {
  width: 44px !important;
  height: 44px !important;
  display: inline-flex !important;
  margin: 0 !important;
}

.post-show-shell .ps-reaction-option,
.post-show-shell .ps-reaction-more-item {
  width: 44px !important;
  height: 44px !important;
  min-width: 44px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border: 0 !important;
  border-radius: 10px !important;
  background: transparent !important;
  color: #111827 !important;
  padding: 0 !important;
  cursor: pointer !important;
  transition: background 0.12s ease, transform 0.12s ease !important;
}

.post-show-shell .ps-reaction-option:hover,
.post-show-shell .ps-reaction-option:focus-visible,
.post-show-shell .ps-reaction-more-item:hover,
.post-show-shell .ps-reaction-more-item:focus-visible {
  background: #f3f4f6 !important;
  transform: scale(1.1) !important;
  outline: none !important;
}

.post-show-shell .ps-reaction-option:active,
.post-show-shell .ps-reaction-more-item:active {
  background: #e5e7eb !important;
  transform: scale(0.96) !important;
}

.post-show-shell .ps-reaction-option img,
.post-show-shell .ps-reaction-option svg,
.post-show-shell .ps-reaction-more-item img,
.post-show-shell .ps-reaction-more-item svg {
  width: 32px !important;
  height: 32px !important;
  max-width: 32px !important;
  max-height: 32px !important;
  display: block !important;
  object-fit: cover !important;
  border-radius: 6px !important;
  flex: 0 0 auto !important;
}

/* Sağa ok (next page) */
.post-show-shell .ps-reaction-more-footer {
  grid-column: 1 / -1 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  margin-top: 4px !important;
}

/* 9. MOBİL OPTİMİZASYON */
@media (max-width: 640px) {
  .post-show-shell .ps-post-title {
    font-size: 18px !important;
    font-weight: 800 !important;
  }

  .post-show-shell .ps-post-body {
    font-size: 15px !important;
  }

  /* Resim sağ/sol mobilde de daralt */
  .post-show-shell .ps-post-image,
  .post-show-shell .ps-full-media,
  .post-show-shell .ps-full-gallery {
    width: calc(100% - 16px) !important;
    margin-left: 8px !important;
    margin-right: 8px !important;
    border-radius: 12px !important;
  }

  .post-show-shell .ps-source-link {
    margin-left: 8px !important;
    margin-right: 8px !important;
    width: calc(100% - 16px) !important;
  }

  .post-show-shell .ps-actions-bar {
    flex-wrap: wrap !important;
    gap: 6px !important;
    padding: 8px 0 4px !important;
  }

  /* Mobilde tepki menüsü — resim 4: beyaz popup kart */
  .post-show-shell .ps-reaction-menu,
  .post-show-shell .ps-reaction-more-menu {
    position: fixed !important;
    left: 50% !important;
    right: auto !important;
    top: auto !important;
    bottom: 80px !important;
    transform: translateX(-50%) !important;
    z-index: 99999 !important;
    min-width: 260px !important;
    max-width: calc(100vw - 24px) !important;
    padding: 14px !important;
    border-radius: 16px !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-reaction-menu:not([hidden]),
  .post-show-shell .ps-reaction-more-menu:not([hidden]) {
    grid-template-columns: repeat(4, 48px) !important;
    gap: 10px !important;
  }

  .post-show-shell .ps-reaction-option,
  .post-show-shell .ps-reaction-more-item {
    width: 48px !important;
    height: 48px !important;
    min-width: 48px !important;
    border-radius: 12px !important;
  }

  .post-show-shell .ps-reaction-option img,
  .post-show-shell .ps-reaction-option svg,
  .post-show-shell .ps-reaction-more-item img,
  .post-show-shell .ps-reaction-more-item svg {
    width: 36px !important;
    height: 36px !important;
    max-width: 36px !important;
    max-height: 36px !important;
  }

  .post-show-shell .ps-reaction-menu .ps-reaction-form,
  .post-show-shell .ps-reaction-more-menu .ps-reaction-form {
    width: 48px !important;
    height: 48px !important;
  }

  /* Mobilde aksiyon ikonları kompakt */
  .post-show-shell .ps-action-row {
    gap: 2px !important;
  }

  .post-show-shell .ps-view-count {
    font-size: 11px !important;
  }
}

/* Dark mod */
html.dark .post-show-shell .ps-reaction-menu,
body.dark .post-show-shell .ps-reaction-menu,
.dark .post-show-shell .ps-reaction-menu,
[data-theme="dark"] .post-show-shell .ps-reaction-menu,
html.dark .post-show-shell .ps-reaction-more-menu,
body.dark .post-show-shell .ps-reaction-more-menu,
.dark .post-show-shell .ps-reaction-more-menu,
[data-theme="dark"] .post-show-shell .ps-reaction-more-menu {
  background: #1e2330 !important;
  border-color: rgba(255,255,255,0.12) !important;
  box-shadow: none !important;
}

html.dark .post-show-shell .ps-reaction-menu-title,
body.dark .post-show-shell .ps-reaction-menu-title,
.dark .post-show-shell .ps-reaction-menu-title,
[data-theme="dark"] .post-show-shell .ps-reaction-menu-title {
  color: #94a3b8 !important;
}

html.dark .post-show-shell .ps-reaction-option:hover,
html.dark .post-show-shell .ps-reaction-more-item:hover,
body.dark .post-show-shell .ps-reaction-option:hover,
body.dark .post-show-shell .ps-reaction-more-item:hover,
.dark .post-show-shell .ps-reaction-option:hover,
.dark .post-show-shell .ps-reaction-more-item:hover,
[data-theme="dark"] .post-show-shell .ps-reaction-option:hover,
[data-theme="dark"] .post-show-shell .ps-reaction-more-item:hover {
  background: rgba(255,255,255,0.10) !important;
}

html.dark .post-show-shell .ps-reaction-pill,
body.dark .post-show-shell .ps-reaction-pill,
.dark .post-show-shell .ps-reaction-pill,
[data-theme="dark"] .post-show-shell .ps-reaction-pill {
  background: rgba(255,255,255,0.10) !important;
  color: #e5e7eb !important;
}

html.dark .post-show-shell .ps-reaction-trigger,
body.dark .post-show-shell .ps-reaction-trigger,
.dark .post-show-shell .ps-reaction-trigger,
[data-theme="dark"] .post-show-shell .ps-reaction-trigger {
  background: rgba(255,255,255,0.10) !important;
  color: #94a3b8 !important;
}

/* =========================================================
   FINAL USER REQUEST:
   - Header altı üst boşluk azaltıldı
   - Dalgalı / shimmer yükleme efekti eklendi
   - Reaction satırı aksiyon ikonlarının üstüne alındı
   - Yorum / bookmark / share ikonları sola hizalandı
   ========================================================= */

.ps-layout.post-show-shell {
  padding-top: 24px !important;
}

.post-show-shell .ps-main {
  gap: 10px !important;
}

.post-show-shell .ps-post-card-inner {
  padding-top: 14px !important;
}

.post-show-shell .ps-post-author-row {
  margin-bottom: 10px !important;
}

.post-show-shell .ps-post-title {
  margin-bottom: 10px !important;
}

.post-show-shell .ps-post-body {
  margin-bottom: 10px !important;
}

.post-show-shell .ps-wave-loader {
  width: 100% !important;
  max-width: 656px !important;
  margin: 0 auto 10px !important;
  padding: 14px 16px !important;
  border-radius: 18px !important;
  background: #ffffff !important;
  display: none !important;
  overflow: hidden !important;
}

.post-show-shell.is-loading .ps-wave-loader {
  display: block !important;
}

.post-show-shell .ps-wave-loader-inner {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
}

.post-show-shell .ps-wave-avatar,
.post-show-shell .ps-wave-line,
.post-show-shell .ps-wave-media {
  position: relative !important;
  overflow: hidden !important;
  background: #eef0f3 !important;
}

.post-show-shell .ps-wave-avatar {
  width: 44px !important;
  height: 44px !important;
  border-radius: 999px !important;
  flex: 0 0 44px !important;
}

.post-show-shell .ps-wave-copy {
  flex: 1 1 auto !important;
  display: flex !important;
  flex-direction: column !important;
  gap: 9px !important;
  min-width: 0 !important;
}

.post-show-shell .ps-wave-line {
  height: 10px !important;
  border-radius: 999px !important;
}

.post-show-shell .ps-wave-line--wide {
  width: 72% !important;
}

.post-show-shell .ps-wave-line--short {
  width: 42% !important;
}

.post-show-shell .ps-wave-media {
  width: 100% !important;
  height: 130px !important;
  margin-top: 14px !important;
  border-radius: 14px !important;
}

.post-show-shell .ps-wave-avatar::after,
.post-show-shell .ps-wave-line::after,
.post-show-shell .ps-wave-media::after {
  content: "" !important;
  position: absolute !important;
  inset: 0 !important;
  transform: translateX(-100%) !important;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.72), transparent) !important;
  animation: psWaveLoading 1.25s ease-in-out infinite !important;
}

@keyframes psWaveLoading {
  100% {
    transform: translateX(100%);
  }
}

.post-show-shell .ps-actions-bar {
  position: relative !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: stretch !important;
  justify-content: flex-start !important;
  gap: 6px !important;
  padding: 4px 18px 14px !important;
  margin: 0 -18px !important;
}

.post-show-shell .ps-reaction-row {
  order: 1 !important;
  width: 100% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: flex-start !important;
  gap: 8px !important;
  margin: 0 !important;
  padding: 0 !important;
}

.post-show-shell .ps-action-row {
  order: 2 !important;
  width: 100% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: flex-start !important;
  gap: 12px !important;
  margin: 0 !important;
  padding: 0 !important;
}

.post-show-shell .ps-action-row > * {
  flex: 0 0 auto !important;
}

.post-show-shell .ps-action-row form {
  margin: 0 !important;
}

.post-show-shell .ps-action-row .ps-action-btn,
.post-show-shell .ps-action-row .ps-bookmark-btn,
.post-show-shell .ps-action-row .ps-action-btn--share {
  margin-left: 0 !important;
  margin-right: 0 !important;
}

.post-show-shell .ps-view-count {
  order: 2 !important;
  position: absolute !important;
  right: 6px !important;
  bottom: 21px !important;
  margin: 0 !important;
}

@media (max-width: 640px) {
  .ps-layout.post-show-shell {
    padding-top: 8px !important;
  }

  .post-show-shell .ps-wave-loader {
    max-width: calc(100% - 16px) !important;
    margin: 0 8px 8px !important;
    border-radius: 14px !important;
    padding: 12px !important;
  }

  .post-show-shell .ps-wave-media {
    height: 96px !important;
  }

  .post-show-shell .ps-actions-bar {
    padding: 4px 12px 12px !important;
    margin-left: -12px !important;
    margin-right: -12px !important;
    gap: 5px !important;
  }

  .post-show-shell .ps-reaction-row {
    gap: 6px !important;
  }

  .post-show-shell .ps-action-row {
    justify-content: flex-start !important;
    gap: 6px !important;
    padding-right: 78px !important;
  }

  .post-show-shell .ps-view-count {
    right: 6px !important;
    bottom: 20px !important;
  }
}

html.dark .post-show-shell .ps-wave-loader,
body.dark .post-show-shell .ps-wave-loader,
.dark .post-show-shell .ps-wave-loader,
[data-theme="dark"] .post-show-shell .ps-wave-loader {
  background: #0f172a !important;
}

html.dark .post-show-shell .ps-wave-avatar,
html.dark .post-show-shell .ps-wave-line,
html.dark .post-show-shell .ps-wave-media,
body.dark .post-show-shell .ps-wave-avatar,
body.dark .post-show-shell .ps-wave-line,
body.dark .post-show-shell .ps-wave-media,
.dark .post-show-shell .ps-wave-avatar,
.dark .post-show-shell .ps-wave-line,
.dark .post-show-shell .ps-wave-media,
[data-theme="dark"] .post-show-shell .ps-wave-avatar,
[data-theme="dark"] .post-show-shell .ps-wave-line,
[data-theme="dark"] .post-show-shell .ps-wave-media {
  background: rgba(255,255,255,0.10) !important;
}

html.dark .post-show-shell .ps-wave-avatar::after,
html.dark .post-show-shell .ps-wave-line::after,
html.dark .post-show-shell .ps-wave-media::after,
body.dark .post-show-shell .ps-wave-avatar::after,
body.dark .post-show-shell .ps-wave-line::after,
body.dark .post-show-shell .ps-wave-media::after,
.dark .post-show-shell .ps-wave-avatar::after,
.dark .post-show-shell .ps-wave-line::after,
.dark .post-show-shell .ps-wave-media::after,
[data-theme="dark"] .post-show-shell .ps-wave-avatar::after,
[data-theme="dark"] .post-show-shell .ps-wave-line::after,
[data-theme="dark"] .post-show-shell .ps-wave-media::after {
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent) !important;
}

</style>
@endpush


@push('head')
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* =========================================================
   OGRAFI FINAL POLISH — ROBOTO + TABLE + SEO UI + REACTION + WAVE FIX
   ========================================================= */
body,
.post-show-shell,
.post-show-shell :where(a, button, input, textarea, select) {
  font-family: "Roboto", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif !important;
  letter-spacing: -0.012em !important;
}

.post-show-shell :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card) {
  border: 1px solid #e7eaf0 !important;
  box-shadow: none !important;
}

.post-show-shell .ps-post-title {
  color: #0f172a !important;
  font-size: clamp(20px, 1.9vw, 26px) !important;
  line-height: 1.34 !important;
  font-weight: 600 !important;
  letter-spacing: -0.025em !important;
}

.post-show-shell .ps-post-body,
.post-show-shell .ps-post-body :where(p, li, td, th, blockquote),
.post-show-shell .ps-comment-text,
.post-show-shell .ps-comment-text-line {
  font-size: 16px !important;
  line-height: 1.68 !important;
  font-weight: 400 !important;
  color: #29313d !important;
}

.post-show-shell .ps-post-body :where(h2, h3, h4) {
  font-weight: 600 !important;
  letter-spacing: -0.018em !important;
  color: #0f172a !important;
}

.post-show-shell :where(.post-author-name, .ps-post-author-name, .ps-comment-author, .ps-sidebar-card-title) {
  font-weight: 500 !important;
  color: #111827 !important;
}

.post-show-shell :where(.post-author-meta, .post-author-date, .ps-post-subline, .ps-comment-time, .ps-source-label, .ps-source-domain) {
  font-size: 13px !important;
  line-height: 1.35 !important;
}

/* Tablolar: daha belirgin, okunaklı ve mobilde taşmadan kaydırılabilir */
.post-show-shell .ps-post-body :where(.ps-table-wrap, .tc-wrap, .ce-table) {
  width: 100% !important;
  margin: 16px 0 !important;
  overflow-x: auto !important;
  border: 1px solid #cfd8e6 !important;
  border-radius: 14px !important;
  background: #ffffff !important;
  box-shadow: none !important;
}

.post-show-shell .ps-table-wrap table,
.post-show-shell .ps-post-body table,
.post-show-shell .tc-table,
.post-show-shell .ce-table table {
  width: 100% !important;
  min-width: 560px !important;
  border-collapse: separate !important;
  border-spacing: 0 !important;
  background: #ffffff !important;
}

.post-show-shell .ps-table-wrap :where(td, th),
.post-show-shell .ps-post-body table :where(td, th),
.post-show-shell .tc-table :where(td, th),
.post-show-shell .ce-table :where(td, th) {
  padding: 12px 14px !important;
  border-right: 1px solid #d7deea !important;
  border-bottom: 1px solid #d7deea !important;
  color: #1f2937 !important;
  vertical-align: top !important;
}

.post-show-shell .ps-table-wrap :where(tr:first-child td, tr:first-child th),
.post-show-shell .ps-post-body table :where(tr:first-child td, tr:first-child th),
.post-show-shell .tc-table :where(tr:first-child td, tr:first-child th),
.post-show-shell .ce-table :where(tr:first-child td, tr:first-child th) {
  background: #f3f6fb !important;
  color: #0f172a !important;
  font-weight: 600 !important;
}

.post-show-shell .ps-table-wrap :where(tr:nth-child(even) td),
.post-show-shell .ps-post-body table :where(tr:nth-child(even) td),
.post-show-shell .tc-table :where(tr:nth-child(even) td),
.post-show-shell .ce-table :where(tr:nth-child(even) td) {
  background: #fbfdff !important;
}

.post-show-shell .ps-table-wrap :where(tr:hover td),
.post-show-shell .ps-post-body table :where(tr:hover td),
.post-show-shell .tc-table :where(tr:hover td),
.post-show-shell .ce-table :where(tr:hover td) {
  background: #f8fbff !important;
}

/* Göz / görüntülenme ve hızlı aksiyon ikonları daha net */
.post-show-shell .ps-view-count {
  gap: 7px !important;
  font-size: 14px !important;
  font-weight: 500 !important;
  color: #111827 !important;
}

.post-show-shell .ps-view-count svg,
.post-show-shell .ps-action-icon svg,
.post-show-shell .ps-action-icon iconify-icon,
.post-show-shell .ps-comments-filter iconify-icon,
.post-show-shell button[aria-label*="Göster"] svg,
.post-show-shell button[aria-label*="göz"] svg,
.post-show-shell button[aria-label*="Göz"] svg {
  width: 22px !important;
  height: 22px !important;
  min-width: 22px !important;
  min-height: 22px !important;
}

.post-show-shell .ps-view-count svg {
  stroke-width: 1.95 !important;
}

/* Reaction açılır menüsü: daha modern, büyük ikonlu, taşmadan açılır */
.post-show-shell :where(.ps-post-card, .ps-post-card-inner, .ps-actions-bar, .ps-reaction-row, .ps-reaction-picker, .ps-reaction-more-wrap) {
  overflow: visible !important;
}

.post-show-shell .ps-reaction-trigger {
  width: 38px !important;
  min-width: 38px !important;
  height: 38px !important;
  border-radius: 999px !important;
  background: #f4f6f8 !important;
  color: #111827 !important;
  transition: background-color .16s ease, transform .16s ease, color .16s ease !important;
}

.post-show-shell .ps-reaction-trigger:hover,
.post-show-shell .ps-reaction-picker.is-open .ps-reaction-trigger {
  background: #eef4ff !important;
  color: #2563eb !important;
  transform: translateY(-1px) !important;
}

.post-show-shell .ps-reaction-menu,
.post-show-shell .ps-reaction-more-menu {
  top: calc(100% + 12px) !important;
  right: auto !important;
  left: 0 !important;
  z-index: 99999 !important;
  width: 244px !important;
  max-width: calc(100vw - 24px) !important;
  padding: 12px !important;
  border: 1px solid rgba(203, 213, 225, .95) !important;
  border-radius: 18px !important;
  background: rgba(255, 255, 255, .98) !important;
  box-shadow: none !important;
  backdrop-filter: blur(14px) !important;
}

.post-show-shell .ps-reaction-menu:not([hidden]),
.post-show-shell .ps-reaction-more-menu:not([hidden]) {
  display: grid !important;
  grid-template-columns: repeat(5, 36px) !important;
  gap: 10px !important;
  align-items: center !important;
  justify-content: start !important;
}

.post-show-shell .ps-reaction-menu-title,
.post-show-shell .ps-reaction-more-title {
  grid-column: 1 / -1 !important;
  margin: 0 0 3px !important;
  padding: 0 2px 7px !important;
  border-bottom: 1px solid #eef2f7 !important;
  color: #475569 !important;
  font-size: 13px !important;
  font-weight: 500 !important;
  line-height: 1.2 !important;
}

.post-show-shell .ps-reaction-form,
.post-show-shell .ps-reaction-option,
.post-show-shell .ps-reaction-more-item {
  width: 36px !important;
  height: 36px !important;
  min-width: 36px !important;
  min-height: 36px !important;
}

.post-show-shell .ps-reaction-option,
.post-show-shell .ps-reaction-more-item {
  border-radius: 999px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  background: transparent !important;
  color: #111827 !important;
  transition: background-color .15s ease, color .15s ease, transform .15s ease !important;
}

.post-show-shell .ps-reaction-option:hover,
.post-show-shell .ps-reaction-option:focus-visible,
.post-show-shell .ps-reaction-more-item:hover,
.post-show-shell .ps-reaction-more-item:focus-visible {
  background: #eef2f7 !important;
  color: #2563eb !important;
  transform: translateY(-2px) scale(1.05) !important;
}

.post-show-shell .ps-reaction-option:active,
.post-show-shell .ps-reaction-more-item:active {
  transform: scale(.94) !important;
}

.post-show-shell .ps-reaction-option :where(img, svg, .ps-reaction-media),
.post-show-shell .ps-reaction-more-item :where(img, svg, .ps-reaction-media),
.post-show-shell .ps-reaction-option > :where(img, svg),
.post-show-shell .ps-reaction-more-item > :where(img, svg) {
  width: 30px !important;
  height: 30px !important;
  max-width: 30px !important;
  max-height: 30px !important;
  border-radius: 999px !important;
  object-fit: cover !important;
}

.post-show-shell .ps-reaction-pill {
  min-height: 34px !important;
  padding: 0 12px !important;
  border: 1px solid #edf0f4 !important;
  background: #f8fafc !important;
  font-size: 13px !important;
  font-weight: 500 !important;
}

.post-show-shell .ps-reaction-pill :where(img, .ps-reaction-media) {
  width: 24px !important;
  height: 24px !important;
}

/* Yükleme düzeltmesi: alttaki gerçek içeriğin üstüne beyaz dalga bindirme yok; sadece üst skeleton çalışır */
.post-show-shell.is-loading .ps-main {
  opacity: 1 !important;
  filter: none !important;
}

.post-show-shell.is-loading :where(
  .ps-sidebar-left,
  .ps-nav-item,
  .ps-post-card,
  .ps-post-card-inner,
  .ps-post-author,
  .ps-post-title,
  .ps-post-image,
  .ps-post-body,
  .ps-source-link,
  .ps-tags-row,
  .ps-reaction-row,
  .ps-action-row,
  .ps-comments-section,
  .ps-comments-header,
  .ps-comment-form-box,
  .ps-comments-list,
  .ps-comment-item,
  .ps-comment-card,
  .ps-sidebar-card,
  .ps-recent-comment-item,
  .ps-tag-row
)::after {
  content: none !important;
  display: none !important;
  animation: none !important;
  background: none !important;
}

.post-show-shell.is-loading :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card) {
  background-image: none !important;
  animation: none !important;
}

.post-show-shell .ps-wave-loader {
  border: 1px solid #eef2f7 !important;
  box-shadow: none !important;
}

.post-show-shell .ps-wave-avatar,
.post-show-shell .ps-wave-line,
.post-show-shell .ps-wave-media {
  background: #eceff3 !important;
}

@media (max-width: 640px) {
  .post-show-shell .ps-post-title {
    font-size: 20px !important;
  }

  .post-show-shell .ps-post-body,
  .post-show-shell .ps-post-body :where(p, li, td, th, blockquote),
  .post-show-shell .ps-comment-text,
  .post-show-shell .ps-comment-text-line {
    font-size: 15.5px !important;
    line-height: 1.66 !important;
  }

  .post-show-shell .ps-reaction-menu,
  .post-show-shell .ps-reaction-more-menu {
    width: 224px !important;
    grid-template-columns: repeat(5, 34px) !important;
    gap: 8px !important;
  }

  .post-show-shell .ps-reaction-form,
  .post-show-shell .ps-reaction-option,
  .post-show-shell .ps-reaction-more-item {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    min-height: 34px !important;
  }

  .post-show-shell .ps-reaction-option :where(img, svg, .ps-reaction-media),
  .post-show-shell .ps-reaction-more-item :where(img, svg, .ps-reaction-media),
  .post-show-shell .ps-reaction-option > :where(img, svg),
  .post-show-shell .ps-reaction-more-item > :where(img, svg) {
    width: 28px !important;
    height: 28px !important;
  }
}

html.dark .post-show-shell,
body.dark .post-show-shell,
.dark .post-show-shell,
[data-theme="dark"] .post-show-shell {
  color-scheme: dark !important;
}

html.dark .post-show-shell :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card, .ps-reaction-menu, .ps-reaction-more-menu, .ps-table-wrap, .tc-wrap, .ce-table),
body.dark .post-show-shell :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card, .ps-reaction-menu, .ps-reaction-more-menu, .ps-table-wrap, .tc-wrap, .ce-table),
.dark .post-show-shell :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card, .ps-reaction-menu, .ps-reaction-more-menu, .ps-table-wrap, .tc-wrap, .ce-table),
[data-theme="dark"] .post-show-shell :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card, .ps-reaction-menu, .ps-reaction-more-menu, .ps-table-wrap, .tc-wrap, .ce-table) {
  background: #111827 !important;
  border-color: rgba(148, 163, 184, .22) !important;
  box-shadow: none !important;
}

html.dark .post-show-shell :where(.ps-post-title, .post-author-name, .ps-comment-author, .ps-view-count),
body.dark .post-show-shell :where(.ps-post-title, .post-author-name, .ps-comment-author, .ps-view-count),
.dark .post-show-shell :where(.ps-post-title, .post-author-name, .ps-comment-author, .ps-view-count),
[data-theme="dark"] .post-show-shell :where(.ps-post-title, .post-author-name, .ps-comment-author, .ps-view-count) {
  color: #f8fafc !important;
}

html.dark .post-show-shell :where(.ps-post-body, .ps-post-body p, .ps-post-body li, .ps-comment-text, .ps-comment-text-line, .ps-table-wrap td, .ps-post-body table td),
body.dark .post-show-shell :where(.ps-post-body, .ps-post-body p, .ps-post-body li, .ps-comment-text, .ps-comment-text-line, .ps-table-wrap td, .ps-post-body table td),
.dark .post-show-shell :where(.ps-post-body, .ps-post-body p, .ps-post-body li, .ps-comment-text, .ps-comment-text-line, .ps-table-wrap td, .ps-post-body table td),
[data-theme="dark"] .post-show-shell :where(.ps-post-body, .ps-post-body p, .ps-post-body li, .ps-comment-text, .ps-comment-text-line, .ps-table-wrap td, .ps-post-body table td) {
  color: #dbe4f0 !important;
}

html.dark .post-show-shell .ps-table-wrap :where(tr:first-child td, tr:first-child th),
body.dark .post-show-shell .ps-table-wrap :where(tr:first-child td, tr:first-child th),
.dark .post-show-shell .ps-table-wrap :where(tr:first-child td, tr:first-child th),
[data-theme="dark"] .post-show-shell .ps-table-wrap :where(tr:first-child td, tr:first-child th) {
  background: #1e293b !important;
  color: #f8fafc !important;
}

html.dark .post-show-shell .ps-table-wrap :where(td, th),
body.dark .post-show-shell .ps-table-wrap :where(td, th),
.dark .post-show-shell .ps-table-wrap :where(td, th),
[data-theme="dark"] .post-show-shell .ps-table-wrap :where(td, th) {
  border-color: rgba(148, 163, 184, .25) !important;
}

html.dark .post-show-shell .ps-reaction-trigger,
body.dark .post-show-shell .ps-reaction-trigger,
.dark .post-show-shell .ps-reaction-trigger,
[data-theme="dark"] .post-show-shell .ps-reaction-trigger,
html.dark .post-show-shell .ps-reaction-pill,
body.dark .post-show-shell .ps-reaction-pill,
.dark .post-show-shell .ps-reaction-pill,
[data-theme="dark"] .post-show-shell .ps-reaction-pill {
  background: rgba(255,255,255,.08) !important;
  border-color: rgba(255,255,255,.08) !important;
  color: #e5e7eb !important;
}

html.dark .post-show-shell .ps-reaction-menu-title,
html.dark .post-show-shell .ps-reaction-more-title,
body.dark .post-show-shell .ps-reaction-menu-title,
body.dark .post-show-shell .ps-reaction-more-title,
.dark .post-show-shell .ps-reaction-menu-title,
.dark .post-show-shell .ps-reaction-more-title,
[data-theme="dark"] .post-show-shell .ps-reaction-menu-title,
[data-theme="dark"] .post-show-shell .ps-reaction-more-title {
  border-bottom-color: rgba(148, 163, 184, .18) !important;
  color: #cbd5e1 !important;
}

html.dark .post-show-shell .ps-reaction-option:hover,
html.dark .post-show-shell .ps-reaction-more-item:hover,
body.dark .post-show-shell .ps-reaction-option:hover,
body.dark .post-show-shell .ps-reaction-more-item:hover,
.dark .post-show-shell .ps-reaction-option:hover,
.dark .post-show-shell .ps-reaction-more-item:hover,
[data-theme="dark"] .post-show-shell .ps-reaction-option:hover,
[data-theme="dark"] .post-show-shell .ps-reaction-more-item:hover {
  background: rgba(255,255,255,.10) !important;
  color: #93c5fd !important;
}
</style>
@endpush


@push('head')
<style>
/* =========================================================
   FINAL FIX: Loader artık blog kartının içindedir.
   Üstte ayrı boş/skeleton kart oluşturmaz, gerçek içerik
   yüklenene kadar sadece post kartının içinde görünür.
   ========================================================= */
.post-show-shell .ps-post-card {
  position: relative !important;
}

.post-show-shell .ps-wave-loader {
  position: absolute !important;
  inset: 0 !important;
  z-index: 35 !important;
  width: 100% !important;
  max-width: none !important;
  min-height: 220px !important;
  max-height: none !important;
  margin: 0 !important;
  padding: 18px !important;
  border-radius: inherit !important;
  background: #ffffff !important;
  overflow: hidden !important;
  pointer-events: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
  display: block !important;
  transform: none !important;
  transition: opacity .22s ease, visibility .22s ease !important;
}

.post-show-shell.is-loading .ps-wave-loader {
  opacity: 1 !important;
  visibility: visible !important;
  display: block !important;
}

.post-show-shell.is-loaded .ps-wave-loader,
.post-show-shell .ps-wave-loader[hidden] {
  opacity: 0 !important;
  visibility: hidden !important;
  pointer-events: none !important;
}

.post-show-shell.is-loading .ps-post-card-inner {
  opacity: 0 !important;
  visibility: hidden !important;
}

.post-show-shell.is-loaded .ps-post-card-inner,
.post-show-shell:not(.is-loading) .ps-post-card-inner {
  opacity: 1 !important;
  visibility: visible !important;
}

/* Önceki global shimmer yüzünden yazılar/fotoğraf altta hayalet gibi görünüyordu; tamamen kapatıldı. */
.post-show-shell.is-loading :where(
  .ps-sidebar-left,
  .ps-nav-item,
  .ps-post-card,
  .ps-post-author,
  .ps-post-title,
  .ps-post-image,
  .ps-post-body,
  .ps-source-link,
  .ps-tags-row,
  .ps-reaction-row,
  .ps-action-row,
  .ps-comments-header,
  .ps-comment-form-box,
  .ps-comment-item,
  .ps-sidebar-card,
  .ps-recent-comment-item,
  .ps-tag-row
)::after {
  content: none !important;
  display: none !important;
  animation: none !important;
}

.post-show-shell.is-loading :where(.ps-post-card, .ps-comments-section, .ps-sidebar-card) {
  background-image: none !important;
  animation: none !important;
}

.post-show-shell.is-loading .ps-main {
  opacity: 1 !important;
}

.post-show-shell .ps-wave-loader-inner {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  width: 100% !important;
}

.post-show-shell .ps-wave-avatar,
.post-show-shell .ps-wave-line,
.post-show-shell .ps-wave-media {
  position: relative !important;
  overflow: hidden !important;
  background: #eef1f5 !important;
}

.post-show-shell .ps-wave-avatar {
  width: 44px !important;
  height: 44px !important;
  min-width: 44px !important;
  border-radius: 999px !important;
}

.post-show-shell .ps-wave-copy {
  display: flex !important;
  flex-direction: column !important;
  gap: 9px !important;
  width: 100% !important;
  min-width: 0 !important;
}

.post-show-shell .ps-wave-line {
  height: 10px !important;
  border-radius: 999px !important;
}

.post-show-shell .ps-wave-line--wide { width: 72% !important; }
.post-show-shell .ps-wave-line--short { width: 42% !important; }

.post-show-shell .ps-wave-media {
  width: 100% !important;
  height: 130px !important;
  margin-top: 14px !important;
  border-radius: 14px !important;
}

.post-show-shell .ps-wave-avatar::after,
.post-show-shell .ps-wave-line::after,
.post-show-shell .ps-wave-media::after {
  content: "" !important;
  position: absolute !important;
  inset: 0 !important;
  transform: translateX(-100%) !important;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.76), transparent) !important;
  animation: psWaveLoading 1.15s ease-in-out infinite !important;
}

@media (max-width: 640px) {
  .post-show-shell .ps-wave-loader {
    padding: 12px !important;
    min-height: 178px !important;
  }

  .post-show-shell .ps-wave-media {
    height: 96px !important;
  }
}

html.dark .post-show-shell .ps-wave-loader,
body.dark .post-show-shell .ps-wave-loader,
.dark .post-show-shell .ps-wave-loader,
[data-theme="dark"] .post-show-shell .ps-wave-loader {
  background: #111827 !important;
}

html.dark .post-show-shell .ps-wave-avatar,
html.dark .post-show-shell .ps-wave-line,
html.dark .post-show-shell .ps-wave-media,
body.dark .post-show-shell .ps-wave-avatar,
body.dark .post-show-shell .ps-wave-line,
body.dark .post-show-shell .ps-wave-media,
.dark .post-show-shell .ps-wave-avatar,
.dark .post-show-shell .ps-wave-line,
.dark .post-show-shell .ps-wave-media,
[data-theme="dark"] .post-show-shell .ps-wave-avatar,
[data-theme="dark"] .post-show-shell .ps-wave-line,
[data-theme="dark"] .post-show-shell .ps-wave-media {
  background: rgba(255,255,255,.10) !important;
}

html.dark .post-show-shell .ps-wave-avatar::after,
html.dark .post-show-shell .ps-wave-line::after,
html.dark .post-show-shell .ps-wave-media::after,
body.dark .post-show-shell .ps-wave-avatar::after,
body.dark .post-show-shell .ps-wave-line::after,
body.dark .post-show-shell .ps-wave-media::after,
.dark .post-show-shell .ps-wave-avatar::after,
.dark .post-show-shell .ps-wave-line::after,
.dark .post-show-shell .ps-wave-media::after,
[data-theme="dark"] .post-show-shell .ps-wave-avatar::after,
[data-theme="dark"] .post-show-shell .ps-wave-line::after,
[data-theme="dark"] .post-show-shell .ps-wave-media::after {
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.22), transparent) !important;
}
</style>
@endpush


@push('head')
<style>
/* =========================================================
   FINAL FIX 2 — loader boş beyaz alanı kaldırıldı + metin taşması düzeltildi
   - Loader artık absolute/overlay değil, post kartının normal yüksekliğini belirler.
   - Yükleme sırasında gerçek içerik display:none olur; altta beyaz boş kutu kalmaz.
   - Yükleme bitince loader display:none olur, içerik aşağı doğru doğal şekilde açılır.
   - Uzun RSS/metin/link parçaları kart dışına taşmaz.
   ========================================================= */
.post-show-shell .ps-post-card {
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0 !important;
  overflow: hidden !important;
}

.post-show-shell .ps-wave-loader {
  position: relative !important;
  inset: auto !important;
  z-index: 1 !important;
  width: 100% !important;
  max-width: 100% !important;
  min-height: 0 !important;
  max-height: none !important;
  height: auto !important;
  margin: 0 !important;
  padding: 18px !important;
  border-radius: inherit !important;
  background: #ffffff !important;
  overflow: hidden !important;
  pointer-events: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
  display: none !important;
  transform: none !important;
  transition: opacity .22s ease, visibility .22s ease !important;
}

.post-show-shell.is-loading .ps-wave-loader {
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
}

.post-show-shell.is-loaded .ps-wave-loader,
.post-show-shell .ps-wave-loader[hidden] {
  display: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
}

.post-show-shell.is-loading .ps-post-card-inner {
  display: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
  height: 0 !important;
  min-height: 0 !important;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  overflow: hidden !important;
}

.post-show-shell.is-loaded .ps-post-card-inner,
.post-show-shell:not(.is-loading) .ps-post-card-inner {
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
  height: auto !important;
  min-height: 0 !important;
  overflow: visible !important;
}

.post-show-shell .ps-wave-loader-inner {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0 !important;
}

.post-show-shell .ps-wave-copy {
  flex: 1 1 auto !important;
  min-width: 0 !important;
  max-width: 100% !important;
}

.post-show-shell .ps-wave-line--wide {
  width: min(72%, 430px) !important;
}

.post-show-shell .ps-wave-line--short {
  width: min(42%, 270px) !important;
}

.post-show-shell .ps-wave-media {
  width: 100% !important;
  max-width: 100% !important;
  height: 130px !important;
  margin-top: 14px !important;
  border-radius: 14px !important;
}

/* Metin ve medya taşmasını kes: başlık, RSS metni, link, yorum, liste hepsi kart içinde kalır. */
.post-show-shell .ps-post-card-inner,
.post-show-shell .ps-post-author-row,
.post-show-shell .post-author-mini,
.post-show-shell .post-author-info,
.post-show-shell .ps-post-title,
.post-show-shell .ps-post-body,
.post-show-shell .ps-post-body :where(p, div, span, a, strong, em, li, h2, h3, h4, h5, h6, blockquote, figcaption),
.post-show-shell .ps-comment-text,
.post-show-shell .ps-comment-text-line,
.post-show-shell .ps-source-link,
.post-show-shell .ps-tags-row,
.post-show-shell .ps-actions-bar {
  max-width: 100% !important;
  min-width: 0 !important;
  box-sizing: border-box !important;
  overflow-wrap: anywhere !important;
  word-break: break-word !important;
}

.post-show-shell .ps-post-body {
  width: 100% !important;
  overflow-x: hidden !important;
  color: #1f2937 !important;
}

.post-show-shell .ps-post-body :where(p, div, li, blockquote) {
  white-space: normal !important;
}

.post-show-shell .ps-post-body :where(ul, ol) {
  max-width: 100% !important;
  margin-left: 0 !important;
  padding-left: 1.15rem !important;
  box-sizing: border-box !important;
}

.post-show-shell .ps-post-body :where(img, video, iframe, figure, .ps-full-media, .ps-full-gallery, .image-tool, .image-tool__image, .image-tool__image-picture) {
  max-width: 100% !important;
  box-sizing: border-box !important;
}

.post-show-shell .ps-post-body :where(img, video) {
  height: auto !important;
  display: block !important;
  object-fit: cover !important;
}

.post-show-shell .ps-post-image,
.post-show-shell .ps-post-image img {
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0 !important;
  overflow: hidden !important;
}

.post-show-shell .ps-table-wrap,
.post-show-shell .ps-post-body :where(.tc-wrap, .ce-table) {
  max-width: 100% !important;
  overflow-x: auto !important;
}

@media (max-width: 640px) {
  .post-show-shell .ps-wave-loader {
    padding: 12px !important;
    border-radius: 14px !important;
  }

  .post-show-shell .ps-wave-media {
    height: 96px !important;
  }

  .post-show-shell .ps-post-card-inner {
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  .post-show-shell .ps-post-title {
    font-size: 19px !important;
    line-height: 1.36 !important;
  }

  .post-show-shell .ps-post-body,
  .post-show-shell .ps-post-body :where(p, div, li, blockquote),
  .post-show-shell .ps-comment-text,
  .post-show-shell .ps-comment-text-line {
    font-size: 15px !important;
    line-height: 1.62 !important;
  }
}

html.dark .post-show-shell .ps-wave-loader,
body.dark .post-show-shell .ps-wave-loader,
.dark .post-show-shell .ps-wave-loader,
[data-theme="dark"] .post-show-shell .ps-wave-loader {
  background: #111827 !important;
}
</style>
@endpush


@push('head')
<style>
/* =========================================================
   FINAL FIX: Tepkiler menüsü yorum kartının arkasında kalmasın
   ve menü/kart gölgesi tamamen kalksın.
   ========================================================= */
.post-show-shell .ps-post-card,
.post-show-shell .ps-post-card-inner,
.post-show-shell .ps-actions-bar,
.post-show-shell .ps-reaction-row,
.post-show-shell .ps-reaction-picker,
.post-show-shell .ps-reaction-more-wrap {
  overflow: visible !important;
}

.post-show-shell .ps-post-card {
  position: relative !important;
  z-index: 30 !important;
  box-shadow: none !important;
}

.post-show-shell .ps-actions-bar,
.post-show-shell .ps-reaction-row {
  position: relative !important;
  z-index: 60 !important;
}

.post-show-shell .ps-reaction-picker,
.post-show-shell .ps-reaction-more-wrap {
  position: relative !important;
  z-index: 90 !important;
}

.post-show-shell .ps-comments-section {
  position: relative !important;
  z-index: 1 !important;
  box-shadow: none !important;
}

.post-show-shell .ps-reaction-menu,
.post-show-shell .ps-reaction-more-menu {
  z-index: 2147483000 !important;
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
}

.post-show-shell .ps-reaction-trigger,
.post-show-shell .ps-reaction-pill,
.post-show-shell .ps-reaction-more-trigger {
  box-shadow: none !important;
}

html.dark .post-show-shell .ps-reaction-menu,
body.dark .post-show-shell .ps-reaction-menu,
.dark .post-show-shell .ps-reaction-menu,
[data-theme="dark"] .post-show-shell .ps-reaction-menu,
html.dark .post-show-shell .ps-reaction-more-menu,
body.dark .post-show-shell .ps-reaction-more-menu,
.dark .post-show-shell .ps-reaction-more-menu,
[data-theme="dark"] .post-show-shell .ps-reaction-more-menu {
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
}
</style>
@endpush


@push('head')
<style>
/* =========================================================
   FINAL MOBILE FIX — Tepkiler mobilde görünür
   - Mobilde tepki menüsü artık kart içinde sıkışmaz.
   - Ekranın altından sabit, temiz bir panel gibi açılır.
   - Menü gölgesi tamamen kapalı kalır.
   ========================================================= */
.post-show-shell .ps-post-card,
.post-show-shell .ps-post-card-inner,
.post-show-shell .ps-actions-bar,
.post-show-shell .ps-reaction-row,
.post-show-shell .ps-reaction-picker,
.post-show-shell .ps-reaction-more-wrap {
  overflow: visible !important;
}

.post-show-shell .ps-reaction-picker.is-open,
.post-show-shell .ps-reaction-more-wrap:has(.ps-reaction-more-menu:not([hidden])) {
  z-index: 2147483000 !important;
}

.post-show-shell .ps-reaction-menu,
.post-show-shell .ps-reaction-more-menu {
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
}

@media (max-width: 640px) {
  .post-show-shell {
    overflow-x: hidden !important;
  }

  .post-show-shell .ps-actions-bar,
  .post-show-shell .ps-reaction-row {
    overflow: visible !important;
    position: relative !important;
    z-index: 120 !important;
  }

  .post-show-shell .ps-reaction-picker,
  .post-show-shell .ps-reaction-more-wrap {
    position: static !important;
    overflow: visible !important;
  }

  .post-show-shell .ps-reaction-trigger,
  .post-show-shell .ps-reaction-more-trigger,
  .post-show-shell .ps-reaction-pill {
    box-shadow: none !important;
  }

  .post-show-shell .ps-reaction-menu,
  .post-show-shell .ps-reaction-more-menu {
    position: fixed !important;
    left: 12px !important;
    right: 12px !important;
    top: auto !important;
    bottom: calc(12px + env(safe-area-inset-bottom, 0px)) !important;
    width: auto !important;
    max-width: none !important;
    max-height: min(72vh, 430px) !important;
    overflow-y: auto !important;
    overscroll-behavior: contain !important;
    z-index: 2147483647 !important;
    padding: 14px !important;
    border-radius: 18px !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: none !important;
    transform: none !important;
  }

  .post-show-shell .ps-reaction-menu:not([hidden]),
  .post-show-shell .ps-reaction-more-menu:not([hidden]) {
    display: grid !important;
    grid-template-columns: repeat(6, minmax(36px, 1fr)) !important;
    gap: 10px !important;
    align-items: center !important;
    justify-content: center !important;
  }

  .post-show-shell .ps-reaction-menu-title,
  .post-show-shell .ps-reaction-more-title {
    grid-column: 1 / -1 !important;
    margin: 0 0 4px !important;
    padding: 0 2px 10px !important;
    border-bottom: 1px solid #edf2f7 !important;
    color: #475569 !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
  }

  .post-show-shell .ps-reaction-menu .ps-reaction-form,
  .post-show-shell .ps-reaction-form,
  .post-show-shell .ps-reaction-option,
  .post-show-shell .ps-reaction-more-item {
    width: 40px !important;
    height: 40px !important;
    min-width: 40px !important;
    min-height: 40px !important;
    justify-self: center !important;
  }

  .post-show-shell .ps-reaction-option,
  .post-show-shell .ps-reaction-more-item {
    border-radius: 999px !important;
    background: transparent !important;
  }

  .post-show-shell .ps-reaction-option:hover,
  .post-show-shell .ps-reaction-option:focus-visible,
  .post-show-shell .ps-reaction-more-item:hover,
  .post-show-shell .ps-reaction-more-item:focus-visible {
    background: #f1f5f9 !important;
    color: #2563eb !important;
    transform: none !important;
  }

  .post-show-shell .ps-reaction-option :where(img, svg, .ps-reaction-media),
  .post-show-shell .ps-reaction-more-item :where(img, svg, .ps-reaction-media),
  .post-show-shell .ps-reaction-option > :where(img, svg),
  .post-show-shell .ps-reaction-more-item > :where(img, svg) {
    width: 30px !important;
    height: 30px !important;
    max-width: 30px !important;
    max-height: 30px !important;
    object-fit: cover !important;
    border-radius: 999px !important;
  }

  .post-show-shell .ps-reaction-more-footer {
    grid-column: 1 / -1 !important;
    margin-top: 4px !important;
  }
}

@media (max-width: 420px) {
  .post-show-shell .ps-reaction-menu:not([hidden]),
  .post-show-shell .ps-reaction-more-menu:not([hidden]) {
    grid-template-columns: repeat(5, minmax(36px, 1fr)) !important;
  }
}

html.dark .post-show-shell .ps-reaction-menu,
body.dark .post-show-shell .ps-reaction-menu,
.dark .post-show-shell .ps-reaction-menu,
[data-theme="dark"] .post-show-shell .ps-reaction-menu,
html.dark .post-show-shell .ps-reaction-more-menu,
body.dark .post-show-shell .ps-reaction-more-menu,
.dark .post-show-shell .ps-reaction-more-menu,
[data-theme="dark"] .post-show-shell .ps-reaction-more-menu {
  background: #111827 !important;
  border-color: rgba(148, 163, 184, .22) !important;
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
}

html.dark .post-show-shell .ps-reaction-menu-title,
body.dark .post-show-shell .ps-reaction-menu-title,
.dark .post-show-shell .ps-reaction-menu-title,
[data-theme="dark"] .post-show-shell .ps-reaction-menu-title,
html.dark .post-show-shell .ps-reaction-more-title,
body.dark .post-show-shell .ps-reaction-more-title,
.dark .post-show-shell .ps-reaction-more-title,
[data-theme="dark"] .post-show-shell .ps-reaction-more-title {
  border-bottom-color: rgba(148, 163, 184, .18) !important;
  color: #cbd5e1 !important;
}
</style>
@endpush

@push('scripts')
<script>
  (function () {
    const shell = document.querySelector('.post-show-shell');
    if (!shell) return;

    const waveLoader = shell.querySelector('[data-ps-wave-loader]');
    const waveStartedAt = (window.performance && performance.now) ? performance.now() : Date.now();
    const waveMinVisibleMs = 1500;
    let waveDone = false;

    if (waveLoader) waveLoader.hidden = false;
    shell.classList.remove('is-loaded');
    shell.classList.add('is-loading');

    const hideWaveLoader = function () {
      if (waveDone) return;
      waveDone = true;

      const now = (window.performance && performance.now) ? performance.now() : Date.now();
      const remaining = Math.max(0, waveMinVisibleMs - (now - waveStartedAt));

      window.setTimeout(function () {
        shell.classList.remove('is-loading');
        shell.classList.add('is-loaded');

        if (waveLoader) {
          window.setTimeout(function () {
            waveLoader.hidden = true;
          }, 260);
        }
      }, remaining);
    };

    if (document.readyState === 'complete') {
      requestAnimationFrame(function () {
        requestAnimationFrame(hideWaveLoader);
      });
    } else {
      window.addEventListener('load', hideWaveLoader, { once: true });
      window.setTimeout(hideWaveLoader, 2200);
    }


    const resize = function (textarea) {
      if (!textarea) return;
      textarea.style.height = 'auto';
      const next = Math.max(textarea.scrollHeight, textarea.classList.contains('ps-comment-mini-textarea') ? 70 : 78);
      textarea.style.height = next + 'px';
    };

    shell.querySelectorAll('textarea.ps-comment-textarea, textarea.ps-comment-mini-textarea').forEach(function (textarea) {
      resize(textarea);
      textarea.addEventListener('input', function () { resize(textarea); });
      textarea.addEventListener('focus', function () { resize(textarea); });
      textarea.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
          const form = textarea.closest('form');
          if (form) {
            event.preventDefault();
            if (form.requestSubmit) form.requestSubmit();
            else form.submit();
          }
        }
      });
    });

    const mainInput = shell.querySelector('#show-comment-input');
    const counter = shell.querySelector('[data-comment-counter]');
    const updateCounter = function () {
      if (!mainInput || !counter) return;
      const max = parseInt(mainInput.getAttribute('maxlength') || '500', 10);
      const len = (mainInput.value || '').length;
      counter.textContent = len + '/' + max;
    };
    if (mainInput) {
      updateCounter();
      mainInput.addEventListener('input', updateCounter);
    }

    shell.querySelectorAll('[data-comment-suggestion]').forEach(function (button) {
      button.addEventListener('click', function () {
        if (!mainInput) return;
        const insert = button.getAttribute('data-comment-suggestion') || button.textContent || '';
        const current = (mainInput.value || '').trim();
        mainInput.value = current ? current + ' ' + insert : insert;
        mainInput.dispatchEvent(new Event('input', { bubbles: true }));
        mainInput.focus();
      });
    });

    const closeMenus = function (except) {
      shell.querySelectorAll('[data-comment-more]').forEach(function (wrap) {
        if (except && wrap === except) return;
        wrap.classList.remove('is-open');
        const trigger = wrap.querySelector('[data-comment-more-trigger]');
        const menu = wrap.querySelector('[data-comment-more-menu]');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
        if (menu) menu.hidden = true;
      });
    };

    shell.querySelectorAll('[data-comment-more-trigger]').forEach(function (trigger) {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const wrap = trigger.closest('[data-comment-more]');
        if (!wrap) return;
        const menu = wrap.querySelector('[data-comment-more-menu]');
        const open = !wrap.classList.contains('is-open');
        closeMenus(wrap);
        wrap.classList.toggle('is-open', open);
        if (menu) menu.hidden = !open;
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    });

    const toggleForm = function (button, attr) {
      const selector = button.getAttribute(attr);
      if (!selector) return;
      const target = shell.querySelector(selector);
      if (!target) return;
      target.classList.toggle('is-open');
      if (target.classList.contains('is-open')) {
        const textarea = target.querySelector('textarea');
        if (textarea) {
          resize(textarea);
          setTimeout(function () { textarea.focus(); }, 10);
        }
      }
      closeMenus();
    };

    shell.querySelectorAll('[data-comment-reply-toggle]').forEach(function (button) {
      button.addEventListener('click', function () { toggleForm(button, 'data-comment-reply-toggle'); });
    });

    shell.querySelectorAll('[data-comment-edit-toggle]').forEach(function (button) {
      button.addEventListener('click', function () { toggleForm(button, 'data-comment-edit-toggle'); });
    });

    shell.querySelectorAll('[data-replies-toggle]').forEach(function (button) {
      button.addEventListener('click', function () {
        const target = shell.querySelector(button.getAttribute('data-replies-target') || '');
        if (!target) return;
        const willHide = !target.hasAttribute('hidden');
        if (willHide) target.setAttribute('hidden', 'hidden');
        else target.removeAttribute('hidden');
        button.setAttribute('aria-expanded', willHide ? 'false' : 'true');
        const label = button.querySelector('[data-replies-toggle-label]');
        if (label) label.textContent = willHide ? 'Yanıtları göster' : 'Yanıtları gizle';
      });
    });

    document.addEventListener('click', function (event) {
      if (!event.target.closest('[data-comment-more]')) closeMenus();
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeMenus();
    });
  })();
</script>
@endpush


@push('head')
<style>
/* =========================================================
   FINAL FIX: Genel arka plan/kart gölgesi azaltıldı
   - Büyük ve ağır gölgeler kaldırıldı.
   - Kartlar sadece ince border + çok hafif derinlik kullanır.
   - 3 nokta, yorum ve tepki menülerinde gölge kapalı kalır.
   ========================================================= */
.post-show-shell {
  --ps-soft-shadow: 0 1px 6px rgba(15, 23, 42, .035);
  --ps-card-border: rgba(226, 232, 240, .92);
}

.post-show-shell :where(
  .ps-post-card,
  .ps-comments-section,
  .ps-sidebar-card,
  .ps-wave-loader,
  .ps-comment-form-box,
  .ps-source-link,
  .ps-full-media,
  .ps-table-wrap,
  .ps-link-preview,
  .ps-related-card,
  .ps-recommend-card
) {
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
}

.post-show-shell :where(
  .ps-post-card,
  .ps-comments-section,
  .ps-sidebar-card,
  .ps-wave-loader
) {
  border: 1px solid var(--ps-card-border) !important;
}

.post-show-shell :where(
  .ps-menu-panel,
  .ps-comment-more-menu,
  .ps-reaction-menu,
  .ps-reaction-more-menu,
  .ps-post-edited-popover,
  .ps-menu-trigger,
  .ps-reaction-trigger,
  .ps-reaction-pill,
  .ps-reaction-more-trigger,
  .ps-action-btn,
  .ps-comment-tool,
  .ps-comment-send,
  .ps-comment-vote-btn,
  .ps-comment-more-trigger
) {
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
}

.post-show-shell .ps-wave-loader {
  box-shadow: none !important;
}

html.dark .post-show-shell,
body.dark .post-show-shell,
.dark .post-show-shell,
[data-theme="dark"] .post-show-shell {
  --ps-soft-shadow: 0 1px 7px rgba(0, 0, 0, .14);
  --ps-card-border: rgba(148, 163, 184, .14);
}

html.dark .post-show-shell :where(.ps-menu-panel, .ps-comment-more-menu, .ps-reaction-menu, .ps-reaction-more-menu),
body.dark .post-show-shell :where(.ps-menu-panel, .ps-comment-more-menu, .ps-reaction-menu, .ps-reaction-more-menu),
.dark .post-show-shell :where(.ps-menu-panel, .ps-comment-more-menu, .ps-reaction-menu, .ps-reaction-more-menu),
[data-theme="dark"] .post-show-shell :where(.ps-menu-panel, .ps-comment-more-menu, .ps-reaction-menu, .ps-reaction-more-menu) {
  box-shadow: none !important;
  filter: none !important;
  -webkit-filter: none !important;
}


  /* Tum golgeler kapali */
  .post-show-shell *,
  .post-show-shell *::before,
  .post-show-shell *::after {
    box-shadow: none !important;
    text-shadow: none !important;
  }

  .ps-post-card,
  .ps-comments-section,
  .ps-sidebar-card,
  .ps-menu-panel,
  .ps-reaction-menu,
  .ps-comment-more-menu,
  .ps-post-edited-popover,
  .ps-nsfw-card {
    box-shadow: none !important;
  }


  /* Düzenlenen gönderi kalem ikonu geri eklendi ve görünürlüğü sabitlendi */
  .post-show-shell .post-author-meta .post-author-edited-wrap,
  .post-show-shell .ps-post-edited-wrap.post-author-edited-wrap {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 20px !important;
    height: 20px !important;
    margin-left: 2px !important;
    flex: 0 0 20px !important;
    vertical-align: middle !important;
    overflow: visible !important;
    color: #64748b !important;
  }

  .post-show-shell .post-author-edited-button,
  .post-show-shell .ps-post-edited-button.post-author-edited-button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 20px !important;
    height: 20px !important;
    min-width: 20px !important;
    min-height: 20px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #64748b !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    box-shadow: none !important;
    outline: none !important;
  }

  .post-show-shell .post-author-edited-button:hover,
  .post-show-shell .post-author-edited-button:focus-visible,
  .post-show-shell .ps-post-edited-wrap.is-open .post-author-edited-button {
    background: #f1f5f9 !important;
    color: #2563eb !important;
    box-shadow: none !important;
  }

  .post-show-shell .post-author-edited-icon,
  .post-show-shell .ps-post-edited-icon.post-author-edited-icon {
    display: block !important;
    width: 15px !important;
    height: 15px !important;
    min-width: 15px !important;
    min-height: 15px !important;
    stroke: currentColor !important;
    fill: none !important;
    opacity: 1 !important;
    visibility: visible !important;
    flex: 0 0 auto !important;
    pointer-events: none !important;
  }

  .post-show-shell .post-author-edited-icon path,
  .post-show-shell .ps-post-edited-icon.post-author-edited-icon path {
    stroke: currentColor !important;
    fill: none !important;
  }

  html.dark .post-show-shell .post-author-edited-button,
  body.dark .post-show-shell .post-author-edited-button,
  .dark .post-show-shell .post-author-edited-button,
  [data-theme="dark"] .post-show-shell .post-author-edited-button {
    color: #cbd5e1 !important;
  }

  html.dark .post-show-shell .post-author-edited-button:hover,
  body.dark .post-show-shell .post-author-edited-button:hover,
  .dark .post-show-shell .post-author-edited-button:hover,
  [data-theme="dark"] .post-show-shell .post-author-edited-button:hover,
  html.dark .post-show-shell .ps-post-edited-wrap.is-open .post-author-edited-button,
  body.dark .post-show-shell .ps-post-edited-wrap.is-open .post-author-edited-button,
  .dark .post-show-shell .ps-post-edited-wrap.is-open .post-author-edited-button,
  [data-theme="dark"] .post-show-shell .ps-post-edited-wrap.is-open .post-author-edited-button {
    background: rgba(148, 163, 184, .14) !important;
    color: #93c5fd !important;
  }


  /* Düzenleme ayrıntıları: kalın metin normalleştirildi */
  .post-show-shell .ps-post-edited-popover,
  .post-show-shell .ps-post-edited-title,
  .post-show-shell .ps-post-edited-detail {
    font-weight: 400 !important;
  }

  .post-show-shell .ps-post-edited-title {
    color: #0f172a !important;
    margin-bottom: 5px !important;
  }

  .post-show-shell .ps-post-edited-detail {
    color: #64748b !important;
  }

  /* Tıklamadan hover ile açılan kullanıcı/kategori kartları */
  .post-show-shell .ps-hover-zone {
    position: relative;
    display: inline-flex;
    align-items: center;
    min-width: 0;
    outline: none !important;
  }

  .post-show-shell .ps-hover-zone--avatar {
    width: 46px;
    height: 46px;
    border-radius: 999px;
  }

  .post-show-shell .ps-hover-zone--category-badge {
    position: absolute;
    right: -7px;
    bottom: -6px;
    width: 30px;
    height: 30px;
    z-index: 8;
    border-radius: 999px;
  }

  .post-show-shell .ps-hover-zone--category-badge .post-author-badge {
    position: relative !important;
    right: auto !important;
    bottom: auto !important;
    width: 30px !important;
    height: 30px !important;
    flex: 0 0 30px !important;
  }

  .post-show-shell .ps-hover-zone--inline {
    width: fit-content;
    max-width: 100%;
  }

  .post-show-shell .ps-hover-card {
    position: absolute;
    left: 0;
    top: calc(100% + 10px);
    z-index: 9999;
    display: block;
    width: min(292px, calc(100vw - 28px));
    padding: 0 0 12px;
    border: 1px solid rgba(226, 232, 240, .95);
    border-radius: 16px;
    background: #ffffff;
    color: #0f172a;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translate3d(0, 6px, 0);
    pointer-events: none;
    transition: opacity .14s ease, visibility .14s ease, transform .14s ease;
    box-shadow: none !important;
    text-align: left;
    white-space: normal;
    font-family: Roboto, Arial, sans-serif;
  }

  .post-show-shell .ps-hover-zone:hover > .ps-hover-card,
  .post-show-shell .ps-hover-zone:focus-within > .ps-hover-card {
    opacity: 1;
    visibility: visible;
    transform: translate3d(0, 0, 0);
    pointer-events: auto;
  }

  .post-show-shell .ps-hover-zone--category-badge .ps-hover-card {
    left: -8px;
    top: calc(100% + 10px);
  }

  .post-show-shell .ps-hover-zone--author-name .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name .ps-hover-card {
    top: calc(100% + 8px);
  }

  .post-show-shell .ps-hover-card-cover {
    display: block;
    width: 100%;
    height: 72px;
    background: linear-gradient(135deg, #eef2ff 0%, #eff6ff 48%, #f8fafc 100%);
    overflow: hidden;
  }

  .post-show-shell .ps-hover-card-cover--category {
    background: linear-gradient(135deg, #ecfdf5 0%, #eef2ff 100%);
  }

  .post-show-shell .ps-hover-card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .post-show-shell .ps-hover-card-main {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    padding: 0 13px;
    margin-top: -22px;
    min-width: 0;
  }

  .post-show-shell .ps-hover-card-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    flex: 0 0 54px;
    border: 3px solid #ffffff;
    border-radius: 999px;
    overflow: hidden;
    background: #f1f5f9;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
  }

  .post-show-shell .ps-hover-card-avatar--category {
    background: #10b981;
    color: #ffffff;
  }

  .post-show-shell .ps-hover-card-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .post-show-shell .ps-hover-card-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
    padding-bottom: 4px;
  }

  .post-show-shell .ps-hover-card-title {
    display: block;
    max-width: 188px;
    overflow: hidden;
    color: #0f172a;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.2;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .post-show-shell .ps-hover-card-subtitle {
    display: block;
    max-width: 188px;
    overflow: hidden;
    color: #64748b;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.25;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .post-show-shell .ps-hover-card-description {
    display: block;
    padding: 10px 13px 0;
    color: #475569;
    font-size: 12.5px;
    font-weight: 400;
    line-height: 1.45;
  }

  .post-show-shell .ps-hover-card-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 13px 0;
    color: #64748b;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.2;
  }

  .post-show-shell .ps-hover-card-stats strong {
    color: #0f172a;
    font-weight: 500;
  }

  .post-show-shell .ps-hover-card-link {
    display: inline-flex;
    width: calc(100% - 26px);
    justify-content: center;
    margin: 12px 13px 0;
    padding: 8px 10px;
    border-radius: 10px;
    background: #f8fafc;
    color: #2563eb;
    font-size: 12px;
    font-weight: 500;
    line-height: 1;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-hover-card {
      position: fixed;
      left: 12px !important;
      right: 12px !important;
      top: auto !important;
      bottom: 16px !important;
      width: auto !important;
      max-width: none !important;
      transform: translate3d(0, 10px, 0);
    }

    .post-show-shell .ps-hover-zone:hover > .ps-hover-card,
    .post-show-shell .ps-hover-zone:focus-within > .ps-hover-card {
      transform: translate3d(0, 0, 0);
    }
  }

  html.dark .post-show-shell .ps-post-edited-title,
  body.dark .post-show-shell .ps-post-edited-title,
  .dark .post-show-shell .ps-post-edited-title,
  [data-theme="dark"] .post-show-shell .ps-post-edited-title {
    color: #f8fafc !important;
  }

  html.dark .post-show-shell .ps-post-edited-detail,
  body.dark .post-show-shell .ps-post-edited-detail,
  .dark .post-show-shell .ps-post-edited-detail,
  [data-theme="dark"] .post-show-shell .ps-post-edited-detail {
    color: #cbd5e1 !important;
  }

  html.dark .post-show-shell .ps-hover-card,
  body.dark .post-show-shell .ps-hover-card,
  .dark .post-show-shell .ps-hover-card,
  [data-theme="dark"] .post-show-shell .ps-hover-card {
    background: #0f172a;
    border-color: rgba(148, 163, 184, .22);
    color: #f8fafc;
  }

  html.dark .post-show-shell .ps-hover-card-cover,
  body.dark .post-show-shell .ps-hover-card-cover,
  .dark .post-show-shell .ps-hover-card-cover,
  [data-theme="dark"] .post-show-shell .ps-hover-card-cover {
    background: linear-gradient(135deg, #1e293b 0%, #111827 100%);
  }

  html.dark .post-show-shell .ps-hover-card-avatar,
  body.dark .post-show-shell .ps-hover-card-avatar,
  .dark .post-show-shell .ps-hover-card-avatar,
  [data-theme="dark"] .post-show-shell .ps-hover-card-avatar {
    border-color: #0f172a;
    background: #1e293b;
    color: #e2e8f0;
  }

  html.dark .post-show-shell .ps-hover-card-title,
  body.dark .post-show-shell .ps-hover-card-title,
  .dark .post-show-shell .ps-hover-card-title,
  [data-theme="dark"] .post-show-shell .ps-hover-card-title,
  html.dark .post-show-shell .ps-hover-card-stats strong,
  body.dark .post-show-shell .ps-hover-card-stats strong,
  .dark .post-show-shell .ps-hover-card-stats strong,
  [data-theme="dark"] .post-show-shell .ps-hover-card-stats strong {
    color: #f8fafc;
  }

  html.dark .post-show-shell .ps-hover-card-subtitle,
  body.dark .post-show-shell .ps-hover-card-subtitle,
  .dark .post-show-shell .ps-hover-card-subtitle,
  [data-theme="dark"] .post-show-shell .ps-hover-card-subtitle,
  html.dark .post-show-shell .ps-hover-card-description,
  body.dark .post-show-shell .ps-hover-card-description,
  .dark .post-show-shell .ps-hover-card-description,
  [data-theme="dark"] .post-show-shell .ps-hover-card-description,
  html.dark .post-show-shell .ps-hover-card-stats,
  body.dark .post-show-shell .ps-hover-card-stats,
  .dark .post-show-shell .ps-hover-card-stats,
  [data-theme="dark"] .post-show-shell .ps-hover-card-stats {
    color: #cbd5e1;
  }

  html.dark .post-show-shell .ps-hover-card-link,
  body.dark .post-show-shell .ps-hover-card-link,
  .dark .post-show-shell .ps-hover-card-link,
  [data-theme="dark"] .post-show-shell .ps-hover-card-link {
    background: rgba(255,255,255,.08);
    color: #93c5fd;
  }


  /* FINAL: hover profil kartı - bio + gerçek takip butonu */
  .post-show-shell .ps-hover-card--user .ps-hover-card-content {
    transform: translateY(7px) !important;
    padding-bottom: 0 !important;
  }

  .post-show-shell .ps-hover-card--user .ps-hover-card-description {
    min-height: 34px;
    padding: 14px 13px 0 !important;
    color: #475569 !important;
    font-size: 12.5px !important;
    font-weight: 400 !important;
    line-height: 1.45 !important;
    overflow-wrap: anywhere;
    word-break: normal;
  }

  .post-show-shell .ps-hover-card--user .ps-hover-card-stats {
    display: none !important;
  }

  .post-show-shell .ps-hover-card-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    width: calc(100% - 26px);
    margin: 12px 13px 0;
  }

  .post-show-shell .ps-hover-card-follow-form {
    display: flex;
    flex: 1 1 0;
    min-width: 0;
    margin: 0 !important;
    padding: 0 !important;
  }

  .post-show-shell .ps-hover-card-follow,
  .post-show-shell .ps-hover-card-link {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    margin: 0 !important;
    padding: 9px 10px !important;
    border-radius: 10px;
    border: 1px solid transparent;
    font-family: Roboto, Arial, sans-serif;
    font-size: 12px;
    font-weight: 500;
    line-height: 1;
    text-decoration: none !important;
    white-space: nowrap;
    box-shadow: none !important;
    cursor: pointer;
  }

  .post-show-shell .ps-hover-card-follow {
    flex: 1 1 0;
    width: 100%;
    background: #2563eb !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
  }

  .post-show-shell .ps-hover-card-follow:hover,
  .post-show-shell .ps-hover-card-follow:focus-visible {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
    outline: none !important;
  }

  .post-show-shell .ps-hover-card-follow:disabled,
  .post-show-shell .ps-hover-card-follow[aria-disabled="true"] {
    background: #e8f0ff !important;
    border-color: #dbeafe !important;
    color: #2563eb !important;
    cursor: default;
  }

  .post-show-shell .ps-hover-card-actions > .ps-hover-card-link {
    flex: 1 1 0;
    width: auto !important;
    background: #f8fafc !important;
    color: #2563eb !important;
    border-color: #eef2f7 !important;
  }

  .post-show-shell .ps-hover-card-actions > .ps-hover-card-link:hover,
  .post-show-shell .ps-hover-card-actions > .ps-hover-card-link:focus-visible {
    background: #eff6ff !important;
    color: #1d4ed8 !important;
    outline: none !important;
  }

  .post-show-shell .ps-hover-card-actions > .ps-hover-card-link:only-child {
    flex-basis: 100%;
  }

  @media (max-width: 420px) {
    .post-show-shell .ps-hover-card-actions {
      gap: 7px;
    }

    .post-show-shell .ps-hover-card-follow,
    .post-show-shell .ps-hover-card-link {
      font-size: 11.5px !important;
      padding-left: 8px !important;
      padding-right: 8px !important;
    }
  }

  html.dark .post-show-shell .ps-hover-card--user .ps-hover-card-description,
  body.dark .post-show-shell .ps-hover-card--user .ps-hover-card-description,
  .dark .post-show-shell .ps-hover-card--user .ps-hover-card-description,
  [data-theme="dark"] .post-show-shell .ps-hover-card--user .ps-hover-card-description {
    color: #cbd5e1 !important;
  }

  html.dark .post-show-shell .ps-hover-card-actions > .ps-hover-card-link,
  body.dark .post-show-shell .ps-hover-card-actions > .ps-hover-card-link,
  .dark .post-show-shell .ps-hover-card-actions > .ps-hover-card-link,
  [data-theme="dark"] .post-show-shell .ps-hover-card-actions > .ps-hover-card-link {
    background: rgba(255,255,255,.08) !important;
    border-color: rgba(255,255,255,.10) !important;
    color: #93c5fd !important;
  }

  html.dark .post-show-shell .ps-hover-card-follow:disabled,
  body.dark .post-show-shell .ps-hover-card-follow:disabled,
  .dark .post-show-shell .ps-hover-card-follow:disabled,
  [data-theme="dark"] .post-show-shell .ps-hover-card-follow:disabled,
  html.dark .post-show-shell .ps-hover-card-follow[aria-disabled="true"],
  body.dark .post-show-shell .ps-hover-card-follow[aria-disabled="true"],
  .dark .post-show-shell .ps-hover-card-follow[aria-disabled="true"],
  [data-theme="dark"] .post-show-shell .ps-hover-card-follow[aria-disabled="true"] {
    background: rgba(37, 99, 235,.18) !important;
    border-color: rgba(147,197,253,.22) !important;
    color: #bfdbfe !important;
  }
</style>
@endpush


@push('head')
<style>
  /* FINAL FIX: hover kart 1 sn bekler, kategori sayaçları yok, kutuya geçerken kaybolmaz */
  .post-show-shell .ps-hover-zone--avatar > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-badge > .ps-hover-card {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }

  .post-show-shell .ps-hover-zone--avatar,
  .post-show-shell .ps-hover-zone--category-badge {
    cursor: default !important;
  }

  .post-show-shell .ps-hover-zone--author-name,
  .post-show-shell .ps-hover-zone--category-name {
    cursor: pointer !important;
  }

  .post-show-shell .ps-hover-zone--author-name:hover > .ps-hover-card,
  .post-show-shell .ps-hover-zone--author-name:focus-within > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name:hover > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name:focus-within > .ps-hover-card {
    opacity: 0 !important;
    visibility: hidden !important;
    transform: translate3d(0, 6px, 0) !important;
    pointer-events: none !important;
  }

  .post-show-shell .ps-hover-zone--author-name.is-hover-loading > .ps-hover-card,
  .post-show-shell .ps-hover-zone--author-name.is-hover-ready > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name.is-hover-loading > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name.is-hover-ready > .ps-hover-card {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translate3d(0, 0, 0) !important;
    pointer-events: auto !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card > *,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card > * {
    animation: none !important;
  }

  .post-show-shell .ps-hover-zone--author-name.is-hover-loading > .ps-hover-card > *,
  .post-show-shell .ps-hover-zone--category-name.is-hover-loading > .ps-hover-card > * {
    opacity: 0 !important;
    visibility: hidden !important;
  }

  .post-show-shell .ps-hover-zone--author-name.is-hover-ready > .ps-hover-card > *,
  .post-show-shell .ps-hover-zone--category-name.is-hover-ready > .ps-hover-card > * {
    opacity: 1 !important;
    visibility: visible !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card::after,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card::after {
    opacity: 0 !important;
    visibility: hidden !important;
    animation: none !important;
    pointer-events: none !important;
  }

  .post-show-shell .ps-hover-zone--author-name.is-hover-loading > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--category-name.is-hover-loading > .ps-hover-card::before {
    opacity: 1 !important;
    visibility: visible !important;
  }

  .post-show-shell .ps-hover-zone--author-name.is-hover-loading > .ps-hover-card::after,
  .post-show-shell .ps-hover-zone--category-name.is-hover-loading > .ps-hover-card::after {
    opacity: 1 !important;
    visibility: visible !important;
    animation: psHoverCardFinalShimmer .78s ease-in-out infinite !important;
  }

  .post-show-shell .ps-hover-zone--author-name.is-hover-ready > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--author-name.is-hover-ready > .ps-hover-card::after,
  .post-show-shell .ps-hover-zone--category-name.is-hover-ready > .ps-hover-card::before,
  .post-show-shell .ps-hover-zone--category-name.is-hover-ready > .ps-hover-card::after {
    opacity: 0 !important;
    visibility: hidden !important;
    animation: none !important;
  }

  @keyframes psHoverCardFinalShimmer {
    from { transform: translateX(-120%); }
    to { transform: translateX(120%); }
  }


  /* FINAL FIX: kategori hover kartındaki sayaçlar kaldırıldı */
  .post-show-shell .ps-hover-card--category .ps-hover-card-stats {
    display: none !important;
  }

  .post-show-shell .ps-hover-zone--author-name .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name .ps-hover-card {
    top: calc(100% + 4px) !important;
  }
  @media (max-width: 640px) {
    .post-show-shell .ps-hover-zone--author-name.is-hover-loading > .ps-hover-card,
    .post-show-shell .ps-hover-zone--author-name.is-hover-ready > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name.is-hover-loading > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name.is-hover-ready > .ps-hover-card {
      transform: translate3d(0, 0, 0) !important;
    }
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  var OPEN_DELAY = 1000;
  var LOADER_DURATION = 650;
  var CLOSE_DELAY = 500;
  var selector = '.post-show-shell .ps-hover-zone--author-name, .post-show-shell .ps-hover-zone--category-name';
  var zones = document.querySelectorAll(selector);

  zones.forEach(function (zone) {
    var openTimer = null;
    var readyTimer = null;
    var closeTimer = null;
    var card = zone.querySelector('.ps-hover-card');

    function clearOpenReadyTimers() {
      if (openTimer) {
        window.clearTimeout(openTimer);
        openTimer = null;
      }

      if (readyTimer) {
        window.clearTimeout(readyTimer);
        readyTimer = null;
      }
    }

    function clearCloseTimer() {
      if (closeTimer) {
        window.clearTimeout(closeTimer);
        closeTimer = null;
      }
    }

    function closeCardNow() {
      clearOpenReadyTimers();
      clearCloseTimer();
      zone.classList.remove('is-hover-loading');
      zone.classList.remove('is-hover-ready');
    }

    function scheduleClose() {
      clearCloseTimer();
      closeTimer = window.setTimeout(closeCardNow, CLOSE_DELAY);
    }

    function openCard() {
      clearCloseTimer();

      if (zone.classList.contains('is-hover-loading') || zone.classList.contains('is-hover-ready')) {
        return;
      }

      clearOpenReadyTimers();

      openTimer = window.setTimeout(function () {
        zone.classList.add('is-hover-loading');

        readyTimer = window.setTimeout(function () {
          zone.classList.remove('is-hover-loading');
          zone.classList.add('is-hover-ready');
        }, LOADER_DURATION);
      }, OPEN_DELAY);
    }

    function handlePointerLeave(event) {
      if (card && event.relatedTarget && card.contains(event.relatedTarget)) {
        clearCloseTimer();
        return;
      }

      scheduleClose();
    }

    zone.addEventListener('mouseenter', openCard);
    zone.addEventListener('mouseleave', handlePointerLeave);
    zone.addEventListener('focusin', openCard);
    zone.addEventListener('focusout', function (event) {
      if (!event.relatedTarget || !zone.contains(event.relatedTarget)) {
        scheduleClose();
      }
    });

    if (card) {
      card.addEventListener('mouseenter', clearCloseTimer);
      card.addEventListener('mouseleave', scheduleClose);
    }
  });
})();
</script>
@endpush


@push('head')
<style>
  /* FINAL FIX: uzun mobil gönderiler için altta ortalı tepki/yorum/paylaş barı */
  .post-show-shell .ps-mobile-long-actions {
    display: none !important;
  }

  @media (max-width: 768px) {
    .post-show-shell.has-mobile-long-actions {
      padding-bottom: calc(112px + env(safe-area-inset-bottom, 0px)) !important;
    }

    .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions {
      position: fixed !important;
      left: 50% !important;
      right: auto !important;
      bottom: calc(14px + env(safe-area-inset-bottom, 0px)) !important;
      z-index: 1200 !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 8px !important;
      width: auto !important;
      max-width: calc(100vw - 28px) !important;
      min-height: 42px !important;
      padding: 5px 10px !important;
      border: 1px solid rgba(37, 99, 235, .18) !important;
      border-radius: 999px !important;
      background: rgba(255, 255, 255, .96) !important;
      box-shadow: none !important;
      transform: translate3d(-50%, 16px, 0) scale(.98) !important;
      opacity: 0 !important;
      visibility: hidden !important;
      pointer-events: none !important;
      transition: opacity .18s ease, transform .18s ease, visibility .18s ease !important;
      -webkit-backdrop-filter: blur(10px) !important;
      backdrop-filter: blur(10px) !important;
    }

    .post-show-shell.has-mobile-long-actions.is-mobile-long-actions-visible .ps-mobile-long-actions {
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
      transform: translate3d(-50%, 0, 0) scale(1) !important;
    }

    .post-show-shell .ps-mobile-long-action-form {
      display: inline-flex !important;
      align-items: center !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    .post-show-shell .ps-mobile-long-action {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 5px !important;
      min-width: 38px !important;
      height: 32px !important;
      padding: 0 8px !important;
      border: 0 !important;
      border-radius: 999px !important;
      background: transparent !important;
      color: #111827 !important;
      box-shadow: none !important;
      outline: none !important;
      text-decoration: none !important;
      font-family: Roboto, Arial, sans-serif !important;
      font-size: 13px !important;
      font-weight: 500 !important;
      line-height: 1 !important;
      cursor: pointer !important;
      -webkit-tap-highlight-color: transparent !important;
    }

    .post-show-shell .ps-mobile-long-action:hover,
    .post-show-shell .ps-mobile-long-action:focus-visible,
    .post-show-shell .ps-mobile-long-action:active {
      background: #f3f7ff !important;
      color: #2563eb !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-long-action--like {
      color: #2563eb !important;
    }

    .post-show-shell .ps-mobile-long-action svg {
      width: 20px !important;
      height: 20px !important;
      display: block !important;
      flex: 0 0 auto !important;
    }

    .post-show-shell .ps-mobile-long-action span {
      display: inline-block !important;
      min-width: 8px !important;
      color: currentColor !important;
      font: inherit !important;
      line-height: 1 !important;
    }
  }

  html.dark .post-show-shell .ps-mobile-long-actions,
  body.dark .post-show-shell .ps-mobile-long-actions,
  .dark .post-show-shell .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions {
    background: rgba(15, 23, 42, .96) !important;
    border-color: rgba(147, 197, 253, .18) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-long-action,
  body.dark .post-show-shell .ps-mobile-long-action,
  .dark .post-show-shell .ps-mobile-long-action,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action {
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-mobile-long-action--like,
  body.dark .post-show-shell .ps-mobile-long-action--like,
  .dark .post-show-shell .ps-mobile-long-action--like,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action--like {
    color: #60a5fa !important;
  }

  html.dark .post-show-shell .ps-mobile-long-action:hover,
  body.dark .post-show-shell .ps-mobile-long-action:hover,
  .dark .post-show-shell .ps-mobile-long-action:hover,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action:hover,
  html.dark .post-show-shell .ps-mobile-long-action:focus-visible,
  body.dark .post-show-shell .ps-mobile-long-action:focus-visible,
  .dark .post-show-shell .ps-mobile-long-action:focus-visible,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action:focus-visible {
    background: rgba(96, 165, 250, .12) !important;
    color: #93c5fd !important;
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  var shell = document.querySelector('.post-show-shell');
  var bar = document.querySelector('[data-mobile-long-actions]');
  var postCard = document.querySelector('.post-show-shell .ps-post-card');
  var postBody = document.querySelector('.post-show-shell .ps-post-body');

  if (!shell || !bar || !postCard) return;

  function isMobile() {
    return window.matchMedia('(max-width: 768px)').matches;
  }

  function isLongPost() {
    var viewport = Math.max(window.innerHeight || 0, 640);
    var cardHeight = postCard.getBoundingClientRect().height || postCard.scrollHeight || 0;
    var bodyHeight = postBody ? (postBody.getBoundingClientRect().height || postBody.scrollHeight || 0) : 0;
    var textLength = postBody ? ((postBody.innerText || postBody.textContent || '').trim().length) : 0;

    return cardHeight > viewport * 1.18 || bodyHeight > viewport * 0.72 || textLength > 900;
  }

  function updateMobileLongActions() {
    var enabled = isLongPost();
    shell.classList.toggle('has-mobile-long-actions', enabled);
    bar.setAttribute('aria-hidden', enabled ? 'false' : 'true');

    if (!enabled) {
      shell.classList.remove('is-mobile-long-actions-visible');
      return;
    }

    var scrollTop = window.scrollY || document.documentElement.scrollTop || 0;
    var cardRect = postCard.getBoundingClientRect();
    var comments = document.getElementById('comments') || document.querySelector('.ps-comments-section');
    var commentsTop = comments ? comments.getBoundingClientRect().top : Infinity;
    var show = scrollTop > 120 && cardRect.bottom > 160 && commentsTop > 150;

    shell.classList.toggle('is-mobile-long-actions-visible', show);
  }

  var ticking = false;
  function requestUpdate() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(function () {
      ticking = false;
      updateMobileLongActions();
    });
  }

  window.addEventListener('scroll', requestUpdate, { passive: true });
  window.addEventListener('resize', requestUpdate, { passive: true });
  window.addEventListener('orientationchange', requestUpdate, { passive: true });
  window.addEventListener('load', requestUpdate);

  if (window.ResizeObserver) {
    var observer = new ResizeObserver(requestUpdate);
    observer.observe(postCard);
    if (postBody) observer.observe(postBody);
  }

  setTimeout(requestUpdate, 350);
  setTimeout(requestUpdate, 1000);
})();
</script>
@endpush


@push('head')
<style>
  /* FINAL FIX: mobil alt bar blur arka plan + erişilebilirlik menüsü */
  @media (max-width: 768px) {
    .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions {
      background: rgba(255, 255, 255, .68) !important;
      border-color: rgba(15, 23, 42, .10) !important;
      -webkit-backdrop-filter: blur(18px) saturate(1.35) !important;
      backdrop-filter: blur(18px) saturate(1.35) !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-long-action--accessibility {
      color: #0f766e !important;
    }

    .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"] {
      background: rgba(15, 118, 110, .10) !important;
      color: #0f766e !important;
    }

    .post-show-shell .ps-mobile-accessibility-backdrop {
      position: fixed !important;
      inset: 0 !important;
      z-index: 1210 !important;
      display: block !important;
      background: rgba(15, 23, 42, .12) !important;
      -webkit-backdrop-filter: blur(4px) !important;
      backdrop-filter: blur(4px) !important;
      opacity: 1 !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-backdrop[hidden] {
      display: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-menu {
      position: fixed !important;
      left: 50% !important;
      bottom: calc(68px + env(safe-area-inset-bottom, 0px)) !important;
      z-index: 1220 !important;
      display: flex !important;
      flex-direction: column !important;
      width: min(286px, calc(100vw - 28px)) !important;
      padding: 9px !important;
      border: 1px solid rgba(15, 23, 42, .10) !important;
      border-radius: 18px !important;
      background: rgba(255, 255, 255, .74) !important;
      -webkit-backdrop-filter: blur(20px) saturate(1.4) !important;
      backdrop-filter: blur(20px) saturate(1.4) !important;
      box-shadow: none !important;
      transform: translate3d(-50%, 0, 0) !important;
      opacity: 1 !important;
      pointer-events: auto !important;
    }

    .post-show-shell .ps-mobile-accessibility-menu[hidden] {
      display: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-item {
      display: grid !important;
      grid-template-columns: 34px 1fr !important;
      align-items: center !important;
      gap: 10px !important;
      width: 100% !important;
      min-height: 42px !important;
      padding: 6px 10px !important;
      border: 0 !important;
      border-radius: 13px !important;
      background: transparent !important;
      color: #111827 !important;
      box-shadow: none !important;
      outline: none !important;
      cursor: pointer !important;
      text-align: left !important;
      font-family: Roboto, Arial, sans-serif !important;
      font-size: 14px !important;
      font-weight: 500 !important;
      line-height: 1.2 !important;
      -webkit-tap-highlight-color: transparent !important;
    }

    .post-show-shell .ps-mobile-accessibility-item:hover,
    .post-show-shell .ps-mobile-accessibility-item:focus-visible,
    .post-show-shell .ps-mobile-accessibility-item:active {
      background: rgba(37, 99, 235, .10) !important;
      color: #2563eb !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-item-icon {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 34px !important;
      height: 34px !important;
      border-radius: 999px !important;
      background: rgba(15, 23, 42, .06) !important;
      color: currentColor !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-item-icon svg {
      width: 20px !important;
      height: 20px !important;
      display: block !important;
    }

    .post-show-shell.is-mobile-reading-larger .ps-post-body,
    .post-show-shell.is-mobile-reading-larger .ps-post-title {
      font-size: calc(1em + 2px) !important;
      line-height: 1.72 !important;
    }

    .post-show-shell.is-mobile-reading-largest .ps-post-body,
    .post-show-shell.is-mobile-reading-largest .ps-post-title {
      font-size: calc(1em + 4px) !important;
      line-height: 1.78 !important;
    }

    .post-show-shell.is-mobile-reading-small .ps-post-body,
    .post-show-shell.is-mobile-reading-small .ps-post-title {
      font-size: calc(1em - 1px) !important;
      line-height: 1.6 !important;
    }

    .post-show-shell.is-mobile-color-sepia .ps-post-card,
    .post-show-shell.is-mobile-color-sepia .ps-comments-section {
      background: #fff8ea !important;
      color: #2f2418 !important;
    }

    .post-show-shell.is-mobile-color-sepia .ps-post-title,
    .post-show-shell.is-mobile-color-sepia .ps-post-body,
    .post-show-shell.is-mobile-color-sepia .ps-post-body * {
      color: #2f2418 !important;
    }

    .post-show-shell.is-mobile-color-contrast .ps-post-card,
    .post-show-shell.is-mobile-color-contrast .ps-comments-section {
      background: #0f172a !important;
      color: #f8fafc !important;
    }

    .post-show-shell.is-mobile-color-contrast .ps-post-title,
    .post-show-shell.is-mobile-color-contrast .ps-post-body,
    .post-show-shell.is-mobile-color-contrast .ps-post-body * {
      color: #f8fafc !important;
    }
  }

  html.dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  body.dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  .dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions {
    background: rgba(15, 23, 42, .68) !important;
    border-color: rgba(148, 163, 184, .14) !important;
    -webkit-backdrop-filter: blur(18px) saturate(1.35) !important;
    backdrop-filter: blur(18px) saturate(1.35) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-accessibility-backdrop,
  body.dark .post-show-shell .ps-mobile-accessibility-backdrop,
  .dark .post-show-shell .ps-mobile-accessibility-backdrop,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-backdrop {
    background: rgba(0, 0, 0, .18) !important;
  }

  html.dark .post-show-shell .ps-mobile-accessibility-menu,
  body.dark .post-show-shell .ps-mobile-accessibility-menu,
  .dark .post-show-shell .ps-mobile-accessibility-menu,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-menu {
    background: rgba(15, 23, 42, .76) !important;
    border-color: rgba(148, 163, 184, .16) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-accessibility-item,
  body.dark .post-show-shell .ps-mobile-accessibility-item,
  .dark .post-show-shell .ps-mobile-accessibility-item,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-item {
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-mobile-accessibility-item:hover,
  body.dark .post-show-shell .ps-mobile-accessibility-item:hover,
  .dark .post-show-shell .ps-mobile-accessibility-item:hover,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-item:hover,
  html.dark .post-show-shell .ps-mobile-accessibility-item:focus-visible,
  body.dark .post-show-shell .ps-mobile-accessibility-item:focus-visible,
  .dark .post-show-shell .ps-mobile-accessibility-item:focus-visible,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-item:focus-visible {
    background: rgba(96, 165, 250, .14) !important;
    color: #93c5fd !important;
  }

  html.dark .post-show-shell .ps-mobile-accessibility-item-icon,
  body.dark .post-show-shell .ps-mobile-accessibility-item-icon,
  .dark .post-show-shell .ps-mobile-accessibility-item-icon,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-item-icon {
    background: rgba(148, 163, 184, .12) !important;
  }
</style>
@endpush


@push('scripts')
<script>
(function () {
  'use strict';

  var shell = document.querySelector('.post-show-shell');
  var trigger = document.querySelector('[data-mobile-accessibility-trigger]');
  var menu = document.querySelector('[data-mobile-accessibility-menu]');
  var backdrop = document.querySelector('[data-mobile-accessibility-backdrop]');
  var readButton = document.querySelector('[data-mobile-a11y-read]');
  var plusButton = document.querySelector('[data-mobile-a11y-font-plus]');
  var minusButton = document.querySelector('[data-mobile-a11y-font-minus]');
  var colorButton = document.querySelector('[data-mobile-a11y-color]');
  var postBody = document.querySelector('.post-show-shell .ps-post-body');
  var postTitle = document.querySelector('.post-show-shell .ps-post-title');

  if (!shell || !trigger || !menu || !backdrop) return;

  var fontLevel = 0;
  var colorLevel = 0;
  var reading = false;

  function openMenu() {
    menu.hidden = false;
    backdrop.hidden = false;
    menu.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
  }

  function closeMenu() {
    menu.hidden = true;
    backdrop.hidden = true;
    menu.setAttribute('aria-hidden', 'true');
    trigger.setAttribute('aria-expanded', 'false');
  }

  function toggleMenu(event) {
    if (event) event.preventDefault();
    if (menu.hidden) openMenu(); else closeMenu();
  }

  function clearFontClasses() {
    shell.classList.remove('is-mobile-reading-small', 'is-mobile-reading-larger', 'is-mobile-reading-largest');
  }

  function applyFontLevel() {
    clearFontClasses();
    if (fontLevel <= -1) shell.classList.add('is-mobile-reading-small');
    if (fontLevel === 1) shell.classList.add('is-mobile-reading-larger');
    if (fontLevel >= 2) shell.classList.add('is-mobile-reading-largest');
  }

  function clearColorClasses() {
    shell.classList.remove('is-mobile-color-sepia', 'is-mobile-color-contrast');
  }

  function applyColorLevel() {
    clearColorClasses();
    if (colorLevel === 1) shell.classList.add('is-mobile-color-sepia');
    if (colorLevel === 2) shell.classList.add('is-mobile-color-contrast');
  }

  function getReadableText() {
    var title = postTitle ? (postTitle.innerText || postTitle.textContent || '').trim() : '';
    var body = postBody ? (postBody.innerText || postBody.textContent || '').trim() : '';
    return (title + '. ' + body).replace(/\s+/g, ' ').trim();
  }

  function toggleReadAloud() {
    if (!('speechSynthesis' in window)) return;

    if (reading) {
      window.speechSynthesis.cancel();
      reading = false;
      if (readButton) readButton.classList.remove('is-active');
      return;
    }

    var text = getReadableText();
    if (!text) return;

    window.speechSynthesis.cancel();
    var utterance = new SpeechSynthesisUtterance(text.slice(0, 5000));
    utterance.lang = document.documentElement.lang || 'tr-TR';
    utterance.rate = 0.95;
    utterance.onend = function () {
      reading = false;
      if (readButton) readButton.classList.remove('is-active');
    };
    utterance.onerror = utterance.onend;
    reading = true;
    if (readButton) readButton.classList.add('is-active');
    window.speechSynthesis.speak(utterance);
  }

  trigger.addEventListener('click', toggleMenu);
  backdrop.addEventListener('click', closeMenu);

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeMenu();
  });

  if (readButton) {
    readButton.addEventListener('click', function () {
      toggleReadAloud();
    });
  }

  if (plusButton) {
    plusButton.addEventListener('click', function () {
      fontLevel = Math.min(2, fontLevel + 1);
      applyFontLevel();
    });
  }

  if (minusButton) {
    minusButton.addEventListener('click', function () {
      fontLevel = Math.max(-1, fontLevel - 1);
      applyFontLevel();
    });
  }

  if (colorButton) {
    colorButton.addEventListener('click', function () {
      colorLevel = (colorLevel + 1) % 3;
      applyColorLevel();
    });
  }
})();
</script>
@endpush


@push('head')
<style>
  /* FINAL FIX: alt blur aksiyon barı artık sadece mobilde değil, tüm ekranlarda çalışır */
  @media (min-width: 769px) {
    .post-show-shell.has-mobile-long-actions {
      padding-bottom: 96px !important;
    }

    .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions {
      position: fixed !important;
      left: 50% !important;
      right: auto !important;
      bottom: 24px !important;
      z-index: 1200 !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 9px !important;
      width: auto !important;
      max-width: min(420px, calc(100vw - 44px)) !important;
      min-height: 44px !important;
      padding: 6px 12px !important;
      border: 1px solid rgba(15, 23, 42, .10) !important;
      border-radius: 999px !important;
      background: rgba(255, 255, 255, .68) !important;
      -webkit-backdrop-filter: blur(18px) saturate(1.35) !important;
      backdrop-filter: blur(18px) saturate(1.35) !important;
      box-shadow: none !important;
      transform: translate3d(-50%, 16px, 0) scale(.98) !important;
      opacity: 0 !important;
      visibility: hidden !important;
      pointer-events: none !important;
      transition: opacity .18s ease, transform .18s ease, visibility .18s ease !important;
    }

    .post-show-shell.has-mobile-long-actions.is-mobile-long-actions-visible .ps-mobile-long-actions {
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
      transform: translate3d(-50%, 0, 0) scale(1) !important;
    }

    .post-show-shell .ps-mobile-long-action-form {
      display: inline-flex !important;
      align-items: center !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    .post-show-shell .ps-mobile-long-action {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 5px !important;
      min-width: 38px !important;
      height: 32px !important;
      padding: 0 8px !important;
      border: 0 !important;
      border-radius: 999px !important;
      background: transparent !important;
      color: #111827 !important;
      box-shadow: none !important;
      outline: none !important;
      text-decoration: none !important;
      font-family: Roboto, Arial, sans-serif !important;
      font-size: 13px !important;
      font-weight: 500 !important;
      line-height: 1 !important;
      cursor: pointer !important;
      -webkit-tap-highlight-color: transparent !important;
    }

    .post-show-shell .ps-mobile-long-action:hover,
    .post-show-shell .ps-mobile-long-action:focus-visible,
    .post-show-shell .ps-mobile-long-action:active {
      background: rgba(37, 99, 235, .10) !important;
      color: #2563eb !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-long-action--like {
      color: #2563eb !important;
    }

    .post-show-shell .ps-mobile-long-action--accessibility {
      color: #0f766e !important;
    }

    .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"] {
      background: rgba(15, 118, 110, .10) !important;
      color: #0f766e !important;
    }

    .post-show-shell .ps-mobile-long-action svg {
      width: 20px !important;
      height: 20px !important;
      display: block !important;
      flex: 0 0 auto !important;
    }

    .post-show-shell .ps-mobile-long-action span {
      display: inline-block !important;
      min-width: 8px !important;
      color: currentColor !important;
      font: inherit !important;
      line-height: 1 !important;
    }

    .post-show-shell .ps-mobile-accessibility-backdrop {
      position: fixed !important;
      inset: 0 !important;
      z-index: 1210 !important;
      display: block !important;
      background: rgba(15, 23, 42, .12) !important;
      -webkit-backdrop-filter: blur(4px) !important;
      backdrop-filter: blur(4px) !important;
      opacity: 1 !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-backdrop[hidden] {
      display: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-menu {
      position: fixed !important;
      left: 50% !important;
      bottom: 82px !important;
      z-index: 1220 !important;
      display: flex !important;
      flex-direction: column !important;
      width: min(300px, calc(100vw - 44px)) !important;
      padding: 10px !important;
      border: 1px solid rgba(15, 23, 42, .10) !important;
      border-radius: 18px !important;
      background: rgba(255, 255, 255, .74) !important;
      -webkit-backdrop-filter: blur(20px) saturate(1.4) !important;
      backdrop-filter: blur(20px) saturate(1.4) !important;
      box-shadow: none !important;
      transform: translate3d(-50%, 0, 0) !important;
      opacity: 1 !important;
      pointer-events: auto !important;
    }

    .post-show-shell .ps-mobile-accessibility-menu[hidden] {
      display: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-item {
      display: grid !important;
      grid-template-columns: 34px 1fr !important;
      align-items: center !important;
      gap: 10px !important;
      width: 100% !important;
      min-height: 42px !important;
      padding: 6px 10px !important;
      border: 0 !important;
      border-radius: 13px !important;
      background: transparent !important;
      color: #111827 !important;
      box-shadow: none !important;
      outline: none !important;
      cursor: pointer !important;
      text-align: left !important;
      font-family: Roboto, Arial, sans-serif !important;
      font-size: 14px !important;
      font-weight: 500 !important;
      line-height: 1.2 !important;
      -webkit-tap-highlight-color: transparent !important;
    }

    .post-show-shell .ps-mobile-accessibility-item:hover,
    .post-show-shell .ps-mobile-accessibility-item:focus-visible,
    .post-show-shell .ps-mobile-accessibility-item:active {
      background: rgba(37, 99, 235, .10) !important;
      color: #2563eb !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-item-icon {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 34px !important;
      height: 34px !important;
      border-radius: 999px !important;
      background: rgba(15, 23, 42, .06) !important;
      color: currentColor !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-mobile-accessibility-item-icon svg {
      width: 20px !important;
      height: 20px !important;
      display: block !important;
    }
  }

  /* Erişilebilirlik seçenekleri tüm ekranlarda metne uygulansın */
  .post-show-shell.is-mobile-reading-larger .ps-post-body,
  .post-show-shell.is-mobile-reading-larger .ps-post-title {
    font-size: calc(1em + 2px) !important;
    line-height: 1.72 !important;
  }

  .post-show-shell.is-mobile-reading-largest .ps-post-body,
  .post-show-shell.is-mobile-reading-largest .ps-post-title {
    font-size: calc(1em + 4px) !important;
    line-height: 1.78 !important;
  }

  .post-show-shell.is-mobile-reading-small .ps-post-body,
  .post-show-shell.is-mobile-reading-small .ps-post-title {
    font-size: calc(1em - 1px) !important;
    line-height: 1.6 !important;
  }

  .post-show-shell.is-mobile-color-sepia .ps-post-card,
  .post-show-shell.is-mobile-color-sepia .ps-comments-section {
    background: #fff8ea !important;
    color: #2f2418 !important;
  }

  .post-show-shell.is-mobile-color-sepia .ps-post-title,
  .post-show-shell.is-mobile-color-sepia .ps-post-body,
  .post-show-shell.is-mobile-color-sepia .ps-post-body * {
    color: #2f2418 !important;
  }

  .post-show-shell.is-mobile-color-contrast .ps-post-card,
  .post-show-shell.is-mobile-color-contrast .ps-comments-section {
    background: #0f172a !important;
    color: #f8fafc !important;
  }

  .post-show-shell.is-mobile-color-contrast .ps-post-title,
  .post-show-shell.is-mobile-color-contrast .ps-post-body,
  .post-show-shell.is-mobile-color-contrast .ps-post-body * {
    color: #f8fafc !important;
  }
</style>
@endpush

@push('head')
<style>
  /* FINAL FIX: erişilebilirlik menüsü sayfanın tamamını blur yapmasın + ikon rengi diğer ikonlarla aynı olsun */
  .post-show-shell .ps-mobile-accessibility-backdrop,
  html.dark .post-show-shell .ps-mobile-accessibility-backdrop,
  body.dark .post-show-shell .ps-mobile-accessibility-backdrop,
  .dark .post-show-shell .ps-mobile-accessibility-backdrop,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-backdrop {
    background: transparent !important;
    -webkit-backdrop-filter: none !important;
    backdrop-filter: none !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility,
  .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"] {
    color: #111827 !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  .post-show-shell .ps-mobile-long-action--accessibility:hover,
  .post-show-shell .ps-mobile-long-action--accessibility:focus-visible,
  .post-show-shell .ps-mobile-long-action--accessibility:active {
    background: rgba(37, 99, 235, .10) !important;
    color: #2563eb !important;
  }

  html.dark .post-show-shell .ps-mobile-long-action--accessibility,
  body.dark .post-show-shell .ps-mobile-long-action--accessibility,
  .dark .post-show-shell .ps-mobile-long-action--accessibility,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action--accessibility,
  html.dark .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  body.dark .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  .dark .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"] {
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  body.dark .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  .dark .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  html.dark .post-show-shell .ps-mobile-long-action--accessibility:hover,
  body.dark .post-show-shell .ps-mobile-long-action--accessibility:hover,
  .dark .post-show-shell .ps-mobile-long-action--accessibility:hover,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action--accessibility:hover,
  html.dark .post-show-shell .ps-mobile-long-action--accessibility:focus-visible,
  body.dark .post-show-shell .ps-mobile-long-action--accessibility:focus-visible,
  .dark .post-show-shell .ps-mobile-long-action--accessibility:focus-visible,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action--accessibility:focus-visible {
    background: rgba(96, 165, 250, .14) !important;
    color: #93c5fd !important;
  }

  .post-show-shell .ps-mobile-accessibility-menu {
    background: rgba(255, 255, 255, .80) !important;
    -webkit-backdrop-filter: blur(18px) saturate(1.35) !important;
    backdrop-filter: blur(18px) saturate(1.35) !important;
  }

  html.dark .post-show-shell .ps-mobile-accessibility-menu,
  body.dark .post-show-shell .ps-mobile-accessibility-menu,
  .dark .post-show-shell .ps-mobile-accessibility-menu,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-menu {
    background: rgba(15, 23, 42, .80) !important;
  }
</style>
@endpush


@push('head')
<style>
  /* FINAL FIX: erişilebilirlik ikon, tüm metin boyutu ve blur sesli okuma oynatıcısı */
  .post-show-shell .ps-mobile-long-action--accessibility svg {
    width: 19px !important;
    height: 19px !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility,
  .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"] {
    color: #111827 !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility:hover,
  .post-show-shell .ps-mobile-long-action--accessibility:focus-visible,
  .post-show-shell .ps-mobile-long-action--accessibility:active,
  .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"] {
    background: rgba(37, 99, 235, .10) !important;
    color: #2563eb !important;
  }

  .post-show-shell.is-mobile-reading-player-open .ps-mobile-long-actions {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
    transform: translate3d(-50%, 12px, 0) scale(.98) !important;
  }

  .post-show-shell .ps-reading-player-wrap {
    position: fixed !important;
    left: 50% !important;
    bottom: calc(22px + env(safe-area-inset-bottom, 0px)) !important;
    z-index: 1260 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    width: auto !important;
    max-width: calc(100vw - 24px) !important;
    transform: translateX(-50%) !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
  }

  .post-show-shell .ps-reading-player-wrap[hidden] {
    display: none !important;
  }

  .post-show-shell .ps-reading-player {
    width: min(360px, calc(100vw - 88px)) !important;
    min-height: 74px !important;
    padding: 12px 18px 11px !important;
    border: 1px solid rgba(15, 23, 42, .12) !important;
    border-radius: 999px !important;
    background: rgba(255, 255, 255, .72) !important;
    -webkit-backdrop-filter: blur(18px) saturate(1.35) !important;
    backdrop-filter: blur(18px) saturate(1.35) !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-reading-progress-track {
    position: relative !important;
    width: calc(100% - 46px) !important;
    height: 4px !important;
    margin: 0 auto 13px !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    background: rgba(15, 23, 42, .14) !important;
  }

  .post-show-shell .ps-reading-progress-fill {
    position: absolute !important;
    inset: 0 auto 0 0 !important;
    width: 0% !important;
    border-radius: inherit !important;
    background: rgba(37, 99, 235, .82) !important;
    transition: width .2s ease !important;
  }

  .post-show-shell .ps-reading-controls {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: clamp(12px, 4vw, 22px) !important;
  }

  .post-show-shell .ps-reading-control,
  .post-show-shell .ps-reading-more-close {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 0 !important;
    background: transparent !important;
    color: #111827 !important;
    box-shadow: none !important;
    outline: none !important;
    cursor: pointer !important;
    -webkit-tap-highlight-color: transparent !important;
  }

  .post-show-shell .ps-reading-control {
    width: 25px !important;
    height: 25px !important;
    padding: 0 !important;
    border-radius: 999px !important;
  }

  .post-show-shell .ps-reading-control svg {
    width: 24px !important;
    height: 24px !important;
    display: block !important;
  }

  .post-show-shell .ps-reading-control--play {
    width: 34px !important;
    height: 34px !important;
    color: #2563eb !important;
  }

  .post-show-shell .ps-reading-control--play svg {
    width: 34px !important;
    height: 34px !important;
  }

  .post-show-shell .ps-reading-control:hover,
  .post-show-shell .ps-reading-control:focus-visible,
  .post-show-shell .ps-reading-more-close:hover,
  .post-show-shell .ps-reading-more-close:focus-visible {
    background: rgba(37, 99, 235, .10) !important;
    color: #2563eb !important;
  }

  .post-show-shell .ps-reading-more-close {
    width: 44px !important;
    height: 44px !important;
    padding: 0 !important;
    border-radius: 999px !important;
    border: 1px solid rgba(15, 23, 42, .12) !important;
    background: rgba(255, 255, 255, .72) !important;
    -webkit-backdrop-filter: blur(16px) saturate(1.25) !important;
    backdrop-filter: blur(16px) saturate(1.25) !important;
  }

  .post-show-shell .ps-reading-more-close svg {
    width: 22px !important;
    height: 22px !important;
  }

  .post-show-shell .ps-reading-icon-play {
    display: none !important;
  }

  .post-show-shell.is-reading-paused .ps-reading-icon-pause {
    display: none !important;
  }

  .post-show-shell.is-reading-paused .ps-reading-icon-play {
    display: block !important;
  }

  @media (max-width: 520px) {
    .post-show-shell .ps-reading-player {
      width: min(310px, calc(100vw - 82px)) !important;
      min-height: 68px !important;
      padding: 11px 15px 10px !important;
    }

    .post-show-shell .ps-reading-controls {
      gap: 14px !important;
    }

    .post-show-shell .ps-reading-control svg {
      width: 22px !important;
      height: 22px !important;
    }

    .post-show-shell .ps-reading-control--play,
    .post-show-shell .ps-reading-control--play svg {
      width: 31px !important;
      height: 31px !important;
    }

    .post-show-shell .ps-reading-more-close {
      width: 40px !important;
      height: 40px !important;
    }
  }

  html.dark .post-show-shell .ps-reading-player,
  body.dark .post-show-shell .ps-reading-player,
  .dark .post-show-shell .ps-reading-player,
  [data-theme="dark"] .post-show-shell .ps-reading-player,
  html.dark .post-show-shell .ps-reading-more-close,
  body.dark .post-show-shell .ps-reading-more-close,
  .dark .post-show-shell .ps-reading-more-close,
  [data-theme="dark"] .post-show-shell .ps-reading-more-close {
    background: rgba(15, 23, 42, .72) !important;
    border-color: rgba(148, 163, 184, .20) !important;
    color: #e5e7eb !important;
  }

  html.dark .post-show-shell .ps-reading-control,
  body.dark .post-show-shell .ps-reading-control,
  .dark .post-show-shell .ps-reading-control,
  [data-theme="dark"] .post-show-shell .ps-reading-control,
  html.dark .post-show-shell .ps-mobile-long-action--accessibility,
  body.dark .post-show-shell .ps-mobile-long-action--accessibility,
  .dark .post-show-shell .ps-mobile-long-action--accessibility,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-action--accessibility {
    color: #e5e7eb !important;
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  var shell = document.querySelector('.post-show-shell');
  if (!shell) return;

  var oldRead = document.querySelector('[data-mobile-a11y-read]');
  var oldPlus = document.querySelector('[data-mobile-a11y-font-plus]');
  var oldMinus = document.querySelector('[data-mobile-a11y-font-minus]');
  var oldColor = document.querySelector('[data-mobile-a11y-color]');

  function cloneWithoutListeners(node) {
    if (!node || !node.parentNode) return node;
    var clone = node.cloneNode(true);
    node.parentNode.replaceChild(clone, node);
    return clone;
  }

  var readButton = cloneWithoutListeners(oldRead);
  var plusButton = cloneWithoutListeners(oldPlus);
  var minusButton = cloneWithoutListeners(oldMinus);
  var colorButton = cloneWithoutListeners(oldColor);
  var menu = document.querySelector('[data-mobile-accessibility-menu]');
  var backdrop = document.querySelector('[data-mobile-accessibility-backdrop]');
  var trigger = document.querySelector('[data-mobile-accessibility-trigger]');
  var player = document.querySelector('[data-reading-player]');
  var progress = document.querySelector('[data-reading-progress]');
  var closePlayer = document.querySelector('[data-reading-player-close]');
  var togglePlayer = document.querySelector('[data-reading-toggle]');
  var restartPlayer = document.querySelector('[data-reading-restart]');
  var skipPlayer = document.querySelector('[data-reading-skip]');
  var collapsePlayer = document.querySelector('[data-reading-collapse]');

  var fontLevel = 0;
  var colorLevel = 0;
  var utterance = null;
  var fullText = '';
  var isReading = false;
  var isPaused = false;

  function closeAccessMenu() {
    if (menu) {
      menu.hidden = true;
      menu.setAttribute('aria-hidden', 'true');
    }
    if (backdrop) backdrop.hidden = true;
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
  }

  function setPlayerVisible(visible) {
    if (!player) return;
    player.hidden = !visible;
    player.setAttribute('aria-hidden', visible ? 'false' : 'true');
    shell.classList.toggle('is-mobile-reading-player-open', visible);
    if (!visible) shell.classList.remove('is-reading-paused');
  }

  function setProgress(percent) {
    if (!progress) return;
    progress.style.width = Math.max(0, Math.min(100, percent || 0)) + '%';
  }

  function getReadableText() {
    var parts = [];
    var title = shell.querySelector('.ps-post-title');
    var body = shell.querySelector('.ps-post-body');
    if (title) parts.push((title.innerText || title.textContent || '').trim());
    if (body) parts.push((body.innerText || body.textContent || '').trim());
    return parts.join('. ').replace(/\s+/g, ' ').trim();
  }

  function startReading() {
    if (!('speechSynthesis' in window)) return;
    fullText = getReadableText();
    if (!fullText) return;

    window.speechSynthesis.cancel();
    setProgress(0);
    closeAccessMenu();
    setPlayerVisible(true);

    utterance = new SpeechSynthesisUtterance(fullText.slice(0, 8000));
    utterance.lang = document.documentElement.lang || 'tr-TR';
    utterance.rate = 0.95;
    utterance.onboundary = function (event) {
      if (!fullText) return;
      var index = typeof event.charIndex === 'number' ? event.charIndex : 0;
      setProgress((index / Math.max(fullText.length, 1)) * 100);
    };
    utterance.onend = function () {
      isReading = false;
      isPaused = false;
      shell.classList.remove('is-reading-paused');
      setProgress(100);
    };
    utterance.onerror = utterance.onend;

    isReading = true;
    isPaused = false;
    shell.classList.remove('is-reading-paused');
    window.speechSynthesis.speak(utterance);
  }

  function stopReading() {
    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    isReading = false;
    isPaused = false;
    utterance = null;
    setProgress(0);
    setPlayerVisible(false);
  }

  function toggleReadingPause() {
    if (!('speechSynthesis' in window)) return;
    if (!isReading) {
      startReading();
      return;
    }
    if (isPaused) {
      window.speechSynthesis.resume();
      isPaused = false;
      shell.classList.remove('is-reading-paused');
    } else {
      window.speechSynthesis.pause();
      isPaused = true;
      shell.classList.add('is-reading-paused');
    }
  }

  function restartReading() {
    startReading();
  }

  function skipReading() {
    setProgress(100);
    stopReading();
  }

  function getTextTargets() {
    var scope = shell.querySelectorAll('.ps-main, .ps-sidebar-right, .ps-mobile-accessibility-menu');
    var targets = [];
    var seen = new Set();
    var selector = 'h1,h2,h3,h4,h5,h6,p,li,a,span,small,strong,em,blockquote,figcaption,td,th,label,button,textarea,input,.ps-post-title,.ps-post-body,.ps-comment-text,.ps-comment-author,.ps-comment-time,.ps-comments-title,.ps-source-domain,.ps-source-label,.ps-tag,.ps-post-author-name,.ps-post-subline,.ps-action-btn,.ps-view-count,.ps-sidebar-card';

    function hasDirectText(el) {
      if (!el || el.closest('.ps-reading-player-wrap')) return false;
      if (/^(svg|path|img|video|iframe)$/i.test(el.tagName)) return false;
      if (/^(INPUT|TEXTAREA|BUTTON)$/i.test(el.tagName)) return true;
      for (var i = 0; i < el.childNodes.length; i++) {
        var node = el.childNodes[i];
        if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '') return true;
      }
      return el.matches('.ps-post-title,.ps-post-body,.ps-comment-text,.ps-sidebar-card,.ps-comments-title,.ps-post-subline,.ps-source-domain,.ps-source-label,.ps-tag,.ps-post-author-name,.ps-action-btn,.ps-view-count');
    }

    scope.forEach(function (root) {
      root.querySelectorAll(selector).forEach(function (el) {
        if (!hasDirectText(el) || seen.has(el)) return;
        seen.add(el);
        targets.push(el);
      });
    });
    return targets;
  }

  function applyFontLevel() {
    var delta = fontLevel * 2;
    getTextTargets().forEach(function (el) {
      if (!el.dataset.psBaseFontSize) {
        el.dataset.psBaseFontSize = String(parseFloat(window.getComputedStyle(el).fontSize) || 14);
      }
      var base = parseFloat(el.dataset.psBaseFontSize) || 14;
      el.style.setProperty('font-size', Math.max(10, base + delta) + 'px', 'important');
      if (fontLevel !== 0) el.style.setProperty('line-height', '1.68', 'important');
      else el.style.removeProperty('line-height');
    });
  }

  function clearColorClasses() {
    shell.classList.remove('is-mobile-color-sepia', 'is-mobile-color-contrast');
  }

  function applyColorLevel() {
    clearColorClasses();
    if (colorLevel === 1) shell.classList.add('is-mobile-color-sepia');
    if (colorLevel === 2) shell.classList.add('is-mobile-color-contrast');
  }

  if (readButton) readButton.addEventListener('click', startReading);
  if (togglePlayer) togglePlayer.addEventListener('click', toggleReadingPause);
  if (restartPlayer) restartPlayer.addEventListener('click', restartReading);
  if (skipPlayer) skipPlayer.addEventListener('click', skipReading);
  if (collapsePlayer) collapsePlayer.addEventListener('click', function () { setPlayerVisible(false); });
  if (closePlayer) closePlayer.addEventListener('click', stopReading);

  if (plusButton) {
    plusButton.addEventListener('click', function () {
      fontLevel = Math.min(3, fontLevel + 1);
      applyFontLevel();
    });
  }

  if (minusButton) {
    minusButton.addEventListener('click', function () {
      fontLevel = Math.max(-3, fontLevel - 1);
      applyFontLevel();
    });
  }

  if (colorButton) {
    colorButton.addEventListener('click', function () {
      colorLevel = (colorLevel + 1) % 3;
      applyColorLevel();
    });
  }

  window.addEventListener('beforeunload', function () {
    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
  });
})();
</script>
@endpush


@push('head')
<style>
  /* FINAL FIX: sesli oku aynı alt barda kalsın; menü kapanınca erişilebilir ikon pause olsun */
  .post-show-shell .ps-reading-player-wrap,
  .post-show-shell.is-mobile-reading-player-open .ps-reading-player-wrap {
    display: none !important;
    visibility: hidden !important;
    pointer-events: none !important;
  }

  .post-show-shell.is-mobile-reading-player-open .ps-mobile-long-actions {
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    transform: translate3d(-50%, 0, 0) scale(1) !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility .ps-accessibility-trigger-icon {
    width: 18px !important;
    height: 18px !important;
    display: block !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility .ps-accessibility-trigger-icon--pause {
    display: none !important;
  }

  .post-show-shell.is-inline-reading .ps-mobile-long-action--accessibility .ps-accessibility-trigger-icon--person {
    display: none !important;
  }

  .post-show-shell.is-inline-reading .ps-mobile-long-action--accessibility .ps-accessibility-trigger-icon--pause {
    display: block !important;
  }

  .post-show-shell.is-inline-reading .ps-mobile-long-action--accessibility {
    background: rgba(37, 99, 235, .10) !important;
    color: #2563eb !important;
  }

  html.dark .post-show-shell.is-inline-reading .ps-mobile-long-action--accessibility,
  body.dark .post-show-shell.is-inline-reading .ps-mobile-long-action--accessibility,
  .dark .post-show-shell.is-inline-reading .ps-mobile-long-action--accessibility,
  [data-theme="dark"] .post-show-shell.is-inline-reading .ps-mobile-long-action--accessibility {
    background: rgba(96, 165, 250, .14) !important;
    color: #93c5fd !important;
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  var shell = document.querySelector('.post-show-shell');
  if (!shell) return;

  function cleanClone(selector) {
    var node = document.querySelector(selector);
    if (!node || !node.parentNode) return node;
    var clone = node.cloneNode(true);
    node.parentNode.replaceChild(clone, node);
    return clone;
  }

  var trigger = cleanClone('[data-mobile-accessibility-trigger]');
  var readButton = cleanClone('[data-mobile-a11y-read]');
  var plusButton = cleanClone('[data-mobile-a11y-font-plus]');
  var minusButton = cleanClone('[data-mobile-a11y-font-minus]');
  var colorButton = cleanClone('[data-mobile-a11y-color]');
  var backdrop = cleanClone('[data-mobile-accessibility-backdrop]');
  var menu = document.querySelector('[data-mobile-accessibility-menu]');
  var player = document.querySelector('[data-reading-player]');

  var fontLevel = 0;
  var colorLevel = 0;
  var isReading = false;
  var utterance = null;

  function hideOldPlayer() {
    shell.classList.remove('is-mobile-reading-player-open', 'is-reading-paused');
    if (player) {
      player.hidden = true;
      player.setAttribute('aria-hidden', 'true');
    }
  }

  function openMenu() {
    if (!menu || !trigger) return;
    hideOldPlayer();
    menu.hidden = false;
    menu.setAttribute('aria-hidden', 'false');
    if (backdrop) backdrop.hidden = false;
    trigger.setAttribute('aria-expanded', 'true');
  }

  function closeMenu() {
    if (menu) {
      menu.hidden = true;
      menu.setAttribute('aria-hidden', 'true');
    }
    if (backdrop) backdrop.hidden = true;
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
  }

  function getReadableText() {
    var parts = [];
    var selectors = ['.ps-post-title', '.ps-post-body', '.ps-source-link', '.ps-tags-row'];
    selectors.forEach(function (selector) {
      shell.querySelectorAll(selector).forEach(function (el) {
        var text = (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim();
        if (text) parts.push(text);
      });
    });
    return parts.join('. ').replace(/\s+/g, ' ').trim();
  }

  function setReadingState(active) {
    isReading = !!active;
    shell.classList.toggle('is-inline-reading', isReading);
    hideOldPlayer();
    if (trigger) {
      trigger.setAttribute('aria-label', isReading ? 'Sesli okumayı durdur' : 'Erişilebilirlik menüsünü aç');
      trigger.setAttribute('title', isReading ? 'Sesli okumayı durdur' : 'Erişilebilirlik');
    }
  }

  function stopReading() {
    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    utterance = null;
    setReadingState(false);
  }

  function startReading() {
    if (!('speechSynthesis' in window)) return;
    var text = getReadableText();
    if (!text) return;

    window.speechSynthesis.cancel();
    closeMenu();
    hideOldPlayer();

    utterance = new SpeechSynthesisUtterance(text.slice(0, 8000));
    utterance.lang = document.documentElement.lang || 'tr-TR';
    utterance.rate = 0.95;
    utterance.onend = function () {
      setReadingState(false);
      utterance = null;
    };
    utterance.onerror = utterance.onend;

    setReadingState(true);
    window.speechSynthesis.speak(utterance);
  }

  function getTextTargets() {
    var targets = [];
    var seen = new Set();
    var roots = shell.querySelectorAll('.ps-main, .ps-sidebar-left, .ps-sidebar-right, .ps-comments-section, .ps-mobile-accessibility-menu');
    var selector = 'h1,h2,h3,h4,h5,h6,p,li,a,span,small,strong,em,blockquote,figcaption,td,th,label,button,textarea,input,div';

    function hasText(el) {
      if (!el || el.closest('svg, .ps-reading-player-wrap')) return false;
      if (/^(svg|path|img|video|iframe|form)$/i.test(el.tagName)) return false;
      var cls = el.className || '';
      if (typeof cls === 'string' && /icon|avatar|badge|media|image|progress|track|fill/i.test(cls)) return false;
      for (var i = 0; i < el.childNodes.length; i++) {
        var node = el.childNodes[i];
        if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '') return true;
      }
      return false;
    }

    roots.forEach(function (root) {
      root.querySelectorAll(selector).forEach(function (el) {
        if (!hasText(el) || seen.has(el)) return;
        seen.add(el);
        targets.push(el);
      });
    });
    return targets;
  }

  function applyFontLevel() {
    var delta = fontLevel * 2;
    getTextTargets().forEach(function (el) {
      if (!el.dataset.psBaseFontSizeFinal) {
        el.dataset.psBaseFontSizeFinal = String(parseFloat(window.getComputedStyle(el).fontSize) || 14);
      }
      var base = parseFloat(el.dataset.psBaseFontSizeFinal) || 14;
      var next = Math.max(10, Math.min(32, base + delta));
      el.style.setProperty('font-size', next + 'px', 'important');
      el.style.setProperty('line-height', fontLevel === 0 ? '' : '1.68', 'important');
      if (fontLevel === 0) el.style.removeProperty('line-height');
    });
  }

  function clearColorClasses() {
    shell.classList.remove('is-mobile-color-sepia', 'is-mobile-color-contrast');
  }

  function applyColorLevel() {
    clearColorClasses();
    if (colorLevel === 1) shell.classList.add('is-mobile-color-sepia');
    if (colorLevel === 2) shell.classList.add('is-mobile-color-contrast');
  }

  if (trigger) {
    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      if (isReading) {
        stopReading();
        closeMenu();
        return;
      }
      if (menu && menu.hidden) openMenu(); else closeMenu();
    });
  }

  if (readButton) {
    readButton.addEventListener('click', function (event) {
      event.preventDefault();
      startReading();
    });
  }

  if (backdrop) backdrop.addEventListener('click', closeMenu);

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeMenu();
  });

  if (plusButton) {
    plusButton.addEventListener('click', function () {
      fontLevel = Math.min(4, fontLevel + 1);
      applyFontLevel();
    });
  }

  if (minusButton) {
    minusButton.addEventListener('click', function () {
      fontLevel = Math.max(-4, fontLevel - 1);
      applyFontLevel();
    });
  }

  if (colorButton) {
    colorButton.addEventListener('click', function () {
      colorLevel = (colorLevel + 1) % 3;
      applyColorLevel();
    });
  }

  window.addEventListener('beforeunload', stopReading);
  hideOldPlayer();
})();
</script>
@endpush

@push('head')
<style>
  /* FINAL FIX 2: like rengi nötr, blur azaltıldı, font erişilebilirliği tüm metinlere uygulanacak */
  .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  .post-show-shell .ps-mobile-long-actions {
    background: rgba(255, 255, 255, .94) !important;
    -webkit-backdrop-filter: blur(6px) saturate(1.05) !important;
    backdrop-filter: blur(6px) saturate(1.05) !important;
    border-color: rgba(15, 23, 42, .12) !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-mobile-accessibility-menu {
    background: rgba(255, 255, 255, .92) !important;
    -webkit-backdrop-filter: blur(8px) saturate(1.08) !important;
    backdrop-filter: blur(8px) saturate(1.08) !important;
    border-color: rgba(15, 23, 42, .12) !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like svg,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like span {
    color: #111827 !important;
    stroke: currentColor !important;
  }

  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like:hover,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like:focus-visible,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like:active {
    color: #2563eb !important;
    background: rgba(37, 99, 235, .10) !important;
  }

  html.dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  body.dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  .dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  html.dark .post-show-shell .ps-mobile-long-actions,
  body.dark .post-show-shell .ps-mobile-long-actions,
  .dark .post-show-shell .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions {
    background: rgba(15, 23, 42, .92) !important;
    -webkit-backdrop-filter: blur(6px) saturate(1.05) !important;
    backdrop-filter: blur(6px) saturate(1.05) !important;
    border-color: rgba(148, 163, 184, .18) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-accessibility-menu,
  body.dark .post-show-shell .ps-mobile-accessibility-menu,
  .dark .post-show-shell .ps-mobile-accessibility-menu,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-menu {
    background: rgba(15, 23, 42, .92) !important;
    -webkit-backdrop-filter: blur(8px) saturate(1.08) !important;
    backdrop-filter: blur(8px) saturate(1.08) !important;
    border-color: rgba(148, 163, 184, .18) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like svg,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like svg,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like svg,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like svg,
  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like span,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like span,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like span,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like span {
    color: #e5e7eb !important;
    stroke: currentColor !important;
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  var shell = document.querySelector('.post-show-shell');
  if (!shell) return;

  var fontLevel = 0;
  var excludedSelector = '.ps-mobile-long-actions, .ps-mobile-accessibility-menu, .ps-reading-player-wrap, svg, path, img, video, iframe, canvas, form';
  var textSelector = [
    '.ps-main h1', '.ps-main h2', '.ps-main h3', '.ps-main h4', '.ps-main h5', '.ps-main h6',
    '.ps-main p', '.ps-main li', '.ps-main a', '.ps-main span', '.ps-main small', '.ps-main strong', '.ps-main em',
    '.ps-main blockquote', '.ps-main figcaption', '.ps-main td', '.ps-main th', '.ps-main label',
    '.ps-comments-section h1', '.ps-comments-section h2', '.ps-comments-section h3', '.ps-comments-section p',
    '.ps-comments-section li', '.ps-comments-section a', '.ps-comments-section span', '.ps-comments-section small',
    '.ps-comments-section strong', '.ps-comments-section em', '.ps-comments-section button',
    '.ps-sidebar-left a', '.ps-sidebar-left span', '.ps-sidebar-left div',
    '.ps-sidebar-right a', '.ps-sidebar-right span', '.ps-sidebar-right div', '.ps-sidebar-right p',
    '.ps-post-title', '.ps-post-body', '.ps-post-body *', '.ps-source-link *', '.ps-tags-row *',
    '.ps-comment-text', '.ps-comment-text *', '.ps-comment-author', '.ps-comment-time', '.ps-comments-title',
    '.ps-sidebar-card *'
  ].join(',');

  function hasReadableText(el) {
    if (!el || el.closest(excludedSelector)) return false;
    if (/^(SVG|PATH|IMG|VIDEO|IFRAME|CANVAS|FORM|STYLE|SCRIPT)$/i.test(el.tagName)) return false;
    var text = '';
    for (var i = 0; i < el.childNodes.length; i++) {
      if (el.childNodes[i].nodeType === Node.TEXT_NODE) {
        text += el.childNodes[i].textContent || '';
      }
    }
    return text.replace(/\s+/g, '').length > 0;
  }

  function collectTextTargets() {
    var targets = [];
    var seen = new Set();
    shell.querySelectorAll(textSelector).forEach(function (el) {
      if (!hasReadableText(el) || seen.has(el)) return;
      seen.add(el);
      targets.push(el);
    });
    return targets;
  }

  function rememberBase(el) {
    if (!el.dataset.psA11yBaseFontSize) {
      el.dataset.psA11yBaseFontSize = String(parseFloat(window.getComputedStyle(el).fontSize) || 14);
    }
    if (!el.dataset.psA11yBaseLineHeight) {
      el.dataset.psA11yBaseLineHeight = window.getComputedStyle(el).lineHeight || '';
    }
  }

  function applyFontLevel() {
    var delta = fontLevel * 2;
    collectTextTargets().forEach(function (el) {
      rememberBase(el);
      var base = parseFloat(el.dataset.psA11yBaseFontSize) || 14;
      var next = Math.max(10, Math.min(34, base + delta));

      if (fontLevel === 0) {
        el.style.removeProperty('font-size');
        el.style.removeProperty('line-height');
        return;
      }

      el.style.setProperty('font-size', next + 'px', 'important');
      el.style.setProperty('line-height', '1.68', 'important');
    });
  }

  document.addEventListener('click', function (event) {
    var plus = event.target.closest('[data-mobile-a11y-font-plus]');
    var minus = event.target.closest('[data-mobile-a11y-font-minus]');

    if (!plus && !minus) return;

    event.preventDefault();
    event.stopPropagation();
    if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();

    if (plus) fontLevel = Math.min(5, fontLevel + 1);
    if (minus) fontLevel = Math.max(-5, fontLevel - 1);
    applyFontLevel();
  }, true);
})();
</script>
@endpush

@push('head')
<style>
  /* FINAL FIX: alt aksiyon barı etiket alanına gelince kapanır */
  .post-show-shell.is-post-tags-reached .ps-mobile-long-actions,
  .post-show-shell.is-post-tags-reached.is-mobile-long-actions-visible .ps-mobile-long-actions {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
    transform: translate3d(-50%, 14px, 0) scale(.98) !important;
  }

  .post-show-shell.is-post-tags-reached .ps-mobile-accessibility-menu,
  .post-show-shell.is-post-tags-reached .ps-mobile-accessibility-backdrop {
    display: none !important;
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  var shell = document.querySelector('.post-show-shell');
  var bar = document.querySelector('[data-mobile-long-actions]');

  if (!shell || !bar) return;

  function getTagsMarker() {
    return shell.querySelector('.ps-post-card .ps-tags-row')
      || shell.querySelector('.ps-tags-row')
      || shell.querySelector('.ps-post-card .ps-actions-bar');
  }

  function closeA11yMenu() {
    var trigger = document.querySelector('[data-mobile-accessibility-trigger]');
    var menu = document.querySelector('[data-mobile-accessibility-menu]');
    var backdrop = document.querySelector('[data-mobile-accessibility-backdrop]');

    if (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
    }

    if (menu) {
      menu.hidden = true;
      menu.setAttribute('aria-hidden', 'true');
    }

    if (backdrop) {
      backdrop.hidden = true;
    }
  }

  function isTagsReached() {
    var marker = getTagsMarker();
    if (!marker) return false;

    var rect = marker.getBoundingClientRect();
    var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;

    /* Bar alt tarafta olduğu için etiketler bara yaklaşmadan gizlenir. */
    var stopLine = Math.max(220, viewportHeight - 118);

    return rect.top <= stopLine;
  }

  function applyTagsLimit() {
    var reached = isTagsReached();
    var current = shell.classList.contains('is-post-tags-reached');

    if (reached !== current) {
      shell.classList.toggle('is-post-tags-reached', reached);
    }

    if (reached) {
      bar.setAttribute('aria-hidden', 'true');
      closeA11yMenu();
    } else if (shell.classList.contains('has-mobile-long-actions')) {
      bar.setAttribute('aria-hidden', 'false');
    }
  }

  var ticking = false;
  function requestApply() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(function () {
      ticking = false;
      applyTagsLimit();
    });
  }

  window.addEventListener('scroll', requestApply, { passive: true });
  window.addEventListener('resize', requestApply, { passive: true });
  window.addEventListener('orientationchange', requestApply, { passive: true });
  window.addEventListener('load', requestApply);

  if (window.ResizeObserver) {
    var observer = new ResizeObserver(requestApply);
    observer.observe(shell);
    var marker = getTagsMarker();
    if (marker) observer.observe(marker);
  }

  if (window.MutationObserver) {
    var mutationObserver = new MutationObserver(requestApply);
    mutationObserver.observe(shell, { attributes: true, attributeFilter: ['class'] });
  }

  setTimeout(requestApply, 100);
  setTimeout(requestApply, 500);
  setTimeout(requestApply, 1200);
})();
</script>
@endpush

@push('head')
<style>
  /* FINAL FIX: alt bar blur geri geldi ama yoğun değil, şeffaf cam görünümü */
  .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  .post-show-shell .ps-mobile-long-actions {
    background: rgba(255, 255, 255, 0.62) !important;
    -webkit-backdrop-filter: blur(10px) saturate(1.08) !important;
    backdrop-filter: blur(10px) saturate(1.08) !important;
    border: 1px solid rgba(15, 23, 42, 0.10) !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-mobile-long-actions::before,
  .post-show-shell .ps-mobile-long-actions::after {
    box-shadow: none !important;
  }

  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action {
    background: rgba(255, 255, 255, 0.28) !important;
    -webkit-backdrop-filter: blur(4px) saturate(1.04) !important;
    backdrop-filter: blur(4px) saturate(1.04) !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:hover,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:focus-visible,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:active,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action[aria-expanded="true"] {
    background: rgba(37, 99, 235, 0.10) !important;
    color: #2563eb !important;
  }

  .post-show-shell .ps-mobile-accessibility-menu {
    background: rgba(255, 255, 255, 0.68) !important;
    -webkit-backdrop-filter: blur(10px) saturate(1.08) !important;
    backdrop-filter: blur(10px) saturate(1.08) !important;
    border: 1px solid rgba(15, 23, 42, 0.10) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  body.dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  .dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  html.dark .post-show-shell .ps-mobile-long-actions,
  body.dark .post-show-shell .ps-mobile-long-actions,
  .dark .post-show-shell .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions {
    background: rgba(17, 24, 39, 0.48) !important;
    -webkit-backdrop-filter: blur(10px) saturate(1.08) !important;
    backdrop-filter: blur(10px) saturate(1.08) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action {
    background: rgba(255, 255, 255, 0.08) !important;
    -webkit-backdrop-filter: blur(4px) saturate(1.04) !important;
    backdrop-filter: blur(4px) saturate(1.04) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-accessibility-menu,
  body.dark .post-show-shell .ps-mobile-accessibility-menu,
  .dark .post-show-shell .ps-mobile-accessibility-menu,
  [data-theme="dark"] .post-show-shell .ps-mobile-accessibility-menu {
    background: rgba(17, 24, 39, 0.58) !important;
    -webkit-backdrop-filter: blur(10px) saturate(1.08) !important;
    backdrop-filter: blur(10px) saturate(1.08) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    box-shadow: none !important;
  }
</style>
@endpush


@push('head')
<style>
  /* FINAL FIX: alt aksiyon barında blur komple bara uygulanır; ikonların kendi blur kutuları yok */
  .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  .post-show-shell .ps-mobile-long-actions {
    background: rgba(255, 255, 255, 0.54) !important;
    -webkit-backdrop-filter: blur(5px) saturate(1.04) !important;
    backdrop-filter: blur(5px) saturate(1.04) !important;
    border: 1px solid rgba(15, 23, 42, 0.10) !important;
    box-shadow: none !important;
    transition:
      background .16s ease,
      backdrop-filter .16s ease,
      -webkit-backdrop-filter .16s ease,
      border-color .16s ease,
      opacity .18s ease,
      transform .18s ease,
      visibility .18s ease !important;
  }

  .post-show-shell .ps-mobile-long-actions:hover,
  .post-show-shell .ps-mobile-long-actions:focus-within,
  .post-show-shell .ps-mobile-long-actions.is-strong-blur,
  .post-show-shell .ps-mobile-long-actions:has(.ps-mobile-long-action:active),
  .post-show-shell .ps-mobile-long-actions:has(.ps-mobile-long-action[aria-expanded="true"]),
  .post-show-shell.is-inline-reading .ps-mobile-long-actions {
    background: rgba(255, 255, 255, 0.78) !important;
    -webkit-backdrop-filter: blur(18px) saturate(1.28) !important;
    backdrop-filter: blur(18px) saturate(1.28) !important;
    border-color: rgba(37, 99, 235, 0.18) !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--accessibility {
    background: transparent !important;
    -webkit-backdrop-filter: none !important;
    backdrop-filter: none !important;
    box-shadow: none !important;
    color: #111827 !important;
  }

  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:hover,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:focus-visible,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:active,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action[aria-expanded="true"] {
    background: rgba(37, 99, 235, 0.10) !important;
    color: #2563eb !important;
    -webkit-backdrop-filter: none !important;
    backdrop-filter: none !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like svg,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like span,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action svg,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action span {
    color: currentColor !important;
    stroke: currentColor !important;
  }

  .post-show-shell .ps-mobile-long-actions::before,
  .post-show-shell .ps-mobile-long-actions::after,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action::before,
  .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action::after {
    display: none !important;
    content: none !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  body.dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  .dark .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  html.dark .post-show-shell .ps-mobile-long-actions,
  body.dark .post-show-shell .ps-mobile-long-actions,
  .dark .post-show-shell .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions {
    background: rgba(17, 24, 39, 0.42) !important;
    -webkit-backdrop-filter: blur(5px) saturate(1.04) !important;
    backdrop-filter: blur(5px) saturate(1.04) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-long-actions:hover,
  body.dark .post-show-shell .ps-mobile-long-actions:hover,
  .dark .post-show-shell .ps-mobile-long-actions:hover,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions:hover,
  html.dark .post-show-shell .ps-mobile-long-actions:focus-within,
  body.dark .post-show-shell .ps-mobile-long-actions:focus-within,
  .dark .post-show-shell .ps-mobile-long-actions:focus-within,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions:focus-within,
  html.dark .post-show-shell .ps-mobile-long-actions.is-strong-blur,
  body.dark .post-show-shell .ps-mobile-long-actions.is-strong-blur,
  .dark .post-show-shell .ps-mobile-long-actions.is-strong-blur,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions.is-strong-blur,
  html.dark .post-show-shell.is-inline-reading .ps-mobile-long-actions,
  body.dark .post-show-shell.is-inline-reading .ps-mobile-long-actions,
  .dark .post-show-shell.is-inline-reading .ps-mobile-long-actions,
  [data-theme="dark"] .post-show-shell.is-inline-reading .ps-mobile-long-actions {
    background: rgba(17, 24, 39, 0.78) !important;
    -webkit-backdrop-filter: blur(18px) saturate(1.28) !important;
    backdrop-filter: blur(18px) saturate(1.28) !important;
    border-color: rgba(96, 165, 250, 0.22) !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action,
  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--like,
  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--accessibility,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--accessibility,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--accessibility,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action--accessibility {
    background: transparent !important;
    -webkit-backdrop-filter: none !important;
    backdrop-filter: none !important;
    color: #e5e7eb !important;
    box-shadow: none !important;
  }

  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:hover,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:hover,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:hover,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:hover,
  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:focus-visible,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:focus-visible,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:focus-visible,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:focus-visible,
  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:active,
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:active,
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:active,
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action:active,
  html.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action[aria-expanded="true"],
  body.dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action[aria-expanded="true"],
  .dark .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action[aria-expanded="true"],
  [data-theme="dark"] .post-show-shell .ps-mobile-long-actions .ps-mobile-long-action[aria-expanded="true"] {
    background: rgba(96, 165, 250, 0.14) !important;
    color: #93c5fd !important;
  }

  .post-show-shell .ps-mobile-accessibility-menu {
    background: rgba(255, 255, 255, 0.70) !important;
    -webkit-backdrop-filter: blur(14px) saturate(1.14) !important;
    backdrop-filter: blur(14px) saturate(1.14) !important;
    box-shadow: none !important;
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  var bar = document.querySelector('[data-mobile-long-actions]');
  if (!bar) return;

  var strongBlurTimer = null;

  function pulseStrongBlur() {
    bar.classList.add('is-strong-blur');
    window.clearTimeout(strongBlurTimer);
    strongBlurTimer = window.setTimeout(function () {
      if (!bar.matches(':hover') && !bar.contains(document.activeElement)) {
        bar.classList.remove('is-strong-blur');
      }
    }, 1400);
  }

  bar.addEventListener('pointerenter', function () {
    bar.classList.add('is-strong-blur');
  });

  bar.addEventListener('pointerleave', function () {
    window.clearTimeout(strongBlurTimer);
    if (!bar.contains(document.activeElement)) {
      bar.classList.remove('is-strong-blur');
    }
  });

  bar.addEventListener('click', pulseStrongBlur, true);
  bar.addEventListener('focusin', pulseStrongBlur, true);
  bar.addEventListener('focusout', function () {
    window.setTimeout(function () {
      if (!bar.matches(':hover') && !bar.contains(document.activeElement)) {
        bar.classList.remove('is-strong-blur');
      }
    }, 80);
  }, true);
})();
</script>
@endpush


@push('head')
<style>
  /* FINAL FIX: erişilebilirlik ikonu eski sade SVG’ye döndü ve mobilde göze batmayacak ölçüye alındı */
  .post-show-shell .ps-mobile-long-action--accessibility .ps-accessibility-trigger-icon--person {
    width: 16px !important;
    height: 16px !important;
    color: inherit !important;
    opacity: .92 !important;
    flex: 0 0 16px !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility .ps-accessibility-trigger-icon--person path {
    fill: currentColor !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility {
    color: inherit !important;
  }

  .post-show-shell .ps-mobile-long-action--accessibility[aria-expanded="true"],
  .post-show-shell .ps-mobile-long-action--accessibility:hover,
  .post-show-shell .ps-mobile-long-action--accessibility:focus-visible,
  .post-show-shell .ps-mobile-long-action--accessibility:active {
    color: #2563eb !important;
  }

  @media (max-width: 640px) {
    .post-show-shell .ps-mobile-long-action--accessibility .ps-accessibility-trigger-icon--person {
      width: 15px !important;
      height: 15px !important;
      flex-basis: 15px !important;
    }
  }
</style>
@endpush

@push('head')
<style>
  /* FINAL FIX: mobilde alt blur bar bütün açılır menülerin arkasında kalsın */
  .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
  .post-show-shell .ps-mobile-long-actions {
    z-index: 720 !important;
  }

  .post-show-shell.is-any-overlay-open .ps-mobile-long-actions {
    z-index: 300 !important;
  }

  .post-show-shell .ps-mobile-accessibility-menu {
    z-index: 12000 !important;
  }

  .post-show-shell .ps-mobile-accessibility-backdrop {
    z-index: 11990 !important;
    pointer-events: none !important;
  }

  .post-show-shell .ps-post-card,
  .post-show-shell .ps-post-card-inner,
  .post-show-shell .post-author-area,
  .post-show-shell .post-author-info,
  .post-show-shell .post-author-meta,
  .post-show-shell .ps-actions-bar,
  .post-show-shell .ps-reaction-row,
  .post-show-shell .ps-reaction-picker,
  .post-show-shell .ps-reaction-more-wrap {
    overflow: visible !important;
  }

  .post-show-shell .ps-hover-zone--author-name,
  .post-show-shell .ps-hover-zone--category-name {
    position: relative !important;
    z-index: 13000 !important;
  }

  .post-show-shell .ps-hover-zone--author-name.is-hover-loading,
  .post-show-shell .ps-hover-zone--author-name.is-hover-ready,
  .post-show-shell .ps-hover-zone--category-name.is-hover-loading,
  .post-show-shell .ps-hover-zone--category-name.is-hover-ready {
    z-index: 2147483200 !important;
  }

  .post-show-shell .ps-hover-zone--author-name > .ps-hover-card,
  .post-show-shell .ps-hover-zone--category-name > .ps-hover-card {
    z-index: 2147483200 !important;
    box-shadow: none !important;
  }

  .post-show-shell .ps-reaction-picker.is-open,
  .post-show-shell .ps-reaction-more-wrap.is-open,
  .post-show-shell .ps-reaction-picker:has(.ps-reaction-menu:not([hidden])),
  .post-show-shell .ps-reaction-more-wrap:has(.ps-reaction-more-menu:not([hidden])) {
    z-index: 2147483300 !important;
  }

  .post-show-shell .ps-reaction-menu,
  .post-show-shell .ps-reaction-more-menu {
    z-index: 2147483300 !important;
    box-shadow: none !important;
  }

  @media (max-width: 768px) {
    .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
    .post-show-shell .ps-mobile-long-actions {
      z-index: 720 !important;
    }

    .post-show-shell.is-any-overlay-open .ps-mobile-long-actions {
      z-index: 300 !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card {
      position: fixed !important;
      left: 12px !important;
      right: 12px !important;
      top: auto !important;
      bottom: calc(74px + env(safe-area-inset-bottom, 0px)) !important;
      width: auto !important;
      max-width: none !important;
      max-height: min(68vh, 430px) !important;
      overflow: auto !important;
      z-index: 2147483200 !important;
      transform: translate3d(0, 10px, 0) !important;
    }

    .post-show-shell .ps-hover-zone--author-name.is-hover-loading > .ps-hover-card,
    .post-show-shell .ps-hover-zone--author-name.is-hover-ready > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name.is-hover-loading > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name.is-hover-ready > .ps-hover-card {
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
      transform: translate3d(0, 0, 0) !important;
    }

    .post-show-shell .ps-reaction-menu,
    .post-show-shell .ps-reaction-more-menu {
      position: fixed !important;
      left: 12px !important;
      right: 12px !important;
      top: auto !important;
      bottom: calc(74px + env(safe-area-inset-bottom, 0px)) !important;
      width: auto !important;
      max-width: none !important;
      max-height: min(68vh, 430px) !important;
      overflow-y: auto !important;
      z-index: 2147483300 !important;
      transform: none !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-reaction-menu:not([hidden]),
    .post-show-shell .ps-reaction-more-menu:not([hidden]) {
      display: grid !important;
      grid-template-columns: repeat(5, minmax(42px, 1fr)) !important;
      gap: 10px !important;
      align-items: center !important;
      justify-content: center !important;
    }

    .post-show-shell .ps-mobile-accessibility-menu {
      z-index: 2147483100 !important;
    }
  }
</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  var shell = document.querySelector('.post-show-shell');
  if (!shell) return;

  var cardSelector = '.ps-hover-zone--author-name, .ps-hover-zone--category-name';
  var zones = Array.prototype.slice.call(shell.querySelectorAll(cardSelector));
  var openDelay = 1000;
  var loaderDuration = 520;
  var closeDelay = 420;

  function closeZone(zone) {
    if (!zone) return;
    window.clearTimeout(zone.__psMobileOpenTimer || 0);
    window.clearTimeout(zone.__psMobileReadyTimer || 0);
    window.clearTimeout(zone.__psMobileCloseTimer || 0);
    zone.classList.remove('is-hover-loading', 'is-hover-ready');
  }

  function closeOtherZones(activeZone) {
    zones.forEach(function (zone) {
      if (zone !== activeZone) closeZone(zone);
    });
  }

  function openZone(zone) {
    if (!zone) return;
    closeOtherZones(zone);
    window.clearTimeout(zone.__psMobileCloseTimer || 0);

    if (zone.classList.contains('is-hover-loading') || zone.classList.contains('is-hover-ready')) {
      return;
    }

    window.clearTimeout(zone.__psMobileOpenTimer || 0);
    window.clearTimeout(zone.__psMobileReadyTimer || 0);

    zone.__psMobileOpenTimer = window.setTimeout(function () {
      zone.classList.add('is-hover-loading');
      shell.classList.add('is-any-overlay-open');

      zone.__psMobileReadyTimer = window.setTimeout(function () {
        zone.classList.remove('is-hover-loading');
        zone.classList.add('is-hover-ready');
        shell.classList.add('is-any-overlay-open');
      }, loaderDuration);
    }, openDelay);
  }

  function scheduleClose(zone) {
    if (!zone) return;
    window.clearTimeout(zone.__psMobileCloseTimer || 0);
    zone.__psMobileCloseTimer = window.setTimeout(function () {
      closeZone(zone);
      updateOverlayState();
    }, closeDelay);
  }

  zones.forEach(function (zone) {
    var card = zone.querySelector('.ps-hover-card');

    zone.addEventListener('touchstart', function (event) {
      if (card && card.contains(event.target)) return;
      event.preventDefault();
      openZone(zone);
    }, { passive: false });

    zone.addEventListener('click', function (event) {
      if (card && card.contains(event.target)) return;

      var isMobile = window.matchMedia('(max-width: 768px)').matches;
      if (isMobile && !zone.classList.contains('is-hover-ready')) {
        event.preventDefault();
        event.stopPropagation();
        openZone(zone);
      }
    }, true);

    if (card) {
      card.addEventListener('touchstart', function () {
        window.clearTimeout(zone.__psMobileCloseTimer || 0);
      }, { passive: true });
      card.addEventListener('mouseenter', function () {
        window.clearTimeout(zone.__psMobileCloseTimer || 0);
      });
      card.addEventListener('mouseleave', function () {
        scheduleClose(zone);
      });
    }
  });

  document.addEventListener('click', function (event) {
    if (event.target.closest(cardSelector)) return;
    zones.forEach(closeZone);
    updateOverlayState();
  }, true);

  function reactionMenuOpen() {
    return !!shell.querySelector('.ps-reaction-menu:not([hidden]), .ps-reaction-more-menu:not([hidden])');
  }

  function hoverCardOpen() {
    return !!shell.querySelector('.ps-hover-zone--author-name.is-hover-loading, .ps-hover-zone--author-name.is-hover-ready, .ps-hover-zone--category-name.is-hover-loading, .ps-hover-zone--category-name.is-hover-ready');
  }

  function accessibilityOpen() {
    var menu = shell.querySelector('[data-mobile-accessibility-menu]');
    return !!(menu && !menu.hidden && menu.getAttribute('aria-hidden') !== 'true');
  }

  function updateReactionOpenClasses() {
    shell.querySelectorAll('.ps-reaction-picker, .ps-reaction-more-wrap').forEach(function (wrap) {
      var hasOpen = !!wrap.querySelector('.ps-reaction-menu:not([hidden]), .ps-reaction-more-menu:not([hidden])');
      wrap.classList.toggle('is-open', hasOpen);
    });
  }

  function updateOverlayState() {
    updateReactionOpenClasses();
    shell.classList.toggle('is-any-overlay-open', reactionMenuOpen() || hoverCardOpen() || accessibilityOpen());
  }

  var observer = new MutationObserver(updateOverlayState);
  observer.observe(shell, {
    subtree: true,
    attributes: true,
    attributeFilter: ['hidden', 'aria-hidden', 'class', 'aria-expanded']
  });

  window.addEventListener('scroll', updateOverlayState, { passive: true });
  window.addEventListener('resize', updateOverlayState);
  updateOverlayState();
})();
</script>
@endpush


@push('head')
<style>
  /* FINAL FIX: mobilde yazar/kategori kartları eski küçük boyutta kalsın ama görünür olsun */
  @media (max-width: 768px) {
    .post-show-shell .ps-hover-zone--author-name,
    .post-show-shell .ps-hover-zone--category-name {
      position: relative !important;
      z-index: 2147483200 !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card {
      position: fixed !important;
      left: 50% !important;
      right: auto !important;
      top: auto !important;
      bottom: calc(76px + env(safe-area-inset-bottom, 0px)) !important;
      width: min(258px, calc(100vw - 28px)) !important;
      max-width: min(258px, calc(100vw - 28px)) !important;
      max-height: min(52vh, 315px) !important;
      overflow: hidden auto !important;
      border-radius: 14px !important;
      padding-bottom: 10px !important;
      transform: translate3d(-50%, 8px, 0) !important;
      z-index: 2147483200 !important;
      box-shadow: none !important;
    }

    .post-show-shell .ps-hover-zone--author-name.is-hover-loading > .ps-hover-card,
    .post-show-shell .ps-hover-zone--author-name.is-hover-ready > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name.is-hover-loading > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name.is-hover-ready > .ps-hover-card {
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
      transform: translate3d(-50%, 0, 0) !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-cover,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-cover {
      height: 54px !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-main,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-main {
      gap: 8px !important;
      margin-top: -17px !important;
      padding: 0 10px !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-avatar,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-avatar {
      width: 44px !important;
      height: 44px !important;
      flex: 0 0 44px !important;
      flex-basis: 44px !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-content,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-content {
      transform: translateY(4px) !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-title,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-title {
      max-width: 168px !important;
      font-size: 13px !important;
      line-height: 1.16 !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-subtitle,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-subtitle {
      max-width: 168px !important;
      font-size: 11px !important;
      line-height: 1.18 !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-description,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-description {
      min-height: 24px !important;
      max-height: 74px !important;
      padding: 10px 10px 0 !important;
      font-size: 11px !important;
      line-height: 1.34 !important;
      overflow: hidden !important;
      display: -webkit-box !important;
      -webkit-line-clamp: 4 !important;
      -webkit-box-orient: vertical !important;
    }

    .post-show-shell .ps-hover-card-actions {
      width: calc(100% - 20px) !important;
      margin: 9px 10px 0 !important;
      gap: 6px !important;
    }

    .post-show-shell .ps-hover-card-follow,
    .post-show-shell .ps-hover-card-link {
      min-height: 29px !important;
      padding: 7px 8px !important;
      border-radius: 9px !important;
      font-size: 11px !important;
    }

    .post-show-shell .ps-hover-card--category .ps-hover-card-actions > .ps-hover-card-link:only-child {
      width: 100% !important;
      flex-basis: 100% !important;
    }
  }
</style>
@endpush


@push('head')
<style>
  /* FINAL FIX: mobilde yazar/kategori hover kartları sol altta, eski küçük boyutta görünür */
  @media (max-width: 640px) {
    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card,
    .post-show-shell .ps-hover-zone--author-name .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name .ps-hover-card {
      position: fixed !important;
      left: 12px !important;
      right: auto !important;
      top: auto !important;
      bottom: calc(74px + env(safe-area-inset-bottom, 0px)) !important;
      width: min(258px, calc(100vw - 24px)) !important;
      max-width: min(258px, calc(100vw - 24px)) !important;
      transform: translate3d(0, 8px, 0) !important;
      z-index: 999999 !important;
    }

    .post-show-shell .ps-hover-zone--author-name.is-hover-loading > .ps-hover-card,
    .post-show-shell .ps-hover-zone--author-name.is-hover-ready > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name.is-hover-loading > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name.is-hover-ready > .ps-hover-card {
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
      transform: translate3d(0, 0, 0) !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-cover,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-cover {
      height: 58px !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-main,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-main {
      gap: 8px !important;
      margin-top: -18px !important;
      padding: 0 11px !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-avatar,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-avatar {
      width: 46px !important;
      height: 46px !important;
      flex-basis: 46px !important;
      border-width: 2px !important;
      font-size: 12px !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-title,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-title {
      max-width: 168px !important;
      font-size: 13px !important;
      line-height: 1.18 !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-subtitle,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-subtitle {
      max-width: 168px !important;
      font-size: 11px !important;
      line-height: 1.2 !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-description,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-description {
      min-height: 28px !important;
      max-height: 64px !important;
      padding: 11px 11px 0 !important;
      font-size: 11.5px !important;
      line-height: 1.38 !important;
      overflow: hidden !important;
      display: -webkit-box !important;
      -webkit-line-clamp: 4 !important;
      -webkit-box-orient: vertical !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-actions,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-actions {
      width: calc(100% - 22px) !important;
      margin: 10px 11px 0 !important;
      gap: 7px !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-follow,
    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card .ps-hover-card-link,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-follow,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card .ps-hover-card-link {
      min-height: 30px !important;
      padding: 8px 8px !important;
      border-radius: 9px !important;
      font-size: 11.5px !important;
    }
  }
</style>
@endpush

@push('head')
<style>
/* Last readable post text scale */
.post-show-shell .ps-post-body,
.post-show-shell .ps-post-body :where(p, div, li, td, th, blockquote, .ce-paragraph, .cdx-block) {
  font-size: 17px !important;
  line-height: 1.72 !important;
}

.post-show-shell .ps-tags-row,
.post-show-shell .ps-tag {
  font-size: 15px !important;
  line-height: 1.35 !important;
}

@media (max-width: 640px) {
  .post-show-shell .ps-post-body,
  .post-show-shell .ps-post-body :where(p, div, li, td, th, blockquote, .ce-paragraph, .cdx-block) {
    font-size: 16px !important;
    line-height: 1.68 !important;
  }

  .post-show-shell .ps-tags-row,
  .post-show-shell .ps-tag {
    font-size: 15px !important;
  }
}
</style>
@endpush


@push('head')
<style>
/* Post show kaynak kutusu: favicon + sade kart görünümü */
.post-show-shell .ps-source-link {
  display: flex !important;
  align-items: flex-start !important;
  justify-content: space-between !important;
  gap: 18px !important;
  min-height: 68px !important;
  padding: 14px 18px !important;
  border: 1px solid rgba(15, 23, 42, .04) !important;
  border-radius: 16px !important;
  background: #f3f4f6 !important;
  color: #111827 !important;
  text-decoration: none !important;
  box-shadow: none !important;
}

.post-show-shell .ps-source-link:hover,
.post-show-shell .ps-source-link:focus-visible {
  background: #ebeef2 !important;
  border-color: rgba(15, 23, 42, .06) !important;
  color: #111827 !important;
  outline: none !important;
}

.post-show-shell .ps-source-copy {
  display: flex !important;
  flex-direction: column !important;
  justify-content: center !important;
  gap: 6px !important;
  min-width: 0 !important;
  flex: 1 1 auto !important;
}

.post-show-shell .ps-source-label {
  color: #9ca3af !important;
  font-size: 10px !important;
  font-weight: 400 !important;
  line-height: 1 !important;
  letter-spacing: .08em !important;
  text-transform: uppercase !important;
}

.post-show-shell .ps-source-domain-row {
  display: inline-flex !important;
  align-items: center !important;
  gap: 7px !important;
  min-width: 0 !important;
  max-width: 100% !important;
}

.post-show-shell .ps-source-favicon {
  display: inline-flex !important;
  width: 16px !important;
  height: 16px !important;
  flex: 0 0 16px !important;
  border-radius: 999px !important;
  object-fit: cover !important;
  background: #fff !important;
  box-shadow: none !important;
}

.post-show-shell .ps-source-domain {
  overflow: hidden !important;
  color: #111827 !important;
  font-size: 16px !important;
  font-weight: 400 !important;
  line-height: 1.3 !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
}

.post-show-shell .ps-source-icon {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 20px !important;
  height: 20px !important;
  flex: 0 0 auto !important;
  color: #9ca3af !important;
  margin-top: 2px !important;
}

.post-show-shell .ps-source-icon iconify-icon {
  font-size: 16px !important;
}

html.dark .post-show-shell .ps-source-link,
body.dark .post-show-shell .ps-source-link,
.dark .post-show-shell .ps-source-link,
[data-theme="dark"] .post-show-shell .ps-source-link {
  background: #161b22 !important;
  border-color: rgba(255, 255, 255, .08) !important;
  color: #fff !important;
}

html.dark .post-show-shell .ps-source-link:hover,
html.dark .post-show-shell .ps-source-link:focus-visible,
body.dark .post-show-shell .ps-source-link:hover,
body.dark .post-show-shell .ps-source-link:focus-visible,
.dark .post-show-shell .ps-source-link:hover,
.dark .post-show-shell .ps-source-link:focus-visible,
[data-theme="dark"] .post-show-shell .ps-source-link:hover,
[data-theme="dark"] .post-show-shell .ps-source-link:focus-visible {
  background: #1f2937 !important;
  border-color: rgba(255, 255, 255, .14) !important;
  color: #fff !important;
}

html.dark .post-show-shell .ps-source-label,
body.dark .post-show-shell .ps-source-label,
.dark .post-show-shell .ps-source-label,
[data-theme="dark"] .post-show-shell .ps-source-label {
  color: #94a3b8 !important;
}

html.dark .post-show-shell .ps-source-domain,
html.dark .post-show-shell .ps-source-icon,
body.dark .post-show-shell .ps-source-domain,
body.dark .post-show-shell .ps-source-icon,
.dark .post-show-shell .ps-source-domain,
.dark .post-show-shell .ps-source-icon,
[data-theme="dark"] .post-show-shell .ps-source-domain,
[data-theme="dark"] .post-show-shell .ps-source-icon {
  color: #fff !important;
}

html.dark .post-show-shell .ps-source-favicon,
body.dark .post-show-shell .ps-source-favicon,
.dark .post-show-shell .ps-source-favicon,
[data-theme="dark"] .post-show-shell .ps-source-favicon {
  background: rgba(255, 255, 255, .92) !important;
}
</style>
@endpush


@push('head')
<style>
/* Göz ikonuna tıklanınca açılan gönderi istatistikleri penceresi */
.post-show-shell .ps-view-count {
  border: 0 !important;
  background: transparent !important;
  cursor: pointer !important;
  text-decoration: none !important;
  -webkit-appearance: none !important;
  appearance: none !important;
}

.post-show-shell .ps-view-count:hover,
.post-show-shell .ps-view-count:focus-visible,
.post-show-shell .ps-view-count.is-open {
  color: #2563eb !important;
  outline: none !important;
}

.post-show-shell .ps-show-stats-modal[hidden] {
  display: none !important;
}

.post-show-shell .ps-show-stats-modal {
  position: fixed !important;
  inset: 0 !important;
  z-index: 999999 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 18px !important;
}

.post-show-shell .ps-show-stats-backdrop {
  position: absolute !important;
  inset: 0 !important;
  background: rgba(15, 23, 42, .40) !important;
  -webkit-backdrop-filter: blur(3px) !important;
  backdrop-filter: blur(3px) !important;
}

.post-show-shell .ps-show-stats-panel {
  position: relative !important;
  z-index: 1 !important;
  width: min(520px, calc(100vw - 28px)) !important;
  max-height: calc(100dvh - 32px) !important;
  overflow-y: auto !important;
  padding: 24px 24px 26px !important;
  border: 1px solid rgba(15, 23, 42, .08) !important;
  border-radius: 12px !important;
  background: #ffffff !important;
  color: #020617 !important;
  box-shadow: none !important;
}

.post-show-shell .ps-show-stats-head {
  display: flex !important;
  align-items: flex-start !important;
  justify-content: space-between !important;
  gap: 16px !important;
  margin-bottom: 28px !important;
}

.post-show-shell .ps-show-stats-title {
  display: block !important;
  margin: 0 !important;
  color: #020617 !important;
  font-size: 21px !important;
  font-weight: 500 !important;
  line-height: 1.2 !important;
  letter-spacing: -0.02em !important;
}

.post-show-shell .ps-show-stats-close {
  width: 28px !important;
  height: 28px !important;
  min-width: 28px !important;
  border: 0 !important;
  border-radius: 0 !important;
  background: transparent !important;
  color: #0f172a !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 0 !important;
  cursor: pointer !important;
  box-shadow: none !important;
}

.post-show-shell .ps-show-stats-close svg {
  width: 20px !important;
  height: 20px !important;
}

.post-show-shell .ps-show-stats-close:hover,
.post-show-shell .ps-show-stats-close:focus-visible {
  color: #2563eb !important;
  outline: none !important;
}

.post-show-shell .ps-show-stats-grid {
  display: grid !important;
  grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
  column-gap: 34px !important;
  row-gap: 34px !important;
}

.post-show-shell .ps-show-stats-item {
  min-width: 0 !important;
  padding: 0 !important;
  border: 0 !important;
  border-radius: 0 !important;
  background: transparent !important;
  color: #334155 !important;
  display: block !important;
}

.post-show-shell .ps-show-stats-item strong {
  display: block !important;
  margin: 0 0 4px !important;
  color: #020617 !important;
  font-size: 21px !important;
  font-weight: 500 !important;
  line-height: 1.1 !important;
  letter-spacing: -0.02em !important;
}

.post-show-shell .ps-show-stats-item span {
  display: block !important;
  color: #475569 !important;
  font-size: 13px !important;
  font-weight: 400 !important;
  line-height: 1.25 !important;
}

body.ps-show-stats-is-open {
  overflow: hidden !important;
}

@media (max-width: 640px) {
  .post-show-shell .ps-show-stats-modal {
    align-items: center !important;
    padding: 12px !important;
  }

  .post-show-shell .ps-show-stats-panel {
    width: 100% !important;
    border-radius: 14px !important;
    padding: 22px 20px 24px !important;
  }

  .post-show-shell .ps-show-stats-head {
    margin-bottom: 24px !important;
  }

  .post-show-shell .ps-show-stats-title {
    font-size: 20px !important;
  }

  .post-show-shell .ps-show-stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    column-gap: 28px !important;
    row-gap: 30px !important;
  }
}

html.dark .post-show-shell .ps-show-stats-panel,
body.dark .post-show-shell .ps-show-stats-panel,
.dark .post-show-shell .ps-show-stats-panel,
[data-theme="dark"] .post-show-shell .ps-show-stats-panel {
  background: #111827 !important;
  border-color: rgba(148, 163, 184, .20) !important;
  color: #f8fafc !important;
}

html.dark .post-show-shell .ps-show-stats-title,
html.dark .post-show-shell .ps-show-stats-item strong,
body.dark .post-show-shell .ps-show-stats-title,
body.dark .post-show-shell .ps-show-stats-item strong,
.dark .post-show-shell .ps-show-stats-title,
.dark .post-show-shell .ps-show-stats-item strong,
[data-theme="dark"] .post-show-shell .ps-show-stats-title,
[data-theme="dark"] .post-show-shell .ps-show-stats-item strong {
  color: #f8fafc !important;
}

html.dark .post-show-shell .ps-show-stats-item span,
body.dark .post-show-shell .ps-show-stats-item span,
.dark .post-show-shell .ps-show-stats-item span,
[data-theme="dark"] .post-show-shell .ps-show-stats-item span {
  color: #cbd5e1 !important;
}

html.dark .post-show-shell .ps-show-stats-close,
body.dark .post-show-shell .ps-show-stats-close,
.dark .post-show-shell .ps-show-stats-close,
[data-theme="dark"] .post-show-shell .ps-show-stats-close {
  color: #f8fafc !important;
}</style>
@endpush

@push('scripts')
<script>
(function () {
  'use strict';

  function closeStatsModal(modal) {
    if (!modal) return;
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('ps-show-stats-is-open');

    var triggerId = modal.getAttribute('data-opened-by');
    var trigger = triggerId ? document.getElementById(triggerId) : document.querySelector('[data-show-stats-trigger][aria-controls="' + modal.id + '"]');
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
      trigger.classList.remove('is-open');
      trigger.focus({ preventScroll: true });
    }
    modal.removeAttribute('data-opened-by');
  }

  function openStatsModal(trigger) {
    if (!trigger) return;
    var modalId = trigger.getAttribute('aria-controls');
    var modal = modalId ? document.getElementById(modalId) : null;
    if (!modal) return;

    document.querySelectorAll('[data-show-stats-modal]').forEach(function (otherModal) {
      if (otherModal !== modal && !otherModal.hidden) closeStatsModal(otherModal);
    });

    if (!trigger.id) {
      trigger.id = 'show_stats_trigger_' + Math.random().toString(36).slice(2, 9);
    }

    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    modal.setAttribute('data-opened-by', trigger.id);
    trigger.setAttribute('aria-expanded', 'true');
    trigger.classList.add('is-open');
    document.body.classList.add('ps-show-stats-is-open');

    window.requestAnimationFrame(function () {
      var closeButton = modal.querySelector('[data-show-stats-close]');
      if (closeButton) closeButton.focus({ preventScroll: true });
    });
  }

  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-show-stats-trigger]');
    if (trigger) {
      event.preventDefault();
      event.stopPropagation();
      openStatsModal(trigger);
      return;
    }

    var closeButton = event.target.closest('[data-show-stats-close]');
    if (closeButton) {
      event.preventDefault();
      closeStatsModal(closeButton.closest('[data-show-stats-modal]'));
      return;
    }

    var backdrop = event.target.closest('[data-show-stats-backdrop]');
    if (backdrop) {
      closeStatsModal(backdrop.closest('[data-show-stats-modal]'));
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('[data-show-stats-modal]:not([hidden])').forEach(function (modal) {
      closeStatsModal(modal);
    });
  });
})();
</script>
@endpush

@push('head')
<style>
  /* FINAL OVERRIDE: post-show mobil aksiyon barı, global mobil alt menünün üstünde dursun */
  @media (max-width: 768px) {
    .post-show-shell.has-mobile-long-actions {
      padding-bottom: calc(184px + env(safe-area-inset-bottom, 0px)) !important;
    }

    .post-show-shell.has-mobile-long-actions .ps-mobile-long-actions,
    .post-show-shell .ps-mobile-long-actions {
      bottom: calc(82px + env(safe-area-inset-bottom, 0px)) !important;
      z-index: 720 !important;
    }

    .post-show-shell.has-mobile-long-actions.is-mobile-long-actions-visible .ps-mobile-long-actions {
      transform: translate3d(-50%, 0, 0) scale(1) !important;
    }

    .post-show-shell.is-post-tags-reached .ps-mobile-long-actions,
    .post-show-shell.is-post-tags-reached.is-mobile-long-actions-visible .ps-mobile-long-actions {
      transform: translate3d(-50%, 14px, 0) scale(.98) !important;
    }

    .post-show-shell .ps-mobile-accessibility-menu {
      bottom: calc(142px + env(safe-area-inset-bottom, 0px)) !important;
    }

    .post-show-shell .ps-hover-zone--author-name > .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name > .ps-hover-card,
    .post-show-shell .ps-hover-zone--author-name .ps-hover-card,
    .post-show-shell .ps-hover-zone--category-name .ps-hover-card,
    .post-show-shell .ps-reaction-menu,
    .post-show-shell .ps-reaction-more-menu {
      bottom: calc(142px + env(safe-area-inset-bottom, 0px)) !important;
    }
  }

  /* Featured image alignment: match article-body media on every viewport. */
  .post-show-shell .ps-post-image {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 0 14px !important;
    box-sizing: border-box !important;
    transform: none !important;
  }

  .post-show-shell .ps-post-image > img {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    object-fit: cover !important;
    object-position: center center !important;
    transform: none !important;
  }
</style>
@endpush


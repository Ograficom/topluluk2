# Post Card Reference

Bu dosya `post-card` gorunumunun markup, partial, script ve ilgili style bloklarini bir araya getirir.

## Post Card Component

Kaynak: `resources/views/blog/post-card.blade.php`

```blade
@props([
    'post' => null,
    'title' => null,
    'excerpt' => null,
    'featuredImage' => null,
    'createdAt' => null,
    'authorName' => null,
    'authorAvatar' => null,
    'reactions' => [],
    'reactionTypes' => [],
    'isHero' => false,
])

@php
    $postArr = is_array($post) ? $post : (is_object($post) ? (array) $post : []);
    $postObj = is_object($post) ? $post : null;
    $postAuthor = $postObj ? $postObj->author : null;
    $postCategory = $postObj ? $postObj->category : null;
    $viewer = auth()->user();
    $isHero = (bool) $isHero;
    $resolveOptimizedImage = static function (?string $url, ?string $variant = null): ?string {
        if (!$variant) {
            return $url;
        }

        return \App\Support\OptimizedImage::variantUrl($url, $variant) ?? $url;
    };
    $resolveImageDimensions = static function (?string $url, array $fallback = [0, 0]): array {
        return \App\Support\OptimizedImage::dimensions($url, $fallback);
    };

    $resolvedTitle = $title;
    if (!is_string($resolvedTitle) || trim($resolvedTitle) === '') {
        $resolvedTitle = optional($postObj)->title ?? $postArr['title'] ?? __('site.post.untitled_story');
    }
    $title = trim($resolvedTitle);

    $featuredImage = $featuredImage
        ?? (optional($postObj)->featured_image_url
        ?? optional($postObj)->featured_image
        ?? optional($postObj)->featuredImage
        ?? $postArr['featured_image_url']
        ?? $postArr['featuredImage']
        ?? $postArr['featured_image']
        ?? null);

    $createdAt = $createdAt ?? (optional($postObj)->published_at ?? optional($postObj)->created_at ?? $postArr['created_at'] ?? null);
    $createdHuman = '';
    $createdIso = '';
    if (!empty($createdAt)) {
        try {
            $date = \Illuminate\Support\Carbon::parse($createdAt);
            $createdHuman = $date->diffForHumans();
            $createdIso = $date->toIso8601String();
        } catch (\Throwable $e) {
            $createdHuman = (string) $createdAt;
        }
    }
    if ($createdHuman === '') {
        $createdHuman = __('site.common.recently');
    }

    $postSlug = (string) (optional($postObj)->slug ?? $postArr['slug'] ?? '');
    $postUrl = $postSlug !== '' ? route('blog.post', $postSlug) : '#';
    $commentsUrl = $postUrl !== '#' ? $postUrl . '#comments' : '#';

    $authorName = trim((string) (
        $authorName
        ?? (optional($postObj)->author_name
        ?? $postArr['author_name']
        ?? (optional($postAuthor)->name ?? null)
        ?? __('site.post.community_author'))
    ));

    $authorAvatar = $authorAvatar
        ?? (optional($postObj)->author_avatar
        ?? $postArr['author_avatar']
        ?? (optional($postAuthor)->profile_photo_url ?? null)
        ?? null);

    $authorInitials = collect(preg_split('/\s+/', trim((string) $authorName), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
    if ($authorInitials === '') {
        $authorInitials = 'AL';
    }

    $authorUrl = !empty($postAuthor?->username)
        ? route('users.show', ['user' => $postAuthor->username])
        : '#';

    $showVerified = (bool) (
        $postAuthor?->is_verified
        || filled($postAuthor?->verification_badge)
        || filled($postAuthor?->verification_badge_svg)
    );

    $categoryName = trim((string) (
        optional($postCategory)->name
        ?? optional($postObj)->category_name
        ?? $postArr['category_name']
        ?? $postArr['category']
        ?? ''
    ));
    $hasCategory = $categoryName !== '';
    $categoryAvatar = optional($postCategory)->profile_image_url
        ?? optional($postCategory)->profile_image
        ?? data_get($postArr, 'category_avatar')
        ?? data_get($postArr, 'category.profile_image_url')
        ?? data_get($postArr, 'category.profile_image')
        ?? null;
    $categoryAvatar = $resolveOptimizedImage($categoryAvatar, 'sidebar-64');
    $categoryParts = collect(preg_split('/\s+/', trim((string) $categoryName), -1, PREG_SPLIT_NO_EMPTY))->values();
    $categoryInitials = $categoryParts->count() <= 1
        ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $categoryParts->first(), 0, 2))
        : $categoryParts
            ->take(2)
            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $part, 0, 1)))
            ->implode('');
    $categoryInitials = $categoryInitials !== '' ? $categoryInitials : 'AI';
    $categoryBadgeText = $hasCategory ? $categoryInitials : 'AI';

    $categoryUrl = $hasCategory && !empty($postCategory?->slug)
        ? route('blog.category', ['category' => $postCategory->slug])
        : null;

    $resolvedExcerpt = trim((string) (
        $excerpt
        ?? optional($postObj)->excerpt
        ?? $postArr['excerpt']
        ?? optional($postObj)->content
        ?? $postArr['content']
        ?? ''
    ));

    $excerptLooksBroken = \Illuminate\Support\Str::contains($resolvedExcerpt, '[object Object]');
    if (($resolvedExcerpt === '' || $excerptLooksBroken) && is_array(optional($postObj)->content_json)) {
        $resolvedExcerpt = collect($postObj->content_json['blocks'] ?? [])
            ->map(function ($block) {
                $type = $block['type'] ?? null;
                $data = $block['data'] ?? [];

                return match ($type) {
                    'paragraph', 'header', 'quote' => trim((string) ($data['text'] ?? '')),
                    'list' => collect($data['items'] ?? [])->flatten()->implode(' '),
                    'checklist' => collect($data['items'] ?? [])->pluck('text')->implode(' '),
                    default => trim((string) ($data['caption'] ?? '')),
                };
            })
            ->filter()
            ->implode(' ');
    }

    $resolvedExcerptRaw = html_entity_decode(trim(strip_tags($resolvedExcerpt)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $resolvedExcerptRaw = preg_replace('/(?:\[\s*object Object\s*\]\s*)+/iu', ' ', $resolvedExcerptRaw) ?? $resolvedExcerptRaw;
    $resolvedExcerptRaw = preg_replace('/\s+/u', ' ', trim($resolvedExcerptRaw)) ?? trim($resolvedExcerptRaw);
    $resolvedExcerptShort = \Illuminate\Support\Str::limit($resolvedExcerptRaw, 180);
    $resolvedExcerptExpanded = \Illuminate\Support\Str::limit($resolvedExcerptRaw, 900);
    $hasInlineContinue = mb_strlen($resolvedExcerptRaw) > mb_strlen($resolvedExcerptShort);
    $resolvedExcerpt = $resolvedExcerptShort;
    $linkPreview = optional($postObj)->link_preview
        ?? $postArr['link_preview']
        ?? null;
    if (is_object($linkPreview)) {
        $linkPreview = (array) $linkPreview;
    }
    $linkPreview = is_array($linkPreview) ? $linkPreview : null;

    $sanitizeSocialEmbedUrl = function (?string $url): ?string {
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
        $allowed = [
            'www.youtube.com',
            'youtube.com',
            'www.youtube-nocookie.com',
            'youtube-nocookie.com',
            'www.instagram.com',
            'instagram.com',
            'www.tiktok.com',
            'tiktok.com',
            'player.vimeo.com',
            'www.dailymotion.com',
            'dailymotion.com',
            'player.twitch.tv',
            'www.facebook.com',
            'facebook.com',
            'twitframe.com',
            'vine.co',
        ];

        return in_array($host, $allowed, true) ? $url : null;
    };

    $buildSocialEmbedUrlFromUrl = function (?string $value) use ($sanitizeSocialEmbedUrl): ?string {
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

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '/');
        $pathParts = array_values(array_filter(explode('/', trim($path, '/'))));
        parse_str((string) ($parts['query'] ?? ''), $query);
        $parentHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

        if ($host === 'youtu.be') {
            $id = $pathParts[0] ?? null;
            return $id ? $sanitizeSocialEmbedUrl('https://www.youtube.com/embed/' . rawurlencode($id)) : null;
        }

        if (str_ends_with($host, 'youtube.com') || str_ends_with($host, 'youtube-nocookie.com')) {
            $id = null;
            if (($pathParts[0] ?? '') === 'watch') {
                $id = (string) ($query['v'] ?? '');
            } elseif (($pathParts[0] ?? '') === 'shorts' || ($pathParts[0] ?? '') === 'embed') {
                $id = $pathParts[1] ?? null;
            }
            return $id ? $sanitizeSocialEmbedUrl('https://www.youtube.com/embed/' . rawurlencode($id)) : null;
        }

        if (str_ends_with($host, 'instagram.com')) {
            $kind = $pathParts[0] ?? '';
            $code = $pathParts[1] ?? '';
            if (in_array($kind, ['p', 'reel', 'tv'], true) && $code !== '') {
                return $sanitizeSocialEmbedUrl("https://www.instagram.com/{$kind}/" . rawurlencode($code) . '/embed');
            }
        }

        if (str_ends_with($host, 'tiktok.com')) {
            if (($pathParts[0] ?? '') === 'embed' && ($pathParts[1] ?? '') === 'v2' && !empty($pathParts[2])) {
                return $sanitizeSocialEmbedUrl('https://www.tiktok.com/embed/v2/' . rawurlencode((string) $pathParts[2]));
            }
            if (($pathParts[1] ?? '') === 'video' && !empty($pathParts[2])) {
                return $sanitizeSocialEmbedUrl('https://www.tiktok.com/embed/v2/' . rawurlencode((string) $pathParts[2]));
            }
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            $id = (($pathParts[0] ?? '') === 'video') ? ($pathParts[1] ?? null) : ($pathParts[0] ?? null);
            if ($id && preg_match('/^\d+$/', $id)) {
                return $sanitizeSocialEmbedUrl('https://player.vimeo.com/video/' . $id);
            }
        }

        if (str_ends_with($host, 'dailymotion.com')) {
            $id = (($pathParts[0] ?? '') === 'video') ? ($pathParts[1] ?? null) : null;
            return $id ? $sanitizeSocialEmbedUrl('https://www.dailymotion.com/embed/video/' . rawurlencode($id)) : null;
        }

        if ($host === 'dai.ly') {
            $id = $pathParts[0] ?? null;
            return $id ? $sanitizeSocialEmbedUrl('https://www.dailymotion.com/embed/video/' . rawurlencode($id)) : null;
        }

        if (in_array($host, ['twitch.tv', 'www.twitch.tv'], true)) {
            if (($pathParts[0] ?? '') === 'videos' && !empty($pathParts[1])) {
                return $sanitizeSocialEmbedUrl('https://player.twitch.tv/?video=v' . rawurlencode((string) $pathParts[1]) . '&parent=' . rawurlencode((string) $parentHost));
            }
            if (count($pathParts) >= 3 && ($pathParts[1] ?? '') === 'clip' && !empty($pathParts[2])) {
                return $sanitizeSocialEmbedUrl('https://player.twitch.tv/?clip=' . rawurlencode((string) $pathParts[2]) . '&parent=' . rawurlencode((string) $parentHost));
            }
        }

        if ($host === 'clips.twitch.tv') {
            $clip = $pathParts[0] ?? null;
            return $clip ? $sanitizeSocialEmbedUrl('https://player.twitch.tv/?clip=' . rawurlencode((string) $clip) . '&parent=' . rawurlencode((string) $parentHost)) : null;
        }

        if (str_ends_with($host, 'facebook.com') || str_ends_with($host, 'fb.watch')) {
            return $sanitizeSocialEmbedUrl('https://www.facebook.com/plugins/video.php?href=' . rawurlencode($value) . '&show_text=false');
        }

        if (in_array($host, ['x.com', 'www.x.com'], true) || str_ends_with($host, 'twitter.com')) {
            if (in_array('status', $pathParts, true)) {
                return $sanitizeSocialEmbedUrl('https://twitframe.com/show?url=' . rawurlencode($value));
            }
        }

        if (str_ends_with($host, 'vine.co') && ($pathParts[0] ?? '') === 'v' && !empty($pathParts[1])) {
            return $sanitizeSocialEmbedUrl('https://vine.co/v/' . rawurlencode((string) $pathParts[1]) . '/embed/simple');
        }

        return null;
    };

    $socialEmbedUrls = collect(is_array(optional($postObj)->content_json) ? (optional($postObj)->content_json['blocks'] ?? []) : [])
        ->map(function ($block) use ($sanitizeSocialEmbedUrl, $buildSocialEmbedUrlFromUrl) {
            if (!is_array($block)) {
                return null;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            if ($type === 'socialEmbed' || $type === 'embed') {
                $raw = trim((string) ($data['src'] ?? $data['embed'] ?? $data['source'] ?? ''));
                return $buildSocialEmbedUrlFromUrl($raw) ?? $sanitizeSocialEmbedUrl($raw);
            }

            if ($type === 'paragraph') {
                $text = trim((string) ($data['text'] ?? ''));
                return $buildSocialEmbedUrlFromUrl($text);
            }

            return null;
        })
        ->filter()
        ->unique()
        ->values();
    $socialPreviewImage = null;
    $primarySocialEmbedUrl = (string) ($socialEmbedUrls->first() ?? '');
    if ($primarySocialEmbedUrl !== '' && preg_match('#youtube(?:-nocookie)?\.com/embed/([^?&/]+)#i', $primarySocialEmbedUrl, $matches)) {
        $socialPreviewImage = 'https://i.ytimg.com/vi/' . rawurlencode((string) $matches[1]) . '/hqdefault.jpg';
    }
    $socialProviderMeta = (function (?string $embedUrl): array {
        $embedUrl = trim((string) $embedUrl);
        $host = strtolower((string) parse_url($embedUrl, PHP_URL_HOST));

        return match (true) {
            str_contains($host, 'youtube') => ['label' => 'YouTube', 'cta' => 'Izlemek icin: YouTube'],
            str_contains($host, 'instagram') => ['label' => 'Instagram', 'cta' => 'Izlemek icin: Instagram'],
            str_contains($host, 'tiktok') => ['label' => 'TikTok', 'cta' => 'Izlemek icin: TikTok'],
            str_contains($host, 'vimeo') => ['label' => 'Vimeo', 'cta' => 'Izlemek icin: Vimeo'],
            str_contains($host, 'dailymotion') => ['label' => 'Dailymotion', 'cta' => 'Izlemek icin: Dailymotion'],
            str_contains($host, 'facebook') => ['label' => 'Facebook', 'cta' => 'Izlemek icin: Facebook'],
            str_contains($host, 'twitch') => ['label' => 'Twitch', 'cta' => 'Izlemek icin: Twitch'],
            str_contains($host, 'twitframe') || str_contains($host, 'twitter') || $host === 'x.com' || $host === 'www.x.com' => ['label' => 'X', 'cta' => 'Acmak icin: X'],
            str_contains($host, 'vine') => ['label' => 'Vine', 'cta' => 'Izlemek icin: Vine'],
            default => ['label' => 'Video', 'cta' => 'Videoyu ac'],
        };
    })($primarySocialEmbedUrl);

    $normalizeCardImageUrl = function (?string $url): ?string {
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
            return url('/storage/' . \Illuminate\Support\Str::after($url, 'storage/'));
        }

        if (\Illuminate\Support\Str::startsWith($url, '/')) {
            return url($url);
        }

        if (preg_match('/\.(png|jpe?g|gif|webp|svg)(?:\?.*)?$/i', $url)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($url);
        }

        return null;
    };

    $contentBlocks = collect(is_array(optional($postObj)->content_json) ? (optional($postObj)->content_json['blocks'] ?? []) : []);
    $contentImageUrls = $contentBlocks->flatMap(function ($block) use ($normalizeCardImageUrl) {
        if (!is_array($block)) {
            return [];
        }

        $type = (string) ($block['type'] ?? '');
        $data = is_array($block['data'] ?? null) ? $block['data'] : [];
        $urls = [];

        if ($type === 'image') {
            $urls[] = data_get($data, 'file.url') ?? data_get($data, 'url') ?? data_get($data, 'src');
        } elseif (in_array($type, ['gallery', 'carousel', 'slider'], true)) {
            foreach (($data['images'] ?? $data['items'] ?? $data['slides'] ?? []) as $entry) {
                if (is_array($entry)) {
                    $urls[] = data_get($entry, 'file.url') ?? data_get($entry, 'url') ?? data_get($entry, 'src') ?? data_get($entry, 'image');
                } elseif (is_string($entry)) {
                    $urls[] = $entry;
                }
            }
        }

        return collect($urls)
            ->map($normalizeCardImageUrl)
            ->filter()
            ->all();
    });

    $htmlContentImageUrls = collect();
    $contentHtml = (string) (optional($postObj)->content ?? $postArr['content'] ?? '');
    if ($contentHtml !== '' && preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $contentHtml, $matches)) {
        $htmlContentImageUrls = collect($matches[1] ?? [])
            ->map($normalizeCardImageUrl)
            ->filter();
    }

    $contentGalleryImages = $contentImageUrls
        ->merge($htmlContentImageUrls)
        ->filter()
        ->unique()
        ->values();

    $contentGalleryImages = $contentGalleryImages
        ->map(function ($image) use ($resolveOptimizedImage, $resolveImageDimensions) {
            $display = $resolveOptimizedImage($image, 'card-640');
            [$width, $height] = $resolveImageDimensions($display, [1024, 1024]);

            return [
                'src' => $display,
                'width' => $width,
                'height' => $height,
            ];
        })
        ->values();

    $hasContentGallery = !$featuredImage && $contentGalleryImages->isNotEmpty();
    $displayImage = $resolveOptimizedImage($featuredImage, 'card-640');
    [$displayImageWidth, $displayImageHeight] = $resolveImageDimensions($displayImage, [1024, 1024]);
    $mediaLoading = $isHero ? 'eager' : 'lazy';
    $mediaFetchPriority = $isHero ? 'high' : 'auto';
    $shouldRenderMediaBlur = !$isHero;
    $showVideoPreview = !$featuredImage && !$hasContentGallery && $socialEmbedUrls->isNotEmpty();

    static $fallbackReactionTypes = null;
    $reactionTypesAll = collect($reactionTypes ?? ($postObj->reactionTypes ?? collect()));
    if ($reactionTypesAll->isEmpty()) {
        if ($fallbackReactionTypes === null) {
            $fallbackReactionTypes = \App\Models\ReactionType::query()
                ->where('is_active', true)
                ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);
        }

        $reactionTypesAll = collect($fallbackReactionTypes);
    }

    $typeMap = $reactionTypesAll->mapWithKeys(function ($type) {
        $id = $type['id'] ?? ($type->id ?? null);

        return $id ? [$id => [
            'id' => $id,
            'short_code' => $type['short_code'] ?? ($type->short_code ?? null),
            'emoji' => $type['emoji'] ?? ($type->emoji ?? null),
            'gif_url' => $type['gif_url'] ?? ($type->gif_url ?? null),
            'label' => $type['label'] ?? ($type->label ?? null),
        ]] : [];
    });

    $renderReactionIcon = function ($value, $labelText = null) {
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
            return '<img src="' . e($resolvedValue) . '" alt="' . e($labelText ?: 'reaction') . '" class="h-5 w-5 rounded-full object-cover">';
        }

        return e($trimmedValue);
    };

    $reactionCounts = collect($postObj->reaction_counts ?? [])->mapWithKeys(fn ($cnt, $typeId) => [$typeId => $cnt]);
    if ($reactionCounts->isEmpty() && $postObj && method_exists($postObj, 'reactions')) {
        $reactionCounts = $postObj->reactions()
            ->whereNotNull('reaction_type_id')
            ->selectRaw('reaction_type_id, count(*) as count')
            ->groupBy('reaction_type_id')
            ->pluck('count', 'reaction_type_id');
    }

    $reactionPills = collect($reactions)
        ->map(function ($reaction) {
            return [
                'count' => (int) ($reaction['count'] ?? 0),
                'icon' => $reaction['icon'] ?? ($reaction['emoji'] ?? ($reaction['gif_url'] ?? null)),
                'label' => $reaction['label'] ?? null,
                'type_id' => $reaction['type_id'] ?? null,
                'short_code' => $reaction['short_code'] ?? null,
            ];
        })
        ->filter(fn ($reaction) => $reaction['count'] > 0)
        ->values();

    if ($reactionPills->isEmpty()) {
        $reactionPills = $reactionCounts->map(function ($count, $typeId) use ($typeMap) {
            $type = $typeMap->get($typeId);
            if (!$type) {
                return null;
            }

            return [
                'count' => (int) $count,
                'icon' => $type['emoji'] ?? $type['gif_url'] ?? null,
                'label' => $type['label'] ?? null,
                'type_id' => $type['id'] ?? null,
                'short_code' => $type['short_code'] ?? null,
            ];
        })->filter()->values();
    }

    $visibleReactionPills = $reactionPills->take(6)->values();
    $reactionOverflowCount = max($reactionPills->count() - $visibleReactionPills->count(), 0);
    $totalReactionCount = (int) ($reactionPills->sum('count') ?: (optional($postObj)->reactions_count ?? $postArr['reactions_count'] ?? 0));

    $commentsCount = (int) (optional($postObj)->comments_count ?? $postArr['comments_count'] ?? 0);
    $viewsCount = (int) (optional($postObj)->views_count ?? $postArr['views_count'] ?? 0);
    $formatCompactMetric = function (int $value): string {
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
    $isPinned = (bool) (optional($postObj)->is_pinned ?? $postArr['is_pinned'] ?? false);
    $isCommentsDisabled = (bool) (optional($postObj)->comments_disabled ?? $postArr['comments_disabled'] ?? false);
    $isNsfw = (bool) (optional($postObj)->is_nsfw ?? $postArr['is_nsfw'] ?? false);
    $isBookmarked = (bool) (optional($postObj)->is_bookmarked ?? $postArr['is_bookmarked'] ?? false);

    $commenterPreviews = collect(optional($postObj)->commenter_previews ?? $postArr['commenter_previews'] ?? [])
        ->take(2)
        ->values();
    $latestCommentPreview = optional($postObj)->latest_comment_preview ?? $postArr['latest_comment_preview'] ?? null;
    $latestCommentAvatar = $latestCommentPreview['avatar'] ?? data_get($commenterPreviews->first(), 'avatar');
    $latestCommentContent = trim((string) ($latestCommentPreview['content'] ?? ''));
    if ($latestCommentContent === '') {
        $latestCommentContent = $commentsCount > 0 ? __('site.widgets.latest_comments') : "\u{0130}lk yorumu sen yap";
    }
    $latestCommentContent = \Illuminate\Support\Str::limit($latestCommentContent, 120);
    $bookmarksCount = (int) (optional($postObj)->bookmarkers_count ?? $postArr['bookmarkers_count'] ?? 0);
    if ($bookmarksCount === 0 && $postObj && method_exists($postObj, 'bookmarkers')) {
        $bookmarksCount = (int) $postObj->bookmarkers()->count();
    }
    $commentsCountDisplay = $formatCompactMetric($commentsCount);
    $bookmarksCountDisplay = $formatCompactMetric($bookmarksCount);
    $viewsCountDisplay = $formatCompactMetric($viewsCount);
    $summaryText = trim((string) ($resolvedExcerptExpanded !== '' ? $resolvedExcerptExpanded : $resolvedExcerptShort));
    $hasSummary = $summaryText !== '';
    $postPublishedMoment = null;
    $postUpdatedMoment = null;
    try {
        $postPublishedMoment = $createdAt ? \Illuminate\Support\Carbon::parse($createdAt) : null;
    } catch (\Throwable $e) {
        $postPublishedMoment = null;
    }
    try {
        $postUpdatedMoment = !empty(optional($postObj)->updated_at ?? $postArr['updated_at'] ?? null)
            ? \Illuminate\Support\Carbon::parse(optional($postObj)->updated_at ?? $postArr['updated_at'])
            : null;
    } catch (\Throwable $e) {
        $postUpdatedMoment = null;
    }
    $isPostEdited = $postUpdatedMoment && $postPublishedMoment && $postUpdatedMoment->gt($postPublishedMoment);
    $postEditedByName = $authorName !== '' ? $authorName : __('site.post.community_author');
    $postEditedAtLabel = $isPostEdited ? $postUpdatedMoment->format('d.m.Y H:i') : null;

    $bookmarkAction = $viewer && $postSlug !== '' ? route('blog.post.bookmark', $postSlug) : null;
    $bookmarkOutlineIcon = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m10.94 18.339l-3.43 2.548a1.71 1.71 0 0 1-2.76-1.23V6.35a3.735 3.735 0 0 1 3.87-3.597h6.76a3.742 3.742 0 0 1 3.87 3.597v13.309a1.708 1.708 0 0 1-2.76 1.229l-3.43-2.548a1.801 1.801 0 0 0-2.12 0"/>
</svg>
SVG;
    $bookmarkFilledIcon = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" fill-rule="evenodd" d="M7 2a3 3 0 0 0-3 3v15.138a1.5 1.5 0 0 0 2.244 1.303l5.26-3.006a1 1 0 0 1 .992 0l5.26 3.006A1.5 1.5 0 0 0 20 20.138V5a3 3 0 0 0-3-3H7z" clip-rule="evenodd"/>
</svg>
SVG;
    $reactionAction = $postSlug !== '' ? route('blog.post.reaction', ['post' => $postSlug]) : null;
    $editUrl = $postSlug !== '' ? route('blog.post.edit', $postSlug) : '#';
    $deleteAction = $postSlug !== '' ? route('blog.post.destroy', $postSlug) : '#';
    $pinAction = $postSlug !== '' ? route('blog.post.pin', $postSlug) : '#';
    $reportUrl = $viewer && $postAuthor && !$viewer->is($postAuthor)
        ? route('users.report.form', $postAuthor)
        : null;

    $isOwnPost = $viewer && $postObj && (int) $viewer->id === (int) $postObj->author_id;
    $canOpenMenu = $viewer && ($isOwnPost || $reportUrl);
    $shareButtonId = $postSlug !== '' ? 'share-' . $postSlug : 'share-' . uniqid();
    $reactionRootId = $postSlug !== '' ? 'post-card-rx-' . $postSlug : 'post-card-rx-' . uniqid();
    $inlineExpandId = $postSlug !== '' ? 'post-card-inline-' . $postSlug : 'post-card-inline-' . uniqid();
    $summaryToggleId = $postSlug !== '' ? 'post-card-summary-' . $postSlug : 'post-card-summary-' . uniqid();
    $menuSheetId = $postSlug !== '' ? 'post-card-menu-sheet-' . $postSlug : 'post-card-menu-sheet-' . uniqid();
@endphp

<article class="alma-post-card site-card">
    <div class="alma-post-card__header">
        <div class="alma-post-card__identity alma-post-card__identity--reference">
            @if($authorAvatar)
                <span class="alma-post-card__avatar-link alma-post-card__avatar-link--reference">
                    <a href="{{ $authorUrl }}" class="alma-post-card__avatar-anchor--reference" aria-label="{{ $authorName }}">
                        <img
                            src="{{ $authorAvatar }}"
                            alt="{{ $authorName }}"
                            class="alma-post-card__avatar alma-post-card__avatar--reference"
                            width="48"
                            height="48"
                            loading="lazy"
                            decoding="async"
                        />
                    </a>
                    @if($hasCategory && $categoryUrl)
                        <a href="{{ $categoryUrl }}" class="alma-post-card__avatar-badge alma-post-card__avatar-badge--reference" aria-label="{{ $categoryName }}">
                            @if($categoryAvatar)
                                <img
                                    src="{{ $categoryAvatar }}"
                                    alt="{{ $categoryName }}"
                                    class="alma-post-card__avatar-badge-image alma-post-card__avatar-badge-image--reference"
                                    width="64"
                                    height="64"
                                    loading="lazy"
                                    decoding="async"
                                />
                            @else
                                <span class="alma-post-card__avatar-badge-text alma-post-card__avatar-badge-text--reference">{{ $categoryBadgeText }}</span>
                            @endif
                        </a>
                    @elseif($hasCategory)
                        <span class="alma-post-card__avatar-badge alma-post-card__avatar-badge--reference" aria-label="{{ $categoryName }}">
                            @if($categoryAvatar)
                                <img
                                    src="{{ $categoryAvatar }}"
                                    alt="{{ $categoryName }}"
                                    class="alma-post-card__avatar-badge-image alma-post-card__avatar-badge-image--reference"
                                    width="64"
                                    height="64"
                                    loading="lazy"
                                    decoding="async"
                                />
                            @else
                                <span class="alma-post-card__avatar-badge-text alma-post-card__avatar-badge-text--reference">{{ $categoryBadgeText }}</span>
                            @endif
                        </span>
                    @endif
                </span>
            @else
                <span class="alma-post-card__avatar-link alma-post-card__avatar-link--reference">
                    <a href="{{ $authorUrl }}" class="alma-post-card__avatar alma-post-card__avatar--fallback alma-post-card__avatar--reference alma-post-card__avatar-anchor--reference" aria-label="{{ $authorName }}">
                        {{ $authorInitials }}
                    </a>
                    @if($hasCategory && $categoryUrl)
                        <a href="{{ $categoryUrl }}" class="alma-post-card__avatar-badge alma-post-card__avatar-badge--reference" aria-label="{{ $categoryName }}">
                            @if($categoryAvatar)
                                <img
                                    src="{{ $categoryAvatar }}"
                                    alt="{{ $categoryName }}"
                                    class="alma-post-card__avatar-badge-image alma-post-card__avatar-badge-image--reference"
                                    width="64"
                                    height="64"
                                    loading="lazy"
                                    decoding="async"
                                />
                            @else
                                <span class="alma-post-card__avatar-badge-text alma-post-card__avatar-badge-text--reference">{{ $categoryBadgeText }}</span>
                            @endif
                        </a>
                    @elseif($hasCategory)
                        <span class="alma-post-card__avatar-badge alma-post-card__avatar-badge--reference" aria-label="{{ $categoryName }}">
                            @if($categoryAvatar)
                                <img
                                    src="{{ $categoryAvatar }}"
                                    alt="{{ $categoryName }}"
                                    class="alma-post-card__avatar-badge-image alma-post-card__avatar-badge-image--reference"
                                    width="64"
                                    height="64"
                                    loading="lazy"
                                    decoding="async"
                                />
                            @else
                                <span class="alma-post-card__avatar-badge-text alma-post-card__avatar-badge-text--reference">{{ $categoryBadgeText }}</span>
                            @endif
                        </span>
                    @endif
                </span>
            @endif

            <div class="alma-post-card__meta alma-post-card__meta--reference">
                <div class="alma-post-card__author-row alma-post-card__author-row--reference">
                    <a href="{{ $authorUrl }}" class="alma-post-card__author alma-post-card__author--reference">{{ $authorName }}</a>
                    @if($showVerified)
                        <x-verification-badge :user="$postAuthor" class="alma-post-card__verified alma-post-card__verified--reference" size="sm" />
                    @endif
                </div>

                <div class="alma-post-card__submeta alma-post-card__submeta--reference">
                    @if($hasCategory && $categoryUrl)
                        <a href="{{ $categoryUrl }}" class="alma-post-card__category alma-post-card__category--reference">{{ $categoryName }}</a>
                    @elseif($hasCategory)
                        <span class="alma-post-card__category alma-post-card__category--reference">{{ $categoryName }}</span>
                    @endif

                    @if($hasCategory)
                        <span class="alma-post-card__dot alma-post-card__dot--reference"></span>
                    @endif

                    @if($createdIso !== '')
                        <time datetime="{{ $createdIso }}" class="alma-post-card__time--reference">{{ $createdHuman }}</time>
                    @else
                        <span class="alma-post-card__time--reference">{{ $createdHuman }}</span>
                    @endif

                    @if($isPostEdited)
                        <details class="alma-post-card__edit-indicator">
                            <summary class="alma-post-card__edit-trigger" aria-label="Duzenleme bilgisi" title="Duzenleme bilgisi">
                                <iconify-icon icon="lucide:pencil"></iconify-icon>
                            </summary>
                            <div class="alma-post-card__edit-popover">
                                <span class="alma-post-card__edit-popover-title">Gonderi duzenlendi</span>
                                <span class="alma-post-card__edit-popover-text">Saat: {{ $postEditedAtLabel }}</span>
                                <span class="alma-post-card__edit-popover-text">Duzenleyen: {{ $postEditedByName !== '' ? $postEditedByName : 'Bilinmiyor' }}</span>
                            </div>
                        </details>
                    @endif

                    @if($isPinned)
                        <span class="alma-post-card__dot alma-post-card__dot--reference"></span>
                        <span class="alma-post-card__pinned">
                            <iconify-icon icon="lucide:pin"></iconify-icon>
                            <span>{{ __('site.post.pinned') }}</span>
                        </span>
                    @endif

                    @if($isNsfw)
                        <span class="alma-post-card__dot alma-post-card__dot--reference"></span>
                        <span class="alma-post-card__pinned">
                            <iconify-icon icon="lucide:shield-alert"></iconify-icon>
                            <span>NSFW</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="alma-post-card__header-actions">
            @if($hasSummary)
                <div class="alma-post-card__summary-ai-wrap">
                    <button
                        type="button"
                        class="alma-post-card__header-pill alma-post-card__summary-ai-btn"
                        data-post-summary-toggle="{{ $summaryToggleId }}"
                        aria-controls="{{ $summaryToggleId }}"
                        aria-expanded="false"
                        aria-label="&Ouml;zetle"
                    >
                        <span class="alma-post-card__summary-ai-btn-inner" aria-hidden="true"></span>
                        <span class="alma-post-card__summary-ai-chip" aria-hidden="true">
                            <svg class="alma-post-card__summary-ai-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 3.6L13.86 8.14L18.4 10L13.86 11.86L12 16.4L10.14 11.86L5.6 10L10.14 8.14L12 3.6Z" />
                                <path d="M18.1 4.9L18.65 6.15L19.9 6.7L18.65 7.25L18.1 8.5L17.55 7.25L16.3 6.7L17.55 6.15L18.1 4.9Z" />
                                <path d="M6.2 14.8L6.7 15.95L7.85 16.45L6.7 16.95L6.2 18.1L5.7 16.95L4.55 16.45L5.7 15.95L6.2 14.8Z" />
                            </svg>
                        </span>
                        <span data-post-summary-label>Ã–zetle</span>
                        <span class="alma-post-card__summary-ai-copy">
                            <span class="alma-post-card__summary-ai-label" data-post-summary-visible-label>&Ouml;zetle</span>
                        </span>
                        <span class="alma-post-card__summary-ai-chevron" aria-hidden="true">
                            <iconify-icon icon="lucide:chevron-down" data-post-summary-chevron></iconify-icon>
                        </span>
                    </button>
                </div>
            @endif

            @if($canOpenMenu)
                <button type="button" class="alma-post-card__menu-trigger sm:hidden" aria-label="{{ __('site.post.more_actions') }}" aria-expanded="false" data-post-mobile-menu-open="{{ $menuSheetId }}">
                    <iconify-icon icon="lucide:ellipsis"></iconify-icon>
                </button>

                <details class="alma-post-card__menu hidden sm:block">
                    <summary class="alma-post-card__menu-trigger" aria-label="{{ __('site.post.more_actions') }}">
                        <iconify-icon icon="lucide:ellipsis"></iconify-icon>
                    </summary>

                    <div class="alma-post-card__menu-panel">
                        @if($isOwnPost)
                            <a href="{{ $editUrl }}" class="alma-post-card__menu-item">
                                <iconify-icon icon="lucide:square-pen"></iconify-icon>
                                <span>{{ __('site.post.edit') }}</span>
                            </a>

                            <form method="POST" action="{{ $pinAction }}" class="alma-post-card__menu-form">
                                @csrf
                                <button type="submit" class="alma-post-card__menu-item alma-post-card__menu-item--button">
                                    <iconify-icon icon="{{ $isPinned ? 'lucide:pin-off' : 'lucide:pin' }}"></iconify-icon>
                                    <span>{{ $isPinned ? __('site.post.unpin') : __('site.post.pin') }}</span>
                                </button>
                            </form>

                            <form method="POST" action="{{ $deleteAction }}" class="alma-post-card__menu-form" onsubmit="return confirm('{{ __('site.post.delete_confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="alma-post-card__menu-item alma-post-card__menu-item--button alma-post-card__menu-item--danger">
                                    <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                    <span>{{ __('site.post.delete') }}</span>
                                </button>
                            </form>
                        @elseif($reportUrl)
                            <a href="{{ $reportUrl }}" class="alma-post-card__menu-item">
                                <iconify-icon icon="lucide:flag"></iconify-icon>
                                <span>{{ __('site.post.report') }}</span>
                            </a>
                        @endif
                    </div>
                </details>

                <div id="{{ $menuSheetId }}" class="pointer-events-none fixed inset-0 z-[91] hidden sm:hidden" aria-hidden="true" data-post-mobile-menu-sheet>
                    <div class="absolute inset-0 bg-slate-900/50 opacity-0 transition duration-300" data-post-mobile-menu-close></div>
                    <div class="absolute inset-x-0 bottom-0 translate-y-full rounded-t-[28px] bg-white p-4 shadow-[0_-24px_48px_-24px_rgba(15,23,42,0.45)] transition duration-300 ease-out" data-post-mobile-menu-panel>
                        <div class="mx-auto mb-4 h-1.5 w-14 rounded-full bg-slate-200"></div>
                        @if($isOwnPost)
                            <a href="{{ $editUrl }}" class="alma-post-card__sheet-action">
                                <iconify-icon icon="lucide:square-pen"></iconify-icon>
                                <span>{{ __('site.post.edit') }}</span>
                            </a>
                            <form method="POST" action="{{ $pinAction }}" class="mt-1">
                                @csrf
                                <button type="submit" class="alma-post-card__sheet-action alma-post-card__sheet-action--button">
                                    <iconify-icon icon="{{ $isPinned ? 'lucide:pin-off' : 'lucide:pin' }}"></iconify-icon>
                                    <span>{{ $isPinned ? __('site.post.unpin') : __('site.post.pin') }}</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ $deleteAction }}" class="mt-1" onsubmit="return confirm('{{ __('site.post.delete_confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="alma-post-card__sheet-action alma-post-card__sheet-action--button alma-post-card__sheet-action--danger">
                                    <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                    <span>{{ __('site.post.delete') }}</span>
                                </button>
                            </form>
                        @elseif($reportUrl)
                            <a href="{{ $reportUrl }}" class="alma-post-card__sheet-action">
                                <iconify-icon icon="lucide:flag"></iconify-icon>
                                <span>{{ __('site.post.report') }}</span>
                            </a>
                        @endif
                        <button type="button" class="alma-post-card__sheet-close" data-post-mobile-menu-close>
                            {{ __('post_create.close') }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="alma-post-card__content">
        @if($hasSummary)
            <div id="{{ $summaryToggleId }}" class="alma-post-card__summary-panel hidden" data-post-summary-panel>
                <div class="alma-post-card__summary-kicker">
                    <iconify-icon icon="lucide:list-filter"></iconify-icon>
                    <span>K&#305;saca</span>
                </div>
                <p class="alma-post-card__summary-text">{{ $summaryText }}</p>
            </div>
        @endif

        <h2 class="alma-post-card__title {{ $isHero ? 'is-hero' : '' }}">
            @if($postUrl !== '#')
                <a href="{{ $postUrl }}">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
        </h2>

        @if($resolvedExcerptShort !== '')
            <p class="alma-post-card__excerpt">{{ $resolvedExcerptShort }}</p>
        @endif

        @if($linkPreview)
            <div class="mt-4">
                @include('blog.partials.link-preview', ['preview' => $linkPreview])
            </div>
        @endif

        @if($showVideoPreview)
            <a href="{{ $postUrl }}" class="alma-post-card__media-link alma-post-card__media-link--video" aria-label="{{ $title }}">
                @if($socialPreviewImage)
                    @if($shouldRenderMediaBlur)
                        <span class="alma-post-card__media-blur" aria-hidden="true" style="background-image: url('{{ e($socialPreviewImage) }}');"></span>
                    @endif
                    <img
                        src="{{ $socialPreviewImage }}"
                        alt="{{ $title }}"
                        class="alma-post-card__image alma-post-card__image--video"
                        width="480"
                        height="360"
                        loading="{{ $mediaLoading }}"
                        fetchpriority="{{ $mediaFetchPriority }}"
                        decoding="async"
                    />
                @else
                    <span class="alma-post-card__video-fallback" aria-hidden="true">
                        <span class="alma-post-card__video-fallback-provider">{{ $socialProviderMeta['label'] }}</span>
                        <span class="alma-post-card__video-fallback-title">{{ $title }}</span>
                    </span>
                @endif
                <span class="alma-post-card__video-play" aria-hidden="true">
                    <span class="alma-post-card__video-play-icon"></span>
                </span>
                <span class="alma-post-card__video-pill">
                    <span class="alma-post-card__video-pill-icon" aria-hidden="true"></span>
                    <span>{{ $socialProviderMeta['cta'] }}</span>
                </span>
            </a>
        @elseif($displayImage)
            <a href="{{ $postUrl }}" class="alma-post-card__media-link {{ $featuredImage ? 'alma-post-card__media-link--featured' : '' }}" aria-label="{{ $title }}">
                @if($shouldRenderMediaBlur)
                    <span class="alma-post-card__media-blur" aria-hidden="true" style="background-image: url('{{ e($displayImage) }}');"></span>
                @endif
                <img
                    src="{{ $displayImage }}"
                    alt="{{ $title }}"
                    class="alma-post-card__image"
                    width="{{ $displayImageWidth }}"
                    height="{{ $displayImageHeight }}"
                    loading="{{ $mediaLoading }}"
                    fetchpriority="{{ $mediaFetchPriority }}"
                    decoding="async"
                />
            </a>
        @elseif($hasContentGallery)
            <div class="alma-post-card__media-slider {{ $contentGalleryImages->count() === 1 ? 'is-single' : '' }}" aria-label="{{ $title }}">
                @foreach($contentGalleryImages as $galleryImage)
                    <a href="{{ $postUrl }}" class="alma-post-card__media-slide" aria-label="{{ $title }} {{ $loop->iteration }}">
                        @if(!$isHero || !$loop->first)
                            <span class="alma-post-card__media-blur" aria-hidden="true" style="background-image: url('{{ e($galleryImage['src']) }}');"></span>
                        @endif
                        <img
                            src="{{ $galleryImage['src'] }}"
                            alt="{{ $title }}"
                            class="alma-post-card__media-slide-image"
                            width="{{ $galleryImage['width'] }}"
                            height="{{ $galleryImage['height'] }}"
                            loading="{{ $loop->first && $isHero ? 'eager' : 'lazy' }}"
                            fetchpriority="{{ $loop->first && $isHero ? 'high' : 'auto' }}"
                            decoding="async"
                        />
                    </a>
                @endforeach
            </div>
        @endif

        @if($hasInlineContinue)
            <div class="alma-post-card__inline-expand">
                <button
                    type="button"
                    class="alma-post-card__inline-toggle"
                    data-post-inline-toggle="{{ $inlineExpandId }}"
                    aria-controls="{{ $inlineExpandId }}"
                    aria-expanded="false"
                >
                    <span data-post-inline-label>Tamam&#305;n&#305; g&#246;ster</span>
                    <iconify-icon icon="lucide:chevron-down" data-post-inline-icon></iconify-icon>
                </button>

                <div id="{{ $inlineExpandId }}" class="alma-post-card__inline-more hidden" data-post-inline-more>
                    <p class="alma-post-card__inline-text">{{ $resolvedExcerptExpanded }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="alma-post-card__footer">
        <div
            class="alma-post-card__reactions"
            data-post-card-reaction-root="{{ $reactionRootId }}"
            @if($reactionAction) data-reaction-post-url="{{ $reactionAction }}" @endif
        >
            @foreach($visibleReactionPills as $reaction)
                @php
                    $reactionLabel = (string) ($reaction['label'] ?? 'Tepki');
                    $reactionShortCode = (string) ($reaction['short_code'] ?? '');
                    $reactionTypeId = $reaction['type_id'] ?? null;
                    $reactionIconHtml = $renderReactionIcon($reaction['icon'] ?? null, $reactionLabel);
                @endphp
                @if($reactionAction && $viewer)
                    <form method="POST" action="{{ $reactionAction }}" class="m-0">
                        @csrf
                        @if($reactionTypeId)
                            <input type="hidden" name="reaction_type_id" value="{{ $reactionTypeId }}">
                        @endif
                        @if($reactionShortCode !== '')
                            <input type="hidden" name="short_code" value="{{ $reactionShortCode }}">
                        @endif
                        <button type="submit" class="alma-post-card__reaction-pill" aria-label="{{ $reactionLabel }}">
                            <span class="alma-post-card__reaction-pill-icon">
                                {!! $reactionIconHtml !!}
                                <span class="alma-post-card__reaction-pill-count">{{ number_format((int) ($reaction['count'] ?? 0)) }}</span>
                            </span>
                        </button>
                    </form>
                @elseif(\Illuminate\Support\Facades\Route::has('login'))
                    <a href="{{ route('login') }}" class="alma-post-card__reaction-pill" aria-label="{{ $reactionLabel }}">
                        <span class="alma-post-card__reaction-pill-icon">
                            {!! $reactionIconHtml !!}
                            <span class="alma-post-card__reaction-pill-count">{{ number_format((int) ($reaction['count'] ?? 0)) }}</span>
                        </span>
                    </a>
                @else
                    <span class="alma-post-card__reaction-pill">
                        <span class="alma-post-card__reaction-pill-icon">
                            {!! $reactionIconHtml !!}
                            <span class="alma-post-card__reaction-pill-count">{{ number_format((int) ($reaction['count'] ?? 0)) }}</span>
                        </span>
                    </span>
                @endif
            @endforeach

            @if($reactionOverflowCount > 0)
                <span class="alma-post-card__reaction-more">+{{ $reactionOverflowCount }}</span>
            @endif

            @include('blog.reaction', [
                'count' => '',
                'icon' => <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M12 12Zm.003 9q-1.866 0-3.51-.708q-1.643-.709-2.859-1.924q-1.216-1.214-1.925-2.856Q3 13.87 3 12.003q0-1.866.709-3.51t1.924-2.859Q6.848 4.418 8.49 3.709Q10.133 3 12 3q.998 0 1.94.203q.943.203 1.79.603V4.92q-.836-.442-1.774-.682Q13.02 4 12 4Q8.675 4 6.337 6.337T4 12q0 3.325 2.337 5.663T12 20q3.325 0 5.663-2.337T20 12q0-.723-.124-1.406q-.124-.682-.36-1.325h1.072q.206.648.31 1.323Q21 11.268 21 12q0 1.868-.708 3.51q-.709 1.643-1.924 2.857q-1.214 1.215-2.856 1.924Q13.87 21 12.003 21ZM20.5 4.5H19q-.213 0-.356-.144q-.144-.144-.144-.357t.144-.356Q18.788 3.5 19 3.5h1.5V2q0-.213.144-.356q.144-.144.357-.144t.356.144q.143.144.143.356v1.5H23q.213 0 .356.144q.144.144.144.357t-.144.356Q23.213 4.5 23 4.5h-1.5V6q0 .213-.144.356q-.144.144-.357.144t-.356-.144Q20.5 6.213 20.5 6V4.5Zm-5.188 6.115q.467 0 .789-.326q.322-.327.322-.794t-.327-.788q-.326-.322-.793-.322t-.789.326q-.322.327-.322.794t.327.788q.327.322.793.322Zm-6.615 0q.466 0 .789-.326q.322-.327.322-.794t-.327-.788q-.327-.322-.793-.322t-.789.326q-.322.327-.322.794t.327.788q.326.322.793.322ZM12 16.885q1.243 0 2.292-.575q1.05-.575 1.72-1.56q.134-.246.007-.498T15.6 14H8.4q-.292 0-.42.252q-.126.252.008.498q.67.985 1.73 1.56q1.06.575 2.282.575Z"/>
</svg>
SVG,
                'label' => null,
                'gifs' => $reactionTypesAll,
                'isAdd' => true,
                'triggerClass' => 'alma-post-card__reaction-picker',
            ])
        </div>

        <div class="alma-post-card__engagement">
            <div class="alma-post-card__engagement-main">
                <a href="{{ $commentsUrl }}" class="alma-post-card__metric-button" aria-label="{{ __('site.post.comments_count', ['count' => number_format($commentsCount)]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" fill-rule="evenodd" d="M2.5 12.096a9.5 9.5 0 1 1 9.5 9.5H3.25a.75.75 0 0 1-.53-1.28l2.053-2.054A9.47 9.47 0 0 1 2.5 12.096m9.5-8a8 8 0 0 0-5.657 13.657a.75.75 0 0 1 0 1.06l-1.282 1.283H12a8 8 0 1 0 0-16" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $commentsCountDisplay }}</span>
                </a>

                @if($bookmarkAction)
                    <form method="POST" action="{{ $bookmarkAction }}" class="alma-post-card__icon-form">
                        @csrf
                        <button type="submit" class="alma-post-card__metric-button {{ $isBookmarked ? 'is-bookmarked' : '' }}" aria-label="{{ __('site.common.bookmarks') }}">
                            {!! $isBookmarked ? $bookmarkFilledIcon : $bookmarkOutlineIcon !!}
                            @if($bookmarksCount > 0)
                                <span>{{ $bookmarksCountDisplay }}</span>
                            @endif
                        </button>
                    </form>
                @elseif(\Illuminate\Support\Facades\Route::has('login'))
                    <a href="{{ route('login') }}" class="alma-post-card__metric-button {{ $isBookmarked ? 'is-bookmarked' : '' }}" aria-label="{{ __('site.common.bookmarks') }}">
                        {!! $bookmarkOutlineIcon !!}
                        @if($bookmarksCount > 0)
                            <span>{{ $bookmarksCountDisplay }}</span>
                        @endif
                    </a>
                @endif

                <button
                    type="button"
                    class="alma-post-card__metric-button"
                    data-post-share
                    data-post-share-id="{{ $shareButtonId }}"
                    data-post-url="{{ $postUrl }}"
                    data-post-title="{{ $title }}"
                    aria-label="{{ __('site.post.share') }}"
                >
                    <svg viewBox="0 0 256 256" width="1.2em" height="1.2em" aria-hidden="true">
                        <path fill="currentColor" d="m229.66 109.66l-48 48a8 8 0 0 1-11.32-11.32L204.69 112H128a88.1 88.1 0 0 0-88 88a8 8 0 0 1-16 0A104.11 104.11 0 0 1 128 96h76.69l-34.35-34.34a8 8 0 0 1 11.32-11.32l48 48a8 8 0 0 1 0 11.32"/>
                    </svg>
                    <span class="sr-only">{{ __('site.post.share') }}</span>
                </button>
            </div>

            <div class="alma-post-card__views" aria-label="{{ __('site.post.views_count', ['count' => number_format($viewsCount)]) }}">
                <iconify-icon icon="ph:eye"></iconify-icon>
                <span>{{ $viewsCountDisplay }}</span>
            </div>
        </div>

        @if($isCommentsDisabled)
            <div class="alma-post-card__comments-strip opacity-70" aria-disabled="true">
                <div class="alma-post-card__comments-meta">
                    <div class="alma-post-card__comment-avatars">
                        <span class="alma-post-card__comment-avatar alma-post-card__comment-avatar--fallback">
                            <iconify-icon icon="lucide:message-square-off"></iconify-icon>
                        </span>
                    </div>

                    <span class="alma-post-card__comments-count">Yorumlar kapal&#305;</span>
                </div>

                <iconify-icon icon="lucide:lock" class="alma-post-card__comments-chevron"></iconify-icon>
            </div>
                @else
                    <a href="{{ $commentsUrl }}" class="alma-post-card__comments-strip">
                        <div class="alma-post-card__comments-meta">
                        @php
                            $previewInitial = collect(preg_split('/\s+/', trim((string) ($latestCommentPreview['name'] ?? 'Topluluk')), -1, PREG_SPLIT_NO_EMPTY))
                                ->take(2)
                                ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                                ->implode('');
                            $previewInitial = $previewInitial !== '' ? $previewInitial : 'TU';
                        @endphp

                        @if($latestCommentAvatar)
                            <img src="{{ $latestCommentAvatar }}" alt="Yorum" class="alma-post-card__comment-avatar" width="36" height="36" loading="lazy" decoding="async" />
                        @else
                            <span class="alma-post-card__comment-avatar alma-post-card__comment-avatar--fallback">{{ $previewInitial }}</span>
                        @endif

                        <div class="alma-post-card__comment-preview">
                            <span class="alma-post-card__comments-count">{{ $latestCommentContent }}</span>
                        </div>
                    </div>
            </a>
        @endif
    </div>
</article>

@once
    <style>
        @keyframes almaSummaryChipDrift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .alma-post-card__menu-trigger {
            background: #ffffff !important;
            color: #475569 !important;
            border: 1px solid rgba(226, 232, 240, 0.95) !important;
            box-shadow: none !important;
            transition: background-color .16s ease, border-color .16s ease, color .16s ease !important;
        }

        .alma-post-card__menu-trigger:hover,
        .alma-post-card__menu-trigger:focus,
        .alma-post-card__menu-trigger:focus-visible {
            background: #ffffff !important;
            color: #475569 !important;
            border-color: rgba(226, 232, 240, 0.95) !important;
            box-shadow: none !important;
        }

        .alma-post-card__menu-trigger:active,
        .alma-post-card__menu-trigger.is-active,
        .alma-post-card__menu[open] .alma-post-card__menu-trigger {
            background: #f3f4f6 !important;
            color: #111827 !important;
            border-color: rgba(203, 213, 225, 0.95) !important;
        }

        .alma-post-card__menu-item {
            background: #ffffff !important;
            border: 1px solid rgba(226, 232, 240, 0.92) !important;
            color: #334155 !important;
        }

        .alma-post-card__menu-item iconify-icon {
            color: #64748b !important;
        }

        .alma-post-card__menu-item:hover,
        .alma-post-card__menu-item:focus,
        .alma-post-card__menu-item:focus-visible {
            background: #ffffff !important;
        }

        .alma-post-card__menu-item:active {
            background: #f3f4f6 !important;
        }

        .alma-post-card__menu-item--danger,
        .alma-post-card__sheet-action--danger {
            color: #b91c1c !important;
        }

        .alma-post-card__menu-item--danger iconify-icon,
        .alma-post-card__sheet-action--danger iconify-icon {
            color: #b91c1c !important;
        }

        .alma-post-card__sheet-action,
        .alma-post-card__sheet-close {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: #ffffff;
            padding: 0.82rem 1rem;
            font-size: 0.92rem;
            line-height: 1.35;
            color: #334155;
            text-align: left;
            transition: background-color .16s ease, border-color .16s ease, color .16s ease;
        }

        .alma-post-card__sheet-action--button {
            cursor: pointer;
        }

        .alma-post-card__sheet-action:hover,
        .alma-post-card__sheet-action:focus,
        .alma-post-card__sheet-action:focus-visible,
        .alma-post-card__sheet-close:hover,
        .alma-post-card__sheet-close:focus,
        .alma-post-card__sheet-close:focus-visible {
            background: #ffffff;
            color: #334155;
            outline: none;
        }

        .alma-post-card__sheet-action:active,
        .alma-post-card__sheet-close:active {
            background: #f3f4f6;
            border-color: rgba(203, 213, 225, 0.95);
        }

        .alma-post-card__sheet-action iconify-icon {
            font-size: 1rem;
            color: #64748b;
            flex-shrink: 0;
        }

        .alma-post-card__sheet-close {
            justify-content: center;
            margin-top: 0.75rem;
            font-weight: 600;
        }

        .alma-post-card__summary-ai-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            background: #d1d5db !important;
            background-size: auto;
            animation: none;
        }

        .alma-post-card__summary-ai-chip::before,
        .alma-post-card__summary-ai-chip::after {
            display: none !important;
        }

        .alma-post-card__summary-ai-btn,
        .alma-post-card__summary-ai-btn:hover,
        .alma-post-card__summary-ai-btn:focus,
        .alma-post-card__summary-ai-btn:focus-visible,
        .alma-post-card__summary-ai-btn:active {
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            background: #f3f4f6 !important;
            color: #374151 !important;
        }

        .alma-post-card__summary-ai-icon {
            color: #fff !important;
            fill: currentColor;
        }

        .alma-post-card__summary-ai-label,
        .alma-post-card__summary-ai-copy,
        [data-post-summary-label] {
            font-weight: 400 !important;
        }

        .alma-post-card__edit-indicator {
            position: relative;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .alma-post-card__edit-trigger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.12rem;
            height: 1.12rem;
            border-radius: 999px;
            color: #7a7a7a;
            cursor: pointer;
            transition: background-color .2s ease, color .2s ease;
        }

        .alma-post-card__edit-trigger::-webkit-details-marker {
            display: none;
        }

        .alma-post-card__edit-trigger iconify-icon {
            font-size: 0.86rem;
        }

        .alma-post-card__edit-trigger:hover,
        .alma-post-card__edit-indicator[open] .alma-post-card__edit-trigger {
            color: #111827;
            background: #f3f4f6;
        }

        .alma-post-card__edit-popover {
            position: absolute;
            top: calc(100% + 0.45rem);
            left: 50%;
            z-index: 20;
            min-width: 13.5rem;
            transform: translateX(-50%);
            border: 1px solid rgba(203, 213, 225, 0.95);
            border-radius: 0.8rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
            padding: 0.7rem 0.8rem;
            color: #334155;
        }

        .alma-post-card__edit-popover-title {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #0f172a;
        }

        .alma-post-card__edit-popover-text {
            display: block;
            margin-top: 0.2rem;
            font-size: 0.75rem;
            line-height: 1.45;
            color: #475569;
        }

        .alma-post-card__media-link,
        .alma-post-card__media-slide {
            position: relative;
            background: #e5e7eb;
        }

        .alma-post-card__media-blur {
            position: absolute;
            inset: 0;
            z-index: 0;
            display: block;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            filter: blur(26px) saturate(1.08);
            opacity: 0.78;
            transform: scale(1.08);
        }

        .alma-post-card__media-link--featured .alma-post-card__image {
            height: auto;
            aspect-ratio: auto;
            object-fit: contain;
            transform: none !important;
        }

        .alma-post-card__media-link--featured:hover .alma-post-card__image {
            transform: none !important;
        }

        .alma-post-card__media-link--video {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: clamp(220px, 38vw, 360px);
        }

        .alma-post-card__media-link--video .alma-post-card__image {
            width: 100%;
            max-width: none;
            margin-inline: 0;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            background: transparent;
        }

        .alma-post-card__video-fallback {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: clamp(220px, 38vw, 360px);
            width: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            border-radius: 16px;
            padding: 1.8rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 54%, #334155 100%);
            color: #f8fafc;
            text-align: center;
        }

        .alma-post-card__video-fallback-provider {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .alma-post-card__video-fallback-title {
            display: block;
            max-width: 32rem;
            font-size: clamp(1rem, 1.9vw, 1.3rem);
            font-weight: 700;
            line-height: 1.4;
        }

        .alma-post-card__video-play,
        .alma-post-card__video-pill {
            position: absolute;
            z-index: 2;
        }

        .alma-post-card__video-play {
            top: 50%;
            left: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 84px;
            height: 58px;
            border-radius: 18px;
            transform: translate(-50%, -50%);
            background: #ef4444;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.24);
        }

        .alma-post-card__video-play-icon {
            display: block;
            width: 0;
            height: 0;
            margin-left: 5px;
            border-top: 12px solid transparent;
            border-bottom: 12px solid transparent;
            border-left: 18px solid #fff;
        }

        .alma-post-card__video-pill {
            right: 18px;
            bottom: 18px;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            max-width: calc(100% - 36px);
            min-height: 2.55rem;
            padding: 0.55rem 0.95rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.78);
            color: #f8fafc;
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.15;
            backdrop-filter: blur(10px);
        }

        .alma-post-card__video-pill-icon {
            position: relative;
            flex: 0 0 1.2rem;
            width: 1.2rem;
            height: 1.2rem;
            border-radius: 999px;
            background: #ef4444;
        }

        .alma-post-card__video-pill-icon::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            transform: translate(-40%, -50%);
            border-top: 4px solid transparent;
            border-bottom: 4px solid transparent;
            border-left: 7px solid #fff;
        }

        .alma-post-card__media-slider {
            display: flex;
            gap: 0.55rem;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            scroll-snap-type: x mandatory;
            padding-bottom: 0.2rem;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .alma-post-card__media-slider::-webkit-scrollbar {
            display: none;
        }

        .alma-post-card__media-slide {
            flex: 0 0 clamp(14rem, 76%, 28rem);
            display: block;
            overflow: hidden;
            border-radius: 16px;
            scroll-snap-align: start;
        }

        .alma-post-card__media-slider.is-single .alma-post-card__media-slide {
            flex-basis: 100%;
        }

        .alma-post-card__media-slide-image {
            width: 100%;
            aspect-ratio: 5 / 4;
            object-fit: cover;
            border-radius: 16px;
            background: transparent;
        }

        .alma-post-card__image,
        .alma-post-card__media-slide-image {
            position: relative;
            z-index: 1;
        }

        @media (max-width: 640px) {
            .alma-post-card__video-play {
                width: 72px;
                height: 50px;
                border-radius: 16px;
            }

            .alma-post-card__video-play-icon {
                margin-left: 4px;
                border-top-width: 10px;
                border-bottom-width: 10px;
                border-left-width: 16px;
            }

            .alma-post-card__video-pill {
                right: 14px;
                bottom: 14px;
                max-width: calc(100% - 28px);
                padding: 0.5rem 0.8rem;
                font-size: 0.78rem;
            }
        }

        .alma-post-card__engagement {
            align-items: center;
        }

        .alma-post-card__engagement-main {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .alma-post-card__icon-form {
            margin: 0;
            display: inline-flex;
            align-items: center;
        }

        .alma-post-card__metric-button,
        .alma-post-card__views {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            min-height: 2rem;
            line-height: 1;
            vertical-align: middle;
        }

        .alma-post-card__metric-button svg,
        .alma-post-card__metric-button iconify-icon,
        .alma-post-card__views svg,
        .alma-post-card__views iconify-icon {
            width: 1.22rem;
            height: 1.22rem;
            flex: 0 0 1.22rem;
            display: block;
        }

        .alma-post-card__metric-button > span,
        .alma-post-card__views > span {
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .alma-post-card__views {
            padding-inline: 0.7rem;
            border-radius: 9999px;
            background: transparent;
        }

        @media (max-width: 640px) {
            .alma-post-card__media-slider {
                gap: 0.5rem;
            }

            .alma-post-card__media-slide {
                flex-basis: calc(100% - 4rem);
            }

            .alma-post-card__media-slider.is-single .alma-post-card__media-slide {
                flex-basis: 100%;
            }
        }
    </style>
    <script>
        document.addEventListener('reaction:selected', function (event) {
            const target = event.target;
            if (!(target instanceof Element)) {
                return;
            }

            const reactionRoot = target.closest('[data-post-card-reaction-root]');
            if (!reactionRoot) {
                return;
            }

            const reactionUrl = reactionRoot.getAttribute('data-reaction-post-url') || '';
            const reactionTypeId = event.detail?.reaction_type_id;
            const shortCode = event.detail?.short_code || '';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"]')?.value
                || '';

            if (!reactionUrl || !csrf || (!reactionTypeId && !shortCode)) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = reactionUrl;
            form.className = 'hidden';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            if (reactionTypeId) {
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'reaction_type_id';
                idInput.value = String(reactionTypeId);
                form.appendChild(idInput);
            }

            if (shortCode) {
                const shortCodeInput = document.createElement('input');
                shortCodeInput.type = 'hidden';
                shortCodeInput.name = 'short_code';
                shortCodeInput.value = shortCode;
                form.appendChild(shortCodeInput);
            }

            document.body.appendChild(form);
            form.submit();
        });

        document.addEventListener('click', async function (event) {
            const summaryToggle = event.target.closest('[data-post-summary-toggle]');
            if (summaryToggle) {
                const targetId = summaryToggle.getAttribute('data-post-summary-toggle') || '';
                const target = targetId ? document.getElementById(targetId) : null;
                if (target) {
                    const expanded = summaryToggle.getAttribute('aria-expanded') === 'true';
                    target.classList.toggle('hidden', expanded);
                    target.classList.toggle('is-open', !expanded);
                    summaryToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    summaryToggle.classList.toggle('is-active', !expanded);
                    summaryToggle.classList.remove('is-clicked');
                    void summaryToggle.offsetWidth;
                    summaryToggle.classList.add('is-clicked');
                    window.setTimeout(() => summaryToggle.classList.remove('is-clicked'), 220);

                    const label = summaryToggle.querySelector('[data-post-summary-label]');
                    const visibleLabel = summaryToggle.querySelector('[data-post-summary-visible-label]');
                    if (visibleLabel) {
                        visibleLabel.textContent = expanded ? '\u00d6zetle' : '\u00d6zeti gizle';
                    }

                    const chevron = summaryToggle.querySelector('[data-post-summary-chevron]');
                    if (chevron) {
                        chevron.setAttribute('icon', expanded ? 'lucide:chevron-down' : 'lucide:chevron-up');
                    }

                    summaryToggle.setAttribute('aria-label', expanded ? '\u00d6zetle' : '\u00d6zeti gizle');
                    if (label) {
                        const nextLabel = expanded ? '\u00d6zetle' : '\u00d6zeti gizle';
                        window.requestAnimationFrame(() => {
                            label.textContent = nextLabel;
                        });
                        label.textContent = expanded ? 'KÄ±saca' : 'Ã–zeti gizle';
                    }
                }

                return;
            }

            const inlineToggle = event.target.closest('[data-post-inline-toggle]');
            if (inlineToggle) {
                const targetId = inlineToggle.getAttribute('data-post-inline-toggle') || '';
                const target = targetId ? document.getElementById(targetId) : null;
                if (target) {
                    const expanded = inlineToggle.getAttribute('aria-expanded') === 'true';
                    target.classList.toggle('hidden', expanded);
                    target.classList.toggle('is-open', !expanded);
                    inlineToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    const label = inlineToggle.querySelector('[data-post-inline-label]');
                    if (label) {
                        label.textContent = expanded ? 'Tamam\u0131n\u0131 g\u00f6ster' : 'Daha az g\u00f6ster';
                    }
                    const icon = inlineToggle.querySelector('[data-post-inline-icon]');
                    if (icon) {
                        icon.setAttribute('icon', expanded ? 'lucide:chevron-down' : 'lucide:chevron-up');
                    }
                }
                return;
            }

            const mobileMenuOpen = event.target.closest('[data-post-mobile-menu-open]');
            if (mobileMenuOpen) {
                const sheetId = mobileMenuOpen.getAttribute('data-post-mobile-menu-open') || '';
                const sheet = sheetId ? document.getElementById(sheetId) : null;
                const panel = sheet?.querySelector('[data-post-mobile-menu-panel]');
                const closeControls = sheet?.querySelectorAll('[data-post-mobile-menu-close]');
                if (sheet && panel) {
                    mobileMenuOpen.classList.add('is-active');
                    mobileMenuOpen.setAttribute('aria-expanded', 'true');
                    sheet.classList.remove('hidden', 'pointer-events-none');
                    sheet.setAttribute('aria-hidden', 'false');
                    document.documentElement.classList.add('overflow-hidden');
                    document.body.classList.add('overflow-hidden');
                    requestAnimationFrame(() => {
                        closeControls?.forEach((control) => control.classList.remove('opacity-0'));
                        panel.classList.remove('translate-y-full');
                    });
                }
                return;
            }

            const mobileMenuClose = event.target.closest('[data-post-mobile-menu-close]');
            if (mobileMenuClose) {
                const sheet = mobileMenuClose.closest('[data-post-mobile-menu-sheet]');
                const panel = sheet?.querySelector('[data-post-mobile-menu-panel]');
                const closeControls = sheet?.querySelectorAll('[data-post-mobile-menu-close]');
                if (sheet && panel) {
                    document.querySelectorAll('[data-post-mobile-menu-open]').forEach((trigger) => {
                        if (trigger.getAttribute('data-post-mobile-menu-open') !== sheet.id) {
                            return;
                        }

                        trigger.classList.remove('is-active');
                        trigger.setAttribute('aria-expanded', 'false');
                    });
                    closeControls?.forEach((control) => control.classList.add('opacity-0'));
                    panel.classList.add('translate-y-full');
                    sheet.setAttribute('aria-hidden', 'true');
                    window.setTimeout(() => {
                        sheet.classList.add('hidden', 'pointer-events-none');
                        document.documentElement.classList.remove('overflow-hidden');
                        document.body.classList.remove('overflow-hidden');
                    }, 280);
                }
                return;
            }

            const shareButton = event.target.closest('[data-post-share]');
            if (!shareButton) {
                return;
            }

            const url = shareButton.getAttribute('data-post-url') || window.location.href;
            const title = shareButton.getAttribute('data-post-title') || document.title;
            const originalLabel = shareButton.getAttribute('aria-label') || '';

            if (navigator.share) {
                try {
                    await navigator.share({ title, url });
                    return;
                } catch (error) {
                    if (error && error.name === 'AbortError') {
                        return;
                    }
                }
            }

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(url);
                } else {
                    const tempInput = document.createElement('input');
                    tempInput.value = url;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    tempInput.remove();
                }

                shareButton.setAttribute('aria-label', @json(__('site.post.link_copied')));
                shareButton.classList.add('is-copied');
                window.setTimeout(() => {
                    shareButton.setAttribute('aria-label', originalLabel);
                    shareButton.classList.remove('is-copied');
                }, 1400);
            } catch (error) {
                // Keep the UI unchanged if the clipboard API fails.
            }
        });
    </script>
@endonce


```

## Link Preview Partial

Kaynak: `resources/views/blog/partials/link-preview.blade.php`

```blade
@php
    $preview = $preview ?? null;
    if (is_object($preview)) {
        $preview = (array) $preview;
    }
    $preview = is_array($preview) ? $preview : [];

    $url = trim((string) ($preview['url'] ?? ''));
    $host = trim((string) ($preview['host'] ?? (parse_url($url, PHP_URL_HOST) ?: '')));
    $host = preg_replace('/^www\./i', '', $host) ?: $host;
    $title = trim((string) ($preview['title'] ?? $host));
    $description = trim((string) ($preview['description'] ?? ''));
    $imageUrl = trim((string) ($preview['image_url'] ?? ''));
    $sourceLabel = \Illuminate\Support\Str::upper(trim((string) ($preview['source_label'] ?? 'Kaynak')));
    $showHostMeta = $host !== '' && \Illuminate\Support\Str::lower($host) !== \Illuminate\Support\Str::lower($title);
@endphp

@if($url !== '')
    <a
        href="{{ $url }}"
        target="_blank"
        rel="nofollow noopener noreferrer"
        class="alma-link-preview"
        aria-label="{{ $title !== '' ? $title : $host }}"
    >
        @if($imageUrl !== '')
            <div class="alma-link-preview__media">
                <img src="{{ $imageUrl }}" alt="{{ $title !== '' ? $title : $host }}" loading="lazy" class="alma-link-preview__image" />
            </div>
        @endif

        <div class="alma-link-preview__body">
            <div class="alma-link-preview__head">
                <div class="min-w-0">
                    <span class="alma-link-preview__eyebrow">{{ $sourceLabel }}</span>
                    <span class="alma-link-preview__title">{{ $title !== '' ? $title : $host }}</span>
                </div>
                <span class="alma-link-preview__icon" aria-hidden="true">
                    <iconify-icon icon="lucide:arrow-up-right"></iconify-icon>
                </span>
            </div>

            @if($description !== '')
                <p class="alma-link-preview__description">{{ $description }}</p>
            @endif

            @if($showHostMeta)
                <span class="alma-link-preview__host">{{ $host }}</span>
            @endif
        </div>
    </a>
@endif

@once
    <style>
        .alma-link-preview {
            display: block;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 1.15rem;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            text-decoration: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .alma-link-preview:hover,
        .alma-link-preview:focus-visible {
            border-color: rgba(148, 163, 184, 0.8);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.1);
            transform: translateY(-1px);
            outline: none;
        }

        .alma-link-preview__media {
            position: relative;
            background: #f8fafc;
        }

        .alma-link-preview__image {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
        }

        .alma-link-preview__body {
            padding: 0.95rem 1rem 1rem;
        }

        .alma-link-preview__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.85rem;
        }

        .alma-link-preview__eyebrow {
            display: block;
            margin-bottom: 0.25rem;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .alma-link-preview__title,
        .alma-link-preview__host {
            display: -webkit-box;
            overflow: hidden;
            color: #0f172a;
            -webkit-box-orient: vertical;
            word-break: break-word;
        }

        .alma-link-preview__title {
            -webkit-line-clamp: 2;
            font-size: 0.98rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .alma-link-preview__description {
            display: -webkit-box;
            overflow: hidden;
            margin-top: 0.55rem;
            color: #475569;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .alma-link-preview__host {
            margin-top: 0.6rem;
            color: #64748b;
            -webkit-line-clamp: 1;
            font-size: 0.82rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .alma-link-preview__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            background: #f8fafc;
            color: #64748b;
        }

        .alma-link-preview__icon iconify-icon {
            font-size: 1rem;
        }

        html.dark .alma-link-preview {
            border-color: rgba(71, 85, 105, 0.75);
            background: #0f172a;
            box-shadow: 0 12px 28px rgba(2, 6, 23, 0.3);
        }

        html.dark .alma-link-preview:hover,
        html.dark .alma-link-preview:focus-visible {
            border-color: rgba(148, 163, 184, 0.55);
            box-shadow: 0 18px 36px rgba(2, 6, 23, 0.4);
        }

        html.dark .alma-link-preview__media {
            background: #111827;
        }

        html.dark .alma-link-preview__title {
            color: #f8fafc;
        }

        html.dark .alma-link-preview__description {
            color: #cbd5e1;
        }

        html.dark .alma-link-preview__host,
        html.dark .alma-link-preview__icon {
            color: #94a3b8;
        }

        html.dark .alma-link-preview__icon {
            background: rgba(15, 23, 42, 0.9);
        }
    </style>
@endonce
```

## Reaction Partial

Kaynak: `resources/views/blog/reaction.blade.php`

```blade
@props([
    'count' => 0,
    'icon' => null,
    'label' => null,
    'class' => '',
    'triggerClass' => null,
    // Options should come from reaction_types (id + emoji/icon/url)
    'gifs' => [],
    'isAdd' => false,
])

@php
    $addSvg = '<iconify-icon icon="lucide:smile-plus" class="text-[18px]"></iconify-icon>';
    $uid = 'rx_' . substr(md5(($label ?? '') . ($count ?? '') . uniqid('', true)), 0, 8);
    $rawIcon = $isAdd ? $addSvg : ($icon ?? null);
    $isAuth = auth()->check();

    $startsWithUrl = function ($val) {
        if (!is_string($val)) return false;
        return str_starts_with($val, 'http://')
            || str_starts_with($val, 'https://')
            || str_starts_with($val, '/storage')
            || str_starts_with($val, '/uploads')
            || str_starts_with($val, '/');
    };

    $renderIcon = function ($value, $labelText = null, $size = 'h-10 w-10 rounded-full') use ($startsWithUrl) {
        if (!isset($value)) {
            return e('?');
        }

        $isString = is_string($value);
        $raw = $isString ? trim($value) : $value;
        $hasHtml = $isString && preg_match('/<\s*(img|svg|iconify-icon)/i', $raw);
        $resolved = $raw;
        if (
            $isString
            && !$startsWithUrl($resolved)
            && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $resolved)
        ) {
            $resolved = str_starts_with($resolved, 'storage/')
                ? url('/' . ltrim($resolved, '/'))
                : asset('storage/' . ltrim($resolved, '/'));
        }

        $isImgExt = $isString && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $resolved);
        $hasUrl = $startsWithUrl($resolved);

        if ($hasHtml) {
            return $raw;
        }

        if ($isImgExt || $hasUrl) {
            return '<img src="'.e($resolved).'" alt="'.e($labelText ?? 'reaction').'" class="'.$size.' object-cover">';
        }

        return e($raw ?: '?');
    };

$optionsSource = collect($gifs ?? []);

    if ($optionsSource->isEmpty()) {
        $optionsSource = \App\Models\ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url'])
            ->map(fn ($type) => [
                'id' => $type->id,
                'short_code' => $type->short_code,
                'emoji' => $type->emoji,
                'gif_url' => $type->gif_url,
                'label' => $type->label,
            ]);
    }

    $options = $optionsSource
        ->take(12)
        ->map(function ($item) use ($renderIcon) {
            $id = null;
            $label = null;
            $iconValue = null;
            $shortCode = null;

            if (is_array($item)) {
                $id = $item['id'] ?? null;
                $shortCode = $item['short_code'] ?? null;
                $label = $item['label'] ?? $item['title'] ?? null;
                $iconValue = $item['icon'] ?? $item['emoji'] ?? $item['gif_url'] ?? $item['short_code'] ?? null;
            } elseif (is_object($item)) {
                $id = $item->id ?? null;
                $shortCode = $item->short_code ?? null;
                $label = $item->label ?? $item->title ?? $item->name ?? null;
                $iconValue = $item->icon ?? $item->emoji ?? $item->gif_url ?? $item->short_code ?? null;
            } else {
                $iconValue = $item;
            }

            if (!$iconValue && $label) {
                $iconValue = mb_substr($label, 0, 1);
            }

            return [
                'id' => $id,
                'short_code' => $shortCode ?? null,
                'icon' => $renderIcon($iconValue, $label),
                'label' => $label,
            ];
        })
        ->filter(fn($opt) => !empty($opt['icon']))
        ->values();

    $fallbackIcon = $rawIcon ?: ($options->first()['icon'] ?? $addSvg);
    $triggerIcon = $renderIcon($fallbackIcon, $label, 'h-5 w-5 rounded-full');
    if ($isAdd) {
        $triggerIcon = isset($icon)
            ? $renderIcon($icon, $label, 'h-5 w-5 rounded-full')
            : $addSvg;
    }
    $defaultTriggerClass = $isAdd
        ? 'rx-add-trigger'
        : 'rx-summary-trigger';
    $triggerClass = $triggerClass ?: $defaultTriggerClass;
@endphp

<div class="relative inline-block {{ $class }}" data-rx-wrapper="{{ $uid }}" @if(!$isAuth) data-rx-guest="1" data-login-url="{{ route('login') }}" @endif>
    @if($isAuth)
        <button
            type="button"
            class="{{ $triggerClass }}"
            data-rx-trigger="{{ $uid }}"
            aria-haspopup="dialog"
            aria-expanded="false"
            aria-label="{{ $label ? $label . ' tepkisi' : 'Tepki sec' }}"
        >
            <span class="text-base leading-none">{!! $triggerIcon !!}</span>
            @if($count !== '')
                <span class="text-[13px] font-semibold">{{ number_format((int) $count) }}</span>
            @endif
            @if($label)
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
            @endif
        </button>
    @else
        @if($isAdd)
            <button
                type="button"
                class="{{ $triggerClass }}"
                data-rx-trigger="{{ $uid }}"
                aria-haspopup="dialog"
                aria-expanded="false"
                aria-label="Tepki sec"
            >
                <span class="text-base leading-none">{!! $triggerIcon !!}</span>
            </button>
        @else
            {{-- Clickable button for guests - redirects to login --}}
            <button
                type="button"
                class="{{ $triggerClass }} cursor-pointer"
                onclick="window.location.href='{{ route('login') }}'"
                aria-label="{{ $label ? $label . ' tepkisi' : 'Tepki sec' }}"
                title="Giris yapin"
            >
                <span class="text-base leading-none">{!! $triggerIcon !!}</span>
                @if($count !== '')
                    <span class="text-[13px] font-semibold">{{ number_format((int) $count) }}</span>
                @endif
            </button>
        @endif
    @endif

    {{-- Reaction dropdown panel --}}
    @if($isAuth || $isAdd)
        <div
            class="fixed z-50 hidden w-[240px] rounded-xl border border-slate-200 bg-white p-3 shadow-[0_16px_34px_rgba(15,23,42,0.14)] dark:border-slate-700 dark:bg-slate-800"
            data-rx-panel="{{ $uid }}"
            role="dialog"
            style="max-width: calc(100vw - 24px);"
        >
            <div class="mb-3 flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                <span class="font-semibold text-gray-800 dark:text-gray-100">Tepkiler</span>
                <button type="button" class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-200" data-rx-close="{{ $uid }}" aria-label="Kapat">
                    <span class="text-lg leading-none">&times;</span>
                </button>
            </div>
            @if($options->isNotEmpty())
                <div class="grid max-h-56 grid-cols-4 gap-2 overflow-y-auto">
                    @foreach($options as $option)
                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-base transition hover:bg-gray-200 dark:bg-slate-700/70 dark:hover:bg-slate-600/80"
                            data-rx-option="{{ $uid }}"
                            @if(!empty($option['id'])) data-rx-option-id="{{ $option['id'] }}" @endif
                            @if(!empty($option['short_code'])) data-rx-option-code="{{ $option['short_code'] }}" @endif
                            aria-label="{{ $option['label'] ?? $option['short_code'] ?? 'Tepki' }}"
                        >
                            <span class="leading-none">{!! $option['icon'] !!}</span>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl bg-gray-100 dark:bg-gray-700/50 px-3 py-6 text-center text-xs text-gray-500 dark:text-gray-400">
                    Tepki bulunamad&#305;.
                </div>
            @endif
        </div>
    @endif
</div>

@once
<style>
    .rx-summary-trigger {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        border-radius: 999px;
        background: #f3f4f6;
        padding: 6px 12px;
        color: #374151;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        transition: background-color .2s ease, color .2s ease;
    }

    .rx-summary-trigger:hover {
        background: #e5e7eb;
    }

    .rx-add-trigger {
        display: inline-flex;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #f3f4f6;
        color: #6b7280;
        transition: background-color .2s ease, color .2s ease;
    }

    .rx-add-trigger:hover {
        background: #e5e7eb;
        color: #374151;
    }

    html.dark .rx-summary-trigger,
    html.dark .rx-add-trigger {
        background: rgba(51, 65, 85, 0.78);
        color: #e5e7eb;
    }

    html.dark .rx-summary-trigger:hover,
    html.dark .rx-add-trigger:hover {
        background: rgba(71, 85, 105, 0.86);
        color: #f8fafc;
    }
</style>
@endonce

@once
<script>
(function () {
    const hideAll = () => {
        document.querySelectorAll('[data-rx-panel]').forEach(panel => {
            panel.classList.add('hidden');
            panel.style.top = '';
            panel.style.left = '';
        });
        document.querySelectorAll('[data-rx-trigger]').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
    };

    const positionPanel = (panel, trigger) => {
        // Remove previous position styles
        panel.style.top = '';
        panel.style.left = '';
        panel.style.right = '';
        
        // Temporarily show to measure
        const prevVisibility = panel.style.visibility;
        panel.classList.remove('hidden');
        panel.style.visibility = 'hidden';

        const panelRect = panel.getBoundingClientRect();
        const triggerRect = trigger.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Calculate available space
        const spaceBelow = viewportHeight - triggerRect.bottom - 16;
        const spaceAbove = triggerRect.top - 16;
        
        // Width of panel
        const panelWidth = panelRect.width || 224; // w-56 = 224px
        
        // Horizontal positioning
        let leftPos = triggerRect.left;
        let topPos;
        
        // Check if panel would overflow on right
        if (leftPos + panelWidth > viewportWidth - 12) {
            // Right-align instead
            panel.style.left = 'auto';
            panel.style.right = Math.max(12, viewportWidth - triggerRect.right) + 'px';
        } else {
            panel.style.left = leftPos + 'px';
        }
        
        // Vertical positioning
        if (spaceBelow >= panelRect.height || spaceBelow > spaceAbove) {
            // Open below
            topPos = triggerRect.bottom + 8;
        } else {
            // Open above
            topPos = triggerRect.top - panelRect.height - 8;
        }
        
        panel.style.top = Math.max(12, topPos) + 'px';
        
        panel.style.visibility = prevVisibility || '';
    };

    const openPanel = (uid) => {
        const panel = document.querySelector(`[data-rx-panel="${uid}"]`);
        const btn = document.querySelector(`[data-rx-trigger="${uid}"]`);
        if (!panel || !btn) return;
        
        hideAll();
        positionPanel(panel, btn);
        panel.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
    };

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-rx-trigger]');
        const closeBtn = e.target.closest('[data-rx-close]');
        const optionBtn = e.target.closest('[data-rx-option]');
        const wrapper = e.target.closest('[data-rx-wrapper]');

        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            openPanel(btn.getAttribute('data-rx-trigger'));
            return;
        }

        if (closeBtn) {
            e.preventDefault();
            hideAll();
            return;
        }

        if (optionBtn) {
            e.preventDefault();
            const wrapperEl = optionBtn.closest('[data-rx-wrapper]');
            const loginUrl = wrapperEl?.getAttribute('data-login-url');
            if (wrapperEl?.hasAttribute('data-rx-guest') && loginUrl) {
                window.location.href = loginUrl;
                return;
            }
            const target = wrapperEl || window;
            target.dispatchEvent(new CustomEvent('reaction:selected', {
                detail: {
                    uid: optionBtn.getAttribute('data-rx-option'),
                    reaction_type_id: optionBtn.getAttribute('data-rx-option-id') ? Number(optionBtn.getAttribute('data-rx-option-id')) : null,
                    short_code: optionBtn.getAttribute('data-rx-option-code') || null,
                    icon_html: optionBtn.querySelector('span')?.innerHTML || optionBtn.innerHTML || null,
                },
                bubbles: true,
            }));
            hideAll();
            return;
        }

        if (!wrapper) hideAll();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideAll();
    });

    // Reposition on scroll
    window.addEventListener('scroll', () => {
        document.querySelectorAll('[data-rx-trigger][aria-expanded="true"]').forEach(btn => {
            const uid = btn.getAttribute('data-rx-trigger');
            const panel = document.querySelector(`[data-rx-panel="${uid}"]`);
            if (panel && !panel.classList.contains('hidden')) {
                positionPanel(panel, btn);
            }
        });
    }, true);
})();
</script>
@endonce










```

## Layout Styles: Core Post Card

Kaynak: `resources/views/layouts/app.blade.php`

```blade


```

## Layout Styles: Overrides

Kaynak: `resources/views/layouts/app.blade.php`

```blade


```

## Layout Styles: Dark Mode

Kaynak: `resources/views/layouts/app.blade.php`

```blade


```

## Layout Styles: Responsive

Kaynak: `resources/views/layouts/app.blade.php`

```blade
        .alma-post-card {
            border-radius: 12px;
            padding: 14px;
            gap: 0;
        }

        .alma-post-card__header {
            position: relative;
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            margin-bottom: 12px;
        }

        .alma-post-card__identity {
            width: 100%;
            padding-right: 92px;
        }

        .alma-post-card__header-actions {
            position: absolute;
            top: -2px;
            right: 0;
            justify-content: flex-end;
        }

        .alma-post-card__header-pill[data-post-summary-toggle] {
            min-width: 40px;
            min-height: 40px;
            padding: 0;
            border-radius: 999px;
            justify-content: center;
        }

        .alma-post-card__summary-ai-copy,
        .alma-post-card__summary-ai-chevron {
            display: none;
        }

        .alma-post-card__summary-ai-chip,
        .alma-post-card__summary-ai-icon-wrap {
            width: 22px;
            height: 22px;
        }

        .alma-post-card__summary-ai-icon {
            width: 14px;
            height: 14px;
        }

        .alma-post-card__author {
            font-size: 14px;
        }

        .alma-post-card__submeta,
        .alma-post-card__submeta a,
        .alma-post-card__submeta time,
        .alma-post-card__submeta span,
        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            font-size: 13px;
        }

        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            min-height: 34px;
            padding: 0 14px;
        }

        .alma-post-card__title,
        .alma-post-card__title.is-hero {
            font-size: 19px;
            line-height: 1.32;
        }

        .alma-post-card__excerpt,
        .alma-post-card__summary-text,
        .alma-post-card__inline-text {
            font-size: 14.5px;
            line-height: 1.58;
        }

        .alma-post-card__comments-count {
            font-size: 14px;
            line-height: 1.42;
        }

        .alma-post-card__image {
            height: auto;
        }

        .alma-post-card__engagement {
            align-items: center;
            padding: 12px 6px;
        }

        .alma-post-card__engagement-main,
        .alma-post-card__reactions {
            gap: 14px;
        }

        .alma-post-card__views,
        .alma-post-card__metric-button,
        .alma-post-card__reaction-pill {
            font-size: 13px;
        }

        .alma-post-card__metric-button span,
        .alma-post-card__views span,
        .alma-post-card__reaction-pill-count {
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .alma-post-card__author {
            font-size: 14px;
            line-height: 1.24;
        }

        .alma-post-card__submeta,
        .alma-post-card__submeta a,
        .alma-post-card__submeta time,
        .alma-post-card__submeta span,
        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            font-size: 13px;
            line-height: 1.25;
        }

        .alma-post-card__header-actions {
            gap: 6px;
        }

        .alma-post-card__header-pill,
        .alma-post-card__header-follow {
            min-height: 34px;
            padding: 0 14px;
        }

        .alma-post-card__identity {
            padding-right: 84px;
        }

        .alma-post-card__header-pill[data-post-summary-toggle] {

    .alma-post-card__header-pill,
    .alma-post-card__header-follow,
    .alma-post-card__reaction-pill,
    .alma-post-card__reaction-picker,
    .alma-post-card__metric-button,
    .alma-post-card__menu-trigger {
        border: none !important;
    }

    .alma-post-card__header-pill,
    .alma-post-card__header-follow,
    .alma-post-card__reaction-pill,
    .alma-post-card__reaction-picker,
    .alma-post-card__reaction-more,
    .alma-post-card__metric-button,
    .alma-post-card__menu-trigger {
        background: transparent !important;
        color: #6b7280 !important;
        border: none !important;
        box-shadow: none !important;
    }

    .alma-post-card__header-pill:hover,
    .alma-post-card__header-follow:hover,
    .alma-post-card__reaction-pill:hover,
    .alma-post-card__reaction-picker:hover,
    .alma-post-card__reaction-more:hover,
    .alma-post-card__metric-button:hover,
    .alma-post-card__menu-trigger:hover {

```


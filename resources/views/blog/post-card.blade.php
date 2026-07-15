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
    $postTags = collect(optional($postObj)->tags ?? data_get($postArr, 'tags', []))
        ->filter()
        ->take(6)
        ->values();

    $normalizePostPlainText = function ($value): string {
        $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/<(br|\/p|\/div|\/li|\/blockquote|\/h[1-6])[^>]*>/iu', "\n", $text) ?? $text;
        $text = trim(strip_tags($text));
        $text = preg_replace('/(?:\[\s*object Object\s*\]\s*)+/iu', ' ', $text) ?? $text;
        $text = preg_replace("/\r\n?|\n/u", "\n", $text) ?? $text;
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */u', "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    };

    $extractEditorPlainText = function ($contentJson) use ($normalizePostPlainText): string {
        if (is_string($contentJson)) {
            $decoded = json_decode($contentJson, true);
            $contentJson = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (!is_array($contentJson)) {
            return '';
        }

        return collect($contentJson['blocks'] ?? [])
            ->map(function ($block) use ($normalizePostPlainText) {
                if (!is_array($block)) {
                    return '';
                }

                $type = (string) ($block['type'] ?? '');
                $data = is_array($block['data'] ?? null) ? $block['data'] : [];

                return match ($type) {
                    'paragraph', 'header', 'quote' => $normalizePostPlainText($data['text'] ?? ''),
                    'list' => collect($data['items'] ?? [])
                        ->flatten()
                        ->map(fn ($item) => $normalizePostPlainText($item))
                        ->filter()
                        ->implode("\n"),
                    'checklist' => collect($data['items'] ?? [])
                        ->map(fn ($item) => $normalizePostPlainText(is_array($item) ? ($item['text'] ?? '') : $item))
                        ->filter()
                        ->implode("\n"),
                    'table' => collect($data['content'] ?? [])
                        ->flatten()
                        ->map(fn ($item) => $normalizePostPlainText($item))
                        ->filter()
                        ->implode(' '),
                    default => $normalizePostPlainText($data['caption'] ?? $data['title'] ?? ''),
                };
            })
            ->filter()
            ->implode("\n\n");
    };

    $postContentJson = optional($postObj)->content_json ?? data_get($postArr, 'content_json');
    $fullPostText = $extractEditorPlainText($postContentJson);

    if ($fullPostText === '') {
        $fullPostText = $normalizePostPlainText(optional($postObj)->content ?? $postArr['content'] ?? '');
    }

    if ($fullPostText === '') {
        $fullPostText = $normalizePostPlainText(optional($postObj)->excerpt ?? $postArr['excerpt'] ?? $excerpt ?? '');
    }

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

    $resolvedExcerptRaw = $normalizePostPlainText($resolvedExcerpt);
    if ($resolvedExcerptRaw === '' || mb_strlen($fullPostText) > mb_strlen($resolvedExcerptRaw)) {
        $resolvedExcerptRaw = $fullPostText !== '' ? $fullPostText : $resolvedExcerptRaw;
    }

    $resolvedExcerptShortSource = $normalizePostPlainText($excerpt ?? optional($postObj)->excerpt ?? $postArr['excerpt'] ?? $resolvedExcerptRaw);
    if ($resolvedExcerptShortSource === '' || \Illuminate\Support\Str::contains($resolvedExcerptShortSource, '[object Object]')) {
        $resolvedExcerptShortSource = $resolvedExcerptRaw;
    }

    $resolvedExcerptShort = \Illuminate\Support\Str::limit($resolvedExcerptShortSource, 180);
    $resolvedExcerptExpanded = $resolvedExcerptRaw;
    $hasInlineContinue = mb_strlen($resolvedExcerptExpanded) > mb_strlen($resolvedExcerptShort);
    $resolvedExcerpt = $resolvedExcerptShort;
    $linkPreview = optional($postObj)->link_preview
        ?? $postArr['link_preview']
        ?? null;
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

    $hasSourcePreview = $linkPreviewUrl !== '';
    $authorAvatarAlt = trim($authorName . ' profil fotografi');
    $categoryAvatarAlt = trim(($categoryName !== '' ? $categoryName : 'Genel') . ' topluluk logosu');
    $heroImageAlt = trim($title . ' gonderi gorseli');

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

        if (in_array($host, ['x.com', 'www.x.com', 'mobile.x.com', 'twitter.com', 'www.twitter.com', 'mobile.twitter.com'], true) || str_ends_with($host, '.x.com') || str_ends_with($host, '.twitter.com')) {
            if (in_array('status', $pathParts, true) || in_array('statuses', $pathParts, true)) {
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

    $resolveLocalQuotePost = function (?string $url) use ($postObj) {
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

        if ($postObj && (string) ($postObj->slug ?? '') === $slug) {
            return null;
        }

        return \App\Models\Post::query()
            ->with([
                'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                'category:id,name,slug,profile_image',
            ])
            ->where('slug', $slug)
            ->first();
    };

    $extractQuoteImageFromPost = function ($quotePost) use ($normalizeCardImageUrl): ?string {
        if (!$quotePost) {
            return null;
        }

        $image = $normalizeCardImageUrl($quotePost->featured_image_url ?? $quotePost->featured_image ?? null);
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
                ->map($normalizeCardImageUrl)
                ->filter()
                ->first();

            if ($image) {
                return $image;
            }
        }

        $content = (string) ($quotePost->content ?? '');
        if ($content !== '' && preg_match('/<img[^>]+(?:src|data-src|data-original)=["\']([^"\']+)["\']/i', $content, $matches)) {
            return $normalizeCardImageUrl($matches[1] ?? null);
        }

        return null;
    };

    $quotePreviewPost = $resolveLocalQuotePost($linkPreviewUrl);
    $quotePreviewUrl = $linkPreviewUrl;
    $quotePreviewTitle = trim((string) (
        $quotePreviewPost?->title
        ?? data_get($linkPreview, 'title', '')
    ));
    $quotePreviewDescription = trim(strip_tags((string) (
        $quotePreviewPost?->excerpt
        ?? data_get($linkPreview, 'description', '')
    )));
    if ($quotePreviewDescription === '' && $quotePreviewPost) {
        $quotePreviewDescription = $normalizePostPlainText($quotePreviewPost->content ?? '');
    }
    $quotePreviewDescription = $quotePreviewDescription !== '' ? \Illuminate\Support\Str::limit($quotePreviewDescription, 170) : '';
    $quotePreviewImage = $extractQuoteImageFromPost($quotePreviewPost)
        ?? $normalizeCardImageUrl((string) data_get($linkPreview, 'image_url', ''));
    $quotePreviewAuthor = $quotePreviewPost?->author;
    $quotePreviewAuthorName = trim((string) ($quotePreviewAuthor?->name ?? data_get($linkPreview, 'site_name', $linkPreviewHost)));
    $quotePreviewAuthorName = $quotePreviewAuthorName !== '' ? $quotePreviewAuthorName : 'Ografi';
    $quotePreviewAuthorAvatar = $quotePreviewAuthor?->profile_photo_url ?? null;
    $quotePreviewCategory = $quotePreviewPost?->category;
    $quotePreviewCategoryAvatar = $quotePreviewCategory?->profile_image_url ?? $quotePreviewCategory?->profile_image ?? null;
    $quotePreviewTime = $quotePreviewPost?->published_at?->diffForHumans()
        ?? $quotePreviewPost?->created_at?->diffForHumans()
        ?? '';
    $quotePreviewInitials = collect(preg_split('/\s+/', trim($quotePreviewAuthorName), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
    $quotePreviewInitials = $quotePreviewInitials !== '' ? $quotePreviewInitials : 'OG';
    $hasQuotePreviewCard = $quotePreviewUrl !== '' && ($quotePreviewTitle !== '' || $quotePreviewDescription !== '' || $quotePreviewImage);
    $stripGeneratedQuoteText = function (string $text) use ($quotePreviewUrl, $quotePreviewTitle): string {
        if (trim($text) === '') {
            return '';
        }

        return collect(preg_split("/\r\n|\n|\r/", $text) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->reject(function ($line) use ($quotePreviewUrl, $quotePreviewTitle) {
                if ($line === '') {
                    return false;
                }

                $normalizedLine = \Illuminate\Support\Str::lower($line);
                $normalizedTitle = \Illuminate\Support\Str::lower(trim($quotePreviewTitle));

                return \Illuminate\Support\Str::startsWith($normalizedLine, ['alinti:', 'alıntı:'])
                    || ($quotePreviewUrl !== '' && $line === $quotePreviewUrl)
                    || filter_var($line, FILTER_VALIDATE_URL)
                    || ($normalizedTitle !== '' && $normalizedLine === $normalizedTitle);
            })
            ->filter()
            ->implode("\n");
    };

    if ($hasQuotePreviewCard && \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($resolvedExcerptRaw . "\n" . $fullPostText), ['alinti:', 'alıntı:'])) {
        $resolvedExcerptRaw = $stripGeneratedQuoteText($resolvedExcerptRaw);
        $resolvedExcerptExpanded = $stripGeneratedQuoteText($resolvedExcerptExpanded);
        $resolvedExcerptShortSource = $stripGeneratedQuoteText($resolvedExcerptShortSource);
        $resolvedExcerptShort = $resolvedExcerptShortSource !== '' ? \Illuminate\Support\Str::limit($resolvedExcerptShortSource, 180) : '';
        $resolvedExcerpt = $resolvedExcerptShort;
        $fullPostText = $stripGeneratedQuoteText($fullPostText);
        $hasInlineContinue = $resolvedExcerptExpanded !== '' && mb_strlen($resolvedExcerptExpanded) > mb_strlen($resolvedExcerptShort);
    }

    $contentBlocks = collect(is_array(optional($postObj)->content_json) ? (optional($postObj)->content_json['blocks'] ?? []) : []);
    $extractBlockImages = function (array $block) use ($normalizeCardImageUrl): array {
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
            ->values()
            ->all();
    };

    $extractBlockVideoEmbed = function (array $block) use ($buildSocialEmbedUrlFromUrl, $sanitizeSocialEmbedUrl): ?string {
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
    };

    $htmlContentImageUrls = collect();
    $contentHtml = (string) (optional($postObj)->content ?? $postArr['content'] ?? '');
    if ($contentHtml !== '' && preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $contentHtml, $matches)) {
        $htmlContentImageUrls = collect($matches[1] ?? [])
            ->map($normalizeCardImageUrl)
            ->filter();
    }

    $contentGalleryImages = $contentBlocks
        ->flatMap(fn ($block) => is_array($block) ? $extractBlockImages($block) : [])
        ->merge($htmlContentImageUrls)
        ->filter()
        ->values();

    $contentGalleryImages = $contentGalleryImages
        ->map(function ($image) use ($resolveImageDimensions) {
            [$width, $height] = $resolveImageDimensions($image, [1024, 1024]);

            return [
                'src' => $image,
                'width' => $width,
                'height' => $height,
            ];
        })
        ->values();

    $featuredRenderWidth = 3840;
    $featuredRenderHeight = 2160;
    $featuredFrameStyle = 'display:block;width:100%;aspect-ratio:3840 / 2160;overflow:hidden;line-height:0;';
    $featuredImageStyle = 'display:block;width:100%;height:100%;max-width:none;object-fit:cover;';
    $gallerySlideStyle = 'display:block;flex:0 0 100%;width:100%;max-width:100%;aspect-ratio:3840 / 2160;overflow:hidden;';
    $hasContentGallery = $contentGalleryImages->isNotEmpty();
    $displayImage = $featuredImage;
    [$displayImageWidth, $displayImageHeight] = $resolveImageDimensions($displayImage, [1024, 1024]);
    $mediaLoading = $isHero ? 'eager' : 'lazy';
    $mediaFetchPriority = $isHero ? 'high' : 'auto';
    $shouldRenderMediaBlur = !$isHero;
    $showVideoPreview = $socialEmbedUrls->isNotEmpty();

    $videoThumbnailFromEmbedUrl = function (?string $embedUrl): ?string {
        $embedUrl = trim((string) $embedUrl);
        if ($embedUrl === '') {
            return null;
        }

        if (preg_match('#youtube(?:-nocookie)?\.com/embed/([^?&/]+)#i', $embedUrl, $matches)) {
            return 'https://i.ytimg.com/vi/' . rawurlencode((string) $matches[1]) . '/hqdefault.jpg';
        }

        if (preg_match('#player\.vimeo\.com/video/(\d+)#i', $embedUrl, $matches)) {
            return 'https://vumbnail.com/' . rawurlencode((string) $matches[1]) . '.jpg';
        }

        return null;
    };

    $mediaItems = collect();
    $featuredImageNormalized = $normalizeCardImageUrl($featuredImage);
    if ($featuredImageNormalized) {
        [$featuredWidth, $featuredHeight] = $resolveImageDimensions($featuredImageNormalized, [1024, 1024]);
        $mediaItems->push([
            'type' => 'image',
            'src' => $featuredImageNormalized,
            'thumb' => $featuredImageNormalized,
            'width' => $featuredWidth,
            'height' => $featuredHeight,
            'alt' => $heroImageAlt !== '' ? $heroImageAlt : $title,
            'label' => 'Gorsel',
        ]);
    }

    foreach ($contentBlocks as $block) {
        if (!is_array($block)) {
            continue;
        }

        foreach ($extractBlockImages($block) as $imageUrl) {
            [$width, $height] = $resolveImageDimensions($imageUrl, [1024, 1024]);
            $mediaItems->push([
                'type' => 'image',
                'src' => $imageUrl,
                'thumb' => $imageUrl,
                'width' => $width,
                'height' => $height,
                'alt' => $title . ' gorsel',
                'label' => 'Gorsel',
            ]);
        }

        $embedUrl = $extractBlockVideoEmbed($block);
        if ($embedUrl) {
            $providerMeta = (function (?string $url): array {
                $url = trim((string) $url);
                $host = strtolower((string) parse_url($url, PHP_URL_HOST));

                return match (true) {
                    str_contains($host, 'youtube') => ['label' => 'YouTube'],
                    str_contains($host, 'instagram') => ['label' => 'Instagram'],
                    str_contains($host, 'tiktok') => ['label' => 'TikTok'],
                    str_contains($host, 'vimeo') => ['label' => 'Vimeo'],
                    str_contains($host, 'dailymotion') => ['label' => 'Dailymotion'],
                    str_contains($host, 'facebook') => ['label' => 'Facebook'],
                    str_contains($host, 'twitch') => ['label' => 'Twitch'],
                    str_contains($host, 'twitframe') || str_contains($host, 'twitter') || $host === 'x.com' || $host === 'www.x.com' => ['label' => 'X'],
                    default => ['label' => 'Video'],
                };
            })($embedUrl);

            $mediaItems->push([
                'type' => 'video',
                'src' => $embedUrl,
                'thumb' => $videoThumbnailFromEmbedUrl($embedUrl),
                'width' => 1280,
                'height' => 720,
                'alt' => $title . ' video',
                'label' => $providerMeta['label'] ?? 'Video',
            ]);
        }
    }

    if ($mediaItems->isEmpty() && $contentGalleryImages->isNotEmpty()) {
        $mediaItems = $contentGalleryImages->map(fn ($image) => [
            'type' => 'image',
            'src' => $image['src'],
            'thumb' => $image['src'],
            'width' => $image['width'],
            'height' => $image['height'],
            'alt' => $title . ' gorsel',
            'label' => 'Gorsel',
        ])->values();
    }

    $mediaItems = $mediaItems->values();
    $hasMediaCarousel = $mediaItems->isNotEmpty();

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

        if (!\Illuminate\Support\Str::startsWith($resolvedValue, ['http://', 'https://', '//', '/'])) {
            if (preg_match('/\.(png|jpe?g|gif|webp|svg)(?:\?.*)?$/i', $resolvedValue)) {
                $resolvedValue = \Illuminate\Support\Str::startsWith($resolvedValue, 'storage/')
                    ? url('/' . ltrim($resolvedValue, '/'))
                    : asset('storage/' . ltrim($resolvedValue, '/'));
            } elseif (\Illuminate\Support\Str::startsWith($resolvedValue, ['uploads/', 'reactions/', 'reaction-types/'])) {
                $resolvedValue = asset('storage/' . ltrim($resolvedValue, '/'));
            }
        }

        $hasHtml = preg_match('/<\s*(img|svg|iconify-icon)/i', $trimmedValue);
        $isImage = preg_match('/\.(png|jpe?g|gif|webp|svg)(?:\?.*)?$/i', $resolvedValue)
            || \Illuminate\Support\Str::startsWith($resolvedValue, ['http://', 'https://', '/storage', '/uploads', '/']);

        if ($hasHtml) {
            return $trimmedValue;
        }

        if ($isImage) {
            return '<img src="' . e($resolvedValue) . '" alt="' . e($labelText ?: 'reaction') . '" class="post-card__reaction-asset" style="width:24px!important;height:24px!important;min-width:24px!important;max-width:24px!important;object-fit:contain!important" loading="lazy" decoding="async">';
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
                'icon' => $reaction['icon'] ?? ($reaction['gif_url'] ?? ($reaction['emoji'] ?? null)),
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
                'icon' => $type['gif_url'] ?? $type['emoji'] ?? null,
                'label' => $type['label'] ?? null,
                'type_id' => $type['id'] ?? null,
                'short_code' => $type['short_code'] ?? null,
            ];
        })->filter()->values();
    }

    $visibleReactionPills = $reactionPills->take(7)->values();
    $reactionOverflowCount = max($reactionPills->count() - $visibleReactionPills->count(), 0);
    $totalReactionCount = (int) ($reactionPills->sum('count') ?: (optional($postObj)->reactions_count ?? $postArr['reactions_count'] ?? 0));
    $hasReactionStrip = $visibleReactionPills->isNotEmpty() || $reactionOverflowCount > 0;

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
    $commenterPreviews = collect(optional($postObj)->commenter_previews ?? $postArr['commenter_previews'] ?? [])
        ->take(3)
        ->values();
    $commenterPreviewExtraCount = max(0, (int) (optional($postObj)->commenter_preview_extra_count ?? $postArr['commenter_preview_extra_count'] ?? 0));
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
    $formatFullMetric = function (int $value): string {
        return number_format(max($value, 0), 0, ',', '.');
    };
    $statsFeedViews = (int) (optional($postObj)->feed_views_count
        ?? optional($postObj)->feed_impressions_count
        ?? $postArr['feed_views_count']
        ?? $postArr['feed_impressions_count']
        ?? $viewsCount);
    $statsListingViews = (int) (optional($postObj)->listing_views_count
        ?? optional($postObj)->impressions_count
        ?? $postArr['listing_views_count']
        ?? $postArr['impressions_count']
        ?? $viewsCount);
    $statsTotalInteractions = max(0, $statsFeedViews + $statsListingViews + $totalReactionCount + $commentsCount + $bookmarksCount);
    $statsTotalInteractionsDisplay = $formatCompactMetric($statsTotalInteractions);
    $statsFeedViewsDisplay = $formatFullMetric($statsFeedViews);
    $statsListingViewsDisplay = $formatFullMetric($statsListingViews);
    $statsReactionsDisplay = $formatFullMetric((int) $totalReactionCount);
    $statsCommentsDisplay = $formatFullMetric($commentsCount);
    $statsBookmarksDisplay = $formatFullMetric($bookmarksCount);
    $showCommentsCountLabel = $commentsCount >= 1;
    $showBookmarksCountLabel = $bookmarksCount >= 1;
    $showViewsMetric = $viewsCount >= 1;
    $reactionVoteCountDisplay = $formatCompactMetric(max($totalReactionCount, 0));
    $summaryText = trim((string) ($resolvedExcerptExpanded !== '' ? $resolvedExcerptExpanded : $resolvedExcerptShort));
    $hasSummary = $summaryText !== '';
    $postPublishedMoment = null;
    $postEditedMoment = null;
    try {
        $postPublishedMoment = $createdAt ? \Illuminate\Support\Carbon::parse($createdAt) : null;
    } catch (\Throwable $e) {
        $postPublishedMoment = null;
    }
    try {
        $postEditedMoment = !empty(optional($postObj)->edited_at ?? $postArr['edited_at'] ?? null)
            ? \Illuminate\Support\Carbon::parse(optional($postObj)->edited_at ?? $postArr['edited_at'])
            : null;
    } catch (\Throwable $e) {
        $postEditedMoment = null;
    }
    $isPostEdited = $postEditedMoment !== null;
    $createdMetaLabel = $postPublishedMoment ? $postPublishedMoment->translatedFormat('d M') : $createdHuman;

    $dashboardPreferences = session('dashboard_preferences', []);
    $showMatureContent = (bool) ($dashboardPreferences['show_mature'] ?? false);
    $blurMatureContent = (bool) ($dashboardPreferences['blur_mature'] ?? true);
    $renderHeroNsfwBlur = $isNsfw && $blurMatureContent;
    $createdMetaTitle = $createdHuman;
    $postEditedAtLabel = $isPostEdited ? $postEditedMoment->translatedFormat('d M Y H:i') : null;
    $postEditedReason = trim((string) (optional($postObj)->edited_reason ?? $postArr['edited_reason'] ?? ''));

    $reactionAction = $postSlug !== '' ? route('blog.post.reaction', ['post' => $postSlug]) : null;
    $bookmarkAction = $postSlug !== '' && \Illuminate\Support\Facades\Route::has('blog.post.bookmark')
        ? route('blog.post.bookmark', ['post' => $postSlug])
        : null;
    $viewAction = $postSlug !== '' ? route('blog.post.view', ['post' => $postSlug]) : null;
    $editUrl = $postSlug !== '' ? route('blog.post.edit', $postSlug) : '#';
    $deleteAction = $postSlug !== '' ? route('blog.post.destroy', $postSlug) : '#';
    $pinAction = $postSlug !== '' ? route('blog.post.pin', $postSlug) : '#';
    $reportUrl = $viewer && $postAuthor && !$viewer->is($postAuthor) && $postSlug !== ''
        ? route('blog.post.report.form', ['post' => $postSlug])
        : null;

    $isOwnPost = $viewer && $postObj && (int) $viewer->id === (int) $postObj->author_id;
    $canOpenMenu = $viewer && ($isOwnPost || $reportUrl);
    $shareButtonId = $postSlug !== '' ? 'share-' . $postSlug : 'share-' . uniqid();
    $reactionRootId = $postSlug !== '' ? 'post-card-rx-' . $postSlug : 'post-card-rx-' . uniqid();
    $inlineExpandId = $postSlug !== '' ? 'post-card-inline-' . $postSlug : 'post-card-inline-' . uniqid();
    $summaryToggleId = $postSlug !== '' ? 'post-card-summary-' . $postSlug : 'post-card-summary-' . uniqid();
    $editToggleId = $postSlug !== '' ? 'post-card-edit-' . $postSlug : 'post-card-edit-' . uniqid();
    $menuSheetId = $postSlug !== '' ? 'post-card-menu-sheet-' . $postSlug : 'post-card-menu-sheet-' . uniqid();
    $cardShellId = $postSlug !== '' ? 'post-card-shell-' . $postSlug : 'post-card-shell-' . uniqid();
    $topicLabel = $hasCategory ? $categoryName : 'Genel';
    $heroImage = $displayImage
        ?: (string) (data_get($contentGalleryImages->first(), 'src') ?? '')
        ?: (string) $socialPreviewImage;
    $showExpandLink = $hasInlineContinue;
    $shouldShowTitleCheck = $showVerified;
    $replyShareUrl = $postUrl !== '#' ? $postUrl : url()->current();
    $commentRowLabel = $isCommentsDisabled
        ? 'Yorumlar kapali'
        : ($commentsCount > 0 ? $commentsCountDisplay . ' yorum' : 'Ilk yorumu sen yap');
    $commentsLinkLabel = 'Yorumlar - ' . $title;
    $commentPreviewPeople = $commenterPreviews
        ->map(function ($preview, $index) {
            $name = trim((string) ($preview['name'] ?? 'Topluluk'));
            $initial = collect(preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY))
                ->take(2)
                ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                ->implode('');

            return [
                'avatar' => $preview['avatar'] ?? null,
                'name' => $name,
                'initial' => $initial !== '' ? $initial : 'TU',
                'accent' => $index % 2 === 0 ? 'ca-1' : 'ca-2',
            ];
        })
        ->take(3)
        ->values();

    $showCommentPreviewAvatars = $commentsCount >= 3 && $commentPreviewPeople->isNotEmpty();
@endphp

@php
    $previewInitial = collect(preg_split('/\s+/', trim((string) ($latestCommentPreview['name'] ?? 'Topluluk')), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
    $previewInitial = $previewInitial !== '' ? $previewInitial : 'TU';
    $repostCreateUrl = $viewer ? route('blog.repost.create', ['post' => optional($postObj)->id ?? $postSlug]) : null;
    $summaryExpandedText = trim((string) ($resolvedExcerptExpanded !== '' ? $resolvedExcerptExpanded : ($resolvedExcerptRaw !== '' ? $resolvedExcerptRaw : $summaryText)));
    $hasFullPostContent = $contentBlocks->isNotEmpty() || trim($contentHtml) !== '' || $mediaItems->isNotEmpty();
    $summaryCollapsedSource = $summaryExpandedText !== '' ? $summaryExpandedText : $resolvedExcerptShort;
    $summaryCollapsedLength = \Illuminate\Support\Str::length($summaryCollapsedSource);
    $summaryHalfLength = $summaryCollapsedLength > 1 ? (int) ceil($summaryCollapsedLength * 0.5) : $summaryCollapsedLength;
    $summaryCollapsedText = $summaryCollapsedLength > $summaryHalfLength
        ? rtrim(\Illuminate\Support\Str::substr($summaryCollapsedSource, 0, $summaryHalfLength), " \t\n\r\0\x0B,.;:-") . '...'
        : $summaryCollapsedSource;
    // Buton sadece uzun yazılarda değil, bütün post kartlarında görünsün.
    // İçerik yoksa tıklama yine karta zarar vermez; varsa 2 satırdan tam içeriğe açılır.
    $summaryCanExpand = $summaryCollapsedText !== '' || $summaryExpandedText !== '' || $hasFullPostContent || $hasSourcePreview || $postUrl !== '#';
    $showExpandLink = true;
    $expandCollapsedLabel = 'Devamını oku';
    $expandExpandedLabel = 'Daha az göster';
    $showAuthorSubline = $createdMetaLabel !== '' || filled($postEditedAtLabel);
    $defaultHeroPlaceholder = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1280 736'><defs><linearGradient id='sky' x1='0' y1='0' x2='0' y2='1'><stop offset='0' stop-color='%2342464c'/><stop offset='0.5' stop-color='%235f625f'/><stop offset='1' stop-color='%23aaa396'/></linearGradient><linearGradient id='road' x1='0' y1='0' x2='0' y2='1'><stop offset='0' stop-color='%23555756'/><stop offset='1' stop-color='%23201f22'/></linearGradient><radialGradient id='lamp' cx='50%' cy='50%' r='50%'><stop offset='0' stop-color='%23ffe9b2' stop-opacity='0.95'/><stop offset='1' stop-color='%23ffe9b2' stop-opacity='0'/></radialGradient></defs><rect width='1280' height='736' fill='url(%23sky)'/><rect y='500' width='1280' height='236' fill='url(%23road)'/><path d='M0 505C144 472 266 465 402 474c147 10 264 57 422 49 174-9 264-60 456-43v256H0Z' fill='%23888881' opacity='0.33'/><rect x='860' y='132' width='18' height='390' rx='9' fill='%23383839'/><circle cx='869' cy='128' r='16' fill='%23ffc96c'/><circle cx='869' cy='128' r='88' fill='url(%23lamp)'/><rect x='773' y='198' width='112' height='228' rx='14' fill='%23d7ddd9'/><rect x='785' y='210' width='88' height='162' rx='6' fill='%238cb2bc'/><rect x='801' y='392' width='54' height='18' rx='9' fill='%234d5054'/><path d='M163 736 343 396l143 340Z' fill='%23343438'/><path d='M279 736 434 444l133 292Z' fill='%23262629'/><rect x='92' y='250' width='130' height='286' rx='10' fill='%23272a31'/><rect x='104' y='264' width='106' height='250' rx='6' fill='%2314181d'/><circle cx='158' cy='297' r='10' fill='%23b4b6b8'/><rect x='137' y='332' width='42' height='76' rx='4' fill='%23cfd0cf'/><path d='M0 602c156-44 307-61 468-47 177 16 348 56 487 52 117-4 216-34 325-78v207H0Z' fill='%23bcae90' opacity='0.28'/><path d='M119 576h1042' stroke='%23ebe6dc' stroke-opacity='0.35' stroke-width='3' stroke-dasharray='12 16'/></svg>";
    $heroDisplayImage = $heroImage !== '' ? $heroImage : $defaultHeroPlaceholder;
    $commentDisplayText = $latestCommentContent;
    $commentHasEllipsis = \Illuminate\Support\Str::endsWith($commentDisplayText, '...');
    if ($commentHasEllipsis) {
        $commentDisplayText = \Illuminate\Support\Str::replaceLast('...', '', $commentDisplayText);
    }
    $isBookmarked = (bool) (optional($postObj)->is_bookmarked ?? $postArr['is_bookmarked'] ?? false);
    $createdSublineLabel = $createdHuman !== '' ? $createdHuman : $createdMetaLabel;
    $bookmarkOutlineIcon = <<<'SVG'
<svg class="post-card__bookmark-icon" viewBox="0 0 24 24" width="1.2em" height="1.2em" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.5"><path class="ps-bookmark-shape" d="M21 16.09v-4.992c0-4.29 0-6.433-1.318-7.766C18.364 2 16.242 2 12 2S5.636 2 4.318 3.332S3 6.81 3 11.098v4.993c0 3.096 0 4.645.734 5.321c.35.323.792.526 1.263.58c.987.113 2.14-.907 4.445-2.946c1.02-.901 1.529-1.352 2.118-1.47c.29-.06.59-.06.88 0c.59.118 1.099.569 2.118 1.47c2.305 2.039 3.458 3.059 4.445 2.945c.47-.053.913-.256 1.263-.579c.734-.676.734-2.224.734-5.321Z"></path><path class="ps-bookmark-line" stroke-linecap="round" d="M15 6H9"></path></g></svg>
SVG;
    $bookmarkFilledIcon = <<<'SVG'
<svg class="post-card__bookmark-icon" viewBox="0 0 24 24" width="1.2em" height="1.2em" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.5"><path class="ps-bookmark-shape" d="M21 16.09v-4.992c0-4.29 0-6.433-1.318-7.766C18.364 2 16.242 2 12 2S5.636 2 4.318 3.332S3 6.81 3 11.098v4.993c0 3.096 0 4.645.734 5.321c.35.323.792.526 1.263.58c.987.113 2.14-.907 4.445-2.946c1.02-.901 1.529-1.352 2.118-1.47c.29-.06.59-.06.88 0c.59.118 1.099.569 2.118 1.47c2.305 2.039 3.458 3.059 4.445 2.945c.47-.053.913-.256 1.263-.579c.734-.676.734-2.224.734-5.321Z"></path><path class="ps-bookmark-line" stroke-linecap="round" d="M15 6H9"></path></g></svg>
SVG;
    $shareIcon = <<<'SVG'
<svg class="post-card__share-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" aria-hidden="true"><path fill="currentColor" d="m229.66 109.66l-48 48a8 8 0 0 1-11.32-11.32L204.69 112H128a88.1 88.1 0 0 0-88 88a8 8 0 0 1-16 0A104.11 104.11 0 0 1 128 96h76.69l-34.35-34.34a8 8 0 0 1 11.32-11.32l48 48a8 8 0 0 1 0 11.32"/></svg>
SVG;
    $reactionAddIcon = <<<'SVG'
<svg class="post-card__reaction-custom-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.5 3v5M21 5.5h-5m4.863 4.937a9 9 0 0 1-4.707 9.546a9 9 0 0 1-10.52-1.619a9 9 0 0 1-1.62-10.52a9 9 0 0 1 9.547-4.707m1.619 12.045a4.5 4.5 0 0 1-6.364 0M9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75S9.168 9 9.375 9s.375.336.375.75m-.375 0h.008v.015h-.008Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75s.168-.75.375-.75s.375.336.375.75m-.375 0h.008v.015h-.008z"/></svg>
SVG;
    $editedInfoIcon = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497zM15 5l4 4"></path></svg>
SVG;
    $publishedPopoverLabel = $postPublishedMoment ? $postPublishedMoment->translatedFormat('d F Y H:i') : $createdMetaTitle;
    $updatedPopoverLabel = $postEditedMoment ? $postEditedMoment->translatedFormat('d F Y H:i') : $postEditedAtLabel;

    $readPostCardHoverText = function ($model, array $keys): string {
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

    $postCardHoverAuthor = $postAuthor;
    try {
        if ($postCardHoverAuthor && method_exists($postCardHoverAuthor, 'fresh') && method_exists($postCardHoverAuthor, 'getKey') && $postCardHoverAuthor->getKey()) {
            $freshPostCardAuthor = $postCardHoverAuthor->fresh();
            if ($freshPostCardAuthor) {
                $postCardHoverAuthor = $freshPostCardAuthor;
            }
        }

        if ($postCardHoverAuthor && method_exists($postCardHoverAuthor, 'profile') && method_exists($postCardHoverAuthor, 'loadMissing')) {
            $postCardHoverAuthor->loadMissing('profile');
        }
    } catch (\Throwable $e) {
        $postCardHoverAuthor = $postAuthor;
    }

    $postCardHoverCategory = $postCategory;
    try {
        if ($postCardHoverCategory && method_exists($postCardHoverCategory, 'fresh') && method_exists($postCardHoverCategory, 'getKey') && $postCardHoverCategory->getKey()) {
            $freshPostCardCategory = $postCardHoverCategory->fresh();
            if ($freshPostCardCategory) {
                $postCardHoverCategory = $freshPostCardCategory;
            }
        }
    } catch (\Throwable $e) {
        $postCardHoverCategory = $postCategory;
    }

    $postCardAuthorUsername = trim((string) (optional($postCardHoverAuthor)->username ?? optional($postAuthor)->username ?? data_get($postArr, 'author.username', '')));
    $postCardAuthorBio = $readPostCardHoverText($postCardHoverAuthor, [
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
    $postCardAuthorBio = $postCardAuthorBio !== '' ? \Illuminate\Support\Str::limit($postCardAuthorBio, 130) : '';
    $postCardAuthorCover = optional($postCardHoverAuthor)->cover_photo_url
        ?? optional($postCardHoverAuthor)->cover_image_url
        ?? optional($postCardHoverAuthor)->banner_url
        ?? optional($postCardHoverAuthor)->cover_photo
        ?? null;

    $postCardCategoryCover = optional($postCardHoverCategory)->cover_image_url
        ?? optional($postCardHoverCategory)->cover_photo_url
        ?? optional($postCardHoverCategory)->banner_url
        ?? optional($postCardHoverCategory)->cover_image
        ?? optional($postCardHoverCategory)->cover
        ?? null;
    $postCardCategoryDescription = $readPostCardHoverText($postCardHoverCategory, [
        'description',
        'bio',
        'excerpt',
        'summary',
        'about',
    ]);
    $postCardCategoryDescription = $postCardCategoryDescription !== '' ? \Illuminate\Support\Str::limit($postCardCategoryDescription, 110) : '';

    $postCardLoginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');
    $postCardIsOwnAuthor = (bool) ($viewer && $postCardHoverAuthor && method_exists($viewer, 'getKey') && method_exists($postCardHoverAuthor, 'getKey') && (int) $viewer->getKey() === (int) $postCardHoverAuthor->getKey());
    $postCardIsFollowingAuthor = false;

    if ($viewer && $postCardHoverAuthor && !$postCardIsOwnAuthor) {
        $explicitPostCardFollowState = optional($postCardHoverAuthor)->is_followed_by_viewer
            ?? optional($postCardHoverAuthor)->viewer_has_followed
            ?? optional($postCardHoverAuthor)->is_following
            ?? null;

        if ($explicitPostCardFollowState !== null) {
            $postCardIsFollowingAuthor = (bool) $explicitPostCardFollowState;
        } elseif (method_exists($viewer, 'isFollowing')) {
            try {
                $postCardIsFollowingAuthor = (bool) $viewer->isFollowing($postCardHoverAuthor);
            } catch (\Throwable $e) {
                $postCardIsFollowingAuthor = false;
            }
        } elseif (method_exists($postCardHoverAuthor, 'followers') && method_exists($viewer, 'getKey')) {
            try {
                $postCardIsFollowingAuthor = (bool) $postCardHoverAuthor->followers()->whereKey($viewer->getKey())->exists();
            } catch (\Throwable $e) {
                $postCardIsFollowingAuthor = false;
            }
        }
    }

    $postCardFollowAction = null;
    if ($postCardHoverAuthor && !$postCardIsOwnAuthor) {
        $postCardFollowRouteCandidates = [
            'users.follow',
            'user.follow',
            'users.follow.store',
            'follow.user',
            'profile.follow',
            'followers.store',
        ];

        foreach ($postCardFollowRouteCandidates as $routeName) {
            if (!\Illuminate\Support\Facades\Route::has($routeName)) {
                continue;
            }

            $routeParameterSets = [
                ['user' => $postCardHoverAuthor],
                ['user' => $postCardAuthorUsername !== '' ? $postCardAuthorUsername : optional($postCardHoverAuthor)->id],
                ['username' => $postCardAuthorUsername],
                ['id' => optional($postCardHoverAuthor)->id],
                $postCardHoverAuthor,
            ];

            foreach ($routeParameterSets as $routeParameters) {
                try {
                    $postCardFollowAction = route($routeName, $routeParameters);
                    break 2;
                } catch (\Throwable $e) {
                    $postCardFollowAction = null;
                }
            }
        }
    }

    if (!$postCardFollowAction && $postCardHoverAuthor && !$postCardIsOwnAuthor) {
        $postCardAuthorFollowKey = $postCardAuthorUsername !== '' ? $postCardAuthorUsername : (string) optional($postCardHoverAuthor)->id;
        if ($postCardAuthorFollowKey !== '') {
            $postCardFollowAction = url('/users/' . rawurlencode($postCardAuthorFollowKey) . '/follow');
        }
    }

    $postCardCanFollowAuthor = (bool) ($viewer && $postCardHoverAuthor && !$postCardIsOwnAuthor && $postCardFollowAction);
    $postCardFollowButtonLabel = $postCardIsFollowingAuthor ? 'Takip ediliyor' : 'Takip et';

@endphp

<article
    class="post-card is-preloading"
    id="{{ $cardShellId }}"
    data-post-card-shell
    data-post-url="{{ $replyShareUrl }}"
    data-post-title="{{ $title }}"
    data-post-view-url="{{ $viewAction ?? '' }}"
    data-post-view-recorded="false"
>
    <div class="post-header" id="post-header">
        <div class="author-block" id="author-block" data-media-type="banani-button">
            <div class="avatar-wrap">
                @if($authorAvatar)
                    <img
                        src="{{ $authorAvatar }}"
                        alt="{{ $authorAvatarAlt !== '' ? $authorAvatarAlt : $authorName }}"
                        class="author-avatar"
                        loading="lazy"
                        decoding="async"
                    />
                @else
                    <span class="author-avatar author-avatar--fallback author-avatar-fallback">{{ $authorInitials }}</span>
                @endif

                @if($hasCategory)
                    @if($categoryUrl)
                        <a href="{{ $categoryUrl }}" class="category-badge" aria-label="{{ $topicLabel }}">
                            @if($categoryAvatar)
                                <img src="{{ $categoryAvatar }}" alt="{{ $categoryAvatarAlt !== '' ? $categoryAvatarAlt : $topicLabel }}" class="category-badge__image" loading="lazy" decoding="async" />
                            @else
                                <span class="category-badge__fallback">{{ $categoryBadgeText }}</span>
                            @endif
                        </a>
                    @else
                        <span class="category-badge" aria-label="{{ $topicLabel }}">
                            @if($categoryAvatar)
                                <img src="{{ $categoryAvatar }}" alt="{{ $categoryAvatarAlt !== '' ? $categoryAvatarAlt : $topicLabel }}" class="category-badge__image" loading="lazy" decoding="async" />
                            @else
                                <span class="category-badge__fallback">{{ $categoryBadgeText }}</span>
                            @endif
                        </span>
                    @endif
                @endif
            </div>

            <div class="author-info" id="author-meta">
                <div class="author-name-row" id="author-name-row">
                    <span class="ps-hover-zone ps-hover-zone--inline ps-hover-zone--author-name" tabindex="0">
                        @if($authorUrl !== '#')
                            <a href="{{ $authorUrl }}" class="author-name">{{ $authorName }}</a>
                        @else
                            <span class="author-name">{{ $authorName }}</span>
                        @endif

                        <span class="ps-hover-card ps-hover-card--user ps-hover-card--inline" role="tooltip">
                            <span class="ps-hover-card-cover">
                                @if($postCardAuthorCover)
                                    <img src="{{ $postCardAuthorCover }}" alt="{{ $authorName }} kapak görseli">
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
                                    @if($postCardAuthorUsername !== '')
                                        <span class="ps-hover-card-subtitle">{{ '@' . $postCardAuthorUsername }}</span>
                                    @endif
                                </span>
                            </span>
                            @if($postCardAuthorBio !== '')
                                <span class="ps-hover-card-description">{{ $postCardAuthorBio }}</span>
                            @endif
                            <span class="ps-hover-card-actions">
                                @if($postCardCanFollowAuthor)
                                    <form method="POST" action="{{ $postCardFollowAction }}" class="ps-hover-card-follow-form">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="ps-hover-card-follow"
                                            @if($postCardIsFollowingAuthor) disabled aria-disabled="true" @endif
                                        >
                                            {{ $postCardFollowButtonLabel }}
                                        </button>
                                    </form>
                                @elseif(!$viewer && $postCardHoverAuthor)
                                    <a href="{{ $postCardLoginUrl }}" class="ps-hover-card-follow ps-hover-card-follow--login">Takip et</a>
                                @endif

                                @if($authorUrl !== '#')
                                    <a href="{{ $authorUrl }}" class="ps-hover-card-link">Profili görüntüle</a>
                                @endif
                            </span>
                        </span>
                    </span>

                    @if($postAuthor)
                        <x-verification-badge :user="$postAuthor" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" />
                    @endif
                </div>

                @if($showAuthorSubline)
                    <div class="author-subline" id="author-subline">
                        @if($hasCategory)
                            <span class="ps-hover-zone ps-hover-zone--inline ps-hover-zone--category-name" tabindex="0">
                                @if($categoryUrl)
                                    <a href="{{ $categoryUrl }}" class="author-subline__topic">{{ $topicLabel }}</a>
                                @else
                                    <span class="author-subline__topic">{{ $topicLabel }}</span>
                                @endif

                                <span class="ps-hover-card ps-hover-card--category ps-hover-card--inline" role="tooltip">
                                    <span class="ps-hover-card-cover ps-hover-card-cover--category">
                                        @if($postCardCategoryCover)
                                            <img src="{{ $postCardCategoryCover }}" alt="{{ $topicLabel }} kapak görseli">
                                        @endif
                                    </span>
                                    <span class="ps-hover-card-main">
                                        <span class="ps-hover-card-avatar ps-hover-card-avatar--category">
                                            @if($categoryAvatar)
                                                <img src="{{ $categoryAvatar }}" alt="{{ $topicLabel }}">
                                            @else
                                                {{ $categoryBadgeText }}
                                            @endif
                                        </span>
                                        <span class="ps-hover-card-content">
                                            <span class="ps-hover-card-title">{{ $topicLabel }}</span>
                                            <span class="ps-hover-card-subtitle">Kategori</span>
                                        </span>
                                    </span>
                                    @if($postCardCategoryDescription !== '')
                                        <span class="ps-hover-card-description">{{ $postCardCategoryDescription }}</span>
                                    @endif
                                    @if($categoryUrl)
                                        <a href="{{ $categoryUrl }}" class="ps-hover-card-link">Kategoriyi görüntüle</a>
                                    @endif
                                </span>
                            </span>
                        @endif

                        @if($createdSublineLabel !== '')
                            <span class="author-subline__item">
                                <time class="post-time time-text" datetime="{{ $createdIso }}" title="{{ $postPublishedMoment ? $postPublishedMoment->translatedFormat('d M Y H:i') : $createdMetaTitle }}">{{ $createdSublineLabel }}</time>
                            </span>
                        @endif

                        @if($isPostEdited && $postEditedAtLabel)
                            <span class="author-subline__edit-wrap">
                                <button
                                    type="button"
                                    class="author-subline__item author-subline__item--edited author-subline__edit-toggle"
                                    data-post-card-edit-toggle
                                    aria-expanded="false"
                                    aria-controls="{{ $editToggleId }}"
                                    aria-label="Update details"
                                >
                                    {!! $editedInfoIcon !!}
                                </button>
                                <span id="{{ $editToggleId }}" class="author-subline__edit-popover" data-post-card-edit-label hidden>
                                    <span class="author-subline__edit-row">
                                        <span class="author-subline__edit-key">Duzenlendi</span>
                                        <span class="author-subline__edit-value">{{ $updatedPopoverLabel }}</span>
                                    </span>
                                    @if($postEditedReason !== '')
                                        <span class="author-subline__edit-row">
                                            <span class="author-subline__edit-key">Neden</span>
                                            <span class="author-subline__edit-value">{{ $postEditedReason }}</span>
                                        </span>
                                    @endif
                                </span>
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if($viewer)
            <div class="og-action-wrap relative z-[999999] inline-flex overflow-visible" data-og-action-wrap>
                <button
                    type="button"
                    class="flex h-[30px] w-[30px] items-center justify-center gap-[3px] rounded-full border border-gray-200 bg-gray-100 p-0 transition hover:border-gray-300 hover:bg-gray-200"
                    data-og-action-trigger
                    aria-haspopup="menu"
                    aria-expanded="false"
                    aria-label="Menüyü aç"
                >
                    <span class="block h-[3px] w-[3px] rounded-full bg-gray-500"></span>
                    <span class="block h-[3px] w-[3px] rounded-full bg-gray-500"></span>
                    <span class="block h-[3px] w-[3px] rounded-full bg-gray-500"></span>
                </button>

                <div
                    class="og-action-menu shadcn-menu fixed z-[999999999] hidden w-[205px] rounded-2xl border border-gray-200 bg-white p-[6px] shadow-[0_2px_7px_rgba(0,0,0,0.025)] max-sm:w-[198px]"
                    data-og-action-menu
                    style="width: 192px !important; min-width: 192px !important; max-width: min(192px, calc(100vw - 24px)) !important; box-sizing: border-box !important; padding: 8px !important; overflow: hidden !important; border: 1px solid #e4e4e7 !important; border-radius: 16px !important; background: #ffffff !important; color: #18181b !important; box-shadow: 0 1px 2px rgba(0,0,0,.05), 0 8px 24px rgba(15,23,42,.08) !important; filter: none !important;"
                >
                    <button
                        type="button"
                        class="group flex h-10 w-full items-center gap-3 rounded-[11px] bg-transparent px-3 text-left text-sm font-normal text-gray-600 transition hover:bg-gray-100"
                        data-post-card-copy
                    >
                        <iconify-icon icon="lucide:link-2" class="h-[17px] w-[17px] min-w-[17px] text-gray-400 transition group-hover:text-blue-600"></iconify-icon>
                        <span class="font-normal text-gray-600">Linki kopyala</span>
                    </button>

                    @if($repostCreateUrl)
                        <a
                            href="{{ $repostCreateUrl }}"
                            class="group flex h-10 w-full items-center gap-3 rounded-[11px] bg-transparent px-3 text-left text-sm font-normal text-gray-600 transition hover:bg-gray-100"
                        >
                            <iconify-icon icon="lucide:repeat-2" class="h-[17px] w-[17px] min-w-[17px] text-gray-400 transition group-hover:text-violet-600"></iconify-icon>
                            <span class="font-normal text-gray-600">Yeniden paylaş</span>
                        </a>
                    @endif

                    @if($isOwnPost)
                        <form method="POST" action="{{ $pinAction }}">
                            @csrf
                            <button
                                type="submit"
                                class="group flex h-10 w-full items-center gap-3 rounded-[11px] bg-transparent px-3 text-left text-sm font-normal text-gray-600 transition hover:bg-gray-100"
                            >
                                <iconify-icon icon="{{ $isPinned ? 'lucide:pin-off' : 'lucide:pin' }}" class="h-[17px] w-[17px] min-w-[17px] text-gray-400 transition group-hover:text-teal-700"></iconify-icon>
                                <span class="font-normal text-gray-600">{{ $isPinned ? 'Sabitlemeyi kaldır' : 'Sabit' }}</span>
                            </button>
                        </form>
                    @endif

                    @if($bookmarkAction && $viewer)
                        <form method="POST" action="{{ $bookmarkAction }}">
                            @csrf
                            <button
                                type="submit"
                                class="group flex h-10 w-full items-center gap-3 rounded-[11px] bg-transparent px-3 text-left text-sm font-normal text-gray-600 transition hover:bg-gray-100"
                            >
                                <iconify-icon icon="{{ $isBookmarked ? 'lucide:bookmark-check' : 'lucide:bookmark' }}" class="h-[17px] w-[17px] min-w-[17px] text-gray-400 transition group-hover:text-gray-900"></iconify-icon>
                                <span class="font-normal text-gray-600">{{ $isBookmarked ? 'Kaydedildi' : 'Bookmark' }}</span>
                            </button>
                        </form>
                    @endif

                    @if($isOwnPost)
                        <div class="og-action-divider my-1.5 mx-1 h-px bg-gray-200"></div>

                        <a
                            href="{{ $editUrl }}"
                            class="group flex h-10 w-full items-center gap-3 rounded-[11px] bg-transparent px-3 text-left text-sm font-normal text-gray-600 transition hover:bg-gray-100"
                        >
                            <iconify-icon icon="lucide:square-pen" class="h-[17px] w-[17px] min-w-[17px] text-gray-400 transition group-hover:text-amber-700"></iconify-icon>
                            <span class="font-normal text-gray-600">Düzenle</span>
                        </a>

                        <form method="POST" action="{{ $deleteAction }}" onsubmit="return confirm('Bu gonderi silinsin mi?');">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="group flex h-10 w-full items-center gap-3 rounded-[11px] bg-transparent px-3 text-left text-sm font-normal text-gray-600 transition hover:bg-gray-100"
                            >
                                <iconify-icon icon="lucide:trash-2" class="h-[17px] w-[17px] min-w-[17px] text-gray-400 transition group-hover:text-red-600"></iconify-icon>
                                <span class="font-normal text-gray-600">Sil</span>
                            </button>
                        </form>
                    @elseif($reportUrl)
                        <a
                            href="{{ $reportUrl }}"
                            class="group flex h-10 w-full items-center gap-3 rounded-[11px] bg-transparent px-3 text-left text-sm font-normal text-gray-600 transition hover:bg-gray-100"
                        >
                            <iconify-icon icon="lucide:flag" class="h-[17px] w-[17px] min-w-[17px] text-gray-400 transition group-hover:text-orange-600"></iconify-icon>
                            <span class="font-normal text-gray-600">Şikayet et</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <h2 class="post-title" id="post-title">
        @if($postUrl !== '#')
            <a href="{{ $postUrl }}" class="post-title__link">{{ $title }}</a>
        @else
            {{ $title }}
        @endif
    </h2>

    @if($hasMediaCarousel)
        <div class="post-card__media-wrap{{ $renderHeroNsfwBlur ? ' post-card__media-wrap--nsfw-blur' : '' }}" data-post-card-media-wrap>
            <div class="post-card__media-scroller" data-post-card-media-scroller>
                @foreach($mediaItems as $mediaIndex => $mediaItem)
                    @php
                        $mediaType = $mediaItem['type'] ?? 'image';
                        $mediaSrc = (string) ($mediaItem['src'] ?? '');
                        $mediaThumb = trim((string) ($mediaItem['thumb'] ?? ''));
                        $mediaAlt = (string) ($mediaItem['alt'] ?? ($title . ' medya'));
                        $mediaLabel = (string) ($mediaItem['label'] ?? ($mediaType === 'video' ? 'Video' : 'Gorsel'));
                    @endphp
                    <article class="post-card__media-slide" data-post-card-media-slide data-media-type="{{ $mediaType }}">
                        @if($postUrl !== '#')
                            <a href="{{ $postUrl }}" class="post-card__media-link" draggable="false">
                                @if($mediaType === 'video')
                                    <div class="post-card__media-frame post-card__media-frame--video">
                                        @if($mediaThumb !== '')
                                            <img
                                                src="{{ $mediaThumb }}"
                                                alt="{{ $mediaAlt }}"
                                                class="post-card__media-image"
                                                loading="lazy"
                                                decoding="async"
                                                draggable="false"
                                            />
                                        @elseif($mediaSrc !== '')
                                            <iframe
                                                src="{{ $mediaSrc }}"
                                                title="{{ $mediaAlt }}"
                                                class="post-card__media-embed"
                                                loading="lazy"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen
                                            ></iframe>
                                        @else
                                            <div class="post-card__media-video-fallback">{{ $mediaLabel }}</div>
                                        @endif
                                        <span class="post-card__media-badge">{{ $mediaLabel }}</span>
                                        <span class="post-card__media-play" aria-hidden="true">
                                            <iconify-icon icon="lucide:play"></iconify-icon>
                                        </span>
                                    </div>
                                @else
                                    <div class="post-card__media-frame">
                                        <img
                                            src="{{ $mediaSrc }}"
                                            alt="{{ $mediaAlt }}"
                                            class="post-card__media-image"
                                            loading="{{ $mediaIndex === 0 ? $mediaLoading : 'lazy' }}"
                                            fetchpriority="{{ $mediaIndex === 0 ? $mediaFetchPriority : 'auto' }}"
                                            decoding="async"
                                            draggable="false"
                                        />
                                    </div>
                                @endif
                            </a>
                        @else
                            <div class="post-card__media-link" draggable="false">
                                @if($mediaType === 'video')
                                    <div class="post-card__media-frame post-card__media-frame--video">
                                        @if($mediaThumb !== '')
                                            <img
                                                src="{{ $mediaThumb }}"
                                                alt="{{ $mediaAlt }}"
                                                class="post-card__media-image"
                                                loading="lazy"
                                                decoding="async"
                                                draggable="false"
                                            />
                                        @elseif($mediaSrc !== '')
                                            <iframe
                                                src="{{ $mediaSrc }}"
                                                title="{{ $mediaAlt }}"
                                                class="post-card__media-embed"
                                                loading="lazy"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen
                                            ></iframe>
                                        @else
                                            <div class="post-card__media-video-fallback">{{ $mediaLabel }}</div>
                                        @endif
                                        <span class="post-card__media-badge">{{ $mediaLabel }}</span>
                                        <span class="post-card__media-play" aria-hidden="true">
                                            <iconify-icon icon="lucide:play"></iconify-icon>
                                        </span>
                                    </div>
                                @else
                                    <div class="post-card__media-frame">
                                        <img
                                            src="{{ $mediaSrc }}"
                                            alt="{{ $mediaAlt }}"
                                            class="post-card__media-image"
                                            loading="{{ $mediaIndex === 0 ? $mediaLoading : 'lazy' }}"
                                            fetchpriority="{{ $mediaIndex === 0 ? $mediaFetchPriority : 'auto' }}"
                                            decoding="async"
                                            draggable="false"
                                        />
                                    </div>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
            @if($renderHeroNsfwBlur)
                <div class="hero-wrap__overlay" aria-label="NSFW icerik uyarisi">
                    <div class="hero-wrap__overlay-actions">
                        <span class="hero-wrap__overlay-label">NSFW +18</span>

                        <a
                            href="{{ url('/dashboard/preferences') }}"
                            class="hero-wrap__overlay-button"
                            data-nsfw-preferences-link
                        >
                            <span class="hero-wrap__overlay-button-icon" aria-hidden="true">
                                <iconify-icon icon="lucide:settings"></iconify-icon>
                            </span>
                            <span class="hero-wrap__overlay-button-text">Ayarları Kapat</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($summaryCollapsedText !== '')
        <div class="post-summary-shell is-collapsed" data-post-card-summary-shell>
            <p class="post-summary is-collapsed" id="{{ $summaryToggleId }}" data-post-card-summary>{{ $summaryCollapsedText }}</p>
            <template data-post-card-summary-collapsed>{{ $summaryCollapsedText }}</template>
            <template data-post-card-summary-expanded>{{ $summaryExpandedText }}</template>
        </div>
    @endif


    @if($hasFullPostContent)
        <div class="post-card__full-content" data-post-card-full-content hidden>
            @if($contentBlocks->isNotEmpty())
                @foreach($contentBlocks as $fullBlockIndex => $fullBlock)
                    @continue(!is_array($fullBlock))
                    @php
                        $fullBlockType = (string) ($fullBlock['type'] ?? '');
                        $fullBlockData = is_array($fullBlock['data'] ?? null) ? $fullBlock['data'] : [];
                        $fullBlockImages = $extractBlockImages($fullBlock);
                        $fullBlockEmbed = $extractBlockVideoEmbed($fullBlock);
                        $fullBlockText = $normalizePostPlainText($fullBlockData['text'] ?? $fullBlockData['caption'] ?? $fullBlockData['title'] ?? '');
                    @endphp

                    @if($fullBlockEmbed)
                        <div class="post-card__full-embed">
                            <iframe
                                src="{{ $fullBlockEmbed }}"
                                title="{{ $title }} video"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    @endif

                    @if($fullBlockType === 'header' && $fullBlockText !== '')
                        <h3 class="post-card__full-heading">{{ $fullBlockText }}</h3>
                    @elseif($fullBlockType === 'quote' && $fullBlockText !== '')
                        <blockquote class="post-card__full-quote">{{ $fullBlockText }}</blockquote>
                    @elseif($fullBlockType === 'paragraph' && $fullBlockText !== '' && !$fullBlockEmbed)
                        <p class="post-card__full-paragraph">{!! nl2br(e($fullBlockText)) !!}</p>
                    @elseif($fullBlockType === 'list')
                        @php
                            $listItems = collect($fullBlockData['items'] ?? [])->flatten()->map(fn ($item) => $normalizePostPlainText($item))->filter()->values();
                            $listStyle = (string) ($fullBlockData['style'] ?? 'unordered');
                        @endphp
                        @if($listItems->isNotEmpty())
                            @if($listStyle === 'ordered')
                                <ol class="post-card__full-list post-card__full-list--ordered">
                                    @foreach($listItems as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ol>
                            @else
                                <ul class="post-card__full-list">
                                    @foreach($listItems as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                    @elseif($fullBlockType === 'checklist')
                        @php
                            $checkItems = collect($fullBlockData['items'] ?? [])->map(function ($item) use ($normalizePostPlainText) {
                                return [
                                    'text' => $normalizePostPlainText(is_array($item) ? ($item['text'] ?? '') : $item),
                                    'checked' => (bool) (is_array($item) ? ($item['checked'] ?? false) : false),
                                ];
                            })->filter(fn ($item) => $item['text'] !== '')->values();
                        @endphp
                        @if($checkItems->isNotEmpty())
                            <ul class="post-card__full-checklist">
                                @foreach($checkItems as $item)
                                    <li>
                                        <span class="post-card__full-check">{{ $item['checked'] ? '✓' : '' }}</span>
                                        <span>{{ $item['text'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif($fullBlockType === 'table')
                        @php
                            $tableRows = collect($fullBlockData['content'] ?? [])->map(fn ($row) => is_array($row) ? $row : [])->filter()->values();
                        @endphp
                        @if($tableRows->isNotEmpty())
                            <div class="post-card__full-table-wrap">
                                <table class="post-card__full-table">
                                    @foreach($tableRows as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td>{{ $normalizePostPlainText($cell) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        @endif
                    @elseif(!in_array($fullBlockType, ['image', 'gallery', 'carousel', 'slider', 'socialEmbed', 'embed'], true) && $fullBlockText !== '')
                        <p class="post-card__full-paragraph">{!! nl2br(e($fullBlockText)) !!}</p>
                    @endif

                    @if(!empty($fullBlockImages))
                        <div class="post-card__full-media-grid {{ count($fullBlockImages) > 1 ? 'post-card__full-media-grid--multi' : '' }}">
                            @foreach($fullBlockImages as $fullImageIndex => $fullImageUrl)
                                <figure class="post-card__full-figure">
                                    <img src="{{ $fullImageUrl }}" alt="{{ $title }} görsel {{ $fullImageIndex + 1 }}" loading="lazy" decoding="async" />
                                    @if(trim((string) ($fullBlockData['caption'] ?? '')) !== '')
                                        <figcaption>{{ $normalizePostPlainText($fullBlockData['caption']) }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @else
                @if($fullPostText !== '')
                    <p class="post-card__full-paragraph">{!! nl2br(e($fullPostText)) !!}</p>
                @endif

                @if($htmlContentImageUrls->isNotEmpty())
                    <div class="post-card__full-media-grid {{ $htmlContentImageUrls->count() > 1 ? 'post-card__full-media-grid--multi' : '' }}">
                        @foreach($htmlContentImageUrls as $fullImageIndex => $fullImageUrl)
                            <figure class="post-card__full-figure">
                                <img src="{{ $fullImageUrl }}" alt="{{ $title }} görsel {{ $fullImageIndex + 1 }}" loading="lazy" decoding="async" />
                            </figure>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    @endif

    @if($hasSourcePreview)
        @php
            $sourceDisplayName = $linkPreviewHost !== ''
                ? $linkPreviewHost
                : trim((string) data_get($linkPreview, 'site_name', ''));
            $sourceDisplayName = $sourceDisplayName !== '' ? $sourceDisplayName : 'Harici kaynak';
        @endphp
        <a
            class="post-card__source"
            href="{{ $linkPreviewUrl }}"
            target="_blank"
            rel="nofollow noopener noreferrer"
            data-post-card-source
            @if($showExpandLink) hidden @endif
            aria-label="Kaynağı aç: {{ $sourceDisplayName }}"
        >
            <span class="post-card__source-copy">
                <span class="post-card__source-label">Kaynak</span>
                <span class="post-card__source-domain-row">
                    @if($linkPreviewFavicon !== '')
                        <img
                            class="post-card__source-favicon"
                            src="{{ $linkPreviewFavicon }}"
                            alt=""
                            loading="lazy"
                            decoding="async"
                            referrerpolicy="no-referrer"
                            onerror="this.style.display='none'"
                        >
                    @endif
                    <span class="post-card__source-domain">{{ $sourceDisplayName }}</span>
                </span>
            </span>
            <span class="post-card__source-icon" aria-hidden="true">
                <iconify-icon icon="lucide:arrow-up-right"></iconify-icon>
            </span>
        </a>
    @endif

    @if($showExpandLink)
        <button
            type="button"
            class="expand-link"
            id="expand-link"
            data-media-type="banani-button"
            data-post-card-expand
            data-post-card-expand-collapsed-label="{{ $expandCollapsedLabel }}"
            data-post-card-expand-expanded-label="{{ $expandExpandedLabel }}"
            aria-expanded="false"
            aria-controls="{{ $summaryToggleId }}"
        >
            <span data-post-card-expand-label>{{ $expandCollapsedLabel }}</span>
            <span class="post-card__expand-icon" aria-hidden="true">
                <iconify-icon icon="lucide:chevron-down" style="font-size: 20px; color: currentColor"></iconify-icon>
            </span>
        </button>
    @endif

    @if($postTags->isNotEmpty())
        <div class="post-card__tags" aria-label="Etiketler">
            @foreach($postTags as $tag)
                @php
                    $tagName = trim((string) (data_get($tag, 'name') ?? ''));
                    $tagSlug = trim((string) (data_get($tag, 'slug') ?? $tagName));
                @endphp
                @if($tagName !== '')
                    <a class="post-card__tag" href="{{ route('blog.index', ['tag' => $tagSlug]) }}">#{{ $tagName }}</a>
                @endif
            @endforeach
        </div>
    @endif

    @if($hasReactionStrip)
        <div class="reactions-row reaction-row" id="reaction-row">
            @foreach($visibleReactionPills as $reaction)
                @php
                    $reactionLabel = (string) ($reaction['label'] ?? 'Tepki');
                    $reactionIconHtml = $renderReactionIcon($reaction['icon'] ?? null, $reactionLabel);
                @endphp
                <div class="reaction-item" data-media-type="banani-button" title="{{ $reactionLabel }}">
                    <span class="reaction-emoji reaction-emoji--html">{!! $reactionIconHtml !!}</span>
                    <span class="reaction-count">{{ number_format((int) ($reaction['count'] ?? 0)) }}</span>
                </div>
            @endforeach

            @if($reactionOverflowCount > 0)
                <button type="button" class="more-pill" data-media-type="banani-button">+{{ $reactionOverflowCount }}</button>
            @endif

            <div class="post-card__reaction-wrap" data-post-card-reaction-wrap data-post-card-reaction-id="{{ $reactionRootId }}">
                <button
                    type="button"
                    class="smiley-btn reaction-add"
                    data-media-type="banani-button"
                    data-post-card-reaction-trigger
                    aria-expanded="false"
                    aria-haspopup="menu"
                    aria-controls="{{ $reactionRootId }}"
                    aria-label="Tepki ver"
                >
                    <div class="post-card__inline-icon">
                        {!! $reactionAddIcon !!}
                    </div>
                </button>

                <div class="post-card__reaction-menu" id="{{ $reactionRootId }}" data-post-card-reaction-menu hidden>
                    <div class="post-card__reaction-menu-title">Tepkiler</div>
                    @foreach(collect($reactionTypesAll)->take(1000) as $reactionType)
                        @php
                            $reactionTypeId = $reactionType['id'] ?? ($reactionType->id ?? null);
                            $reactionTypeLabel = (string) ($reactionType['label'] ?? ($reactionType->label ?? 'Tepki'));
                            $reactionTypeIcon = $renderReactionIcon(
                                $reactionType['gif_url'] ?? ($reactionType->gif_url ?? ($reactionType['emoji'] ?? ($reactionType->emoji ?? null))),
                                $reactionTypeLabel
                            );
                        @endphp
                        @if($viewer && $reactionAction)
                            <form method="POST" action="{{ $reactionAction }}" class="post-card__reaction-form">
                                @csrf
                                @if($reactionTypeId)
                                    <input type="hidden" name="reaction_type_id" value="{{ $reactionTypeId }}">
                                @endif
                                <button type="submit" class="post-card__reaction-option" aria-label="{{ $reactionTypeLabel }}">
                                    <span class="reaction-emoji reaction-emoji--html" style="display:inline-flex!important;width:24px!important;height:24px!important;min-width:24px!important;font-size:24px!important;line-height:24px!important">{!! $reactionTypeIcon !!}</span>
                                </button>
                            </form>
                        @elseif(\Illuminate\Support\Facades\Route::has('login'))
                            <a href="{{ route('login') }}" class="post-card__reaction-option" aria-label="{{ $reactionTypeLabel }}">
                                <span class="reaction-emoji reaction-emoji--html" style="display:inline-flex!important;width:24px!important;height:24px!important;min-width:24px!important;font-size:24px!important;line-height:24px!important">{!! $reactionTypeIcon !!}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @elseif($viewer || \Illuminate\Support\Facades\Route::has('login'))
        <div class="reactions-row reaction-row" id="reaction-row">
            <div class="post-card__reaction-wrap" data-post-card-reaction-wrap data-post-card-reaction-id="{{ $reactionRootId }}">
                <button
                    type="button"
                    class="smiley-btn reaction-add"
                    data-media-type="banani-button"
                    data-post-card-reaction-trigger
                    aria-expanded="false"
                    aria-haspopup="menu"
                    aria-controls="{{ $reactionRootId }}"
                    aria-label="Tepki ver"
                >
                    <div class="post-card__inline-icon">
                        {!! $reactionAddIcon !!}
                    </div>
                </button>

                <div class="post-card__reaction-menu" id="{{ $reactionRootId }}" data-post-card-reaction-menu hidden>
                    <div class="post-card__reaction-menu-title">Tepkiler</div>
                    @foreach(collect($reactionTypesAll)->take(1000) as $reactionType)
                        @php
                            $reactionTypeId = $reactionType['id'] ?? ($reactionType->id ?? null);
                            $reactionTypeLabel = (string) ($reactionType['label'] ?? ($reactionType->label ?? 'Tepki'));
                            $reactionTypeIcon = $renderReactionIcon(
                                $reactionType['gif_url'] ?? ($reactionType->gif_url ?? ($reactionType['emoji'] ?? ($reactionType->emoji ?? null))),
                                $reactionTypeLabel
                            );
                        @endphp
                        @if($viewer && $reactionAction)
                            <form method="POST" action="{{ $reactionAction }}" class="post-card__reaction-form">
                                @csrf
                                @if($reactionTypeId)
                                    <input type="hidden" name="reaction_type_id" value="{{ $reactionTypeId }}">
                                @endif
                                <button type="submit" class="post-card__reaction-option" aria-label="{{ $reactionTypeLabel }}">
                                    <span class="reaction-emoji reaction-emoji--html" style="display:inline-flex!important;width:24px!important;height:24px!important;min-width:24px!important;font-size:24px!important;line-height:24px!important">{!! $reactionTypeIcon !!}</span>
                                </button>
                            </form>
                        @elseif(\Illuminate\Support\Facades\Route::has('login'))
                            <a href="{{ route('login') }}" class="post-card__reaction-option" aria-label="{{ $reactionTypeLabel }}">
                                <span class="reaction-emoji reaction-emoji--html" style="display:inline-flex!important;width:24px!important;height:24px!important;min-width:24px!important;font-size:24px!important;line-height:24px!important">{!! $reactionTypeIcon !!}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="action-bar" id="action-bar">
        <div class="action-left" id="action-left">
            

            @if($isCommentsDisabled ?? false)
                <span class="action-btn action-chip action-chip--metric action-chip--disabled action-chip--subtle" aria-disabled="true">
                    <div class="post-card__inline-icon">
                        <svg class="post-card__comment-icon" viewBox="0 0 24 24" width="1.2em" height="1.2em" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linejoin="round"><path stroke-width="1.5" d="M14.17 20.89c4.184-.277 7.516-3.657 7.79-7.9c.053-.83.053-1.69 0-2.52c-.274-4.242-3.606-7.62-7.79-7.899a33 33 0 0 0-4.34 0c-4.184.278-7.516 3.657-7.79 7.9a20 20 0 0 0 0 2.52c.1 1.545.783 2.976 1.588 4.184c.467.845.159 1.9-.328 2.823c-.35.665-.526.997-.385 1.237c.14.24.455.248 1.084.263c1.245.03 2.084-.322 2.75-.813c.377-.279.566-.418.696-.434s.387.09.899.3c.46.19.995.307 1.485.34c1.425.094 2.914.094 4.342 0Z"></path><path stroke-linecap="round" stroke-width="2" d="M11.995 12h.01m3.986 0H16m-8 0h.009"></path></g></svg>
                    </div>
                    @if($showCommentsCountLabel)
                        <span class="action-chip__label">{{ $commentsCountDisplay }}</span>
                    @endif
                </span>
            @else
                <a href="{{ $commentsUrl }}" class="action-btn post-card__action-link action-chip action-chip--metric action-chip--subtle" data-media-type="banani-button" aria-label="{{ $commentsLinkLabel }}">
                    <div class="post-card__inline-icon">
                        <svg class="post-card__comment-icon" viewBox="0 0 24 24" width="1.2em" height="1.2em" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linejoin="round"><path stroke-width="1.5" d="M14.17 20.89c4.184-.277 7.516-3.657 7.79-7.9c.053-.83.053-1.69 0-2.52c-.274-4.242-3.606-7.62-7.79-7.899a33 33 0 0 0-4.34 0c-4.184.278-7.516 3.657-7.79 7.9a20 20 0 0 0 0 2.52c.1 1.545.783 2.976 1.588 4.184c.467.845.159 1.9-.328 2.823c-.35.665-.526.997-.385 1.237c.14.24.455.248 1.084.263c1.245.03 2.084-.322 2.75-.813c.377-.279.566-.418.696-.434s.387.09.899.3c.46.19.995.307 1.485.34c1.425.094 2.914.094 4.342 0Z"></path><path stroke-linecap="round" stroke-width="2" d="M11.995 12h.01m3.986 0H16m-8 0h.009"></path></g></svg>
                    </div>
                    @if($showCommentsCountLabel)
                        <span class="action-chip__label">{{ $commentsCountDisplay }}</span>
                    @endif
                </a>
            @endif

            @if($bookmarkAction && $viewer)
                <form method="POST" action="{{ $bookmarkAction }}" class="post-card__action-form">
                    @csrf
                    <button
                        type="submit"
                        class="action-btn post-card__action-button action-chip action-chip--metric action-chip--subtle {{ $isBookmarked ? 'is-bookmarked' : '' }}"
                        data-media-type="banani-button"
                        aria-label="Kaydet"
                    >
                        <div class="post-card__inline-icon">
                            {!! $isBookmarked ? $bookmarkFilledIcon : $bookmarkOutlineIcon !!}
                        </div>
                        @if($showBookmarksCountLabel)
                            <span class="action-chip__label">{{ $bookmarksCountDisplay }}</span>
                        @endif
                    </button>
                </form>
            @else
                <span class="action-btn action-chip action-chip--metric action-chip--subtle" aria-label="Kaydet">
                    <div class="post-card__inline-icon">
                        {!! $bookmarkOutlineIcon !!}
                    </div>
                    @if($showBookmarksCountLabel)
                            <span class="action-chip__label">{{ $bookmarksCountDisplay }}</span>
                        @endif
                </span>
            @endif

            <button type="button" class="action-btn post-card__action-button action-chip action-chip--subtle" data-media-type="banani-button" data-post-card-copy data-post-card-share-trigger aria-label="Paylas">
                <div class="post-card__inline-icon">
                    {!! $shareIcon !!}
                </div>
            </button>
        </div>

        <button
            type="button"
            class="post-metric post-metric--views"
            data-post-card-view-metric
            data-post-card-stats-trigger
            aria-haspopup="dialog"
            aria-expanded="false"
            aria-label="Istatistikleri goster"
            @if(!$showViewsMetric) hidden @endif
        >
            <div class="post-card__inline-icon">
                <svg class="post-card__view-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </div>
            <span data-post-card-view-count>{{ $viewsCountDisplay }}</span>
        </button>
    </div>

    <div
        class="post-card__stats-modal"
        data-post-card-stats-modal
        data-post-card-stats-feed-count="{{ $statsFeedViews }}"
        data-post-card-stats-listing-count="{{ $statsListingViews }}"
        data-post-card-stats-reactions-count="{{ (int) $totalReactionCount }}"
        data-post-card-stats-comments-count="{{ $commentsCount }}"
        data-post-card-stats-bookmarks-count="{{ $bookmarksCount }}"
        data-post-card-stats-feed-follows-views="{{ $statsFeedViews === $viewsCount ? 'true' : 'false' }}"
        data-post-card-stats-listing-follows-views="{{ $statsListingViews === $viewsCount ? 'true' : 'false' }}"
        hidden
        role="dialog"
        aria-modal="true"
        aria-label="Gonderi istatistikleri"
    >
        <div class="post-card__stats-panel">
            <div class="post-card__stats-head">
                <strong><span data-post-card-stats-total>{{ $statsTotalInteractionsDisplay }}</span> etkileşim</strong>
                <button type="button" class="post-card__stats-close" data-post-card-stats-close aria-label="Kapat">
                    <iconify-icon icon="lucide:x" style="font-size: 22px; color: currentColor"></iconify-icon>
                </button>
            </div>
            <div class="post-card__stats-grid">
                <div class="post-card__stats-item">
                    <strong data-post-card-stats-feed>{{ $statsFeedViewsDisplay }}</strong>
                    <span>akışlardaki izlenimler</span>
                </div>
                <div class="post-card__stats-item">
                    <strong data-post-card-stats-listing>{{ $statsListingViewsDisplay }}</strong>
                    <span>ilanları</span>
                </div>
                <div class="post-card__stats-item">
                    <strong>{{ $statsReactionsDisplay }}</strong>
                    <span>gönderilere verilen tepkiler</span>
                </div>
                <div class="post-card__stats-item">
                    <strong>{{ $statsCommentsDisplay }}</strong>
                    <span>yorumlar</span>
                </div>
                <div class="post-card__stats-item">
                    <strong>{{ $statsBookmarksDisplay }}</strong>
                    <span>yer işaretleri</span>
                </div>
            </div>
        </div>
    </div>

    @if($commentsCount > 0)
        @if($isCommentsDisabled ?? false)
            <div class="comment-row comment-row--disabled" id="comment-row" aria-disabled="true">
                @if($showCommentPreviewAvatars)
                    <div class="comment-avatars">
                        @foreach($commentPreviewPeople as $commentPreview)
                            @if(!empty($commentPreview['avatar']))
                                <img
                                    src="{{ $commentPreview['avatar'] }}"
                                    alt="{{ trim($commentPreview['name'] . ' profil fotografi') }}"
                                    class="comment-avatar"
                                    loading="lazy"
                                    decoding="async"
                                />
                            @else
                                <span class="comment-avatar {{ $commentPreview['accent'] }}">{{ $commentPreview['initial'] }}</span>
                            @endif
                        @endforeach
                        @if($commenterPreviewExtraCount > 0)
                            <span class="comment-avatar-overflow">+{{ $commenterPreviewExtraCount }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @else
            <a class="comment-row" id="comment-row" href="{{ $commentsUrl }}" aria-label="{{ $commentsLinkLabel }}">
                @if($showCommentPreviewAvatars)
                    <div class="comment-avatars">
                        @foreach($commentPreviewPeople as $commentPreview)
                            @if(!empty($commentPreview['avatar']))
                                <img
                                    src="{{ $commentPreview['avatar'] }}"
                                    alt="{{ trim($commentPreview['name'] . ' profil fotografi') }}"
                                    class="comment-avatar"
                                    loading="lazy"
                                    decoding="async"
                                />
                            @else
                                <span class="comment-avatar {{ $commentPreview['accent'] }}">{{ $commentPreview['initial'] }}</span>
                            @endif
                        @endforeach
                        @if($commenterPreviewExtraCount > 0)
                            <span class="comment-avatar-overflow">+{{ $commenterPreviewExtraCount }}</span>
                        @endif
                    </div>
                @endif
            </a>
        @endif
    @endif

    <div class="post-card__toast" data-post-card-toast aria-live="polite">Link kopyalandi</div>
</article>

@once
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet"
    />
    <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

    <style>
        [data-post-card-shell] .og-action-wrap {
            position: relative !important;
            z-index: 999999 !important;
            display: inline-flex !important;
            overflow: visible !important;
        }

        [data-post-card-shell] [data-og-action-trigger] {
            display: flex !important;
            width: 30px !important;
            height: 30px !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 3px !important;
            padding: 0 !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 9999px !important;
            background: #f3f4f6 !important;
            box-shadow: none !important;
            color: inherit !important;
            transform: none !important;
            transition: background-color .15s ease, border-color .15s ease !important;
        }

        [data-post-card-shell] [data-og-action-trigger]:hover,
        [data-post-card-shell] [data-og-action-trigger]:focus-visible,
        [data-post-card-shell] [data-og-action-trigger][aria-expanded="true"] {
            border-color: #d1d5db !important;
            background: #e5e7eb !important;
            outline: none !important;
        }

        [data-post-card-shell] [data-og-action-trigger] span {
            display: block !important;
            width: 3px !important;
            height: 3px !important;
            border-radius: 9999px !important;
            background: #6b7280 !important;
        }

        [data-post-card-shell] .og-action-menu {
            position: fixed !important;
            z-index: 999999999 !important;
            width: 205px !important;
            padding: 6px !important;
            padding-bottom: 6px !important;
            overflow: hidden !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 16px !important;
            background: #ffffff !important;
            color: #4b5563 !important;
            box-shadow: 0 2px 7px rgba(0, 0, 0, 0.025) !important;
            outline: none !important;
        }

        [data-post-card-shell] .og-action-menu.hidden {
            display: none !important;
        }

        [data-post-card-shell] .og-action-menu a,
        [data-post-card-shell] .og-action-menu button {
            display: flex !important;
            width: 100% !important;
            height: 40px !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 12px !important;
            padding: 0 12px !important;
            border: 0 !important;
            border-radius: 11px !important;
            background: transparent !important;
            box-shadow: none !important;
            color: #4b5563 !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            line-height: 1.25 !important;
            text-align: left !important;
            text-decoration: none !important;
            transform: none !important;
            transition: background-color .15s ease, color .15s ease !important;
        }

        [data-post-card-shell] .og-action-menu > :last-child,
        [data-post-card-shell] .og-action-menu > form:last-child > button {
            margin-bottom: 0 !important;
        }

        [data-post-card-shell] .og-action-menu a:hover,
        [data-post-card-shell] .og-action-menu a:focus-visible,
        [data-post-card-shell] .og-action-menu button:hover,
        [data-post-card-shell] .og-action-menu button:focus-visible {
            background: #f3f4f6 !important;
            color: #4b5563 !important;
            outline: none !important;
        }

        [data-post-card-shell] .og-action-menu iconify-icon {
            width: 17px !important;
            min-width: 17px !important;
            height: 17px !important;
            color: #9ca3af !important;
            pointer-events: none !important;
        }

        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:link-2"] {
            color: #2563eb !important;
        }

        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:repeat-2"] {
            color: #7c3aed !important;
        }

        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:pin"],
        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:pin-off"] {
            color: #0f766e !important;
        }

        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:bookmark"],
        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:bookmark-check"] {
            color: #111827 !important;
        }

        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:square-pen"] {
            color: #b45309 !important;
        }

        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:flag"] {
            color: #ea580c !important;
        }

        [data-post-card-shell] .og-action-menu :where(a, button):hover iconify-icon[icon="lucide:trash-2"] {
            color: #dc2626 !important;
        }

        [data-post-card-shell] .og-action-menu span {
            color: #4b5563 !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            line-height: 1.25 !important;
        }

        [data-post-card-shell] .og-action-menu .og-action-divider {
            height: 1px !important;
            margin: 6px 4px !important;
            background: #e5e7eb !important;
        }

        @media (max-width: 639px) {
            [data-post-card-shell] .og-action-menu {
                width: 225px !important;
            }
        }

        :root {
            --background: #f3f1ee;
            --foreground: #111111;
            --border: #e9e5e0;
            --input: #f8f6f3;
            --primary: #0a66ff;
            --primary-foreground: #ffffff;
            --secondary: #f4f8ff;
            --secondary-foreground: #0a66ff;
            --muted: #f7f5f2;
            --muted-foreground: #6b6b6b;
            --success: #e8f7ee;
            --success-foreground: #146c43;
            --accent: #ffe7a3;
            --accent-foreground: #7a5400;
            --destructive: #fdebec;
            --destructive-foreground: #a61b29;
            --warning: #fff4e5;
            --warning-foreground: #8a4b08;
            --card: #ffffff;
            --card-foreground: #111111;
            --sidebar: #f8f6f3;
            --sidebar-foreground: #111111;
            --sidebar-primary: #eaf2ff;
            --sidebar-primary-foreground: #0a66ff;
            --radius-sm: 4px;
            --radius-md: 6px;
            --radius-lg: 8px;
            --radius-xl: 12px;
            --font-family-body: Roboto;
        }

        [data-post-card-shell] {
            position: relative;
            width: 100%;
            max-width: 660px;
            padding: 18px 24px 16px;
            border-radius: 18px;
            background: #ffffff;
            color: #111111;
            border: 0;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            font-family: var(--font-family-body), sans-serif;
        }

        [data-post-card-shell] * {
            box-sizing: border-box;
        }

        [data-post-card-shell] a,
        [data-post-card-shell] button {
            font: inherit;
        }

        [data-post-card-shell] a {
            color: inherit;
            text-decoration: none;
        }

        [data-post-card-shell] button {
            padding: 0;
            border: 0;
            background: transparent;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            box-shadow: none;
        }

        [data-post-card-shell] .post-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        [data-post-card-shell] .author-block {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            min-width: 0;
            flex: 1;
        }

        [data-post-card-shell] .avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        [data-post-card-shell] .author-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-sizing: border-box;
            object-fit: cover;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .author-avatar--fallback,
        [data-post-card-shell] .author-avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #c8a98a, #8b6348);
            color: #fff;
            font-size: 14px;
            font-weight: 400;
            text-transform: uppercase;
        }

        [data-post-card-shell] .category-badge {
            position: absolute;
            right: -2px;
            bottom: -2px;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #fff;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        }

        [data-post-card-shell] .category-badge__image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }

        [data-post-card-shell] .category-badge__fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            color: #fff;
            font-size: 8px;
            font-weight: 400;
            line-height: 1;
        }

        [data-post-card-shell] .author-info {
            flex: 1;
            min-width: 0;
        }

        [data-post-card-shell] .author-name-row {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            line-height: 1.3;
        }

        [data-post-card-shell] .author-name {
            overflow: hidden;
            color: #111;
            font-size: 15px;
            font-weight: 400;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-post-card-shell] .author-verified {
            display: inline-flex;
            align-items: center;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .author-subline {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            flex-wrap: wrap;
            color: #808791;
            font-size: 13px;
        }

        [data-post-card-shell] .author-subline__topic {
            color: #111827;
            font-size: 13px;
            font-weight: 400;
            white-space: nowrap;
        }

        [data-post-card-shell] .author-subline__item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-width: 0;
        }

        [data-post-card-shell] .author-subline__item iconify-icon {
            color: #94a3b8;
            font-size: 13px;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .author-subline__item--edited {
            color: #64748b;
            font-weight: 400;
        }

        [data-post-card-shell] .author-subline__edit-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .author-subline__edit-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            background: transparent;
            color: #7b8591;
            cursor: pointer;
            transition: opacity 0.12s ease, color 0.12s ease;
        }

        [data-post-card-shell] .author-subline__edit-toggle:hover,
        [data-post-card-shell] .author-subline__edit-toggle:focus-visible {
            color: #334155;
            opacity: 0.9;
            outline: none;
        }

        [data-post-card-shell] .post-card__edited-icon {
            width: 12px;
            height: 12px;
            display: block;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .author-subline__edit-popover {
            position: absolute;
            top: calc(100% + 8px);
            left: -8px;
            z-index: 35;
            display: flex;
            min-width: 212px;
            flex-direction: column;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid #dfe4ea;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(10px);
        }

        [data-post-card-shell] .author-subline__edit-popover[hidden] {
            display: none !important;
        }

        [data-post-card-shell] .author-subline__edit-row {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        [data-post-card-shell] .author-subline__edit-key {
            color: #8a94a3;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.35;
        }

        [data-post-card-shell] .author-subline__edit-value {
            color: #111827;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.4;
            white-space: nowrap;
        }

        [data-post-card-shell] .category-tag,
        [data-post-card-shell] .topic-link {
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 400;
            white-space: nowrap;
        }

        [data-post-card-shell] .dot-sep {
            color: #ccc;
            font-size: 11px;
            line-height: 1;
        }

        [data-post-card-shell] .post-time,
        [data-post-card-shell] .time-text {
            color: #808791;
            font-size: 13px;
            font-weight: 400;
            white-space: nowrap;
        }

        [data-post-card-shell] .menu-btn,
        [data-post-card-shell] .menu-button {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0;
            background: transparent;
            color: #111111;
            flex-shrink: 0;
            transition: none;
        }

        [data-post-card-shell] .menu-btn:hover,
        [data-post-card-shell] .menu-btn:focus-visible,
        [data-post-card-shell] .menu-button:hover,
        [data-post-card-shell] .menu-button:focus-visible {
            background: #f4f4f5;
            color: #111111;
            outline: none;
        }

        [data-post-card-shell] .post-card__menu-wrap,
        [data-post-card-shell] .post-card__reaction-wrap {
            position: relative;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .post-card__menu,
        [data-post-card-shell] .post-card__reaction-menu,
        [data-post-card-reaction-menu] {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 30;
            background: #fff;
            border: 0;
            border-radius: 8px;
            box-shadow: none;
            backdrop-filter: none;
        }

        [data-post-card-shell] .post-card__menu {
            min-width: 168px;
            padding: 8px;
        }

        [data-post-card-shell] .post-card__reaction-menu:not([hidden]),
        [data-post-card-reaction-menu]:not([hidden]) {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            align-content: flex-start;
            justify-content: flex-start;
            gap: 10px 12px;
            min-width: 0;
            width: min(208px, calc(100vw - 32px));
            max-width: min(208px, calc(100vw - 32px));
            padding: 10px 12px 12px;
        }

        [data-post-card-shell] .post-card__reaction-menu-title,
        [data-post-card-reaction-menu] .post-card__reaction-menu-title {
            flex: 0 0 100%;
            color: #7b8190;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.2;
            padding: 0 0 2px;
        }

        [data-post-card-shell] .post-card__menu[hidden],
        [data-post-card-shell] .post-card__reaction-menu[hidden],
        [data-post-card-reaction-menu][hidden] {
            display: none !important;
        }

        [data-post-card-shell] .post-card__menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            min-height: 38px;
            padding: 9px 12px;
            border-radius: 6px;
            background: transparent;
            color: #111111;
            font-size: 14px;
            font-weight: 400;
            text-align: left;
            transition: none;
        }

        [data-post-card-shell] .post-card__menu-item:hover,
        [data-post-card-shell] .post-card__menu-item:focus-visible,
        [data-post-card-reaction-menu] .post-card__reaction-option:hover,
        [data-post-card-reaction-menu] .post-card__reaction-option:focus-visible,
        [data-post-card-shell] .post-card__reaction-option:hover,
        [data-post-card-shell] .post-card__reaction-option:focus-visible {
            background: #f4f4f5;
            color: #111111;
            outline: none;
        }

        [data-post-card-shell] .post-card__menu-item--danger {
            color: #a61b29;
        }

        [data-post-card-shell] .post-card__menu-item iconify-icon {
            color: currentColor;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .post-card__menu-form,
        [data-post-card-shell] .post-card__action-form,
        [data-post-card-reaction-menu] .post-card__reaction-form,
        [data-post-card-shell] .post-card__reaction-form {
            margin: 0;
        }

        [data-post-card-shell] .post-card__reaction-form,
        [data-post-card-reaction-menu] .post-card__reaction-form,
        [data-post-card-reaction-menu] > a,
        [data-post-card-shell] .post-card__reaction-menu > a {
            display: inline-flex;
            flex: 0 0 34px;
            width: 34px;
        }

        [data-post-card-shell] .post-card__menu-form {
            display: block;
        }

        [data-post-card-shell] .post-title {
            margin: 0 0 10px 0;
            color: #111;
            font-size: 24px;
            font-weight: 400;
            line-height: 1.28;
            letter-spacing: -0.02em;
        }

        [data-post-card-shell] .post-title__link {
            color: inherit;
            text-decoration: none;
            transition: color 0.15s ease, opacity 0.15s ease;
        }

        [data-post-card-shell] .post-title__link:hover,
        [data-post-card-shell] .post-title__link:focus-visible {
            color: #0f172a;
            opacity: 0.86;
            outline: none;
        }

        [data-post-card-shell] .post-summary {
            margin: 0 0 12px 0;
            color: #30343a;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.66;
            white-space: pre-line;
        }

        [data-post-card-shell] .post-card__link-preview {
            margin: 0 0 14px;
        }

        [data-post-card-shell] .post-card__media-wrap {
            position: relative;
            width: 100%;
            margin-bottom: 16px;
            border-radius: 18px;
            overflow: hidden;
            background: #f8fafc;
        }

        [data-post-card-shell] .post-card__media-wrap--nsfw-blur {
            background: #f8fafc;
            isolation: isolate;
        }

        [data-post-card-shell] .post-card__media-wrap--nsfw-blur .post-card__media-scroller,
        [data-post-card-shell] .post-card__media-wrap--nsfw-blur .post-card__media-frame,
        [data-post-card-shell] .post-card__media-wrap--nsfw-blur .post-card__media-image,
        [data-post-card-shell] .post-card__media-wrap--nsfw-blur .post-card__media-video-fallback {
            opacity: 0 !important;
            visibility: hidden !important;
            filter: none !important;
            transform: none !important;
            transition: none !important;
            pointer-events: none !important;
        }

        [data-post-card-shell] .hero-wrap__overlay {
            position: absolute;
            inset: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            overflow: hidden;
            background: #f8fafc;
            color: #0f172a;
            pointer-events: none;
            touch-action: pan-y;
        }

        [data-post-card-shell] .hero-wrap__overlay::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 0;
            background:
                linear-gradient(110deg, #f8fafc 0%, #eef2f7 36%, #ffffff 50%, #eef2f7 64%, #f8fafc 100%);
            background-size: 240% 100%;
            animation: nsfwSolidPreload 1.1s ease-in-out infinite;
        }

        [data-post-card-shell] .hero-wrap__overlay::after {
            content: "";
            position: absolute;
            left: 16px;
            bottom: 16px;
            z-index: 1;
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            border: 2px solid rgba(37, 99, 235, 0.18);
            border-top-color: #2563eb;
            animation: nsfwOverlaySpin 0.7s linear infinite;
        }

        [data-post-card-shell] .hero-wrap__overlay-actions {
            position: relative;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            max-width: 100%;
            pointer-events: auto;
            touch-action: pan-y;
        }

        [data-post-card-shell] .hero-wrap__overlay-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 9999px;
            background: #e5e7eb;
            color: #374151;
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            font-weight: 500;
            line-height: 1;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        [data-post-card-shell] .hero-wrap__overlay-button {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 36px;
            padding: 0 18px;
            border: 1px solid rgba(37, 99, 235, 0.18);
            border-radius: 9999px;
            background: #2563eb;
            color: #ffffff;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            font-weight: 400;
            line-height: 1;
            letter-spacing: 0.04em;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            box-shadow: none;
            -webkit-tap-highlight-color: transparent;
            touch-action: pan-y;
        }

        [data-post-card-shell] .hero-wrap__overlay-button:hover,
        [data-post-card-shell] .hero-wrap__overlay-button:focus-visible {
            background: #1d4ed8;
            color: #ffffff;
            outline: none;
        }

        [data-post-card-shell] .hero-wrap__overlay-button-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            color: currentColor;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .hero-wrap__overlay-button-icon iconify-icon {
            display: block;
            font-size: 16px;
            line-height: 1;
            color: currentColor;
        }

        [data-post-card-shell] .hero-wrap__overlay-button.is-loading {
            pointer-events: none;
            opacity: 0.9;
        }

        @keyframes nsfwSolidPreload {
            0% {
                background-position: 120% 0;
            }

            100% {
                background-position: -120% 0;
            }
        }

        @keyframes nsfwOverlaySpin {
            from {
                rotate: 0deg;
            }

            to {
                rotate: 360deg;
            }
        }

        html.dark [data-post-card-shell] .post-card__media-wrap--nsfw-blur,
        body.dark [data-post-card-shell] .post-card__media-wrap--nsfw-blur,
        [data-theme="dark"] [data-post-card-shell] .post-card__media-wrap--nsfw-blur {
            background: #111827;
        }

        html.dark [data-post-card-shell] .hero-wrap__overlay,
        body.dark [data-post-card-shell] .hero-wrap__overlay,
        [data-theme="dark"] [data-post-card-shell] .hero-wrap__overlay {
            background: #111827;
            color: #f9fafb;
        }

        html.dark [data-post-card-shell] .hero-wrap__overlay::before,
        body.dark [data-post-card-shell] .hero-wrap__overlay::before,
        [data-theme="dark"] [data-post-card-shell] .hero-wrap__overlay::before {
            background:
                linear-gradient(110deg, #111827 0%, #1f2937 36%, #273244 50%, #1f2937 64%, #111827 100%);
            background-size: 240% 100%;
        }

        html.dark [data-post-card-shell] .hero-wrap__overlay-label,
        body.dark [data-post-card-shell] .hero-wrap__overlay-label,
        [data-theme="dark"] [data-post-card-shell] .hero-wrap__overlay-label {
            background: #374151;
            color: #f9fafb;
        }

        html.dark [data-post-card-shell] .hero-wrap__overlay-button,
        body.dark [data-post-card-shell] .hero-wrap__overlay-button,
        [data-theme="dark"] [data-post-card-shell] .hero-wrap__overlay-button {
            background: #2563eb;
            color: #ffffff;
            border-color: rgba(96, 165, 250, 0.35);
        }

        [data-post-card-shell] .post-card__media-scroller {
            display: flex;
            gap: 0;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            cursor: grab;
            user-select: none;
            touch-action: pan-y pinch-zoom;
        }

        [data-post-card-shell] .post-card__media-scroller::-webkit-scrollbar {
            display: none;
        }

        [data-post-card-shell] .post-card__media-scroller.is-dragging {
            cursor: grabbing;
            scroll-behavior: auto;
        }

        [data-post-card-shell] .post-card__media-slide {
            flex: 0 0 100%;
            width: 100%;
            min-width: 100%;
            scroll-snap-align: start;
            scroll-snap-stop: always;
        }

        [data-post-card-shell] .post-card__media-link {
            display: block;
            width: 100%;
            text-decoration: none;
            color: inherit;
            -webkit-user-drag: none;
            user-select: none;
        }

        [data-post-card-shell] .post-card__media-frame {
            position: relative;
            width: 100%;
            min-height: 220px;
            max-height: 560px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        [data-post-card-shell] .post-card__media-frame--video {
            background: #0f172a;
        }

        [data-post-card-shell] .post-card__media-image {
            display: block;
            width: 100%;
            height: auto;
            max-height: 560px;
            object-fit: contain;
            pointer-events: none;
            -webkit-user-drag: none;
            user-select: none;
            background: #f8fafc;
        }

        [data-post-card-shell] .post-card__media-embed {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 360px;
            border: 0;
            pointer-events: none;
            background: #f8fafc;
        }

        [data-post-card-shell] .post-card__media-video-fallback {
            width: 100%;
            min-height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 400;
            color: #ffffff;
            background: linear-gradient(135deg, #111827, #334155);
        }

        [data-post-card-shell] .post-card__media-badge {
            position: absolute;
            left: 14px;
            bottom: 14px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.76);
            color: #ffffff;
            font-size: 12px;
            font-weight: 400;
            line-height: 1;
        }

        [data-post-card-shell] .post-card__media-play {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.68);
            color: #ffffff;
            font-size: 24px;
            pointer-events: none;
        }

        [data-post-card-shell] .expand-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: max-content;
            max-width: 100%;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            margin-bottom: 14px;
            color: #2563eb;
            font-size: 15px;
            font-weight: 400;
            text-decoration: none;
            transition: opacity 0.12s ease;
            appearance: none;
            -webkit-appearance: none;
        }

        [data-post-card-shell] .expand-link:hover,
        [data-post-card-shell] .expand-link:focus-visible {
            opacity: 0.82;
            outline: none;
        }

        [data-post-card-shell] [data-post-card-expand-label] {
            display: inline-block;
            overflow: visible;
            white-space: nowrap;
        }

        [data-post-card-shell] .post-card__expand-icon {
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 180ms ease;
        }

        [data-post-card-shell] .expand-link[aria-expanded='true'] .post-card__expand-icon {
            transform: rotate(180deg);
        }

        [data-post-card-shell] .post-summary-shell {
            display: grid;
            gap: 0;
        }

        [data-post-card-shell] .reactions-row,
        [data-post-card-shell] .reaction-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 8px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        }

        [data-post-card-shell] .reaction-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 11px;
            border: 0;
            border-radius: 999px;
            background: #f5f6f8;
            color: #111827;
            font-size: 14px;
            font-weight: 400;
            white-space: nowrap;
            box-shadow: none;
            transition: background 0.12s ease;
        }

        [data-post-card-shell] .reaction-item:hover {
            background: #eceff3;
        }

        [data-post-card-shell] .reaction-item:active {
            background: #e4e8ee;
        }

        [data-post-card-shell] .reaction-emoji,
        [data-post-card-reaction-menu] .reaction-emoji {
            font-size: 20px;
            line-height: 1;
        }

        [data-post-card-shell] .reaction-count,
        [data-post-card-reaction-menu] .reaction-count {
            color: #111827;
            font-size: 14px;
            line-height: 1;
        }

        [data-post-card-shell] .reaction-emoji--html,
        [data-post-card-reaction-menu] .reaction-emoji--html,
        [data-post-card-shell] .reaction-emoji--html img,
        [data-post-card-reaction-menu] .reaction-emoji--html img,
        [data-post-card-shell] .reaction-emoji--html svg,
        [data-post-card-reaction-menu] .reaction-emoji--html svg,
        [data-post-card-shell] .reaction-emoji--html iconify-icon,
        [data-post-card-reaction-menu] .reaction-emoji--html iconify-icon {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        [data-post-card-shell] .more-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 7px 11px;
            border: 0;
            border-radius: 999px;
            background: #f5f6f8;
            color: #111827;
            font-size: 14px;
            font-weight: 400;
            box-shadow: none;
            transition: background 0.12s ease;
        }

        [data-post-card-shell] .more-pill:hover,
        [data-post-card-shell] .more-pill:focus-visible {
            background: #eceff3;
            outline: none;
        }

        [data-post-card-shell] .more-pill:active {
            background: #e4e8ee;
        }

        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .reaction-add {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: #f5f6f8;
            color: #6b7280;
            box-shadow: none;
            transition: background 0.12s ease, color 0.12s ease;
        }

        [data-post-card-shell] .smiley-btn:hover,
        [data-post-card-shell] .smiley-btn:focus-visible,
        [data-post-card-shell] .reaction-add:hover,
        [data-post-card-shell] .reaction-add:focus-visible {
            background: #eceff3;
            color: #475569;
            outline: none;
        }

        [data-post-card-shell] .smiley-btn:active,
        [data-post-card-shell] .reaction-add:active {
            background: #e4e8ee;
            color: #334155;
        }

        [data-post-card-shell] .post-card__reaction-add-icon,
        [data-post-card-shell] .post-card__inline-icon {
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .post-card__bookmark-icon {
            display: block;
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .post-card__share-icon {
            display: block;
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .post-card__reaction-custom-icon {
            display: block;
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }

        [data-post-card-shell] .post-card__reaction-option,
        [data-post-card-reaction-menu] .post-card__reaction-option {
            width: 100%;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            color: inherit;
            text-decoration: none;
        }

        [data-post-card-shell] .action-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        [data-post-card-shell] .action-left {
            display: flex;
            align-items: center;
            gap: 18px;
            flex: 1;
            min-width: 0;
            flex-wrap: nowrap;
        }

        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: auto;
            height: 30px;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            color: #64748b;
            font-size: 15px;
            box-shadow: none;
            transition: background 0.12s ease, color 0.12s ease;
        }

        [data-post-card-shell] .action-chip--metric {
            width: auto;
            min-width: 0;
            padding: 0;
            border-radius: 999px;
        }

        [data-post-card-shell] .action-chip--subtle {
            background: transparent;
        }

        [data-post-card-shell] .action-chip__label {
            color: currentColor;
            font-size: 14px;
            font-weight: 400;
            line-height: 1;
        }

        [data-post-card-shell] .action-chip--disabled {
            cursor: not-allowed;
            opacity: 0.64;
        }

        [data-post-card-shell] .action-btn:hover,
        [data-post-card-shell] .action-btn:focus-visible,
        [data-post-card-shell] .action-stat:hover,
        [data-post-card-shell] .action-stat:focus-visible {
            background: transparent;
            color: #334155;
            outline: none;
        }

        [data-post-card-shell] .action-btn:active,
        [data-post-card-shell] .action-stat:active {
            background: transparent;
            color: #334155;
        }

        [data-post-card-shell] .action-chip.is-bookmarked,
        [data-post-card-shell] .action-chip.is-active {
            background: transparent;
            color: #334155;
        }

        [data-post-card-shell] .post-card__action-link,
        [data-post-card-shell] .post-card__action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            appearance: none;
            -webkit-appearance: none;
        }

        [data-post-card-shell] .post-card__action-form {
            display: inline-flex;
        }

        [data-post-card-shell] .post-card__action-form button,
        [data-post-card-shell] .action-bar button {
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            appearance: none;
            -webkit-appearance: none;
        }

        [data-post-card-shell] .post-card__action-button svg,
        [data-post-card-shell] .post-card__action-link svg {
            width: 18px;
            height: 18px;
        }

        [data-post-card-shell] .post-metric {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #6b7280;
            font-size: 14px;
            font-weight: 400;
            white-space: nowrap;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
        }

        [data-post-card-shell] .post-card__stats-modal {
            position: fixed;
            inset: 0;
            z-index: 2147483000;
            display: block;
            padding: 18px 12px;
            background: rgba(0, 0, 0, 0.42);
        }

        [data-post-card-shell] .post-card__stats-modal[hidden] {
            display: none !important;
        }

        [data-post-card-shell] .post-card__stats-panel {
            position: absolute;
            top: 50%;
            left: 50%;
            width: min(520px, calc(100vw - 24px));
            min-height: 236px;
            padding: 20px 24px 26px;
            border-radius: 12px;
            background: #ffffff;
            color: #111111;
            box-shadow: none;
            transform: translate(-50%, -50%);
        }

        [data-post-card-shell] .post-card__stats-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 26px;
        }

        [data-post-card-shell] .post-card__stats-head strong {
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
        }

        [data-post-card-shell] .post-card__stats-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border: 0;
            border-radius: 999px;
            background: #f1f1f1;
            color: #6b6b6b;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
        }

        [data-post-card-shell] .post-card__stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            column-gap: 34px;
            row-gap: 46px;
        }

        [data-post-card-shell] .post-card__stats-item {
            min-width: 0;
        }

        [data-post-card-shell] .post-card__stats-item strong {
            display: block;
            margin-bottom: 3px;
            color: #111111;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.1;
        }

        [data-post-card-shell] .post-card__stats-item span {
            display: block;
            color: #666666;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.25;
        }

        @media (max-width: 520px) {
            [data-post-card-shell] .post-card__stats-modal {
                padding: 16px 10px;
            }

            [data-post-card-shell] .post-card__stats-panel {
                width: min(356px, calc(100vw - 20px));
                min-height: 240px;
                padding: 18px 20px 24px;
            }

            [data-post-card-shell] .post-card__stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 24px;
                row-gap: 28px;
            }
        }

        [data-post-card-shell] [data-post-card-view-metric][hidden],
        [data-post-card-shell] .post-metric--views[hidden] {
            display: none !important;
        }

        [data-post-card-shell] .comment-row {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            min-width: 0;
            margin-top: 8px;
            margin-bottom: 4px;
            padding: 0;
            border-top: 0;
            border-radius: 0;
            background: transparent;
            cursor: pointer;
            transition: background 0.12s ease, opacity 0.12s ease;
        }

        [data-post-card-shell] .comment-row:hover,
        [data-post-card-shell] .comment-row:focus-visible {
            background: transparent;
            opacity: 1;
            outline: none;
        }

        [data-post-card-shell] .comment-row--disabled {
            cursor: default;
        }

        [data-post-card-shell] .comment-row--disabled:hover {
            background: transparent;
        }

        [data-post-card-shell] .comment-avatars {
            display: flex;
            align-items: center;
            position: relative;
            flex-shrink: 0;
        }

        [data-post-card-shell] .comment-avatar {
            width: 24px;
            height: 24px;
            min-width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            object-fit: cover;
            font-size: 9px;
            font-weight: 400;
            color: #fff;
        }

        [data-post-card-shell] .comment-avatar + .comment-avatar {
            margin-left: -7px;
        }

        [data-post-card-shell] .comment-avatar-overflow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            min-width: 24px;
            margin-left: -7px;
            border: 2px solid #fff;
            border-radius: 999px;
            background: #f4f4f5;
            color: #52525b;
            font-size: 10px;
            font-weight: 500;
            line-height: 1;
        }

        [data-post-card-shell] .ca-1 {
            background: linear-gradient(135deg, #f97316, #ea580c);
        }

        [data-post-card-shell] .ca-2 {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        [data-post-card-shell] .comment-text-wrap {
            flex: 1;
            min-width: 0;
        }

        [data-post-card-shell] .comment-label {
            display: -webkit-box;
            overflow: hidden;
            color: #23272f;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.35;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        [data-post-card-shell] .post-card__toast {
            position: absolute;
            left: 50%;
            bottom: 18px;
            z-index: 35;
            padding: 11px 16px;
            border-radius: 999px;
            background: rgba(17, 17, 17, 0.92);
            color: #ffffff;
            font-size: 13px;
            font-weight: 400;
            transform: translate(-50%, 16px);
            opacity: 0;
            visibility: hidden;
            transition: opacity 160ms ease, transform 160ms ease, visibility 160ms ease;
            pointer-events: none;
        }

        [data-post-card-shell] .post-card__toast.is-visible {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, 0);
        }

        [data-post-card-shell] {
            max-width: 100%;
            padding: 18px 18px 14px;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: none;
        }

        [data-post-card-shell] .post-header {
            align-items: flex-start;
            margin-bottom: 12px;
        }

        [data-post-card-shell] .author-avatar {
            width: 34px;
            height: 34px;
        }

        [data-post-card-shell] .author-avatar--fallback,
        [data-post-card-shell] .author-avatar-fallback {
            background: #c8a98a;
        }

        [data-post-card-shell] .category-badge {
            width: 16px;
            height: 16px;
            right: -3px;
            bottom: -3px;
            border: 0;
            background: #ffffff;
            box-shadow: none;
        }

        [data-post-card-shell] .category-badge__fallback {
            background: #10b981;
            font-size: 7px;
        }

        [data-post-card-shell] .author-name {
            font-size: 14px;
            font-weight: 400;
            line-height: 1.1;
        }

        [data-post-card-shell] .author-subline {
            gap: 5px;
            margin-top: 1px;
            font-size: 11px;
            line-height: 1.2;
        }

        [data-post-card-shell] .author-subline__topic {
            color: #111111;
            font-size: 11px;
            font-weight: 400;
        }

        [data-post-card-shell] .post-time,
        [data-post-card-shell] .time-text {
            color: #6b7280;
            font-size: 11px;
            font-weight: 400;
        }

        [data-post-card-shell] .post-title {
            margin: 0 0 20px;
            color: #000000;
            font-size: 18px;
            font-weight: 400;
            line-height: 1.42;
            letter-spacing: 0;
        }

        [data-post-card-shell] .post-title__link,
        [data-post-card-shell] .post-title__link:hover,
        [data-post-card-shell] .post-title__link:focus-visible {
            color: inherit;
            opacity: 1;
            transition: none;
        }

        [data-post-card-shell] .post-card__media-wrap {
            margin: 0 0 18px;
            border-radius: 8px;
            background: #f3f4f6;
        }

        [data-post-card-shell] .post-card__media-frame {
            min-height: 0;
            max-height: none;
            aspect-ratio: 16 / 9;
            background: #f3f4f6;
        }

        [data-post-card-shell] .post-card__media-image {
            width: 100%;
            height: 100%;
            max-height: none;
            object-fit: cover;
            background: transparent;
        }

        [data-post-card-shell] .post-summary {
            margin: 0 0 14px;
            color: #000000;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.55;
        }

        [data-post-card-shell] .post-card__source[hidden] {
            display: none !important;
        }

        [data-post-card-shell] .post-card__quote-preview[hidden] {
            display: none !important;
        }

        [data-post-card-shell] .post-card__quote-preview {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            margin: 0 0 14px;
            padding: 14px;
            border: 1px solid #d9dee7;
            border-radius: 12px;
            background: #ffffff;
            color: #000000;
            text-decoration: none;
        }

        [data-post-card-shell] .post-card__quote-preview-head {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        [data-post-card-shell] .post-card__quote-preview-avatar-wrap {
            position: relative;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
        }

        [data-post-card-shell] .post-card__quote-preview-avatar {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            object-fit: cover;
        }

        [data-post-card-shell] .post-card__quote-preview-avatar-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #4f46e5;
            font-size: 12px;
            font-weight: 600;
        }

        [data-post-card-shell] .post-card__quote-preview-category {
            position: absolute;
            right: -3px;
            bottom: -3px;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            object-fit: cover;
            background: #ffffff;
        }

        [data-post-card-shell] .post-card__quote-preview-meta {
            display: flex;
            min-width: 0;
            flex-direction: column;
            line-height: 1.2;
        }

        [data-post-card-shell] .post-card__quote-preview-author {
            overflow: hidden;
            color: #111827;
            font-size: 13px;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-post-card-shell] .post-card__quote-preview-time {
            color: #6b7280;
            font-size: 12px;
            font-weight: 400;
        }

        [data-post-card-shell] .post-card__quote-preview-title {
            color: #000000;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.35;
        }

        [data-post-card-shell] .post-card__quote-preview-description {
            color: #111827;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.45;
        }

        [data-post-card-shell] .post-card__quote-preview-media {
            display: block;
            overflow: hidden;
            border-radius: 8px;
            background: #f3f4f6;
        }

        [data-post-card-shell] .post-card__quote-preview-media img {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
        }

        [data-post-card-shell] .post-card__quote-preview-open {
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

        [data-post-card-shell] .post-card__quote-preview-open iconify-icon {
            font-size: 16px;
        }

        [data-post-card-shell] .post-card__source {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            width: 100%;
            min-height: 68px;
            margin: 0 0 12px;
            padding: 14px 18px;
            border: 1px solid rgba(15, 23, 42, 0.04);
            border-radius: 16px;
            background: #f3f4f6;
            color: #111827;
            text-decoration: none;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            transition: background-color 0.14s ease, border-color 0.14s ease, color 0.14s ease;
        }

        [data-post-card-shell] .post-card__source:hover,
        [data-post-card-shell] .post-card__source:focus-visible {
            background: #ebeef2;
            border-color: rgba(15, 23, 42, 0.06);
            color: #111827;
            outline: none;
        }

        [data-post-card-shell] .post-card__source:active {
            background: #e5e7eb;
            color: #111827;
        }

        [data-post-card-shell] .post-card__source-copy {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            min-width: 0;
            flex: 1 1 auto;
        }

        [data-post-card-shell] .post-card__source-label {
            color: #9ca3af;
            font-size: 10px;
            font-weight: 500;
            line-height: 1;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        [data-post-card-shell] .post-card__source-domain-row {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
            max-width: 100%;
        }

        [data-post-card-shell] .post-card__source-favicon {
            display: inline-flex;
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            border-radius: 999px;
            object-fit: cover;
            background: #ffffff;
            box-shadow: none;
        }

        [data-post-card-shell] .post-card__source-domain {
            overflow: hidden;
            color: #111827;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-post-card-shell] .post-card__source-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
            color: #9ca3af;
            margin-top: 2px;
        }

        [data-post-card-shell] .post-card__source:hover .post-card__source-icon,
        [data-post-card-shell] .post-card__source:focus-visible .post-card__source-icon,
        [data-post-card-shell] .post-card__source:active .post-card__source-icon {
            color: #6b7280;
        }

        [data-post-card-shell] .post-card__source-icon iconify-icon {
            font-size: 16px;
        }

        [data-post-card-shell] .post-card__tags {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 6px 10px;
            margin: 3px 0 12px;
        }

        [data-post-card-shell] .post-card__tag {
            color: #1b32ff;;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.2;
            text-decoration: none;
        }

        [data-post-card-shell] .reaction-row,
        [data-post-card-shell] .reactions-row {
            margin: 0 0 16px;
            gap: 8px;
        }

        [data-post-card-shell] .reaction-item,
        [data-post-card-shell] .more-pill,
        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .reaction-add {
            background: #f4f4f4;
            box-shadow: none;
            transition: none;
        }

        [data-post-card-shell] .reaction-item:hover,
        [data-post-card-shell] .more-pill:hover,
        [data-post-card-shell] .smiley-btn:hover,
        [data-post-card-shell] .reaction-add:hover,
        [data-post-card-shell] .reaction-item:focus-visible,
        [data-post-card-shell] .more-pill:focus-visible,
        [data-post-card-shell] .smiley-btn:focus-visible,
        [data-post-card-shell] .reaction-add:focus-visible {
            background: #f4f4f4;
            color: #111111;
            outline: none;
        }

        [data-post-card-shell] .action-bar {
            margin: 0 -18px;
            padding: 12px 18px 12px;
            gap: 12px;
            box-shadow: inset 0 1px 0 #eeeeee;
        }

        [data-post-card-shell] .action-left {
            gap: 20px;
        }

        [data-post-card-shell] .post-card__vote-cluster {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-width: 54px;
            height: 28px;
            padding: 0 9px;
            border-radius: 999px;
            background: #f7f7f7;
            color: #000000;
        }

        [data-post-card-shell] .post-card__vote-cluster iconify-icon {
            font-size: 14px;
        }

        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .post-metric {
            height: 28px;
            color: #000000;
            font-size: 12px;
            transition: none;
        }

        [data-post-card-shell] .post-card__inline-icon,
        [data-post-card-shell] .post-card__bookmark-icon,
        [data-post-card-shell] .post-card__share-icon,
        [data-post-card-shell] .post-card__reaction-custom-icon {
            width: 16px;
            height: 16px;
        }

        [data-post-card-reaction-layer] {
            position: fixed;
            inset: 0;
            z-index: 60;
            pointer-events: none;
        }

        [data-post-card-reaction-layer] .post-card__reaction-menu {
            pointer-events: auto;
        }

        @media (max-width: 820px) {
            [data-post-card-shell] {
                padding: 16px 18px 14px;
                border-radius: 16px;
            }
        }

        @media (max-width: 560px) {
            [data-post-card-shell] {
                width: 100%;
                max-width: 100%;
                margin-inline: 0;
                box-sizing: border-box;
                padding: 16px 16px 14px;
                border-radius: 0;
            }

            [data-post-card-shell] .post-header {
                gap: 10px;
            }

            [data-post-card-shell] .author-block {
                gap: 10px;
            }

            [data-post-card-shell] .post-title {
                font-size: 20px;
                margin-bottom: 5px;
            }

            [data-post-card-shell] .post-summary {
                margin-bottom: 8px;
                font-size: 15px;
                line-height: 1.58;
            }

            [data-post-card-shell] .post-card__media-wrap {
                margin-bottom: 14px;
                border-radius: 12px;
            }

            [data-post-card-shell] .post-card__media-frame {
                min-height: 180px;
                max-height: 420px;
            }

            [data-post-card-shell] .post-card__media-image {
                max-height: 420px;
            }

            [data-post-card-shell] .post-card__media-play {
                width: 54px;
                height: 54px;
                font-size: 20px;
            }

            [data-post-card-shell] .expand-link {
                margin-bottom: 14px;
                font-size: 13.5px;
            }

            [data-post-card-shell] .reaction-row,
            [data-post-card-shell] .reactions-row {
                gap: 8px;
            }

            [data-post-card-shell] .post-card__reaction-menu,
            [data-post-card-reaction-menu] {
                left: 0;
                right: auto;
                width: min(208px, calc(100vw - 20px));
                max-width: min(208px, calc(100vw - 20px));
            }

            [data-post-card-shell] .post-card__reaction-menu:not([hidden]),
            [data-post-card-reaction-menu]:not([hidden]) {
                gap: 10px 12px;
                padding: 10px 12px 12px;
            }

            [data-post-card-shell] .post-card__reaction-menu-title,
            [data-post-card-reaction-menu] .post-card__reaction-menu-title {
                padding: 0 2px 4px;
                font-size: 12px;
            }

            [data-post-card-shell] .post-card__reaction-option,
            [data-post-card-reaction-menu] .post-card__reaction-option {
                width: 100%;
                height: 34px;
                border-radius: 0;
            }

            [data-post-card-shell] .post-card__reaction-form,
            [data-post-card-reaction-menu] .post-card__reaction-form,
            [data-post-card-reaction-menu] > a,
            [data-post-card-shell] .post-card__reaction-menu > a {
                flex-basis: 34px;
                width: 34px;
            }

            [data-post-card-shell] .action-left {
                gap: 16px;
            }

            [data-post-card-shell] .action-bar {
                align-items: center;
                margin-left: -16px;
                margin-right: -16px;
                padding-left: 16px;
                padding-right: 16px;
            }

            [data-post-card-shell] .comment-row {
                padding-top: 0;
                gap: 8px;
            }

            [data-post-card-shell] .comment-label {
                font-size: 14px;
            }
        }



        /* Typography balance: kalın fontlar kaldırıldı, boyutlar orantılı hale getirildi. */
        [data-post-card-shell] {
            font-family: 'Roboto', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
        }

        [data-post-card-shell],
        [data-post-card-shell] *,
        [data-post-card-shell] *::before,
        [data-post-card-shell] *::after {
            font-weight: 400 !important;
        }

        [data-post-card-shell] .author-name {
            font-size: 14px;
            line-height: 1.25;
            letter-spacing: 0;
        }

        [data-post-card-shell] .author-subline,
        [data-post-card-shell] .author-subline__topic,
        [data-post-card-shell] .post-time,
        [data-post-card-shell] .time-text {
            font-size: 12px;
            line-height: 1.35;
        }

        [data-post-card-shell] .post-title {
            font-size: clamp(16px, 2.1vw, 18px);
            line-height: 1.42;
            letter-spacing: 0;
        }

        [data-post-card-shell] .post-summary,
        [data-post-card-shell] .comment-label {
            font-size: clamp(13.5px, 1.7vw, 14.5px);
            line-height: 1.6;
        }

        [data-post-card-shell] .expand-link,
        [data-post-card-shell] .post-card__menu-item,
        [data-post-card-shell] .post-card__reaction-menu-title,
        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .post-metric,
        [data-post-card-shell] .action-chip__label,
        [data-post-card-shell] .reaction-count,
        [data-post-card-shell] .post-card__source-domain,
        [data-post-card-shell] .post-card__tag {
            font-size: 13px;
            line-height: 1.35;
        }

        [data-post-card-shell] .post-card__source-label,
        [data-post-card-shell] .category-badge__fallback {
            font-size: 10px;
            line-height: 1.1;
        }

        @media (max-width: 640px) {
            [data-post-card-shell] {
                font-size: 13.5px;
            }

            [data-post-card-shell] .post-title {
                font-size: 16px;
                line-height: 1.45;
            }

            [data-post-card-shell] .post-summary,
            [data-post-card-shell] .comment-label {
                font-size: 13.5px;
                line-height: 1.58;
            }

            [data-post-card-shell] .author-name {
                font-size: 13.5px;
            }

            [data-post-card-shell] .author-subline,
            [data-post-card-shell] .author-subline__topic,
            [data-post-card-shell] .post-time,
            [data-post-card-shell] .time-text {
                font-size: 11.5px;
            }
        }


        /* Spacing, reaction icon and modern menu refinements */
        [data-post-card-shell] .post-title {
            margin: 0 0 12px;
        }

        [data-post-card-shell] .post-card__media-wrap {
            margin: 0 0 14px;
        }

        [data-post-card-shell] .post-summary {
            margin: 0 0 10px;
        }

        [data-post-card-shell] .expand-link {
            margin-bottom: 10px;
        }

        [data-post-card-shell] .post-card__tags {
            gap: 10px;
            margin: 0 0 14px;
        }

        [data-post-card-shell] .reaction-row,
        [data-post-card-shell] .reactions-row {
            margin: 0 0 10px;
            padding-bottom: 8px;
            gap: 8px;
        }

        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .reaction-add {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            background: #f5f7fb;
            color: #4b5563;
        }

        [data-post-card-shell] .post-card__reaction-custom-icon,
        [data-post-card-shell] .post-card__inline-icon {
            width: 20px;
            height: 20px;
        }

        [data-post-card-shell] .post-card__reaction-custom-icon svg,
        [data-post-card-shell] .post-card__inline-icon svg,
        [data-post-card-shell] .post-card__inline-icon iconify-icon {
            width: 20px;
            height: 20px;
            font-size: 20px !important;
        }

        [data-post-card-shell] .reaction-emoji,
        [data-post-card-reaction-menu] .reaction-emoji,
        [data-post-card-shell] .reaction-emoji--html,
        [data-post-card-reaction-menu] .reaction-emoji--html,
        [data-post-card-shell] .reaction-emoji--html img,
        [data-post-card-reaction-menu] .reaction-emoji--html img,
        [data-post-card-shell] .reaction-emoji--html svg,
        [data-post-card-reaction-menu] .reaction-emoji--html svg,
        [data-post-card-shell] .reaction-emoji--html iconify-icon,
        [data-post-card-reaction-menu] .reaction-emoji--html iconify-icon {
            width: 22px;
            height: 22px;
            font-size: 22px;
        }

        [data-post-card-shell] .action-bar {
            margin: 0 -18px;
            padding: 10px 18px 10px;
            gap: 10px;
        }

        [data-post-card-shell] .post-card__menu {
            min-width: 200px;
            padding: 8px;
            border: 1px solid #e6eaf0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.10);
        }

        [data-post-card-shell] .post-card__menu-item {
            min-height: 42px;
            padding: 10px 12px;
            border-radius: 10px;
            color: #334155;
            font-size: 13.5px;
            background: transparent;
        }

        [data-post-card-shell] .post-card__menu-item span {
            color: inherit;
        }

        [data-post-card-shell] .post-card__menu-item iconify-icon {
            color: #64748b;
            font-size: 17px !important;
        }

        [data-post-card-shell] .post-card__menu-item:hover,
        [data-post-card-shell] .post-card__menu-item:focus-visible {
            background: #f8fafc;
            color: #0f172a;
        }

        [data-post-card-shell] .post-card__menu-item--danger {
            color: #b42318;
        }

        [data-post-card-shell] .post-card__menu-item--danger iconify-icon {
            color: #b42318;
        }

        [data-post-card-shell] .post-card__menu-wrap .menu-btn,
        [data-post-card-shell] .post-card__menu-wrap .menu-button {
            width: 34px;
            height: 34px;
            border-radius: 999px;
        }

        @media (max-width: 560px) {
            [data-post-card-shell] .post-card__tags {
                margin: 0 0 12px;
            }

            [data-post-card-shell] .reaction-row,
            [data-post-card-shell] .reactions-row {
                margin: 0 0 8px;
                padding-bottom: 8px;
            }

            [data-post-card-shell] .action-bar {
                padding-top: 10px;
                padding-bottom: 10px;
            }
        }


        /* Menu hover/touch states + richer reaction asset rendering */
        [data-post-card-shell] .post-card__menu-item,
        [data-post-card-shell] .post-card__menu-form,
        [data-post-card-shell] .post-card__menu-item span,
        [data-post-card-shell] .post-card__menu-item iconify-icon,
        [data-post-card-shell] .menu-btn,
        [data-post-card-shell] .menu-button {
            -webkit-tap-highlight-color: rgba(37, 99, 235, 0.12);
        }

        [data-post-card-shell] .post-card__menu-item:hover,
        [data-post-card-shell] .post-card__menu-item:focus-visible {
            background: #eef4ff;
            color: #0f172a;
            outline: none;
        }

        [data-post-card-shell] .post-card__menu-item:active {
            background: #dbeafe;
            color: #0f172a;
        }

        [data-post-card-shell] .post-card__menu-item:hover iconify-icon,
        [data-post-card-shell] .post-card__menu-item:focus-visible iconify-icon,
        [data-post-card-shell] .post-card__menu-item:active iconify-icon {
            color: currentColor;
        }

        [data-post-card-shell] .post-card__menu-item--danger:hover,
        [data-post-card-shell] .post-card__menu-item--danger:focus-visible {
            background: #fef2f2;
            color: #b42318;
        }

        [data-post-card-shell] .post-card__menu-item--danger:active {
            background: #fee2e2;
            color: #991b1b;
        }

        [data-post-card-shell] .menu-btn:hover,
        [data-post-card-shell] .menu-button:hover,
        [data-post-card-shell] .menu-btn:focus-visible,
        [data-post-card-shell] .menu-button:focus-visible {
            background: #f3f4f6;
            color: #0f172a;
        }

        [data-post-card-shell] .menu-btn:active,
        [data-post-card-shell] .menu-button:active {
            background: #e5e7eb;
            color: #0f172a;
        }

        [data-post-card-shell] .reaction-emoji--html,
        [data-post-card-reaction-menu] .reaction-emoji--html {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
        }

        [data-post-card-shell] .post-card__reaction-asset,
        [data-post-card-shell] .reaction-emoji--html img,
        [data-post-card-reaction-menu] .reaction-emoji--html img {
            display: block;
            width: 22px;
            height: 22px;
            min-width: 22px;
            min-height: 22px;
            border-radius: 999px;
            object-fit: cover;
            overflow: hidden;
            background: #f8fafc;
        }

        [data-post-card-shell] .post-card__reaction-option,
        [data-post-card-reaction-menu] .post-card__reaction-option {
            width: 36px;
            height: 36px;
            border-radius: 999px;
        }

        [data-post-card-shell] .post-card__reaction-option:hover,
        [data-post-card-shell] .post-card__reaction-option:focus-visible,
        [data-post-card-reaction-menu] .post-card__reaction-option:hover,
        [data-post-card-reaction-menu] .post-card__reaction-option:focus-visible {
            background: #f3f4f6;
            outline: none;
        }

        [data-post-card-shell] .post-card__reaction-option:active,
        [data-post-card-reaction-menu] .post-card__reaction-option:active {
            background: #e5e7eb;
        }


        /* Post media preload animation - fixed: no spinner, no stuck overlay */
        [data-post-card-shell] .post-card__media-frame {
            position: relative;
            overflow: hidden;
            background: #eef2f7;
            isolation: isolate;
        }

        [data-post-card-shell] .post-card__media-frame::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background:
                linear-gradient(
                    100deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.72) 45%,
                    rgba(255, 255, 255, 0) 80%
                ),
                linear-gradient(135deg, #eef2f7 0%, #f8fafc 48%, #e5e7eb 100%);
            background-size: 220% 100%, 100% 100%;
            animation:
                postCardMediaPreload 1.05s linear infinite,
                postCardPreloadOverlayOut 0.26s ease 1.25s forwards;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.22s ease, visibility 0.22s ease;
        }

        [data-post-card-shell] .post-card__media-image {
            opacity: 1;
            transform: none;
        }

        [data-post-card-shell] .post-card__media-frame.is-loaded::before,
        [data-post-card-shell] .post-card__media-frame.is-error::before {
            opacity: 0;
            visibility: hidden;
            animation-play-state: paused;
        }

        @keyframes postCardMediaPreload {
            0% {
                background-position: 220% 0, 0 0;
            }

            100% {
                background-position: -220% 0, 0 0;
            }
        }

        @keyframes postCardPreloadOverlayOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        /* Text preload shimmer effect - fixed: never keeps text hidden */
        [data-post-card-shell].is-preloading .author-name,
        [data-post-card-shell].is-preloading .author-subline__topic,
        [data-post-card-shell].is-preloading .author-subline__item,
        [data-post-card-shell].is-preloading .post-title,
        [data-post-card-shell].is-preloading .post-title__link,
        [data-post-card-shell].is-preloading .post-summary,
        [data-post-card-shell].is-preloading .post-card__tag,
        [data-post-card-shell].is-preloading .reaction-item,
        [data-post-card-shell].is-preloading .action-chip__label {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            isolation: isolate;
        }

        [data-post-card-shell].is-preloading .author-name::after,
        [data-post-card-shell].is-preloading .author-subline__topic::after,
        [data-post-card-shell].is-preloading .author-subline__item::after,
        [data-post-card-shell].is-preloading .post-title::after,
        [data-post-card-shell].is-preloading .post-title__link::after,
        [data-post-card-shell].is-preloading .post-summary::after,
        [data-post-card-shell].is-preloading .post-card__tag::after,
        [data-post-card-shell].is-preloading .reaction-item::after,
        [data-post-card-shell].is-preloading .action-chip__label::after {
            content: "";
            position: absolute;
            inset: -2px;
            z-index: 2;
            pointer-events: none;
            border-radius: inherit;
            background:
                linear-gradient(
                    100deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.72) 45%,
                    rgba(255, 255, 255, 0) 80%
                ),
                linear-gradient(135deg, #eef2f7 0%, #f8fafc 48%, #e5e7eb 100%);
            background-size: 220% 100%, 100% 100%;
            animation:
                postCardMediaPreload 1.05s linear infinite,
                postCardPreloadOverlayOut 0.26s ease 0.95s forwards;
        }

        [data-post-card-shell].is-preloading .author-name,
        [data-post-card-shell].is-preloading .author-subline__topic,
        [data-post-card-shell].is-preloading .author-subline__item,
        [data-post-card-shell].is-preloading .post-title__link,
        [data-post-card-shell].is-preloading .post-card__tag {
            display: inline-block;
        }

        [data-post-card-shell].is-preloading .post-title {
            display: block;
            min-height: 1.55em;
        }

        [data-post-card-shell].is-preloading .post-summary {
            display: block;
            min-height: 1.35em;
        }

        @media (prefers-color-scheme: dark) {
            [data-post-card-shell] .post-card__media-frame {
                background: #111827;
            }

            [data-post-card-shell] .post-card__media-frame::before,
            [data-post-card-shell].is-preloading .author-name::after,
            [data-post-card-shell].is-preloading .author-subline__topic::after,
            [data-post-card-shell].is-preloading .author-subline__item::after,
            [data-post-card-shell].is-preloading .post-title::after,
            [data-post-card-shell].is-preloading .post-title__link::after,
            [data-post-card-shell].is-preloading .post-summary::after,
            [data-post-card-shell].is-preloading .post-card__tag::after,
            [data-post-card-shell].is-preloading .reaction-item::after,
            [data-post-card-shell].is-preloading .action-chip__label::after {
                background:
                    linear-gradient(
                        100deg,
                        rgba(255, 255, 255, 0) 0%,
                        rgba(255, 255, 255, 0.08) 45%,
                        rgba(255, 255, 255, 0) 80%
                    ),
                    linear-gradient(135deg, #111827 0%, #1f2937 48%, #0f172a 100%);
                background-size: 220% 100%, 100% 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            [data-post-card-shell] .post-card__media-frame::before,
            [data-post-card-shell].is-preloading .author-name::after,
            [data-post-card-shell].is-preloading .author-subline__topic::after,
            [data-post-card-shell].is-preloading .author-subline__item::after,
            [data-post-card-shell].is-preloading .post-title::after,
            [data-post-card-shell].is-preloading .post-title__link::after,
            [data-post-card-shell].is-preloading .post-summary::after,
            [data-post-card-shell].is-preloading .post-card__tag::after,
            [data-post-card-shell].is-preloading .reaction-item::after,
            [data-post-card-shell].is-preloading .action-chip__label::after {
                display: none !important;
            }

            [data-post-card-shell],
            [data-post-card-shell] * {
                animation: none !important;
                transition: none !important;
            }
        }


        /* Final override: hashtag alignment and slightly larger readable text */
        [data-post-card-shell] {
            font-size: 15px;
        }

        [data-post-card-shell] .author-name {
            font-size: 14.5px !important;
            line-height: 1.35 !important;
        }

        [data-post-card-shell] .author-subline,
        [data-post-card-shell] .author-subline__topic,
        [data-post-card-shell] .post-time,
        [data-post-card-shell] .time-text {
            font-size: 12.5px !important;
            line-height: 1.4 !important;
        }

        [data-post-card-shell] .post-title {
            font-size: clamp(18px, 2.2vw, 20px) !important;
            line-height: 1.48 !important;
        }

        [data-post-card-shell] .post-summary,
        [data-post-card-shell] .comment-label {
            font-size: clamp(15px, 1.8vw, 16px) !important;
            line-height: 1.68 !important;
        }

        [data-post-card-shell] .expand-link {
            font-size: 14.5px !important;
            line-height: 1.45 !important;
            margin-top: 2px !important;
            margin-bottom: 12px !important;
        }

        [data-post-card-shell] .post-card__tags {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            flex-wrap: wrap !important;
            gap: 5px 10px !important;
            width: 100% !important;
            margin: 3px 0 12px !important;
            padding-top: 0 !important;
            text-align: left !important;
        }

        [data-post-card-shell] .post-card__tag {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 24px !important;
            color: #1b32ff !important;
            font-size: 14px !important;
            line-height: 1.45 !important;
            text-align: center !important;
            text-decoration: none !important;
            -webkit-tap-highlight-color: transparent;
            transition: color 0.12s ease, opacity 0.12s ease, background-color 0.12s ease;
        }

        [data-post-card-shell] .post-card__tag:hover,
        [data-post-card-shell] .post-card__tag:focus-visible {
            color: #1230d8 !important;
            opacity: 0.82;
            outline: none;
        }

        [data-post-card-shell] .post-card__tag:active {
            color: #0f25ad !important;
            opacity: 0.72;
        }

        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .post-metric,
        [data-post-card-shell] .action-chip__label,
        [data-post-card-shell] .reaction-count,
        [data-post-card-shell] .post-card__source-domain {
            font-size: 14px !important;
            line-height: 1.4 !important;
        }

        [data-post-card-shell] .post-card__source-label {
            font-size: 10.5px !important;
        }

        @media (max-width: 640px) {
            [data-post-card-shell] {
                font-size: 14.5px !important;
            }

            [data-post-card-shell] .post-title {
                font-size: 17.5px !important;
                line-height: 1.48 !important;
            }

            [data-post-card-shell] .post-summary,
            [data-post-card-shell] .comment-label {
                font-size: 14.5px !important;
                line-height: 1.66 !important;
            }

            [data-post-card-shell] .expand-link {
                font-size: 14px !important;
            }

            [data-post-card-shell] .post-card__tags {
                justify-content: flex-start !important;
                gap: 5px 9px !important;
                margin: 3px 0 11px !important;
                padding-top: 0 !important;
                text-align: left !important;
            }

            [data-post-card-shell] .post-card__tag {
                font-size: 13.5px !important;
                min-height: 24px !important;
            }
        }

    

        /* Final dark mode compatibility: white text/icons, soft touch states, dark dropdown */
        html.dark [data-post-card-shell],
        body.dark [data-post-card-shell],
        .dark [data-post-card-shell],
        [data-theme="dark"] [data-post-card-shell] {
            background: #0b1120 !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: none !important;
        }

        html.dark [data-post-card-shell] .post-title,
        html.dark [data-post-card-shell] .post-title__link,
        html.dark [data-post-card-shell] .post-summary,
        html.dark [data-post-card-shell] .author-name,
        html.dark [data-post-card-shell] .author-subline__topic,
        html.dark [data-post-card-shell] .comment-label,
        body.dark [data-post-card-shell] .post-title,
        body.dark [data-post-card-shell] .post-title__link,
        body.dark [data-post-card-shell] .post-summary,
        body.dark [data-post-card-shell] .author-name,
        body.dark [data-post-card-shell] .author-subline__topic,
        body.dark [data-post-card-shell] .comment-label,
        .dark [data-post-card-shell] .post-title,
        .dark [data-post-card-shell] .post-title__link,
        .dark [data-post-card-shell] .post-summary,
        .dark [data-post-card-shell] .author-name,
        .dark [data-post-card-shell] .author-subline__topic,
        .dark [data-post-card-shell] .comment-label,
        [data-theme="dark"] [data-post-card-shell] .post-title,
        [data-theme="dark"] [data-post-card-shell] .post-title__link,
        [data-theme="dark"] [data-post-card-shell] .post-summary,
        [data-theme="dark"] [data-post-card-shell] .author-name,
        [data-theme="dark"] [data-post-card-shell] .author-subline__topic,
        [data-theme="dark"] [data-post-card-shell] .comment-label {
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .author-subline,
        html.dark [data-post-card-shell] .author-subline__item,
        html.dark [data-post-card-shell] .post-time,
        html.dark [data-post-card-shell] .time-text,
        body.dark [data-post-card-shell] .author-subline,
        body.dark [data-post-card-shell] .author-subline__item,
        body.dark [data-post-card-shell] .post-time,
        body.dark [data-post-card-shell] .time-text,
        .dark [data-post-card-shell] .author-subline,
        .dark [data-post-card-shell] .author-subline__item,
        .dark [data-post-card-shell] .post-time,
        .dark [data-post-card-shell] .time-text,
        [data-theme="dark"] [data-post-card-shell] .author-subline,
        [data-theme="dark"] [data-post-card-shell] .author-subline__item,
        [data-theme="dark"] [data-post-card-shell] .post-time,
        [data-theme="dark"] [data-post-card-shell] .time-text {
            color: rgba(255, 255, 255, 0.72) !important;
        }

        html.dark [data-post-card-shell] .post-card__inline-icon,
        html.dark [data-post-card-shell] .post-card__inline-icon svg,
        html.dark [data-post-card-shell] .post-card__inline-icon iconify-icon,
        html.dark [data-post-card-shell] .post-card__bookmark-icon,
        html.dark [data-post-card-shell] .post-card__share-icon,
        html.dark [data-post-card-shell] .post-card__reaction-custom-icon,
        html.dark [data-post-card-shell] .action-btn,
        html.dark [data-post-card-shell] .action-stat,
        html.dark [data-post-card-shell] .post-metric,
        html.dark [data-post-card-shell] .action-chip__label,
        body.dark [data-post-card-shell] .post-card__inline-icon,
        body.dark [data-post-card-shell] .post-card__inline-icon svg,
        body.dark [data-post-card-shell] .post-card__inline-icon iconify-icon,
        body.dark [data-post-card-shell] .post-card__bookmark-icon,
        body.dark [data-post-card-shell] .post-card__share-icon,
        body.dark [data-post-card-shell] .post-card__reaction-custom-icon,
        body.dark [data-post-card-shell] .action-btn,
        body.dark [data-post-card-shell] .action-stat,
        body.dark [data-post-card-shell] .post-metric,
        body.dark [data-post-card-shell] .action-chip__label,
        .dark [data-post-card-shell] .post-card__inline-icon,
        .dark [data-post-card-shell] .post-card__inline-icon svg,
        .dark [data-post-card-shell] .post-card__inline-icon iconify-icon,
        .dark [data-post-card-shell] .post-card__bookmark-icon,
        .dark [data-post-card-shell] .post-card__share-icon,
        .dark [data-post-card-shell] .post-card__reaction-custom-icon,
        .dark [data-post-card-shell] .action-btn,
        .dark [data-post-card-shell] .action-stat,
        .dark [data-post-card-shell] .post-metric,
        .dark [data-post-card-shell] .action-chip__label,
        [data-theme="dark"] [data-post-card-shell] .post-card__inline-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__inline-icon svg,
        [data-theme="dark"] [data-post-card-shell] .post-card__inline-icon iconify-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__bookmark-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__share-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__reaction-custom-icon,
        [data-theme="dark"] [data-post-card-shell] .action-btn,
        [data-theme="dark"] [data-post-card-shell] .action-stat,
        [data-theme="dark"] [data-post-card-shell] .post-metric,
        [data-theme="dark"] [data-post-card-shell] .action-chip__label {
            color: #ffffff !important;
            stroke: currentColor !important;
            fill: currentColor;
        }

        html.dark [data-post-card-shell] .action-bar,
        body.dark [data-post-card-shell] .action-bar,
        .dark [data-post-card-shell] .action-bar,
        [data-theme="dark"] [data-post-card-shell] .action-bar {
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.10) !important;
        }

        html.dark [data-post-card-shell] .reaction-row,
        html.dark [data-post-card-shell] .reactions-row,
        body.dark [data-post-card-shell] .reaction-row,
        body.dark [data-post-card-shell] .reactions-row,
        .dark [data-post-card-shell] .reaction-row,
        .dark [data-post-card-shell] .reactions-row,
        [data-theme="dark"] [data-post-card-shell] .reaction-row,
        [data-theme="dark"] [data-post-card-shell] .reactions-row {
            border-bottom-color: rgba(255, 255, 255, 0.10) !important;
        }

        html.dark [data-post-card-shell] .menu-btn,
        html.dark [data-post-card-shell] .menu-button,
        html.dark [data-post-card-shell] .smiley-btn,
        html.dark [data-post-card-shell] .reaction-add,
        html.dark [data-post-card-shell] .reaction-item,
        html.dark [data-post-card-shell] .more-pill,
        html.dark [data-post-card-shell] .post-card__vote-cluster,
        body.dark [data-post-card-shell] .menu-btn,
        body.dark [data-post-card-shell] .menu-button,
        body.dark [data-post-card-shell] .smiley-btn,
        body.dark [data-post-card-shell] .reaction-add,
        body.dark [data-post-card-shell] .reaction-item,
        body.dark [data-post-card-shell] .more-pill,
        body.dark [data-post-card-shell] .post-card__vote-cluster,
        .dark [data-post-card-shell] .menu-btn,
        .dark [data-post-card-shell] .menu-button,
        .dark [data-post-card-shell] .smiley-btn,
        .dark [data-post-card-shell] .reaction-add,
        .dark [data-post-card-shell] .reaction-item,
        .dark [data-post-card-shell] .more-pill,
        .dark [data-post-card-shell] .post-card__vote-cluster,
        [data-theme="dark"] [data-post-card-shell] .menu-btn,
        [data-theme="dark"] [data-post-card-shell] .menu-button,
        [data-theme="dark"] [data-post-card-shell] .smiley-btn,
        [data-theme="dark"] [data-post-card-shell] .reaction-add,
        [data-theme="dark"] [data-post-card-shell] .reaction-item,
        [data-theme="dark"] [data-post-card-shell] .more-pill,
        [data-theme="dark"] [data-post-card-shell] .post-card__vote-cluster {
            background: transparent !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .menu-btn:hover,
        html.dark [data-post-card-shell] .menu-btn:focus-visible,
        html.dark [data-post-card-shell] .menu-button:hover,
        html.dark [data-post-card-shell] .menu-button:focus-visible,
        html.dark [data-post-card-shell] .smiley-btn:hover,
        html.dark [data-post-card-shell] .smiley-btn:focus-visible,
        html.dark [data-post-card-shell] .reaction-add:hover,
        html.dark [data-post-card-shell] .reaction-add:focus-visible,
        html.dark [data-post-card-shell] .action-btn:hover,
        html.dark [data-post-card-shell] .action-btn:focus-visible,
        html.dark [data-post-card-shell] .action-stat:hover,
        html.dark [data-post-card-shell] .action-stat:focus-visible,
        body.dark [data-post-card-shell] .menu-btn:hover,
        body.dark [data-post-card-shell] .menu-btn:focus-visible,
        body.dark [data-post-card-shell] .menu-button:hover,
        body.dark [data-post-card-shell] .menu-button:focus-visible,
        body.dark [data-post-card-shell] .smiley-btn:hover,
        body.dark [data-post-card-shell] .smiley-btn:focus-visible,
        body.dark [data-post-card-shell] .reaction-add:hover,
        body.dark [data-post-card-shell] .reaction-add:focus-visible,
        body.dark [data-post-card-shell] .action-btn:hover,
        body.dark [data-post-card-shell] .action-btn:focus-visible,
        body.dark [data-post-card-shell] .action-stat:hover,
        body.dark [data-post-card-shell] .action-stat:focus-visible,
        .dark [data-post-card-shell] .menu-btn:hover,
        .dark [data-post-card-shell] .menu-btn:focus-visible,
        .dark [data-post-card-shell] .menu-button:hover,
        .dark [data-post-card-shell] .menu-button:focus-visible,
        .dark [data-post-card-shell] .smiley-btn:hover,
        .dark [data-post-card-shell] .smiley-btn:focus-visible,
        .dark [data-post-card-shell] .reaction-add:hover,
        .dark [data-post-card-shell] .reaction-add:focus-visible,
        .dark [data-post-card-shell] .action-btn:hover,
        .dark [data-post-card-shell] .action-btn:focus-visible,
        .dark [data-post-card-shell] .action-stat:hover,
        .dark [data-post-card-shell] .action-stat:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .menu-btn:hover,
        [data-theme="dark"] [data-post-card-shell] .menu-btn:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .menu-button:hover,
        [data-theme="dark"] [data-post-card-shell] .menu-button:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .smiley-btn:hover,
        [data-theme="dark"] [data-post-card-shell] .smiley-btn:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .reaction-add:hover,
        [data-theme="dark"] [data-post-card-shell] .reaction-add:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .action-btn:hover,
        [data-theme="dark"] [data-post-card-shell] .action-btn:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .action-stat:hover,
        [data-theme="dark"] [data-post-card-shell] .action-stat:focus-visible {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            outline: none !important;
        }

        html.dark [data-post-card-shell] .menu-btn:active,
        html.dark [data-post-card-shell] .menu-button:active,
        html.dark [data-post-card-shell] .smiley-btn:active,
        html.dark [data-post-card-shell] .reaction-add:active,
        html.dark [data-post-card-shell] .action-btn:active,
        html.dark [data-post-card-shell] .action-stat:active,
        body.dark [data-post-card-shell] .menu-btn:active,
        body.dark [data-post-card-shell] .menu-button:active,
        body.dark [data-post-card-shell] .smiley-btn:active,
        body.dark [data-post-card-shell] .reaction-add:active,
        body.dark [data-post-card-shell] .action-btn:active,
        body.dark [data-post-card-shell] .action-stat:active,
        .dark [data-post-card-shell] .menu-btn:active,
        .dark [data-post-card-shell] .menu-button:active,
        .dark [data-post-card-shell] .smiley-btn:active,
        .dark [data-post-card-shell] .reaction-add:active,
        .dark [data-post-card-shell] .action-btn:active,
        .dark [data-post-card-shell] .action-stat:active,
        [data-theme="dark"] [data-post-card-shell] .menu-btn:active,
        [data-theme="dark"] [data-post-card-shell] .menu-button:active,
        [data-theme="dark"] [data-post-card-shell] .smiley-btn:active,
        [data-theme="dark"] [data-post-card-shell] .reaction-add:active,
        [data-theme="dark"] [data-post-card-shell] .action-btn:active,
        [data-theme="dark"] [data-post-card-shell] .action-stat:active {
            background: rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .post-card__menu,
        body.dark [data-post-card-shell] .post-card__menu,
        .dark [data-post-card-shell] .post-card__menu,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu {
            background: #111827 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 18px 38px rgba(0, 0, 0, 0.36) !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item,
        html.dark [data-post-card-shell] .post-card__menu-item span,
        html.dark [data-post-card-shell] .post-card__menu-item iconify-icon,
        body.dark [data-post-card-shell] .post-card__menu-item,
        body.dark [data-post-card-shell] .post-card__menu-item span,
        body.dark [data-post-card-shell] .post-card__menu-item iconify-icon,
        .dark [data-post-card-shell] .post-card__menu-item,
        .dark [data-post-card-shell] .post-card__menu-item span,
        .dark [data-post-card-shell] .post-card__menu-item iconify-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item span,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item iconify-icon {
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item:hover,
        html.dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        body.dark [data-post-card-shell] .post-card__menu-item:hover,
        body.dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        .dark [data-post-card-shell] .post-card__menu-item:hover,
        .dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:hover,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:focus-visible {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            outline: none !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item:active,
        body.dark [data-post-card-shell] .post-card__menu-item:active,
        .dark [data-post-card-shell] .post-card__menu-item:active,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:active {
            background: rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item--danger,
        html.dark [data-post-card-shell] .post-card__menu-item--danger iconify-icon,
        body.dark [data-post-card-shell] .post-card__menu-item--danger,
        body.dark [data-post-card-shell] .post-card__menu-item--danger iconify-icon,
        .dark [data-post-card-shell] .post-card__menu-item--danger,
        .dark [data-post-card-shell] .post-card__menu-item--danger iconify-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item--danger,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item--danger iconify-icon {
            color: #fca5a5 !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item--danger:hover,
        html.dark [data-post-card-shell] .post-card__menu-item--danger:focus-visible,
        body.dark [data-post-card-shell] .post-card__menu-item--danger:hover,
        body.dark [data-post-card-shell] .post-card__menu-item--danger:focus-visible,
        .dark [data-post-card-shell] .post-card__menu-item--danger:hover,
        .dark [data-post-card-shell] .post-card__menu-item--danger:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item--danger:hover,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item--danger:focus-visible {
            background: rgba(248, 113, 113, 0.12) !important;
            color: #fecaca !important;
        }

        html.dark [data-post-card-shell] .post-card__source,
        body.dark [data-post-card-shell] .post-card__source,
        .dark [data-post-card-shell] .post-card__source,
        [data-theme="dark"] [data-post-card-shell] .post-card__source {
            background: #161b22 !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        html.dark [data-post-card-shell] .post-card__source-label,
        body.dark [data-post-card-shell] .post-card__source-label,
        .dark [data-post-card-shell] .post-card__source-label,
        [data-theme="dark"] [data-post-card-shell] .post-card__source-label {
            color: #94a3b8 !important;
        }

        html.dark [data-post-card-shell] .post-card__source-favicon,
        body.dark [data-post-card-shell] .post-card__source-favicon,
        .dark [data-post-card-shell] .post-card__source-favicon,
        [data-theme="dark"] [data-post-card-shell] .post-card__source-favicon {
            background: rgba(255, 255, 255, 0.92) !important;
        }

        html.dark [data-post-card-shell] .post-card__source-domain,
        html.dark [data-post-card-shell] .post-card__source-icon,
        html.dark [data-post-card-shell] .post-card__source-icon iconify-icon,
        body.dark [data-post-card-shell] .post-card__source-domain,
        body.dark [data-post-card-shell] .post-card__source-icon,
        body.dark [data-post-card-shell] .post-card__source-icon iconify-icon,
        .dark [data-post-card-shell] .post-card__source-domain,
        .dark [data-post-card-shell] .post-card__source-icon,
        .dark [data-post-card-shell] .post-card__source-icon iconify-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__source-domain,
        [data-theme="dark"] [data-post-card-shell] .post-card__source-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__source-icon iconify-icon {
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .post-card__source:hover,
        html.dark [data-post-card-shell] .post-card__source:focus-visible,
        body.dark [data-post-card-shell] .post-card__source:hover,
        body.dark [data-post-card-shell] .post-card__source:focus-visible,
        .dark [data-post-card-shell] .post-card__source:hover,
        .dark [data-post-card-shell] .post-card__source:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .post-card__source:hover,
        [data-theme="dark"] [data-post-card-shell] .post-card__source:focus-visible {
            background: #1f2937 !important;
            border-color: rgba(255, 255, 255, 0.14) !important;
            color: #ffffff !important;
            outline: none !important;
        }

        html.dark [data-post-card-shell] .post-card__source:active,
        body.dark [data-post-card-shell] .post-card__source:active,
        .dark [data-post-card-shell] .post-card__source:active,
        [data-theme="dark"] [data-post-card-shell] .post-card__source:active {
            background: #273244 !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .post-card__tag,
        body.dark [data-post-card-shell] .post-card__tag,
        .dark [data-post-card-shell] .post-card__tag,
        [data-theme="dark"] [data-post-card-shell] .post-card__tag {
            color: #93c5fd !important;
        }

        html.dark [data-post-card-shell] .post-card__tag:hover,
        html.dark [data-post-card-shell] .post-card__tag:focus-visible,
        body.dark [data-post-card-shell] .post-card__tag:hover,
        body.dark [data-post-card-shell] .post-card__tag:focus-visible,
        .dark [data-post-card-shell] .post-card__tag:hover,
        .dark [data-post-card-shell] .post-card__tag:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .post-card__tag:hover,
        [data-theme="dark"] [data-post-card-shell] .post-card__tag:focus-visible {
            color: #bfdbfe !important;
            opacity: 1 !important;
        }


        /* Final fix: Daha fazla göster + reaction popup dark mode */
        html.dark [data-post-card-shell] .expand-link,
        body.dark [data-post-card-shell] .expand-link,
        .dark [data-post-card-shell] .expand-link,
        [data-theme="dark"] [data-post-card-shell] .expand-link {
            color: rgba(255, 255, 255, 0.88) !important;
            background: transparent !important;
        }

        html.dark [data-post-card-shell] .expand-link iconify-icon,
        body.dark [data-post-card-shell] .expand-link iconify-icon,
        .dark [data-post-card-shell] .expand-link iconify-icon,
        [data-theme="dark"] [data-post-card-shell] .expand-link iconify-icon,
        html.dark [data-post-card-shell] .post-card__expand-icon,
        body.dark [data-post-card-shell] .post-card__expand-icon,
        .dark [data-post-card-shell] .post-card__expand-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__expand-icon {
            color: rgba(255, 255, 255, 0.88) !important;
        }

        html.dark [data-post-card-shell] .expand-link:hover,
        html.dark [data-post-card-shell] .expand-link:focus-visible,
        body.dark [data-post-card-shell] .expand-link:hover,
        body.dark [data-post-card-shell] .expand-link:focus-visible,
        .dark [data-post-card-shell] .expand-link:hover,
        .dark [data-post-card-shell] .expand-link:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .expand-link:hover,
        [data-theme="dark"] [data-post-card-shell] .expand-link:focus-visible {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.07) !important;
            outline: none !important;
        }

        html.dark [data-post-card-shell] .expand-link:active,
        body.dark [data-post-card-shell] .expand-link:active,
        .dark [data-post-card-shell] .expand-link:active,
        [data-theme="dark"] [data-post-card-shell] .expand-link:active {
            background: rgba(255, 255, 255, 0.11) !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-reaction-layer] .post-card__reaction-menu,
        body.dark [data-post-card-reaction-layer] .post-card__reaction-menu,
        .dark [data-post-card-reaction-layer] .post-card__reaction-menu,
        [data-theme="dark"] [data-post-card-reaction-layer] .post-card__reaction-menu,
        html.dark [data-post-card-shell] .post-card__reaction-menu,
        body.dark [data-post-card-shell] .post-card__reaction-menu,
        .dark [data-post-card-shell] .post-card__reaction-menu,
        [data-theme="dark"] [data-post-card-shell] .post-card__reaction-menu,
        html.dark [data-post-card-reaction-menu],
        body.dark [data-post-card-reaction-menu],
        .dark [data-post-card-reaction-menu],
        [data-theme="dark"] [data-post-card-reaction-menu] {
            background: #111827 !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 18px 38px rgba(0, 0, 0, 0.42) !important;
        }

        html.dark [data-post-card-reaction-layer] .post-card__reaction-menu-title,
        body.dark [data-post-card-reaction-layer] .post-card__reaction-menu-title,
        .dark [data-post-card-reaction-layer] .post-card__reaction-menu-title,
        [data-theme="dark"] [data-post-card-reaction-layer] .post-card__reaction-menu-title,
        html.dark [data-post-card-reaction-menu] .post-card__reaction-menu-title,
        body.dark [data-post-card-reaction-menu] .post-card__reaction-menu-title,
        .dark [data-post-card-reaction-menu] .post-card__reaction-menu-title,
        [data-theme="dark"] [data-post-card-reaction-menu] .post-card__reaction-menu-title {
            color: rgba(255, 255, 255, 0.72) !important;
        }

        html.dark [data-post-card-reaction-layer] .post-card__reaction-option,
        body.dark [data-post-card-reaction-layer] .post-card__reaction-option,
        .dark [data-post-card-reaction-layer] .post-card__reaction-option,
        [data-theme="dark"] [data-post-card-reaction-layer] .post-card__reaction-option,
        html.dark [data-post-card-shell] .post-card__reaction-option,
        body.dark [data-post-card-shell] .post-card__reaction-option,
        .dark [data-post-card-shell] .post-card__reaction-option,
        [data-theme="dark"] [data-post-card-shell] .post-card__reaction-option,
        html.dark [data-post-card-reaction-menu] .post-card__reaction-option,
        body.dark [data-post-card-reaction-menu] .post-card__reaction-option,
        .dark [data-post-card-reaction-menu] .post-card__reaction-option,
        [data-theme="dark"] [data-post-card-reaction-menu] .post-card__reaction-option {
            background: transparent !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-reaction-layer] .post-card__reaction-option:hover,
        html.dark [data-post-card-reaction-layer] .post-card__reaction-option:focus-visible,
        body.dark [data-post-card-reaction-layer] .post-card__reaction-option:hover,
        body.dark [data-post-card-reaction-layer] .post-card__reaction-option:focus-visible,
        .dark [data-post-card-reaction-layer] .post-card__reaction-option:hover,
        .dark [data-post-card-reaction-layer] .post-card__reaction-option:focus-visible,
        [data-theme="dark"] [data-post-card-reaction-layer] .post-card__reaction-option:hover,
        [data-theme="dark"] [data-post-card-reaction-layer] .post-card__reaction-option:focus-visible,
        html.dark [data-post-card-reaction-menu] .post-card__reaction-option:hover,
        html.dark [data-post-card-reaction-menu] .post-card__reaction-option:focus-visible,
        body.dark [data-post-card-reaction-menu] .post-card__reaction-option:hover,
        body.dark [data-post-card-reaction-menu] .post-card__reaction-option:focus-visible,
        .dark [data-post-card-reaction-menu] .post-card__reaction-option:hover,
        .dark [data-post-card-reaction-menu] .post-card__reaction-option:focus-visible,
        [data-theme="dark"] [data-post-card-reaction-menu] .post-card__reaction-option:hover,
        [data-theme="dark"] [data-post-card-reaction-menu] .post-card__reaction-option:focus-visible {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            outline: none !important;
        }

        html.dark [data-post-card-reaction-layer] .post-card__reaction-option:active,
        body.dark [data-post-card-reaction-layer] .post-card__reaction-option:active,
        .dark [data-post-card-reaction-layer] .post-card__reaction-option:active,
        [data-theme="dark"] [data-post-card-reaction-layer] .post-card__reaction-option:active,
        html.dark [data-post-card-reaction-menu] .post-card__reaction-option:active,
        body.dark [data-post-card-reaction-menu] .post-card__reaction-option:active,
        .dark [data-post-card-reaction-menu] .post-card__reaction-option:active,
        [data-theme="dark"] [data-post-card-reaction-menu] .post-card__reaction-option:active {
            background: rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-reaction-layer] .reaction-emoji,
        html.dark [data-post-card-reaction-layer] .reaction-emoji--html,
        body.dark [data-post-card-reaction-layer] .reaction-emoji,
        body.dark [data-post-card-reaction-layer] .reaction-emoji--html,
        .dark [data-post-card-reaction-layer] .reaction-emoji,
        .dark [data-post-card-reaction-layer] .reaction-emoji--html,
        [data-theme="dark"] [data-post-card-reaction-layer] .reaction-emoji,
        [data-theme="dark"] [data-post-card-reaction-layer] .reaction-emoji--html {
            color: #ffffff !important;
        }


        /* Uc nokta menusu: hover ve tiklama alaninin tamamini gri yap */
        [data-post-card-shell] .post-card__menu-item,
        [data-post-card-shell] .post-card__menu button.post-card__menu-item,
        [data-post-card-shell] .post-card__menu a.post-card__menu-item {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            border-radius: 10px !important;
            background: transparent !important;
            color: #334155 !important;
            cursor: pointer !important;
            transition: background-color .14s ease, color .14s ease !important;
        }

        [data-post-card-shell] .post-card__menu-item span,
        [data-post-card-shell] .post-card__menu-item iconify-icon {
            color: currentColor !important;
        }

        [data-post-card-shell] .post-card__menu-item:hover,
        [data-post-card-shell] .post-card__menu-item:focus-visible {
            background: #f1f5f9 !important;
            color: #111827 !important;
            outline: none !important;
        }

        [data-post-card-shell] .post-card__menu-item:active {
            background: #e5e7eb !important;
            color: #111827 !important;
        }

        [data-post-card-shell] .post-card__menu-item--danger,
        [data-post-card-shell] .post-card__menu-item--danger iconify-icon {
            color: #b42318 !important;
        }

        [data-post-card-shell] .post-card__menu-item--danger:hover,
        [data-post-card-shell] .post-card__menu-item--danger:focus-visible {
            background: #f1f5f9 !important;
            color: #b42318 !important;
        }

        [data-post-card-shell] .post-card__menu-item--danger:active {
            background: #e5e7eb !important;
            color: #991b1b !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item,
        body.dark [data-post-card-shell] .post-card__menu-item,
        .dark [data-post-card-shell] .post-card__menu-item,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item {
            background: transparent !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item:hover,
        html.dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        body.dark [data-post-card-shell] .post-card__menu-item:hover,
        body.dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        .dark [data-post-card-shell] .post-card__menu-item:hover,
        .dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:hover,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:focus-visible {
            background: rgba(148, 163, 184, 0.18) !important;
            color: #ffffff !important;
            outline: none !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item:active,
        body.dark [data-post-card-shell] .post-card__menu-item:active,
        .dark [data-post-card-shell] .post-card__menu-item:active,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:active {
            background: rgba(148, 163, 184, 0.28) !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item--danger,
        html.dark [data-post-card-shell] .post-card__menu-item--danger iconify-icon,
        body.dark [data-post-card-shell] .post-card__menu-item--danger,
        body.dark [data-post-card-shell] .post-card__menu-item--danger iconify-icon,
        .dark [data-post-card-shell] .post-card__menu-item--danger,
        .dark [data-post-card-shell] .post-card__menu-item--danger iconify-icon,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item--danger,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item--danger iconify-icon {
            color: #fca5a5 !important;
        }



        /* Force menu item full-row gray hover/active states */
        [data-post-card-shell] .post-card__menu,
        [data-post-card-shell] .post-card__menu * {
            box-sizing: border-box !important;
        }

        [data-post-card-shell] .post-card__menu-form {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        [data-post-card-shell] .post-card__menu-item,
        [data-post-card-shell] .post-card__menu a.post-card__menu-item,
        [data-post-card-shell] .post-card__menu button.post-card__menu-item {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            width: 100% !important;
            min-width: 100% !important;
            min-height: 42px !important;
            padding: 10px 12px !important;
            border: 0 !important;
            border-radius: 10px !important;
            background: transparent !important;
            color: #334155 !important;
            text-align: left !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: background-color .12s ease, color .12s ease !important;
        }

        [data-post-card-shell] .post-card__menu-item span,
        [data-post-card-shell] .post-card__menu-item iconify-icon {
            color: inherit !important;
        }

        [data-post-card-shell] .post-card__menu-item:hover,
        [data-post-card-shell] .post-card__menu-item:focus,
        [data-post-card-shell] .post-card__menu-item:focus-visible,
        [data-post-card-shell] .post-card__menu-form:hover > .post-card__menu-item,
        [data-post-card-shell] .post-card__menu-form:focus-within > .post-card__menu-item {
            background: #f3f4f6 !important;
            color: #111827 !important;
            outline: none !important;
        }

        [data-post-card-shell] .post-card__menu-item:active,
        [data-post-card-shell] .post-card__menu-form:active > .post-card__menu-item {
            background: #e5e7eb !important;
            color: #111827 !important;
        }

        [data-post-card-shell] .post-card__menu-item--danger {
            color: #b42318 !important;
        }

        [data-post-card-shell] .post-card__menu-item--danger:hover,
        [data-post-card-shell] .post-card__menu-item--danger:focus,
        [data-post-card-shell] .post-card__menu-item--danger:focus-visible,
        [data-post-card-shell] .post-card__menu-form:hover > .post-card__menu-item--danger,
        [data-post-card-shell] .post-card__menu-form:focus-within > .post-card__menu-item--danger {
            background: #f3f4f6 !important;
            color: #991b1b !important;
        }

        [data-post-card-shell] .post-card__menu-item--danger:active,
        [data-post-card-shell] .post-card__menu-form:active > .post-card__menu-item--danger {
            background: #e5e7eb !important;
            color: #7f1d1d !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item,
        body.dark [data-post-card-shell] .post-card__menu-item,
        .dark [data-post-card-shell] .post-card__menu-item,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item {
            color: #f8fafc !important;
            background: transparent !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item:hover,
        html.dark [data-post-card-shell] .post-card__menu-item:focus,
        html.dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        html.dark [data-post-card-shell] .post-card__menu-form:hover > .post-card__menu-item,
        body.dark [data-post-card-shell] .post-card__menu-item:hover,
        body.dark [data-post-card-shell] .post-card__menu-item:focus,
        body.dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        body.dark [data-post-card-shell] .post-card__menu-form:hover > .post-card__menu-item,
        .dark [data-post-card-shell] .post-card__menu-item:hover,
        .dark [data-post-card-shell] .post-card__menu-item:focus,
        .dark [data-post-card-shell] .post-card__menu-item:focus-visible,
        .dark [data-post-card-shell] .post-card__menu-form:hover > .post-card__menu-item,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:hover,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:focus,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-form:hover > .post-card__menu-item {
            background: rgba(255, 255, 255, 0.10) !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-item:active,
        html.dark [data-post-card-shell] .post-card__menu-form:active > .post-card__menu-item,
        body.dark [data-post-card-shell] .post-card__menu-item:active,
        body.dark [data-post-card-shell] .post-card__menu-form:active > .post-card__menu-item,
        .dark [data-post-card-shell] .post-card__menu-item:active,
        .dark [data-post-card-shell] .post-card__menu-form:active > .post-card__menu-item,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-item:active,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-form:active > .post-card__menu-item {
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
        }



        /* FINAL FIX: menu items full-row gray hover/active for a/button/form buttons */
        [data-post-card-shell] [data-post-card-menu] {
            overflow: hidden !important;
        }

        [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item,
        [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item,
        [data-post-card-shell] [data-post-card-menu] .post-card__menu-form > button.post-card__menu-item,
        [data-post-card-shell] .post-card__menu a.post-card__menu-item,
        [data-post-card-shell] .post-card__menu button.post-card__menu-item,
        [data-post-card-shell] .post-card__menu .post-card__menu-form > button.post-card__menu-item {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 10px !important;
            width: 100% !important;
            min-width: 100% !important;
            min-height: 40px !important;
            padding: 10px 12px !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 10px !important;
            background: transparent !important;
            box-shadow: none !important;
            color: #334155 !important;
            text-align: left !important;
            text-decoration: none !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: background-color .12s ease, color .12s ease !important;
        }

        [data-post-card-shell] [data-post-card-menu] .post-card__menu-form,
        [data-post-card-shell] .post-card__menu .post-card__menu-form {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:hover,
        [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:focus,
        [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:focus-visible,
        [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:hover,
        [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:focus,
        [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:focus-visible,
        [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:hover > button.post-card__menu-item,
        [data-post-card-shell] .post-card__menu a.post-card__menu-item:hover,
        [data-post-card-shell] .post-card__menu a.post-card__menu-item:focus,
        [data-post-card-shell] .post-card__menu a.post-card__menu-item:focus-visible,
        [data-post-card-shell] .post-card__menu button.post-card__menu-item:hover,
        [data-post-card-shell] .post-card__menu button.post-card__menu-item:focus,
        [data-post-card-shell] .post-card__menu button.post-card__menu-item:focus-visible,
        [data-post-card-shell] .post-card__menu .post-card__menu-form:hover > button.post-card__menu-item {
            background-color: #f1f5f9 !important;
            background-image: none !important;
            color: #0f172a !important;
            outline: none !important;
        }

        [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:active,
        [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:active,
        [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:active > button.post-card__menu-item,
        [data-post-card-shell] .post-card__menu a.post-card__menu-item:active,
        [data-post-card-shell] .post-card__menu button.post-card__menu-item:active,
        [data-post-card-shell] .post-card__menu .post-card__menu-form:active > button.post-card__menu-item {
            background-color: #e5e7eb !important;
            background-image: none !important;
            color: #0f172a !important;
        }

        [data-post-card-shell] [data-post-card-menu] .post-card__menu-item span,
        [data-post-card-shell] [data-post-card-menu] .post-card__menu-item iconify-icon,
        [data-post-card-shell] .post-card__menu .post-card__menu-item span,
        [data-post-card-shell] .post-card__menu .post-card__menu-item iconify-icon {
            color: inherit !important;
            pointer-events: none !important;
        }

        [data-post-card-shell] [data-post-card-menu] .post-card__menu-item--danger,
        [data-post-card-shell] .post-card__menu .post-card__menu-item--danger {
            color: #b42318 !important;
        }

        [data-post-card-shell] [data-post-card-menu] .post-card__menu-item--danger:hover,
        [data-post-card-shell] [data-post-card-menu] .post-card__menu-item--danger:focus,
        [data-post-card-shell] [data-post-card-menu] .post-card__menu-item--danger:focus-visible,
        [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:hover > .post-card__menu-item--danger,
        [data-post-card-shell] .post-card__menu .post-card__menu-item--danger:hover,
        [data-post-card-shell] .post-card__menu .post-card__menu-item--danger:focus,
        [data-post-card-shell] .post-card__menu .post-card__menu-item--danger:focus-visible,
        [data-post-card-shell] .post-card__menu .post-card__menu-form:hover > .post-card__menu-item--danger {
            background-color: #f1f5f9 !important;
            color: #b42318 !important;
        }

        [data-post-card-shell] [data-post-card-menu] .post-card__menu-item--danger:active,
        [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:active > .post-card__menu-item--danger,
        [data-post-card-shell] .post-card__menu .post-card__menu-item--danger:active,
        [data-post-card-shell] .post-card__menu .post-card__menu-form:active > .post-card__menu-item--danger {
            background-color: #e5e7eb !important;
            color: #991b1b !important;
        }

        html.dark [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:hover,
        html.dark [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:focus,
        html.dark [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:focus-visible,
        html.dark [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:hover,
        html.dark [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:focus,
        html.dark [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:focus-visible,
        html.dark [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:hover > button.post-card__menu-item,
        body.dark [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:hover,
        body.dark [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:hover,
        body.dark [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:hover > button.post-card__menu-item,
        .dark [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:hover,
        .dark [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:hover,
        .dark [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:hover > button.post-card__menu-item,
        [data-theme="dark"] [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:hover,
        [data-theme="dark"] [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:hover,
        [data-theme="dark"] [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:hover > button.post-card__menu-item {
            background-color: rgba(255, 255, 255, 0.10) !important;
            background-image: none !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:active,
        html.dark [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:active,
        html.dark [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:active > button.post-card__menu-item,
        body.dark [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:active,
        body.dark [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:active,
        body.dark [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:active > button.post-card__menu-item,
        .dark [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:active,
        .dark [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:active,
        .dark [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:active > button.post-card__menu-item,
        [data-theme="dark"] [data-post-card-shell] [data-post-card-menu] a.post-card__menu-item:active,
        [data-theme="dark"] [data-post-card-shell] [data-post-card-menu] button.post-card__menu-item:active,
        [data-theme="dark"] [data-post-card-shell] [data-post-card-menu] .post-card__menu-form:active > button.post-card__menu-item {
            background-color: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
        }



        /* Alt aksiyon ikonlari: yorum, bookmark, paylas, view - genis yuvarlak gri arka plan */
        [data-post-card-shell] .action-bar .action-btn,
        [data-post-card-shell] .action-bar .post-card__action-link,
        [data-post-card-shell] .action-bar .post-card__action-button,
        [data-post-card-shell] .action-bar .action-chip,
        [data-post-card-shell] .action-bar .post-metric,
        [data-post-card-shell] .action-bar .post-metric--views {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            min-width: 44px !important;
            height: 36px !important;
            padding: 0 13px !important;
            border: 0 !important;
            border-radius: 999px !important;
            background: #f1f5f9 !important;
            color: #475569 !important;
            line-height: 1 !important;
            text-decoration: none !important;
            box-shadow: none !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: background-color .14s ease, color .14s ease, transform .12s ease !important;
        }

        [data-post-card-shell] .action-bar .post-card__action-form {
            display: inline-flex !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        [data-post-card-shell] .action-bar .action-left {
            gap: 9px !important;
            align-items: center !important;
        }

        [data-post-card-shell] .action-bar .post-card__inline-icon {
            width: 18px !important;
            height: 18px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 0 0 18px !important;
            color: currentColor !important;
        }

        [data-post-card-shell] .action-bar .post-card__inline-icon iconify-icon,
        [data-post-card-shell] .action-bar .post-card__inline-icon svg,
        [data-post-card-shell] .action-bar .post-card__bookmark-icon,
        [data-post-card-shell] .action-bar .post-card__share-icon {
            width: 18px !important;
            height: 18px !important;
            font-size: 18px !important;
            color: currentColor !important;
        }

        [data-post-card-shell] .action-bar .action-chip__label,
        [data-post-card-shell] .action-bar [data-post-card-view-count] {
            color: currentColor !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
        }

        [data-post-card-shell] .action-bar a.action-btn:hover,
        [data-post-card-shell] .action-bar button.action-btn:hover,
        [data-post-card-shell] .action-bar .action-btn:hover,
        [data-post-card-shell] .action-bar .post-card__action-link:hover,
        [data-post-card-shell] .action-bar .post-card__action-button:hover,
        [data-post-card-shell] .action-bar .action-chip:hover,
        [data-post-card-shell] .action-bar .post-metric:hover,
        [data-post-card-shell] .action-bar .post-metric--views:hover,
        [data-post-card-shell] .action-bar a.action-btn:focus-visible,
        [data-post-card-shell] .action-bar button.action-btn:focus-visible,
        [data-post-card-shell] .action-bar .post-card__action-link:focus-visible,
        [data-post-card-shell] .action-bar .post-card__action-button:focus-visible {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            border-radius: 999px !important;
            outline: none !important;
            transform: translateY(-1px) !important;
        }

        [data-post-card-shell] .action-bar a.action-btn:active,
        [data-post-card-shell] .action-bar button.action-btn:active,
        [data-post-card-shell] .action-bar .action-btn:active,
        [data-post-card-shell] .action-bar .post-card__action-link:active,
        [data-post-card-shell] .action-bar .post-card__action-button:active,
        [data-post-card-shell] .action-bar .action-chip:active,
        [data-post-card-shell] .action-bar .post-metric:active,
        [data-post-card-shell] .action-bar .post-metric--views:active {
            background: #cbd5e1 !important;
            color: #020617 !important;
            border-radius: 999px !important;
            transform: translateY(0) scale(.98) !important;
        }

        [data-post-card-shell] .action-bar .action-chip.is-bookmarked,
        [data-post-card-shell] .action-bar .action-chip.is-active,
        [data-post-card-shell] .action-bar .post-card__action-button.is-bookmarked {
            background: #dbeafe !important;
            color: #2563eb !important;
        }

        [data-post-card-shell] .action-bar .action-chip.is-bookmarked:hover,
        [data-post-card-shell] .action-bar .post-card__action-button.is-bookmarked:hover {
            background: #bfdbfe !important;
            color: #1d4ed8 !important;
        }

        [data-post-card-shell] .action-bar [data-post-card-view-metric][hidden],
        [data-post-card-shell] .action-bar .post-metric--views[hidden] {
            display: none !important;
        }

        html.dark [data-post-card-shell] .action-bar .action-btn,
        html.dark [data-post-card-shell] .action-bar .post-card__action-link,
        html.dark [data-post-card-shell] .action-bar .post-card__action-button,
        html.dark [data-post-card-shell] .action-bar .action-chip,
        html.dark [data-post-card-shell] .action-bar .post-metric,
        body.dark [data-post-card-shell] .action-bar .action-btn,
        body.dark [data-post-card-shell] .action-bar .post-card__action-link,
        body.dark [data-post-card-shell] .action-bar .post-card__action-button,
        body.dark [data-post-card-shell] .action-bar .action-chip,
        body.dark [data-post-card-shell] .action-bar .post-metric,
        .dark [data-post-card-shell] .action-bar .action-btn,
        .dark [data-post-card-shell] .action-bar .post-card__action-link,
        .dark [data-post-card-shell] .action-bar .post-card__action-button,
        .dark [data-post-card-shell] .action-bar .action-chip,
        .dark [data-post-card-shell] .action-bar .post-metric,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-btn,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-link,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-button,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-chip,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-metric {
            background: rgba(148, 163, 184, .16) !important;
            color: #e5e7eb !important;
        }

        html.dark [data-post-card-shell] .action-bar .action-btn:hover,
        html.dark [data-post-card-shell] .action-bar .post-card__action-link:hover,
        html.dark [data-post-card-shell] .action-bar .post-card__action-button:hover,
        html.dark [data-post-card-shell] .action-bar .action-chip:hover,
        html.dark [data-post-card-shell] .action-bar .post-metric:hover,
        body.dark [data-post-card-shell] .action-bar .action-btn:hover,
        body.dark [data-post-card-shell] .action-bar .post-card__action-link:hover,
        body.dark [data-post-card-shell] .action-bar .post-card__action-button:hover,
        body.dark [data-post-card-shell] .action-bar .action-chip:hover,
        body.dark [data-post-card-shell] .action-bar .post-metric:hover,
        .dark [data-post-card-shell] .action-bar .action-btn:hover,
        .dark [data-post-card-shell] .action-bar .post-card__action-link:hover,
        .dark [data-post-card-shell] .action-bar .post-card__action-button:hover,
        .dark [data-post-card-shell] .action-bar .action-chip:hover,
        .dark [data-post-card-shell] .action-bar .post-metric:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-btn:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-link:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-button:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-chip:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-metric:hover {
            background: rgba(148, 163, 184, .26) !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .action-bar .action-btn:active,
        html.dark [data-post-card-shell] .action-bar .post-card__action-link:active,
        html.dark [data-post-card-shell] .action-bar .post-card__action-button:active,
        html.dark [data-post-card-shell] .action-bar .action-chip:active,
        html.dark [data-post-card-shell] .action-bar .post-metric:active,
        body.dark [data-post-card-shell] .action-bar .action-btn:active,
        body.dark [data-post-card-shell] .action-bar .post-card__action-link:active,
        body.dark [data-post-card-shell] .action-bar .post-card__action-button:active,
        body.dark [data-post-card-shell] .action-bar .action-chip:active,
        body.dark [data-post-card-shell] .action-bar .post-metric:active,
        .dark [data-post-card-shell] .action-bar .action-btn:active,
        .dark [data-post-card-shell] .action-bar .post-card__action-link:active,
        .dark [data-post-card-shell] .action-bar .post-card__action-button:active,
        .dark [data-post-card-shell] .action-bar .action-chip:active,
        .dark [data-post-card-shell] .action-bar .post-metric:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-btn:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-link:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-button:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-chip:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-metric:active {
            background: rgba(148, 163, 184, .36) !important;
            color: #ffffff !important;
        }

        html.dark [data-post-card-shell] .action-bar .action-chip.is-bookmarked,
        body.dark [data-post-card-shell] .action-bar .action-chip.is-bookmarked,
        .dark [data-post-card-shell] .action-bar .action-chip.is-bookmarked,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-chip.is-bookmarked,
        html.dark [data-post-card-shell] .action-bar .post-card__action-button.is-bookmarked,
        body.dark [data-post-card-shell] .action-bar .post-card__action-button.is-bookmarked,
        .dark [data-post-card-shell] .action-bar .post-card__action-button.is-bookmarked,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-button.is-bookmarked {
            background: rgba(37, 99, 235, .24) !important;
            color: #93c5fd !important;
        }


        /* FINAL OVERRIDE: alt ikonlar sadece hover/tiklama aninda gri, efektsiz */
        [data-post-card-shell] .action-bar .action-btn,
        [data-post-card-shell] .action-bar .post-card__action-link,
        [data-post-card-shell] .action-bar .post-card__action-button,
        [data-post-card-shell] .action-bar .action-chip,
        [data-post-card-shell] .action-bar .post-metric,
        [data-post-card-shell] .action-bar .post-metric--views {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            min-width: 44px !important;
            height: 36px !important;
            padding: 0 13px !important;
            border: 0 !important;
            border-radius: 999px !important;
            background: transparent !important;
            color: #475569 !important;
            line-height: 1 !important;
            text-decoration: none !important;
            box-shadow: none !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: none !important;
            transform: none !important;
        }

        [data-post-card-shell] .action-bar .post-card__action-form {
            display: inline-flex !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        [data-post-card-shell] .action-bar .action-left {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        [data-post-card-shell] .action-bar .post-card__inline-icon {
            width: 18px !important;
            height: 18px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 0 0 18px !important;
            color: currentColor !important;
        }

        [data-post-card-shell] .action-bar .post-card__inline-icon iconify-icon,
        [data-post-card-shell] .action-bar .post-card__inline-icon svg,
        [data-post-card-shell] .action-bar .post-card__bookmark-icon,
        [data-post-card-shell] .action-bar .post-card__share-icon {
            width: 18px !important;
            height: 18px !important;
            font-size: 18px !important;
            color: currentColor !important;
        }

        [data-post-card-shell] .action-bar .action-chip__label,
        [data-post-card-shell] .action-bar [data-post-card-view-count] {
            color: currentColor !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
        }

        [data-post-card-shell] .action-bar a.action-btn:hover,
        [data-post-card-shell] .action-bar button.action-btn:hover,
        [data-post-card-shell] .action-bar .action-btn:hover,
        [data-post-card-shell] .action-bar .post-card__action-link:hover,
        [data-post-card-shell] .action-bar .post-card__action-button:hover,
        [data-post-card-shell] .action-bar .action-chip:hover,
        [data-post-card-shell] .action-bar .post-metric:hover,
        [data-post-card-shell] .action-bar .post-metric--views:hover,
        [data-post-card-shell] .action-bar a.action-btn:focus-visible,
        [data-post-card-shell] .action-bar button.action-btn:focus-visible,
        [data-post-card-shell] .action-bar .action-btn:focus-visible,
        [data-post-card-shell] .action-bar .post-card__action-link:focus-visible,
        [data-post-card-shell] .action-bar .post-card__action-button:focus-visible,
        [data-post-card-shell] .action-bar .action-chip:focus-visible,
        [data-post-card-shell] .action-bar .post-metric:focus-visible,
        [data-post-card-shell] .action-bar .post-metric--views:focus-visible {
            background: #f1f5f9 !important;
            color: #334155 !important;
            border-radius: 999px !important;
            outline: none !important;
            transition: none !important;
            transform: none !important;
        }

        [data-post-card-shell] .action-bar a.action-btn:active,
        [data-post-card-shell] .action-bar button.action-btn:active,
        [data-post-card-shell] .action-bar .action-btn:active,
        [data-post-card-shell] .action-bar .post-card__action-link:active,
        [data-post-card-shell] .action-bar .post-card__action-button:active,
        [data-post-card-shell] .action-bar .action-chip:active,
        [data-post-card-shell] .action-bar .post-metric:active,
        [data-post-card-shell] .action-bar .post-metric--views:active {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            border-radius: 999px !important;
            transition: none !important;
            transform: none !important;
        }

        [data-post-card-shell] .action-bar .action-chip.is-bookmarked,
        [data-post-card-shell] .action-bar .action-chip.is-active,
        [data-post-card-shell] .action-bar .post-card__action-button.is-bookmarked,
        [data-post-card-shell] .action-bar .post-card__action-button.is-active {
            background: transparent !important;
            color: #2563eb !important;
            transition: none !important;
            transform: none !important;
        }

        [data-post-card-shell] .action-bar .action-chip.is-bookmarked:hover,
        [data-post-card-shell] .action-bar .action-chip.is-active:hover,
        [data-post-card-shell] .action-bar .post-card__action-button.is-bookmarked:hover,
        [data-post-card-shell] .action-bar .post-card__action-button.is-active:hover {
            background: #f1f5f9 !important;
            color: #2563eb !important;
            transform: none !important;
        }

        [data-post-card-shell] .action-bar [data-post-card-view-metric][hidden],
        [data-post-card-shell] .action-bar .post-metric--views[hidden] {
            display: none !important;
        }

        html.dark [data-post-card-shell] .action-bar .action-btn,
        html.dark [data-post-card-shell] .action-bar .post-card__action-link,
        html.dark [data-post-card-shell] .action-bar .post-card__action-button,
        html.dark [data-post-card-shell] .action-bar .action-chip,
        html.dark [data-post-card-shell] .action-bar .post-metric,
        body.dark [data-post-card-shell] .action-bar .action-btn,
        body.dark [data-post-card-shell] .action-bar .post-card__action-link,
        body.dark [data-post-card-shell] .action-bar .post-card__action-button,
        body.dark [data-post-card-shell] .action-bar .action-chip,
        body.dark [data-post-card-shell] .action-bar .post-metric,
        .dark [data-post-card-shell] .action-bar .action-btn,
        .dark [data-post-card-shell] .action-bar .post-card__action-link,
        .dark [data-post-card-shell] .action-bar .post-card__action-button,
        .dark [data-post-card-shell] .action-bar .action-chip,
        .dark [data-post-card-shell] .action-bar .post-metric,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-btn,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-link,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-button,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-chip,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-metric {
            background: transparent !important;
            color: #e5e7eb !important;
            transition: none !important;
            transform: none !important;
        }

        html.dark [data-post-card-shell] .action-bar .action-btn:hover,
        html.dark [data-post-card-shell] .action-bar .post-card__action-link:hover,
        html.dark [data-post-card-shell] .action-bar .post-card__action-button:hover,
        html.dark [data-post-card-shell] .action-bar .action-chip:hover,
        html.dark [data-post-card-shell] .action-bar .post-metric:hover,
        body.dark [data-post-card-shell] .action-bar .action-btn:hover,
        body.dark [data-post-card-shell] .action-bar .post-card__action-link:hover,
        body.dark [data-post-card-shell] .action-bar .post-card__action-button:hover,
        body.dark [data-post-card-shell] .action-bar .action-chip:hover,
        body.dark [data-post-card-shell] .action-bar .post-metric:hover,
        .dark [data-post-card-shell] .action-bar .action-btn:hover,
        .dark [data-post-card-shell] .action-bar .post-card__action-link:hover,
        .dark [data-post-card-shell] .action-bar .post-card__action-button:hover,
        .dark [data-post-card-shell] .action-bar .action-chip:hover,
        .dark [data-post-card-shell] .action-bar .post-metric:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-btn:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-link:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-button:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-chip:hover,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-metric:hover {
            background: rgba(148, 163, 184, .22) !important;
            color: #ffffff !important;
            transition: none !important;
            transform: none !important;
        }

        html.dark [data-post-card-shell] .action-bar .action-btn:active,
        html.dark [data-post-card-shell] .action-bar .post-card__action-link:active,
        html.dark [data-post-card-shell] .action-bar .post-card__action-button:active,
        html.dark [data-post-card-shell] .action-bar .action-chip:active,
        html.dark [data-post-card-shell] .action-bar .post-metric:active,
        body.dark [data-post-card-shell] .action-bar .action-btn:active,
        body.dark [data-post-card-shell] .action-bar .post-card__action-link:active,
        body.dark [data-post-card-shell] .action-bar .post-card__action-button:active,
        body.dark [data-post-card-shell] .action-bar .action-chip:active,
        body.dark [data-post-card-shell] .action-bar .post-metric:active,
        .dark [data-post-card-shell] .action-bar .action-btn:active,
        .dark [data-post-card-shell] .action-bar .post-card__action-link:active,
        .dark [data-post-card-shell] .action-bar .post-card__action-button:active,
        .dark [data-post-card-shell] .action-bar .action-chip:active,
        .dark [data-post-card-shell] .action-bar .post-metric:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-btn:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-link:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-card__action-button:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .action-chip:active,
        [data-theme="dark"] [data-post-card-shell] .action-bar .post-metric:active {
            background: rgba(148, 163, 184, .32) !important;
            color: #ffffff !important;
            transition: none !important;
            transform: none !important;
        }



        /* FINAL OVERRIDE: action icons larger, round 3-dot button, white fade above continue link */
        [data-post-card-shell] .action-bar .action-btn,
        [data-post-card-shell] .action-bar .post-card__action-link,
        [data-post-card-shell] .action-bar .post-card__action-button,
        [data-post-card-shell] .action-bar .action-chip,
        [data-post-card-shell] .action-bar .post-metric,
        [data-post-card-shell] .action-bar .post-metric--views {
            min-width: 48px !important;
            height: 38px !important;
            padding: 0 14px !important;
            border-radius: 999px !important;
        }

        [data-post-card-shell] .action-bar .action-left {
            gap: 10px !important;
        }

        [data-post-card-shell] .action-bar .post-card__inline-icon {
            width: 20px !important;
            height: 20px !important;
            flex: 0 0 20px !important;
        }

        [data-post-card-shell] .action-bar .post-card__inline-icon iconify-icon,
        [data-post-card-shell] .action-bar .post-card__inline-icon svg,
        [data-post-card-shell] .action-bar .post-card__bookmark-icon,
        [data-post-card-shell] .action-bar .post-card__share-icon {
            width: 20px !important;
            height: 20px !important;
            font-size: 20px !important;
        }

        [data-post-card-shell] .action-bar .action-chip__label,
        [data-post-card-shell] .action-bar [data-post-card-view-count] {
            font-size: 13.5px !important;
        }

        [data-post-card-shell] .post-card__menu-wrap .menu-btn,
        [data-post-card-shell] .post-card__menu-wrap .menu-button,
        [data-post-card-shell] .post-card__menu-wrap button.menu-btn,
        [data-post-card-shell] .post-card__menu-wrap button.menu-button {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            min-height: 38px !important;
            padding: 0 !important;
            border-radius: 999px !important;
            background: transparent !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
        }

        [data-post-card-shell] .post-card__menu-wrap .menu-btn .post-card__inline-icon,
        [data-post-card-shell] .post-card__menu-wrap .menu-button .post-card__inline-icon {
            width: 20px !important;
            height: 20px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 0 0 20px !important;
        }

        [data-post-card-shell] .post-card__menu-wrap .menu-btn iconify-icon,
        [data-post-card-shell] .post-card__menu-wrap .menu-button iconify-icon {
            font-size: 20px !important;
        }

        [data-post-card-shell] .post-card__menu-wrap .menu-btn:hover,
        [data-post-card-shell] .post-card__menu-wrap .menu-button:hover,
        [data-post-card-shell] .post-card__menu-wrap .menu-btn:focus-visible,
        [data-post-card-shell] .post-card__menu-wrap .menu-button:focus-visible {
            background: #e5e7eb !important;
            border-radius: 999px !important;
        }

        [data-post-card-shell] .post-card__menu-wrap .menu-btn:active,
        [data-post-card-shell] .post-card__menu-wrap .menu-button:active {
            background: #d1d5db !important;
            border-radius: 999px !important;
        }

        [data-post-card-shell] .post-summary-shell {
            position: relative !important;
            margin-bottom: 0 !important;
            padding-bottom: 2px !important;
            overflow: visible !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -2px;
            height: 36px;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.72) 58%, rgba(255, 255, 255, 0.96) 82%, #ffffff 100%);
            z-index: 1;
        }

        [data-post-card-shell] .post-summary-shell.is-expanded::after {
            content: none !important;
        }

        [data-post-card-shell] .post-summary {
            position: relative;
            z-index: 0;
        }

        [data-post-card-shell] .expand-link {
            position: relative !important;
            z-index: 2 !important;
            margin-top: -4px !important;
            margin-bottom: 10px !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:hover,
        html.dark [data-post-card-shell] .post-card__menu-wrap .menu-button:hover,
        html.dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:focus-visible,
        html.dark [data-post-card-shell] .post-card__menu-wrap .menu-button:focus-visible,
        body.dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:hover,
        body.dark [data-post-card-shell] .post-card__menu-wrap .menu-button:hover,
        body.dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:focus-visible,
        body.dark [data-post-card-shell] .post-card__menu-wrap .menu-button:focus-visible,
        .dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:hover,
        .dark [data-post-card-shell] .post-card__menu-wrap .menu-button:hover,
        .dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:focus-visible,
        .dark [data-post-card-shell] .post-card__menu-wrap .menu-button:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-wrap .menu-btn:hover,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-wrap .menu-button:hover,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-wrap .menu-btn:focus-visible,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-wrap .menu-button:focus-visible {
            background: rgba(255, 255, 255, 0.12) !important;
        }

        html.dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:active,
        html.dark [data-post-card-shell] .post-card__menu-wrap .menu-button:active,
        body.dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:active,
        body.dark [data-post-card-shell] .post-card__menu-wrap .menu-button:active,
        .dark [data-post-card-shell] .post-card__menu-wrap .menu-btn:active,
        .dark [data-post-card-shell] .post-card__menu-wrap .menu-button:active,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-wrap .menu-btn:active,
        [data-theme="dark"] [data-post-card-shell] .post-card__menu-wrap .menu-button:active {
            background: rgba(255, 255, 255, 0.18) !important;
        }



        /* FINAL OVERRIDE: Devamını oku tam içerik + belirgin alt satır fade */
        [data-post-card-shell] .post-summary-shell {
            position: relative !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
            overflow: visible !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            content: "" !important;
            position: absolute !important;
            left: 0 !important;
            right: 0 !important;
            bottom: -1px !important;
            height: 0.75em !important;
            min-height: 13px !important;
            pointer-events: none !important;
            z-index: 2 !important;
            background: linear-gradient(
                180deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.50) 28%,
                rgba(255, 255, 255, 0.88) 62%,
                #ffffff 100%
            ) !important;
        }

        [data-post-card-shell] .post-summary-shell.is-expanded::after,
        [data-post-card-shell] .post-summary.is-expanded::after {
            content: none !important;
            display: none !important;
        }

        [data-post-card-shell] .post-summary {
            position: relative !important;
            z-index: 1 !important;
            margin-bottom: 0 !important;
            white-space: pre-line !important;
        }

        [data-post-card-shell] .expand-link {
            position: relative !important;
            z-index: 3 !important;
            margin-top: 2px !important;
            margin-bottom: 10px !important;
        }

        html.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        body.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        .dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        [data-theme="dark"] [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            background: linear-gradient(
                180deg,
                rgba(17, 24, 39, 0) 0%,
                rgba(17, 24, 39, 0.54) 30%,
                rgba(17, 24, 39, 0.90) 64%,
                #111827 100%
            ) !important;
        }



        /* FINAL OVERRIDE: devamini oku tam metin ve satir kaybi */
        [data-post-card-shell] .post-summary {
            white-space: pre-line !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            height: .58em !important;
            bottom: .12em !important;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.82) 48%, #ffffff 100%) !important;
        }

        [data-post-card-shell] .post-summary-shell.is-expanded::after {
            content: none !important;
            display: none !important;
        }

        html.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        body.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        .dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        [data-theme="dark"] [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            background: linear-gradient(180deg, rgba(17,24,39,0) 0%, rgba(17,24,39,.84) 48%, #111827 100%) !important;
        }



        /* FINAL: devamını oku tam gönderi içeriği + fotoğraf/video alanı */
        [data-post-card-shell] .post-card__full-content {
            display: grid;
            gap: 14px;
            width: 100%;
            margin: 4px 0 12px;
            color: #0f172a;
            font-size: 15px;
            line-height: 1.72;
            word-break: break-word;
        }

        [data-post-card-shell] .post-card__full-content[hidden] { display: none !important; }

        [data-post-card-shell] .post-card__full-heading {
            margin: 4px 0 0;
            color: #0f172a;
            font-size: 18px;
            font-weight: 600;
            line-height: 1.35;
        }

        [data-post-card-shell] .post-card__full-paragraph,
        [data-post-card-shell] .post-card__full-quote {
            margin: 0;
            color: #0f172a;
            font-size: 15px;
            font-weight: 400;
            line-height: 1.72;
            white-space: normal;
        }

        [data-post-card-shell] .post-card__full-quote {
            padding: 12px 14px;
            border-left: 3px solid #dbeafe;
            border-radius: 14px;
            background: #f8fafc;
            color: #334155;
        }

        [data-post-card-shell] .post-card__full-list,
        [data-post-card-shell] .post-card__full-list--ordered,
        [data-post-card-shell] .post-card__full-checklist {
            margin: 0;
            padding-left: 22px;
            color: #0f172a;
            font-size: 15px;
            line-height: 1.68;
        }

        [data-post-card-shell] .post-card__full-checklist {
            display: grid;
            gap: 8px;
            padding-left: 0;
            list-style: none;
        }

        [data-post-card-shell] .post-card__full-checklist li { display: flex; gap: 9px; align-items: flex-start; }

        [data-post-card-shell] .post-card__full-check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            min-width: 18px;
            margin-top: 4px;
            border-radius: 6px;
            background: #eef2ff;
            color: #2563eb;
            font-size: 12px;
            line-height: 1;
        }

        [data-post-card-shell] .post-card__full-media-grid {
            display: grid;
            gap: 10px;
            width: 100%;
        }

        [data-post-card-shell] .post-card__full-media-grid--multi { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        [data-post-card-shell] .post-card__full-figure {
            display: grid;
            gap: 7px;
            margin: 0;
            overflow: hidden;
            border-radius: 18px;
            background: #f8fafc;
        }

        [data-post-card-shell] .post-card__full-figure img {
            display: block;
            width: 100%;
            height: auto;
            max-height: 620px;
            object-fit: contain;
            border-radius: 18px;
            background: #f1f5f9;
        }

        [data-post-card-shell] .post-card__full-figure figcaption {
            padding: 0 10px 10px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.45;
        }

        [data-post-card-shell] .post-card__full-embed {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            border-radius: 18px;
            background: #0f172a;
        }

        [data-post-card-shell] .post-card__full-embed iframe { display: block; width: 100%; height: 100%; border: 0; }

        [data-post-card-shell] .post-card__full-table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
        }

        [data-post-card-shell] .post-card__full-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        [data-post-card-shell] .post-card__full-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; color: #0f172a; }
        [data-post-card-shell] .post-card__full-table tr:last-child td { border-bottom: 0; }

        [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            height: .72em !important;
            bottom: .08em !important;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.86) 42%, #ffffff 100%) !important;
        }

        html.dark [data-post-card-shell] .post-card__full-content,
        html.dark [data-post-card-shell] .post-card__full-heading,
        html.dark [data-post-card-shell] .post-card__full-paragraph,
        html.dark [data-post-card-shell] .post-card__full-list,
        html.dark [data-post-card-shell] .post-card__full-list--ordered,
        html.dark [data-post-card-shell] .post-card__full-checklist,
        html.dark [data-post-card-shell] .post-card__full-table td,
        body.dark [data-post-card-shell] .post-card__full-content,
        body.dark [data-post-card-shell] .post-card__full-heading,
        body.dark [data-post-card-shell] .post-card__full-paragraph,
        body.dark [data-post-card-shell] .post-card__full-list,
        body.dark [data-post-card-shell] .post-card__full-list--ordered,
        body.dark [data-post-card-shell] .post-card__full-checklist,
        body.dark [data-post-card-shell] .post-card__full-table td,
        .dark [data-post-card-shell] .post-card__full-content,
        .dark [data-post-card-shell] .post-card__full-heading,
        .dark [data-post-card-shell] .post-card__full-paragraph,
        .dark [data-post-card-shell] .post-card__full-list,
        .dark [data-post-card-shell] .post-card__full-list--ordered,
        .dark [data-post-card-shell] .post-card__full-checklist,
        .dark [data-post-card-shell] .post-card__full-table td,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-content,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-heading,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-paragraph,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-list,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-list--ordered,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-checklist,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-table td { color: #f8fafc !important; }

        html.dark [data-post-card-shell] .post-card__full-quote,
        body.dark [data-post-card-shell] .post-card__full-quote,
        .dark [data-post-card-shell] .post-card__full-quote,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-quote {
            background: rgba(255,255,255,.06) !important;
            border-left-color: rgba(147,197,253,.55) !important;
            color: #e5e7eb !important;
        }

        html.dark [data-post-card-shell] .post-card__full-figure,
        html.dark [data-post-card-shell] .post-card__full-figure img,
        body.dark [data-post-card-shell] .post-card__full-figure,
        body.dark [data-post-card-shell] .post-card__full-figure img,
        .dark [data-post-card-shell] .post-card__full-figure,
        .dark [data-post-card-shell] .post-card__full-figure img,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-figure,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-figure img { background: #111827 !important; }

        html.dark [data-post-card-shell] .post-card__full-table-wrap,
        body.dark [data-post-card-shell] .post-card__full-table-wrap,
        .dark [data-post-card-shell] .post-card__full-table-wrap,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-table-wrap { border-color: rgba(255,255,255,.12) !important; }

        html.dark [data-post-card-shell] .post-card__full-table td,
        body.dark [data-post-card-shell] .post-card__full-table td,
        .dark [data-post-card-shell] .post-card__full-table td,
        [data-theme="dark"] [data-post-card-shell] .post-card__full-table td { border-bottom-color: rgba(255,255,255,.10) !important; }

        html.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        body.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        .dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        [data-theme="dark"] [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            background: linear-gradient(180deg, rgba(15,23,42,0) 0%, rgba(15,23,42,.86) 42%, #0f172a 100%) !important;
        }

        @media (max-width: 640px) {
            [data-post-card-shell] .post-card__full-media-grid--multi { grid-template-columns: 1fr; }
            [data-post-card-shell] .post-card__full-figure img { max-height: none; }
        }



        /* FINAL OVERRIDE: kapalı metin her yerde 2 satır; buton hemen altında */
        [data-post-card-shell] .post-summary-shell.is-collapsed {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
            overflow: hidden !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed .post-summary,
        [data-post-card-shell] .post-summary.is-collapsed {
            display: -webkit-box !important;
            -webkit-box-orient: vertical !important;
            -webkit-line-clamp: 2 !important;
            overflow: hidden !important;
            white-space: normal !important;
            line-height: 1.45 !important;
            max-height: calc(1.45em * 2) !important;
            margin-bottom: 0 !important;
        }

        [data-post-card-shell] .post-summary-shell.is-expanded .post-summary,
        [data-post-card-shell] .post-summary.is-expanded {
            display: block !important;
            -webkit-line-clamp: unset !important;
            max-height: none !important;
            overflow: visible !important;
            white-space: pre-line !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            height: .72em !important;
            min-height: 10px !important;
            bottom: 0 !important;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.70) 45%, #ffffff 100%) !important;
        }

        [data-post-card-shell] .expand-link {
            margin-top: 2px !important;
            margin-bottom: 10px !important;
        }

        @media (max-width: 640px) {
            [data-post-card-shell] .post-summary-shell.is-collapsed .post-summary,
            [data-post-card-shell] .post-summary.is-collapsed {
                -webkit-line-clamp: 2 !important;
                max-height: calc(1.45em * 2) !important;
            }
        }

        html.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        body.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        .dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        [data-theme="dark"] [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            background: linear-gradient(180deg, rgba(17,24,39,0) 0%, rgba(17,24,39,.72) 45%, #111827 100%) !important;
        }


        /* OGRafi final: tüm post kartlarında kapalı açıklama 2 satır + Devamını oku butonu görünür */
        [data-post-card-shell] .post-summary-shell.is-collapsed {
            overflow: hidden !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed .post-summary,
        [data-post-card-shell] .post-summary.is-collapsed {
            display: -webkit-box !important;
            -webkit-box-orient: vertical !important;
            -webkit-line-clamp: 2 !important;
            overflow: hidden !important;
            white-space: normal !important;
            line-height: 1.45 !important;
            max-height: calc(1.45em * 2) !important;
            margin-bottom: 0 !important;
        }

        [data-post-card-shell] .post-summary-shell.is-expanded .post-summary,
        [data-post-card-shell] .post-summary.is-expanded {
            display: block !important;
            -webkit-line-clamp: unset !important;
            max-height: none !important;
            overflow: visible !important;
            white-space: pre-line !important;
        }

        [data-post-card-shell] .expand-link {
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            margin-top: 4px !important;
            margin-bottom: 10px !important;
        }



        /* OGRafi final all posts: Devamını oku butonu bütün kartlarda görünür */
        [data-post-card-shell] .expand-link[data-post-card-expand] {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            visibility: visible !important;
            opacity: 1 !important;
        }


        /* OGRAPHI KESIN COZUM: Kapalıyken HER post özeti yalnızca 2 satır görünür. */
        [data-post-card-shell] [data-post-card-summary-shell],
        [data-post-card-shell] .post-summary-shell {
            position: relative !important;
            display: block !important;
            margin: 0 0 4px 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        [data-post-card-shell] [data-post-card-summary],
        [data-post-card-shell] .post-summary {
            display: -webkit-box !important;
            -webkit-box-orient: vertical !important;
            -webkit-line-clamp: 2 !important;
            line-clamp: 2 !important;
            max-height: calc(1.55em * 2) !important;
            min-height: 0 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: normal !important;
            line-height: 1.55 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        [data-post-card-shell] [data-post-card-summary-shell].is-collapsed,
        [data-post-card-shell] .post-summary-shell.is-collapsed {
            max-height: calc(1.55em * 2) !important;
            overflow: hidden !important;
        }

        [data-post-card-shell] [data-post-card-summary].is-collapsed,
        [data-post-card-shell] .post-summary.is-collapsed {
            display: -webkit-box !important;
            -webkit-box-orient: vertical !important;
            -webkit-line-clamp: 2 !important;
            line-clamp: 2 !important;
            max-height: calc(1.55em * 2) !important;
            overflow: hidden !important;
            white-space: normal !important;
        }

        [data-post-card-shell] [data-post-card-summary-shell].is-expanded,
        [data-post-card-shell] .post-summary-shell.is-expanded,
        [data-post-card-shell] [data-post-card-summary].is-expanded,
        [data-post-card-shell] .post-summary.is-expanded {
            display: block !important;
            -webkit-line-clamp: unset !important;
            line-clamp: unset !important;
            max-height: none !important;
            overflow: visible !important;
            white-space: pre-line !important;
        }

        [data-post-card-shell] [data-post-card-full-content][hidden],
        [data-post-card-shell] .post-card__full-content[hidden],
        [data-post-card-shell]:not(.is-summary-expanded) [data-post-card-full-content],
        [data-post-card-shell]:not(.is-summary-expanded) .post-card__full-content {
            display: none !important;
        }

        [data-post-card-shell] .expand-link[data-post-card-expand],
        [data-post-card-shell] button[data-post-card-expand] {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            align-items: center !important;
            justify-content: center !important;
            width: auto !important;
            max-width: max-content !important;
            margin-top: 6px !important;
        }


        /* OGRAPHI update: yumuşatılmış beyaz kayip efekti */
        [data-post-card-shell] [data-post-card-summary-shell].is-collapsed::after,
        [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            content: '' !important;
            position: absolute !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            height: 44px !important;
            pointer-events: none !important;
            background: linear-gradient(
                180deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.18) 34%,
                rgba(255, 255, 255, 0.50) 62%,
                rgba(255, 255, 255, 0.82) 82%,
                #ffffff 100%
            ) !important;
        }

        [data-post-card-shell] [data-post-card-summary-shell].is-expanded::after,
        [data-post-card-shell] .post-summary-shell.is-expanded::after,
        [data-post-card-shell].is-summary-expanded [data-post-card-summary-shell]::after,
        [data-post-card-shell].is-summary-expanded .post-summary-shell::after {
            content: none !important;
            display: none !important;
        }

        html.dark [data-post-card-shell] [data-post-card-summary-shell].is-collapsed::after,
        body.dark [data-post-card-shell] [data-post-card-summary-shell].is-collapsed::after,
        .dark [data-post-card-shell] [data-post-card-summary-shell].is-collapsed::after,
        [data-theme="dark"] [data-post-card-shell] [data-post-card-summary-shell].is-collapsed::after,
        html.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        body.dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        .dark [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        [data-theme="dark"] [data-post-card-shell] .post-summary-shell.is-collapsed::after {
            background: linear-gradient(
                180deg,
                rgba(15, 23, 42, 0) 0%,
                rgba(15, 23, 42, 0.20) 34%,
                rgba(15, 23, 42, 0.50) 62%,
                rgba(15, 23, 42, 0.84) 82%,
                #0f172a 100%
            ) !important;
        }


        /* OGRAPHI update: başlık fontu biraz daha kalın */
        [data-post-card-shell] .post-title,
        [data-post-card-shell] .post-title__link {
            font-weight: 500 !important;
        }


        /* Final width balance: beyaz kartı eşit şekilde sağdan-soldan daralt, iç padding korunur */
        [data-post-card-shell] {
            width: 100% !important;
            max-width: 620px !important;
            margin-inline: auto !important;
        }

        @media (max-width: 820px) {
            [data-post-card-shell] {
                max-width: 600px !important;
                margin-inline: auto !important;
            }
        }

        @media (max-width: 560px) {
            [data-post-card-shell] {
                width: calc(100% - 16px) !important;
                max-width: calc(100% - 16px) !important;
                margin-inline: auto !important;
            }
        }



        /* Final content balance: sadece post içeriği ve ikonlar küçültüldü */
        [data-post-card-shell] .post-summary {
            font-size: 15px !important;
            line-height: 1.62 !important;
        }

        [data-post-card-shell] .post-card__full-heading {
            font-size: 17px !important;
            line-height: 1.34 !important;
        }

        [data-post-card-shell] .post-card__full-paragraph,
        [data-post-card-shell] .post-card__full-quote,
        [data-post-card-shell] .post-card__full-list,
        [data-post-card-shell] .post-card__full-list--ordered,
        [data-post-card-shell] .post-card__full-checklist {
            font-size: 14px !important;
            line-height: 1.64 !important;
        }

        [data-post-card-shell] .post-card__reaction-custom-icon,
        [data-post-card-shell] .post-card__inline-icon {
            width: 18px !important;
            height: 18px !important;
        }

        [data-post-card-shell] .post-card__reaction-custom-icon svg,
        [data-post-card-shell] .post-card__inline-icon svg,
        [data-post-card-shell] .post-card__inline-icon iconify-icon {
            width: 18px !important;
            height: 18px !important;
            font-size: 18px !important;
        }

        [data-post-card-shell] .post-card__bookmark-icon,
        [data-post-card-shell] .post-card__share-icon,
        [data-post-card-shell] .post-card__reaction-add-icon,
        [data-post-card-shell] .post-card__expand-icon {
            width: 15px !important;
            height: 15px !important;
        }

        [data-post-card-shell] .post-card__menu-item iconify-icon,
        [data-post-card-shell] .post-card__source-icon iconify-icon,
        [data-post-card-shell] .post-card__vote-cluster iconify-icon {
            font-size: 15px !important;
            width: 15px !important;
            height: 15px !important;
        }



        /* Final fix: üst akışla birebir sağ-sol hizalama. Parent genişliğine takılmasın diye viewport'a göre hesaplandı. */
        @media (min-width: 961px) {
            [data-post-card-shell] {
                width: min(100%, 680px) !important;
                max-width: 680px !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
        }

        @media (max-width: 960px) {
            [data-post-card-shell] {
                width: calc(100vw - 16px) !important;
                max-width: calc(100vw - 16px) !important;
                margin-left: calc(50% - 50vw + 8px) !important;
                margin-right: calc(50% - 50vw + 8px) !important;
            }
        }



        /* Final batch control: her tiklamada 25 post daha goster */
        .ografi-post-batch-refresh-wrap {
            width: min(calc(100% - 24px), 640px);
            max-width: 640px;
            margin: 14px auto 18px;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .ografi-post-batch-refresh,
        .ografi-post-batch-control {
            appearance: none;
            -webkit-appearance: none;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(15, 23, 42, 0.10);
            border-radius: 999px;
            background: #ffffff;
            color: #0f172a;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: none;
        }

        .ografi-post-batch-refresh:hover,
        .ografi-post-batch-refresh:focus-visible,
        .ografi-post-batch-control:hover,
        .ografi-post-batch-control:focus-visible {
            background: #ffffff;
            border-color: rgba(15, 23, 42, 0.10);
            outline: none;
        }

        .ografi-post-batch-refresh:active,
        .ografi-post-batch-control:active {
            transform: none;
        }

        .ografi-post-batch-refresh svg,
        .ografi-post-batch-control svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .ografi-post-batch-refresh.is-loading svg,
        .ografi-post-batch-control.is-loading svg {
            animation: none;
        }

        .ografi-post-batch-control:disabled,
        .ografi-post-batch-refresh:disabled {
            opacity: 0.42;
            cursor: not-allowed;
            transform: none;
        }

        html.dark .ografi-post-batch-refresh,
        body.dark .ografi-post-batch-refresh,
        .dark .ografi-post-batch-refresh,
        [data-theme="dark"] .ografi-post-batch-refresh,
        html.dark .ografi-post-batch-control,
        body.dark .ografi-post-batch-control,
        .dark .ografi-post-batch-control,
        [data-theme="dark"] .ografi-post-batch-control {
            background: #0b1120;
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: none;
        }

        html.dark .ografi-post-batch-refresh:hover,
        body.dark .ografi-post-batch-refresh:hover,
        .dark .ografi-post-batch-refresh:hover,
        [data-theme="dark"] .ografi-post-batch-refresh:hover,
        html.dark .ografi-post-batch-refresh:focus-visible,
        body.dark .ografi-post-batch-refresh:focus-visible,
        .dark .ografi-post-batch-refresh:focus-visible,
        [data-theme="dark"] .ografi-post-batch-refresh:focus-visible,
        html.dark .ografi-post-batch-control:hover,
        body.dark .ografi-post-batch-control:hover,
        .dark .ografi-post-batch-control:hover,
        [data-theme="dark"] .ografi-post-batch-control:hover,
        html.dark .ografi-post-batch-control:focus-visible,
        body.dark .ografi-post-batch-control:focus-visible,
        .dark .ografi-post-batch-control:focus-visible,
        [data-theme="dark"] .ografi-post-batch-control:focus-visible {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.18);
        }

        @media (max-width: 560px) {
            .ografi-post-batch-refresh-wrap {
                width: calc(100% - 16px);
                max-width: calc(100% - 16px);
                margin: 12px auto 16px;
                gap: 8px;
            }

            .ografi-post-batch-refresh,
            .ografi-post-batch-control {
                width: 40px;
                height: 40px;
            }
        }



        /* Final preload override: dalgalı yükleme efekti yazı + resim + ikon dahil her yerde görünsün */
        [data-post-card-shell].is-preloading .avatar-wrap,
        [data-post-card-shell].is-preloading .author-avatar,
        [data-post-card-shell].is-preloading .author-avatar--fallback,
        [data-post-card-shell].is-preloading .author-avatar-fallback,
        [data-post-card-shell].is-preloading .category-badge,
        [data-post-card-shell].is-preloading .category-badge__fallback,
        [data-post-card-shell].is-preloading .comment-avatars,
        [data-post-card-shell].is-preloading .comment-avatar,
        [data-post-card-shell].is-preloading .menu-btn,
        [data-post-card-shell].is-preloading .post-card__action-button,
        [data-post-card-shell].is-preloading .post-card__action-link,
        [data-post-card-shell].is-preloading .post-card__inline-icon,
        [data-post-card-shell].is-preloading .post-card__bookmark-icon,
        [data-post-card-shell].is-preloading .post-card__share-icon,
        [data-post-card-shell].is-preloading .post-card__reaction-custom-icon,
        [data-post-card-shell].is-preloading .post-card__reaction-add-icon,
        [data-post-card-shell].is-preloading .post-card__source-icon,
        [data-post-card-shell].is-preloading .post-card__media-frame {
            position: relative !important;
            overflow: hidden !important;
            isolation: isolate !important;
        }

        [data-post-card-shell].is-preloading .avatar-wrap::after,
        [data-post-card-shell].is-preloading .category-badge::after,
        [data-post-card-shell].is-preloading .comment-avatars::after,
        [data-post-card-shell].is-preloading .menu-btn::after,
        [data-post-card-shell].is-preloading .post-card__action-button::after,
        [data-post-card-shell].is-preloading .post-card__action-link::after,
        [data-post-card-shell].is-preloading .post-card__inline-icon::after,
        [data-post-card-shell].is-preloading .post-card__bookmark-icon::after,
        [data-post-card-shell].is-preloading .post-card__share-icon::after,
        [data-post-card-shell].is-preloading .post-card__reaction-custom-icon::after,
        [data-post-card-shell].is-preloading .post-card__reaction-add-icon::after,
        [data-post-card-shell].is-preloading .post-card__source-icon::after {
            content: "";
            position: absolute;
            inset: -1px;
            z-index: 3;
            pointer-events: none;
            border-radius: inherit;
            background:
                linear-gradient(
                    100deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.72) 45%,
                    rgba(255, 255, 255, 0) 80%
                ),
                linear-gradient(135deg, #eef2f7 0%, #f8fafc 48%, #e5e7eb 100%);
            background-size: 220% 100%, 100% 100%;
            animation:
                postCardMediaPreload 1.05s linear infinite,
                postCardPreloadOverlayOut 0.26s ease 1.05s forwards;
        }

        [data-post-card-shell].is-preloading .author-avatar,
        [data-post-card-shell].is-preloading .author-avatar--fallback,
        [data-post-card-shell].is-preloading .author-avatar-fallback,
        [data-post-card-shell].is-preloading .category-badge__image,
        [data-post-card-shell].is-preloading .category-badge__fallback,
        [data-post-card-shell].is-preloading .comment-avatar,
        [data-post-card-shell].is-preloading .post-card__inline-icon,
        [data-post-card-shell].is-preloading .post-card__inline-icon svg,
        [data-post-card-shell].is-preloading .post-card__inline-icon iconify-icon,
        [data-post-card-shell].is-preloading .post-card__bookmark-icon,
        [data-post-card-shell].is-preloading .post-card__share-icon,
        [data-post-card-shell].is-preloading .post-card__reaction-custom-icon,
        [data-post-card-shell].is-preloading .post-card__reaction-add-icon,
        [data-post-card-shell].is-preloading .post-card__source-icon,
        [data-post-card-shell].is-preloading .post-card__source-icon svg,
        [data-post-card-shell].is-preloading .post-card__source-icon iconify-icon,
        [data-post-card-shell].is-preloading .menu-btn > *,
        [data-post-card-shell].is-preloading .post-card__action-button > *,
        [data-post-card-shell].is-preloading .post-card__action-link > * {
            opacity: 0 !important;
        }

        [data-post-card-shell].is-preloading .avatar-wrap,
        [data-post-card-shell].is-preloading .comment-avatars,
        [data-post-card-shell].is-preloading .menu-btn,
        [data-post-card-shell].is-preloading .post-card__action-button,
        [data-post-card-shell].is-preloading .post-card__action-link {
            background: #eef2f7 !important;
        }

        @media (prefers-color-scheme: dark) {
            [data-post-card-shell].is-preloading .avatar-wrap::after,
            [data-post-card-shell].is-preloading .category-badge::after,
            [data-post-card-shell].is-preloading .comment-avatars::after,
            [data-post-card-shell].is-preloading .menu-btn::after,
            [data-post-card-shell].is-preloading .post-card__action-button::after,
            [data-post-card-shell].is-preloading .post-card__action-link::after,
            [data-post-card-shell].is-preloading .post-card__inline-icon::after,
            [data-post-card-shell].is-preloading .post-card__bookmark-icon::after,
            [data-post-card-shell].is-preloading .post-card__share-icon::after,
            [data-post-card-shell].is-preloading .post-card__reaction-custom-icon::after,
            [data-post-card-shell].is-preloading .post-card__reaction-add-icon::after,
            [data-post-card-shell].is-preloading .post-card__source-icon::after {
                background:
                    linear-gradient(
                        100deg,
                        rgba(255, 255, 255, 0) 0%,
                        rgba(255, 255, 255, 0.08) 45%,
                        rgba(255, 255, 255, 0) 80%
                    ),
                    linear-gradient(135deg, #111827 0%, #1f2937 48%, #0f172a 100%);
                background-size: 220% 100%, 100% 100%;
            }

            [data-post-card-shell].is-preloading .avatar-wrap,
            [data-post-card-shell].is-preloading .comment-avatars,
            [data-post-card-shell].is-preloading .menu-btn,
            [data-post-card-shell].is-preloading .post-card__action-button,
            [data-post-card-shell].is-preloading .post-card__action-link {
                background: #111827 !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            [data-post-card-shell].is-preloading .avatar-wrap::after,
            [data-post-card-shell].is-preloading .category-badge::after,
            [data-post-card-shell].is-preloading .comment-avatars::after,
            [data-post-card-shell].is-preloading .menu-btn::after,
            [data-post-card-shell].is-preloading .post-card__action-button::after,
            [data-post-card-shell].is-preloading .post-card__action-link::after,
            [data-post-card-shell].is-preloading .post-card__inline-icon::after,
            [data-post-card-shell].is-preloading .post-card__bookmark-icon::after,
            [data-post-card-shell].is-preloading .post-card__share-icon::after,
            [data-post-card-shell].is-preloading .post-card__reaction-custom-icon::after,
            [data-post-card-shell].is-preloading .post-card__reaction-add-icon::after,
            [data-post-card-shell].is-preloading .post-card__source-icon::after {
                display: none !important;
            }
        }



        /* Final fix: yükleme dalgası kalıcı değil; resimde de görünür, yükleme bitince kaybolur */
        [data-post-card-shell].is-preloading .post-card__media-frame::before {
            content: "" !important;
            display: block !important;
            visibility: visible;
            opacity: 1;
            z-index: 6 !important;
            background:
                linear-gradient(
                    100deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.76) 45%,
                    rgba(255, 255, 255, 0) 80%
                ),
                linear-gradient(135deg, rgba(238, 242, 247, 0.92) 0%, rgba(248, 250, 252, 0.92) 48%, rgba(229, 231, 235, 0.92) 100%) !important;
            background-size: 220% 100%, 100% 100% !important;
            animation:
                postCardMediaPreload 1.05s linear infinite,
                postCardPreloadOverlayOut 0.28s ease 1.1s forwards !important;
            animation-play-state: running !important;
        }

        [data-post-card-shell] .post-card__media-frame.is-loaded::before,
        [data-post-card-shell] .post-card__media-frame.is-error::before,
        [data-post-card-shell]:not(.is-preloading) .post-card__media-frame::before,
        [data-post-card-shell].is-preloading-done .post-card__media-frame::before,
        [data-post-card-shell].is-preloading-done .avatar-wrap::after,
        [data-post-card-shell].is-preloading-done .category-badge::after,
        [data-post-card-shell].is-preloading-done .comment-avatars::after,
        [data-post-card-shell].is-preloading-done .menu-btn::after,
        [data-post-card-shell].is-preloading-done .post-card__action-button::after,
        [data-post-card-shell].is-preloading-done .post-card__action-link::after,
        [data-post-card-shell].is-preloading-done .post-card__inline-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__bookmark-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__share-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__reaction-custom-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__reaction-add-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__source-icon::after {
            content: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            display: none !important;
            animation: none !important;
        }

        [data-post-card-shell].is-preloading-done .author-avatar,
        [data-post-card-shell].is-preloading-done .author-avatar--fallback,
        [data-post-card-shell].is-preloading-done .author-avatar-fallback,
        [data-post-card-shell].is-preloading-done .category-badge__image,
        [data-post-card-shell].is-preloading-done .category-badge__fallback,
        [data-post-card-shell].is-preloading-done .comment-avatar,
        [data-post-card-shell].is-preloading-done .post-card__inline-icon,
        [data-post-card-shell].is-preloading-done .post-card__inline-icon svg,
        [data-post-card-shell].is-preloading-done .post-card__inline-icon iconify-icon,
        [data-post-card-shell].is-preloading-done .post-card__bookmark-icon,
        [data-post-card-shell].is-preloading-done .post-card__share-icon,
        [data-post-card-shell].is-preloading-done .post-card__reaction-custom-icon,
        [data-post-card-shell].is-preloading-done .post-card__reaction-add-icon,
        [data-post-card-shell].is-preloading-done .post-card__source-icon,
        [data-post-card-shell].is-preloading-done .post-card__source-icon svg,
        [data-post-card-shell].is-preloading-done .post-card__source-icon iconify-icon,
        [data-post-card-shell].is-preloading-done .menu-btn > *,
        [data-post-card-shell].is-preloading-done .post-card__action-button > *,
        [data-post-card-shell].is-preloading-done .post-card__action-link > *,
        [data-post-card-shell]:not(.is-preloading) .author-avatar,
        [data-post-card-shell]:not(.is-preloading) .author-avatar--fallback,
        [data-post-card-shell]:not(.is-preloading) .author-avatar-fallback,
        [data-post-card-shell]:not(.is-preloading) .category-badge__image,
        [data-post-card-shell]:not(.is-preloading) .category-badge__fallback,
        [data-post-card-shell]:not(.is-preloading) .comment-avatar,
        [data-post-card-shell]:not(.is-preloading) .post-card__inline-icon,
        [data-post-card-shell]:not(.is-preloading) .post-card__inline-icon svg,
        [data-post-card-shell]:not(.is-preloading) .post-card__inline-icon iconify-icon,
        [data-post-card-shell]:not(.is-preloading) .post-card__bookmark-icon,
        [data-post-card-shell]:not(.is-preloading) .post-card__share-icon,
        [data-post-card-shell]:not(.is-preloading) .post-card__reaction-custom-icon,
        [data-post-card-shell]:not(.is-preloading) .post-card__reaction-add-icon,
        [data-post-card-shell]:not(.is-preloading) .post-card__source-icon,
        [data-post-card-shell]:not(.is-preloading) .post-card__source-icon svg,
        [data-post-card-shell]:not(.is-preloading) .post-card__source-icon iconify-icon,
        [data-post-card-shell]:not(.is-preloading) .menu-btn > *,
        [data-post-card-shell]:not(.is-preloading) .post-card__action-button > *,
        [data-post-card-shell]:not(.is-preloading) .post-card__action-link > * {
            opacity: 1 !important;
        }

        html.dark [data-post-card-shell].is-preloading .post-card__media-frame::before,
        body.dark [data-post-card-shell].is-preloading .post-card__media-frame::before,
        .dark [data-post-card-shell].is-preloading .post-card__media-frame::before,
        [data-theme="dark"] [data-post-card-shell].is-preloading .post-card__media-frame::before {
            background:
                linear-gradient(
                    100deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.10) 45%,
                    rgba(255, 255, 255, 0) 80%
                ),
                linear-gradient(135deg, rgba(17, 24, 39, 0.94) 0%, rgba(31, 41, 55, 0.94) 48%, rgba(15, 23, 42, 0.94) 100%) !important;
            background-size: 220% 100%, 100% 100% !important;
        }



        /* FINAL FIX: icon/resim yükleme efekti kalıcı kalmasın */
        [data-post-card-shell].is-preloading .author-avatar,
        [data-post-card-shell].is-preloading .author-avatar--fallback,
        [data-post-card-shell].is-preloading .author-avatar-fallback,
        [data-post-card-shell].is-preloading .category-badge__image,
        [data-post-card-shell].is-preloading .category-badge__fallback,
        [data-post-card-shell].is-preloading .comment-avatar,
        [data-post-card-shell].is-preloading .post-card__inline-icon,
        [data-post-card-shell].is-preloading .post-card__inline-icon svg,
        [data-post-card-shell].is-preloading .post-card__inline-icon iconify-icon,
        [data-post-card-shell].is-preloading .post-card__bookmark-icon,
        [data-post-card-shell].is-preloading .post-card__share-icon,
        [data-post-card-shell].is-preloading .post-card__reaction-custom-icon,
        [data-post-card-shell].is-preloading .post-card__reaction-add-icon,
        [data-post-card-shell].is-preloading .post-card__source-icon,
        [data-post-card-shell].is-preloading .post-card__source-icon svg,
        [data-post-card-shell].is-preloading .post-card__source-icon iconify-icon,
        [data-post-card-shell].is-preloading .menu-btn > *,
        [data-post-card-shell].is-preloading .post-card__action-button > *,
        [data-post-card-shell].is-preloading .post-card__action-link > * {
            opacity: 1 !important;
            visibility: visible !important;
        }

        [data-post-card-shell].is-preloading .avatar-wrap,
        [data-post-card-shell].is-preloading .comment-avatars,
        [data-post-card-shell].is-preloading .menu-btn,
        [data-post-card-shell].is-preloading .post-card__action-button,
        [data-post-card-shell].is-preloading .post-card__action-link {
            background-color: transparent !important;
        }

        [data-post-card-shell].is-preloading .avatar-wrap::after,
        [data-post-card-shell].is-preloading .category-badge::after,
        [data-post-card-shell].is-preloading .comment-avatars::after,
        [data-post-card-shell].is-preloading .menu-btn::after,
        [data-post-card-shell].is-preloading .post-card__action-button::after,
        [data-post-card-shell].is-preloading .post-card__action-link::after,
        [data-post-card-shell].is-preloading .post-card__inline-icon::after,
        [data-post-card-shell].is-preloading .post-card__bookmark-icon::after,
        [data-post-card-shell].is-preloading .post-card__share-icon::after,
        [data-post-card-shell].is-preloading .post-card__reaction-custom-icon::after,
        [data-post-card-shell].is-preloading .post-card__reaction-add-icon::after,
        [data-post-card-shell].is-preloading .post-card__source-icon::after,
        [data-post-card-shell].is-preloading .post-card__media-frame::before {
            animation:
                postCardMediaPreload 1.05s linear infinite,
                postCardPreloadOverlayOut 0.22s ease 1.05s forwards !important;
            opacity: 1;
            visibility: visible;
        }

        [data-post-card-shell]:not(.is-preloading) .avatar-wrap::after,
        [data-post-card-shell]:not(.is-preloading) .category-badge::after,
        [data-post-card-shell]:not(.is-preloading) .comment-avatars::after,
        [data-post-card-shell]:not(.is-preloading) .menu-btn::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__action-button::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__action-link::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__inline-icon::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__bookmark-icon::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__share-icon::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__reaction-custom-icon::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__reaction-add-icon::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__source-icon::after,
        [data-post-card-shell]:not(.is-preloading) .post-card__media-frame::before,
        [data-post-card-shell].is-preloading-done .avatar-wrap::after,
        [data-post-card-shell].is-preloading-done .category-badge::after,
        [data-post-card-shell].is-preloading-done .comment-avatars::after,
        [data-post-card-shell].is-preloading-done .menu-btn::after,
        [data-post-card-shell].is-preloading-done .post-card__action-button::after,
        [data-post-card-shell].is-preloading-done .post-card__action-link::after,
        [data-post-card-shell].is-preloading-done .post-card__inline-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__bookmark-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__share-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__reaction-custom-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__reaction-add-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__source-icon::after,
        [data-post-card-shell].is-preloading-done .post-card__media-frame::before {
            content: none !important;
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            animation: none !important;
        }


        /* Yazar ve kategori yazısı hover popup kartları */
        [data-post-card-shell] .ps-hover-zone {
            position: relative;
            display: inline-flex;
            align-items: center;
            min-width: 0;
            outline: none !important;
        }

        [data-post-card-shell] .ps-hover-zone--inline {
            width: fit-content;
            max-width: 100%;
        }

        [data-post-card-shell] .ps-hover-zone--author-name,
        [data-post-card-shell] .ps-hover-zone--category-name {
            cursor: pointer !important;
        }

        [data-post-card-shell] .ps-hover-card {
            position: absolute;
            left: 0;
            top: calc(100% + 8px);
            z-index: 9999;
            display: block;
            width: min(258px, calc(100vw - 28px));
            padding: 0 0 10px;
            border: 1px solid rgba(226, 232, 240, .95);
            border-radius: 14px;
            background: #ffffff;
            color: #0f172a;
            overflow: hidden;
            isolation: isolate;
            opacity: 0;
            visibility: hidden;
            transform: translate3d(0, 6px, 0);
            pointer-events: none;
            transition: opacity .12s ease, visibility .12s ease, transform .12s ease;
            box-shadow: none !important;
            text-align: left;
            white-space: normal;
            font-family: var(--font-family-body), Roboto, Arial, sans-serif;
        }

        [data-post-card-shell] .ps-hover-zone--author-name:hover > .ps-hover-card,
        [data-post-card-shell] .ps-hover-zone--author-name:focus-within > .ps-hover-card,
        [data-post-card-shell] .ps-hover-zone--category-name:hover > .ps-hover-card,
        [data-post-card-shell] .ps-hover-zone--category-name:focus-within > .ps-hover-card {
            opacity: 1;
            visibility: visible;
            transform: translate3d(0, 0, 0);
            pointer-events: auto;
        }

        [data-post-card-shell] .ps-hover-zone--author-name:hover > .ps-hover-card > *,
        [data-post-card-shell] .ps-hover-zone--author-name:focus-within > .ps-hover-card > *,
        [data-post-card-shell] .ps-hover-zone--category-name:hover > .ps-hover-card > *,
        [data-post-card-shell] .ps-hover-zone--category-name:focus-within > .ps-hover-card > * {
            opacity: 0;
            animation: postCardHoverCardContentReveal .001s linear 1.5s forwards;
        }

        [data-post-card-shell] .ps-hover-card::before {
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

        [data-post-card-shell] .ps-hover-card::after {
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

        [data-post-card-shell] .ps-hover-zone--author-name:hover > .ps-hover-card::before,
        [data-post-card-shell] .ps-hover-zone--author-name:focus-within > .ps-hover-card::before,
        [data-post-card-shell] .ps-hover-zone--category-name:hover > .ps-hover-card::before,
        [data-post-card-shell] .ps-hover-zone--category-name:focus-within > .ps-hover-card::before {
            opacity: 1;
            visibility: visible;
            animation: postCardHoverCardSkeletonOff .001s linear 1.5s forwards;
        }

        [data-post-card-shell] .ps-hover-zone--author-name:hover > .ps-hover-card::after,
        [data-post-card-shell] .ps-hover-zone--author-name:focus-within > .ps-hover-card::after,
        [data-post-card-shell] .ps-hover-zone--category-name:hover > .ps-hover-card::after,
        [data-post-card-shell] .ps-hover-zone--category-name:focus-within > .ps-hover-card::after {
            opacity: 1;
            visibility: visible;
            animation: postCardHoverCardShimmer .9s ease-in-out 0s infinite, postCardHoverCardSkeletonOff .001s linear 1.5s forwards;
        }

        [data-post-card-shell] .ps-hover-card-cover {
            display: block;
            width: 100%;
            height: 58px;
            background: linear-gradient(135deg, #eef2ff 0%, #eff6ff 48%, #f8fafc 100%);
            overflow: hidden;
        }

        [data-post-card-shell] .ps-hover-card-cover--category {
            background: linear-gradient(135deg, #ecfdf5 0%, #eef2ff 100%);
        }

        [data-post-card-shell] .ps-hover-card-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        [data-post-card-shell] .ps-hover-card-main {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            min-width: 0;
            margin-top: -18px;
            padding: 0 11px;
        }

        [data-post-card-shell] .ps-hover-card-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            border: 2px solid #ffffff;
            border-radius: 999px;
            overflow: hidden;
            background: #f1f5f9;
            color: #64748b;
            font-size: 12px;
            font-weight: 400;
            line-height: 1;
        }

        [data-post-card-shell] .ps-hover-card-avatar--category {
            background: #10b981;
            color: #ffffff;
        }

        [data-post-card-shell] .ps-hover-card-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        [data-post-card-shell] .ps-hover-card-content {
            display: flex;
            flex-direction: column;
            min-width: 0;
            transform: translateY(5px);
            padding-bottom: 0;
        }

        [data-post-card-shell] .ps-hover-card-title {
            display: block;
            max-width: 168px;
            overflow: hidden;
            color: #0f172a;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.18;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-post-card-shell] .ps-hover-card-subtitle {
            display: block;
            max-width: 168px;
            overflow: hidden;
            color: #64748b;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-post-card-shell] .ps-hover-card-description {
            display: block;
            min-height: 28px;
            padding: 11px 11px 0;
            color: #475569;
            font-size: 11.5px;
            font-weight: 400;
            line-height: 1.38;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        [data-post-card-shell] .ps-hover-card-actions {
            display: flex;
            align-items: center;
            gap: 7px;
            width: calc(100% - 22px);
            margin: 10px 11px 0;
        }

        [data-post-card-shell] .ps-hover-card-follow-form {
            display: flex;
            flex: 1 1 0;
            min-width: 0;
            margin: 0 !important;
            padding: 0 !important;
        }

        [data-post-card-shell] .ps-hover-card-follow,
        [data-post-card-shell] .ps-hover-card-link {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            margin: 0 !important;
            padding: 8px 8px !important;
            border: 1px solid transparent;
            border-radius: 9px;
            font-family: var(--font-family-body), Roboto, Arial, sans-serif;
            font-size: 11.5px;
            font-weight: 400;
            line-height: 1;
            text-decoration: none !important;
            white-space: nowrap;
            box-shadow: none !important;
            cursor: pointer;
        }

        [data-post-card-shell] .ps-hover-card-follow {
            flex: 1 1 0;
            width: 100%;
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }

        [data-post-card-shell] .ps-hover-card-follow:hover,
        [data-post-card-shell] .ps-hover-card-follow:focus-visible {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
            outline: none !important;
        }

        [data-post-card-shell] .ps-hover-card-follow:disabled,
        [data-post-card-shell] .ps-hover-card-follow[aria-disabled="true"] {
            background: #e8f0ff !important;
            border-color: #dbeafe !important;
            color: #2563eb !important;
            cursor: default;
        }

        [data-post-card-shell] .ps-hover-card-actions > .ps-hover-card-link {
            flex: 1 1 0;
            width: auto !important;
            background: #f8fafc !important;
            color: #2563eb !important;
            border-color: #eef2f7 !important;
        }

        [data-post-card-shell] .ps-hover-card-actions > .ps-hover-card-link:hover,
        [data-post-card-shell] .ps-hover-card-actions > .ps-hover-card-link:focus-visible,
        [data-post-card-shell] .ps-hover-card > .ps-hover-card-link:hover,
        [data-post-card-shell] .ps-hover-card > .ps-hover-card-link:focus-visible {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            outline: none !important;
        }

        [data-post-card-shell] .ps-hover-card > .ps-hover-card-link {
            width: calc(100% - 22px) !important;
            margin: 10px 11px 0 !important;
            background: #f8fafc !important;
            color: #2563eb !important;
            border-color: #eef2f7 !important;
        }

        [data-post-card-shell] .ps-hover-card-actions > .ps-hover-card-link:only-child {
            flex-basis: 100%;
        }

        @keyframes postCardHoverCardContentReveal {
            to { opacity: 1; }
        }

        @keyframes postCardHoverCardSkeletonOff {
            to { opacity: 0; visibility: hidden; }
        }

        @keyframes postCardHoverCardShimmer {
            from { transform: translateX(-120%); }
            to { transform: translateX(120%); }
        }

        @media (max-width: 640px) {
            [data-post-card-shell] .ps-hover-card {
                position: fixed;
                left: 12px !important;
                right: 12px !important;
                top: auto !important;
                bottom: 16px !important;
                width: auto !important;
                max-width: none !important;
                transform: translate3d(0, 10px, 0);
            }

            [data-post-card-shell] .ps-hover-zone:hover > .ps-hover-card,
            [data-post-card-shell] .ps-hover-zone:focus-within > .ps-hover-card {
                transform: translate3d(0, 0, 0);
            }
        }

        html.dark [data-post-card-shell] .ps-hover-card,
        body.dark [data-post-card-shell] .ps-hover-card,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card {
            background: #0f172a;
            border-color: rgba(148, 163, 184, .22);
            color: #f8fafc;
        }

        html.dark [data-post-card-shell] .ps-hover-card-cover,
        body.dark [data-post-card-shell] .ps-hover-card-cover,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card-cover {
            background: linear-gradient(135deg, #1e293b 0%, #111827 100%);
        }

        html.dark [data-post-card-shell] .ps-hover-card-avatar,
        body.dark [data-post-card-shell] .ps-hover-card-avatar,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card-avatar {
            border-color: #0f172a;
            background: #1e293b;
            color: #e2e8f0;
        }

        html.dark [data-post-card-shell] .ps-hover-card-title,
        body.dark [data-post-card-shell] .ps-hover-card-title,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card-title {
            color: #f8fafc;
        }

        html.dark [data-post-card-shell] .ps-hover-card-subtitle,
        body.dark [data-post-card-shell] .ps-hover-card-subtitle,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card-subtitle,
        html.dark [data-post-card-shell] .ps-hover-card-description,
        body.dark [data-post-card-shell] .ps-hover-card-description,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card-description {
            color: #cbd5e1;
        }

        html.dark [data-post-card-shell] .ps-hover-card-actions > .ps-hover-card-link,
        body.dark [data-post-card-shell] .ps-hover-card-actions > .ps-hover-card-link,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card-actions > .ps-hover-card-link,
        html.dark [data-post-card-shell] .ps-hover-card > .ps-hover-card-link,
        body.dark [data-post-card-shell] .ps-hover-card > .ps-hover-card-link,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card > .ps-hover-card-link {
            background: rgba(255,255,255,.08) !important;
            border-color: rgba(255,255,255,.10) !important;
            color: #93c5fd !important;
        }

        html.dark [data-post-card-shell] .ps-hover-card-follow:disabled,
        body.dark [data-post-card-shell] .ps-hover-card-follow:disabled,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card-follow:disabled,
        html.dark [data-post-card-shell] .ps-hover-card-follow[aria-disabled="true"],
        body.dark [data-post-card-shell] .ps-hover-card-follow[aria-disabled="true"],
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card-follow[aria-disabled="true"] {
            background: rgba(37,99,235,.18) !important;
            border-color: rgba(147,197,253,.22) !important;
            color: #bfdbfe !important;
        }

        html.dark [data-post-card-shell] .ps-hover-card::before,
        body.dark [data-post-card-shell] .ps-hover-card::before,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card::before {
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

        html.dark [data-post-card-shell] .ps-hover-card::after,
        body.dark [data-post-card-shell] .ps-hover-card::after,
        [data-theme="dark"] [data-post-card-shell] .ps-hover-card::after {
            background: linear-gradient(100deg, transparent 0%, rgba(255,255,255,.12) 48%, transparent 100%);
        }

        /* OGRafi compact update: içerik yazısını ve kart yüksekliğini küçült */
        [data-post-card-shell] {
            padding: 14px 20px 10px !important;
        }

        [data-post-card-shell] .post-header {
            margin-bottom: 8px !important;
        }

        [data-post-card-shell] .post-summary-shell,
        [data-post-card-shell] [data-post-card-summary-shell] {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        [data-post-card-shell] .post-summary,
        [data-post-card-shell] [data-post-card-summary] {
            font-size: 13px !important;
            line-height: 1.38 !important;
            margin: 0 0 4px !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed .post-summary,
        [data-post-card-shell] .post-summary.is-collapsed,
        [data-post-card-shell] [data-post-card-summary-shell].is-collapsed [data-post-card-summary],
        [data-post-card-shell] [data-post-card-summary].is-collapsed {
            max-height: calc(1.38em * 2) !important;
            line-height: 1.38 !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed::after,
        [data-post-card-shell] [data-post-card-summary-shell].is-collapsed::after {
            height: 22px !important;
            bottom: 0 !important;
        }

        [data-post-card-shell] .expand-link,
        [data-post-card-shell] .expand-link[data-post-card-expand] {
            font-size: 12.5px !important;
            line-height: 1.2 !important;
            margin-top: 0 !important;
            margin-bottom: 6px !important;
        }

        [data-post-card-shell] .reaction-row,
        [data-post-card-shell] .reactions-row {
            gap: 8px !important;
            margin-bottom: 5px !important;
            padding-bottom: 7px !important;
        }

        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .reaction-add,
        [data-post-card-shell] .reaction-item,
        [data-post-card-shell] .more-pill {
            min-height: 28px !important;
            height: 28px !important;
            padding: 0 8px !important;
            font-size: 12.5px !important;
        }

        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .reaction-add {
            width: 28px !important;
            min-width: 28px !important;
            padding: 0 !important;
        }

        [data-post-card-shell] .reaction-emoji,
        [data-post-card-reaction-menu] .reaction-emoji,
        [data-post-card-shell] .reaction-emoji--html,
        [data-post-card-reaction-menu] .reaction-emoji--html,
        [data-post-card-shell] .reaction-emoji--html img,
        [data-post-card-reaction-menu] .reaction-emoji--html img,
        [data-post-card-shell] .reaction-emoji--html svg,
        [data-post-card-reaction-menu] .reaction-emoji--html svg,
        [data-post-card-shell] .reaction-emoji--html iconify-icon,
        [data-post-card-reaction-menu] .reaction-emoji--html iconify-icon {
            width: 17px !important;
            height: 17px !important;
            font-size: 17px !important;
        }

        [data-post-card-shell] .action-bar {
            gap: 8px !important;
            min-height: 26px !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        [data-post-card-shell] .action-left {
            gap: 14px !important;
        }

        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .post-card__action-link,
        [data-post-card-shell] .post-card__action-button {
            height: 26px !important;
            min-height: 26px !important;
            font-size: 13px !important;
        }

        [data-post-card-shell] .action-chip__label,
        [data-post-card-shell] .post-metric {
            font-size: 12px !important;
        }

        [data-post-card-shell] .post-card__reaction-custom-icon,
        [data-post-card-shell] .post-card__inline-icon,
        [data-post-card-shell] .post-card__reaction-custom-icon svg,
        [data-post-card-shell] .post-card__inline-icon svg,
        [data-post-card-shell] .post-card__inline-icon iconify-icon {
            width: 16px !important;
            height: 16px !important;
            font-size: 16px !important;
        }

        [data-post-card-shell] .post-card__bookmark-icon,
        [data-post-card-shell] .post-card__share-icon,
        [data-post-card-shell] .post-card__reaction-add-icon,
        [data-post-card-shell] .post-card__expand-icon {
            width: 14px !important;
            height: 14px !important;
        }

        [data-post-card-shell] .comment-row {
            margin-top: 4px !important;
            margin-bottom: 0 !important;
            gap: 8px !important;
        }

        [data-post-card-shell] .comment-label {
            font-size: 12.5px !important;
            line-height: 1.35 !important;
        }

        [data-post-card-shell] .post-card__media-frame,
        [data-post-card-shell] .post-card__media-image {
            max-height: 460px !important;
        }

        [data-post-card-shell] .post-card__full-figure img {
            max-height: 520px !important;
        }

        @media (max-width: 640px) {
            [data-post-card-shell] {
                padding: 12px 16px 9px !important;
            }

            [data-post-card-shell] .post-summary,
            [data-post-card-shell] [data-post-card-summary] {
                font-size: 12.75px !important;
                line-height: 1.36 !important;
            }

            [data-post-card-shell] .post-summary-shell.is-collapsed .post-summary,
            [data-post-card-shell] .post-summary.is-collapsed,
            [data-post-card-shell] [data-post-card-summary-shell].is-collapsed [data-post-card-summary],
            [data-post-card-shell] [data-post-card-summary].is-collapsed {
                max-height: calc(1.36em * 2) !important;
                line-height: 1.36 !important;
            }

            [data-post-card-shell] .action-left {
                gap: 12px !important;
            }

            [data-post-card-shell] .post-card__media-frame,
            [data-post-card-shell] .post-card__media-image {
                max-height: 340px !important;
            }
        }

        /* Post card readable scale fix */
        [data-post-card-shell] .post-summary,
        [data-post-card-shell] [data-post-card-summary] {
            font-size: 16px !important;
            line-height: 1.5 !important;
            margin: 0 0 6px !important;
        }

        [data-post-card-shell] .post-summary-shell.is-collapsed .post-summary,
        [data-post-card-shell] .post-summary.is-collapsed,
        [data-post-card-shell] [data-post-card-summary-shell].is-collapsed [data-post-card-summary],
        [data-post-card-shell] [data-post-card-summary].is-collapsed {
            max-height: calc(1.5em * 2) !important;
            line-height: 1.5 !important;
        }

        [data-post-card-shell] .expand-link,
        [data-post-card-shell] .expand-link[data-post-card-expand] {
            font-size: 15px !important;
            line-height: 1.3 !important;
        }

        [data-post-card-shell] .post-card__tags {
            gap: 9px !important;
        }

        [data-post-card-shell] .post-card__tag {
            font-size: 15px !important;
            line-height: 1.35 !important;
        }

        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .reaction-add,
        [data-post-card-shell] .reaction-item,
        [data-post-card-shell] .more-pill {
            min-height: 32px !important;
            height: 32px !important;
            padding: 0 9px !important;
            font-size: 14px !important;
        }

        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .reaction-add {
            width: 32px !important;
            min-width: 32px !important;
            padding: 0 !important;
        }

        [data-post-card-shell] .reaction-emoji,
        [data-post-card-reaction-menu] .reaction-emoji,
        [data-post-card-shell] .reaction-emoji--html,
        [data-post-card-reaction-menu] .reaction-emoji--html,
        [data-post-card-shell] .reaction-emoji--html img,
        [data-post-card-reaction-menu] .reaction-emoji--html img,
        [data-post-card-shell] .reaction-emoji--html svg,
        [data-post-card-reaction-menu] .reaction-emoji--html svg,
        [data-post-card-shell] .reaction-emoji--html iconify-icon,
        [data-post-card-reaction-menu] .reaction-emoji--html iconify-icon {
            width: 20px !important;
            height: 20px !important;
            font-size: 20px !important;
        }

        [data-post-card-shell] .action-bar {
            min-height: 30px !important;
        }

        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .post-card__action-link,
        [data-post-card-shell] .post-card__action-button {
            height: 30px !important;
            min-height: 30px !important;
            font-size: 14px !important;
        }

        [data-post-card-shell] .action-chip__label,
        [data-post-card-shell] .post-metric {
            font-size: 13.5px !important;
        }

        [data-post-card-shell] .post-card__reaction-custom-icon,
        [data-post-card-shell] .post-card__inline-icon,
        [data-post-card-shell] .post-card__reaction-custom-icon svg,
        [data-post-card-shell] .post-card__inline-icon svg,
        [data-post-card-shell] .post-card__inline-icon iconify-icon {
            width: 20px !important;
            height: 20px !important;
            font-size: 20px !important;
        }

        [data-post-card-shell] .post-card__bookmark-icon,
        [data-post-card-shell] .post-card__share-icon,
        [data-post-card-shell] .post-card__reaction-add-icon,
        [data-post-card-shell] .post-card__expand-icon {
            width: 18px !important;
            height: 18px !important;
        }

        @media (max-width: 640px) {
            [data-post-card-shell] .post-summary,
            [data-post-card-shell] [data-post-card-summary] {
                font-size: 15px !important;
                line-height: 1.48 !important;
            }

            [data-post-card-shell] .post-summary-shell.is-collapsed .post-summary,
            [data-post-card-shell] .post-summary.is-collapsed,
            [data-post-card-shell] [data-post-card-summary-shell].is-collapsed [data-post-card-summary],
            [data-post-card-shell] [data-post-card-summary].is-collapsed {
                max-height: calc(1.48em * 2) !important;
                line-height: 1.48 !important;
            }

            [data-post-card-shell] .post-card__tag,
            [data-post-card-shell] .expand-link,
            [data-post-card-shell] .expand-link[data-post-card-expand] {
                font-size: 14.5px !important;
            }
        }

        /* Final typography and reaction sizing requested for feed cards. */
        html body [data-post-card-shell] .post-title,
        html body [data-post-card-shell] .post-title__link {
            font-family: "Roboto", Arial, Helvetica, sans-serif !important;
            font-size: 20px !important;
            font-weight: 700 !important;
            line-height: 1.35 !important;
        }

        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji--html,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji--html img,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji--html svg,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji--html iconify-icon {
            width: 26px !important;
            height: 26px !important;
            min-width: 26px !important;
            font-size: 26px !important;
            line-height: 26px !important;
        }

        html body [data-post-card-reaction-menu] .post-card__reaction-option {
            min-width: 42px !important;
            min-height: 42px !important;
        }

        html body [data-post-card-reaction-layer] .post-card__reaction-option .reaction-emoji,
        html body [data-post-card-reaction-layer] .post-card__reaction-option .reaction-emoji--html,
        html body [data-post-card-reaction-layer] .post-card__reaction-option .reaction-emoji--html img,
        html body [data-post-card-reaction-layer] .post-card__reaction-option .reaction-emoji--html svg,
        html body [data-post-card-reaction-layer] .post-card__reaction-option .reaction-emoji--html iconify-icon,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji--html,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji--html img,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji--html svg,
        html body [data-post-card-reaction-menu] .post-card__reaction-option .reaction-emoji--html iconify-icon {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            max-width: 24px !important;
            font-size: 24px !important;
            line-height: 24px !important;
            object-fit: contain !important;
        }

        html body [data-post-card-reaction-layer] .post-card__reaction-option,
        html body [data-post-card-reaction-menu] .post-card__reaction-option {
            min-width: 40px !important;
            min-height: 40px !important;
            padding: 6px !important;
        }

        html body [data-post-card-shell] [data-post-card-summary-shell].is-collapsed,
        html body [data-post-card-shell] .post-summary-shell.is-collapsed {
            max-height: none !important;
            overflow: visible !important;
        }

        html body [data-post-card-shell] [data-post-card-summary-shell].is-collapsed [data-post-card-summary],
        html body [data-post-card-shell] .post-summary-shell.is-collapsed .post-summary {
            display: block !important;
            -webkit-line-clamp: unset !important;
            line-clamp: unset !important;
            max-height: none !important;
            overflow: visible !important;
            white-space: normal !important;
        }

        /* Commenter stack appears from three comments onward; feed actions stay black. */
        html body [data-post-card-shell] .comment-avatar,
        html body [data-post-card-shell] .comment-avatar-overflow {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            font-size: 11px !important;
        }

        html body [data-post-card-shell] .comment-avatar + .comment-avatar,
        html body [data-post-card-shell] .comment-avatar-overflow {
            margin-left: -9px !important;
        }

        html body [data-post-card-shell] .action-bar :is(
            .action-btn,
            .post-card__action-link,
            .post-card__action-button,
            .action-chip,
            .post-metric,
            .post-metric--views,
            .post-card__inline-icon,
            .post-card__inline-icon svg,
            .post-card__inline-icon iconify-icon,
            .post-card__bookmark-icon,
            .post-card__share-icon,
            .action-chip__label,
            [data-post-card-view-count]
        ) {
            color: #111111 !important;
            stroke: currentColor !important;
        }

        /* Authoritative mobile proportions based on the supplied reference card. */
        @media (max-width: 640px) {
            html,
            body {
                max-width: 100vw !important;
                overflow-x: clip !important;
                overflow-y: visible !important;
                touch-action: pan-y pinch-zoom !important;
            }

            html body .home-feed-shell,
            html body .home-feed-shell > .ografi-filterable-post {
                width: 100vw !important;
                min-width: 0 !important;
                max-width: 100vw !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                overflow-x: hidden !important;
                box-sizing: border-box !important;
            }

            html body article.post-card[data-post-card-shell] {
                width: 100vw !important;
                min-width: 0 !important;
                max-width: 100vw !important;
                margin-top: 0 !important;
                margin-right: calc(50% - 50vw) !important;
                margin-bottom: 0 !important;
                margin-left: calc(50% - 50vw) !important;
                padding: 10px 15px 0 !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 8px !important;
                background: #fff !important;
                box-shadow: none !important;
                overflow: hidden !important;
            }

            html body article.post-card[data-post-card-shell] * {
                min-width: 0;
                box-sizing: border-box;
            }

            html body article.post-card[data-post-card-shell] .post-header {
                min-height: 39px !important;
                margin: 0 0 6px !important;
            }

            html body article.post-card[data-post-card-shell] .author-block {
                gap: 7px !important;
            }

            html body article.post-card[data-post-card-shell] .author-avatar {
                width: 34px !important;
                height: 34px !important;
                min-width: 34px !important;
            }

            html body article.post-card[data-post-card-shell] .author-name {
                font-size: 14px !important;
                font-weight: 600 !important;
                line-height: 18px !important;
            }

            html body article.post-card[data-post-card-shell] :is(.author-subline, .post-time, .author-subline__topic) {
                font-size: 12px !important;
                line-height: 16px !important;
            }

            html body article.post-card[data-post-card-shell] h2.post-title,
            html body article.post-card[data-post-card-shell] h2.post-title > a.post-title__link {
                display: block !important;
                margin: 0 0 18px !important;
                padding: 0 !important;
                font-family: Roboto, Arial, sans-serif !important;
                font-size: 20px !important;
                font-weight: 700 !important;
                line-height: 1.32 !important;
                letter-spacing: 0 !important;
                color: #050505 !important;
                white-space: normal !important;
                overflow-wrap: anywhere !important;
            }

            html body article.post-card[data-post-card-shell] .post-card__media-wrap {
                width: 100% !important;
                margin: 0 0 17px !important;
                border: 0 !important;
                border-radius: 8px !important;
                overflow: hidden !important;
            }

            html body article.post-card[data-post-card-shell] :is(.post-card__media-scroller, .post-card__media-slide, .post-card__media-link, .post-card__media-frame) {
                width: 100% !important;
                max-width: 100% !important;
            }

            html body article.post-card[data-post-card-shell] :is(.post-card__media-frame, .post-card__media-image, .hero-image) {
                display: block !important;
                width: 100% !important;
                min-height: 0 !important;
                height: auto !important;
                max-height: none !important;
                aspect-ratio: 1.5 / 1 !important;
                object-fit: cover !important;
                border-radius: 8px !important;
            }

            html body article.post-card[data-post-card-shell] :is(.post-summary-shell, .post-summary-shell.is-collapsed) {
                max-height: none !important;
                margin: 0 0 3px !important;
                padding: 0 !important;
                overflow: hidden !important;
            }

            html body article.post-card[data-post-card-shell] :is(.post-summary, .post-summary.is-collapsed, [data-post-card-summary]) {
                display: -webkit-box !important;
                max-height: none !important;
                margin: 0 !important;
                overflow: hidden !important;
                -webkit-box-orient: vertical !important;
                -webkit-line-clamp: 6 !important;
                line-clamp: 6 !important;
                white-space: normal !important;
                font-size: 16px !important;
                line-height: 1.5 !important;
                color: #111 !important;
            }

            html body article.post-card[data-post-card-shell] .expand-link {
                margin: 5px 0 15px !important;
                padding: 0 !important;
                font-size: 17px !important;
                font-weight: 600 !important;
                line-height: 22px !important;
                color: #2563eb !important;
            }

            html body article.post-card[data-post-card-shell] .post-card__expand-icon,
            html body article.post-card[data-post-card-shell] .post-card__expand-icon iconify-icon {
                width: 20px !important;
                height: 20px !important;
                font-size: 20px !important;
                color: #2563eb !important;
            }

            html body article.post-card[data-post-card-shell] .post-card__tags {
                gap: 8px 12px !important;
                margin: 0 0 17px !important;
            }

            html body article.post-card[data-post-card-shell] .post-card__tag {
                font-size: 15px !important;
                line-height: 20px !important;
                color: #2563eb !important;
            }

            html body article.post-card[data-post-card-shell] .reactions-row {
                min-height: 35px !important;
                margin: 0 0 6px !important;
                padding: 0 !important;
                gap: 7px !important;
                border: 0 !important;
            }

            html body article.post-card[data-post-card-shell] .action-bar {
                min-height: 48px !important;
                height: 48px !important;
                margin: 0 -15px !important;
                padding: 0 15px !important;
                border-top: 1px solid #e5e7eb !important;
            }

            html body article.post-card[data-post-card-shell] :is(.action-btn, .action-stat, .post-card__action-link, .post-card__action-button) {
                min-height: 36px !important;
                height: 36px !important;
            }

            html body article.post-card[data-post-card-shell] :is(.post-card__inline-icon, .post-card__inline-icon svg, .post-card__bookmark-icon, .post-card__share-icon) {
                width: 22px !important;
                height: 22px !important;
            }
        }

        html body article.post-card[data-post-card-shell] .expand-link,
        html body article.post-card[data-post-card-shell] .post-card__tag {
            color: #2563eb !important;
        }

        html body article.post-card[data-post-card-shell] .expand-link {
            font-size: 17px !important;
            line-height: 22px !important;
            font-weight: 600 !important;
        }

        html body article.post-card[data-post-card-shell] .post-card__expand-icon,
        html body article.post-card[data-post-card-shell] .post-card__expand-icon iconify-icon {
            width: 20px !important;
            height: 20px !important;
            font-size: 20px !important;
            color: #2563eb !important;
        }

</style>

    <script>
        (function () {
            if (window.__postCardIntegratedInit) {
                return;
            }

            window.__postCardIntegratedInit = true;

            const rootSelector = '[data-post-card-shell]';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const applyMobileReferenceCardStyles = function () {
                if (!window.matchMedia('(max-width: 640px)').matches) {
                    return;
                }

                const viewportWidth = Math.max(280, document.documentElement.clientWidth || window.innerWidth || 360);
                const cardWidth = Math.max(278, viewportWidth - 2) + 'px';
                const contentWidth = Math.max(248, viewportWidth - 32) + 'px';

                document.querySelectorAll(rootSelector).forEach(function (card) {
                    const force = function (element, properties) {
                        if (!element) return;
                        Object.entries(properties).forEach(function ([property, value]) {
                            element.style.setProperty(property, value, 'important');
                        });
                    };

                    force(card, {
                        width: cardWidth,
                        'min-width': '0',
                        'max-width': cardWidth,
                        margin: '0',
                        padding: '10px 15px 0',
                        overflow: 'hidden',
                        'box-sizing': 'border-box',
                        'border-radius': '8px'
                    });

                    card.querySelectorAll('.post-header, .post-title, .post-summary-shell, .post-card__full-content, .post-card__tags, .reactions-row, .comment-row').forEach(function (element) {
                        force(element, { width: contentWidth, 'min-width': '0', 'max-width': contentWidth });
                    });

                    card.querySelectorAll('.post-card__media-wrap, .post-card__media-scroller, .post-card__media-slide, .post-card__media-link, .post-card__media-frame, .post-card__media-image').forEach(function (element) {
                        force(element, { width: contentWidth, 'min-width': contentWidth, 'max-width': contentWidth });
                    });

                    card.querySelectorAll('.post-title, .post-title__link').forEach(function (element) {
                        force(element, {
                            'font-size': '18px',
                            'font-weight': '700',
                            'line-height': '1.42',
                            'white-space': 'normal',
                            'overflow-wrap': 'anywhere'
                        });
                    });

                    card.querySelectorAll('.post-summary, [data-post-card-summary]').forEach(function (element) {
                        force(element, {
                            display: '-webkit-box',
                            'font-size': '17px',
                            'font-weight': '400',
                            'line-height': '1.48',
                            overflow: 'hidden',
                            '-webkit-box-orient': 'vertical',
                            '-webkit-line-clamp': '6',
                            'white-space': 'normal'
                        });
                    });

                    card.querySelectorAll('.author-name').forEach(function (element) {
                        force(element, { 'font-size': '14px', 'line-height': '18px', 'font-weight': '600' });
                    });

                    card.querySelectorAll('.author-subline, .post-time, .author-subline__topic').forEach(function (element) {
                        force(element, { 'font-size': '12px', 'line-height': '16px' });
                    });

                    card.querySelectorAll('.expand-link, .post-card__tag').forEach(function (element) {
                        force(element, { 'font-size': '16px', 'line-height': '22px', 'font-weight': '600' });
                    });

                    card.querySelectorAll('.post-card__media-frame, .post-card__media-image').forEach(function (element) {
                        force(element, { height: 'auto', 'aspect-ratio': '1.5 / 1', 'object-fit': 'cover' });
                    });

                    const actionBar = card.querySelector('.action-bar');
                    force(actionBar, {
                        width: cardWidth,
                        'min-width': cardWidth,
                        'max-width': cardWidth,
                        height: '48px',
                        'min-height': '48px',
                        margin: '0 -15px',
                        padding: '0'
                    });

                    card.querySelectorAll('.post-card__inline-icon, .post-card__inline-icon svg, .post-card__bookmark-icon, .post-card__share-icon').forEach(function (element) {
                        force(element, { width: '24px', height: '24px' });
                    });
                });
            };

            applyMobileReferenceCardStyles();

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applyMobileReferenceCardStyles, { once: true });
            }

            let mobileCardStyleFrame = 0;
            const mobileCardObserver = new MutationObserver(function (mutations) {
                const hasNewCard = mutations.some(function (mutation) {
                    return Array.from(mutation.addedNodes).some(function (node) {
                        return node.nodeType === Node.ELEMENT_NODE
                            && (node.matches?.(rootSelector) || node.querySelector?.(rootSelector));
                    });
                });

                if (!hasNewCard || mobileCardStyleFrame) return;

                mobileCardStyleFrame = window.requestAnimationFrame(function () {
                    mobileCardStyleFrame = 0;
                    applyMobileReferenceCardStyles();
                });
            });

            mobileCardObserver.observe(document.documentElement, {
                childList: true,
                subtree: true
            });

            const forceFinishPreload = function (card) {
                if (!card) {
                    return;
                }

                card.classList.add('is-preloading-done');
                card.classList.remove('is-preloading');

                card.querySelectorAll('.post-card__media-frame').forEach(function (frame) {
                    frame.classList.add('is-loaded');
                    frame.classList.remove('is-error');
                });
            };



            const setupTextPreload = function (card) {
                if (!card) {
                    return;
                }

                const startedAt = Date.now();
                const minimumVisibleTime = 700;
                const maximumVisibleTime = 3200;
                let finished = false;

                const finishPreload = function () {
                    if (finished) {
                        return;
                    }

                    finished = true;
                    const elapsed = Date.now() - startedAt;
                    const delay = Math.max(0, minimumVisibleTime - elapsed);

                    window.setTimeout(function () {
                        forceFinishPreload(card);
                    }, delay);
                };

                const imageAssets = Array.from(card.querySelectorAll('img'));
                const pendingImages = imageAssets.filter(function (image) {
                    return !(image.complete && image.naturalWidth > 0);
                });

                if (!pendingImages.length) {
                    finishPreload();
                    return;
                }

                let pendingCount = pendingImages.length;
                const markOneDone = function () {
                    pendingCount -= 1;
                    if (pendingCount <= 0) {
                        finishPreload();
                    }
                };

                pendingImages.forEach(function (image) {
                    image.addEventListener('load', markOneDone, { once: true });
                    image.addEventListener('error', markOneDone, { once: true });
                });

                window.setTimeout(finishPreload, maximumVisibleTime);
            };

            const setupMediaPreload = function (card) {
                if (!card) {
                    return;
                }

                card.querySelectorAll('.post-card__media-frame').forEach(function (frame) {
                    const image = frame.querySelector('.post-card__media-image');

                    if (!image) {
                        frame.classList.add('is-loaded');
                        frame.classList.remove('is-error');
                        return;
                    }

                    const markLoaded = function () {
                        frame.classList.add('is-loaded');
                        frame.classList.remove('is-error');
                    };

                    const markError = function () {
                        frame.classList.add('is-error');
                    };

                    if (image.complete && image.naturalWidth > 0) {
                        markLoaded();
                        return;
                    }

                    image.addEventListener('load', markLoaded, { once: true });
                    image.addEventListener('error', markError, { once: true });
                });
            };

            const copyText = async function (value) {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(value);
                    return;
                }

                const input = document.createElement('input');
                input.value = value;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
            };

            const showToast = function (card, message) {
                const toast = card?.querySelector('[data-post-card-toast]');
                if (!toast) {
                    return;
                }

                toast.textContent = message;
                toast.classList.add('is-visible');
                window.clearTimeout(toast.__timer);
                toast.__timer = window.setTimeout(function () {
                    toast.classList.remove('is-visible');
                }, 1600);
            };

            const pulseActionButton = function (button) {
                if (!button) {
                    return;
                }

                button.classList.add('is-active');
                window.clearTimeout(button.__activeTimer);
                button.__activeTimer = window.setTimeout(function () {
                    button.classList.remove('is-active');
                }, 900);
            };

            const formatMetric = function (value) {
                const numeric = Number(value) || 0;
                if (numeric >= 1000000) {
                    const formatted = (numeric / 1000000).toFixed(numeric >= 10000000 ? 0 : 1);
                    return formatted.replace(/\.0$/, '') + 'M';
                }

                if (numeric >= 1000) {
                    const formatted = (numeric / 1000).toFixed(numeric >= 10000 ? 0 : 1);
                    return formatted.replace(/\.0$/, '') + 'K';
                }

                return new Intl.NumberFormat('tr-TR').format(numeric);
            };

            const formatFullMetric = function (value) {
                return new Intl.NumberFormat('tr-TR').format(Math.max(Number(value) || 0, 0));
            };

            const syncStatsModalCounts = function (card) {
                const modal = card?.querySelector('[data-post-card-stats-modal]');
                if (!modal) {
                    return;
                }

                const feedCount = Number(modal.dataset.postCardStatsFeedCount) || 0;
                const listingCount = Number(modal.dataset.postCardStatsListingCount) || 0;
                const reactionCount = Number(modal.dataset.postCardStatsReactionsCount) || 0;
                const commentCount = Number(modal.dataset.postCardStatsCommentsCount) || 0;
                const bookmarkCount = Number(modal.dataset.postCardStatsBookmarksCount) || 0;
                const total = feedCount + listingCount + reactionCount + commentCount + bookmarkCount;

                const totalNode = modal.querySelector('[data-post-card-stats-total]');
                const feedNode = modal.querySelector('[data-post-card-stats-feed]');
                const listingNode = modal.querySelector('[data-post-card-stats-listing]');

                if (totalNode) {
                    totalNode.textContent = formatMetric(total);
                }

                if (feedNode) {
                    feedNode.textContent = formatFullMetric(feedCount);
                }

                if (listingNode) {
                    listingNode.textContent = formatFullMetric(listingCount);
                }
            };

            const setStatsModalState = function (card, open) {
                const trigger = card?.querySelector('[data-post-card-stats-trigger]');
                const modal = card?.querySelector('[data-post-card-stats-modal]');
                if (!trigger || !modal) {
                    return;
                }

                if (open) {
                    syncStatsModalCounts(card);
                }

                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                modal.hidden = !open;
            };

            const syncViewCount = function (card, count) {
                if (!card) {
                    return;
                }

                const numericCount = Number(count) || 0;

                card.querySelectorAll('[data-post-card-view-count]').forEach(function (node) {
                    node.textContent = formatMetric(numericCount);
                });

                card.querySelectorAll('[data-post-card-view-metric]').forEach(function (node) {
                    // View ikonu ve sayacı sadece sayı 1 veya daha fazlaysa görünür.
                    node.hidden = numericCount < 1;
                });

                const statsModal = card.querySelector('[data-post-card-stats-modal]');
                if (statsModal) {
                    if (statsModal.dataset.postCardStatsFeedFollowsViews === 'true') {
                        statsModal.dataset.postCardStatsFeedCount = String(numericCount);
                    }

                    if (statsModal.dataset.postCardStatsListingFollowsViews === 'true') {
                        statsModal.dataset.postCardStatsListingCount = String(numericCount);
                    }

                    syncStatsModalCounts(card);
                }
            };

            const recordView = async function (card) {
                if (!card || card.getAttribute('data-post-view-recorded') === 'true') {
                    return;
                }

                const viewUrl = card.getAttribute('data-post-view-url') || '';
                if (!viewUrl || !csrfToken) {
                    return;
                }

                const response = await fetch(viewUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('view_request_failed');
                }

                const payload = await response.json();
                if (typeof payload.count === 'number') {
                    syncViewCount(card, payload.count);
                }

                card.setAttribute('data-post-view-recorded', 'true');
            };

            const setMenuState = function (card, open) {
                const button = card?.querySelector('[data-og-action-trigger]');
                const panel = card?.querySelector('[data-og-action-menu]');
                if (!button || !panel) {
                    return;
                }

                button.setAttribute('aria-expanded', open ? 'true' : 'false');
                panel.classList.toggle('hidden', !open);

                if (!open) {
                    panel.style.top = '';
                    panel.style.left = '';
                    return;
                }

                const rect = button.getBoundingClientRect();
                const menuWidth = panel.offsetWidth;
                const menuHeight = panel.offsetHeight;
                const padding = 10;

                let top = rect.bottom + 8;
                let left = rect.left + (rect.width / 2) - (menuWidth / 2);

                if (left < padding) {
                    left = padding;
                }

                if (left + menuWidth > window.innerWidth - padding) {
                    left = window.innerWidth - menuWidth - padding;
                }

                if (top + menuHeight > window.innerHeight - padding) {
                    top = rect.top - menuHeight - 8;
                }

                panel.style.top = `${Math.round(top)}px`;
                panel.style.left = `${Math.round(left)}px`;
            };

            const ensureReactionLayer = function () {
                let layer = document.querySelector('[data-post-card-reaction-layer]');
                if (layer) {
                    return layer;
                }

                layer = document.createElement('div');
                layer.setAttribute('data-post-card-reaction-layer', '');
                document.body.appendChild(layer);

                return layer;
            };

            const getReactionElements = function (card) {
                const button = card?.querySelector('[data-post-card-reaction-trigger]');
                const wrap = card?.querySelector('[data-post-card-reaction-wrap]');
                const panelId = button?.getAttribute('aria-controls') || '';
                const panel = panelId ? document.getElementById(panelId) : null;

                return { button, wrap, panel };
            };

            const mountReactionMenu = function (card) {
                const { wrap, panel } = getReactionElements(card);
                if (!wrap || !panel) {
                    return;
                }

                panel.dataset.reactionHomeId = wrap.getAttribute('data-post-card-reaction-id') || '';
                ensureReactionLayer().appendChild(panel);
            };

            const restoreReactionMenu = function (card) {
                const { wrap, panel } = getReactionElements(card);
                if (!wrap || !panel) {
                    return;
                }

                wrap.appendChild(panel);
            };

            const resetReactionMenuPosition = function (card) {
                const { panel } = getReactionElements(card);
                if (!panel) {
                    return;
                }

                panel.style.position = '';
                panel.style.display = '';
                panel.style.gridTemplateColumns = '';
                panel.style.gridAutoRows = '';
                panel.style.alignItems = '';
                panel.style.gap = '';
                panel.style.padding = '';
                panel.style.boxSizing = '';
                panel.style.left = '';
                panel.style.right = '';
                panel.style.top = '';
                panel.style.width = '';
                panel.style.maxWidth = '';
                panel.style.transform = '';

                const title = panel.querySelector('.post-card__reaction-menu-title');
                if (title) {
                    title.style.gridColumn = '';
                }

                panel.querySelectorAll('.post-card__reaction-form, a.post-card__reaction-option').forEach(function (item) {
                    item.style.display = '';
                    item.style.width = '';
                    item.style.height = '';
                });
            };

            const positionReactionMenu = function (card) {
                const { button: trigger, panel } = getReactionElements(card);
                if (!panel || panel.hidden) {
                    return;
                }

                const isMobile = window.innerWidth <= 640;
                const viewportPadding = isMobile ? 10 : 16;
                if (!trigger) {
                    return;
                }

                const triggerRect = trigger.getBoundingClientRect();
                const desiredWidth = 208;
                const panelWidth = Math.min(desiredWidth, window.innerWidth - (viewportPadding * 2));
                const preferredLeft = isMobile
                    ? triggerRect.left - 12
                    : triggerRect.right - panelWidth;
                const maxLeft = Math.max(viewportPadding, window.innerWidth - panelWidth - viewportPadding);
                const left = Math.max(viewportPadding, Math.min(preferredLeft, maxLeft));

                panel.style.position = 'fixed';
                panel.style.display = 'grid';
                panel.style.gridTemplateColumns = 'repeat(4, 34px)';
                panel.style.gridAutoRows = '34px';
                panel.style.alignItems = 'stretch';
                panel.style.gap = '10px 12px';
                panel.style.padding = '10px 12px 12px';
                panel.style.boxSizing = 'border-box';
                panel.style.left = `${Math.round(left)}px`;
                panel.style.right = 'auto';
                panel.style.width = `${Math.round(panelWidth)}px`;
                panel.style.maxWidth = `${Math.round(panelWidth)}px`;
                panel.style.transform = 'translateX(0)';

                const title = panel.querySelector('.post-card__reaction-menu-title');
                if (title) {
                    title.style.gridColumn = '1 / -1';
                }

                panel.querySelectorAll('.post-card__reaction-form, a.post-card__reaction-option').forEach(function (item) {
                    item.style.display = 'flex';
                    item.style.width = '34px';
                    item.style.height = '34px';
                });

                const panelRect = panel.getBoundingClientRect();
                const maxTop = Math.max(viewportPadding, window.innerHeight - panelRect.height - viewportPadding);
                const top = Math.max(viewportPadding, Math.min(triggerRect.bottom + 8, maxTop));
                panel.style.top = `${Math.round(top)}px`;
            };

            const setReactionState = function (card, open) {
                const { button, panel } = getReactionElements(card);
                if (!button || !panel) {
                    return;
                }

                button.setAttribute('aria-expanded', open ? 'true' : 'false');

                if (open) {
                    mountReactionMenu(card);
                    panel.hidden = false;
                    window.requestAnimationFrame(function () {
                        positionReactionMenu(card);
                    });
                    return;
                }

                panel.hidden = true;
                resetReactionMenuPosition(card);
                restoreReactionMenu(card);
            };

            const setEditedState = function (card, open) {
                const button = card?.querySelector('[data-post-card-edit-toggle]');
                const label = card?.querySelector('[data-post-card-edit-label]');
                if (!button || !label) {
                    return;
                }

                button.setAttribute('aria-expanded', open ? 'true' : 'false');
                label.hidden = !open;
            };

            const closeFloatingPanels = function (exceptCard) {
                document.querySelectorAll(rootSelector).forEach(function (card) {
                    if (exceptCard && card === exceptCard) {
                        return;
                    }

                    setMenuState(card, false);
                    setReactionState(card, false);
                    setEditedState(card, false);
                    setStatsModalState(card, false);
                });
            };


            const setupMediaCarousel = function (card) {
                const scroller = card?.querySelector('[data-post-card-media-scroller]');
                if (!scroller || scroller.dataset.carouselBound === 'true') {
                    return;
                }

                scroller.dataset.carouselBound = 'true';

                let isPointerDown = false;
                let startX = 0;
                let startY = 0;
                let scrollLeft = 0;
                let isDragging = false;
                let isHorizontalDrag = false;
                let clickBlockUntil = 0;

                const stopDragging = function (event) {
                    if (event?.pointerId && scroller.hasPointerCapture?.(event.pointerId)) {
                        try {
                            scroller.releasePointerCapture(event.pointerId);
                        } catch (error) {
                            // Ignore release errors on browsers that already released capture.
                        }
                    }

                    isPointerDown = false;
                    isHorizontalDrag = false;

                    window.requestAnimationFrame(function () {
                        scroller.classList.remove('is-dragging');
                    });
                };

                scroller.addEventListener('pointerdown', function (event) {
                    if (event.pointerType === 'mouse' && event.button !== 0) {
                        return;
                    }

                    isPointerDown = true;
                    isDragging = false;
                    isHorizontalDrag = false;
                    startX = event.clientX;
                    startY = event.clientY;
                    scrollLeft = scroller.scrollLeft;

                    if (event.pointerType === 'mouse') {
                        scroller.classList.add('is-dragging');
                        scroller.setPointerCapture?.(event.pointerId);
                    }
                });

                scroller.addEventListener('pointermove', function (event) {
                    if (!isPointerDown) {
                        return;
                    }

                    const diffX = event.clientX - startX;
                    const diffY = event.clientY - startY;
                    const absX = Math.abs(diffX);
                    const absY = Math.abs(diffY);

                    if (!isDragging && absX < 8 && absY < 8) {
                        return;
                    }

                    if (!isHorizontalDrag && absY > absX && event.pointerType !== 'mouse') {
                        stopDragging(event);
                        return;
                    }

                    if (absX <= absY || absX < 10) {
                        return;
                    }

                    isDragging = true;
                    isHorizontalDrag = true;
                    clickBlockUntil = Date.now() + 350;

                    scroller.classList.add('is-dragging');

                    if (!scroller.hasPointerCapture?.(event.pointerId)) {
                        scroller.setPointerCapture?.(event.pointerId);
                    }

                    scroller.scrollLeft = scrollLeft - diffX;
                    event.preventDefault();
                }, { passive: false });

                scroller.addEventListener('pointerup', function (event) {
                    if (isDragging && isHorizontalDrag) {
                        clickBlockUntil = Date.now() + 350;
                        event.preventDefault();
                    }

                    stopDragging(event);
                }, { passive: false });

                scroller.addEventListener('pointercancel', function (event) {
                    stopDragging(event);
                });

                scroller.addEventListener('lostpointercapture', function (event) {
                    stopDragging(event);
                });

                scroller.querySelectorAll('a, img').forEach(function (node) {
                    node.addEventListener('dragstart', function (event) {
                        event.preventDefault();
                    });
                });

                scroller.addEventListener('click', function (event) {
                    if (Date.now() < clickBlockUntil) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                }, true);
            };

            const syncExpandedState = function (card, expanded) {
                const summary = card?.querySelector('[data-post-card-summary]');
                const trigger = card?.querySelector('[data-post-card-expand]');
                const label = card?.querySelector('[data-post-card-expand-label]');
                const summaryShell = card?.querySelector('[data-post-card-summary-shell]');
                const source = card?.querySelector('[data-post-card-source]');
                const fullContent = card?.querySelector('[data-post-card-full-content]');
                const collapsedTemplate = summaryShell?.querySelector('[data-post-card-summary-collapsed]');
                const expandedTemplate = summaryShell?.querySelector('[data-post-card-summary-expanded]');

                if (!trigger || !label) {
                    return;
                }

                const collapsedLabel = trigger.getAttribute('data-post-card-expand-collapsed-label') || 'Daha fazla göster';
                const expandedLabel = trigger.getAttribute('data-post-card-expand-expanded-label') || 'Daha az göster';

                if (card) {
                    card.classList.toggle('is-summary-expanded', expanded);
                    card.classList.toggle('is-summary-collapsed', !expanded);
                }

                if (summary) {
                    const collapsedText = collapsedTemplate?.content?.textContent ?? collapsedTemplate?.textContent ?? summary.textContent ?? '';
                    const expandedText = expandedTemplate?.content?.textContent ?? expandedTemplate?.textContent ?? collapsedText;

                    summary.textContent = fullContent ? collapsedText : (expanded ? expandedText : collapsedText);
                    summary.classList.toggle('is-expanded', expanded);
                    summary.classList.toggle('is-collapsed', !expanded);
                }

                trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                trigger.hidden = expanded;
                trigger.style.display = expanded ? 'none' : 'inline-flex';
                trigger.setAttribute('aria-hidden', expanded ? 'true' : 'false');
                label.textContent = expanded ? expandedLabel : collapsedLabel;

                if (summaryShell) {
                    summaryShell.hidden = false;
                    summaryShell.classList.toggle('is-expanded', expanded && !fullContent);
                    summaryShell.classList.toggle('is-collapsed', !expanded || Boolean(fullContent));
                }

                if (fullContent) {
                    fullContent.hidden = !expanded;
                    fullContent.style.display = expanded ? '' : 'none';
                }

                if (source) {
                    source.hidden = !expanded;
                }
            };

            const setupNsfwPreferencesLinks = function () {
                document.querySelectorAll('[data-nsfw-preferences-link]').forEach(function (link) {
                    if (link.dataset.nsfwLinkReady === 'true') {
                        return;
                    }

                    link.dataset.nsfwLinkReady = 'true';
                    let touchStartY = 0;
                    let touchMoved = false;

                    link.addEventListener('touchstart', function (event) {
                        touchMoved = false;
                        touchStartY = event.touches && event.touches.length ? event.touches[0].clientY : 0;
                    }, { passive: true });

                    link.addEventListener('touchmove', function (event) {
                        const currentY = event.touches && event.touches.length ? event.touches[0].clientY : touchStartY;

                        if (Math.abs(currentY - touchStartY) > 8) {
                            touchMoved = true;
                        }
                    }, { passive: true });

                    link.addEventListener('click', function (event) {
                        if (touchMoved) {
                            event.preventDefault();
                            touchMoved = false;
                            return;
                        }

                        link.classList.add('is-loading');
                    });
                });
            };

            document.addEventListener('click', async function (event) {
                const menuTrigger = event.target.closest('[data-og-action-trigger]');
                if (menuTrigger) {
                    const card = menuTrigger.closest(rootSelector);
                    const open = menuTrigger.getAttribute('aria-expanded') !== 'true';
                    closeFloatingPanels(card);
                    setMenuState(card, open);
                    return;
                }

                const reactionTrigger = event.target.closest('[data-post-card-reaction-trigger]');
                if (reactionTrigger) {
                    const card = reactionTrigger.closest(rootSelector);
                    const open = reactionTrigger.getAttribute('aria-expanded') !== 'true';
                    closeFloatingPanels(card);
                    setReactionState(card, open);
                    return;
                }

                const editTrigger = event.target.closest('[data-post-card-edit-toggle]');
                if (editTrigger) {
                    const card = editTrigger.closest(rootSelector);
                    const open = editTrigger.getAttribute('aria-expanded') !== 'true';
                    closeFloatingPanels(card);
                    setEditedState(card, open);
                    return;
                }

                const statsTrigger = event.target.closest('[data-post-card-stats-trigger]');
                if (statsTrigger) {
                    event.preventDefault();
                    const card = statsTrigger.closest(rootSelector);
                    const open = statsTrigger.getAttribute('aria-expanded') !== 'true';
                    closeFloatingPanels(card);
                    setStatsModalState(card, open);
                    return;
                }

                const statsClose = event.target.closest('[data-post-card-stats-close]');
                if (statsClose) {
                    event.preventDefault();
                    const card = statsClose.closest(rootSelector);
                    setStatsModalState(card, false);
                    return;
                }

                const statsModal = event.target.closest('[data-post-card-stats-modal]');
                if (statsModal && event.target === statsModal) {
                    const card = statsModal.closest(rootSelector);
                    setStatsModalState(card, false);
                    return;
                }

                const copyTrigger = event.target.closest('[data-post-card-copy]');
                if (copyTrigger) {
                    event.preventDefault();
                    const card = copyTrigger.closest(rootSelector);
                    const shareUrl = card?.getAttribute('data-post-url');
                    const shareTitle = card?.getAttribute('data-post-title') || document.title;
                    const shareTrigger = copyTrigger.closest('[data-post-card-share-trigger]') ? copyTrigger : null;
                    closeFloatingPanels();

                    if (!card || !shareUrl) {
                        return;
                    }

                    try {
                        pulseActionButton(shareTrigger);
                        if (shareTrigger && navigator.share) {
                            await navigator.share({ title: shareTitle, url: shareUrl });
                            showToast(card, 'Paylasildi');
                        } else {
                            await copyText(shareUrl);
                            showToast(card, 'Link kopyalandi');
                        }
                    } catch (error) {
                        if (error?.name === 'AbortError') {
                            return;
                        }

                        try {
                            await copyText(shareUrl);
                            showToast(card, 'Link kopyalandi');
                        } catch (copyError) {
                            showToast(card, 'Paylasim kullanilamiyor');
                        }
                    }
                    return;
                }

                const expandTrigger = event.target.closest('[data-post-card-expand]');
                if (expandTrigger && expandTrigger.tagName === 'BUTTON') {
                    const card = expandTrigger.closest(rootSelector);
                    const hasInlineContent = Boolean(
                        card?.querySelector('[data-post-card-summary]') ||
                        card?.querySelector('[data-post-card-full-content]') ||
                        card?.querySelector('[data-post-card-source]')
                    );

                    // Özet/içerik bulunmayan nadir kartlarda buton boşa basmasın; post detayına gitsin.
                    if (!hasInlineContent && card?.dataset?.postUrl && card.dataset.postUrl !== '#') {
                        window.location.href = card.dataset.postUrl;
                        return;
                    }

                    const expanded = expandTrigger.getAttribute('aria-expanded') !== 'true';
                    syncExpandedState(card, expanded);
                    if (expanded) {
                        try {
                            await recordView(card);
                        } catch (error) {
                            showToast(card, 'Goruntulenme kaydedilemedi');
                        }
                    }
                    return;
                }

                if (!event.target.closest('[data-og-action-wrap]') && !event.target.closest('[data-og-action-menu]') && !event.target.closest('[data-post-card-reaction-wrap]') && !event.target.closest('[data-post-card-reaction-menu]') && !event.target.closest('[data-post-card-stats-modal]') && !event.target.closest('.author-subline__edit-wrap')) {
                    closeFloatingPanels();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeFloatingPanels();
                }
            });

            window.addEventListener('resize', function () {
                document.querySelectorAll(rootSelector).forEach(function (card) {
                    setMenuState(card, false);
                });
                document.querySelectorAll(rootSelector).forEach(function (card) {
                    positionReactionMenu(card);
                });
            });

            window.addEventListener('scroll', function () {
                document.querySelectorAll(rootSelector).forEach(function (card) {
                    setMenuState(card, false);
                });
                document.querySelectorAll(rootSelector).forEach(function (card) {
                    positionReactionMenu(card);
                });
            }, { passive: true });

            document.querySelectorAll(rootSelector).forEach(function (card) {
                syncExpandedState(card, false);
                setMenuState(card, false);
                setReactionState(card, false);
                setEditedState(card, false);
                setupTextPreload(card);
                setupMediaPreload(card);
                setupMediaCarousel(card);
                setupNsfwPreferencesLinks();
            });

            window.setTimeout(function () {
                document.querySelectorAll(rootSelector).forEach(forceFinishPreload);
            }, 1800);

            window.addEventListener('load', function () {
                window.setTimeout(function () {
                    document.querySelectorAll(rootSelector).forEach(forceFinishPreload);
                }, 120);
            }, { once: true });

            if ('MutationObserver' in window) {
                const preloadCleanupObserver = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        mutation.addedNodes.forEach(function (node) {
                            if (!(node instanceof Element)) {
                                return;
                            }

                            const cards = node.matches(rootSelector)
                                ? [node]
                                : Array.from(node.querySelectorAll(rootSelector));

                            cards.forEach(function (card) {
                                window.setTimeout(function () {
                                    forceFinishPreload(card);
                                }, 1800);
                            });
                        });
                    });
                });

                preloadCleanupObserver.observe(document.documentElement, { childList: true, subtree: true });
            }
        })();
    </script>

    <script>
        (function () {
            const applyOgraFiTwoLineSummary = function () {
                document.querySelectorAll('[data-post-card-shell]').forEach(function (card) {
                    const summaryShell = card.querySelector('[data-post-card-summary-shell]');
                    const summary = card.querySelector('[data-post-card-summary]');
                    const fullContent = card.querySelector('[data-post-card-full-content]');
                    const trigger = card.querySelector('[data-post-card-expand]');
                    const label = card.querySelector('[data-post-card-expand-label]');

                    card.classList.add('is-summary-collapsed');
                    card.classList.remove('is-summary-expanded');

                    if (summaryShell) {
                        summaryShell.hidden = false;
                        summaryShell.classList.add('is-collapsed');
                        summaryShell.classList.remove('is-expanded');
                    }

                    if (summary) {
                        summary.classList.add('is-collapsed');
                        summary.classList.remove('is-expanded');
                    }

                    if (fullContent) {
                        fullContent.hidden = true;
                        fullContent.style.display = 'none';
                    }

                    if (trigger) {
                        trigger.hidden = false;
                        trigger.style.display = 'inline-flex';
                        trigger.setAttribute('aria-expanded', 'false');
                    }

                    if (label) {
                        label.textContent = 'Devamını oku';
                    }
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', applyOgraFiTwoLineSummary);
            } else {
                applyOgraFiTwoLineSummary();
            }

            window.addEventListener('load', applyOgraFiTwoLineSummary);
        })();
    </script>


    <script>
        (function () {
            return;

            if (window.__ografiPostBatchRefreshInit) {
                return;
            }

            window.__ografiPostBatchRefreshInit = true;

            const PAGE_SIZE = 25;
            let visibleCount = PAGE_SIZE;
            let nextPageUrl = null;
            let fetchedPageNumber = getCurrentPageNumber();
            let isLoading = false;
            let reachedEnd = false;

            const refreshSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 11a8.1 8.1 0 0 0-15.5-2M4 5v4h4m-4 4a8.1 8.1 0 0 0 15.5 2M20 19v-4h-4"/></svg>';

            function getCurrentPageNumber() {
                const value = Number(new URL(window.location.href).searchParams.get('page') || '1');
                return Number.isFinite(value) && value > 0 ? value : 1;
            }

            const getCards = function () {
                return Array.from(document.querySelectorAll('[data-post-card-shell]'));
            };

            const getFeedParent = function (cards) {
                const firstCard = cards[0];
                if (!firstCard) {
                    return document.querySelector('[data-post-feed], .posts-feed, .post-feed, .feed, main') || document.body;
                }

                return firstCard.parentElement || document.body;
            };

            const findNextPageUrl = function (scope) {
                const root = scope || document;
                const direct = root.querySelector('a[rel="next"], link[rel="next"], nav[role="navigation"] a[aria-label*="Next"], nav[role="navigation"] a[aria-label*="Sonraki"]');
                if (direct?.href) {
                    return direct.href;
                }

                const candidates = Array.from(root.querySelectorAll('a[href]'));
                const next = candidates.find(function (link) {
                    const text = (link.textContent || link.getAttribute('aria-label') || '').trim().toLowerCase();
                    return ['next', 'sonraki', 'daha fazla', '›', '»', '>'].includes(text)
                        || text.includes('sonraki')
                        || text.includes('daha fazla')
                        || text.includes('next');
                });

                return next?.href || null;
            };

            const buildFallbackNextPageUrl = function () {
                const url = new URL(window.location.href);
                const nextNumber = Math.max(fetchedPageNumber + 1, 2);
                url.searchParams.set('page', String(nextNumber));
                return url.toString();
            };

            const createBatchButton = function (type, label, svg) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = type === 'refresh' ? 'ografi-post-batch-refresh' : 'ografi-post-batch-control ografi-post-batch-control--' + type;
                button.setAttribute('aria-label', label);
                button.setAttribute('title', label);
                button.innerHTML = svg;
                return button;
            };

            const setupNewBatchCards = function (cards) {
                cards.forEach(function (card) {
                    setupTextPreload(card);
                    setupMediaPreload(card);
                    setupMediaCarousel(card);
                    forceFinishPreload(card);
                });

                setupNsfwPreferencesLinks();
            };

            const loadNextBatch = async function (button) {
                if (isLoading) {
                    return;
                }

                isLoading = true;
                button?.classList.add('is-loading');
                if (button) {
                    button.disabled = true;
                }

                try {
                    let cardsNow = getCards();
                    const needsFetch = visibleCount >= cardsNow.length && !reachedEnd;

                    if (needsFetch) {
                        nextPageUrl = nextPageUrl || findNextPageUrl(document) || buildFallbackNextPageUrl();
                        await fetchAndAppendNextPage();
                        cardsNow = getCards();
                    }

                    visibleCount = Math.min(visibleCount + PAGE_SIZE, cardsNow.length);
                    renderBatchCards(false);
                } finally {
                    isLoading = false;
                    button?.classList.remove('is-loading');
                    if (button) {
                        button.disabled = false;
                    }
                }
            };

            const createBatchControls = function () {
                const cards = getCards();
                const wrap = document.createElement('div');
                wrap.className = 'ografi-post-batch-refresh-wrap';
                wrap.setAttribute('data-ografi-post-batch-refresh-wrap', 'true');

                const loadMoreButton = createBatchButton('refresh', '25 gonderi daha goster', refreshSvg);
                loadMoreButton.disabled = isLoading || (visibleCount >= cards.length && reachedEnd);

                loadMoreButton.addEventListener('click', async function () {
                    await loadNextBatch(loadMoreButton);
                });

                wrap.append(loadMoreButton);
                return wrap;

            };

            const prepareNewCard = function (card) {
                card.classList.add('is-summary-collapsed');
                card.classList.remove('is-summary-expanded');

                const summaryShell = card.querySelector('[data-post-card-summary-shell]');
                const summary = card.querySelector('[data-post-card-summary]');
                const fullContent = card.querySelector('[data-post-card-full-content]');
                const trigger = card.querySelector('[data-post-card-expand]');
                const label = card.querySelector('[data-post-card-expand-label]');

                if (summaryShell) {
                    summaryShell.hidden = false;
                    summaryShell.classList.add('is-collapsed');
                    summaryShell.classList.remove('is-expanded');
                }

                if (summary) {
                    summary.classList.add('is-collapsed');
                    summary.classList.remove('is-expanded');
                }

                if (fullContent) {
                    fullContent.hidden = true;
                    fullContent.style.display = 'none';
                }

                if (trigger) {
                    trigger.hidden = false;
                    trigger.style.display = 'inline-flex';
                    trigger.setAttribute('aria-expanded', 'false');
                    trigger.setAttribute('aria-hidden', 'false');
                }

                if (label) {
                    label.textContent = 'Devamını oku';
                }
            };

            const fetchAndAppendNextPage = async function () {
                if (!nextPageUrl || reachedEnd) {
                    return;
                }

                const requestUrl = nextPageUrl;
                const response = await fetch(requestUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html, application/xhtml+xml'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    reachedEnd = true;
                    return;
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const currentCards = getCards();
                const currentIds = new Set(currentCards.map(function (card) {
                    return card.id || card.getAttribute('data-post-url') || '';
                }).filter(Boolean));

                const incomingCards = Array.from(doc.querySelectorAll('[data-post-card-shell]')).filter(function (card) {
                    const key = card.id || card.getAttribute('data-post-url') || '';
                    return !key || !currentIds.has(key);
                });

                if (!incomingCards.length) {
                    reachedEnd = true;
                    return;
                }

                const parent = getFeedParent(currentCards);
                const pagination = parent.querySelector('.pagination, nav[role="navigation"]');
                const anchor = pagination || parent.querySelector('[data-ografi-post-batch-refresh-wrap]') || null;

                incomingCards.forEach(function (card) {
                    prepareNewCard(card);
                    if (anchor) {
                        parent.insertBefore(card, anchor);
                    } else {
                        parent.appendChild(card);
                    }
                });
                setupNewBatchCards(incomingCards);

                const pageFromRequest = Number(new URL(requestUrl, window.location.href).searchParams.get('page') || String(fetchedPageNumber + 1));
                if (Number.isFinite(pageFromRequest) && pageFromRequest > fetchedPageNumber) {
                    fetchedPageNumber = pageFromRequest;
                } else {
                    fetchedPageNumber += 1;
                }

                nextPageUrl = findNextPageUrl(doc) || buildFallbackNextPageUrl();
            };

            const renderBatchCards = function (shouldScroll) {
                const cards = getCards();
                document.querySelectorAll('[data-ografi-post-batch-refresh-wrap]').forEach(function (node) {
                    node.remove();
                });

                if (!cards.length) {
                    return;
                }

                if (visibleCount < PAGE_SIZE) {
                    visibleCount = PAGE_SIZE;
                }

                const currentEnd = Math.min(visibleCount, cards.length);

                cards.forEach(function (card, index) {
                    const visible = index < currentEnd;
                    card.hidden = !visible;
                    card.style.display = visible ? '' : 'none';
                    card.setAttribute('data-ografi-batch-index', String(index + 1));
                });

                const shouldShowControls = currentEnd < cards.length || !reachedEnd;

                if (shouldShowControls) {
                    const lastVisibleCard = cards[currentEnd - 1];
                    if (lastVisibleCard?.parentElement) {
                        lastVisibleCard.insertAdjacentElement('afterend', createBatchControls());
                    }
                }

                if (shouldScroll) {
                    const lastVisibleCard = cards[currentEnd - 1];
                    lastVisibleCard?.scrollIntoView({ behavior: 'auto', block: 'end' });
                }
            };

            const initBatchRefresh = function () {
                const cards = getCards();
                if (!cards.length) {
                    return;
                }

                nextPageUrl = nextPageUrl || findNextPageUrl(document) || buildFallbackNextPageUrl();
                visibleCount = Math.max(PAGE_SIZE, Math.min(visibleCount, Math.max(cards.length, PAGE_SIZE)));
                renderBatchCards(false);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initBatchRefresh);
            } else {
                initBatchRefresh();
            }

            window.addEventListener('load', initBatchRefresh);
        })();
    </script>

@endonce

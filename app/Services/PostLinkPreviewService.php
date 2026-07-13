<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RssItem;
use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PostLinkPreviewService
{
    private const CACHE_PREFIX = 'post-link-preview:';

    public function attachToPosts(mixed $posts): void
    {
        $collection = $this->extractCollection($posts);
        if ($collection->isEmpty()) {
            return;
        }

        $rssLinksByPostId = RssItem::query()
            ->whereIn('post_id', $collection->pluck('id')->filter()->values())
            ->whereNotNull('link')
            ->pluck('link', 'post_id');

        $collection->each(function ($post) use ($rssLinksByPostId): void {
            if (!is_object($post)) {
                return;
            }

            $post->link_preview = $this->resolvePreviewForPost($post, (string) ($rssLinksByPostId[$post->id] ?? ''));
        });
    }

    public function attachToPost(object $post): void
    {
        $rssLink = '';
        if (!empty($post->id)) {
            $rssLink = (string) RssItem::query()
                ->where('post_id', $post->id)
                ->whereNotNull('link')
                ->value('link');
        }

        $post->link_preview = $this->resolvePreviewForPost($post, $rssLink);
    }

    public function previewForUrl(string $url, bool $fetchRemote = false): ?array
    {
        $url = $this->normalizeUrl($url);
        if (!$url || !$this->shouldPreviewUrl($url)) {
            return null;
        }

        $fallback = $this->buildFallbackPreview($url);
        $cacheKey = self::CACHE_PREFIX . sha1($url);
        $fetchRemote = $fetchRemote || app()->environment('testing');
        $remote = Cache::has($cacheKey)
            ? (array) Cache::get($cacheKey, [])
            : [];

        if ($remote === [] && $fetchRemote) {
            $remote = Cache::remember(
                $cacheKey,
                now()->addHours(12),
                fn (): array => $this->fetchRemotePreview($url)
            );
        }

        return $this->mergePreview($fallback, $remote);
    }

    private function resolvePreviewForPost(object $post, string $rssLink = ''): ?array
    {
        $preview = $this->previewFromContentJson($post->content_json ?? null);
        if ($preview) {
            return $preview;
        }

        $rssLink = $this->normalizeUrl($rssLink);
        if ($rssLink) {
            return $this->previewForUrl($rssLink);
        }

        $contentUrl = $this->extractUrlFromHtml((string) ($post->content ?? ''));
        if ($contentUrl) {
            return $this->previewForUrl($contentUrl);
        }

        $excerptUrl = $this->extractFirstUrl((string) ($post->excerpt ?? ''));
        if ($excerptUrl) {
            return $this->previewForUrl($excerptUrl);
        }

        return null;
    }

    private function previewFromContentJson(mixed $contentJson): ?array
    {
        if (!is_array($contentJson)) {
            return null;
        }

        foreach (($contentJson['blocks'] ?? []) as $block) {
            if (!is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            if ($type === 'linkTool') {
                $preview = $this->previewFromLinkToolBlock($data);
                if ($preview) {
                    return $preview;
                }

                continue;
            }

            if (in_array($type, ['embed', 'socialEmbed', 'video', 'image', 'gallery', 'carousel', 'slider'], true)) {
                continue;
            }

            $url = $this->extractFirstUrl($this->flattenText($data));
            if ($url) {
                return $this->previewForUrl($url);
            }
        }

        return null;
    }

    private function previewFromLinkToolBlock(array $data): ?array
    {
        $url = $this->normalizeUrl((string) ($data['link'] ?? ''));
        if (!$url || !$this->shouldPreviewUrl($url)) {
            return null;
        }

        $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];
        $preview = $this->mergePreview($this->buildFallbackPreview($url), [
            'title' => $this->cleanText((string) ($meta['title'] ?? '')),
            'description' => $this->cleanText((string) ($meta['description'] ?? '')),
            'site_name' => $this->cleanText((string) ($meta['site_name'] ?? $meta['siteName'] ?? '')),
            'image_url' => $this->normalizeUrl(
                (string) data_get($meta, 'image.url', data_get($meta, 'image', '')),
                $url
            ),
            'icon_url' => $this->normalizeUrl((string) ($meta['icon'] ?? $meta['icon_url'] ?? ''), $url),
        ]);

        if ($this->hasRemoteMeta($preview)) {
            return $preview;
        }

        return $this->previewForUrl($url);
    }

    private function fetchRemotePreview(string $url): array
    {
        try {
            $response = Http::timeout(6)
                ->connectTimeout(3)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; OGrafiLinkPreview/1.0; +https://ografi.test)',
                    'Accept-Language' => 'tr,en;q=0.8',
                ])
                ->get($url);

            if (!$response->successful()) {
                return [];
            }

            $html = (string) $response->body();
            if (trim($html) === '') {
                return [];
            }

            $previousState = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);

            $xpath = new DOMXPath($dom);

            $title = $this->firstNonEmpty([
                $this->queryMetaContent($xpath, "//meta[@property='og:title']/@content"),
                $this->queryMetaContent($xpath, "//meta[@name='twitter:title']/@content"),
                $this->queryNodeText($xpath, '//title'),
            ]);

            $description = $this->firstNonEmpty([
                $this->queryMetaContent($xpath, "//meta[@property='og:description']/@content"),
                $this->queryMetaContent($xpath, "//meta[@name='description']/@content"),
                $this->queryMetaContent($xpath, "//meta[@name='twitter:description']/@content"),
            ]);

            $siteName = $this->firstNonEmpty([
                $this->queryMetaContent($xpath, "//meta[@property='og:site_name']/@content"),
                $this->queryMetaContent($xpath, "//meta[@name='application-name']/@content"),
            ]);

            $imageUrl = $this->firstNonEmpty([
                $this->queryMetaContent($xpath, "//meta[@property='og:image']/@content"),
                $this->queryMetaContent($xpath, "//meta[@property='og:image:url']/@content"),
                $this->queryMetaContent($xpath, "//meta[@name='twitter:image']/@content"),
                $this->queryMetaContent($xpath, "//meta[@itemprop='image']/@content"),
                $this->queryMetaContent($xpath, "//link[@rel='image_src']/@href"),
                $this->queryMetaContent($xpath, '(//img[@src])[1]/@src'),
            ]);

            $iconUrl = $this->firstNonEmpty([
                $this->queryMetaContent($xpath, "//meta[@property='og:logo']/@content"),
                $this->queryMetaContent($xpath, "//meta[@property='og:image:logo']/@content"),
                $this->queryMetaContent($xpath, "//link[contains(@rel,'apple-touch-icon')]/@href"),
                $this->queryMetaContent($xpath, "//link[contains(@rel,'mask-icon')]/@href"),
                $this->queryMetaContent($xpath, "//link[contains(@rel,'shortcut icon')]/@href"),
                $this->queryMetaContent($xpath, "//link[contains(@rel,'icon')]/@href"),
            ]);

            return array_filter([
                'title' => $this->cleanText($title ?? ''),
                'description' => $this->cleanText($description ?? ''),
                'site_name' => $this->cleanText($siteName ?? ''),
                'image_url' => $this->normalizeUrl($imageUrl ?? '', $url),
                'icon_url' => $this->normalizeUrl($iconUrl ?? '', $url),
            ]);
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildFallbackPreview(string $url): array
    {
        $host = (string) parse_url($url, PHP_URL_HOST);
        $host = Str::lower(preg_replace('/^www\./i', '', $host) ?: $host);
        $fallbackIconUrl = $host !== '' ? 'https://www.google.com/s2/favicons?sz=64&domain=' . rawurlencode($host) : null;

        return [
            'url' => $url,
            'host' => $host,
            'site_name' => $host,
            'title' => $host,
            'description' => null,
            'image_url' => null,
            'icon_url' => $fallbackIconUrl,
            'source_label' => 'Kaynak',
        ];
    }

    private function mergePreview(array $base, array $override): array
    {
        return [
            'url' => (string) ($override['url'] ?? $base['url'] ?? ''),
            'host' => (string) ($override['host'] ?? $base['host'] ?? ''),
            'site_name' => (string) ($override['site_name'] ?? $base['site_name'] ?? ''),
            'title' => (string) ($override['title'] ?? $base['title'] ?? ''),
            'description' => $override['description'] ?? $base['description'] ?? null,
            'image_url' => $override['image_url'] ?? $base['image_url'] ?? null,
            'icon_url' => $override['icon_url'] ?? $base['icon_url'] ?? null,
            'source_label' => (string) ($override['source_label'] ?? $base['source_label'] ?? 'Kaynak'),
        ];
    }

    private function hasRemoteMeta(array $preview): bool
    {
        return filled($preview['description'] ?? null)
            || filled($preview['image_url'] ?? null)
            || (
                filled($preview['title'] ?? null)
                && Str::lower((string) ($preview['title'] ?? '')) !== Str::lower((string) ($preview['host'] ?? ''))
            );
    }

    private function shouldPreviewUrl(string $url): bool
    {
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }

        $embeddableHosts = [
            'youtu.be',
            'youtube.com',
            'www.youtube.com',
            'youtube-nocookie.com',
            'www.youtube-nocookie.com',
            'instagram.com',
            'www.instagram.com',
            'tiktok.com',
            'www.tiktok.com',
            'vimeo.com',
            'www.vimeo.com',
            'player.vimeo.com',
            'dailymotion.com',
            'www.dailymotion.com',
            'dai.ly',
            'twitch.tv',
            'www.twitch.tv',
            'clips.twitch.tv',
            'facebook.com',
            'www.facebook.com',
            'fb.watch',
            'x.com',
            'www.x.com',
            'twitter.com',
            'www.twitter.com',
            'vine.co',
        ];

        if (in_array($host, $embeddableHosts, true)) {
            return false;
        }

        return !preg_match('/\.(?:png|jpe?g|gif|webp|svg|mp4|webm|mov|avi|pdf)(?:[?#].*)?$/i', $url);
    }

    private function normalizeUrl(?string $url, ?string $baseUrl = null): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, '//')) {
            $url = 'https:' . $url;
        } elseif ($baseUrl && !preg_match('#^[a-z][a-z0-9+\-.]*://#i', $url)) {
            $base = parse_url($baseUrl);
            if (!is_array($base) || empty($base['scheme']) || empty($base['host'])) {
                return null;
            }

            if (Str::startsWith($url, '/')) {
                $url = $base['scheme'] . '://' . $base['host'] . $url;
            } else {
                $basePath = isset($base['path']) ? preg_replace('#/[^/]*$#', '/', (string) $base['path']) : '/';
                $url = $base['scheme'] . '://' . $base['host'] . ($basePath ?: '/') . ltrim($url, '/');
            }
        } elseif (!preg_match('#^[a-z][a-z0-9+\-.]*://#i', $url)) {
            $url = Str::startsWith($url, 'www.') ? 'https://' . $url : null;
        }

        if (!$url) {
            return null;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = Str::lower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = (string) ($parts['host'] ?? '');
        if ($host === '') {
            return null;
        }

        return $url;
    }

    private function extractUrlFromHtml(string $html): ?string
    {
        if (preg_match('/href=["\']([^"\']+)["\']/i', $html, $matches)) {
            $href = $this->normalizeUrl((string) ($matches[1] ?? ''));
            if ($href && $this->shouldPreviewUrl($href)) {
                return $href;
            }
        }

        return $this->extractFirstUrl(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function extractFirstUrl(string $text): ?string
    {
        $text = html_entity_decode(trim($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($text === '') {
            return null;
        }

        if (!preg_match('/((?:https?:\/\/|www\.)[^\s<>"\'\]\)]+)/iu', $text, $matches)) {
            return null;
        }

        $candidate = rtrim((string) ($matches[1] ?? ''), ".,;:!?)]}");
        $url = $this->normalizeUrl($candidate);

        return ($url && $this->shouldPreviewUrl($url)) ? $url : null;
    }

    private function flattenText(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return '';
        }

        return collect($value)
            ->map(fn ($item) => $this->flattenText($item))
            ->implode(' ');
    }

    private function cleanText(string $value): ?string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);

        return $value !== '' ? $value : null;
    }

    private function queryMetaContent(DOMXPath $xpath, string $expression): ?string
    {
        $node = $xpath->query($expression)?->item(0);

        return $node ? trim((string) $node->nodeValue) : null;
    }

    private function queryNodeText(DOMXPath $xpath, string $expression): ?string
    {
        $node = $xpath->query($expression)?->item(0);

        return $node ? trim((string) $node->textContent) : null;
    }

    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractCollection(mixed $posts): Collection
    {
        if ($posts instanceof LengthAwarePaginator || $posts instanceof Paginator || $posts instanceof PaginatorContract) {
            return $posts->getCollection();
        }

        if ($posts instanceof Collection) {
            return $posts;
        }

        return collect($posts);
    }
}

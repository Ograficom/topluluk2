<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Post;
use App\Models\SitePage;
use App\Models\SitemapSetting;
use App\Models\SantralPageSetting;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    private const CACHE_KEY = 'ografI.sitemap.xml.v4';

    public function xml(): string
    {
        $xml = Cache::rememberForever(
            self::CACHE_KEY,
            fn (): string => $this->buildAndStore(),
        );

        // Remove legacy XSLT instructions from already-cached sitemap XML as well.
        return preg_replace('/<\\?xml-stylesheet\\b[^?]*\\?>\\s*/i', '', $xml) ?? $xml;
    }

    public function rebuild(): array
    {
        Cache::forget(self::CACHE_KEY);
        $xml = $this->xml();

        return [
            'xml' => $xml,
            'url_count' => SitemapSetting::current()->url_count,
            'generated_at' => SitemapSetting::current()->last_generated_at,
        ];
    }

    public function invalidate(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function canonicalImageUrl(string $imageUrl, string $baseUrl): ?string
    {
        $parts = parse_url($imageUrl);
        if (! is_array($parts) || empty($parts['path'])) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if ($scheme !== '' && ! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $imageHost = strtolower((string) ($parts['host'] ?? ''));
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $baseHost = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));

        // Rebase this application's own asset URLs to the canonical sitemap host.
        // Keep externally hosted gallery images on their original public host.
        if ($imageHost === '' || ($imageHost !== '' && in_array($imageHost, array_filter([$appHost, $baseHost]), true))) {
            $url = $baseUrl.'/'.ltrim($parts['path'], '/');
            if (! empty($parts['query'])) {
                $url .= '?'.$parts['query'];
            }

            return $url;
        }

        return filter_var($imageUrl, FILTER_VALIDATE_URL) ? $imageUrl : null;
    }

    private function buildAndStore(): string
    {
        $settings = SitemapSetting::current();
        $baseUrl = rtrim($settings->base_url ?: config('app.url'), '/');
        $included = $settings->include ?? [];
        $urls = [];

        $add = function (string $path, $lastModified = null) use (&$urls, $baseUrl): void {
            $path = '/'.ltrim($path, '/');
            $urls[$baseUrl.$path] = [
                'loc' => $baseUrl.$path,
                'lastmod' => $lastModified?->toAtomString(),
            ];
        };

        if (in_array('pages', $included, true)) {
            $add('/');
            $add('/channels');
            $add('/tags');
            $add('/contacts');
            $add('/santral', SantralPageSetting::query()->find(1)?->updated_at);
            SitePage::query()->published()->orderBy('id')->limit(50000)->get()
                ->each(fn (SitePage $page) => $add('/page/'.rawurlencode($page->slug), $page->updated_at));
        }

        if (in_array('posts', $included, true)) {
            Post::query()->published()
                ->where('is_adult', false)
                ->whereNotNull('slug')
                ->orderByDesc('updated_at')
                ->limit(50000)
                ->get(['title', 'slug', 'excerpt', 'meta_description', 'cover_image', 'media', 'updated_at', 'published_at'])
                ->each(function (Post $post) use (&$urls, $add, $baseUrl): void {
                    $postUrl = $baseUrl.'/posts/'.rawurlencode($post->slug);
                    $add('/posts/'.rawurlencode($post->slug), $post->updated_at ?? $post->published_at);

                    $caption = trim((string) ($post->meta_description ?: $post->excerpt ?: $post->title));
                    $imageUrls = array_filter([
                        $post->coverImageUrl(),
                        ...$post->galleryImageUrls(),
                    ]);

                    $images = collect($imageUrls)
                        ->map(fn (string $imageUrl): ?string => $this->canonicalImageUrl($imageUrl, $baseUrl))
                        ->filter()
                        ->unique()
                        ->take(1000)
                        ->map(fn (string $imageUrl): array => [
                            'loc' => $imageUrl,
                            'title' => (string) $post->title,
                            'caption' => $caption,
                        ])
                        ->values()
                        ->all();

                    if ($images !== []) {
                        $urls[$postUrl]['images'] = $images;
                    }
                });
        }

        if (in_array('channels', $included, true)) {
            Channel::query()->active()
                ->whereNotNull('slug')
                ->orderBy('id')
                ->limit(50000)
                ->get(['slug', 'updated_at'])
                ->each(fn (Channel $channel) => $add('/channels/'.rawurlencode($channel->slug), $channel->updated_at));
        }

        if (in_array('tags', $included, true)) {
            Tag::query()->whereHas('posts', fn ($query) => $query->published()->where('is_adult', false))
                ->whereNotNull('slug')
                ->orderBy('id')
                ->limit(50000)
                ->get(['slug', 'updated_at'])
                ->each(fn (Tag $tag) => $add('/tags/'.rawurlencode($tag->slug), $tag->updated_at));
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

        foreach (array_slice(array_values($urls), 0, 50000) as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";
            if ($url['lastmod']) {
                $xml .= '    <lastmod>'.htmlspecialchars($url['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</lastmod>\n";
            }

            foreach ($url['images'] ?? [] as $image) {
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>'.htmlspecialchars($image['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</image:loc>\n";
                if ($image['title'] !== '') {
                    $xml .= '      <image:title>'.htmlspecialchars($image['title'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</image:title>\n";
                }
                if ($image['caption'] !== '') {
                    $xml .= '      <image:caption>'.htmlspecialchars($image['caption'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</image:caption>\n";
                }
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        $settings->forceFill([
            'last_generated_at' => now(),
            'url_count' => min(count($urls), 50000),
        ])->saveQuietly();

        return $xml;
    }
}

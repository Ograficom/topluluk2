<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Post;
use App\Models\SitePage;
use App\Models\SitemapSetting;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    private const CACHE_KEY = 'ografI.sitemap.xml';

    public function xml(): string
    {
        $cached = Cache::get(self::CACHE_KEY);

        // Eski önbellekte XSL bağlantısı yoksa XML'i yeni biçimde yeniden üret.
        if (is_string($cached) && ! str_contains($cached, '<?xml-stylesheet')) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::rememberForever(self::CACHE_KEY, fn (): string => $this->buildAndStore());
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
            SitePage::query()->published()->orderBy('id')->limit(50000)->get()
                ->each(fn (SitePage $page) => $add('/pages/'.rawurlencode($page->slug), $page->updated_at));
        }

        if (in_array('posts', $included, true)) {
            Post::query()->published()
                ->where('is_adult', false)
                ->whereNotNull('slug')
                ->orderByDesc('updated_at')
                ->limit(50000)
                ->get(['slug', 'updated_at', 'published_at'])
                ->each(fn (Post $post) => $add('/posts/'.rawurlencode($post->slug), $post->updated_at ?? $post->published_at));
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
        $xml .= '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach (array_slice(array_values($urls), 0, 50000) as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";
            if ($url['lastmod']) {
                $xml .= '    <lastmod>'.htmlspecialchars($url['lastmod'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</lastmod>\n";
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

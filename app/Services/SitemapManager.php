<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SitemapManager
{
    public const SETTINGS_KEY = 'sitemap.settings';
    private const CACHE_TTL_MINUTES = 10;

    public function defaults(): array
    {
        return [
            'include_posts' => true,
            'include_categories' => true,
            'include_tags' => true,
            'include_pages' => true,
        ];
    }

    public function getSettings(): array
    {
        $stored = Cache::get(self::SETTINGS_KEY);
        if (!is_array($stored)) {
            $stored = [];
        }

        $merged = array_merge($this->defaults(), $stored);
        if ($merged !== $stored) {
            Cache::forever(self::SETTINGS_KEY, $merged);
        }

        return $merged;
    }

    public function saveSettings(array $settings): void
    {
        $settings = array_merge($this->defaults(), $settings);
        Cache::forever(self::SETTINGS_KEY, $settings);
        Cache::forget($this->entriesCacheKey($settings));
    }

    public function regenerate(): void
    {
        Cache::forget($this->entriesCacheKey($this->getSettings()));
    }

    public function entries(): array
    {
        $settings = $this->getSettings();
        $cacheKey = $this->entriesCacheKey($settings);
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), fn () => $this->buildEntries($settings));
    }

    public function sitemapIndexItems(): array
    {
        $settings = $this->getSettings();
        $items = [];

        $items[] = [
            'loc' => url('/news-sitemap.xml'),
            'lastmod' => Carbon::now()->toAtomString(),
        ];

        if ($settings['include_posts'] ?? false) {
            $items[] = [
                'loc' => url('/posts.xml'),
                'lastmod' => Carbon::now()->toAtomString(),
            ];
        }

        if ($settings['include_categories'] ?? false) {
            $items[] = [
                'loc' => url('/categories.xml'),
                'lastmod' => Carbon::now()->toAtomString(),
            ];
        }

        if ($settings['include_tags'] ?? false) {
            $items[] = [
                'loc' => url('/tags.xml'),
                'lastmod' => Carbon::now()->toAtomString(),
            ];
        }

        if ($settings['include_pages'] ?? false) {
            $items[] = [
                'loc' => url('/pages.xml'),
                'lastmod' => Carbon::now()->toAtomString(),
            ];
        }

        return $items;
    }

    public function postsEntries(): array
    {
        return ($this->getSettings()['include_posts'] ?? false)
            ? $this->cachedEntries('posts', fn () => $this->buildPostItems())
            : [];
    }

    public function newsEntries(): array
    {
        return Post::query()
            ->published()
            ->where('published_at', '>=', now()->subDays(2))
            ->orderByDesc('published_at')
            ->limit(1000)
            ->get(['slug', 'title', 'published_at', 'created_at'])
            ->map(fn (Post $post) => [
                'loc' => route('blog.post', ['post' => $post]),
                'publication_name' => (string) config('seo.news.publication_name', 'Ografi'),
                'language' => (string) config('seo.news.language', 'tr'),
                'publication_date' => ($post->published_at ?: $post->created_at ?: now())->toAtomString(),
                'title' => trim((string) $post->title),
            ])
            ->filter(fn (array $item) => $item['title'] !== '')
            ->values()
            ->all();
    }

    public function categoriesEntries(): array
    {
        return ($this->getSettings()['include_categories'] ?? false)
            ? $this->cachedEntries('categories', fn () => $this->buildCategoryItems())
            : [];
    }

    public function tagsEntries(): array
    {
        return ($this->getSettings()['include_tags'] ?? false)
            ? $this->cachedEntries('tags', fn () => $this->buildTagItems())
            : [];
    }

    public function pagesEntries(): array
    {
        return ($this->getSettings()['include_pages'] ?? false)
            ? $this->cachedEntries('pages', fn () => $this->buildPageItems())
            : [];
    }

    private function buildEntries(array $settings): array
    {
        $items = [];

        if ($settings['include_posts'] ?? false) {
            $items = array_merge($items, $this->buildPostItems());
        }

        if ($settings['include_categories'] ?? false) {
            $items = array_merge($items, $this->buildCategoryItems());
        }

        if ($settings['include_tags'] ?? false) {
            $items = array_merge($items, $this->buildTagItems());
        }

        if ($settings['include_pages'] ?? false) {
            $items = array_merge($items, $this->buildPageItems());
        }

        return $items;
    }

    private function buildPostItems(): array
    {
        return Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at', 'published_at'])
            ->map(function (Post $post) {
                $lastModified = $post->updated_at ?? $post->published_at;
                return [
                    'loc' => route('blog.post', ['post' => $post]),
                    'lastmod' => $lastModified ? $lastModified->toAtomString() : Carbon::now()->toAtomString(),
                    'changefreq' => 'daily',
                    'priority' => '0.9',
                ];
            })
            ->values()
            ->all();
    }

    private function buildCategoryItems(): array
    {
        $items = [];

        $items[] = [
            'loc' => route('blog.categories'),
            'lastmod' => Carbon::now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];

        $items = array_merge($items, Category::query()
            ->orderBy('name')
            ->get(['slug', 'updated_at', 'created_at'])
            ->map(function (Category $category) {
                $lastModified = $category->updated_at ?? $category->created_at ?? Carbon::now();
                return [
                    'loc' => route('blog.category', ['category' => $category->slug]),
                    'lastmod' => $lastModified->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ];
            })
            ->values()
            ->all());

        return $items;
    }

    private function buildTagItems(): array
    {
        $items = [
            [
                'loc' => route('blog.tags'),
                'lastmod' => Carbon::now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ],
        ];

        $items = array_merge($items, Tag::query()
            ->orderBy('name')
            ->get(['slug', 'updated_at', 'created_at'])
            ->map(function (Tag $tag) {
                $lastModified = $tag->updated_at ?? $tag->created_at ?? Carbon::now();
                return [
                    'loc' => route('blog.index', ['tag' => $tag->slug]),
                    'lastmod' => $lastModified->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.5',
                ];
            })
            ->values()
            ->all());

        return $items;
    }

    private function buildPageItems(): array
    {
        return Page::query()
            ->published()
            ->orderBy('title')
            ->get(['slug', 'updated_at', 'published_at'])
            ->map(function (Page $page) {
                $lastModified = $page->updated_at ?? $page->published_at ?? Carbon::now();
                return [
                    'loc' => route('pages.show', ['slug' => $page->slug]),
                    'lastmod' => $lastModified->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.4',
                ];
            })
            ->values()
            ->all();
    }

    private function entriesCacheKey(array $settings): string
    {
        return 'sitemap.entries.' . md5(json_encode($settings));
    }

    private function cachedEntries(string $key, callable $builder): array
    {
        $settings = $this->getSettings();
        $cacheKey = 'sitemap.entries.' . $key . '.' . md5(json_encode($settings));
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), fn () => $builder());
    }
}

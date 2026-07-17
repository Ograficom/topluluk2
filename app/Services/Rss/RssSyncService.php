<?php

namespace App\Services\Rss;

use App\Models\Post;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RssSyncService
{
    public function syncAllEnabled(): array
    {
        $summary = [
            'feeds' => 0,
            'items_new' => 0,
            'items_updated' => 0,
            'posts_created' => 0,
            'posts_updated' => 0,
            'errors' => 0,
        ];

        RssFeed::query()
            ->where('is_enabled', true)
            ->orderBy('id')
            ->each(function (RssFeed $feed) use (&$summary) {
                $result = $this->syncFeed($feed);
                $summary['feeds']++;
                $summary['items_new'] += $result['items_new'];
                $summary['items_updated'] += $result['items_updated'];
                $summary['posts_created'] += $result['posts_created'];
                $summary['posts_updated'] += $result['posts_updated'];
                $summary['errors'] += $result['error'] ? 1 : 0;
            });

        return $summary;
    }

    public function syncFeed(RssFeed $feed, bool $force = false): array
    {
        $result = [
            'items_new' => 0,
            'items_updated' => 0,
            'posts_created' => 0,
            'posts_updated' => 0,
            'error' => null,
        ];

        try {
            $headers = [
                'User-Agent' => 'Grafi RSS Sync (+Laravel)',
                'Accept' => 'application/rss+xml, application/atom+xml, application/xml, text/xml;q=0.9, text/html;q=0.8, */*;q=0.7',
            ];
            $hasPendingAiRewrite = $this->hasPendingAiRewrite($feed);

            if (!$force && !$hasPendingAiRewrite && $feed->etag) {
                $headers['If-None-Match'] = $feed->etag;
            }
            if (!$force && !$hasPendingAiRewrite && $feed->last_modified) {
                $headers['If-Modified-Since'] = $feed->last_modified;
            }

            $response = Http::withoutVerifying()->timeout(12)->withHeaders($headers)->get($feed->url);

            $feed->last_checked_at = now();

            if ($response->status() === 304) {
                $feed->last_error = null;
                $feed->save();
                return $result;
            }

            if (!$response->successful()) {
                throw new \RuntimeException("HTTP {$response->status()}");
            }

            $feed->etag = $response->header('ETag') ?: $feed->etag;
            $feed->last_modified = $response->header('Last-Modified') ?: $feed->last_modified;

            $xml = $response->body();
            $parsedItems = $this->parseXmlItems($xml);
            if ($parsedItems === []) {
                $parsedItems = $this->parseDomItems($xml, $feed->url);
            }

            $parsedItems = array_slice($parsedItems, 0, 10);

            foreach ($parsedItems as $item) {
                $guid = Str::limit((string) ($item['guid'] ?: $item['link'] ?: Str::uuid()), 512, '');
                $title = Str::limit((string) ($item['title'] ?? ''), 500, '');
                $link = $this->safeUrl($item['link'] ?? null);

                if ($this->isLikelyAdvertisementItem($title, $item['tags'] ?? [], $link)) {
                    continue;
                }

                $publishedAt = $this->parsePublishedAt((string) ($item['published_at'] ?? ''));

                $summary = $this->sanitizeHtmlToText($item['summary'] ?? '');
                $rawContent = (string) ($item['content'] ?? $item['summary'] ?? '');
                if ($link && (bool) ($feed->fetch_dom_content ?? true)) {
                    $articleData = $this->fetchArticleData($link);
                    $fetchedContent = (string) ($articleData['content'] ?? '');
                    // The feed's own enclosure/media:thumbnail is the publisher's explicit
                    // featured image for this item, so it outranks anything scraped from the page.
                    $item['media_items'] = $this->normalizeMediaItems(array_merge(
                        $item['media_items'] ?? [],
                        $articleData['media_items'] ?? []
                    ));
                    $item['media_url'] = $this->firstImageUrl($item['media_items'] ?? []) ?? ($item['media_url'] ?? null);
                    $item['tags'] = $this->normalizeTagNames(array_merge(
                        $item['tags'] ?? [],
                        $articleData['tags'] ?? []
                    ));
                    if (
                        $fetchedContent !== ''
                        && (
                            $this->shouldFetchSourceContent($rawContent, (string) ($item['summary'] ?? ''))
                            || mb_strlen($this->sanitizeHtmlToText($fetchedContent)) >= 120
                        )
                    ) {
                        $rawContent = $fetchedContent;
                    }
                }
                $item['tags'] = $this->normalizeTagNames(array_merge($item['tags'] ?? [], $this->extractHashtagsFromText($rawContent)));
                $rawContent = $this->cleanArticleHtmlFragment($rawContent, $title, (string) ($item['summary'] ?? ''));
                $content = $this->sanitizeHtml($this->absolutizeHtmlUrls($rawContent, $link ?: $feed->url));
                $content = $this->appendMediaHtml($content, $item['media_items'] ?? [], $title);

                $hash = hash('sha256', json_encode([
                    'title' => $title,
                    'link' => $link,
                    'published_at' => $publishedAt?->toAtomString(),
                    'summary' => $summary,
                    'content' => $content,
                    'media_url' => $item['media_url'] ?? null,
                    'media_items' => $item['media_items'] ?? [],
                    'tags' => $item['tags'] ?? [],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                $rssItem = RssItem::query()
                    ->where('rss_feed_id', $feed->id)
                    ->where('guid', $guid)
                    ->first();

                if (!$rssItem) {
                    $rssItem = new RssItem([
                        'rss_feed_id' => $feed->id,
                        'guid' => $guid,
                    ]);
                    $result['items_new']++;
                } elseif ($rssItem->hash !== $hash) {
                    $result['items_updated']++;
                }

                $rssItem->fill([
                    'title' => $title ?: $rssItem->title,
                    'link' => $link,
                    'published_at' => $publishedAt,
                    'summary' => $summary ?: null,
                    'content' => $content ?: null,
                    'hash' => $hash,
                ]);
                $rssItem->save();

                if ($feed->import_as_posts) {
                    try {
                        $postChange = $this->importItemAsPost($feed, $rssItem, $item);
                        $result['posts_created'] += $postChange['created'] ? 1 : 0;
                        $result['posts_updated'] += $postChange['updated'] ? 1 : 0;
                    } catch (\Throwable $e) {
                        $result['error'] = $e->getMessage();
                    }
                }
            }

            if ($feed->import_as_posts && $feed->ai_rewrite_enabled) {
                $this->processPendingAiItems($feed, $result);
            }

            $feed->last_success_at = now();
            $feed->last_error = $result['error'];
            $feed->save();
        } catch (\Throwable $e) {
            $feed->last_checked_at = now();
            $feed->last_error = $e->getMessage();
            $feed->save();
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    private function processPendingAiItems(RssFeed $feed, array &$result): void
    {
        $feed->items()
            ->whereNotNull('hash')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->filter(fn (RssItem $item) => blank($item->ai_content)
                || $item->ai_source_hash !== RssArticleRewriteService::expectedSourceHash((string) $item->hash))
            ->take(20)
            ->each(function (RssItem $item) use ($feed, &$result) {
                try {
                    $postChange = $this->importItemAsPost($feed, $item, [
                        'tags' => $this->extractHashtagsFromText($item->content ?: ''),
                        'media_items' => [],
                        'media_url' => null,
                    ]);
                    $result['posts_created'] += $postChange['created'] ? 1 : 0;
                    $result['posts_updated'] += $postChange['updated'] ? 1 : 0;
                } catch (\Throwable $e) {
                    $result['error'] = $e->getMessage();
                }
            });
    }

    private function importItemAsPost(RssFeed $feed, RssItem $item, array $raw): array
    {
        $change = ['created' => false, 'updated' => false];

        $title = $item->title ?: 'Untitled';
        $html = $item->content ?: '';
        $excerpt = $item->summary ?: Str::limit(trim($this->sanitizeHtmlToText($html)), 200);

        if ($feed->ai_rewrite_enabled) {
            $rewritten = app(RssArticleRewriteService::class)->rewrite($item, $feed->ai_model);
            $title = $rewritten['title'];
            $excerpt = $rewritten['summary'];
            $html = $this->appendMediaHtml($rewritten['content'], $raw['media_items'] ?? [], $title);
            $raw['tags'] = $this->normalizeTagNames(array_merge($raw['tags'] ?? [], $rewritten['tags'] ?? []));
        }

        $featured = $this->firstImageUrl($raw['media_items'] ?? []) ?? $this->safeUrl($raw['media_url'] ?? null);
        $tagIds = $this->resolveTagIds($raw['tags'] ?? []);

        if (!$item->post_id) {
            $post = new Post();
            $post->title = $title;
            $post->slug = $this->uniquePostSlug(Str::slug($title) ?: 'rss', $item->id);
            $post->meta_title = $title;
            $post->meta_description = $excerpt ?: null;
            $post->meta_keywords = null;
            $post->excerpt = $excerpt ?: null;
            $post->featured_image = $featured;
            $post->content = $html ?: '<p></p>';
            $post->content_json = null;
            $post->category_id = $feed->default_category_id;
            $post->author_id = $feed->default_author_id;
            $post->is_published = (bool) $feed->auto_publish;
            $post->published_at = $feed->auto_publish ? ($item->published_at ?: now()) : null;
            $post->save();
            if ($tagIds !== []) {
                $post->tags()->syncWithoutDetaching($tagIds);
            }

            $item->post_id = $post->id;
            $item->imported_at = now();
            $item->save();

            $change['created'] = true;
            return $change;
        }

        if (!$feed->update_existing_posts) {
            return $change;
        }

        $post = Post::find($item->post_id);
        if (!$post) {
            $item->post_id = null;
            $item->save();
            return $change;
        }

        $currentFeaturedIsSiteAsset = $this->looksLikeSiteAsset((string) ($post->featured_image ?? ''));
        $shouldUpdate =
            ($post->title ?? '') !== $title ||
            ($post->content ?? '') !== $html ||
            ($post->excerpt ?? '') !== ($excerpt ?: '') ||
            ($featured && ($post->featured_image ?? '') !== $featured) ||
            (!$featured && $currentFeaturedIsSiteAsset);

        if ($tagIds !== []) {
            $post->tags()->syncWithoutDetaching($tagIds);
        }

        if (!$shouldUpdate) {
            return $change;
        }

        $post->title = $title;
        $post->meta_title = $title;
        $post->meta_description = $excerpt ?: null;
        $post->excerpt = $excerpt ?: null;
        $post->content = $html ?: $post->content;
        if ($featured) {
            $post->featured_image = $featured;
        } elseif ($currentFeaturedIsSiteAsset) {
            $post->featured_image = null;
        }
        if ($feed->auto_publish && !$post->is_published) {
            $post->is_published = true;
            $post->published_at = $post->published_at ?: ($item->published_at ?: now());
        }
        $post->save();

        $item->imported_at = $item->imported_at ?: now();
        $item->save();

        $change['updated'] = true;
        return $change;
    }

    private function uniquePostSlug(string $base, int $rssItemId): string
    {
        $slug = Str::limit($base ?: 'rss', 220, '');
        if ($slug === '') {
            $slug = 'rss';
        }

        if (!Post::where('slug', $slug)->exists()) {
            return $slug;
        }

        $withId = Str::limit($slug . '-rss-' . $rssItemId, 255, '');
        if (!Post::where('slug', $withId)->exists()) {
            return $withId;
        }

        return Str::limit($slug . '-' . Str::random(8), 255, '');
    }

    private function hasPendingAiRewrite(RssFeed $feed): bool
    {
        if (!$feed->ai_rewrite_enabled || !$feed->import_as_posts) {
            return false;
        }

        return $feed->items()
            ->whereNotNull('hash')
            ->orderByDesc('published_at')
            ->limit(500)
            ->get(['id', 'hash', 'ai_source_hash', 'ai_content'])
            ->contains(fn (RssItem $item) => blank($item->ai_content)
                || $item->ai_source_hash !== RssArticleRewriteService::expectedSourceHash((string) $item->hash));
    }

    private function parseXmlItems(string $xml): array
    {
        $xml = trim($xml);
        if ($xml === '') {
            return [];
        }

        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$doc) {
            return [];
        }

        $items = [];

        if (isset($doc->channel->item)) {
            foreach ($doc->channel->item as $item) {
                $namespaces = $item->getNameSpaces(true);
                $contentNs = $namespaces['content'] ?? null;
                $mediaNs = $namespaces['media'] ?? null;

                $encoded = $contentNs ? (string) $item->children($contentNs)->encoded : '';
                $mediaItems = [];
                if ($mediaNs) {
                    $mediaChildren = $item->children($mediaNs);
                    foreach ($mediaChildren->content as $mediaContent) {
                        $mediaItems[] = $this->mediaItemFromUrl(
                            $this->simpleXmlAttr($mediaContent, 'url'),
                            $this->simpleXmlAttr($mediaContent, 'type'),
                            $this->simpleXmlAttr($mediaContent, 'medium')
                        );
                    }
                    foreach ($mediaChildren->thumbnail as $mediaThumb) {
                        $mediaItems[] = $this->mediaItemFromUrl($this->simpleXmlAttr($mediaThumb, 'url'), 'image/*', 'image');
                    }
                }
                foreach ($item->enclosure as $enclosure) {
                    $enclosureType = (string) ($enclosure['type'] ?? '');
                    $mediaItems[] = $this->mediaItemFromUrl((string) ($enclosure['url'] ?? ''), $enclosureType, '');
                }
                $mediaItems = $this->normalizeMediaItems($mediaItems);

                $items[] = [
                    'guid' => (string) ($item->guid ?: ''),
                    'title' => (string) ($item->title ?: ''),
                    'link' => (string) ($item->link ?: ''),
                    'published_at' => (string) ($item->pubDate ?: ''),
                    'summary' => (string) ($item->description ?: ''),
                    'content' => $encoded ?: (string) ($item->description ?: ''),
                    'media_url' => $this->firstImageUrl($mediaItems),
                    'media_items' => $mediaItems,
                    'tags' => $this->extractRssTags($item->category ?? []),
                ];
            }

            return $items;
        }

        if ($doc->getName() === 'feed' && isset($doc->entry)) {
            foreach ($doc->entry as $entry) {
                $link = '';
                $mediaItems = [];
                foreach ($entry->link as $l) {
                    $rel = (string) ($l['rel'] ?? '');
                    $type = (string) ($l['type'] ?? '');
                    if ($rel === '' || $rel === 'alternate') {
                        $link = (string) ($l['href'] ?? '');
                    }
                    if ($rel === 'enclosure') {
                        $mediaItems[] = $this->mediaItemFromUrl((string) ($l['href'] ?? ''), $type, '');
                    }
                }
                $namespaces = $entry->getNameSpaces(true);
                $mediaNs = $namespaces['media'] ?? null;
                if ($mediaNs) {
                    $mediaChildren = $entry->children($mediaNs);
                    foreach ($mediaChildren->content as $mediaContent) {
                        $mediaItems[] = $this->mediaItemFromUrl(
                            $this->simpleXmlAttr($mediaContent, 'url'),
                            $this->simpleXmlAttr($mediaContent, 'type'),
                            $this->simpleXmlAttr($mediaContent, 'medium')
                        );
                    }
                    foreach ($mediaChildren->thumbnail as $mediaThumb) {
                        $mediaItems[] = $this->mediaItemFromUrl($this->simpleXmlAttr($mediaThumb, 'url'), 'image/*', 'image');
                    }
                }
                $mediaItems = $this->normalizeMediaItems($mediaItems);

                $content = (string) ($entry->content ?: $entry->summary ?: '');
                $summary = (string) ($entry->summary ?: '');
                $published = (string) ($entry->published ?: $entry->updated ?: '');

                $items[] = [
                    'guid' => (string) ($entry->id ?: $link),
                    'title' => (string) ($entry->title ?: ''),
                    'link' => $link,
                    'published_at' => $published,
                    'summary' => $summary,
                    'content' => $content,
                    'media_url' => $this->firstImageUrl($mediaItems),
                    'media_items' => $mediaItems,
                    'tags' => $this->extractAtomTags($entry->category ?? []),
                ];
            }
        }

        return $items;
    }

    private function simpleXmlAttr(\SimpleXMLElement $node, string $attribute): string
    {
        // ArrayAccess (`$node['attr']`) silently returns an empty value for plain,
        // unprefixed attributes on an element that was reached through a namespaced
        // children() call (e.g. media:content/url) — a long-standing SimpleXML quirk.
        // Reading via attributes() resolves the un-namespaced attribute correctly.
        $value = (string) ($node->attributes()[$attribute] ?? '');
        if ($value !== '') {
            return $value;
        }

        return (string) ($node[$attribute] ?? '');
    }

    private function parsePublishedAt(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDomItems(string $html, string $sourceUrl): array
    {
        $sourceUrl = $this->safeUrl($sourceUrl);
        $html = trim($html);
        if ($html === '' || !$sourceUrl) {
            return [];
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html)) {
            libxml_clear_errors();
            return [];
        }
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $this->promoteLazyMediaAttributes($xpath, $sourceUrl);
        $title = $this->extractDomTitle($xpath);
        $description = $this->extractMetaContent($xpath, [
            ['name', 'description'],
            ['property', 'og:description'],
            ['name', 'twitter:description'],
        ]);
        $tags = $this->extractDomTags($xpath);
        $publishedAt = $this->extractDomPublishedAt($xpath);
        $this->removeNoisyDomNodes($xpath);

        $contentNode = $this->bestDomContentNode($xpath);
        if (!$contentNode) {
            return [];
        }

        $this->stripDomLinks($xpath, $contentNode);
        $tags = $this->normalizeTagNames(array_merge($tags, $this->extractHashtagsFromText($this->innerHtml($contentNode))));
        $this->pruneArticleContentNode($xpath, $contentNode, $title, $description);
        $content = $this->innerHtml($contentNode);
        $text = $this->sanitizeHtmlToText($content);
        
        // Minimum 200 karakter içerik
        if ($text === '' || mb_strlen($text) < 200) {
            return [];
        }

        $title = $title ?: Str::limit($text, 90, '');
        $mediaItems = $this->extractDomMediaItems($xpath, $contentNode, $sourceUrl);

        return [[
            'guid' => $sourceUrl,
            'title' => $title,
            'link' => $sourceUrl,
            'published_at' => $publishedAt,
            'summary' => $description !== '' ? $description : Str::limit($text, 220),
            'content' => $content,
            'media_url' => $this->firstImageUrl($mediaItems),
            'media_items' => $mediaItems,
            'tags' => $tags,
        ]];
    }

    private function bestDomContentNode(\DOMXPath $xpath): ?\DOMNode
    {
        $queries = [
            '//article',
            '//*[@itemprop="articleBody"]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " post-content ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " entry-content ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " article-content ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " article-body ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " story-body ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " news-content ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " content ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " main-content ")]',
            '//main',
            '//*[@role="main"]',
            '//body',
        ];

        $best = null;
        $bestScore = 0;
        foreach ($queries as $query) {
            foreach ($xpath->query($query) ?: [] as $node) {
                if (!$node instanceof \DOMNode) {
                    continue;
                }

                $innerHtml = $this->innerHtml($node);
                $textLength = mb_strlen($this->sanitizeHtmlToText($innerHtml));
                
                // Minimum 200 karakter
                if ($textLength < 200) {
                    continue;
                }

                $paragraphCount = $xpath->query('.//p', $node)?->length ?? 0;
                $headingCount = $xpath->query('.//h1|.//h2|.//h3|.//h4|.//h5|.//h6', $node)?->length ?? 0;
                $mediaCount = $xpath->query('.//img|.//video|.//iframe|.//picture', $node)?->length ?? 0;
                $linkCount = $xpath->query('.//a', $node)?->length ?? 0;
                $linkTextLength = mb_strlen(trim((string) ($xpath->query('.//a', $node)?->item(0)?->textContent ?? '')));
                $navCount = $xpath->query('.//nav|.//*[contains(translate(@class, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "nav")]', $node)?->length ?? 0;
                $listCount = $xpath->query('.//ul|.//ol', $node)?->length ?? 0;
                $tableCount = $xpath->query('.//table', $node)?->length ?? 0;
                $liCount = $xpath->query('.//li', $node)?->length ?? 0;
                
                // Kategori listesi tespiti: çok link ve az paragraf = navigation
                if ($paragraphCount > 0 && ($linkCount / max($paragraphCount, 1)) > 3) {
                    continue;
                }
                
                // Çok fazla list item = navigation/kategori
                if ($liCount > ($paragraphCount * 2)) {
                    continue;
                }
                
                // Minimum 2 paragraf gerekli
                if ($paragraphCount < 2) {
                    continue;
                }
                
                // Score: paragraf ağırlaştırılmış, tablo/liste hafifletilmiş
                $score = $textLength + ($paragraphCount * 300) + ($headingCount * 100) + ($mediaCount * 120) - ($navCount * 2000) - ($listCount * 100) - ($tableCount * 100) - min($linkTextLength, 500) - ($liCount * 20);

                if ($score > $bestScore) {
                    $best = $node;
                    $bestScore = $score;
                }
            }
        }

        return $best;
    }

    private function removeNoisyDomNodes(\DOMXPath $xpath): void
    {
        $queries = [
            '//script|//style|//noscript|//nav|//header|//footer|//form|//aside|//button|//svg|//canvas|//link',
            '//div[@id="skip-to-content"]|//div[@class="skip-nav"]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " advertisement ") or contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " ads ") or contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " sponsored ") or contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " reklam ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " advert ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " banner ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " breadcrumb ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " comment ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " newsletter ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " popular ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " related ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " share ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " sidebar ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " widget ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " menu ") or contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " nav-menu ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " navigation ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " recommended ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " trending ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " bottom-bar ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " skip ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " modal ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " popup ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " notification ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " cookie ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " consent ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " video-ads ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " sticky-header ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " ", normalize-space(@role), " ", normalize-space(@aria-label), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " floating-bar ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " category ") or contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " categories ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " tag-list ") or contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " tag-cloud ")]',
            '//*[contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " archive ") or contains(translate(concat(" ", normalize-space(@class), " ", normalize-space(@id), " "), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), " author ")]',
            '//ul[count(.//li) > 15]|//ol[count(.//li) > 15]',
            '//input|//textarea|//select|//button[@type="button"]|//button[@type="submit"]',
        ];

        foreach ($queries as $query) {
            $nodes = [];
            foreach ($xpath->query($query) ?: [] as $node) {
                if ($node instanceof \DOMNode) {
                    $nodes[] = $node;
                }
            }

            foreach ($nodes as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    private function pruneArticleContentNode(\DOMXPath $xpath, \DOMNode $scope, string $title, string $description): void
    {
        $this->truncateContentAtStopMarker($xpath, $scope);
        $this->removeBoilerplateContentNodes($xpath, $scope, $title, $description);
        $this->removeEmptyContentNodes($xpath, $scope);
    }

    private function truncateContentAtStopMarker(\DOMXPath $xpath, \DOMNode $scope): void
    {
        $nodes = [];
        foreach ($xpath->query('.//*[self::h1 or self::h2 or self::h3 or self::h4 or self::p or self::div or self::section or self::aside or self::ul or self::ol]', $scope) ?: [] as $node) {
            if ($node instanceof \DOMElement) {
                $nodes[] = $node;
            }
        }

        foreach ($nodes as $node) {
            $text = $this->normalizeComparableText($node->textContent);
            if (!$this->isArticleStopText($text)) {
                continue;
            }

            $this->removeNodeAndFollowingSiblings($node);
            return;
        }
    }

    private function removeNodeAndFollowingSiblings(\DOMNode $node): void
    {
        $parent = $node->parentNode;
        if (!$parent) {
            return;
        }

        while ($node) {
            $next = $node->nextSibling;
            $parent->removeChild($node);
            $node = $next;
        }
    }

    private function removeBoilerplateContentNodes(\DOMXPath $xpath, \DOMNode $scope, string $title, string $description): void
    {
        $title = $this->normalizeComparableText($title);
        $description = $this->normalizeComparableText($description);
        $seenTitle = false;
        $nodes = [];

        foreach ($xpath->query('.//*[self::h1 or self::h2 or self::h3 or self::h4 or self::p or self::span or self::div]', $scope) ?: [] as $node) {
            if ($node instanceof \DOMElement) {
                $nodes[] = $node;
            }
        }

        foreach ($nodes as $node) {
            if (!$node->parentNode) {
                continue;
            }

            $text = $this->normalizeComparableText($node->textContent);
            if ($text === '') {
                continue;
            }

            if ($title !== '' && $text === $title) {
                if ($seenTitle || in_array(strtolower($node->nodeName), ['h1', 'h2', 'h3', 'h4', 'p'], true)) {
                    $node->parentNode?->removeChild($node);
                    continue;
                }
                $seenTitle = true;
            }

            if ($description !== '' && $text === $description && mb_strlen($text) < 500) {
                $node->parentNode?->removeChild($node);
                continue;
            }

            if ($this->isBoilerplateText($text)) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    private function removeEmptyContentNodes(\DOMXPath $xpath, \DOMNode $scope): void
    {
        $nodes = [];
        foreach ($xpath->query('.//*[not(self::img) and not(self::video) and not(self::source) and not(self::iframe) and not(normalize-space()) and not(*)]', $scope) ?: [] as $node) {
            if ($node instanceof \DOMNode) {
                $nodes[] = $node;
            }
        }

        foreach ($nodes as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    private function isArticleStopText(string $text): bool
    {
        if ($text === '') {
            return false;
        }

        foreach ([
            'ilgili konular',
            'en cok okunanlar',
            'son haberler',
            'siradaki haber',
            'daha fazla haber',
            'benzer haberler',
            'populer haberler',
            'gundemden basliklar',
            'yorumlar',
        ] as $marker) {
            if ($text === $marker || str_starts_with($text, $marker . ' ') || str_starts_with($text, $marker . ':')) {
                return true;
            }
        }

        return false;
    }

    private function isBoilerplateText(string $text): bool
    {
        if ($text === '') {
            return true;
        }

        if (mb_strlen($text) <= 140 && preg_match('/\b(takip et|paylas|paylaş|abone ol|favorilerine ekle|favori kaynaklarına ekle|reklam|sponsorlu içerik|sponsorlu)\b/u', $text)) {
            return true;
        }

        return str_contains($text, '102 yillik tarihiyle')
            || str_contains($text, '102 yıllık tarihiyle')
            || str_contains($text, 'tikla ve favori kaynaklarina ekle')
            || str_contains($text, 'tıkla ve favori kaynaklarına ekle')
            || str_contains($text, 'google news')
            || str_contains($text, 'whatsapp kanal')
            || str_contains($text, 'telegram kanal')
            || str_contains($text, 'haberi paylaş')
            || str_contains($text, 'haberi paylas');
    }

    private function normalizeComparableText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text, " \t\n\r\0\x0B:-|");
        $text = Str::lower($text);
        $text = str_replace(
            ["\u{0307}", 'ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'â', 'î', 'û'],
            ['', 'i', 'g', 'u', 's', 'o', 'c', 'a', 'i', 'u'],
            $text
        );

        return $text;
    }

    private function extractDomTitle(\DOMXPath $xpath): string
    {
        $metaTitle = $this->extractMetaContent($xpath, [
            ['property', 'og:title'],
            ['name', 'twitter:title'],
        ]);
        if ($metaTitle !== '') {
            return Str::limit($metaTitle, 500, '');
        }

        foreach (['(//h1)[1]', '(//title)[1]'] as $query) {
            $node = $xpath->query($query)?->item(0);
            $title = trim((string) ($node?->textContent ?? ''));
            if ($title !== '') {
                return Str::limit($title, 500, '');
            }
        }

        return '';
    }

    private function extractMetaContent(\DOMXPath $xpath, array $lookups): string
    {
        foreach ($lookups as [$attribute, $value]) {
            $query = sprintf('//meta[translate(@%s, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="%s"]/@content', $attribute, strtolower($value));
            $content = trim((string) ($xpath->query($query)?->item(0)?->nodeValue ?? ''));
            if ($content !== '') {
                return html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        return '';
    }

    private function extractDomPublishedAt(\DOMXPath $xpath): string
    {
        $published = $this->extractMetaContent($xpath, [
            ['property', 'article:published_time'],
            ['name', 'pubdate'],
            ['name', 'publishdate'],
            ['name', 'date'],
        ]);
        if ($published !== '') {
            return $published;
        }

        foreach (['//time[@datetime][1]/@datetime', '//*[@itemprop="datePublished"][1]/@content'] as $query) {
            $value = trim((string) ($xpath->query($query)?->item(0)?->nodeValue ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function extractDomMediaItems(\DOMXPath $xpath, \DOMNode $contentNode, string $baseUrl): array
    {
        $items = [];

        // Publisher-curated featured image/video (og:image, twitter:image) takes priority
        // over any incidental image found inside the article body, so it is listed first.
        foreach ([
            ['property', 'og:image', 'image/*', 'image'],
            ['name', 'twitter:image', 'image/*', 'image'],
            ['property', 'og:video', '', 'video'],
            ['property', 'og:video:url', '', 'video'],
            ['property', 'og:video:secure_url', '', 'video'],
        ] as [$attribute, $value, $type, $medium]) {
            $url = $this->extractMetaContent($xpath, [[$attribute, $value]]);
            if ($url !== '') {
                $items[] = $this->mediaItemFromUrl($this->resolveUrl($url, $baseUrl) ?? '', $type, $medium);
            }
        }

        foreach ($xpath->query('.//img[@src or @data-src or @data-original or @data-lazy-src or @data-srcset or @srcset]', $contentNode) as $image) {
            if (!$image instanceof \DOMElement) {
                continue;
            }

            $imageUrl = $this->bestMediaUrl($image, ['src', 'data-src', 'data-original', 'data-lazy-src', 'data-srcset', 'srcset'], $baseUrl) ?? '';
            if ($this->looksLikeNonArticleImageElement($image, $imageUrl)) {
                continue;
            }

            $items[] = $this->mediaItemFromUrl(
                $imageUrl,
                'image/*',
                'image'
            );
        }

        foreach ($xpath->query('.//picture//source[@src or @data-src or @data-original or @data-lazy-src or @data-srcset or @srcset]', $contentNode) as $source) {
            if (!$source instanceof \DOMElement) {
                continue;
            }

            $items[] = $this->mediaItemFromUrl(
                $this->bestMediaUrl($source, ['src', 'data-src', 'data-original', 'data-lazy-src', 'data-srcset', 'srcset'], $baseUrl) ?? '',
                (string) $source->getAttribute('type'),
                'image'
            );
        }

        foreach ($xpath->query('.//video[@src or @data-src]', $contentNode) as $video) {
            if (!$video instanceof \DOMElement) {
                continue;
            }

            $items[] = $this->mediaItemFromUrl(
                $this->bestMediaUrl($video, ['src', 'data-src'], $baseUrl) ?? '',
                'video/*',
                'video'
            );
        }

        foreach ($xpath->query('.//video/source[@src or @data-src]|.//source[@src or @data-src]', $contentNode) as $source) {
            if (!$source instanceof \DOMElement) {
                continue;
            }

            $items[] = $this->mediaItemFromUrl(
                $this->bestMediaUrl($source, ['src', 'data-src'], $baseUrl) ?? '',
                (string) $source->getAttribute('type'),
                'video'
            );
        }

        foreach ($xpath->query('.//iframe[@src]', $contentNode) as $iframe) {
            $items[] = $this->mediaItemFromUrl($this->resolveUrl((string) $iframe->getAttribute('src'), $baseUrl) ?? '', '', '');
        }

        return $this->normalizeMediaItems($items);
    }

    private function extractDomTags(\DOMXPath $xpath): array
    {
        $tags = [];
        $keywords = $this->extractMetaContent($xpath, [['name', 'keywords']]);
        if ($keywords !== '') {
            $tags = array_merge($tags, preg_split('/[,;]/', $keywords) ?: []);
        }

        foreach ($xpath->query('//meta[translate(@property, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="article:tag"]/@content') as $tag) {
            $tags[] = (string) $tag->nodeValue;
        }

        foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " tag ")]//a|//*[contains(concat(" ", normalize-space(@class), " "), " tags ")]//a') as $tag) {
            $tags[] = (string) $tag->textContent;
        }

        return $this->normalizeTagNames($tags);
    }

    private function extractHashtagsFromText(string $text): array
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (!preg_match_all('/#([\p{L}\p{N}_-]{2,80})/u', $text, $matches)) {
            return [];
        }

        return $this->normalizeTagNames($matches[1] ?? []);
    }

    private function isLikelyAdvertisementItem(?string $title, array $tags, ?string $link): bool
    {
        $haystack = mb_strtolower(trim((string) $title));
        foreach ($tags as $tag) {
            $haystack .= ' ' . mb_strtolower((string) $tag);
        }

        if ($haystack !== '' && preg_match(
            '/\b(reklam|sponsorlu\s*icerik|sponsorlu|advertorial|advertisement|sponsored\s*content|sponsored\s*post|tanitim\s*icerigi|ilanli\s*haber)\b/ui',
            $this->normalizeComparableText($haystack)
        )) {
            return true;
        }

        $link = $this->safeUrl($link);
        if (!$link) {
            return false;
        }

        $host = mb_strtolower((string) parse_url($link, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }

        $adHosts = [
            'doubleclick.net', 'googlesyndication.com', 'googleadservices.com', 'adservice.google.com',
            'taboola.com', 'outbrain.com', 'adnxs.com', 'criteo.com', 'pubmatic.com', 'rubiconproject.com',
            'openx.net', 'yieldmo.com', 'media.net', 'mgid.com', 'revcontent.com', 'zemanta.com',
        ];

        foreach ($adHosts as $adHost) {
            if ($host === $adHost || str_ends_with($host, '.' . $adHost)) {
                return true;
            }
        }

        return false;
    }

    private function safeUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, '//')) {
            $url = 'https:' . $url;
        }

        try {
            $parsed = new \GuzzleHttp\Psr7\Uri($url);
            $scheme = strtolower($parsed->getScheme());
            if (!in_array($scheme, ['http', 'https'], true)) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        return $url;
    }

    private function sanitizeHtmlToText(string $html): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
        return $text;
    }

    private function sanitizeHtml(string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $allowedTags = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'blockquote', 'code', 'pre', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'img', 'figure', 'figcaption', 'hr', 'span', 'video', 'source', 'iframe'];
        $allowed = '<' . implode('><', $allowedTags) . '>';

        $html = strip_tags($html, $allowed);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//*[@*]') as $node) {
            /** @var \DOMElement $node */
            foreach (iterator_to_array($node->attributes) as $attr) {
                $name = strtolower($attr->name);
                if (str_starts_with($name, 'on')) {
                    $node->removeAttributeNode($attr);
                    continue;
                }

                if ($node->tagName === 'img' && $name === 'src') {
                    $safe = $this->safeUrl($attr->value);
                    if (!$safe) {
                        $node->parentNode?->removeChild($node);
                        continue 2;
                    }
                    $node->setAttribute('src', $safe);
                    continue;
                }

                if (in_array($node->tagName, ['video', 'source', 'iframe'], true) && $name === 'src') {
                    $safe = $this->safeUrl($attr->value);
                    if (!$safe) {
                        $node->parentNode?->removeChild($node);
                        continue 2;
                    }
                    if ($node->tagName === 'iframe' && !$this->isAllowedEmbedUrl($safe)) {
                        $node->parentNode?->removeChild($node);
                        continue 2;
                    }
                    $node->setAttribute('src', $safe);
                    if ($node->tagName === 'iframe') {
                        $node->setAttribute('loading', 'lazy');
                        $node->setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
                    }
                    if ($node->tagName === 'video') {
                        $node->setAttribute('controls', 'controls');
                    }
                    continue;
                }

                if ($node->tagName === 'video' && $name === 'poster') {
                    $safe = $this->safeUrl($attr->value);
                    if ($safe) {
                        $node->setAttribute('poster', $safe);
                    } else {
                        $node->removeAttribute('poster');
                    }
                    continue;
                }

                if ($node->tagName === 'video' && $name === 'controls') {
                    $node->setAttribute('controls', 'controls');
                    continue;
                }

                if (in_array($node->tagName, ['img', 'iframe'], true) && in_array($name, ['alt', 'title'], true)) {
                    continue;
                }

                $node->removeAttributeNode($attr);
            }
        }

        foreach ($xpath->query('//script|//style') as $node) {
            $node->parentNode?->removeChild($node);
        }

        return $this->removePlainTextUrlsFromHtml($this->stripXmlEncodingDeclaration($dom->saveHTML() ?: ''));
    }

    private function cleanArticleHtmlFragment(string $html, string $title = '', string $description = ''): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if ($html === strip_tags($html)) {
            $html = $this->truncateRawContentAtStopMarker($html);
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            libxml_clear_errors();
            return $html;
        }
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $this->stripDomLinks($xpath, $dom);
        $this->pruneArticleContentNode($xpath, $dom, $title, $description);

        $cleaned = $this->stripXmlEncodingDeclaration($dom->saveHTML() ?: $html);

        // Safety net: the boilerplate/description-dedup pruning above must never wipe out
        // the entire article body. That happens when a feed's own <description> is reused
        // as both the content and the description (e.g. fetch_dom_content is disabled, so
        // there is no scraped article page) - the only paragraph then matches $description
        // exactly and gets removed as a "duplicate", leaving the post with no text at all.
        // Fall back to the original, un-pruned fragment rather than publishing empty posts.
        if (trim($this->sanitizeHtmlToText($cleaned)) === '' && trim($this->sanitizeHtmlToText($html)) !== '') {
            return $html;
        }

        return $cleaned;
    }

    private function truncateRawContentAtStopMarker(string $html): string
    {
        $pattern = '/(?:^|[\r\n\s])(?:İlgili Konular|Ilgili Konular|En Çok Okunanlar|En Cok Okunanlar|Son Haberler|Sıradaki Haber|Siradaki Haber|Daha Fazla Haber|Benzer Haberler|Popüler Haberler|Populer Haberler|Yorumlar)\s*:?.*$/ius';

        return trim(preg_replace($pattern, '', $html, 1) ?? $html);
    }

    private function stripDomLinks(\DOMXPath $xpath, \DOMNode $scope): void
    {
        $links = [];
        foreach ($xpath->query('.//a', $scope) ?: [] as $node) {
            if ($node instanceof \DOMElement) {
                $links[] = $node;
            }
        }

        foreach ($links as $link) {
            $parent = $link->parentNode;
            if (!$parent) {
                continue;
            }

            $text = trim(preg_replace('/\s+/', ' ', $link->textContent) ?? '');
            $href = $this->safeUrl($link->getAttribute('href'));
            $embedUrl = $href ? $this->socialEmbedUrlFromUrl($href) : null;

            if ($embedUrl) {
                $figure = $link->ownerDocument->createElement('figure');
                $iframe = $link->ownerDocument->createElement('iframe');
                $iframe->setAttribute('src', $embedUrl);
                $iframe->setAttribute('loading', 'lazy');
                $iframe->setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
                $figure->appendChild($iframe);
                $parent->replaceChild($figure, $link);
                continue;
            }

            if ($text === '' || $this->looksLikeUrl($text)) {
                $parent->removeChild($link);
                continue;
            }

            $parent->replaceChild($link->ownerDocument->createTextNode($text), $link);
        }
    }

    private function removePlainTextUrlsFromHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            libxml_clear_errors();
            return $html;
        }
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//text()[not(ancestor::script) and not(ancestor::style)]') ?: [] as $textNode) {
            $value = (string) $textNode->nodeValue;
            $cleaned = preg_replace('~https?://[^\s<>"\']+~i', '', $value) ?? $value;
            $cleaned = preg_replace('~\bwww\.[^\s<>"\']+~i', '', $cleaned) ?? $cleaned;
            if ($cleaned !== $value) {
                $textNode->nodeValue = $cleaned;
            }
        }

        foreach ($xpath->query('//*[not(self::img) and not(self::video) and not(self::source) and not(self::iframe) and not(normalize-space()) and not(*)]') ?: [] as $node) {
            $node->parentNode?->removeChild($node);
        }

        return $this->stripXmlEncodingDeclaration($dom->saveHTML() ?: $html);
    }

    private function looksLikeUrl(string $text): bool
    {
        return (bool) preg_match('~^(?:https?://|www\.)\S+$~i', trim($text));
    }

    private function socialEmbedUrlFromUrl(string $url): ?string
    {
        $url = $this->safeUrl($url);
        if (!$url) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));

        if ($host === 'twitframe.com') {
            return $url;
        }

        if (
            in_array($host, ['x.com', 'www.x.com', 'mobile.x.com', 'twitter.com', 'www.twitter.com', 'mobile.twitter.com'], true)
            || str_ends_with($host, '.x.com')
            || str_ends_with($host, '.twitter.com')
        ) {
            // Check for status/statuses or /video/ pattern (includes videos)
            if (in_array('status', $parts, true) || in_array('statuses', $parts, true) || in_array('video', $parts, true)) {
                return 'https://twitframe.com/show?url=' . rawurlencode($url);
            }
        }

        return null;
    }

    private function isAllowedEmbedUrl(string $url): bool
    {
        $url = $this->safeUrl($url);
        if (!$url) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host === 'twitframe.com'
            || str_ends_with($host, '.youtube.com')
            || in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be'], true)
            || str_ends_with($host, '.vimeo.com')
            || in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true);
    }

    private function shouldFetchSourceContent(string $content, string $summary): bool
    {
        $contentText = $this->sanitizeHtmlToText($content);
        $summaryText = $this->sanitizeHtmlToText($summary);

        if ($contentText === '') {
            return true;
        }

        // Eğer içerik 500 karakterden az ise kaynağı çek
        if (mb_strlen($contentText) < 500) {
            return true;
        }

        // Eğer summary ve content aynı veya çok benzer ise kaynağı çek
        if ($summaryText !== '' && $contentText === $summaryText) {
            return true;
        }

        // Eğer summary ve content'in ilk 300 karakteri aynı ise kaynağı çek
        return $summaryText !== '' && mb_substr($contentText, 0, 300) === mb_substr($summaryText, 0, 300);
    }

    private function fetchArticleData(string $url): array
    {
        $empty = [
            'content' => '',
            'media_items' => [],
            'tags' => [],
        ];

        $url = $this->safeUrl($url);
        if (!$url) {
            return $empty;
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(12)
                ->withHeaders([
                    'User-Agent' => 'Grafi RSS Sync (+Laravel)',
                    'Accept' => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
                ])
                ->get($url);
        } catch (\Throwable) {
            return $empty;
        }

        if (!$response->successful()) {
            return $empty;
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        if ($contentType !== '' && !str_contains($contentType, 'html')) {
            return $empty;
        }

        $html = trim($response->body());
        if ($html === '') {
            return $empty;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html)) {
            libxml_clear_errors();
            return $empty;
        }
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $this->promoteLazyMediaAttributes($xpath, $url);
        $tags = $this->extractDomTags($xpath);
        $this->removeNoisyDomNodes($xpath);

        $node = $this->bestDomContentNode($xpath);
        if (!$node) {
            return [
                'content' => '',
                'media_items' => $this->extractDomMediaItems($xpath, $dom, $url),
                'tags' => $tags,
            ];
        }

        $this->stripDomLinks($xpath, $node);
        $tags = $this->normalizeTagNames(array_merge($tags, $this->extractHashtagsFromText($this->innerHtml($node))));
        $this->pruneArticleContentNode($xpath, $node, '', '');
        $content = $this->innerHtml($node);
        $text = $this->sanitizeHtmlToText($content);
        
        // Minimum 200 karakter içerik
        if (mb_strlen($text) < 200) {
            return [
                'content' => '',
                'media_items' => $this->extractDomMediaItems($xpath, $node, $url),
                'tags' => $tags,
            ];
        }

        return [
            'content' => $content,
            'media_items' => $this->extractDomMediaItems($xpath, $node, $url),
            'tags' => $tags,
        ];
    }

    private function fetchArticleHtml(string $url): string
    {
        return (string) ($this->fetchArticleData($url)['content'] ?? '');
    }

    private function innerHtml(\DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument?->saveHTML($child) ?: '';
        }

        return trim($html);
    }

    private function absolutizeHtmlUrls(string $html, ?string $baseUrl): string
    {
        $html = trim($html);
        $baseUrl = $this->safeUrl($baseUrl);
        if ($html === '' || !$baseUrl) {
            return $html;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if (!$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            libxml_clear_errors();
            return $html;
        }
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $this->promoteLazyMediaAttributes($xpath, $baseUrl);

        $attributes = [
            'img' => ['src'],
            'video' => ['src', 'poster'],
            'source' => ['src'],
            'iframe' => ['src'],
        ];

        foreach ($attributes as $tag => $attributeNames) {
            foreach ($xpath->query('//' . $tag) as $node) {
                if (!$node instanceof \DOMElement) {
                    continue;
                }

                foreach ($attributeNames as $attributeName) {
                    if (!$node->hasAttribute($attributeName)) {
                        continue;
                    }

                    $resolved = $this->resolveUrl($node->getAttribute($attributeName), $baseUrl);
                    if ($resolved) {
                        $node->setAttribute($attributeName, $resolved);
                    }
                }
            }
        }

        return $this->stripXmlEncodingDeclaration($dom->saveHTML() ?: $html);
    }

    private function stripXmlEncodingDeclaration(string $html): string
    {
        return trim(preg_replace('/^<\?xml encoding="utf-8" \?>\s*/i', '', $html) ?? $html);
    }

    private function promoteLazyMediaAttributes(\DOMXPath $xpath, string $baseUrl): void
    {
        foreach ($xpath->query('//img') as $image) {
            if (!$image instanceof \DOMElement || $image->hasAttribute('src')) {
                continue;
            }

            $src = $this->bestMediaUrl($image, ['data-src', 'data-original', 'data-lazy-src', 'data-srcset', 'srcset'], $baseUrl);
            if ($src) {
                $image->setAttribute('src', $src);
            }
        }

        foreach ($xpath->query('//source') as $source) {
            if (!$source instanceof \DOMElement || $source->hasAttribute('src')) {
                continue;
            }

            $src = $this->bestMediaUrl($source, ['data-src', 'data-srcset', 'srcset'], $baseUrl);
            if ($src) {
                $source->setAttribute('src', $src);
            }
        }

        foreach ($xpath->query('//video') as $video) {
            if (!$video instanceof \DOMElement || $video->hasAttribute('src')) {
                continue;
            }

            $src = $this->bestMediaUrl($video, ['data-src'], $baseUrl);
            if ($src) {
                $video->setAttribute('src', $src);
            }
        }
    }

    private function bestMediaUrl(\DOMElement $node, array $attributes, string $baseUrl): ?string
    {
        foreach ($attributes as $attribute) {
            if (!$node->hasAttribute($attribute)) {
                continue;
            }

            $value = trim($node->getAttribute($attribute));
            if ($value === '') {
                continue;
            }

            if (str_contains($attribute, 'srcset')) {
                $value = $this->firstSrcsetUrl($value);
                if (!$value) {
                    continue;
                }
            }

            $resolved = $this->resolveUrl($value, $baseUrl);
            if ($resolved) {
                return $resolved;
            }
        }

        return null;
    }

    private function firstSrcsetUrl(string $srcset): ?string
    {
        $candidates = array_values(array_filter(array_map('trim', explode(',', $srcset))));
        if ($candidates === []) {
            return null;
        }

        $bestUrl = null;
        $bestScore = -1;

        foreach ($candidates as $candidate) {
            $parts = preg_split('/\s+/', $candidate) ?: [];
            $url = trim((string) ($parts[0] ?? ''));
            if ($url === '') {
                continue;
            }

            $descriptor = trim((string) ($parts[1] ?? ''));
            $score = 0;
            if ($descriptor !== '') {
                if (preg_match('/^(\d+(?:\.\d+)?)w$/i', $descriptor, $m)) {
                    $score = (float) $m[1];
                } elseif (preg_match('/^(\d+(?:\.\d+)?)x$/i', $descriptor, $m)) {
                    // Pixel-density descriptors are much smaller numbers than widths;
                    // scale them up so a higher density still outranks a plain, undescribed URL.
                    $score = ((float) $m[1]) * 10000;
                }
            }

            if ($bestUrl === null || $score >= $bestScore) {
                $bestUrl = $url;
                $bestScore = $score;
            }
        }

        return $bestUrl;
    }

    private function resolveUrl(string $url, string $baseUrl): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, '//')) {
            $url = 'https:' . $url;
        }

        try {
            $resolved = \GuzzleHttp\Psr7\UriResolver::resolve(
                new \GuzzleHttp\Psr7\Uri($baseUrl),
                new \GuzzleHttp\Psr7\Uri($url)
            );
        } catch (\Throwable) {
            return null;
        }

        return $this->safeUrl((string) $resolved);
    }

    private function mediaItemFromUrl(string $url, string $type = '', string $medium = ''): ?array
    {
        $url = $this->safeUrl($url);
        if (!$url) {
            return null;
        }

        $socialEmbedUrl = $this->socialEmbedUrlFromUrl($url);
        if ($socialEmbedUrl) {
            $url = $socialEmbedUrl;
        }

        $type = strtolower(trim($type));
        $medium = strtolower(trim($medium));
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        $kind = match (true) {
            str_starts_with($type, 'image/') || $medium === 'image' || preg_match('/\.(avif|gif|jpe?g|png|webp|svg)$/i', $path) => 'image',
            str_starts_with($type, 'video/') || $medium === 'video' || preg_match('/\.(m3u8|mov|mp4|m4v|ogg|ogv|webm)$/i', $path) => 'video',
            $socialEmbedUrl !== null || str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be') || str_contains($url, 'vimeo.com') || str_contains($url, 'twitframe.com') => 'embed',
            default => null,
        };

        if (!$kind) {
            return null;
        }

        return [
            'url' => $url,
            'type' => $type,
            'kind' => $kind,
        ];
    }

    private function normalizeMediaItems(array $items): array
    {
        return collect($items)
            ->filter(fn ($item) => is_array($item) && filled($item['url'] ?? null) && filled($item['kind'] ?? null))
            ->reject(fn ($item) => ($item['kind'] ?? null) === 'image' && $this->looksLikeSiteAsset((string) ($item['url'] ?? '')))
            ->unique(fn ($item) => $item['url'])
            ->values()
            ->all();
    }

    private function firstImageUrl(array $mediaItems): ?string
    {
        $image = collect($mediaItems)->first(fn ($item) => ($item['kind'] ?? null) === 'image' && ! $this->looksLikeSiteAsset((string) ($item['url'] ?? '')));

        return is_array($image) ? $this->safeUrl($image['url'] ?? null) : null;
    }

    private function looksLikeSiteAsset(string $url): bool
    {
        $decodedUrl = mb_strtolower(rawurldecode($url));
        $path = mb_strtolower(rawurldecode((string) parse_url($url, PHP_URL_PATH)));

        $nonArticleTerms = [
            'logo', 'icon', 'favicon', 'avatar', 'profile', 'placeholder', 'default', 'no-image', 'site-logo',
            'banner', 'reklam', 'advert', 'advertisement', 'promo', 'promotion', 'sponsor', 'sponsored',
            'subscribe', 'subscription', 'abonelik', 'e-dergi', 'edergi', 'dergi-abone',
            'google-kaynak', 'google-source', 'preferred-source', 'tercih-edilen-kaynak', 'kaynak-olarak-ekle',
            'badge', 'button', 'sidebar-widget', 'footer-widget',
        ];

        foreach ($nonArticleTerms as $term) {
            if (str_contains($decodedUrl, $term)) {
                return true;
            }
        }

        if (preg_match('/(?:^|[-_\/.])(ads?|adserver|doubleclick)(?:[-_\/.]|$)/i', $decodedUrl)) {
            return true;
        }

        // Common advertising creative sizes embedded in filenames or URL parameters.
        if (preg_match('/(?:^|[^0-9])(728x90|970x90|970x250|468x60|320x50|320x100|300x250|300x600|160x600)(?:[^0-9]|$)/i', $decodedUrl)) {
            return true;
        }

        return (bool) preg_match('/(?:^|[-_\/.])(logo|icon|favicon|avatar|profile|placeholder|default|no-image|site-logo)(?:[-_\/.]|$)/i', $path);
    }

    private function looksLikeNonArticleImageElement(\DOMElement $image, string $url): bool
    {
        if ($this->looksLikeSiteAsset($url)) {
            return true;
        }

        $signals = [];
        foreach (['alt', 'title', 'class', 'id', 'role', 'aria-label'] as $attribute) {
            $signals[] = (string) $image->getAttribute($attribute);
        }

        $ancestor = $image->parentNode;
        for ($level = 0; $level < 3 && $ancestor instanceof \DOMElement; $level++, $ancestor = $ancestor->parentNode) {
            foreach (['class', 'id', 'role', 'aria-label'] as $attribute) {
                $signals[] = (string) $ancestor->getAttribute($attribute);
            }
        }

        $metadata = mb_strtolower(html_entity_decode(implode(' ', $signals), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (preg_match('/\b(reklam|banner|advert(?:isement)?|sponsor(?:ed)?|promo(?:tion)?|abonelik|abone ol|e-?dergi|google.{0,30}(kaynak|source|ekle))\b/ui', $metadata)) {
            return true;
        }

        $width = (int) preg_replace('/[^0-9].*/', '', (string) $image->getAttribute('width'));
        $height = (int) preg_replace('/[^0-9].*/', '', (string) $image->getAttribute('height'));

        return $width >= 300 && $height > 0 && $height <= 250 && ($width / $height) >= 3.2;
    }

    private function appendMediaHtml(string $html, array $mediaItems, string $title = ''): string
    {
        $altText = Str::limit(trim($this->sanitizeHtmlToText($title)), 125, '');
        if ($altText !== '') {
            $html = preg_replace_callback('/<img\b([^>]*)>/iu', static function (array $matches) use ($altText): string {
                $attributes = $matches[1] ?? '';
                if (preg_match('/\balt\s*=\s*(["\'])\s*\1/iu', $attributes)) {
                    $attributes = preg_replace('/\balt\s*=\s*(["\'])\s*\1/iu', 'alt="' . e($altText) . '"', $attributes, 1) ?? $attributes;
                } elseif (!preg_match('/\balt\s*=/iu', $attributes)) {
                    $attributes .= ' alt="' . e($altText) . '"';
                }

                if (!preg_match('/\bloading\s*=/iu', $attributes)) {
                    $attributes .= ' loading="lazy"';
                }

                return '<img' . $attributes . '>';
            }, $html) ?? $html;
        }

        $mediaItems = $this->normalizeMediaItems($mediaItems);
        if ($mediaItems === []) {
            return $html;
        }

        $existing = collect($mediaItems)
            ->filter(fn ($item) => str_contains($html, (string) ($item['url'] ?? '')))
            ->pluck('url')
            ->all();

        $blocks = [];
        foreach ($mediaItems as $item) {
            $url = (string) ($item['url'] ?? '');
            if ($url === '' || in_array($url, $existing, true)) {
                continue;
            }

            if (($item['kind'] ?? '') === 'image') {
                $blocks[] = '<figure><img src="' . e($url) . '" alt="' . e($altText) . '" loading="lazy"></figure>';
                continue;
            }

            if (($item['kind'] ?? '') === 'video') {
                $type = trim((string) ($item['type'] ?? ''));
                $typeAttr = $type !== '' ? ' type="' . e($type) . '"' : '';
                $blocks[] = '<figure><video controls><source src="' . e($url) . '"' . $typeAttr . '></video></figure>';
                continue;
            }

            if (($item['kind'] ?? '') === 'embed') {
                $blocks[] = '<figure><iframe src="' . e($url) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></figure>';
            }
        }

        if ($blocks === []) {
            return $html;
        }

        return trim($html . "\n" . implode("\n", $blocks));
    }

    private function extractRssTags(iterable $categories): array
    {
        $tags = [];
        foreach ($categories as $category) {
            $value = trim((string) $category);
            if ($value !== '') {
                $tags[] = $value;
            }
        }

        return $this->normalizeTagNames($tags);
    }

    private function extractAtomTags(iterable $categories): array
    {
        $tags = [];
        foreach ($categories as $category) {
            $term = trim((string) ($category['term'] ?? ''));
            $label = trim((string) ($category['label'] ?? ''));
            $value = $label !== '' ? $label : $term;
            if ($value !== '') {
                $tags[] = $value;
            }
        }

        return $this->normalizeTagNames($tags);
    }

    private function normalizeTagNames(array $tags): array
    {
        return collect($tags)
            ->map(fn ($tag) => trim(html_entity_decode(strip_tags((string) $tag), ENT_QUOTES | ENT_HTML5, 'UTF-8')))
            ->filter()
            ->map(fn ($tag) => Str::limit($tag, 80, ''))
            ->unique(fn ($tag) => Str::lower($tag))
            ->take(12)
            ->values()
            ->all();
    }

    private function resolveTagIds(array $tags): array
    {
        return collect($this->normalizeTagNames($tags))
            ->map(function (string $name) {
                $slug = Str::slug($name);
                if ($slug === '') {
                    return null;
                }

                return Tag::query()->firstOrCreate(
                    ['slug' => Str::limit($slug, 255, '')],
                    ['name' => $name]
                )->id;
            })
            ->filter()
            ->values()
            ->all();
    }
}

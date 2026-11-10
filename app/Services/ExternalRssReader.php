<?php

namespace App\Services;

use App\Models\RssItem;
use App\Models\Post;
use App\Models\RssSource;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class ExternalRssReader
{
    private const MAX_RESPONSE_BYTES = 2_097_152;

    public function fetchSource(RssSource $source): int
    {
        try {
            $url = $this->validatedFeedUrl($source->url);
            $response = $this->requestFeed($url);

            // NTV may reject direct requests from data-centre IPs with HTTP 403.
            // In that case, use Google News RSS restricted to the same NTV section.
            if ($response->status() === 403 && $this->isNtvFeed($url)) {
                $fallbackUrl = $this->ntvFallbackFeedUrl($url);
                $response = $this->requestFeed($fallbackUrl);
                if ($response->successful()) {
                    $url = $fallbackUrl;
                }
            }

            if (! $response || ! $response->successful()) {
                $status = $response?->status() ?? 0;
                if ($status === 403) {
                    throw new RuntimeException('Kaynak HTTP 403 döndürdü: NTV sunucusu OgraFi sunucusundan gelen otomatik isteği engelliyor. Tarayıcı başlıkları ve güvenli yönlendirme denendi; kaynak tarafındaki IP/bot koruması devam ediyorsa alternatif RSS adresi veya izin listesi gerekir.');
                }

                throw new RuntimeException('Kaynak HTTP '.$status.' yanıtı döndürdü.');
            }

            $body = $response->body();
            if ($body === '' || strlen($body) > self::MAX_RESPONSE_BYTES) {
                throw new RuntimeException('RSS yanıtı boş veya izin verilen 2 MB sınırını aşıyor.');
            }

            $previous = libxml_use_internal_errors(true);
            try {
                $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA | LIBXML_COMPACT);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }

            if (! $xml instanceof SimpleXMLElement) {
                throw new RuntimeException('Kaynak geçerli RSS veya Atom XML belgesi döndürmedi.');
            }

            $entries = $this->entries($xml);
            if ($entries === null) {
                throw new RuntimeException('Desteklenmeyen akış biçimi. RSS 2.0 veya Atom 1.0 gerekli.');
            }

            $saved = 0;
            foreach ($entries as $entry) {
                if ($saved >= 100) break;
                $item = $this->normalizeEntry($entry, $url);
                if ($item === null) {
                    continue;
                }

                $rssItem = RssItem::query()->firstOrNew([
                    'rss_source_id' => $source->id,
                    'guid_hash' => hash('sha256', $item['guid']),
                ]);
                $rssItem->fill($item);
                $rssItem->save();

                if ($rssItem->published_post_id) {
                    $this->syncPublishedPostAssets($rssItem);
                }

                $saved++;
            }

            // Publish a small batch each run. This safely drains the first-import
            // backlog without flooding the site with dozens of posts at once.
            if ($source->auto_publish && $source->author_id && $source->channel_id) {
                RssItem::query()
                    ->where('rss_source_id', $source->id)
                    ->whereNull('published_post_id')
                    ->orderBy('published_at')
                    ->orderBy('id')
                    ->limit(3)
                    ->get()
                    ->each(fn (RssItem $pendingItem) => $this->publishItem($source, $pendingItem));
            }

            $source->forceFill([
                'site_url' => $this->safeWebUrl($this->channelValue($xml, 'link')) ?: $source->site_url,
                'description' => Str::limit($this->plainText($this->channelDescription($xml)), 2000),
                'last_fetched_at' => now(),
                'last_error' => null,
            ])->save();

            return $saved;
        } catch (Throwable $exception) {
            $source->forceFill([
                'last_fetched_at' => now(),
                'last_error' => Str::limit($exception->getMessage(), 2000),
            ])->save();

            throw $exception;
        }
    }

    public function fetchAll(): array
    {
        $result = ['sources' => 0, 'items' => 0, 'errors' => 0];

        RssSource::query()->where('is_active', true)->orderBy('id')->chunkById(50, function ($sources) use (&$result): void {
            foreach ($sources as $source) {
                $result['sources']++;
                try {
                    $result['items'] += $this->fetchSource($source);
                } catch (Throwable) {
                    $result['errors']++;
                }
            }
        });

        return $result;
    }



    private function syncPublishedPostAssets(RssItem $item): void
    {
        $post = Post::query()->find($item->published_post_id);
        if (! $post) return;

        $images = collect($item->image_urls ?? [])
            ->filter(fn ($image) => is_string($image) && $this->safeWebUrl($image))
            ->unique()
            ->take(10)
            ->values();
        $cover = $item->image_url ?: $images->first();
        $gallery = $images->reject(fn ($image) => $image === $cover)->values()->all();
        $tagNames = collect($item->tags ?? [])
            ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
            ->map(fn ($tag) => mb_substr(trim(ltrim($tag, '#')), 0, 100))
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->take(20)
            ->values()
            ->all();

        // Refresh the body of untouched, auto-imported posts when the feed
        // now provides a richer authorized article body. Do not overwrite posts
        // whose EditorJS content has been manually expanded or edited.
        $body = $post->body;
        $decodedExistingBody = is_string($body) ? json_decode($body, true) : null;
        $existingBlocks = is_array($decodedExistingBody) ? ($decodedExistingBody['blocks'] ?? []) : [];
        $existingText = collect($existingBlocks)->map(fn ($block) => trim((string) data_get($block, 'data.text', '')))->filter()->implode("\n\n");
        $newText = trim((string) $item->summary);
        if ($newText !== '' && mb_strlen($existingText) < 1500 && mb_strlen($newText) > mb_strlen($existingText)) {
            $paragraphs = preg_split('/\\n\\s*\\n/u', $newText) ?: [$newText];
            $blocks = [];
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if ($paragraph === '') continue;
                $blocks[] = ['id' => Str::random(10), 'type' => 'paragraph', 'data' => ['text' => $paragraph]];
            }
            if ($blocks !== []) {
                $body = json_encode(['time' => now()->valueOf(), 'blocks' => $blocks, 'version' => '2.30.0'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
        if (is_string($body)) {
            $decodedBody = json_decode($body, true);
            if (is_array($decodedBody) && isset($decodedBody['blocks']) && is_array($decodedBody['blocks'])) {
                $decodedBody['blocks'] = array_values(array_filter(
                    $decodedBody['blocks'],
                    fn ($block) => ! str_starts_with(
                        trim((string) data_get($block, 'data.text', '')),
                        'Bu içerik RSS kaynağından alınmıştır. Kaynak:'
                    )
                ));
                $body = json_encode($decodedBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        $post->forceFill([
            'cover_image' => $cover ?: $post->cover_image,
            'media' => $gallery,
            'tags' => $tagNames,
            'body' => $body,
        ])->save();

        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $base = Str::slug($tagName);
            if ($base === '') continue;
            $tag = Tag::query()->firstOrCreate(['slug' => $base], ['name' => $tagName]);
            $tagIds[] = $tag->id;
        }
        $post->tagRecords()->sync($tagIds);
    }

    private function publishItem(RssSource $source, RssItem $item): void
    {
        if ($item->published_post_id || ! $source->auto_publish || ! $source->author_id || ! $source->channel_id) {
            return;
        }

        $title = Str::limit(trim($item->title), 255, '');
        if ($title === '') return;

        $summary = trim((string) $item->summary);
        $excerpt = Str::limit($summary !== '' ? $summary : $title, 500, '');
        $slugBase = Str::limit(Str::slug($title) ?: 'rss-haberi', 220, '');
        $slug = $slugBase;
        for ($suffix = 1; Post::query()->where('slug', $slug)->exists(); $suffix++) {
            $slug = Str::limit($slugBase, 220 - strlen((string) $suffix), '').'-'.$suffix;
        }

        $blocks = [];
        if ($summary !== '') {
            foreach (preg_split('/\\n\\s*\\n/u', $summary) ?: [$summary] as $paragraph) {
                $paragraph = trim($paragraph);
                if ($paragraph === '') continue;
                $blocks[] = ['id' => Str::random(10), 'type' => 'paragraph', 'data' => ['text' => $paragraph]];
            }
        }

        $tagNames = collect($item->tags ?? [])
            ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
            ->map(fn ($tag) => mb_substr(trim(ltrim($tag, '#')), 0, 100))
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->take(20)
            ->values()
            ->all();
        $imageUrls = collect($item->image_urls ?? [])
            ->filter(fn ($image) => is_string($image) && $this->safeWebUrl($image))
            ->unique()
            ->take(10)
            ->values()
            ->all();
        $coverImage = $item->image_url ?: ($imageUrls[0] ?? null);
        $galleryImages = collect($imageUrls)->reject(fn ($image) => $image === $coverImage)->values()->all();

        $post = Post::query()->create([
            'user_id' => $source->author_id,
            'channel_id' => $source->channel_id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'meta_description' => Str::limit($excerpt, 300, ''),
            'body' => json_encode(['time' => now()->valueOf(), 'blocks' => $blocks, 'version' => '2.30.0'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'cover_image' => $coverImage,
            'media' => $galleryImages,
            'tags' => $tagNames,
            'is_published' => true,
            'published_at' => now(),
            'is_adult' => false,
        ]);

        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $base = Str::slug($tagName);
            if ($base === '') continue;
            $tag = Tag::query()->firstOrCreate(['slug' => $base], ['name' => $tagName]);
            $tagIds[] = $tag->id;
        }
        $post->tagRecords()->sync($tagIds);

        $item->forceFill(['published_post_id' => $post->id])->save();
    }

    private function requestFeed(string $url)
    {
        return $this->requestFeedWithFinalUrl($url)[0];
    }

    /**
     * Follow redirects manually and return the final public URL as well.
     * The final URL is important for Google News RSS links, which redirect
     * to the original publisher's article page.
     */
    private function requestFeedWithFinalUrl(string $url): array
    {
        $url = $this->validatedFeedUrl($url);
        $response = null;

        for ($redirect = 0; $redirect <= 5; $redirect++) {
            $origin = (parse_url($url, PHP_URL_SCHEME) ?: 'https').'://'.parse_url($url, PHP_URL_HOST).'/';

            $response = Http::withOptions([
                'allow_redirects' => false,
                'verify' => true,
            ])->connectTimeout(7)->timeout(20)->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/rss+xml,application/atom+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Referer' => $origin,
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            ])->get($url);

            if (! $response->redirect()) {
                return [$response, $url];
            }

            if ($redirect === 5) {
                throw new RuntimeException('Kaynak çok fazla yönlendirme yaptı.');
            }

            $location = trim((string) $response->header('Location'));
            $nextUrl = $location !== '' ? $this->resolveUrl($location, $url) : null;
            if ($nextUrl === null) {
                throw new RuntimeException('Kaynak geçersiz bir yönlendirme adresi döndürdü.');
            }

            $url = $this->validatedFeedUrl($nextUrl);
        }

        return [$response, $url];
    }

    private function isNtvFeed(string $url): bool
    {
        return strtolower((string) parse_url($url, PHP_URL_HOST)) === 'www.ntv.com.tr'
            && str_ends_with(strtolower((string) parse_url($url, PHP_URL_PATH)), '.rss');
    }

    private function ntvFallbackFeedUrl(string $url): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $section = preg_replace('/\\.rss$/i', '', basename($path)) ?: 'son-dakika';
        $query = rawurlencode('site:ntv.com.tr/'.$section);

        return 'https://news.google.com/rss/search?q='.$query.'&hl=tr&gl=TR&ceid=TR%3Atr';
    }

    private function validatedFeedUrl(string $url): string
    {
        $parts = parse_url(trim($url));
        if (! is_array($parts) || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
            throw new RuntimeException('Yalnızca kullanıcı bilgisi içermeyen HTTP veya HTTPS RSS adresleri kabul edilir.');
        }

        $host = strtolower(rtrim($parts['host'], '.'));
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')
            || filter_var($host, FILTER_VALIDATE_IP) && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new RuntimeException('Yerel ağ veya özel IP adreslerine RSS isteği gönderilemez.');
        }

        $port = (int) ($parts['port'] ?? (strtolower($parts['scheme']) === 'https' ? 443 : 80));
        if (! in_array($port, [80, 443], true)) {
            throw new RuntimeException('RSS adresinde yalnızca 80 veya 443 portları kullanılabilir.');
        }

        if (! filter_var($host, FILTER_VALIDATE_IP)) {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
            if ($records === []) {
                throw new RuntimeException('RSS alan adı çözümlenemedi.');
            }
            foreach ($records as $record) {
                $ip = $record['ip'] ?? $record['ipv6'] ?? null;
                if ($ip && ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    throw new RuntimeException('RSS alan adı özel veya yerel bir IP adresine çözülüyor.');
                }
            }
        }

        return $url;
    }

    private function entries(SimpleXMLElement $xml): ?iterable
    {
        if ($xml->getName() === 'rss' && isset($xml->channel)) {
            return $xml->channel->item ?? [];
        }

        if ($xml->getName() === 'feed') {
            $atom = $xml->children('http://www.w3.org/2005/Atom');
            return $atom->entry ?? [];
        }

        if ($xml->getName() === 'RDF') {
            $rdf = $xml->children('http://purl.org/rss/1.0/');
            return $rdf->item ?? [];
        }

        return null;
    }

    private function normalizeEntry(SimpleXMLElement $entry, string $feedUrl): ?array
    {
        $atom = $entry->children('http://www.w3.org/2005/Atom');
        $title = trim((string) ($entry->title ?? $atom->title ?? ''));
        $link = trim((string) ($entry->link ?? ''));
        $guid = trim((string) ($entry->guid ?? $entry->id ?? $atom->id ?? $link));

        if ($link === '' && isset($atom->link)) {
            foreach ($atom->link as $atomLink) {
                $attributes = $atomLink->attributes();
                if (! isset($attributes['rel']) || (string) $attributes['rel'] === 'alternate') {
                    $link = (string) ($attributes['href'] ?? '');
                    if ($link !== '') break;
                }
            }
        }

        $link = $this->resolveUrl($link ?: $guid, $feedUrl);
        if ($title === '' || $link === null) {
            return null;
        }

        if ($guid === '') {
            $guid = $link;
        }

        // Prefer the richest content explicitly supplied by the feed. Publishers often
        // put a teaser in <description> and fuller feed text in content:encoded or
        // Atom <content>; choosing description first silently discards that content.
        $contentNamespace = $entry->children('http://purl.org/rss/1.0/modules/content/');
        $contentCandidates = [
            (string) ($contentNamespace->encoded ?? ''),
            (string) ($atom->content ?? ''),
            (string) ($entry->content ?? ''),
            (string) ($atom->summary ?? ''),
            (string) ($entry->description ?? ''),
            (string) ($entry->summary ?? ''),
        ];
        $content = '';
        $bestContentLength = 0;
        foreach ($contentCandidates as $candidate) {
            $candidateLength = mb_strlen($this->readableContent($candidate));
            if ($candidateLength > $bestContentLength) {
                $content = $candidate;
                $bestContentLength = $candidateLength;
            }
        }

        $author = trim((string) ($entry->author ?? ''));
        if ($author === '' && isset($atom->author->name)) {
            $author = trim((string) $atom->author->name);
        }

        // Read all standard RSS/Media RSS image locations, including inline HTML images.
        $images = [];
        $media = $entry->children('http://search.yahoo.com/mrss/');
        foreach ($media->thumbnail ?? [] as $thumbnail) {
            $images[] = (string) ($thumbnail->attributes()->url ?? '');
        }
        foreach ($media->content ?? [] as $mediaContent) {
            $attributes = $mediaContent->attributes();
            $type = strtolower((string) ($attributes['type'] ?? ''));
            $medium = strtolower((string) ($attributes['medium'] ?? ''));
            if ($medium === 'image' || str_starts_with($type, 'image/')) {
                $images[] = (string) ($attributes['url'] ?? '');
            }
        }
        foreach ($entry->enclosure ?? [] as $enclosureNode) {
            $attributes = $enclosureNode->attributes();
            if (str_starts_with(strtolower((string) ($attributes['type'] ?? '')), 'image/')) {
                $images[] = (string) ($attributes['url'] ?? '');
            }
        }
        if (preg_match_all('~<img\\b[^>]*\\bsrc=[\\\"\\\']([^\\\"\\\']+)[\\\"\\\']~i', $content, $matches)) {
            $images = array_merge($images, $matches[1]);
        }
        $imageUrls = collect($images)
            ->map(fn ($image) => $this->resolveUrl(trim((string) $image), $feedUrl))
            ->filter()
            ->unique()
            ->take(10)
            ->values()
            ->all();
        $image = $imageUrls[0] ?? '';

        // RSS <category>, Atom category/@term and Dublin Core subject tags.
        $tags = [];
        foreach ($entry->category ?? [] as $category) {
            $tags[] = trim((string) $category);
        }
        $atomEntry = $entry->children('http://www.w3.org/2005/Atom');
        foreach ($atomEntry->category ?? [] as $category) {
            $attributes = $category->attributes();
            $tags[] = trim((string) ($attributes['term'] ?? $attributes['label'] ?? $category));
        }
        $dc = $entry->children('http://purl.org/dc/elements/1.1/');
        foreach ($dc->subject ?? [] as $subject) {
            $tags[] = trim((string) $subject);
        }
        $tags = collect($tags)
            ->filter()
            ->map(fn ($tag) => mb_substr(ltrim($tag, '#'), 0, 100))
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->take(20)
            ->values()
            ->all();

        // Many publishers expose only a headline in RSS. Enrich missing image,
        // description and tags from the publicly accessible article metadata.
        // Do not copy the full copyrighted article body into OgraFi.
        $articleHost = strtolower((string) parse_url($link, PHP_URL_HOST));
        $isCumhuriyetArticle = $articleHost === 'cumhuriyet.com.tr' || str_ends_with($articleHost, '.cumhuriyet.com.tr');

        // Cumhuriyet's public RSS supplies summaries, not the complete article.
        // When the publisher has authorized full-text republication, attempt to
        // extract the article body from the linked page as well as its metadata.
        if ($isCumhuriyetArticle || $imageUrls === [] || $tags === [] || $this->plainText($content) === $this->plainText($title) || str_contains($articleHost, 'news.google.com')) {
            $articleMetadata = $this->fetchArticleMetadata($link);
            if ($articleMetadata !== []) {
                if (! empty($articleMetadata['url']) && ! str_contains((string) parse_url($link, PHP_URL_HOST), 'news.google.com')) {
                    $link = $articleMetadata['url'];
                } elseif (! empty($articleMetadata['url']) && str_contains((string) parse_url($link, PHP_URL_HOST), 'news.google.com')) {
                    $link = $articleMetadata['url'];
                }
                $articleImages = $articleMetadata['images'] ?? [];
                $imageUrls = array_slice(array_values(array_unique(array_merge($articleImages, $imageUrls))), 0, 10);
                $image = $articleImages[0] ?? ($image ?: ($imageUrls[0] ?? ''));
                $tags = array_values(array_unique(array_slice(array_merge($tags, $articleMetadata['tags'] ?? []), 0, 20)));
                if ($isCumhuriyetArticle && ! empty($articleMetadata['article_body'])) {
                    $content = $articleMetadata['article_body'];
                } elseif ($this->plainText($content) === '' || $this->plainText($content) === $this->plainText($title)) {
                    $content = $articleMetadata['description'] ?? $content;
                }
            }
        }

        $date = trim((string) ($entry->pubDate ?? $entry->published ?? $entry->updated ?? $atom->published ?? $atom->updated ?? ''));
        try {
            $publishedAt = $date !== '' ? Carbon::parse($date) : null;
        } catch (Throwable) {
            $publishedAt = null;
        }

        return [
            'guid' => Str::limit($guid, 2000, ''),
            'title' => Str::limit($title, 500, ''),
            'link' => $link,
            'summary' => Str::limit($this->readableContent($content), 50000, ''),
            'author' => $author !== '' ? Str::limit($author, 255, '') : null,
            'image_url' => $this->safeWebUrl($image),
            'image_urls' => $imageUrls,
            'tags' => $tags,
            'published_at' => $publishedAt,
        ];
    }

    private function fetchArticleMetadata(string $articleUrl): array
    {
        try {
            [$response, $finalUrl] = $this->requestFeedWithFinalUrl($articleUrl);
            if (! $response || ! $response->successful()) return [];

            $html = $response->body();
            if ($html === '' || strlen($html) > 5_000_000) return [];

            $previous = libxml_use_internal_errors(true);
            try {
                $document = new \DOMDocument();
                $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);
                $xpath = new \DOMXPath($document);
                $meta = function (string $property) use ($xpath): string {
                    $nodes = $xpath->query('//meta[@property="'.$property.'" or @name="'.$property.'"]/@content');
                    return $nodes && $nodes->length ? trim((string) $nodes->item(0)->nodeValue) : '';
                };

                $canonicalNodes = $xpath->query('//link[@rel="canonical"]/@href');
                $canonicalUrl = $canonicalNodes && $canonicalNodes->length
                    ? $this->resolveUrl(trim((string) $canonicalNodes->item(0)->nodeValue), $finalUrl)
                    : null;
                $publisherUrl = $canonicalUrl ?: ($this->resolveUrl($meta('og:url'), $finalUrl) ?: $finalUrl);
                $finalHost = strtolower((string) parse_url($finalUrl, PHP_URL_HOST));
                $isGoogleNewsWrapper = $finalHost === 'news.google.com' || str_ends_with($finalHost, '.news.google.com');

                $images = [];
                // Never use Google's wrapper/logo as the article's cover image.
                if (! $isGoogleNewsWrapper) {
                    foreach (['og:image', 'og:image:secure_url', 'twitter:image', 'twitter:image:src'] as $key) {
                        $value = $meta($key);
                        if ($value !== '') {
                            $resolved = $this->resolveUrl($value, $finalUrl);
                            if ($resolved) $images[] = $resolved;
                        }
                    }
                }

                // Collect actual article-body images as a gallery too, not only the
                // publisher's single og:image. Prefer lazy-loaded full-size URLs.
                $imageNodes = $xpath->query('//article//img | //main//img | //*[@itemprop="articleBody"]//img');
                if ($imageNodes) {
                    foreach ($imageNodes as $imageNode) {
                        if (! $imageNode instanceof \DOMElement) continue;
                        $candidate = '';
                        foreach (['data-src', 'data-original', 'data-lazy-src', 'data-url', 'src'] as $attribute) {
                            $candidate = trim($imageNode->getAttribute($attribute));
                            if ($candidate !== '') break;
                        }
                        if ($candidate === '' || str_starts_with($candidate, 'data:')) continue;
                        $resolved = $this->resolveUrl($candidate, $finalUrl);
                        if (! $resolved) continue;
                        $path = strtolower((string) parse_url($resolved, PHP_URL_PATH));
                        if (preg_match('/\\.(svg|gif)$/i', $path) || preg_match('/logo|icon|avatar|placeholder|sprite|tracking|pixel/i', $path)) continue;
                        $images[] = $resolved;
                    }
                }

                $tags = [];
                foreach (['keywords', 'news_keywords', 'article:tag', 'article:section'] as $key) {
                    $value = $meta($key);
                    if ($value !== '') {
                        foreach (preg_split('/[,;|]/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $tag) {
                            $tags[] = trim($tag);
                        }
                    }
                }

                $description = $meta('og:description') ?: $meta('description') ?: $meta('twitter:description');

                // Collect every available full-body candidate. Some publishers expose
                // only the lead paragraph in JSON-LD/itemprop while the remaining
                // paragraphs live in a separate article content container.
                $articleBody = '';
                $jsonLdNodes = $xpath->query('//script[@type="application/ld+json"]');
                if ($jsonLdNodes) {
                    foreach ($jsonLdNodes as $node) {
                        $data = json_decode((string) $node->textContent, true);
                        $structuredItems = is_array($data) && array_is_list($data) ? $data : [$data];
                        foreach ($structuredItems as $structured) {
                            if (! is_array($structured)) continue;
                            $candidates = [$structured];
                            if (isset($structured['@graph']) && is_array($structured['@graph'])) {
                                $candidates = array_merge($candidates, $structured['@graph']);
                            }
                            foreach ($candidates as $candidate) {
                                if (! is_array($candidate)) continue;
                                $bodyCandidate = $candidate['articleBody'] ?? null;
                                if (is_string($bodyCandidate)) {
                                    $bodyText = $this->readableContent($bodyCandidate);
                                    if (mb_strlen($bodyText) > mb_strlen($articleBody)) {
                                        $articleBody = $bodyText;
                                    }
                                }
                            }
                        }
                    }
                }

                // Check semantic containers independently and keep the longest
                // clean result instead of stopping at the first, often abbreviated,
                // articleBody element.
                $bodyQueries = [
                    '//*[@itemprop="articleBody"]',
                    '//article[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"article-body") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"article-content") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"news-content") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"detail-content")]',
                    '//main//article',
                    '//article',
                    '//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"article-detail") or contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"news-detail")]',
                ];

                foreach ($bodyQueries as $bodyQuery) {
                    $bodyNodes = $xpath->query($bodyQuery);
                    if (! $bodyNodes || $bodyNodes->length === 0) continue;

                    foreach ($bodyNodes as $bodyNode) {
                        if (! $bodyNode instanceof \DOMElement) continue;
                        $paragraphNodes = $xpath->query('.//h1 | .//h2 | .//h3 | .//p', $bodyNode);
                        if (! $paragraphNodes) continue;

                        $parts = [];
                        foreach ($paragraphNodes as $paragraphNode) {
                            $text = trim(preg_replace('/\\s+/u', ' ', $paragraphNode->textContent) ?? '');
                            if ($text === '' || mb_strlen($text) < 35) continue;

                            $class = strtolower($paragraphNode->getAttribute('class'));
                            $parentClass = strtolower($paragraphNode->parentNode instanceof \DOMElement ? $paragraphNode->parentNode->getAttribute('class') : '');
                            if (preg_match('/advert|related|recommend|social|share|comment|newsletter|breadcrumb|author-box|tag-list/', $class.' '.$parentClass)) continue;

                            $parts[] = $text;
                        }

                        $candidateBody = implode("\\n\\n", array_values(array_unique($parts)));
                        if (mb_strlen($candidateBody) > mb_strlen($articleBody)) {
                            $articleBody = $candidateBody;
                        }
                    }
                }

                $articleBody = Str::limit($articleBody, 100000, '');

                // Structured article metadata often contains better keywords and a concise description.
                $jsonLdNodes = $xpath->query('//script[@type="application/ld+json"]');
                if ($jsonLdNodes) {
                    foreach ($jsonLdNodes as $node) {
                        $data = json_decode((string) $node->textContent, true);
                        foreach (is_array($data) && array_is_list($data) ? $data : [$data] as $structured) {
                            if (! is_array($structured)) continue;
                            if (isset($structured['@graph']) && is_array($structured['@graph'])) {
                                foreach ($structured['@graph'] as $graphItem) {
                                    if (! is_array($graphItem)) continue;
                                    $tags = array_merge($tags, (array) ($graphItem['keywords'] ?? []));
                                    $description = $description ?: (string) ($graphItem['description'] ?? '');
                                }
                            }
                            $tags = array_merge($tags, (array) ($structured['keywords'] ?? []), (array) ($structured['articleSection'] ?? []));
                            $description = $description ?: (string) ($structured['description'] ?? '');
                        }
                    }
                }

                $tags = collect($tags)
                    ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
                    ->map(fn ($tag) => mb_substr(trim(ltrim($tag, '#')), 0, 100))
                    ->unique(fn ($tag) => mb_strtolower($tag))
                    ->take(20)
                    ->values()
                    ->all();

                return [
                    'images' => array_slice(array_values(array_unique($images)), 0, 10),
                    'tags' => $tags,
                    'description' => Str::limit($this->plainText($description), 12000, ''),
                    'article_body' => Str::limit($articleBody, 50000, ''),
                    'url' => $publisherUrl,
                ];
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
        } catch (Throwable) {
            // Metadata enrichment is optional; a publisher blocking page requests
            // must not prevent the RSS headline itself from being imported.
            return [];
        }
    }

    private function channelValue(SimpleXMLElement $xml, string $key): string
    {
        if ($xml->getName() === 'rss') return trim((string) ($xml->channel->{$key} ?? ''));
        return '';
    }

    private function channelDescription(SimpleXMLElement $xml): string
    {
        if ($xml->getName() === 'rss') return (string) ($xml->channel->description ?? '');
        $atom = $xml->children('http://www.w3.org/2005/Atom');
        return (string) ($atom->subtitle ?? '');
    }

    /**
     * Convert HTML supplied inside an RSS/Atom item to readable text and keep
     * paragraph boundaries. The publisher's webpage is not scraped for its body.
     */
    private function readableContent(string $value): string
    {
        if ($value === '') return '';

        $value = preg_replace('~<br\\s*/?>|</(p|div|li|h[1-6]|blockquote|tr)>~i', "$0\n", $value) ?? $value;
        $value = preg_replace('~<(p|div|li|h[1-6]|blockquote|tr)(\\s[^>]*)?>~i', "\n", $value) ?? $value;
        $decoded = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = str_replace(["\\r\n", "\\r"], "\n", $decoded);
        $lines = preg_split('/\n+/u', $decoded) ?: [];
        $lines = array_values(array_filter(array_map(
            fn ($line) => trim(preg_replace('/[\\t ]+/u', ' ', $line) ?? ''),
            $lines
        ), fn ($line) => $line !== ''));

        return trim(implode("\n\n", $lines));
    }

    private function plainText(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', strip_tags($decoded)) ?? '');
    }

    private function safeWebUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) return null;
        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) ? $url : null;
    }

    private function resolveUrl(string $url, string $base): ?string
    {
        if ($url === '') return null;
        if (str_starts_with($url, '//')) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';
            $url = $scheme.':'.$url;
        } elseif (! preg_match('~^https?://~i', $url)) {
            $baseParts = parse_url($base);
            if (! is_array($baseParts) || empty($baseParts['host'])) return null;
            $origin = ($baseParts['scheme'] ?? 'https').'://'.$baseParts['host'].(isset($baseParts['port']) ? ':'.$baseParts['port'] : '');
            $url = str_starts_with($url, '/') ? $origin.$url : rtrim(dirname($baseParts['path'] ?? '/'), '/').'/'.$url;
            if (! str_starts_with($url, 'http')) $url = $origin.$url;
        }

        return $this->safeWebUrl($url);
    }
}

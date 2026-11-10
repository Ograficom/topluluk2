<?php

namespace App\Services;

use App\Models\RssItem;
use App\Models\Post;
use App\Models\RssSource;
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
                $isNewItem = ! $rssItem->exists;
                $rssItem->fill($item);
                $rssItem->save();

                if ($isNewItem && $source->auto_publish && $source->author_id && $source->channel_id) {
                    $this->publishItem($source, $rssItem);
                }

                $saved++;
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
            $blocks[] = ['id' => Str::random(10), 'type' => 'paragraph', 'data' => ['text' => $summary]];
        }
        $blocks[] = [
            'id' => Str::random(10),
            'type' => 'paragraph',
            'data' => ['text' => 'Bu içerik RSS kaynağından alınmıştır. Kaynak: '.$item->link],
        ];

        $post = Post::query()->create([
            'user_id' => $source->author_id,
            'channel_id' => $source->channel_id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'meta_description' => Str::limit($excerpt, 300, ''),
            'body' => json_encode(['time' => now()->valueOf(), 'blocks' => $blocks, 'version' => '2.30.0'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'cover_image' => $item->image_url,
            'media' => [],
            'tags' => [],
            'is_published' => true,
            'published_at' => now(),
            'is_adult' => false,
        ]);

        $item->forceFill(['published_post_id' => $post->id])->save();
    }

    private function requestFeed(string $url)
    {
        $url = $this->validatedFeedUrl($url);
        $response = null;

        // Follow redirects manually; every destination is checked against SSRF rules.
        for ($redirect = 0; $redirect <= 3; $redirect++) {
            $origin = (parse_url($url, PHP_URL_SCHEME) ?: 'https').'://'.parse_url($url, PHP_URL_HOST).'/';

            $response = Http::withOptions([
                'allow_redirects' => false,
                'verify' => true,
            ])->connectTimeout(7)->timeout(20)->withHeaders([
                'Accept' => 'application/rss+xml, application/atom+xml, application/xml, text/xml, application/*+xml;q=0.9, */*;q=0.5',
                'Accept-Language' => 'tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Referer' => $origin,
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            ])->get($url);

            if (! $response->redirect()) {
                return $response;
            }

            if ($redirect === 3) {
                throw new RuntimeException('RSS kaynağı çok fazla yönlendirme yaptı.');
            }

            $location = trim((string) $response->header('Location'));
            $nextUrl = $location !== '' ? $this->resolveUrl($location, $url) : null;
            if ($nextUrl === null) {
                throw new RuntimeException('RSS kaynağı geçersiz bir yönlendirme adresi döndürdü.');
            }

            $url = $this->validatedFeedUrl($nextUrl);
        }

        return $response;
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

        $content = (string) ($entry->description ?? $entry->summary ?? $entry->content ?? $atom->summary ?? $atom->content ?? '');
        $contentNamespace = $entry->children('http://purl.org/rss/1.0/modules/content/');
        if (isset($contentNamespace->encoded)) {
            $content = (string) $contentNamespace->encoded;
        }

        $author = trim((string) ($entry->author ?? ''));
        if ($author === '' && isset($atom->author->name)) {
            $author = trim((string) $atom->author->name);
        }

        $image = '';
        $media = $entry->children('http://search.yahoo.com/mrss/');
        if (isset($media->thumbnail)) {
            $image = (string) ($media->thumbnail->attributes()->url ?? '');
        } elseif (isset($media->content)) {
            $image = (string) ($media->content->attributes()->url ?? '');
        }
        if ($image === '' && isset($entry->enclosure)) {
            $enclosure = $entry->enclosure->attributes();
            $type = strtolower((string) ($enclosure['type'] ?? ''));
            if (str_starts_with($type, 'image/')) $image = (string) ($enclosure['url'] ?? '');
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
            'summary' => Str::limit($this->plainText($content), 12000, ''),
            'author' => $author !== '' ? Str::limit($author, 255, '') : null,
            'image_url' => $this->safeWebUrl($image),
            'published_at' => $publishedAt,
        ];
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

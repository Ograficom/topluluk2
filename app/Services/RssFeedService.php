<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Post;
use App\Models\RssSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class RssFeedService
{
    public function xml(string $type = 'all', ?string $slug = null): string
    {
        abort_unless(in_array($type, ['all', 'latest', 'author', 'channel', 'tag'], true), 404);

        $settings = RssSetting::current();
        abort_unless($settings->enabled && $this->typeEnabled($settings, $type), 404);

        $entity = null;
        $query = Post::query()
            ->published()
            ->where('is_adult', false)
            ->whereNotNull('slug')
            ->with(['user', 'channel', 'tagRecords'])
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        $title = 'OgraFi';
        $description = 'OgraFi üzerinde yayınlanan yazılar.';

        if ($type === 'author') {
            $entity = User::query()->where('username', $slug)->firstOrFail();
            $query->where('user_id', $entity->getKey());
            $title = $entity->displayName().' - OgraFi';
            $description = $entity->bio ?: $entity->displayName().' tarafından yayınlanan yazılar.';
        } elseif ($type === 'channel') {
            $entity = Channel::query()->active()->where('slug', $slug)->firstOrFail();
            $query->where('channel_id', $entity->getKey());
            $title = $entity->name.' - OgraFi';
            $description = $entity->description ?: $entity->name.' kanalındaki yazılar.';
        } elseif ($type === 'tag') {
            $entity = Tag::query()->where('slug', $slug)->firstOrFail();
            $query->whereHas('tagRecords', fn (Builder $tags) => $tags->where('tags.id', $entity->getKey()));
            $title = '#'.$entity->name.' - OgraFi';
            $description = '#'.$entity->name.' etiketiyle yayınlanan yazılar.';
        } elseif ($type === 'latest') {
            $title = 'OgraFi - Son yazılar';
            $description = 'OgraFi üzerinde yayınlanan en yeni yazılar.';
        }

        $contentVersion = Post::query()->max('updated_at') ?: 'empty';
        $entityVersion = $entity?->updated_at?->getTimestamp() ?? 'none';
        $cacheKey = 'ografi.rss.v2.'.sha1(implode('|', [
            $type,
            (string) $slug,
            (string) $settings->updated_at?->getTimestamp(),
            (string) $contentVersion,
            (string) $entityVersion,
        ]));

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(max(1, min(1440, (int) $settings->cache_minutes))),
            function () use ($query, $settings, $title, $description, $type, $slug): string {
                $items = $query->limit(max(1, min(100, (int) $settings->item_limit)))->get();
                $selfUrl = $this->feedUrl($type, $slug);
                $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
                $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"><channel>'."\n";
                $xml .= $this->element('title', $title);
                $xml .= $this->element('link', url('/'));
                $xml .= $this->element('description', $description);
                $xml .= $this->element('language', 'tr-TR');
                $xml .= $this->element('atom:link', '', [
                    'href' => $selfUrl,
                    'rel' => 'self',
                    'type' => 'application/rss+xml',
                ], true);

                foreach ($items as $post) {
                    $link = route('posts.show', ['post' => $post->slug]);
                    $body = trim((string) ($post->body ?: $post->excerpt ?: ''));
                    // RSS description and content:encoded both receive the complete
                    // article body. CDATA preserves HTML and avoids truncating long posts.
                    $fullContent = $body;

                    $xml .= "<item>\n";
                    $xml .= $this->element('title', (string) $post->title);
                    $xml .= $this->element('link', $link);
                    $xml .= $this->element('guid', $link, ['isPermaLink' => 'true'], true);
                    $xml .= $this->cdataElement('description', $fullContent);
                    $xml .= $this->cdataElement('content:encoded', $fullContent);
                    $xml .= $this->element('dc:creator', $post->user?->displayName() ?: 'OgraFi');
                    $publishedAt = $post->published_at ?: $post->created_at;
                    if ($publishedAt) {
                        $xml .= $this->element('pubDate', $publishedAt->toRssString());
                    }
                    if ($post->channel) {
                        $xml .= $this->element('category', (string) $post->channel->name);
                    }
                    foreach ($post->tagRecords as $tag) {
                        $xml .= $this->element('category', (string) $tag->name);
                    }
                    $xml .= "</item>\n";
                }

                return $xml."</channel></rss>";
            },
        );
    }

    public function invalidate(): void
    {
        // Cache keys are versioned from settings and the latest post update.
        // Old entries expire naturally; no broad Cache::flush() is needed.
    }

    private function typeEnabled(RssSetting $settings, string $type): bool
    {
        return match ($type) {
            'all' => true,
            'latest' => $settings->latest_enabled,
            'author' => $settings->author_feeds_enabled,
            'channel' => $settings->channel_feeds_enabled,
            'tag' => $settings->tag_feeds_enabled,
            default => false,
        };
    }

    private function feedUrl(string $type, ?string $slug): string
    {
        return match ($type) {
            'latest' => url('/feed/latest.xml'),
            'author' => url('/feed/authors/'.rawurlencode((string) $slug).'.xml'),
            'channel' => url('/feed/channels/'.rawurlencode((string) $slug).'.xml'),
            'tag' => url('/feed/tags/'.rawurlencode((string) $slug).'.xml'),
            default => url('/rss'),
        };
    }

    private function cdataElement(string $name, string $value): string
    {
        // A CDATA section cannot contain its closing delimiter; split it safely.
        return '<'.$name.'><![CDATA['.str_replace(']]>', ']]]]><![CDATA[>', $value).']]></'.$name.'>'."\n";
    }

    private function element(string $name, string $value, array $attributes = [], bool $selfClosing = false): string
    {
        $attrs = '';
        foreach ($attributes as $key => $attribute) {
            $attrs .= ' '.$key.'="'.htmlspecialchars((string) $attribute, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
        }

        if ($selfClosing) {
            return '<'.$name.$attrs.' />'."\n";
        }

        return '<'.$name.$attrs.'>'.htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</'.$name.'>'."\n";
    }
}

<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\Post;
use App\Models\RssSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RssFeedService
{
    public function xml(string $type = 'all', ?string $slug = null): string
    {
        $settings = RssSetting::current();
        abort_unless($settings->enabled && $this->typeEnabled($settings, $type), 404);

        $cacheKey = 'ografi.rss.'.sha1($type.'|'.($slug ?? ''));
        return Cache::remember($cacheKey, now()->addMinutes(max(1, $settings->cache_minutes)), function () use ($type, $slug, $settings): string {
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
                $author = User::query()->where('username', $slug)->firstOrFail();
                $query->where('user_id', $author->id);
                $title = $author->displayName().' - OgraFi';
                $description = $author->bio ?: $author->displayName().' tarafından yayınlanan yazılar.';
            } elseif ($type === 'channel') {
                $channel = Channel::query()->active()->where('slug', $slug)->firstOrFail();
                $query->where('channel_id', $channel->id);
                $title = $channel->name.' - OgraFi';
                $description = $channel->description ?: $channel->name.' kanalındaki yazılar.';
            } elseif ($type === 'tag') {
                $tag = Tag::query()->where('slug', $slug)->firstOrFail();
                $query->whereHas('tagRecords', fn (Builder $tags) => $tags->whereKey($tag->id));
                $title = '#'.$tag->name.' - OgraFi';
                $description = '#'.$tag->name.' etiketiyle yayınlanan yazılar.';
            } elseif ($type === 'latest') {
                $title = 'OgraFi - Son yazılar';
                $description = 'OgraFi üzerinde yayınlanan en yeni yazılar.';
            }

            $items = $query->limit(max(1, min(100, $settings->item_limit)))->get();
            $selfUrl = $this->feedUrl($type, $slug);
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<rss version="2.0"><channel>'."\n";
            $xml .= $this->element('title', $title);
            $xml .= $this->element('link', url('/'));
            $xml .= $this->element('description', $description);
            $xml .= $this->element('language', 'tr-TR');
            $xml .= $this->element('atom:link', '', ['href' => $selfUrl, 'rel' => 'self', 'type' => 'application/rss+xml'], true);

            foreach ($items as $post) {
                $link = route('posts.show', $post);
                $summary = $settings->content_mode === 'full'
                    ? ($post->body ?: $post->excerpt)
                    : ($post->excerpt ?: $post->body);
                $summary = Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $summary)) ?? ''), 20000, '');

                $xml .= '<item>'."\n";
                $xml .= $this->element('title', $post->title);
                $xml .= $this->element('link', $link);
                $xml .= $this->element('guid', $link, ['isPermaLink' => 'true'], true);
                $xml .= $this->element('description', $summary);
                $xml .= $this->element('author', $post->user?->email ?: $post->user?->displayName() ?: 'OgraFi');
                $xml .= $this->element('pubDate', ($post->published_at ?: $post->created_at)?->toRssString() ?? now()->toRssString());
                if ($post->channel) {
                    $xml .= $this->element('category', $post->channel->name);
                }
                foreach ($post->tagRecords as $tag) {
                    $xml .= $this->element('category', $tag->name);
                }
                if ($image = $post->coverImageUrl()) {
                    $xml .= $this->element('enclosure', '', ['url' => $image, 'type' => 'image'], true);
                }
                $xml .= "</item>\n";
            }

            return $xml."</channel></rss>";
        });
    }

    public function invalidate(): void
    {
        foreach (['all', 'latest'] as $type) {
            Cache::forget('ografi.rss.'.sha1($type.'|'));
        }

        // Entity-specific caches expire according to the Filament setting.
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
            default => url('/rss.xml'),
        };
    }

    private function element(string $name, string $value, array $attributes = [], bool $selfClosing = false): string
    {
        $attrs = '';
        foreach ($attributes as $key => $attribute) {
            $attrs .= ' '.$key.'="'.htmlspecialchars((string) $attribute, ENT_XML1 | ENT_QUOTES, 'UTF-8').'"';
        }

        if ($selfClosing) {
            return '<'.$name.$attrs.' />'."\n";
        }

        return '<'.$name.$attrs.'>'.htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</'.$name.'>'."\n";
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Models\RssSource;
use App\Models\Story;
use App\Models\User;
use App\Services\OllamaRssEditor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class ImportRssWithOllama extends Command
{
    protected $signature = 'rss:import {--source=* : RSS/Atom source URL} {--limit= : Maximum new items} {--dry-run}';

    protected $description = 'Import RSS items and rewrite them through Ollama';

    public function handle(OllamaRssEditor $editor): int
    {
        $sources = $this->configuredSources();

        if ($sources === []) {
            $this->error('No active RSS source configured. Add one in Filament or set RSS_IMPORT_SOURCES.');
            return self::FAILURE;
        }

        foreach ($sources as $source) {
            $imported = 0;
            $limit = max(1, (int) ($this->option('limit') ?: $source['limit']));

            try {
                foreach ($this->readFeed($source['url']) as $item) {
                    if ($imported >= $limit) break;
                    if (Story::where('canonical_url', $item['url'])->exists()) continue;

                    $edited = $editor->edit($item['title'], mb_substr($item['summary'], 0, 4000));
                    $this->line($edited['title']);
                    if ($this->option('dry-run')) {
                        $imported++;
                        continue;
                    }

                    $paragraphs = array_values(array_filter(preg_split('/\R{2,}/u', $edited['content'])));
                    $body = ['time' => now()->valueOf(), 'blocks' => [], 'version' => '2.30.8'];
                    foreach ($paragraphs as $paragraph) {
                        $body['blocks'][] = ['id' => Str::random(10), 'type' => 'paragraph', 'data' => ['text' => e($paragraph)]];
                    }

                    Story::create([
                        'user_id' => $source['user_id'],
                        'community_id' => $source['community_id'],
                        'title' => $edited['title'],
                        'subtitle' => $edited['summary'] ?: null,
                        'summary' => $edited['summary'] ?: null,
                        'body' => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'body_rendered' => collect($paragraphs)->map(fn ($p) => '<p>'.e($p).'</p>')->implode("\n"),
                        'canonical_url' => $item['url'],
                        'content_visibility' => 'All',
                        'published_at' => $source['publish'] ? now() : null,
                        'approved_at' => $source['publish'] ? now() : null,
                        'meta' => ['meta_title' => $edited['title'], 'meta_description' => $edited['summary'], 'meta_canonical_url' => $item['url']],
                    ]);
                    $imported++;
                }

                $source['model']?->update(['last_run_at' => now(), 'last_error' => null]);
            } catch (Throwable $exception) {
                report($exception);
                $source['model']?->update(['last_run_at' => now(), 'last_error' => mb_substr($exception->getMessage(), 0, 2000)]);
                $this->warn($source['url'].': '.$exception->getMessage());
            }

            $this->info("{$source['name']}: imported {$imported} RSS item(s).");
        }

        return self::SUCCESS;
    }

    private function configuredSources(): array
    {
        if ($urls = array_values(array_filter($this->option('source')))) {
            $user = User::where('email', config('rss_import.user_email'))->first();
            if (! $user) return [];

            return array_map(fn ($url) => [
                'name' => $url,
                'url' => $url,
                'user_id' => $user->id,
                'community_id' => null,
                'publish' => (bool) config('rss_import.publish'),
                'limit' => config('rss_import.limit'),
                'model' => null,
            ], $urls);
        }

        if (Schema::hasTable('rss_sources')) {
            $databaseSources = RssSource::query()->where('is_active', true)->get()->map(fn (RssSource $source) => [
                'name' => $source->name,
                'url' => $source->url,
                'user_id' => $source->user_id,
                'community_id' => $source->community_id,
                'publish' => $source->auto_publish,
                'limit' => $source->item_limit,
                'model' => $source,
            ])->all();

            if ($databaseSources !== []) return $databaseSources;
        }

        $user = User::where('email', config('rss_import.user_email'))->first();
        if (! $user) return [];
        $community = config('rss_import.community_slug')
            ? Community::where('slug', config('rss_import.community_slug'))->first()
            : null;

        return array_map(fn ($url) => [
            'name' => $url,
            'url' => $url,
            'user_id' => $user->id,
            'community_id' => $community?->id,
            'publish' => (bool) config('rss_import.publish'),
            'limit' => config('rss_import.limit'),
            'model' => null,
        ], array_values(array_filter(config('rss_import.sources'))));
    }

    private function readFeed(string $url): array
    {
        if (! filter_var($url, FILTER_VALIDATE_URL) || ! Str::startsWith($url, ['http://', 'https://'])) {
            throw new \InvalidArgumentException('Invalid RSS URL.');
        }

        $xml = new SimpleXMLElement(Http::timeout(30)->get($url)->throw()->body(), LIBXML_NOCDATA);
        $nodes = isset($xml->channel->item) ? $xml->channel->item : $xml->entry;
        $items = [];
        foreach ($nodes as $node) {
            $link = (string) $node->link;
            if ($link === '' && isset($node->link['href'])) $link = (string) $node->link['href'];
            $summary = (string) ($node->description ?: $node->summary ?: $node->content);
            if ($link && trim((string) $node->title) !== '') {
                $items[] = ['title' => trim(strip_tags((string) $node->title)), 'summary' => trim(strip_tags($summary)), 'url' => $link];
            }
        }

        return $items;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Models\Story;
use App\Models\User;
use App\Services\OllamaRssEditor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class ImportRssWithOllama extends Command
{
    protected $signature = 'rss:import {--source=* : RSS/Atom source URL} {--limit= : Maximum new items} {--dry-run}';

    protected $description = 'Import RSS items and rewrite them through Ollama';

    public function handle(OllamaRssEditor $editor): int
    {
        $sources = array_values(array_filter($this->option('source') ?: config('rss_import.sources')));
        $limit = max(1, (int) ($this->option('limit') ?: config('rss_import.limit')));
        $user = User::where('email', config('rss_import.user_email'))->first();
        $community = config('rss_import.community_slug')
            ? Community::where('slug', config('rss_import.community_slug'))->first()
            : null;

        if ($sources === []) {
            $this->error('No RSS source configured. Set RSS_IMPORT_SOURCES.');
            return self::FAILURE;
        }
        if (! $user) {
            $this->error('RSS import user was not found. Set RSS_IMPORT_USER_EMAIL.');
            return self::FAILURE;
        }

        $imported = 0;
        foreach ($sources as $source) {
            if ($imported >= $limit) break;

            try {
                foreach ($this->readFeed($source) as $item) {
                    if ($imported >= $limit) break 2;
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
                        'user_id' => $user->id,
                        'community_id' => $community?->id,
                        'title' => $edited['title'],
                        'subtitle' => $edited['summary'] ?: null,
                        'summary' => $edited['summary'] ?: null,
                        'body' => $body,
                        'body_rendered' => collect($paragraphs)->map(fn ($p) => '<p>'.e($p).'</p>')->implode("\n"),
                        'canonical_url' => $item['url'],
                        'content_visibility' => 'All',
                        'published_at' => config('rss_import.publish') ? now() : null,
                        'approved_at' => config('rss_import.publish') ? now() : null,
                        'meta' => ['meta_title' => $edited['title'], 'meta_description' => $edited['summary'], 'meta_canonical_url' => $item['url']],
                    ]);
                    $imported++;
                }
            } catch (Throwable $exception) {
                report($exception);
                $this->warn($source.': '.$exception->getMessage());
            }
        }

        $this->info("Imported {$imported} RSS item(s).");
        return self::SUCCESS;
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

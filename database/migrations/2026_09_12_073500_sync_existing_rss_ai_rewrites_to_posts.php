<?php

use App\Services\Rss\RssArticleRewriteService;
use App\Support\PostSeoText;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rss_items')
            ->join('rss_feeds', 'rss_feeds.id', '=', 'rss_items.rss_feed_id')
            ->where('rss_feeds.ai_rewrite_enabled', true)
            ->where('rss_feeds.import_as_posts', true)
            ->whereNotNull('rss_items.post_id')
            ->whereNotNull('rss_items.ai_rewritten_at')
            ->whereNotNull('rss_items.ai_title')
            ->whereNotNull('rss_items.ai_content')
            ->whereNotNull('rss_items.hash')
            ->select([
                'rss_items.id as rss_item_id',
                'rss_items.post_id',
                'rss_items.hash',
                'rss_items.ai_source_hash',
                'rss_items.ai_title',
                'rss_items.ai_summary',
                'rss_items.ai_content',
            ])
            ->orderBy('rss_items.id')
            ->get()
            ->each(function ($item): void {
                $expectedHash = RssArticleRewriteService::expectedSourceHash((string) $item->hash);
                if (! hash_equals($expectedHash, (string) $item->ai_source_hash)) {
                    return;
                }

                $title = trim((string) $item->ai_title);
                $summary = trim((string) ($item->ai_summary ?? ''));
                $content = trim((string) $item->ai_content);

                if ($title === '' || $content === '') {
                    return;
                }

                DB::table('posts')
                    ->where('id', $item->post_id)
                    ->update([
                        'title' => $title,
                        'excerpt' => $summary !== '' ? $summary : null,
                        'content' => $content,
                        'content_json' => null,
                        'meta_title' => PostSeoText::title($title),
                        'meta_description' => PostSeoText::description($summary),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        // The original copied RSS body is intentionally not restored.
    }
};

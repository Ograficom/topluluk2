<?php

namespace App\Console\Commands;

use App\Models\RssItem;
use App\Services\Rss\RssArticleRewriteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RewriteAllLinkedRssPosts extends Command
{
    protected $signature = 'rss:ai-rewrite-linked {--limit=5 : Maximum linked RSS posts to rewrite in this run}';

    protected $description = 'Rewrite existing RSS-linked posts with the active AI provider, including posts from disabled feeds';

    public function handle(RssArticleRewriteService $rewriter): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $processed = 0;
        $errors = 0;

        $items = RssItem::query()
            ->whereNotNull('post_id')
            ->whereNotNull('hash')
            ->whereNull('ai_rejected_at')
            ->where(function ($query) {
                $query->whereNull('ai_last_attempted_at')
                    ->orWhere('ai_last_attempted_at', '<=', now()->subMinutes(15));
            })
            ->with('feed')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->filter(fn (RssItem $item) => blank($item->ai_content)
                || $item->ai_source_hash !== RssArticleRewriteService::expectedSourceHash((string) $item->hash))
            ->take($limit);

        foreach ($items as $item) {
            try {
                // Pass no feed-specific model here. The active provider must choose
                // its own configured model (OpenAI or Ollama) to avoid cross-provider
                // model ids such as gpt-5.6-luna being sent to Ollama.
                $rewriter->rewrite($item, null);
                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                Log::warning('Linked RSS AI rewrite failed', [
                    'rss_item_id' => $item->id,
                    'post_id' => $item->post_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Linked RSS AI rewrite: processed={$processed} errors={$errors}");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}

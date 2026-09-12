<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rss_items') || ! Schema::hasTable('rss_feeds')) {
            return;
        }

        $feedIds = DB::table('rss_items')
            ->whereNotNull('post_id')
            ->distinct()
            ->pluck('rss_feed_id')
            ->filter()
            ->values();

        if ($feedIds->isNotEmpty()) {
            DB::table('rss_feeds')
                ->whereIn('id', $feedIds)
                ->update([
                    'import_as_posts' => true,
                    'ai_rewrite_enabled' => true,
                    'updated_at' => now(),
                ]);
        }

        DB::table('rss_items')
            ->whereNotNull('post_id')
            ->update([
                'ai_source_hash' => null,
                'ai_title' => null,
                'ai_summary' => null,
                'ai_content' => null,
                'ai_tags' => null,
                'ai_rewritten_at' => null,
                'ai_rewrite_error' => null,
                'ai_rewrite_attempts' => 0,
                'ai_last_attempted_at' => null,
                'ai_rejected_at' => null,
                'ai_rejection_reason' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // AI output cannot be reconstructed safely after a forced rewrite reset.
    }
};

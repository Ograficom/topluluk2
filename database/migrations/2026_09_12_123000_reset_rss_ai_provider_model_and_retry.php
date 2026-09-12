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
            // Provider-neutral: null means the active AI backend chooses its own
            // configured model. This prevents OpenAI model ids from being sent to Ollama.
            DB::table('rss_feeds')
                ->whereIn('id', $feedIds)
                ->update([
                    'ai_model' => null,
                    'ai_rewrite_enabled' => true,
                    'import_as_posts' => true,
                    'updated_at' => now(),
                ]);
        }

        // Failed attempts use a 15 minute backoff. Clear it so the corrected
        // provider/model configuration retries every linked post immediately.
        DB::table('rss_items')
            ->whereNotNull('post_id')
            ->whereNull('ai_content')
            ->update([
                'ai_last_attempted_at' => null,
                'ai_rewrite_error' => null,
                'ai_rewrite_attempts' => 0,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Previous provider-specific model selections are intentionally not restored.
    }
};

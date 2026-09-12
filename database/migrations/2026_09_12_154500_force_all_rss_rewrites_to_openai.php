<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('rss_feeds')->update([
            'ai_rewrite_enabled' => true,
            'ai_model' => 'gpt-5.6-terra',
            'updated_at' => $now,
        ]);

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
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Intentionally irreversible: restoring stale generated RSS text would
        // risk republishing source-copy content.
    }
};

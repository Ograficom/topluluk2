<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rss_feeds')
            ->where('import_as_posts', true)
            ->update(['ai_rewrite_enabled' => true]);
    }

    public function down(): void
    {
        // Existing feed preferences are intentionally not disabled on rollback.
    }
};

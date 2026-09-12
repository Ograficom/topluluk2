<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rss_feeds')->update([
            'ai_rewrite_enabled' => false,
            'ai_model' => null,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Provider removal is intentionally not reversible from this migration.
    }
};

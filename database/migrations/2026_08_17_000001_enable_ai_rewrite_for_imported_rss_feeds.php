<?php

use App\Models\RssFeed;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        RssFeed::query()
            ->where('import_as_posts', true)
            ->update(['ai_rewrite_enabled' => true]);
    }

    public function down(): void
    {
        // Existing feed preferences are intentionally not disabled on rollback.
    }
};

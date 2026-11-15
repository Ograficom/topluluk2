<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('site_settings')
            && Schema::hasColumn('site_settings', 'home_seo_index')
        ) {
            DB::table('site_settings')
                ->where('id', 1)
                ->update(['home_seo_index' => true]);
        }
    }

    public function down(): void
    {
        // Keep the indexing preference enabled when rolling back this corrective migration.
    }
};

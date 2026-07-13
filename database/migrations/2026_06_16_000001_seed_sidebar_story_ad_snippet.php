<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('snippets')) {
            return;
        }

        $now = now();

        DB::table('snippets')->updateOrInsert(
            ['key' => 'ads_sidebar_story'],
            [
                'title' => 'Reklam - Sag Sutun Story',
                'description' => 'Sag sidebar icin story oranli reklam alani. Post detay, p sayfalari, iletisim, arama ve SSS sayfalarinda gosterilmez.',
                'content' => '<!-- Reklam kodunuzu buraya yapistirin -->',
                'is_active' => false,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('snippets')) {
            return;
        }

        DB::table('snippets')
            ->where('key', 'ads_sidebar_story')
            ->delete();
    }
};

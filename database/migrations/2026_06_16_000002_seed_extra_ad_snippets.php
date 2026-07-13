<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $slots = [
        [
            'title' => 'Reklam - Sol Sutun Ust',
            'key' => 'ads_left_sidebar_top',
            'description' => 'Sol sidebar ustunde gorunen reklam alani. Post detay, p sayfalari, iletisim, arama ve SSS sayfalarinda gosterilmez.',
        ],
        [
            'title' => 'Reklam - Ana Icerik Ust',
            'key' => 'ads_main_before_content',
            'description' => 'Ana icerigin ustunde gorunen reklam alani. Post detay, p sayfalari, iletisim, arama ve SSS sayfalarinda gosterilmez.',
        ],
        [
            'title' => 'Reklam - Ana Icerik Alt',
            'key' => 'ads_main_after_content',
            'description' => 'Ana icerigin altinda gorunen reklam alani. Post detay, p sayfalari, iletisim, arama ve SSS sayfalarinda gosterilmez.',
        ],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('snippets')) {
            return;
        }

        $now = now();

        foreach ($this->slots as $slot) {
            DB::table('snippets')->updateOrInsert(
                ['key' => $slot['key']],
                [
                    'title' => $slot['title'],
                    'description' => $slot['description'],
                    'content' => '<!-- Reklam kodunuzu buraya yapistirin -->',
                    'is_active' => false,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('snippets')) {
            return;
        }

        DB::table('snippets')
            ->whereIn('key', array_column($this->slots, 'key'))
            ->delete();
    }
};

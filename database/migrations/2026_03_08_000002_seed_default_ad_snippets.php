<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $slots = [
        [
            'title' => 'Reklam - Feed Ust',
            'key' => 'ads_feed_top',
            'description' => 'Post listesinin ustunde gorunen reklam alani.',
        ],
        [
            'title' => 'Reklam - Feed Arasi',
            'key' => 'ads_feed_inline',
            'description' => 'Her 3 posttan sonra giren reklam alani.',
        ],
        [
            'title' => 'Reklam - Sag Sutun',
            'key' => 'ads_sidebar_top',
            'description' => 'Sag sidebar ustunde gorunen reklam alani.',
        ],
        [
            'title' => 'Reklam - Mobil Akis',
            'key' => 'ads_mobile_inline',
            'description' => 'Mobil cihazlarda akis icinde gorunen reklam alani.',
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

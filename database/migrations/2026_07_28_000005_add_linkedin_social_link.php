<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('social_links')->updateOrInsert(
            ['platform' => 'linkedin'],
            [
                'label' => 'LinkedIn',
                'value' => null,
                'is_active' => false,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('social_links')->where('platform', 'linkedin')->delete();
    }
};

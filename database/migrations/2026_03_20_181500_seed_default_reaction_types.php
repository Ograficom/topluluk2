<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        if (
            DB::table('reaction_types')->where('short_code', 'simile')->exists()
            && ! DB::table('reaction_types')->where('short_code', 'soft_smile')->exists()
        ) {
            DB::table('reaction_types')
                ->where('short_code', 'simile')
                ->update([
                    'label' => 'Soft Smile',
                    'short_code' => 'soft_smile',
                    'emoji' => '☺️',
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
        }

        DB::table('reaction_types')
            ->where('short_code', 'smile')
            ->update([
                'label' => 'Smile',
                'emoji' => '😊',
                'is_active' => true,
                'updated_at' => $now,
            ]);

        DB::table('reaction_types')->upsert([
            [
                'label' => 'Heart',
                'short_code' => 'heart',
                'emoji' => '❤️',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Popcorn',
                'short_code' => 'popcorn',
                'emoji' => '🍿',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Medal',
                'short_code' => 'medal',
                'emoji' => '🏅',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Star',
                'short_code' => 'star',
                'emoji' => '⭐',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Diamond',
                'short_code' => 'diamond',
                'emoji' => '💎',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Clap',
                'short_code' => 'clap',
                'emoji' => '👏',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Trophy',
                'short_code' => 'trophy',
                'emoji' => '🏆',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Clown',
                'short_code' => 'clown',
                'emoji' => '🤡',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Elder',
                'short_code' => 'elder',
                'emoji' => '🧓',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Salute',
                'short_code' => 'salute',
                'emoji' => '🫡',
                'gif_url' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['short_code'], ['label', 'emoji', 'gif_url', 'is_active', 'updated_at']);
    }

    public function down(): void
    {
        DB::table('reaction_types')
            ->whereIn('short_code', [
                'heart',
                'popcorn',
                'medal',
                'star',
                'diamond',
                'clap',
                'trophy',
                'clown',
                'elder',
                'salute',
            ])
            ->delete();

        if (
            DB::table('reaction_types')->where('short_code', 'soft_smile')->exists()
            && ! DB::table('reaction_types')->where('short_code', 'simile')->exists()
        ) {
            DB::table('reaction_types')
                ->where('short_code', 'soft_smile')
                ->update([
                    'label' => 'smile',
                    'short_code' => 'simile',
                    'emoji' => '😊',
                    'updated_at' => now(),
                ]);
        }
    }
};

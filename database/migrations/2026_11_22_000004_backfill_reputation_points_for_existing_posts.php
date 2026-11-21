<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')
            || ! Schema::hasTable('users')
            || ! Schema::hasTable('reputation_transactions')
            || ! Schema::hasTable('reputation_settings')) {
            return;
        }

        $settings = DB::table('reputation_settings')->where('id', 1)->first();
        $points = (int) ($settings->post_points ?? 10);
        $excludeCompanies = (bool) ($settings->exclude_company_accounts ?? true);

        if ($points <= 0) {
            return;
        }

        DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->where('posts.is_published', true)
            ->where('posts.is_spam', false)
            ->whereNull('posts.advertisement_id')
            ->where(function ($query): void {
                $query->whereNull('posts.published_at')
                    ->orWhere('posts.published_at', '<=', now());
            })
            ->when($excludeCompanies, fn ($query) => $query->where('users.is_company', false))
            ->select(['posts.id', 'posts.user_id'])
            ->orderBy('posts.id')
            ->chunk(500, function ($posts) use ($points): void {
                $now = now();
                $rows = $posts->map(fn ($post): array => [
                    'user_id' => $post->user_id,
                    'points' => $points,
                    'event_key' => 'post:'.$post->id.':published',
                    'description' => 'Yazı yayınladı',
                    'source_type' => \App\Models\Post::class,
                    'source_id' => $post->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('reputation_transactions')->insertOrIgnore($rows);
            });
    }

    public function down(): void
    {
        // Keep awarded history intact; removing reputation transactions would
        // also erase points earned through normal activity after this migration.
    }
};

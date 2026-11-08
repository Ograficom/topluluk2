<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('advertisement_id')->nullable()->unique()->after('user_id')
                ->constrained('advertisements')->cascadeOnDelete();
        });

        DB::table('advertisements')->orderBy('id')->each(function (object $ad): void {
            $base = Str::limit(Str::slug($ad->title) ?: 'sponsored-post', 240, '');
            $slug = $base;
            for ($suffix = 1; DB::table('posts')->where('slug', $slug)->exists(); $suffix++) {
                $slug = Str::limit($base, 240 - strlen((string) $suffix), '').'-'.$suffix;
            }

            DB::table('posts')->insert([
                'user_id' => $ad->user_id,
                'advertisement_id' => $ad->id,
                'title' => $ad->title,
                'slug' => $slug,
                'excerpt' => Str::limit($ad->description, 500),
                'body' => $ad->description,
                'cover_image' => $ad->image_path,
                'video_url' => $ad->video_url,
                'is_published' => true,
                'published_at' => $ad->created_at ?? now(),
                'is_adult' => false,
                'created_at' => $ad->created_at ?? now(),
                'updated_at' => $ad->updated_at ?? now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('advertisement_id');
        });
    }
};

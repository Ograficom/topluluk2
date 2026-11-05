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
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::create('post_tag', function (Blueprint $table): void {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['post_id', 'tag_id']);
            $table->index('tag_id');
        });

        // Preserve the legacy JSON column and copy existing tags into the new relational tables.
        DB::table('posts')->select(['id', 'tags'])->orderBy('id')->chunk(200, function ($posts): void {
            foreach ($posts as $post) {
                $values = json_decode((string) $post->tags, true);
                if (! is_array($values)) continue;

                foreach ($values as $value) {
                    if (! is_string($value) || trim($value) === '') continue;
                    $name = trim($value);
                    $base = Str::limit(Str::slug($name), 110, '');
                    if ($base === '') continue;

                    $tag = DB::table('tags')->where('slug', $base)->first();
                    if (! $tag) {
                        $slug = $base;
                        $suffix = 1;
                        while (DB::table('tags')->where('slug', $slug)->exists()) {
                            $slug = Str::limit($base, 110, '').'-'.$suffix++;
                        }
                        $tagId = DB::table('tags')->insertGetId([
                            'name' => $name,
                            'slug' => $slug,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $tagId = $tag->id;
                    }

                    DB::table('post_tag')->insertOrIgnore([
                        'post_id' => $post->id,
                        'tag_id' => $tagId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tags');
    }
};

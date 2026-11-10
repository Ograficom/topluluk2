<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'posts' => [
                'seo_title' => fn (Blueprint $table) => $table->string('seo_title', 255)->nullable(),
                'seo_keywords' => fn (Blueprint $table) => $table->text('seo_keywords')->nullable(),
                'canonical_url' => fn (Blueprint $table) => $table->string('canonical_url', 2048)->nullable(),
                'robots_index' => fn (Blueprint $table) => $table->boolean('robots_index')->default(true),
                'og_image' => fn (Blueprint $table) => $table->string('og_image', 2048)->nullable(),
            ],
            'users' => [
                'profile_seo_title' => fn (Blueprint $table) => $table->string('profile_seo_title', 255)->nullable(),
                'profile_seo_description' => fn (Blueprint $table) => $table->text('profile_seo_description')->nullable(),
                'profile_seo_keywords' => fn (Blueprint $table) => $table->text('profile_seo_keywords')->nullable(),
                'profile_canonical_url' => fn (Blueprint $table) => $table->string('profile_canonical_url', 2048)->nullable(),
                'profile_robots_index' => fn (Blueprint $table) => $table->boolean('profile_robots_index')->default(true),
                'profile_og_image' => fn (Blueprint $table) => $table->string('profile_og_image', 2048)->nullable(),
            ],
        ] as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column => $definition) {
                    if (! Schema::hasColumn($tableName, $column)) $definition($table);
                }
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'posts' => ['seo_title', 'seo_keywords', 'canonical_url', 'robots_index', 'og_image'],
            'users' => ['profile_seo_title', 'profile_seo_description', 'profile_seo_keywords', 'profile_canonical_url', 'profile_robots_index', 'profile_og_image'],
        ] as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) $table->dropColumn($column);
                }
            });
        }
    }
};

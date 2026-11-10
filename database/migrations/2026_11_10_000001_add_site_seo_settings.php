<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings')) {
            Schema::table('site_settings', function (Blueprint $table): void {
                if (! Schema::hasColumn('site_settings', 'seo_default_title')) $table->string('seo_default_title', 255)->nullable();
                if (! Schema::hasColumn('site_settings', 'seo_default_description')) $table->text('seo_default_description')->nullable();
                if (! Schema::hasColumn('site_settings', 'seo_default_keywords')) $table->text('seo_default_keywords')->nullable();
                if (! Schema::hasColumn('site_settings', 'seo_og_image')) $table->string('seo_og_image', 2048)->nullable();
                if (! Schema::hasColumn('site_settings', 'google_site_verification')) $table->string('google_site_verification', 255)->nullable();
                if (! Schema::hasColumn('site_settings', 'home_seo_og_image')) $table->string('home_seo_og_image', 2048)->nullable();
                if (! Schema::hasColumn('site_settings', 'home_seo_title')) $table->string('home_seo_title', 255)->nullable();
                if (! Schema::hasColumn('site_settings', 'home_seo_description')) $table->text('home_seo_description')->nullable();
                if (! Schema::hasColumn('site_settings', 'home_seo_keywords')) $table->text('home_seo_keywords')->nullable();
                if (! Schema::hasColumn('site_settings', 'home_seo_index')) $table->boolean('home_seo_index')->default(true);
            });
        }

        if (Schema::hasTable('site_pages')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                if (! Schema::hasColumn('site_pages', 'meta_keywords')) $table->text('meta_keywords')->nullable();
                if (! Schema::hasColumn('site_pages', 'canonical_url')) $table->string('canonical_url', 2048)->nullable();
                if (! Schema::hasColumn('site_pages', 'robots_index')) $table->boolean('robots_index')->default(true);
                if (! Schema::hasColumn('site_pages', 'og_image')) $table->string('og_image', 2048)->nullable();
            });
        }

        if (Schema::hasTable('channels')) {
            Schema::table('channels', function (Blueprint $table): void {
                if (! Schema::hasColumn('channels', 'meta_title')) $table->string('meta_title')->nullable();
                if (! Schema::hasColumn('channels', 'meta_description')) $table->text('meta_description')->nullable();
                if (! Schema::hasColumn('channels', 'meta_keywords')) $table->text('meta_keywords')->nullable();
                if (! Schema::hasColumn('channels', 'canonical_url')) $table->string('canonical_url', 2048)->nullable();
                if (! Schema::hasColumn('channels', 'robots_index')) $table->boolean('robots_index')->default(true);
                if (! Schema::hasColumn('channels', 'og_image')) $table->string('og_image', 2048)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'site_settings' => ['seo_default_title', 'seo_default_description', 'seo_default_keywords', 'seo_og_image', 'google_site_verification', 'home_seo_og_image', 'home_seo_title', 'home_seo_description', 'home_seo_keywords', 'home_seo_index'],
            'site_pages' => ['meta_keywords', 'canonical_url', 'robots_index', 'og_image'],
            'channels' => ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'robots_index', 'og_image'],
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

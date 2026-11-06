<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sitemap_settings')) {
            Schema::create('sitemap_settings', function (Blueprint $table): void {
                $table->id();
                $table->boolean('enabled')->default(true);
                $table->string('base_url', 2048)->nullable();
                $table->json('include')->nullable();
                $table->timestamp('last_generated_at')->nullable();
                $table->unsignedInteger('url_count')->default(0);
                $table->timestamps();
            });

            return;
        }

        // Önceki yarım kalmış kurulumlarda mevcut tabloyu bozmadan eksik alanları tamamla.
        Schema::table('sitemap_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('sitemap_settings', 'enabled')) {
                $table->boolean('enabled')->default(true);
            }
            if (! Schema::hasColumn('sitemap_settings', 'base_url')) {
                $table->string('base_url', 2048)->nullable();
            }
            if (! Schema::hasColumn('sitemap_settings', 'include')) {
                $table->json('include')->nullable();
            }
            if (! Schema::hasColumn('sitemap_settings', 'last_generated_at')) {
                $table->timestamp('last_generated_at')->nullable();
            }
            if (! Schema::hasColumn('sitemap_settings', 'url_count')) {
                $table->unsignedInteger('url_count')->default(0);
            }
            if (! Schema::hasColumn('sitemap_settings', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('sitemap_settings', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_settings');
    }
};

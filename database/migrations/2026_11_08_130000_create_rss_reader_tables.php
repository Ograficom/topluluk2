<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->text('url');
            $table->char('url_hash', 64)->unique();
            $table->string('site_url', 2048)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_fetched_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('rss_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rss_source_id')->constrained('rss_sources')->cascadeOnDelete();
            $table->char('guid_hash', 64);
            $table->text('guid')->nullable();
            $table->string('title', 500);
            $table->string('link', 2048);
            $table->longText('summary')->nullable();
            $table->string('author')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['rss_source_id', 'guid_hash']);
            $table->index(['rss_source_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_items');
        Schema::dropIfExists('rss_sources');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->boolean('latest_enabled')->default(true);
            $table->boolean('author_feeds_enabled')->default(true);
            $table->boolean('channel_feeds_enabled')->default(true);
            $table->boolean('tag_feeds_enabled')->default(true);
            $table->unsignedSmallInteger('item_limit')->default(20);
            $table->string('content_mode', 20)->default('excerpt');
            $table->unsignedSmallInteger('cache_minutes')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_settings');
    }
};

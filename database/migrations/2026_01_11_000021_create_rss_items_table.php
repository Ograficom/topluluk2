<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_feed_id')->constrained('rss_feeds')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('guid', 512);
            $table->string('title', 500)->nullable();
            $table->string('link', 2048)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('hash', 64)->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['rss_feed_id', 'guid']);
            $table->index(['rss_feed_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_items');
    }
};


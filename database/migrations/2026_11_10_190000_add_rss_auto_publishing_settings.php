<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rss_sources', function (Blueprint $table): void {
            $table->foreignId('author_id')->nullable()->after('url_hash')->constrained('users')->nullOnDelete();
            $table->foreignId('channel_id')->nullable()->after('author_id')->constrained('channels')->nullOnDelete();
            $table->boolean('auto_publish')->default(true)->after('channel_id')->index();
        });

        Schema::table('rss_items', function (Blueprint $table): void {
            $table->foreignId('published_post_id')->nullable()->after('rss_source_id')->constrained('posts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rss_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('published_post_id');
        });

        Schema::table('rss_sources', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('author_id');
            $table->dropConstrainedForeignId('channel_id');
            $table->dropColumn('auto_publish');
        });
    }
};

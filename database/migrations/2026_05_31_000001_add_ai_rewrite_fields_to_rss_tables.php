<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rss_feeds', function (Blueprint $table) {
            $table->boolean('ai_rewrite_enabled')->default(false)->after('update_existing_posts');
            $table->string('ai_model')->nullable()->after('ai_rewrite_enabled');
        });

        Schema::table('rss_items', function (Blueprint $table) {
            $table->string('ai_source_hash', 64)->nullable()->after('hash');
            $table->string('ai_title', 500)->nullable()->after('ai_source_hash');
            $table->text('ai_summary')->nullable()->after('ai_title');
            $table->longText('ai_content')->nullable()->after('ai_summary');
            $table->timestamp('ai_rewritten_at')->nullable()->after('ai_content');
            $table->text('ai_rewrite_error')->nullable()->after('ai_rewritten_at');
        });
    }

    public function down(): void
    {
        Schema::table('rss_items', function (Blueprint $table) {
            $table->dropColumn([
                'ai_source_hash',
                'ai_title',
                'ai_summary',
                'ai_content',
                'ai_rewritten_at',
                'ai_rewrite_error',
            ]);
        });

        Schema::table('rss_feeds', function (Blueprint $table) {
            $table->dropColumn(['ai_rewrite_enabled', 'ai_model']);
        });
    }
};

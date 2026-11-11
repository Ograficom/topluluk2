<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->boolean('is_spam')->default(false)->index();
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->boolean('comment_spam_enabled')->default(true);
            $table->json('comment_spam_words')->nullable();
            $table->unsignedTinyInteger('comment_spam_max_links')->default(2);
            $table->unsignedTinyInteger('comment_spam_rate_limit')->default(4);
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'comment_spam_enabled',
                'comment_spam_words',
                'comment_spam_max_links',
                'comment_spam_rate_limit',
            ]);
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex(['is_spam']);
            $table->dropColumn('is_spam');
        });
    }
};

<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->boolean('is_spam')->default(false)->index();
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->boolean('post_spam_enabled')->default(true);
            $table->json('post_spam_words')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn(['post_spam_enabled', 'post_spam_words']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['is_spam']);
            $table->dropColumn('is_spam');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->boolean('widget_comments_enabled')->default(true);
            $table->unsignedInteger('widget_comments_count')->default(10);
            $table->boolean('widget_tags_enabled')->default(true);
            $table->unsignedInteger('widget_tags_count')->default(8);
            $table->boolean('widget_trending_enabled')->default(true);
            $table->unsignedInteger('widget_trending_count')->default(5);
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'widget_comments_enabled',
                'widget_comments_count',
                'widget_tags_enabled',
                'widget_tags_count',
                'widget_trending_enabled',
                'widget_trending_count',
            ]);
        });
    }
};

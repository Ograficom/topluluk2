<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('categories_name_color')->nullable();
            $table->string('categories_stats_color')->nullable();
            $table->string('categories_description_color')->nullable();
            $table->string('categories_accent_color')->nullable();
            $table->string('categories_hover_bg_color')->nullable();
            $table->string('categories_border_color')->nullable();
            $table->unsignedInteger('categories_avatar_size')->nullable();
            $table->decimal('categories_name_font_size', 5, 1)->nullable();
            $table->decimal('categories_stats_font_size', 5, 1)->nullable();
            $table->decimal('categories_description_font_size', 5, 1)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'categories_name_color',
                'categories_stats_color',
                'categories_description_color',
                'categories_accent_color',
                'categories_hover_bg_color',
                'categories_border_color',
                'categories_avatar_size',
                'categories_name_font_size',
                'categories_stats_font_size',
                'categories_description_font_size',
            ]);
        });
    }
};

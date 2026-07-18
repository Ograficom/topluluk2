<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('brand_background_color')->nullable();
            $table->string('brand_surface_color')->nullable();
            $table->string('brand_button_color')->nullable();
            $table->string('brand_button_hover_color')->nullable();
            $table->string('brand_button_text_color')->nullable();
            $table->string('brand_text_color')->nullable();
            $table->string('brand_font_family')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'brand_background_color',
                'brand_surface_color',
                'brand_button_color',
                'brand_button_hover_color',
                'brand_button_text_color',
                'brand_text_color',
                'brand_font_family',
            ]);
        });
    }
};

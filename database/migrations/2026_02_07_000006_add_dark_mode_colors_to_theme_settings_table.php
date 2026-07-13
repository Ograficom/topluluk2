<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('dark_bg_color')->nullable();
            $table->string('dark_surface_color')->nullable();
            $table->string('dark_surface2_color')->nullable();
            $table->string('dark_text_color')->nullable();
            $table->string('dark_muted_color')->nullable();
            $table->string('dark_border_color')->nullable();
            $table->string('dark_primary_color')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'dark_bg_color',
                'dark_surface_color',
                'dark_surface2_color',
                'dark_text_color',
                'dark_muted_color',
                'dark_border_color',
                'dark_primary_color',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('layout_bg_color')->nullable();
            $table->string('left_column_bg_color')->nullable();
            $table->string('main_column_bg_color')->nullable();
            $table->string('right_column_bg_color')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'layout_bg_color',
                'left_column_bg_color',
                'main_column_bg_color',
                'right_column_bg_color',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('layout_max_width')->nullable();
            $table->string('layout_padding_x')->nullable();
            $table->string('layout_padding_y')->nullable();
            $table->string('layout_gap')->nullable();
            $table->string('layout_left_width')->nullable();
            $table->string('layout_main_width')->nullable();
            $table->string('layout_right_width')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'layout_max_width',
                'layout_padding_x',
                'layout_padding_y',
                'layout_gap',
                'layout_left_width',
                'layout_main_width',
                'layout_right_width',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('layout_max_width_custom')->nullable();
            $table->string('layout_padding_x_custom')->nullable();
            $table->string('layout_padding_y_custom')->nullable();
            $table->string('layout_gap_custom')->nullable();
            $table->string('layout_left_width_custom')->nullable();
            $table->string('layout_main_width_custom')->nullable();
            $table->string('layout_right_width_custom')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->dropColumn([
                'layout_max_width_custom',
                'layout_padding_x_custom',
                'layout_padding_y_custom',
                'layout_gap_custom',
                'layout_left_width_custom',
                'layout_main_width_custom',
                'layout_right_width_custom',
            ]);
        });
    }
};

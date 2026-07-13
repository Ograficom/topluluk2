<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table): void {
            $table->string('header_max_width')->nullable()->after('header_bg_color');
            $table->string('header_search_width')->nullable()->after('header_max_width');
            $table->string('header_search_border_color')->nullable()->after('header_search_width');
            $table->string('header_search_input_bg_color')->nullable()->after('header_search_border_color');
            $table->string('header_search_dropdown_bg_color')->nullable()->after('header_search_input_bg_color');
            $table->string('header_search_text_color')->nullable()->after('header_search_dropdown_bg_color');
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'header_max_width',
                'header_search_width',
                'header_search_border_color',
                'header_search_input_bg_color',
                'header_search_dropdown_bg_color',
                'header_search_text_color',
            ]);
        });
    }
};


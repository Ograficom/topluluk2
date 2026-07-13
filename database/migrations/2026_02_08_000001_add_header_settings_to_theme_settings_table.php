<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table): void {
            $table->string('header_height')->nullable();
            $table->string('header_padding_x')->nullable();
            $table->string('header_padding_y')->nullable();
            $table->string('header_left_width')->nullable();
            $table->string('header_right_width')->nullable();
            $table->string('header_bg_color')->nullable();
            $table->string('header_logo_text')->nullable();
            $table->string('header_logo_image')->nullable();
            $table->string('header_logo_url')->nullable();
            $table->string('header_logo_alt')->nullable();
            $table->string('header_login_label')->nullable();
            $table->string('header_login_url')->nullable();
            $table->text('header_user_menu_html')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'header_height',
                'header_padding_x',
                'header_padding_y',
                'header_left_width',
                'header_right_width',
                'header_bg_color',
                'header_logo_text',
                'header_logo_image',
                'header_logo_url',
                'header_logo_alt',
                'header_login_label',
                'header_login_url',
                'header_user_menu_html',
            ]);
        });
    }
};

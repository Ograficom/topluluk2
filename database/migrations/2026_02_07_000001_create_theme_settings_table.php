<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->longText('header_html')->nullable();
            $table->longText('left_column_html')->nullable();
            $table->longText('right_column_html')->nullable();
            $table->longText('home_html')->nullable();
            $table->longText('messages_html')->nullable();
            $table->longText('notifications_html')->nullable();
            $table->longText('categories_html')->nullable();
            $table->longText('tags_html')->nullable();
            $table->longText('profile_html')->nullable();
            $table->longText('index_html')->nullable();
            $table->longText('post_show_html')->nullable();
            $table->timestamps();
        });

        DB::table('theme_settings')->insert([
            'header_html' => null,
            'left_column_html' => null,
            'right_column_html' => null,
            'home_html' => null,
            'messages_html' => null,
            'notifications_html' => null,
            'categories_html' => null,
            'tags_html' => null,
            'profile_html' => null,
            'index_html' => null,
            'post_show_html' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};

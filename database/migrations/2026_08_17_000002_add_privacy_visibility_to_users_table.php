<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('following_visibility', 16)->default('public')->after('preferred_locale');
            $table->string('posts_visibility', 16)->default('public')->after('following_visibility');
            $table->string('comments_visibility', 16)->default('public')->after('posts_visibility');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['following_visibility', 'posts_visibility', 'comments_visibility']);
        });
    }
};

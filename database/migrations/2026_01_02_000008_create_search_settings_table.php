<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('include_posts')->default(true);
            $table->boolean('include_post_content')->default(true);
            $table->boolean('include_categories')->default(true);
            $table->boolean('include_tags')->default(true);
            $table->boolean('include_users')->default(true);
            $table->unsignedSmallInteger('limit_per_type')->default(5);
            $table->unsignedSmallInteger('min_query_length')->default(2);
            $table->timestamps();
        });

        DB::table('search_settings')->insert([
            'is_enabled' => true,
            'include_posts' => true,
            'include_post_content' => true,
            'include_categories' => true,
            'include_tags' => true,
            'include_users' => true,
            'limit_per_type' => 5,
            'min_query_length' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('search_settings');
    }
};

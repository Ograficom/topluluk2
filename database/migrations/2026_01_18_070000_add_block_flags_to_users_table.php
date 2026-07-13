<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('block_messages')->default(false)->after('role');
            $table->boolean('block_posts')->default(false)->after('block_messages');
            $table->boolean('block_categories')->default(false)->after('block_posts');
            $table->boolean('block_tags')->default(false)->after('block_categories');
            $table->boolean('block_comments')->default(false)->after('block_tags');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'block_messages',
                'block_posts',
                'block_categories',
                'block_tags',
                'block_comments',
            ]);
        });
    }
};

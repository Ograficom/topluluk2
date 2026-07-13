<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'comments_disabled')) {
                $table->boolean('comments_disabled')->default(false)->after('is_pinned');
            }

            if (!Schema::hasColumn('posts', 'is_nsfw')) {
                $table->boolean('is_nsfw')->default(false)->after('comments_disabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'is_nsfw')) {
                $table->dropColumn('is_nsfw');
            }

            if (Schema::hasColumn('posts', 'comments_disabled')) {
                $table->dropColumn('comments_disabled');
            }
        });
    }
};

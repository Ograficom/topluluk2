<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('posts', 'is_pinned')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->boolean('is_pinned')->default(false)->after('is_published');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'is_pinned')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('is_pinned');
            });
        }
    }
};

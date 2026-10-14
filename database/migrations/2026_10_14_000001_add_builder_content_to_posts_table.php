<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'builder_content')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->json('builder_content')->nullable()->after('content_json');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'builder_content')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropColumn('builder_content');
            });
        }
    }
};

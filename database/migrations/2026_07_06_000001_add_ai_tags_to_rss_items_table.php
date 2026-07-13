<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rss_items', function (Blueprint $table) {
            $table->json('ai_tags')->nullable()->after('ai_content');
        });
    }

    public function down(): void
    {
        Schema::table('rss_items', function (Blueprint $table) {
            $table->dropColumn('ai_tags');
        });
    }
};

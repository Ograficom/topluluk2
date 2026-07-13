<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rss_feeds', function (Blueprint $table) {
            $table->boolean('fetch_dom_content')->default(true)->after('auto_publish');
        });
    }

    public function down(): void
    {
        Schema::table('rss_feeds', function (Blueprint $table) {
            $table->dropColumn('fetch_dom_content');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rss_items', function (Blueprint $table): void {
            $table->json('image_urls')->nullable()->after('image_url');
            $table->json('tags')->nullable()->after('image_urls');
        });
    }

    public function down(): void
    {
        Schema::table('rss_items', function (Blueprint $table): void {
            $table->dropColumn(['image_urls', 'tags']);
        });
    }
};

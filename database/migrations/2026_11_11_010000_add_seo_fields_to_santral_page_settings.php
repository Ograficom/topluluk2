<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santral_page_settings', function (Blueprint $table): void {
            $table->string('seo_title', 180)->nullable();
            $table->text('seo_description')->nullable();
            $table->json('seo_tags')->nullable();
            $table->string('featured_image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('santral_page_settings', function (Blueprint $table): void {
            $table->dropColumn(['seo_title', 'seo_description', 'seo_tags', 'featured_image']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seo_page_settings')) {
            Schema::create('seo_page_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('route_name', 120)->unique();
                $table->string('title', 255)->nullable();
                $table->text('description')->nullable();
                $table->text('keywords')->nullable();
                $table->string('canonical_url', 2048)->nullable();
                $table->string('og_image', 2048)->nullable();
                $table->boolean('robots_index')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_page_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_meta_title')->nullable();
            $table->text('site_meta_description')->nullable();
            $table->string('site_meta_keywords')->nullable();
            $table->string('og_site_name')->nullable();
            $table->string('og_default_title')->nullable();
            $table->text('og_default_description')->nullable();
            $table->string('og_url')->nullable();
            $table->string('og_type')->default('website');
            $table->string('og_default_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};

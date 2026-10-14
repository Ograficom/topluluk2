<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table): void {
            $table->json('component_styles')->nullable();
            $table->json('page_layouts')->nullable();
        });

        Schema::create('theme_design_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('theme_setting_id')->constrained('theme_settings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 120)->default('Otomatik yedek');
            $table->json('component_styles');
            $table->json('page_layouts')->nullable();
            $table->timestamps();

            $table->index(['theme_setting_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_design_revisions');

        Schema::table('theme_settings', function (Blueprint $table): void {
            $table->dropColumn(['component_styles', 'page_layouts']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('santral_page_settings')) {
            return;
        }

        Schema::table('santral_page_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('santral_page_settings', 'seo_title')) {
                $table->string('seo_title', 180)->nullable();
            }

            if (! Schema::hasColumn('santral_page_settings', 'seo_description')) {
                $table->text('seo_description')->nullable();
            }

            if (! Schema::hasColumn('santral_page_settings', 'seo_tags')) {
                $table->json('seo_tags')->nullable();
            }

            if (! Schema::hasColumn('santral_page_settings', 'featured_image')) {
                $table->string('featured_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('santral_page_settings')) {
            return;
        }

        Schema::table('santral_page_settings', function (Blueprint $table): void {
            $columns = array_filter(
                ['seo_title', 'seo_description', 'seo_tags', 'featured_image'],
                fn (string $column): bool => Schema::hasColumn('santral_page_settings', $column)
            );

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

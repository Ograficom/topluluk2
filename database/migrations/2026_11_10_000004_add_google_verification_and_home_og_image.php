<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'google_site_verification')) {
                $table->string('google_site_verification', 255)->nullable();
            }

            if (! Schema::hasColumn('site_settings', 'home_seo_og_image')) {
                $table->string('home_seo_og_image', 2048)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        Schema::table('site_settings', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('site_settings', 'google_site_verification')) {
                $columns[] = 'google_site_verification';
            }
            if (Schema::hasColumn('site_settings', 'home_seo_og_image')) {
                $columns[] = 'home_seo_og_image';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

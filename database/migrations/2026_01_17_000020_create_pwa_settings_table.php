<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pwa_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('install_banner_enabled')->default(true);
            $table->boolean('twa_enabled')->default(false);
            $table->string('app_name')->nullable();
            $table->string('short_name')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('theme_color', 20)->nullable();
            $table->string('background_color', 20)->nullable();
            $table->string('display', 30)->nullable();
            $table->string('start_url', 255)->nullable();
            $table->string('scope', 255)->nullable();
            $table->string('orientation', 30)->nullable();
            $table->string('lang', 10)->nullable();
            $table->string('dir', 10)->nullable();
            $table->json('categories')->nullable();
            $table->json('shortcuts')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('icon_192')->nullable();
            $table->string('icon_512')->nullable();
            $table->string('icon_maskable_192')->nullable();
            $table->string('icon_maskable_512')->nullable();
            $table->string('login_hero_image')->nullable();
            $table->string('install_banner_title')->nullable();
            $table->string('install_banner_description', 500)->nullable();
            $table->string('install_banner_button_label')->nullable();
            $table->string('twa_package_id')->nullable();
            $table->string('twa_fallback_url')->nullable();
            $table->json('twa_sha256_cert_fingerprints')->nullable();
            $table->timestamps();
        });

        DB::table('pwa_settings')->insert([
            'is_enabled' => true,
            'install_banner_enabled' => true,
            'twa_enabled' => false,
            'app_name' => config('app.name', 'OGrafi'),
            'short_name' => 'OGrafi',
            'description' => 'OGrafi: paylasimlar, kategoriler ve etiketler.',
            'theme_color' => '#111827',
            'background_color' => '#ffffff',
            'display' => 'standalone',
            'start_url' => '/',
            'scope' => '/',
            'orientation' => 'portrait',
            'lang' => 'tr',
            'dir' => 'ltr',
            'categories' => json_encode(['education', 'lifestyle']),
            'shortcuts' => json_encode([]),
            'screenshots' => json_encode([]),
            'install_banner_title' => 'Uygulamayi yukle',
            'install_banner_description' => 'OGrafi uygulamasini ana ekrana ekleyin.',
            'install_banner_button_label' => 'Yukle',
            'twa_sha256_cert_fingerprints' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pwa_settings');
    }
};

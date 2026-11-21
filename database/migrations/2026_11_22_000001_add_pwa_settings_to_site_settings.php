<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->boolean('pwa_enabled')->default(true);
            $table->string('pwa_name', 80)->default('OgraFi');
            $table->string('pwa_short_name', 24)->default('OgraFi');
            $table->string('pwa_description', 250)->nullable();
            $table->string('pwa_start_url', 255)->default('/');
            $table->string('pwa_display', 20)->default('standalone');
            $table->string('pwa_orientation', 20)->default('any');
            $table->string('pwa_theme_color', 7)->default('#2563eb');
            $table->string('pwa_background_color', 7)->default('#f7f9fd');
            $table->string('pwa_icon_192_path')->nullable();
            $table->string('pwa_icon_512_path')->nullable();
            $table->string('pwa_maskable_icon_512_path')->nullable();
            $table->string('pwa_offline_title', 100)->default('İnternet bağlantısı yok');
            $table->string('pwa_offline_message', 500)->default('Bağlantın yeniden geldiğinde sayfayı yenileyebilirsin.');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'pwa_enabled', 'pwa_name', 'pwa_short_name', 'pwa_description',
                'pwa_start_url', 'pwa_display', 'pwa_orientation', 'pwa_theme_color',
                'pwa_background_color', 'pwa_icon_192_path', 'pwa_icon_512_path',
                'pwa_maskable_icon_512_path', 'pwa_offline_title', 'pwa_offline_message',
            ]);
        });
    }
};

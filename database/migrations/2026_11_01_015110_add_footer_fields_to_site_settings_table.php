<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('footer_year')->nullable();
            $table->string('instagram_url', 2048)->nullable();
            $table->string('telegram_url', 2048)->nullable();
            $table->string('threads_url', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'footer_year',
                'instagram_url',
                'telegram_url',
                'threads_url',
            ]);
        });
    }
};

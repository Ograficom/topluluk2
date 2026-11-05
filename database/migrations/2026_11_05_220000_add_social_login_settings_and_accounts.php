<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->boolean('google_login_enabled')->default(false);
            $table->string('google_client_id')->nullable();
            $table->text('google_client_secret')->nullable();
            $table->boolean('yandex_login_enabled')->default(false);
            $table->string('yandex_client_id')->nullable();
            $table->text('yandex_client_secret')->nullable();
        });

        Schema::create('social_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 20);
            $table->string('provider_user_id', 191);
            $table->string('provider_email')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_user_id']);
            $table->unique(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'google_login_enabled',
                'google_client_id',
                'google_client_secret',
                'yandex_login_enabled',
                'yandex_client_id',
                'yandex_client_secret',
            ]);
        });
    }
};

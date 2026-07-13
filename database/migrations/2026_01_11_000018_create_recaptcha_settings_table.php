<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recaptcha_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('login_enabled')->default(true);
            $table->boolean('register_enabled')->default(true);
            $table->boolean('comment_enabled')->default(true);
            $table->decimal('minimum_score', 3, 2)->default(0.50);
            $table->boolean('verify_action')->default(true);
            $table->string('site_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->string('allowed_hostnames')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recaptcha_settings');
    }
};


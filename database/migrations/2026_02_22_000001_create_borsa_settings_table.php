<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borsa_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('symbols')->nullable();
            $table->string('response_path')->nullable();
            $table->string('symbol_key')->nullable()->default('symbol');
            $table->string('price_key')->nullable()->default('price');
            $table->string('change_key')->nullable()->default('change');
            $table->unsignedInteger('cache_seconds')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borsa_settings');
    }
};

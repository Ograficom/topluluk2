<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('license_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_hash', 64)->unique()->comment('SHA-256 hash of the license key');
            $table->string('label')->nullable()->comment('Human-readable label, e.g. "Production server"');
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->timestamp('activated_at')->nullable();
            $table->string('domain')->nullable();
            $table->integer('max_domains')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_keys');
    }
};

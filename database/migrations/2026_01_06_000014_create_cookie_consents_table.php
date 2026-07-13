<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookie_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cookie_policy_id')->constrained('cookie_policies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_id', 64)->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedInteger('policy_version')->default(1)->index();
            $table->boolean('accepted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consents');
    }
};

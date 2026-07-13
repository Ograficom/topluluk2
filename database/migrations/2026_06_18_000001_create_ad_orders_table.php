<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('placement', 80);
            $table->unsignedSmallInteger('duration_days');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedInteger('price_cents');
            $table->string('currency', 3)->default('TRY');
            $table->string('title')->nullable();
            $table->string('target_url')->nullable();
            $table->string('image_path');
            $table->string('status', 40)->default('pending_payment');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['placement', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_orders');
    }
};

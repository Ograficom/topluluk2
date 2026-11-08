<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->string('target_url', 2048);
            $table->string('button_text', 40)->default('Daha fazla bilgi al');
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->string('status', 24)->default('pending')->index();
            $table->string('billing_type', 16)->default('paid');
            $table->decimal('total_budget', 10, 2)->default(0);
            $table->decimal('daily_budget', 10, 2)->default(0);
            $table->decimal('spent_amount', 10, 2)->default(0);
            $table->string('payment_status', 24)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
            $table->index(['status', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};

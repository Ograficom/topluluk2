<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_task_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('task_key', 64);
            $table->string('period_key', 32)->default('lifetime');
            $table->unsignedInteger('progress')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->json('reward_badge')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'task_key', 'period_key'], 'user_task_progress_unique');
            $table->index(['completed_at', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_task_progress');
    }
};

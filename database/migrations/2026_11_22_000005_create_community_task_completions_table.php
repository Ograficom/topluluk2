<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_task_completions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('task_key', 100);
            $table->string('period_key', 30);
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamp('claimed_at');
            $table->timestamps();
            $table->unique(['user_id', 'task_key', 'period_key'], 'community_task_user_period_unique');
            $table->index(['task_key', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_task_completions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reputation_settings', 'tasks')) {
            Schema::table('reputation_settings', function (Blueprint $table): void {
                $table->json('tasks')->nullable();
            });
        }

        if (! Schema::hasTable('task_completions')) {
            Schema::create('task_completions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('task_key', 120);
                $table->string('task_name', 120);
                $table->unsignedInteger('reward_points')->default(0);
                $table->json('badge')->nullable();
                $table->timestamp('completed_at');
                $table->timestamps();
                $table->unique(['user_id', 'task_key']);
                $table->index('completed_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_completions');

        if (Schema::hasColumn('reputation_settings', 'tasks')) {
            Schema::table('reputation_settings', function (Blueprint $table): void {
                $table->dropColumn('tasks');
            });
        }
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_participations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('task_key', 120);
            $table->string('task_name', 120);
            $table->string('external_username', 100)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->unique(['user_id', 'task_key']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_participations');
    }
};

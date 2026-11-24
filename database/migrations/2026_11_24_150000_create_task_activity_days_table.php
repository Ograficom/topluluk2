<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_activity_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity', 40);
            $table->date('day');
            $table->timestamp('created_at');
            $table->unique(['user_id', 'activity', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activity_days');
    }
};

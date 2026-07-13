<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('fingerprint', 40)->nullable();
            $table->boolean('is_like')->default(true);
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
            $table->unique(['comment_id', 'fingerprint']);
            $table->index(['comment_id', 'is_like']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};


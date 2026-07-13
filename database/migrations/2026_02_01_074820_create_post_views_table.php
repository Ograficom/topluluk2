<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 64)->nullable();
            $table->string('view_key', 128);
            $table->timestamps();

            $table->unique(['post_id', 'view_key']);
            $table->index(['post_id', 'user_id']);
            $table->index(['post_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_views');
    }
};

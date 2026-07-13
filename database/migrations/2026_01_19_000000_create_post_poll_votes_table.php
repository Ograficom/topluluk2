<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('block_id', 64);
            $table->unsignedInteger('option_index');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('device_id', 100)->nullable();
            $table->timestamps();

            $table->index(['post_id', 'block_id']);
            $table->unique(['post_id', 'block_id', 'user_id']);
            $table->unique(['post_id', 'block_id', 'device_id']);
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_poll_votes');
    }
};

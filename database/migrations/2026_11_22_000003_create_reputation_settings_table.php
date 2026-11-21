<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reputation_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->boolean('exclude_company_accounts')->default(true);
            $table->unsignedInteger('post_points')->default(10);
            $table->unsignedInteger('comment_points')->default(2);
            $table->unsignedInteger('post_like_points')->default(3);
            $table->unsignedInteger('comment_like_points')->default(2);
            $table->json('reward_badges')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reputation_settings');
    }
};

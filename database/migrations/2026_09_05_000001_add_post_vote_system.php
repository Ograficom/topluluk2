<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'votes_enabled')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->boolean('votes_enabled')->default(true)->after('comments_disabled');
            });
        }

        if (! Schema::hasTable('post_votes')) {
            Schema::create('post_votes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->tinyInteger('value');
                $table->timestamps();

                $table->unique(['post_id', 'user_id']);
                $table->index(['post_id', 'value']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_votes');

        if (Schema::hasColumn('posts', 'votes_enabled')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropColumn('votes_enabled');
            });
        }
    }
};

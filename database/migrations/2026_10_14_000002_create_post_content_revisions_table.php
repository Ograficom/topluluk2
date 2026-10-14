<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('post_content_revisions')) {
            return;
        }

        Schema::create('post_content_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('revision_type', 32)->default('content');
            $table->string('title')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->json('content_json')->nullable();
            $table->json('builder_content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('snapshot')->nullable();
            $table->string('revision_reason', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_content_revisions');
    }
};

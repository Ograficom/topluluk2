<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('url', 2048);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('import_as_posts')->default(true);
            $table->boolean('auto_publish')->default(false);
            $table->boolean('update_existing_posts')->default(true);
            $table->foreignId('default_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('default_author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('etag')->nullable();
            $table->string('last_modified')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};


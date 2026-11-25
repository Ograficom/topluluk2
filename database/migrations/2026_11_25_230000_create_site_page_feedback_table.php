<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_page_feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_page_id')->constrained('site_pages')->cascadeOnDelete();
            $table->string('voter_key', 64);
            $table->string('vote', 8);
            $table->string('email', 254)->nullable();
            $table->text('message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['site_page_id', 'voter_key']);
            $table->index(['site_page_id', 'vote']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_page_feedback');
    }
};

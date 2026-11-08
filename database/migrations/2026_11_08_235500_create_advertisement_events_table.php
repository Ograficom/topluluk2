<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('advertisement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_token', 64)->index();
            $table->string('event_type', 24)->index();
            $table->unsignedSmallInteger('seconds')->default(0);
            $table->string('referrer_host', 190)->nullable();
            $table->timestamps();
            $table->index(['advertisement_id', 'created_at']);
            $table->index(['advertisement_id', 'user_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_events');
    }
};

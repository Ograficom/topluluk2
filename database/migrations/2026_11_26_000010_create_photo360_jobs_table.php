<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('photo360_jobs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('expected');
            $table->unsignedSmallInteger('received')->default(0);
            $table->string('state', 24)->default('uploading');
            $table->string('message', 255)->default('Kareler bekleniyor.');
            $table->timestamps();
            $table->index(['user_id','state']);
            $table->index('updated_at');
        });
    }
    public function down(): void { Schema::dropIfExists('photo360_jobs'); }
};

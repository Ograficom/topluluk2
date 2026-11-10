<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::create('account_deletion_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name');
            $table->string('username')->nullable();
            $table->string('email');
            $table->string('status')->default('pending')->index();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_requests');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};

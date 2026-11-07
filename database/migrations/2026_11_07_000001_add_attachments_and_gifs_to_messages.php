<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table): void {
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime', 150)->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->text('gif_url')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table): void {
            $table->dropColumn(['attachment_path','attachment_name','attachment_mime','attachment_size','gif_url']);
        });
    }
};

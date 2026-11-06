<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('mailer', 30)->default('smtp');
            $table->string('scheme', 20)->default('smtps');
            $table->string('host')->default('smtp.hostinger.com');
            $table->unsignedSmallInteger('port')->default(465);
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->default('OgraFi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};

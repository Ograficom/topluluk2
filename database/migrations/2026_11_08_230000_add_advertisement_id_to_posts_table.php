<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('advertisement_id')->nullable()->unique()->after('user_id')
                ->constrained('advertisements')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('advertisement_id');
        });
    }
};

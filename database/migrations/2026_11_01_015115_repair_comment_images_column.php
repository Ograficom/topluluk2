<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('comments', 'images')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->json('images')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Keep the repaired column and uploaded comment images when rolling back.
    }
};

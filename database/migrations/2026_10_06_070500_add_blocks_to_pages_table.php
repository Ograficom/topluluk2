<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pages', 'blocks')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->json('blocks')->nullable()->after('content');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pages', 'blocks')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->dropColumn('blocks');
            });
        }
    }
};

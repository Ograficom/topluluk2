<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borsa_settings', function (Blueprint $table): void {
            if (!Schema::hasColumn('borsa_settings', 'base_symbol')) {
                $table->string('base_symbol')->nullable()->after('symbols');
            }
            if (!Schema::hasColumn('borsa_settings', 'pair_symbols')) {
                $table->string('pair_symbols')->nullable()->after('base_symbol');
            }
        });
    }

    public function down(): void
    {
        Schema::table('borsa_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('borsa_settings', 'pair_symbols')) {
                $table->dropColumn('pair_symbols');
            }
            if (Schema::hasColumn('borsa_settings', 'base_symbol')) {
                $table->dropColumn('base_symbol');
            }
        });
    }
};

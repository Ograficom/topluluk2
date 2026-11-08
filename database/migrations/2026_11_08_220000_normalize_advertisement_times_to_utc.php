<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Existing advertisement dates were entered as Turkey local time
        // but stored as UTC wall-clock values. Normalize them to real UTC.
        DB::table('advertisements')->update([
            'starts_at' => DB::raw('DATE_SUB(starts_at, INTERVAL 3 HOUR)'),
            'ends_at' => DB::raw('DATE_SUB(ends_at, INTERVAL 3 HOUR)'),
        ]);
    }

    public function down(): void
    {
        DB::table('advertisements')->update([
            'starts_at' => DB::raw('DATE_ADD(starts_at, INTERVAL 3 HOUR)'),
            'ends_at' => DB::raw('DATE_ADD(ends_at, INTERVAL 3 HOUR)'),
        ]);
    }
};

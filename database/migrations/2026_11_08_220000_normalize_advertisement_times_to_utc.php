<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->shiftAdvertisementTimes(-3);
    }

    public function down(): void
    {
        $this->shiftAdvertisementTimes(3);
    }

    private function shiftAdvertisementTimes(int $hours): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        $modifier = $hours > 0 ? '+' : '';
        $amount = abs($hours);

        $expression = match ($driver) {
            'sqlite' => fn (string $column) => DB::raw("datetime({$column}, '{$modifier}{$hours} hours')"),
            'pgsql' => fn (string $column) => DB::raw("{$column} + INTERVAL '{$hours} hours'"),
            'sqlsrv' => fn (string $column) => DB::raw("DATEADD(HOUR, {$hours}, {$column})"),
            default => fn (string $column) => DB::raw(
                $hours < 0
                    ? "DATE_SUB({$column}, INTERVAL {$amount} HOUR)"
                    : "DATE_ADD({$column}, INTERVAL {$amount} HOUR)"
            ),
        };

        DB::table('advertisements')->update([
            'starts_at' => $expression('starts_at'),
            'ends_at' => $expression('ends_at'),
        ]);
    }
};

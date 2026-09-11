<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recaptcha_settings')) {
            return;
        }

        $requiredColumns = [
            'block_vpn_logins',
            'block_tor_logins',
            'verify_unknown_devices',
            'bot_honeypot_enabled',
        ];

        foreach ($requiredColumns as $column) {
            if (! Schema::hasColumn('recaptcha_settings', $column)) {
                return;
            }
        }

        DB::table('recaptcha_settings')->update([
            'block_vpn_logins' => true,
            'block_tor_logins' => true,
            'verify_unknown_devices' => true,
            'bot_honeypot_enabled' => true,
        ]);
    }

    public function down(): void
    {
        // Security flags are intentionally not disabled on rollback.
    }
};

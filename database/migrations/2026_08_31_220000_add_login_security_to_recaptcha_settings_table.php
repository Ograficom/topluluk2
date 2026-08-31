<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recaptcha_settings')) {
            return;
        }

        Schema::table('recaptcha_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('recaptcha_settings', 'block_vpn_logins')) {
                $table->boolean('block_vpn_logins')->default(true);
            }

            if (! Schema::hasColumn('recaptcha_settings', 'block_tor_logins')) {
                $table->boolean('block_tor_logins')->default(true);
            }

            if (! Schema::hasColumn('recaptcha_settings', 'verify_unknown_devices')) {
                $table->boolean('verify_unknown_devices')->default(true);
            }

            if (! Schema::hasColumn('recaptcha_settings', 'trusted_device_days')) {
                $table->unsignedSmallInteger('trusted_device_days')->default(90);
            }

            if (! Schema::hasColumn('recaptcha_settings', 'bot_honeypot_enabled')) {
                $table->boolean('bot_honeypot_enabled')->default(true);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('recaptcha_settings')) {
            return;
        }

        $columns = collect([
            'block_vpn_logins',
            'block_tor_logins',
            'verify_unknown_devices',
            'trusted_device_days',
            'bot_honeypot_enabled',
        ])->filter(fn (string $column) => Schema::hasColumn('recaptcha_settings', $column))->all();

        if ($columns !== []) {
            Schema::table('recaptcha_settings', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};

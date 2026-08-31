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

        Schema::table('recaptcha_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('recaptcha_settings', 'forgot_password_enabled')) {
                $table->boolean('forgot_password_enabled')->default(true)->after('register_enabled');
            }

            if (! Schema::hasColumn('recaptcha_settings', 'admin_enabled')) {
                $table->boolean('admin_enabled')->default(true)->after('forgot_password_enabled');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('recaptcha_settings')) {
            return;
        }

        Schema::table('recaptcha_settings', function (Blueprint $table): void {
            $columns = [];

            if (Schema::hasColumn('recaptcha_settings', 'forgot_password_enabled')) {
                $columns[] = 'forgot_password_enabled';
            }

            if (Schema::hasColumn('recaptcha_settings', 'admin_enabled')) {
                $columns[] = 'admin_enabled';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

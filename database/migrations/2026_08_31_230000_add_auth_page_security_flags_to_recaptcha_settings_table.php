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
                $table->boolean('forgot_password_enabled')->default(true);
            }

            if (! Schema::hasColumn('recaptcha_settings', 'admin_enabled')) {
                $table->boolean('admin_enabled')->default(true);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('recaptcha_settings')) {
            return;
        }

        $columns = collect([
            'forgot_password_enabled',
            'admin_enabled',
        ])->filter(fn (string $column) => Schema::hasColumn('recaptcha_settings', $column))->all();

        if ($columns !== []) {
            Schema::table('recaptcha_settings', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};

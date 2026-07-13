<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class InstallState
{
    public static function isInstalled(): bool
    {
        if (!filter_var(env('APP_INSTALL_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        if ((bool) env('APP_INSTALLED', false)) {
            return true;
        }

        if (file_exists(self::lockPath())) {
            return true;
        }

        try {
            return Schema::hasTable('users');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function markInstalled(): void
    {
        $dir = dirname(self::lockPath());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists(self::lockPath())) {
            file_put_contents(self::lockPath(), date('c'));
        }
    }

    public static function lockPath(): string
    {
        return storage_path('app/installed.lock');
    }
}

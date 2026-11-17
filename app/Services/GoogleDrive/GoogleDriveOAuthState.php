<?php

namespace App\Services\GoogleDrive;

class GoogleDriveOAuthState
{
    public static function issue(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function matches(?string $expected, ?string $received): bool
    {
        return is_string($expected)
            && $expected !== ''
            && is_string($received)
            && $received !== ''
            && hash_equals($expected, $received);
    }
}

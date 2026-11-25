<?php

namespace App\Support;

class PwaCapabilities
{
    public static function scopeOrigins(?string $value): array
    {
        $origins = preg_split('/\s+/', trim($value ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        return array_slice(array_values(array_unique(array_filter($origins,
            fn (string $origin): bool => (bool) preg_match('/^https:\/\/(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+ografi\.com$/D', $origin)
        ))), 0, 10);
    }
}

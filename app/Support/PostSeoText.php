<?php

namespace App\Support;

use Illuminate\Support\Str;

final class PostSeoText
{
    public static function title(?string $title): ?string
    {
        $title = self::plain($title);

        return $title === '' ? null : Str::limit($title, 65, '');
    }

    public static function description(?string ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $text = self::plain($candidate);
            if ($text === '') {
                continue;
            }

            return Str::limit($text, 160, '');
        }

        return null;
    }

    public static function plain(?string $value): string
    {
        $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}

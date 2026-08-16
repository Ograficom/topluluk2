<?php

namespace App\Support;

final class StructuredDataUrl
{
    public static function sameAs(?string $value, string $platform = 'website'): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $value)) {
            return self::validPublicUrl($value);
        }

        if (preg_match('~^(?:www\.)?[a-z0-9.-]+\.[a-z]{2,}(?:[/?#].*)?$~i', $value)) {
            return self::validPublicUrl('https://' . $value);
        }

        if ($platform === 'whatsapp') {
            $url = self::whatsAppUrl($value);

            return $url ? self::validPublicUrl($url) : null;
        }

        $handle = ltrim($value, '@');
        if ($handle === '' || !preg_match('/^[a-z0-9._-]+$/i', $handle)) {
            return null;
        }

        $url = match ($platform) {
            'facebook' => 'https://www.facebook.com/' . rawurlencode($handle),
            'instagram' => 'https://www.instagram.com/' . rawurlencode($handle),
            'x' => 'https://x.com/' . rawurlencode($handle),
            'tiktok' => 'https://www.tiktok.com/@' . rawurlencode($handle),
            'youtube' => 'https://www.youtube.com/@' . rawurlencode($handle),
            default => null,
        };

        return $url ? self::validPublicUrl($url) : null;
    }

    private static function whatsAppUrl(string $value): ?string
    {
        $number = preg_replace('/\D+/', '', $value);
        if (str_starts_with($number, '0')) {
            $number = '90' . substr($number, 1);
        }

        return strlen($number) >= 7 ? 'https://wa.me/' . $number : null;
    }

    private static function validPublicUrl(string $value): ?string
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($value);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));

        if (!in_array($scheme, ['http', 'https'], true)
            || $host === ''
            || !str_contains($host, '.')
            || isset($parts['user'])
            || isset($parts['pass'])
            || $host === 'localhost'
        ) {
            return null;
        }

        return $value;
    }
}

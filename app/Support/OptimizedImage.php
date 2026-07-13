<?php

namespace App\Support;

use Illuminate\Support\Str;

class OptimizedImage
{
    public static function variantUrl(?string $url, string $variant): ?string
    {
        $publicPath = static::publicPathFromUrl($url);

        if (!$publicPath) {
            return null;
        }

        $directory = pathinfo($publicPath, PATHINFO_DIRNAME);
        $filename = pathinfo($publicPath, PATHINFO_FILENAME);

        foreach (['webp', 'jpg', 'jpeg', 'png'] as $extension) {
            $candidate = $directory . DIRECTORY_SEPARATOR . $filename . '--' . $variant . '.' . $extension;

            if (is_file($candidate)) {
                return static::urlFromPublicPath($candidate);
            }
        }

        return null;
    }

    public static function dimensions(?string $url, array $fallback = [null, null]): array
    {
        $publicPath = static::publicPathFromUrl($url);

        if ($publicPath && is_file($publicPath)) {
            $size = @getimagesize($publicPath);

            if (is_array($size) && !empty($size[0]) && !empty($size[1])) {
                return [(int) $size[0], (int) $size[1]];
            }
        }

        return [
            (int) ($fallback[0] ?? 0),
            (int) ($fallback[1] ?? 0),
        ];
    }

    public static function publicPathFromUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || Str::startsWith($url, 'data:')) {
            return null;
        }

        if (Str::startsWith($url, '/storage/')) {
            return public_path(ltrim($url, '/'));
        }

        if (Str::startsWith($url, 'storage/')) {
            return public_path('storage/' . ltrim(Str::after($url, 'storage/'), '/'));
        }

        $parts = parse_url($url);
        $path = (string) ($parts['path'] ?? '');

        if ($path !== '' && Str::startsWith($path, '/storage/')) {
            return public_path(ltrim($path, '/'));
        }

        $relativePath = ltrim($path !== '' ? $path : $url, '/');
        $directPublicPath = public_path($relativePath);

        if (is_file($directPublicPath)) {
            return $directPublicPath;
        }

        $storagePublicPath = public_path('storage/' . ltrim(Str::after($relativePath, 'storage/'), '/'));

        return is_file($storagePublicPath) ? $storagePublicPath : null;
    }

    private static function urlFromPublicPath(string $publicPath): string
    {
        $relativePath = Str::after($publicPath, public_path() . DIRECTORY_SEPARATOR);
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

        return asset($relativePath);
    }
}

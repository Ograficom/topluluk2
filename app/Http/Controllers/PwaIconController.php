<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PwaIconController extends Controller
{
    private const ICONS = [
        '192' => ['pwa_icon_192_path', 192, 'any', 'pwa-icon-192.png'],
        '512' => ['pwa_icon_512_path', 512, 'any', 'pwa-icon-512.png'],
        'maskable-512' => ['pwa_maskable_icon_512_path', 512, 'maskable', 'pwa-icon-maskable-512.png'],
    ];

    public static function manifestIcons(?SiteSetting $settings): array
    {
        $revision = substr(hash('sha256', json_encode([
            'ografi-blue-20261125-v2',
            $settings?->pwa_icon_192_path,
            $settings?->pwa_icon_512_path,
            $settings?->pwa_maskable_icon_512_path,
            $settings?->updated_at?->format('Y-m-d H:i:s.u'),
        ])), 0, 16);

        $icons = [];
        $hasStandardIcon = self::uploadedPng($settings?->pwa_icon_192_path, 192) !== null
            || self::uploadedPng($settings?->pwa_icon_512_path, 512) !== null;
        $canResize = function_exists('imagecreatefromstring') && function_exists('imagepng');
        foreach (self::ICONS as $icon => [$column, $size, $purpose, $fallback]) {
            // Use an actual public PNG for defaults. Uploaded icons use an
            // extensionless Laravel route because some web servers intercept
            // requests ending in .png before they reach the application.
            $custom = self::uploadedPng($settings?->{$column}, $size) !== null
                || ($hasStandardIcon && $canResize);
            $icons[] = [
                'src' => $custom
                    ? route('pwa.icon', ['icon' => $icon, 'v' => $revision], false)
                    : '/assets/img/meta/'.$fallback.'?v='.$revision,
                'sizes' => $size.'x'.$size,
                'type' => 'image/png',
                'purpose' => $purpose,
            ];
        }
        return $icons;
    }

    public function show(string $icon): Response
    {
        abort_unless(isset(self::ICONS[$icon]), 404);
        [$column, $size, $purpose, $fallback] = self::ICONS[$icon];
        $settings = SiteSetting::current();

        // The PWA management panel is authoritative. Do not replace its
        // uploads with the website header logo or depend on /storage symlinks.
        $png = self::uploadedPng($settings?->{$column}, $size);

        // If only one standard icon was uploaded, derive the missing size
        // when GD is available. Maskable images get a padded white background.
        if ($png === null && function_exists('imagecreatefromstring') && function_exists('imagepng')) {
            $source = self::uploadedPng($settings?->pwa_icon_512_path, 512)
                ?? self::uploadedPng($settings?->pwa_icon_192_path, 192);
            if ($source !== null) {
                $png = $this->resize($source, $size, $purpose === 'maskable');
            }
        }

        $png ??= file_get_contents(public_path('assets/img/meta/'.$fallback));

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, max-age=0, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public static function uploadedPng(?string $path, int $size): ?string
    {
        if (blank($path)) return null;
        try {
            $disk = Storage::disk('public');
            if (! $disk->exists($path) || $disk->size($path) > 5 * 1024 * 1024) return null;
            $contents = $disk->get($path);
            if (! is_string($contents)) return null;
            $info = @getimagesizefromstring($contents);
            if ($info && $info[0] === $size && $info[1] === $size && $info[2] === IMAGETYPE_PNG) {
                return $contents;
            }
        } catch (\Throwable) {
            // A missing or unreadable upload must not break the manifest.
        }
        return null;
    }

    private function resize(string $bytes, int $size, bool $maskable): ?string
    {
        $source = @imagecreatefromstring($bytes);
        if (! $source) return null;
        $canvas = imagecreatetruecolor($size, $size);
        if (! $canvas) return null;
        if ($maskable) {
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            $area = (int) floor($size * 0.56);
        } else {
            imagealphablending($canvas, false);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagesavealpha($canvas, true);
            $area = $size;
        }
        $offset = (int) floor(($size - $area) / 2);
        imagecopyresampled($canvas, $source, $offset, $offset, 0, 0, $area, $area, imagesx($source), imagesy($source));
        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();
        return is_string($png) && $png !== '' ? $png : null;
    }
}

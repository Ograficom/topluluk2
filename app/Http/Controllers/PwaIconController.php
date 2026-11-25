<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PwaIconController extends Controller
{
    public function show(string $icon): Response
    {
        $size = $icon === '192' ? 192 : 512;
        $maskable = $icon === 'maskable-512';
        $settings = SiteSetting::current();
        $disk = Storage::disk('public');

        // Serve uploaded icons directly; a public storage symlink is not required.
        $primary = $maskable ? $settings?->pwa_maskable_icon_512_path
            : ($size === 192 ? $settings?->pwa_icon_192_path : $settings?->pwa_icon_512_path);
        $candidates = array_unique(array_filter([
            $primary, $settings?->pwa_icon_512_path, $settings?->pwa_icon_192_path,
            $settings?->favicon_path, $settings?->brand_logo_path,
        ]));

        foreach ($candidates as $path) {
            try {
                if (! $disk->exists($path) || $disk->size($path) > 10 * 1024 * 1024) {
                    continue;
                }
                $bytes = $disk->get($path);
                if (! is_string($bytes)) continue;
                $info = @getimagesizefromstring($bytes);
                if (! $info || $info[0] < 1 || $info[1] < 1
                    || $info[0] > 4096 || $info[1] > 4096
                    || ! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)) {
                    continue;
                }
                // A wide wordmark is not a suitable app icon.
                if ($info[0] / $info[1] > 1.5 || $info[1] / $info[0] > 1.5) continue;
                if ($info[2] === IMAGETYPE_PNG && $info[0] === $size && $info[1] === $size
                    && (! $maskable || $path === $primary)) {
                    return $this->png($bytes);
                }
                if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) continue;
                $source = @imagecreatefromstring($bytes);
                if (! $source) continue;
                $canvas = imagecreatetruecolor($size, $size);
                if (! $canvas) continue;
                if ($maskable) {
                    imagefill($canvas, 0, 0, imagecolorallocate($canvas, 13, 110, 253));
                    $area = (int) floor($size * 0.56);
                } else {
                    imagealphablending($canvas, false);
                    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
                    imagesavealpha($canvas, true);
                    $area = $size;
                }
                $scale = min($area / $info[0], $area / $info[1]);
                $width = max(1, (int) round($info[0] * $scale));
                $height = max(1, (int) round($info[1] * $scale));
                imagecopyresampled($canvas, $source, (int) (($size - $width) / 2),
                    (int) (($size - $height) / 2), 0, 0, $width, $height, $info[0], $info[1]);
                ob_start();
                imagepng($canvas);
                $png = ob_get_clean();
                if (is_string($png) && $png !== '') return $this->png($png);
            } catch (\Throwable $exception) {
                // Missing or unreadable uploaded images must not break the app manifest.
                continue;
            }
        }

        $fallback = $maskable ? 'pwa-icon-maskable-512.png' : 'pwa-icon-'.$size.'.png';
        return $this->png(file_get_contents(public_path('assets/img/meta/'.$fallback)));
    }

    private function png(string $contents): Response
    {
        return response($contents, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=0, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

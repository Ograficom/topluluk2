<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Support\SvgSanitizer;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SiteBrandingController extends Controller
{
    public function logo(Request $request): Response
    {
        $original = $this->serveAsset('brand_logo_path');
        $size = $request->query('size');
        if (! in_array($size, ['192', '512'], true)) return $original;

        $size = (int) $size;
        $maskable = $request->query('maskable') === '1';
        $contents = $original->getContent();
        $info = is_string($contents) ? @getimagesizefromstring($contents) : false;
        if ($info && $info[0] <= 4096 && $info[1] <= 4096
            && function_exists('imagecreatefromstring') && function_exists('imagepng')) {
            $source = @imagecreatefromstring($contents);
            if ($source) {
                $canvas = imagecreatetruecolor($size, $size);
                if ($canvas) {
                    if ($maskable) {
                        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
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
                    if (is_string($png) && $png !== '') {
                        return response($png)->header('Content-Type', 'image/png')->header('Cache-Control', 'no-store');
                    }
                }
            }
        }
        $fallback = $maskable ? 'pwa-icon-maskable-512.png' : 'pwa-icon-'.$size.'.png';
        return response(file_get_contents(public_path('assets/img/meta/'.$fallback)))
            ->header('Content-Type', 'image/png')->header('Cache-Control', 'no-store');
    }

    public function favicon(): Response
    {
        return $this->serveAsset('favicon_path');
    }

    public function updateLogo(Request $request, SvgSanitizer $svgSanitizer): RedirectResponse
    {
        return $this->updateAsset(
            $request,
            $svgSanitizer,
            'logo',
            'brand_logo_path',
            ['svg', 'png', 'jpg', 'jpeg', 'webp'],
            ['image/svg+xml', 'image/svg', 'image/png', 'image/jpeg', 'image/webp'],
            'Logo başarıyla güncellendi.',
        );
    }

    public function updateFavicon(Request $request, SvgSanitizer $svgSanitizer): RedirectResponse
    {
        return $this->updateAsset(
            $request,
            $svgSanitizer,
            'favicon',
            'favicon_path',
            ['svg', 'png', 'ico', 'webp'],
            ['image/svg+xml', 'image/svg', 'image/png', 'image/vnd.microsoft.icon', 'image/x-icon', 'image/ico', 'image/webp', 'application/octet-stream'],
            'Favicon başarıyla güncellendi.',
        );
    }

    private function updateAsset(
        Request $request,
        SvgSanitizer $svgSanitizer,
        string $input,
        string $column,
        array $extensions,
        array $mimeTypes,
        string $message,
    ): RedirectResponse {
        abort_unless($request->user()?->canAccessPanel(Filament::getPanel('admin')), 403);

        $validated = $request->validate([
            $input => [
                'required',
                'file',
                'max:2048',
                'mimetypes:'.implode(',', $mimeTypes),
                'extensions:'.implode(',', $extensions),
            ],
        ]);

        $file = $validated[$input];
        $mimeType = $file->getMimeType();
        $extension = match ($mimeType) {
            'image/svg+xml', 'image/svg' => 'svg',
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/vnd.microsoft.icon', 'image/x-icon', 'image/ico' => 'ico',
            'application/octet-stream' => strtolower($file->getClientOriginalExtension()) === 'ico'
                ? 'ico'
                : abort(422, 'Bu dosya türü desteklenmiyor.'),
            default => abort(422, 'Bu dosya türü desteklenmiyor.'),
        };
        $path = 'site-branding/'.Str::uuid().'.'.$extension;
        $disk = Storage::disk('public');

        if ($extension === 'svg') {
            try {
                $contents = $svgSanitizer->sanitize($file->getContent());
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    $input => 'SVG dosyası geçersiz veya güvenli biçimde işlenemedi.',
                ]);
            }

            if (! $disk->put($path, $contents)) {
                throw new \RuntimeException('Logo dosyası depolama alanına kaydedilemedi.');
            }

            $disk->setVisibility($path, 'public');
        } elseif (! $disk->putFileAs('site-branding', $file, basename($path), ['visibility' => 'public'])) {
            throw new \RuntimeException('Logo dosyası depolama alanına kaydedilemedi.');
        }

        $settings = SiteSetting::current() ?? new SiteSetting;
        $previousAsset = $settings->{$column};

        $settings->id = 1;
        $settings->{$column} = $path;
        $settings->save();

        if (filled($previousAsset) && $previousAsset !== $path) {
            $disk->delete($previousAsset);
        }

        return redirect()
            ->route('filament.admin.pages.site-branding')
            ->with('status', $message);
    }

    private function serveAsset(string $column): Response
    {
        $path = SiteSetting::current()?->{$column};
        abort_if(blank($path), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        $contentType = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            default => abort(404),
        };

        return response($disk->get($path))
            ->header('Content-Type', $contentType)
            ->header('Cache-Control', 'no-store');
    }
}

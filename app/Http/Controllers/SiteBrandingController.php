<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Support\SvgSanitizer;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SiteBrandingController extends Controller
{
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

        return back()->with('status', $message);
    }
}

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
        abort_unless(
            $request->user()?->canAccessPanel(Filament::getPanel('admin')),
            403,
        );

        $validated = $request->validate([
            'logo' => [
                'required',
                'file',
                'max:2048',
                'mimetypes:image/svg+xml,image/svg,image/png,image/jpeg,image/webp',
                'extensions:svg,png,jpg,jpeg,webp',
            ],
        ]);

        $logo = $validated['logo'];
        $mimeType = $logo->getMimeType();
        $extension = match ($mimeType) {
            'image/svg+xml', 'image/svg' => 'svg',
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => abort(422, 'Bu dosya türü logo olarak desteklenmiyor.'),
        };
        $path = 'site-branding/'.Str::uuid().'.'.$extension;
        $disk = Storage::disk('public');

        if ($extension === 'svg') {
            try {
                $contents = $svgSanitizer->sanitize($logo->getContent());
            } catch (\InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    'logo' => 'SVG dosyası geçersiz veya güvenli biçimde işlenemedi.',
                ]);
            }

            if (! $disk->put($path, $contents)) {
                throw new \RuntimeException('Logo dosyası depolama alanına kaydedilemedi.');
            }

            $disk->setVisibility($path, 'public');
        } elseif (! $disk->putFileAs('site-branding', $logo, basename($path), ['visibility' => 'public'])) {
            throw new \RuntimeException('Logo dosyası depolama alanına kaydedilemedi.');
        }

        $settings = SiteSetting::current() ?? new SiteSetting;
        $previousLogo = $settings->brand_logo_path;

        $settings->id = 1;
        $settings->brand_logo_path = $path;
        $settings->save();

        if (filled($previousLogo) && $previousLogo !== $path) {
            $disk->delete($previousLogo);
        }

        return back()->with('status', 'Logo başarıyla güncellendi.');
    }
}

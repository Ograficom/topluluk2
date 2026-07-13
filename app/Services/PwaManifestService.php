<?php

namespace App\Services;

use App\Models\PwaSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PwaManifestService
{
    public function buildManifest(PwaSetting $pwa): array
    {
        $startUrl = $this->manifestUrl($pwa->start_url ?: '/');
        $scope = $this->manifestUrl($pwa->scope ?: '/');

        $manifest = [
            'name' => $pwa->app_name ?? config('app.name', 'OGrafi'),
            'short_name' => $pwa->short_name ?? config('app.name', 'OGrafi'),
            'description' => $pwa->description ?? null,
            'start_url' => $startUrl,
            'scope' => $scope,
            'display' => ($pwa->display && $pwa->display !== 'fullscreen') ? $pwa->display : 'standalone',
            'theme_color' => '#2563eb',
            'background_color' => $pwa->background_color ?? '#ffffff',
            'orientation' => $pwa->orientation ?? 'portrait',
            'lang' => $pwa->lang ?? 'tr',
            'dir' => $pwa->dir ?? 'ltr',
        ];

        $icons = [];
        $iconPairs = [
            ['path' => $pwa->icon_192, 'sizes' => '192x192', 'purpose' => 'any'],
            ['path' => $pwa->icon_512, 'sizes' => '512x512', 'purpose' => 'any'],
            ['path' => $pwa->icon_maskable_192, 'sizes' => '192x192', 'purpose' => 'maskable'],
            ['path' => $pwa->icon_maskable_512, 'sizes' => '512x512', 'purpose' => 'maskable'],
        ];
        foreach ($iconPairs as $icon) {
            if (!empty($icon['path']) && Storage::disk('public')->exists($icon['path'])) {
                $realSizes = $this->getImageSizesFromStorage($icon['path']) ?: $icon['sizes'];
                $icons[] = [
                    'src' => $this->manifestUrl($pwa->iconUrl($icon['path'])),
                    'sizes' => $realSizes,
                    'type' => $this->guessMime($icon['path']),
                    'purpose' => $icon['purpose'],
                ];
            }
        }
        if (!empty($icons)) {
            $manifest['icons'] = $icons;
        }
        if (empty($icons)) {
            $fallbackIcons = [];
            $fallbackPairs = [
                ['path' => public_path('pwa/icon-192.png'), 'sizes' => '192x192'],
                ['path' => public_path('pwa/icon-512.png'), 'sizes' => '512x512'],
            ];
            foreach ($fallbackPairs as $fallback) {
                if (is_file($fallback['path'])) {
                    $fallbackSizes = $this->getImageSizesFromFile($fallback['path']) ?: $fallback['sizes'];
                    $fallbackIcons[] = [
                        'src' => $this->manifestUrl('/pwa/' . basename($fallback['path'])),
                        'sizes' => $fallbackSizes,
                        'type' => 'image/png',
                        'purpose' => 'any',
                    ];
                }
            }
            if (!empty($fallbackIcons)) {
                $manifest['icons'] = $fallbackIcons;
            }
        }

        $screenshots = collect($this->toArray($pwa->screenshots))
            ->filter(fn ($item) => !empty($item['image']))
            ->map(function (array $item) use ($pwa) {
                $sizes = $this->getImageSizesFromStorage($item['image'] ?? '');
                $type = $item['image'] ? $this->guessMime($item['image']) : null;
                return array_filter([
                    'src' => $this->manifestUrl($pwa->iconUrl($item['image'])),
                    'label' => $item['label'] ?? null,
                    'form_factor' => $item['form_factor'] ?? null,
                    'sizes' => $sizes ?: null,
                    'type' => $type,
                ]);
            })
            ->values()
            ->all();
        if (!empty($screenshots)) {
            $manifest['screenshots'] = $screenshots;
        }

        $shortcuts = collect($this->toArray($pwa->shortcuts))
            ->filter(fn ($item) => !empty($item['name']) && !empty($item['url']))
            ->map(function (array $item) use ($pwa) {
                $shortcut = [
                    'name' => $item['name'],
                    'short_name' => $item['short_name'] ?? null,
                    'description' => $item['description'] ?? null,
                    'url' => $this->manifestUrl($item['url']),
                ];
                if (!empty($item['icon'])) {
                    $sizes = $this->getImageSizesFromStorage($item['icon']) ?: '96x96';
                    $shortcut['icons'] = [[
                        'src' => $this->manifestUrl($pwa->iconUrl($item['icon'])),
                        'sizes' => $sizes,
                        'type' => $this->guessMime($item['icon']),
                    ]];
                }
                return array_filter($shortcut, fn ($value) => $value !== null && $value !== '');
            })
            ->values()
            ->all();
        if (empty($shortcuts)) {
            $shortcuts = [
                [
                    'name' => 'Yeni Mesajlar',
                    'url' => '/messages',
                ],
                [
                    'name' => 'Profil',
                    'url' => '/user/profile',
                ],
                [
                    'name' => 'Bildirimler',
                    'url' => '/notifications',
                ],
            ];
        }
        if (!empty($shortcuts)) {
            $manifest['shortcuts'] = $shortcuts;
        }

        $categories = array_values(array_filter($this->toArray($pwa->categories)));
        if (!empty($categories)) {
            $manifest['categories'] = $categories;
        }

        if ($pwa->twa_enabled && !empty($pwa->twa_package_id)) {
            $manifest['prefer_related_applications'] = true;
            $manifest['related_applications'] = [[
                'platform' => 'play',
                'id' => $pwa->twa_package_id,
            ]];
        }

        return Arr::where($manifest, fn ($value) => $value !== null && $value !== '');
    }

    private function guessMime(string $path): string
    {
        $ext = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };
    }

    private function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private function manifestUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            $path = parse_url($url, PHP_URL_PATH) ?? '/';
            $query = parse_url($url, PHP_URL_QUERY);

            return $query ? $path . '?' . $query : $path;
        }

        if (Str::startsWith($url, '/')) {
            return $url;
        }

        return '/' . ltrim($url, '/');
    }

    private function getImageSizesFromStorage(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }
        $fullPath = Storage::disk('public')->path($path);
        return $this->getImageSizesFromFile($fullPath);
    }

    private function getImageSizesFromFile(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }
        $size = @getimagesize($path);
        if (!$size) {
            return null;
        }
        return $size[0] . 'x' . $size[1];
    }
}

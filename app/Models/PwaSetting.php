<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PwaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_enabled',
        'install_banner_enabled',
        'twa_enabled',
        'app_name',
        'short_name',
        'description',
        'theme_color',
        'background_color',
        'display',
        'start_url',
        'scope',
        'orientation',
        'lang',
        'dir',
        'categories',
        'shortcuts',
        'screenshots',
        'icon_192',
        'icon_512',
        'icon_maskable_192',
        'icon_maskable_512',
        'login_hero_image',
        'install_banner_title',
        'install_banner_description',
        'install_banner_button_label',
        'twa_package_id',
        'twa_fallback_url',
        'twa_sha256_cert_fingerprints',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'install_banner_enabled' => 'boolean',
        'twa_enabled' => 'boolean',
        'categories' => 'array',
        'shortcuts' => 'array',
        'screenshots' => 'array',
        'twa_sha256_cert_fingerprints' => 'array',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => true,
        ]);
    }

    public static function currentOrNull(): ?self
    {
        if (!Schema::hasTable('pwa_settings')) {
            return null;
        }

        return static::current();
    }

    public function iconUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}

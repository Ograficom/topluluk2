<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    public const DEFAULT_BRAND_NAME = 'Repost';

    protected $fillable = [
        'brand_name',
        'brand_logo_path',
        'favicon_path',
        'footer_year',
        'instagram_url',
        'telegram_url',
        'threads_url',
    ];

    public static function current(): ?self
    {
        return static::query()->find(1);
    }

    public function brandLogoUrl(): ?string
    {
        return filled($this->brand_logo_path)
            ? '/site-branding/logo'
            : null;
    }

    public function faviconUrl(): ?string
    {
        return filled($this->favicon_path)
            ? '/site-branding/favicon'
            : null;
    }

    public function faviconMimeType(): ?string
    {
        if (blank($this->favicon_path)) {
            return null;
        }

        return match (strtolower(pathinfo($this->favicon_path, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            default => null,
        };
    }
}

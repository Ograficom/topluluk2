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
        'google_login_enabled',
        'google_client_id',
        'google_client_secret',
        'yandex_login_enabled',
        'yandex_client_id',
        'yandex_client_secret',
        'advertisement_daily_price',
        'seo_default_title', 'seo_default_description', 'seo_default_keywords', 'seo_og_image',
        'home_seo_title', 'home_seo_description', 'home_seo_keywords', 'home_seo_index',
        'comment_spam_enabled', 'comment_spam_words', 'comment_spam_max_links', 'comment_spam_rate_limit',
    ];

    protected function casts(): array
    {
        return [
            'google_login_enabled' => 'boolean',
            'yandex_login_enabled' => 'boolean',
            'google_client_secret' => 'encrypted',
            'yandex_client_secret' => 'encrypted',
            'advertisement_daily_price' => 'decimal:2',
            'home_seo_index' => 'boolean',
            'comment_spam_enabled' => 'boolean',
            'comment_spam_words' => 'array',
            'comment_spam_max_links' => 'integer',
            'comment_spam_rate_limit' => 'integer',
        ];
    }

    public function isGoogleLoginEnabled(): bool
    {
        return $this->google_login_enabled && filled($this->google_client_id) && filled($this->google_client_secret);
    }

    public function isYandexLoginEnabled(): bool
    {
        return $this->yandex_login_enabled && filled($this->yandex_client_id) && filled($this->yandex_client_secret);
    }

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

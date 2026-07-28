<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SocialLink extends Model
{
    protected $fillable = [
        'platform',
        'label',
        'value',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Iconify "simple-icons" koleksiyonundaki marka ikonu adi.
     */
    public const ICONS = [
        'facebook' => 'simple-icons:facebook',
        'instagram' => 'simple-icons:instagram',
        'x' => 'simple-icons:x',
        'youtube' => 'simple-icons:youtube',
        'whatsapp' => 'simple-icons:whatsapp',
        'tiktok' => 'simple-icons:tiktok',
        'telegram' => 'simple-icons:telegram',
        'discord' => 'simple-icons:discord',
        'github' => 'simple-icons:github',
        'linkedin' => 'simple-icons:linkedin',
    ];

    /**
     * Kullanici adindan otomatik link uretmek icin platform bazli sablonlar.
     * Deger zaten http(s):// ile basliyorsa bu sablonlar hic kullanilmaz,
     * girilen adres oldugu gibi kullanilir.
     */
    private const URL_TEMPLATES = [
        'facebook' => 'https://facebook.com/%s',
        'instagram' => 'https://instagram.com/%s',
        'x' => 'https://x.com/%s',
        'youtube' => 'https://youtube.com/@%s',
        'whatsapp' => 'https://wa.me/%s',
        'tiktok' => 'https://tiktok.com/@%s',
        'telegram' => 'https://t.me/%s',
        'discord' => 'https://discord.gg/%s',
        'github' => 'https://github.com/%s',
        'linkedin' => 'https://linkedin.com/company/%s',
    ];

    protected static function booted(): void
    {
        $forgetCache = static function (): void {
            Cache::forget('social_links:active');
        };

        static::saved($forgetCache);
        static::deleted($forgetCache);
    }

    public function icon(): string
    {
        return self::ICONS[$this->platform] ?? 'simple-icons:link';
    }

    public function getUrlAttribute(): ?string
    {
        $value = trim((string) $this->value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $handle = ltrim($value, '@/');
        $template = self::URL_TEMPLATES[$this->platform] ?? null;

        return $template ? sprintf($template, $handle) : $value;
    }

    /**
     * Aktif ve gecerli bir linki olan sosyal medya kayitlarini, sort_order'a
     * gore sirali dondurur. 10 dk cache'lenir, kayit degisince otomatik bosalir.
     */
    public static function activeLinks(): \Illuminate\Support\Collection
    {
        return Cache::remember('social_links:active', now()->addMinutes(10), function () {
            return self::query()
                ->where('is_active', true)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (self $link) => [
                    'platform' => $link->platform,
                    'label' => $link->label,
                    'icon' => $link->icon(),
                    'url' => $link->url,
                ])
                ->filter(fn (array $link) => filled($link['url']))
                ->values();
        });
    }
}

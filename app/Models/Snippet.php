<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Snippet extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'key',
        'description',
        'content',
        'image_path',
        'link_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $forgetCache = static function (Snippet $snippet): void {
            Cache::forget(self::cacheKey($snippet->key));
        };

        static::saved($forgetCache);
        static::deleted($forgetCache);
    }

    public static function cacheKey(string $key): string
    {
        return "snippet:{$key}";
    }

    public static function findActiveByKey(string $key): ?self
    {
        return Cache::remember(
            self::cacheKey($key),
            now()->addMinutes(10),
            fn () => self::query()
                ->where('key', $key)
                ->where('is_active', true)
                ->first()
        );
    }

    public static function render(string $key, string $default = ''): string
    {
        $snippet = self::findActiveByKey($key);

        if (!$snippet) {
            return $default;
        }

        if (filled($snippet->image_path)) {
            return self::renderImageHtml($snippet);
        }

        $content = $snippet->content;

        if ($content === null || trim(preg_replace('/<!--.*?-->/s', '', $content)) === '') {
            return $default;
        }

        return $content;
    }

    private static function renderImageHtml(self $snippet): string
    {
        $imageUrl = Storage::disk('public')->url($snippet->image_path);
        $alt = e((string) ($snippet->title ?: 'Reklam'));

        $img = sprintf(
            '<img src="%s" alt="%s" style="display:block;width:100%%;height:auto;border-radius:10px;" loading="lazy">',
            e($imageUrl),
            $alt
        );

        $targetUrl = trim((string) ($snippet->link_url ?? ''));

        if ($targetUrl === '' || !Str::startsWith($targetUrl, ['http://', 'https://'])) {
            return $img;
        }

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener sponsored" style="display:block;">%s</a>',
            e($targetUrl),
            $img
        );
    }
}

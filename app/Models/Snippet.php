<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Snippet extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'key',
        'description',
        'content',
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
        return self::findActiveByKey($key)?->content ?? $default;
    }
}

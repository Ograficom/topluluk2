<?php

namespace App\Models;

use App\Services\SitemapService;
use Illuminate\Database\Eloquent\Model;

class SeoPageSetting extends Model
{
    protected $fillable = [
        'route_name', 'title', 'description', 'keywords',
        'canonical_url', 'og_image', 'robots_index',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => app(SitemapService::class)->invalidate());
        static::deleted(fn () => app(SitemapService::class)->invalidate());
    }

    protected function casts(): array
    {
        return ['robots_index' => 'boolean'];
    }
}

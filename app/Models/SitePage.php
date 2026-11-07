<?php

namespace App\Models;

use App\Services\SitemapService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SitePage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'is_published',
        'show_in_footer',
        'published_at',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_in_footer' => 'boolean',
            'published_at' => 'datetime',
            'views' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $page): void {
            if (blank($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::saved(function (): void {
            app(SitemapService::class)->invalidate();
        });

        static::deleted(function (): void {
            app(SitemapService::class)->invalidate();
        });
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_published', true)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}

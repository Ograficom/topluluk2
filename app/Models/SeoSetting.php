<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SeoSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_meta_title',
        'site_meta_description',
        'site_meta_keywords',
        'og_site_name',
        'og_default_title',
        'og_default_description',
        'og_url',
        'og_type',
        'og_default_image',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'site_meta_title' => config('app.name', 'Ografi'),
            'og_site_name' => config('app.name', 'Ografi'),
            'og_type' => 'website',
        ]);
    }

    public static function currentOrNull(): ?self
    {
        if (!Schema::hasTable('seo_settings')) {
            return null;
        }

        return static::current();
    }

    public function ogDefaultImageUrl(): ?string
    {
        $path = trim((string) $this->og_default_image);
        if ($path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}

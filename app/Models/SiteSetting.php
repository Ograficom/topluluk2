<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    public const DEFAULT_BRAND_NAME = 'Repost';

    protected $fillable = [
        'brand_name',
        'brand_logo_path',
    ];

    public static function current(): ?self
    {
        return static::query()->find(1);
    }

    public function brandLogoUrl(): ?string
    {
        return filled($this->brand_logo_path)
            ? Storage::disk('public')->url($this->brand_logo_path)
            : null;
    }
}

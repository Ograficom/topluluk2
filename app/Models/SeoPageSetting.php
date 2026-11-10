<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPageSetting extends Model
{
    protected $fillable = [
        'route_name', 'title', 'description', 'keywords',
        'canonical_url', 'og_image', 'robots_index',
    ];

    protected function casts(): array
    {
        return ['robots_index' => 'boolean'];
    }
}

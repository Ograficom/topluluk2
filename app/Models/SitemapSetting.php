<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitemapSetting extends Model
{
    protected $fillable = [
        'enabled',
        'base_url',
        'include',
        'last_generated_at',
        'url_count',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'include' => 'array',
            'last_generated_at' => 'datetime',
            'url_count' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'enabled' => true,
                'base_url' => rtrim((string) config('app.url'), '/'),
                'include' => ['posts', 'channels', 'tags', 'pages'],
            ],
        );
    }
}

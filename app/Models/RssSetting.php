<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RssSetting extends Model
{
    protected $fillable = [
        'enabled',
        'latest_enabled',
        'author_feeds_enabled',
        'channel_feeds_enabled',
        'tag_feeds_enabled',
        'item_limit',
        'content_mode',
        'cache_minutes',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'latest_enabled' => 'boolean',
            'author_feeds_enabled' => 'boolean',
            'channel_feeds_enabled' => 'boolean',
            'tag_feeds_enabled' => 'boolean',
            'item_limit' => 'integer',
            'cache_minutes' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'enabled' => true,
                'latest_enabled' => true,
                'author_feeds_enabled' => true,
                'channel_feeds_enabled' => true,
                'tag_feeds_enabled' => true,
                'item_limit' => 20,
                'content_mode' => 'excerpt',
                'cache_minutes' => 10,
            ],
        );
    }
}

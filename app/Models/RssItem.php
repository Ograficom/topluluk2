<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RssItem extends Model
{
    protected $fillable = [
        'rss_source_id',
        'guid_hash',
        'guid',
        'title',
        'link',
        'summary',
        'author',
        'image_url',
        'image_urls',
        'tags',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'image_urls' => 'array',
            'tags' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(RssSource::class, 'rss_source_id');
    }
}

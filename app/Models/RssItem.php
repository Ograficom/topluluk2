<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RssItem extends Model
{
    protected $fillable = [
        'rss_feed_id',
        'post_id',
        'guid',
        'title',
        'link',
        'published_at',
        'summary',
        'content',
        'hash',
        'ai_source_hash',
        'ai_title',
        'ai_summary',
        'ai_content',
        'ai_tags',
        'ai_rewritten_at',
        'ai_rewrite_error',
        'imported_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'ai_tags' => 'array',
        'ai_rewritten_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(RssFeed::class, 'rss_feed_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}

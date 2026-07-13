<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RssFeed extends Model
{
    protected $fillable = [
        'name',
        'url',
        'is_enabled',
        'import_as_posts',
        'auto_publish',
        'fetch_dom_content',
        'update_existing_posts',
        'ai_rewrite_enabled',
        'ai_model',
        'default_category_id',
        'default_author_id',
        'etag',
        'last_modified',
        'last_checked_at',
        'last_success_at',
        'last_error',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'import_as_posts' => 'boolean',
        'auto_publish' => 'boolean',
        'fetch_dom_content' => 'boolean',
        'update_existing_posts' => 'boolean',
        'ai_rewrite_enabled' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_success_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RssItem::class);
    }

    public function defaultCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'default_category_id');
    }

    public function defaultAuthor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_author_id');
    }
}

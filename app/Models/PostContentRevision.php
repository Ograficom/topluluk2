<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostContentRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'revision_type',
        'title',
        'excerpt',
        'content',
        'content_json',
        'builder_content',
        'meta_title',
        'meta_description',
        'snapshot',
        'revision_reason',
        'created_at',
    ];

    protected $casts = [
        'content_json' => 'array',
        'builder_content' => 'array',
        'snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

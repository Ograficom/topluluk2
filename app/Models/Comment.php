<?php

namespace App\Models;

use App\Support\PrivacyVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'parent_id',
        'user_id',
        'author_name',
        'author_email',
        'content',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('privacy', function (Builder $query) {
            PrivacyVisibility::apply(
                $query,
                $query->qualifyColumn('user_id'),
                'comments_visibility',
                auth()->user(),
            );
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }
}

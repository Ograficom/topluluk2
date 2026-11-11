<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'body',
        'images',
        'is_spam',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_spam' => 'boolean',
        ];
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
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->filter(fn ($path): bool => is_string($path) && $path !== '' && ! str_contains($path, '..') && Storage::disk('public')->exists($path))
            ->map(fn (string $path): string => asset('storage/'.$path))
            ->values()
            ->all();
    }
}

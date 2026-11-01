<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'tags',
        'is_published',
        'published_at',
        'views',
        'is_adult',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'is_adult' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function coverImageUrl(): ?string
    {
        $coverImage = trim((string) $this->cover_image);

        if ($coverImage === '') {
            return null;
        }

        if (filter_var($coverImage, FILTER_VALIDATE_URL)) {
            return in_array(parse_url($coverImage, PHP_URL_SCHEME), ['http', 'https'], true)
                ? $coverImage
                : null;
        }

        $path = ltrim($coverImage, '/');

        if (str_contains($path, '..')) {
            return null;
        }

        if (Str::startsWith($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return is_file(public_path($path)) ? asset($path) : null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(fn (Builder $query) => $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

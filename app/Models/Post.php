<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'channel_id', 'advertisement_id', 'title', 'slug', 'excerpt', 'body',
        'cover_image', 'media', 'video_url', 'video_path', 'meta_description', 'location', 'tags', 'is_published',
        'published_at', 'views', 'is_adult', 'is_spam',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'media' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'is_adult' => 'boolean',
            'is_spam' => 'boolean',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class)->withTrashed(); }
    public function channel(): BelongsTo { return $this->belongsTo(Channel::class); }
    public function advertisement(): BelongsTo { return $this->belongsTo(Advertisement::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class)->latest(); }
    public function reactions(): HasMany { return $this->hasMany(PostReaction::class); }

    public function tagRecords(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function bookmarkedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_bookmarks')->withTimestamps();
    }

    public function galleryImageUrls(): array
    {
        return collect($this->media ?? [])
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(function (string $path): ?string {
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    return in_array(parse_url($path, PHP_URL_SCHEME), ['http', 'https'], true) ? $path : null;
                }
                $path = ltrim($path, '/');
                if (str_contains($path, '..')) return null;
                if (Str::startsWith($path, 'storage/')) $path = Str::after($path, 'storage/');
                if (Storage::disk('public')->exists($path)) return asset('storage/'.$path);
                return is_file(public_path($path)) ? asset($path) : null;
            })->filter()->unique()->values()->all();
    }

    public function coverImageUrl(): ?string
    {
        $coverImage = trim((string) $this->cover_image);
        if ($coverImage === '') return null;
        if (filter_var($coverImage, FILTER_VALIDATE_URL)) {
            return in_array(parse_url($coverImage, PHP_URL_SCHEME), ['http', 'https'], true) ? $coverImage : null;
        }
        $path = ltrim($coverImage, '/');
        if (str_contains($path, '..')) return null;
        if (Str::startsWith($path, 'storage/')) $path = Str::after($path, 'storage/');
        if (Storage::disk('public')->exists($path)) return asset('storage/'.$path);
        return is_file(public_path($path)) ? asset($path) : null;
    }

    /**
     * Publicly visible posts must be published and, when sponsored, backed by
     * an approved advertisement that is currently eligible for delivery.
     * This keeps pending/rejected/paused/expired/unpaid ads out of every
     * public feed, teaser, comment widget, tag list and channel query using
     * the published scope.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('is_spam', false)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(function (Builder $query): void {
                $query->whereNull('advertisement_id')
                    ->orWhereHas('advertisement', fn (Builder $advertisement) => $advertisement->deliverable());
            });
    }

    public function getRouteKeyName(): string { return 'slug'; }
}

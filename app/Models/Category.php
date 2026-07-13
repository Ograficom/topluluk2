<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by_user_id',
        'name',
        'slug',
        'description',
        'profile_image',
        'cover_image',
    ];

    protected $appends = [
        'profile_image_url',
        'cover_image_url',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'category_user')->withTimestamps();
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        if (Str::startsWith($this->profile_image, ['http://', 'https://', '//'])) {
            $parsedHost = parse_url($this->profile_image, PHP_URL_HOST);
            $parsedPath = parse_url($this->profile_image, PHP_URL_PATH);

            if (is_string($parsedPath) && Str::startsWith($parsedPath, '/storage/') && in_array($parsedHost, ['localhost', '127.0.0.1'], true)) {
                return url($parsedPath);
            }

            return $this->profile_image;
        }

        if (Str::startsWith($this->profile_image, '/storage/')) {
            return url($this->profile_image);
        }

        if (Str::startsWith($this->profile_image, 'storage/')) {
            return url('/storage/' . Str::after($this->profile_image, 'storage/'));
        }

        return Storage::disk('public')->url($this->profile_image);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        if (Str::startsWith($this->cover_image, ['http://', 'https://', '//'])) {
            $parsedHost = parse_url($this->cover_image, PHP_URL_HOST);
            $parsedPath = parse_url($this->cover_image, PHP_URL_PATH);

            if (is_string($parsedPath) && Str::startsWith($parsedPath, '/storage/') && in_array($parsedHost, ['localhost', '127.0.0.1'], true)) {
                return url($parsedPath);
            }

            return $this->cover_image;
        }

        if (Str::startsWith($this->cover_image, '/storage/')) {
            return url($this->cover_image);
        }

        if (Str::startsWith($this->cover_image, 'storage/')) {
            return url('/storage/' . Str::after($this->cover_image, 'storage/'));
        }

        return Storage::disk('public')->url($this->cover_image);
    }
}

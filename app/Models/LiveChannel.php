<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LiveChannel extends Model
{
    protected $fillable = [
        'name',
        'category',
        'stream_url',
        'featured_image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'featured_image_url',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        $value = trim((string) $this->featured_image);

        if ($value === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $value) === 1 || str_starts_with($value, '//')) {
            return $value;
        }

        return Storage::disk('public')->url(ltrim($value, '/'));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Advertisement extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'image_path', 'video_url', 'target_url',
        'button_text', 'starts_at', 'ends_at', 'status', 'billing_type',
        'total_budget', 'daily_budget', 'spent_amount', 'payment_status',
        'approved_by', 'approved_at', 'rejection_reason', 'impressions', 'clicks',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'approved_at' => 'datetime',
            'total_budget' => 'decimal:2',
            'daily_budget' => 'decimal:2',
            'spent_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Advertisement $advertisement): void {
            if ($advertisement->isDirty('status') && $advertisement->status === 'approved') {
                $advertisement->approved_by = auth()->id();
                $advertisement->approved_at = now();
                $advertisement->rejection_reason = null;
            }
        });

        static::saved(fn (Advertisement $advertisement) => $advertisement->syncLinkedPost());
    }

    /**
     * Keep the profile/feed Post mirror in sync with the advertisement record.
     * This is also called after Filament saves an advertisement.
     */
    public function syncLinkedPost(): Post
    {
        $post = $this->post()->first();
        $base = Str::limit(Str::slug($this->title) ?: 'sponsored-post', 240, '');
        $slug = $post?->slug ?? $base;

        for ($suffix = 1; Post::query()->where('slug', $slug)->when($post, fn ($query) => $query->where('id', '<>', $post->id))->exists(); $suffix++) {
            $slug = Str::limit($base, 240 - strlen((string) $suffix), '').'-'.$suffix;
        }

        $attributes = [
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $slug,
            'excerpt' => Str::limit($this->description, 250),
            'body' => $this->description,
            'cover_image' => $this->image_path,
            'video_url' => $this->video_url,
            'is_published' => $this->status === 'approved',
            'published_at' => $this->created_at ?? now(),
            'is_adult' => false,
        ];

        if ($post) {
            $post->update($attributes);
            return $post->refresh();
        }

        return $this->post()->create($attributes);
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function post(): HasOne { return $this->hasOne(Post::class); }
    public function events(): HasMany { return $this->hasMany(AdvertisementEvent::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function imageUrl(): ?string
    {
        return $this->image_path && Storage::disk('public')->exists($this->image_path)
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    public function scopeDeliverable(Builder $query): Builder
    {
        return $query->where('status', 'approved')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->where(fn (Builder $q) => $q->where('billing_type', 'free')
                ->orWhere(fn (Builder $paid) => $paid->where('payment_status', 'paid')
                    ->whereColumn('spent_amount', '<', 'total_budget')));
    }

    public function getClickThroughRateAttribute(): float
    {
        return $this->impressions > 0 ? round(($this->clicks / $this->impressions) * 100, 2) : 0;
    }
}

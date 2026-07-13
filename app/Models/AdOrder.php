<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AdOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'placement',
        'duration_days',
        'width',
        'height',
        'price_cents',
        'currency',
        'title',
        'target_url',
        'image_path',
        'status',
        'starts_at',
        'ends_at',
        'paid_at',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'price_cents' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_cents / 100, 2, ',', '.') . ' ' . $this->currency;
    }
}

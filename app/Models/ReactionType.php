<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReactionType extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'label',
        'short_code',
        'emoji',
        'gif_url',
        'is_active',
        'submitted_by_user_id',
        'moderation_status',
        'moderation_note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (ReactionType $reactionType): void {
            $reactionType->moderation_status = $reactionType->moderation_status ?: self::STATUS_APPROVED;

            if ($reactionType->moderation_status !== self::STATUS_APPROVED) {
                $reactionType->is_active = false;
            }
        });
    }

    public static function moderationStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Beklemede',
            self::STATUS_APPROVED => 'Onaylandi',
            self::STATUS_REJECTED => 'Reddedildi',
        ];
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }
}

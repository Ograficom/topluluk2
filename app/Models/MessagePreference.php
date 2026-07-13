<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessagePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'allow_messages',
        'allow_following_only',
    ];

    protected $casts = [
        'allow_messages' => 'boolean',
        'allow_following_only' => 'boolean',
    ];

    public static function forUser(User $user): self
    {
        return static::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'allow_messages' => true,
                'allow_following_only' => true,
            ]
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

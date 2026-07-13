<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_enabled',
        'allow_following_only',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'allow_following_only' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => true,
            'allow_following_only' => true,
        ]);
    }
}

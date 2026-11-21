<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReputationSetting extends Model
{
    protected $fillable = [
        'enabled', 'exclude_company_accounts', 'post_points', 'comment_points',
        'post_like_points', 'comment_like_points', 'reward_badges',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'exclude_company_accounts' => 'boolean',
            'reward_badges' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}

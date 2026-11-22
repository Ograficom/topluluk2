<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTaskProgress extends Model
{
    protected $fillable = [
        'user_id', 'task_key', 'period_key', 'progress', 'completed_at', 'reward_badge',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'reward_badge' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

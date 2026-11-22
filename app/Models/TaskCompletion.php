<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCompletion extends Model
{
    protected $fillable = ['user_id', 'task_key', 'task_name', 'reward_points', 'badge', 'completed_at'];

    protected function casts(): array
    {
        return [
            'badge' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
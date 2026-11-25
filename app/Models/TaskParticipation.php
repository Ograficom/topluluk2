<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskParticipation extends Model
{
    protected $fillable = ['user_id', 'task_key', 'task_name', 'external_username', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

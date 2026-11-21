<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class ReputationTransaction extends Model
{
    protected $fillable = ['user_id', 'points', 'event_key', 'description', 'source_type', 'source_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

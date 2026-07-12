<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    protected $fillable = [
        'user_id',
        'blocking_id',
    ];

    /**
     * Returns who a user is blocking.
     *
     * @return BelongsTo
     */
    public function blocking(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocking_id');
    }

    /**
     * Returns who is blocking a user.
     *
     * @return BelongsTo
     */
    public function blockers(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

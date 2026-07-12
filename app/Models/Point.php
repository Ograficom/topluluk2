<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Point extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'points',
        'reason',
    ];

    public function pointable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getCurrentPoints(Model $pointable)
    {
        $currentPoints = self::where('user_id', $pointable->id)
            ->orderBy('created_at', 'desc')
            ->pluck('current_points')
            ->first();

        if (! $currentPoints) {
            $currentPoints = 0;
        }

        return $currentPoints;
    }

    public function addAwards(Model $pointable, $amount, $reason)
    {
        $award = new static();
        $award->user_id = $pointable->id;
        $award->amount = $amount;
        $award->current_points = $this->getCurrentPoints($pointable) + $amount;
        $award->reason = $reason;

        $pointable->awards()->save($award);

        return $award;
    }
}

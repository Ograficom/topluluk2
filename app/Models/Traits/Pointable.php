<?php

namespace App\Models\Traits;

use App\Models\Point;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Pointable
{
    public function awards($amount = null): HasMany
    {
        return $this->hasMany(Point::class)
            ->orderBy('created_at', 'desc')
            ->take($amount);
    }

    public function countAwards(): int
    {
        return $this->awards()->count();
    }

    public function currentPoints()
    {
        return (new Point())->getCurrentPoints($this);
    }

    public function addPoints($amount, $reason)
    {
        return (new Point())->addAwards($this, $amount, $reason);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $guarded = [];

    public function scopeDefault($query)
    {
        $query->where('is_default', true);
    }

    public function isDefault()
    {
        return $this->is_default == true;
    }

    public function badge()
    {
        return $this->hasOne(Badge::class);
    }
}

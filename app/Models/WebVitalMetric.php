<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebVitalMetric extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metric_value' => 'float',
            'is_secure' => 'boolean',
        ];
    }
}

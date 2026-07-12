<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];
}

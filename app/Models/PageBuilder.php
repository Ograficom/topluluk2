<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilder extends Model
{
    protected $fillable = [
        'key',
        'title',
        'sections',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'sections' => 'array',
        'is_active' => 'boolean',
    ];
}

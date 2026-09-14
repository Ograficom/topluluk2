<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannedPostWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'is_active',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

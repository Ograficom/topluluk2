<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentBlockedWord extends Model
{
    //
    protected $fillable = [
    'word',
    'is_active',
    ];

    protected $casts = [
    'is_active' => 'boolean',
    ];
}

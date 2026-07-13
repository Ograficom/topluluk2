<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RssSource extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_publish' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }
}

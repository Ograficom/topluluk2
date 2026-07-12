<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mews\Purifier\Casts\CleanHtmlInput;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'position',
        'alias',
        'code',
        'status',
    ];

    protected $casts = [
        'code' => CleanHtmlInput::class,
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function isActive()
    {
        return $this->status == true;
    }
}

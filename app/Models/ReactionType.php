<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'short_code',
        'emoji',
        'gif_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RssSource extends Model
{
    protected $fillable = [
        'name',
        'url',
        'url_hash',
        'site_url',
        'description',
        'is_active',
        'last_fetched_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_fetched_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(RssItem::class);
    }
}

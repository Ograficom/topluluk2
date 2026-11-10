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
        'author_id',
        'channel_id',
        'auto_publish',
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
            'auto_publish' => 'boolean',
            'last_fetched_at' => 'datetime',
        ];
    }

    public function author() { return $this->belongsTo(User::class, 'author_id'); }

    public function channel() { return $this->belongsTo(Channel::class, 'channel_id'); }

    public function items(): HasMany
    {
        return $this->hasMany(RssItem::class);
    }
}

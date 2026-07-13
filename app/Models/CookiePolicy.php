<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookiePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'banner_title',
        'banner_message',
        'content',
        'is_enabled',
        'version',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'version' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (CookiePolicy $policy): void {
            if ($policy->isDirty(['content', 'banner_message', 'banner_title'])) {
                $policy->version = $policy->version + 1;
            }
        });
    }

    public function consents()
    {
        return $this->hasMany(CookieConsent::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}

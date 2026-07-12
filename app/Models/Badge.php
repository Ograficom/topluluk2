<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Badge extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function getImageUrl(): ?string
    {
        if ($this->image && str_starts_with($this->image, 'images/badges/')) {
            return asset($this->image);
        } else {
            return Storage::disk(getCurrentDisk())->url($this->image);
        }
    }

    public function scopeMembershipYearsBadge($query)
    {
        $query->where('alias', 'membership_years');
    }
}

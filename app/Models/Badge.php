<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'icon_svg_path',
        'eligible_profile_type',
        'requires_verified',
        'color',
        'min_points',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'eligible_profile_type' => 'string',
            'requires_verified' => 'boolean',
            'min_points' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('min_points');
    }

    public function scopeEligibleForUser(Builder $query, ?User $user): Builder
    {
        $profileType = Str::lower(trim((string) ($user?->profile_type ?? 'person')));
        $isVerified = (bool) ($user?->is_verified ?? false);

        return $query
            ->where(function (Builder $badgeQuery) use ($profileType): void {
                $badgeQuery
                    ->whereNull('eligible_profile_type')
                    ->orWhere('eligible_profile_type', '')
                    ->orWhere('eligible_profile_type', $profileType);
            })
            ->when(!$isVerified, fn (Builder $badgeQuery) => $badgeQuery->where('requires_verified', false));
    }

    public function getIconSvgUrlAttribute(): ?string
    {
        $path = trim((string) ($this->icon_svg_path ?? ''));

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        if (Str::startsWith($path, '/storage/')) {
            return url($path);
        }

        if (Str::startsWith($path, 'storage/')) {
            return url('/storage/' . Str::after($path, 'storage/'));
        }

        return Storage::disk('public')->url($path);
    }
}

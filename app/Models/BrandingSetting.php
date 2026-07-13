<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BrandingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'logo_path',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'logo_path' => null,
        ]);
    }

    public static function currentOrNull(): ?self
    {
        if (!Schema::hasTable('branding_settings')) {
            return null;
        }

        return static::current();
    }

    public function logoUrl(): ?string
    {
        $path = trim((string) ($this->logo_path ?? ''));
        if ($path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}


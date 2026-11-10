<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SantralPageSetting extends Model
{
    protected $table = 'santral_page_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'steps' => 'array',
            'slides' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BorsaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_active',
        'api_url',
        'api_key',
        'symbols',
        'base_symbol',
        'pair_symbols',
        'response_path',
        'symbol_key',
        'price_key',
        'change_key',
        'cache_seconds',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_active' => true,
            'api_url' => 'https://cdn.moneyconvert.net/api/latest.json',
            'api_key' => null,
            'symbols' => 'USD,EUR,TRY',
            'base_symbol' => 'USD',
            'pair_symbols' => 'USD/TRY,EUR/TRY',
            'response_path' => 'rates',
            'symbol_key' => 'symbol',
            'price_key' => 'price',
            'change_key' => null,
            'cache_seconds' => 300,
        ]);
    }

    public static function currentOrNull(): ?self
    {
        if (!Schema::hasTable('borsa_settings')) {
            return null;
        }

        return static::current();
    }
}

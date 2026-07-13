<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_enabled',
        'include_posts',
        'include_post_content',
        'include_categories',
        'include_tags',
        'include_users',
        'limit_per_type',
        'min_query_length',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'include_posts' => 'boolean',
        'include_post_content' => 'boolean',
        'include_categories' => 'boolean',
        'include_tags' => 'boolean',
        'include_users' => 'boolean',
        'limit_per_type' => 'integer',
        'min_query_length' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => true,
            'include_posts' => true,
            'include_post_content' => true,
            'include_categories' => true,
            'include_tags' => true,
            'include_users' => true,
            'limit_per_type' => 5,
            'min_query_length' => 2,
        ]);
    }
}

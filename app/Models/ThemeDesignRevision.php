<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeDesignRevision extends Model
{
    protected $fillable = [
        'theme_setting_id',
        'user_id',
        'label',
        'component_styles',
        'page_layouts',
    ];

    protected $casts = [
        'component_styles' => 'array',
        'page_layouts' => 'array',
    ];

    public function themeSetting(): BelongsTo
    {
        return $this->belongsTo(ThemeSetting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

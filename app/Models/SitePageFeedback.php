<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePageFeedback extends Model
{
    protected $table = 'site_page_feedback';

    protected $guarded = ['id', 'voter_key'];

    protected $hidden = ['voter_key'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function sitePage(): BelongsTo
    {
        return $this->belongsTo(SitePage::class);
    }
}

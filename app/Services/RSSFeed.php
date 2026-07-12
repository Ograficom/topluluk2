<?php

namespace App\Services;

use App\Models\Story;
use Illuminate\Database\Eloquent\Collection;

class RSSFeed
{
    public static function getFeedItems(): Collection
    {
        return Story::published()->latest()->limit(100)->get();
    }
}

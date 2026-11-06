<?php

namespace App\Observers;

use App\Services\SitemapService;
use Illuminate\Database\Eloquent\Model;

class SitemapObserver
{
    public function saved(Model $model): void
    {
        app(SitemapService::class)->invalidate();
    }

    public function deleted(Model $model): void
    {
        app(SitemapService::class)->invalidate();
    }

    public function restored(Model $model): void
    {
        app(SitemapService::class)->invalidate();
    }
}

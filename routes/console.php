<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\SitemapService;
use App\Services\ExternalRssReader;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('admin-notifications:sync-all')
    ->everyMinute()
    ->name('admin-pending-notifications-sync')
    ->withoutOverlapping();

Schedule::call(fn () => app(SitemapService::class)->rebuild())
    ->everyFifteenMinutes()
    ->name('sitemap-rebuild')
    ->withoutOverlapping();

Schedule::command('rss:fetch')
    ->everyFiveMinutes()
    ->name('external-rss-fetch')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/rss-schedule.log'));

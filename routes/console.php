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


Schedule::command('google-drive:backup')
    ->dailyAt('02:00')
    ->name('google-drive-daily-encrypted-backup')
    ->withoutOverlapping()
    ->onFailure(fn () => logger()->error('Google Drive daily backup failed.'));

Schedule::command('google-drive:prune')
    ->dailyAt('02:30')
    ->name('google-drive-backup-retention-cleanup')
    ->withoutOverlapping();

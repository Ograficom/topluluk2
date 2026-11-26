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

Schedule::call(function (): void {
    \Illuminate\Support\Facades\DB::table('photo360_jobs')
        ->where('updated_at', '<', now()->subDay())->orderBy('id')
        ->chunkById(50, function ($jobs): void {
            foreach ($jobs as $job) {
                \Illuminate\Support\Facades\File::deleteDirectory(storage_path('app/private/photo360/'.$job->id));
                \Illuminate\Support\Facades\DB::table('photo360_jobs')->where('id', $job->id)->delete();
            }
        });
})->hourly()->name('photo360-prune')->withoutOverlapping();

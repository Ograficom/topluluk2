<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\SitemapService;
use App\Services\ExternalRssReader;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(SitemapService::class)->rebuild())
    ->everyFifteenMinutes()
    ->name('sitemap-rebuild')
    ->withoutOverlapping();

Schedule::call(fn () => app(ExternalRssReader::class)->fetchAll())
    ->everyFiveMinutes()
    ->name('external-rss-fetch')
    ->withoutOverlapping();

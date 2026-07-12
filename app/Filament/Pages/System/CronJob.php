<?php

namespace App\Filament\Pages\System;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CronJob extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static string $view = 'filament.pages.system.cron-job';

    protected static ?int $navigationSort = 18;

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Cron Job');
    }

    public static function getNavigationLabel(): string
    {
        return __('Cron Job');
    }
}

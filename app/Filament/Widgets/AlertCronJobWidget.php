<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AlertCronJobWidget extends Widget
{
    protected static string $view = 'filament.widgets.alert-cron-job-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $lastExecutedCronJob = config('alma.cronjob.last_execution');

        return $lastExecutedCronJob !== '' ? false : true;
    }
}

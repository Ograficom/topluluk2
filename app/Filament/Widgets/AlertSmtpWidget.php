<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AlertSmtpWidget extends Widget
{
    protected static string $view = 'filament.widgets.alert-smtp-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $currentMailDriver = settings()->group('advanced')->get('current_mail_driver');

        return $currentMailDriver !== 'smtp' ? false : true;
    }
}

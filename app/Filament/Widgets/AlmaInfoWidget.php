<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AlmaInfoWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.alma-info-widget';
}

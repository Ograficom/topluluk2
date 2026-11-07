<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'OgraFi Yönetim Merkezi';

    protected static ?string $navigationLabel = 'Panel';

    protected string $view = 'filament.pages.dashboard';
}

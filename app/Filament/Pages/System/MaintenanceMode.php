<?php

namespace App\Filament\Pages\System;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class MaintenanceMode extends Page
{
    protected static ?int $navigationSort = 17;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string $view = 'filament.pages.system.maintenance-mode';

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Maintenance Mode');
    }

    public static function getNavigationLabel(): string
    {
        return __('Maintenance Mode');
    }
}

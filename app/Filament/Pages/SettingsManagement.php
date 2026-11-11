<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SettingsManagement extends Page
{
    protected static ?string $slug = 'settings-management';

    protected static ?string $navigationLabel = 'Ayarlar';

    protected static ?string $title = 'Ayarlar';

    // Keep this as a standalone navigation item, outside collapsible groups.
    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 9999;

    protected string $view = 'filament.pages.settings-management';
    public function getHeading(): ?string
    {
        return null;
    }
}

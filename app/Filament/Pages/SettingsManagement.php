<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SettingsManagement extends Page
{
    protected static ?string $slug = 'settings-management';

    protected static ?string $navigationLabel = 'Ayarlar';

    protected static ?string $title = 'Ayarlar';

    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings-management';
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SettingsManagement extends Page
{
    protected static ?string $slug = 'settings-management';

    protected static ?string $navigationLabel = 'Ayarlar';

    protected static ?string $title = 'Ayarlar';

    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings-management';
}

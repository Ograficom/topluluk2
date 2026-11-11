<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AdvertisementManager extends Page
{
    protected static ?string $slug = 'advertisement-manager';

    protected static ?string $navigationLabel = 'Reklam yöneticisi';

    protected static ?string $title = 'Reklam yöneticisi';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 9998;

    protected string $view = 'filament.pages.advertisement-manager';
}

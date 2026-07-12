<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Appearance extends Page
{
    protected static ?int $navigationSort = 9;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static string $view = 'filament.pages.appearance';

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Appearance settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Appearance');
    }
}

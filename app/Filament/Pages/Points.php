<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Points extends Page
{
    protected static ?int $navigationSort = 14;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static string $view = 'filament.pages.points';

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Points settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Points');
    }
}

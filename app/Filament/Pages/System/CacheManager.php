<?php

namespace App\Filament\Pages\System;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CacheManager extends Page
{
    protected static ?int $navigationSort = 18;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static string $view = 'filament.pages.system.cache-manager';

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Cache Manager');
    }

    public static function getNavigationLabel(): string
    {
        return __('Cache');
    }
}

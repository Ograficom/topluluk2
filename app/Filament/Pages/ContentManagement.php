<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ContentManagement extends Page
{
    protected static ?string $slug = 'content-management';

    protected static ?string $navigationLabel = 'İçerik Yönetimi';

    protected static ?string $title = 'İçerik Yönetimi';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected string $view = 'filament.pages.content-management';
}

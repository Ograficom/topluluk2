<?php

namespace App\Filament\Pages;

use App\Models\GoogleDriveConnection;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class GoogleDriveSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCloud;
    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'Google Drive';
    protected static ?string $title = 'Google Drive depolama';
    protected static ?int $navigationSort = 90;
    protected string $view = 'filament.pages.google-drive-settings';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_admin;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getViewData(): array
    {
        return [
            'connection' => GoogleDriveConnection::current(),
            'enabled' => (bool) config('google-drive.enabled'),
        ];
    }
}

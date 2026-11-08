<?php

namespace App\Filament\Resources\Advertisements;

use App\Filament\Resources\Advertisements\Pages\CreateAdvertisement;
use App\Filament\Resources\Advertisements\Pages\EditAdvertisement;
use App\Filament\Resources\Advertisements\Pages\ListAdvertisements;
use App\Filament\Resources\Advertisements\Schemas\AdvertisementForm;
use App\Filament\Resources\Advertisements\Tables\AdvertisementsTable;
use App\Models\Advertisement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    public static function form(Schema $schema): Schema { return AdvertisementForm::configure($schema); }
    public static function table(Table $table): Table { return AdvertisementsTable::configure($table); }
    public static function getNavigationLabel(): string { return 'Reklamlar'; }
    public static function getModelLabel(): string { return 'reklam'; }
    public static function getPluralModelLabel(): string { return 'reklamlar'; }
    public static function getPages(): array
    {
        return ['index' => ListAdvertisements::route('/'), 'create' => CreateAdvertisement::route('/create'), 'edit' => EditAdvertisement::route('/{record}/edit')];
    }
}

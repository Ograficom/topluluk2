<?php

namespace App\Filament\Resources\AccountDeletionRequests;

use App\Filament\Resources\AccountDeletionRequests\Pages\ListAccountDeletionRequests;
use App\Filament\Resources\AccountDeletionRequests\Tables\AccountDeletionRequestsTable;
use App\Models\AccountDeletionRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountDeletionRequestResource extends Resource
{
    protected static ?string $model = AccountDeletionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserMinus;

    protected static ?string $navigationGroup = 'Kullanıcı Yönetimi';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return AccountDeletionRequestsTable::configure($table);
    }

    public static function getNavigationLabel(): string
    {
        return 'Hesap silme talepleri';
    }

    public static function getModelLabel(): string
    {
        return 'hesap silme talebi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'hesap silme talepleri';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountDeletionRequests::route('/'),
        ];
    }
}

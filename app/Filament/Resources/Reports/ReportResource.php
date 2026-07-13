<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\Pages\EditReport;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Filament\Resources\Reports\Schemas\ReportForm;
use App\Filament\Resources\Reports\Tables\ReportsTable;
use App\Models\Report;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static string | \UnitEnum | null $navigationGroup = 'Moderasyon';
    protected static ?string $navigationLabel = 'Sikayetler';
    protected static ?string $pluralLabel = 'Sikayetler';
    protected static ?string $modelLabel = 'Sikayet';
    protected static ?int $navigationSort = 90;

    protected static ?string $recordTitleAttribute = 'topic';

    public static function form(Schema $schema): Schema
    {
        return ReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            'edit' => EditReport::route('/{record}/edit'),
        ];
    }
}










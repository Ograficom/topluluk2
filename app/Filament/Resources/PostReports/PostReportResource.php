<?php

namespace App\Filament\Resources\PostReports;

use App\Filament\Resources\PostReports\Pages\EditPostReport;
use App\Filament\Resources\PostReports\Pages\ListPostReports;
use App\Filament\Resources\PostReports\Schemas\PostReportForm;
use App\Filament\Resources\PostReports\Tables\PostReportsTable;
use App\Models\PostReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostReportResource extends Resource
{
    protected static ?string $model = PostReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|\UnitEnum|null $navigationGroup = 'İçerik Yönetimi';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PostReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostReportsTable::configure($table);
    }

    public static function getNavigationLabel(): string
    {
        return 'Yazı şikayetleri';
    }

    public static function getModelLabel(): string
    {
        return 'yazı şikayeti';
    }

    public static function getPluralModelLabel(): string
    {
        return 'yazı şikayetleri';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostReports::route('/'),
            'edit' => EditPostReport::route('/{record}/edit'),
        ];
    }
}

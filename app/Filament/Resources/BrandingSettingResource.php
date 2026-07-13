<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandingSettingResource\Pages;
use App\Models\BrandingSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BrandingSettingResource extends Resource
{
    protected static ?string $model = BrandingSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Logo';

    protected static ?string $modelLabel = 'Logo';

    protected static ?string $pluralModelLabel = 'Logo';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Placeholder::make('logo_heading')->label('Site Logo'),
            FileUpload::make('logo_path')
                ->label('Logo')
                ->disk('public')
                ->directory('branding')
                ->visibility('public')
                ->imagePreviewHeight('140')
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                ->maxSize(5120)
                ->helperText('PNG/SVG/JPG yukleyebilirsiniz. Frontend logo buradan okunur.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->visibility('public')
                    ->height(40),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrandingSettings::route('/'),
            'edit' => Pages\EditBrandingSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }
}








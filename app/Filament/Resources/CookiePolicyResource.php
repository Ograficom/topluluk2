<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CookiePolicyResource\Pages;
use App\Models\CookiePolicy;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CookiePolicyResource extends Resource
{
    protected static ?string $model = CookiePolicy::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'Çerez Politikası';

    protected static ?string $modelLabel = 'Çerez Politikası';

    protected static ?string $pluralModelLabel = 'Çerez Politikası';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_enabled')
                    ->label('Aktif')
                    ->inline(false),
            ]),
            Forms\Components\TextInput::make('banner_title')
                ->label('Banner başlığı')
                ->maxLength(255),
            Forms\Components\Textarea::make('banner_message')
                ->label('Kısa mesaj')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\RichEditor::make('content')
                ->label('İçerik')
                ->columnSpanFull()
                ->toolbarButtons([
                    'bold',
                    'italic',
                    'underline',
                    'strike',
                    'bulletList',
                    'orderedList',
                    'blockquote',
                    'link',
                    'codeBlock',
                ])
                ->fileAttachmentsDisk(config('filesystems.default', 'public'))
                ->fileAttachmentsVisibility('public'),
            Forms\Components\TextInput::make('version')
                ->label('Politika sürümü')
                ->numeric()
                ->readOnly()
                ->helperText('İçerik değiştiğinde otomatik artar.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('version')
                    ->label('Sürüm')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Güncellendi')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCookiePolicies::route('/'),
            'create' => Pages\CreateCookiePolicy::route('/create'),
            'edit' => Pages\EditCookiePolicy::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }

    public static function canCreate(): bool
    {
        return CookiePolicy::count() === 0;
    }
}












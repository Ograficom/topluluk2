<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReactionTypeResource\Pages;
use App\Models\ReactionType;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReactionTypeResource extends Resource
{
    protected static ?string $model = ReactionType::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Blog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-face-smile';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('label')
                ->label('Ad')
                ->required()
                ->maxLength(100),
            TextInput::make('short_code')
                ->label('Kisa Kod')
                ->required()
                ->unique(table: 'reaction_types', column: 'short_code', ignoreRecord: true)
                ->maxLength(50)
                ->helperText('Orn: like, clap, wow. Frontend bu kodla istek gonderir.'),
            TextInput::make('emoji')
                ->label('Emoji')
                ->maxLength(10)
                ->helperText('Tek bir emoji veya Unicode karakter.'),
            FileUpload::make('gif_url')
                ->label('GIF/Resim')
                ->directory('reaction-types')
                ->image()
                ->visibility('public')
                ->preserveFilenames()
                ->acceptedFileTypes(['image/gif', 'image/png', 'image/jpeg', 'image/webp'])
                ->maxSize(10240)
                ->helperText('GIF veya gorsel yukleyin; public diskte saklanir.'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Ad')->sortable()->searchable(),
                TextColumn::make('short_code')->label('Kod')->sortable()->searchable(),
                TextColumn::make('emoji')->label('Emoji'),
                TextColumn::make('gif_url')->label('GIF')->limit(20),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('reactions_count')->label('Toplam Tepki')->counts('reactions'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReactionTypes::route('/'),
            'create' => Pages\CreateReactionType::route('/create'),
            'edit' => Pages\EditReactionType::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['label', 'short_code', 'emoji'];
    }
}








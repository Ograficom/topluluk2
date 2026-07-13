<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SnippetResource\Pages;
use App\Models\Snippet;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SnippetResource extends Resource
{
    protected static ?string $model = Snippet::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Icerik';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-code-bracket-square';

    protected static ?string $navigationLabel = 'Kısa Kodlar';

    protected static ?string $modelLabel = 'Kısa Kod';

    protected static ?string $pluralModelLabel = 'Kısa Kodlar';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                TextInput::make('title')
                    ->label('Başlık')
                    ->maxLength(255),
                TextInput::make('key')
                    ->label('Anahtar')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
            ]),
            Textarea::make('description')
                ->label('Açıklama')
                ->maxLength(500)
                ->rows(2),
            Textarea::make('content')
                ->label('HTML İçerik')
                ->rows(12)
                ->helperText('Bu alana ham HTML girilir ve @snippet(\'anahtar\') ile Blade içinde gösterilir.')
                ->required(),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Anahtar')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Güncellendi')
                    ->since()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSnippets::route('/'),
            'create' => Pages\CreateSnippet::route('/create'),
            'edit' => Pages\EditSnippet::route('/{record}/edit'),
        ];
    }
}












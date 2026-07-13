<?php

namespace App\Filament\Resources\CommentBlockedWords;

use App\Filament\Resources\CommentBlockedWords\Pages;
use App\Models\CommentBlockedWord;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CommentBlockedWordResource extends Resource
{
    protected static ?string $model = CommentBlockedWord::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Blog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $modelLabel = 'Yasaklı yorum kelimesi';

    protected static ?string $pluralModelLabel = 'Yasaklı yorum kelimeleri';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('word')
                ->label('Kelime veya ifade')
                ->required()
                ->maxLength(255)
                ->unique(table: 'comment_blocked_words', column: 'word', ignoreRecord: true),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('word')
                    ->label('Kelime veya ifade')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Güncellendi')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktiflik'),
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
            'index' => Pages\ListCommentBlockedWords::route('/'),
            'create' => Pages\CreateCommentBlockedWord::route('/create'),
            'edit' => Pages\EditCommentBlockedWord::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['word'];
    }
}

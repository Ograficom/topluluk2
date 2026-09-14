<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BannedPostWordResource\Pages;
use App\Models\BannedPostWord;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BannedPostWordResource extends Resource
{
    protected static ?string $model = BannedPostWord::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Moderasyon';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationLabel = 'Yasaklı Kelimeler';

    protected static ?string $modelLabel = 'Yasaklı Kelime';

    protected static ?string $pluralModelLabel = 'Yasaklı Kelimeler';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('word')
                ->label('Yasaklı kelime / ifade')
                ->required()
                ->maxLength(120)
                ->unique(table: 'banned_post_words', column: 'word', ignoreRecord: true)
                ->helperText('Tek kelime veya ifade girebilirsiniz. Büyük/küçük harf ayrımı yapılmaz.'),
            Toggle::make('is_active')
                ->label('Kural aktif')
                ->default(true)
                ->inline(false),
            Textarea::make('note')
                ->label('Not')
                ->rows(3)
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('word')
                    ->label('Yasaklı kelime / ifade')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Not')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Eklenme')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Durum')
                    ->trueLabel('Aktif')
                    ->falseLabel('Pasif'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('word');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBannedPostWords::route('/'),
            'create' => Pages\CreateBannedPostWord::route('/create'),
            'edit' => Pages\EditBannedPostWord::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['word', 'note'];
    }
}

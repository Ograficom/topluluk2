<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Iletisim';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Mesajlar';

    protected static ?string $pluralModelLabel = 'Mesajlar';

    protected static ?string $modelLabel = 'Mesaj';

    protected static bool $isGloballySearchable = false;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sender.name')
                    ->label('Gonderen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('recipient.name')
                    ->label('Alici')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('body')
                    ->label('Mesaj')
                    ->wrap()
                    ->limit(120),
                IconColumn::make('read_at')
                    ->label('Okundu')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->read_at !== null),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessages::route('/'),
        ];
    }
}




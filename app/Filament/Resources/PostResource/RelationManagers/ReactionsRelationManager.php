<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ReactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'reactions';

    protected static ?string $title = 'Tepkiler';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('reaction_type_id')
                ->label('Tepki turu')
                ->relationship('type', 'label')
                ->required()
                ->preload()
                ->searchable(),
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->label('Kullanici')
                ->searchable()
                ->preload()
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type.label')
                    ->label('Tepki')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.short_code')
                    ->label('Kod')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.emoji')
                    ->label('Emoji'),
                Tables\Columns\TextColumn::make('type.gif_url')
                    ->label('GIF')
                    ->limit(20),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kullanici')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Olusturulma')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}





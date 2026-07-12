<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class BadgesRelationManager extends RelationManager
{
    protected static string $relationship = 'badges';

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Badges');
    }

    public static function getPluralRecordLabel(): string
    {
        return __('badges');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->deselectAllRecordsWhenFiltered(false)
            ->columns([
                Tables\Columns\ImageColumn::make('badge.images')
                    ->label(__('Badge'))
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => $record->badge->getImageUrl()),
                Tables\Columns\TextColumn::make('badge_alias'),
                Tables\Columns\TextColumn::make('badge.name')
                    ->label(__('Name')),
                Tables\Columns\TextColumn::make('sort_id')
                    ->label('Sort ID'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}

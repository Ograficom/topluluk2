<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Channel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable()
                    ->limit(48),
                TextColumn::make('channel.name')
                    ->label('Kanal')
                    ->placeholder('—')
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Yayında')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Yayın tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('views')
                    ->label('Görüntülenme')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('channel_id')
                    ->label('Kanal')
                    ->options(fn () => Channel::query()->orderBy('name')->pluck('name', 'id')),
                SelectFilter::make('is_published')
                    ->label('Durum')
                    ->options([1 => 'Yayında', 0 => 'Taslak']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

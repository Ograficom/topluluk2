<?php

namespace App\Filament\Resources\SitePages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SitePagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('URL')
                    ->searchable()
                    ->copyable()
                    ->url(fn (\\App\\Models\\SitePage $record): string => route('pages.show', ['slug' => $record->slug]))
                    ->openUrlInNewTab(),
                IconColumn::make('is_published')
                    ->label('Yayın durumu')
                    ->boolean()
                    ->tooltip(fn (\\App\\Models\\SitePage $record): string => ! $record->is_published
                        ? 'Taslak: ziyaretçilere açık değil'
                        : ($record->published_at?->isFuture() ? 'Planlandı: yayın tarihi henüz gelmedi' : 'Yayında')),
                TextColumn::make('updated_at')
                    ->label('Son güncelleme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
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

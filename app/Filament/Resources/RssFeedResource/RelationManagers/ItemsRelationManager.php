<?php

namespace App\Filament\Resources\RssFeedResource\RelationManagers;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'RSS Items';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Baslik')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('published_at')
                    ->label('Yayin')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('post_id')
                    ->label('Gonderi')
                    ->boolean()
                    ->state(fn ($record) => (bool) $record->post_id),
                IconColumn::make('ai_rewritten_at')
                    ->label('AI')
                    ->boolean()
                    ->state(fn ($record) => (bool) $record->ai_rewritten_at),
                TextColumn::make('ai_rewrite_error')
                    ->label('AI hata')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('imported_at')
                    ->label('Import')
                    ->since(),
                TextColumn::make('updated_at')
                    ->label('Guncellendi')
                    ->since(),
            ])
            ->headerActions([])
            ->actions([
                Actions\Action::make('open_link')
                    ->label('Link')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->link)),
                Actions\Action::make('open_post')
                    ->label('Gonderi')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => $record->post_id ? PostResource::getUrl('edit', ['record' => $record->post_id]) : null)
                    ->visible(fn ($record) => (bool) $record->post_id),
            ])
            ->bulkActions([]);
    }
}






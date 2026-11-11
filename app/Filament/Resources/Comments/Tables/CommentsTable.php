<?php

namespace App\Filament\Resources\Comments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('body')
                    ->label('Yorum')
                    ->searchable()
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('Yazar')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('post.title')
                    ->label('Yazı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_spam')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Spam incelemesinde' : 'Yayında')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success'),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_spam')
                    ->label('Durum')
                    ->options([false => 'Yayında', true => 'Spam incelemesinde']),
                SelectFilter::make('user_id')
                    ->label('Yazar')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('post_id')
                    ->label('Yazı')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approveSpam')
                    ->label('Spam değil')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => (bool) $record->is_spam)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['is_spam' => false])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

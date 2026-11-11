<?php

namespace App\Filament\Resources\PostReports\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('No')->sortable(),
                TextColumn::make('post.title')->label('Bildirilen yazı')->searchable()->limit(45)->wrap(),
                TextColumn::make('user.name')->label('Bildiren')->searchable()->placeholder('Silinmiş kullanıcı'),
                TextColumn::make('reason')->label('Neden')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'spam' => 'Spam veya reklam',
                    'misinformation' => 'Yanlış bilgi',
                    'harassment' => 'Hakaret veya taciz',
                    'copyright' => 'Telif hakkı',
                    'inappropriate' => 'Uygunsuz içerik',
                    'other' => 'Diğer',
                    default => $state,
                }),
                TextColumn::make('status')->label('Durum')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'Bekliyor',
                    'reviewing' => 'İnceleniyor',
                    'resolved' => 'İşlem yapıldı',
                    'rejected' => 'Uygun bulunmadı',
                    default => $state,
                })->color(fn (string $state): string => match ($state) {
                    'pending' => 'danger',
                    'reviewing' => 'warning',
                    'resolved' => 'success',
                    'rejected' => 'gray',
                    default => 'gray',
                }),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Durum')->options([
                    'pending' => 'Bekliyor',
                    'reviewing' => 'İnceleniyor',
                    'resolved' => 'İşlem yapıldı',
                    'rejected' => 'Uygun bulunmadı',
                ]),
                SelectFilter::make('reason')->label('Bildirim nedeni')->options([
                    'spam' => 'Spam veya reklam',
                    'misinformation' => 'Yanlış bilgi',
                    'harassment' => 'Hakaret veya taciz',
                    'copyright' => 'Telif hakkı ihlali',
                    'inappropriate' => 'Uygunsuz içerik',
                    'other' => 'Diğer',
                ]),
            ])
            ->recordActions([
                EditAction::make()->label('İncele'),
                DeleteAction::make()
                    ->label('Sil')
                    ->requiresConfirmation(),
            ]);
    }
}

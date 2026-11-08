<?php

namespace App\\Filament\\Resources\\Advertisements\\Tables;

use Filament\\Actions\\EditAction;
use Filament\\Tables\\Columns\\TextColumn;
use Filament\\Tables\\Filters\\SelectFilter;
use Filament\\Tables\\Table;

class AdvertisementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Reklam')
                    ->searchable()
                    ->description(fn ($record): string => $record->user?->username ?? 'Reklamveren yok')
                    ->limit(42)
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Yayın')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Onay bekliyor',
                        'approved' => 'Onaylandı',
                        'rejected' => 'Reddedildi',
                        'paused' => 'Durduruldu',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        'paused' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('billing_type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'paid' ? 'Ücretli' : 'Ücretsiz')
                    ->color(fn (string $state): string => $state === 'paid' ? 'info' : 'gray'),
                TextColumn::make('payment_status')
                    ->label('Ödeme')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Ödeme alındı',
                        'pending' => 'Bekliyor',
                        'failed' => 'Başarısız',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('starts_at')
                    ->label('Başlangıç')
                    ->dateTime('d.m.Y H:i', 'Europe/Istanbul')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ends_at')
                    ->label('Bitiş')
                    ->dateTime('d.m.Y H:i', 'Europe/Istanbul')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_budget')
                    ->label('Bütçe')
                    ->money('TRY')
                    ->sortable(),
                TextColumn::make('impressions')
                    ->label('Gösterim')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('clicks')
                    ->label('Tıklama')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Yayın durumu')
                    ->options([
                        'pending' => 'Onay bekliyor',
                        'approved' => 'Onaylandı',
                        'rejected' => 'Reddedildi',
                        'paused' => 'Durduruldu',
                    ]),
                SelectFilter::make('billing_type')
                    ->label('Reklam türü')
                    ->options(['free' => 'Ücretsiz', 'paid' => 'Ücretli']),
                SelectFilter::make('payment_status')
                    ->label('Ödeme durumu')
                    ->options([
                        'pending' => 'Ödeme bekliyor',
                        'paid' => 'Ödeme alındı',
                        'failed' => 'Ödeme başarısız',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Düzenle')
                    ->icon('heroicon-o-pencil-square'),
            ]);
    }
}

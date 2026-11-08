<?php

namespace App\Filament\Resources\Advertisements\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->description(fn ($record): string => str($record->description)->limit(72)->toString())
                    ->wrap()
                    ->weight('semibold'),
                TextColumn::make('user.username')->label('Reklamveren')->searchable()->sortable(),
                TextColumn::make('status')
                    ->label('Yayın durumu')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Onay bekliyor', 'approved' => 'Onaylandı',
                        'rejected' => 'Reddedildi', 'paused' => 'Durduruldu', default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning', 'approved' => 'success',
                        'rejected' => 'danger', 'paused' => 'gray', default => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->label('Ödeme')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Bekliyor', 'paid' => 'Ödendi', 'failed' => 'Başarısız', default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success', 'pending' => 'warning', 'failed' => 'danger', default => 'gray',
                    }),
                TextColumn::make('total_budget')->label('Bütçe')->money('TRY')->sortable(),
                TextColumn::make('spent_amount')->label('Harcanan')->money('TRY')->sortable(),
                TextColumn::make('impressions')->label('Gösterim')->numeric()->sortable(),
                TextColumn::make('clicks')->label('Tıklama')->numeric()->sortable(),
                TextColumn::make('starts_at')->label('Başlangıç')->dateTime('d.m.Y H:i', 'Europe/Istanbul')->sortable(),
                TextColumn::make('ends_at')->label('Bitiş')->dateTime('d.m.Y H:i', 'Europe/Istanbul')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Yayın durumu')->options([
                    'pending' => 'Onay bekliyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', 'paused' => 'Durduruldu',
                ]),
                SelectFilter::make('billing_type')->label('Reklam türü')->options(['free' => 'Ücretsiz', 'paid' => 'Ücretli']),
                SelectFilter::make('payment_status')->label('Ödeme durumu')->options([
                    'pending' => 'Bekliyor', 'paid' => 'Ödendi', 'failed' => 'Başarısız',
                ]),
            ])
            ->recordActions([EditAction::make()->label('Düzenle')]);
    }
}

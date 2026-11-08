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
        return $table->columns([
            TextColumn::make('title')->label('Reklam')->searchable()->limit(36),
            TextColumn::make('user.username')->label('Reklamveren')->searchable(),
            TextColumn::make('status')->label('Durum')->badge(),
            TextColumn::make('billing_type')->label('Tür')->badge(),
            TextColumn::make('payment_status')->label('Ödeme')->badge(),
            TextColumn::make('starts_at')->label('Başlangıç')->dateTime('d.m.Y H:i')->sortable(),
            TextColumn::make('ends_at')->label('Bitiş')->dateTime('d.m.Y H:i')->sortable(),
            TextColumn::make('impressions')->label('Gösterim')->numeric(),
            TextColumn::make('clicks')->label('Tıklama')->numeric(),
        ])->filters([
            SelectFilter::make('status')->label('Durum')->options([
                'pending' => 'Onay bekliyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', 'paused' => 'Durduruldu',
            ]),
            SelectFilter::make('billing_type')->label('Tür')->options(['free' => 'Ücretsiz', 'paid' => 'Ücretli']),
        ])->recordActions([EditAction::make()]);
    }
}

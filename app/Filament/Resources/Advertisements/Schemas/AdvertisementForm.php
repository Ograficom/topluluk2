<?php

namespace App\\Filament\\Resources\\Advertisements\\Schemas;

use Filament\\Forms\\Components\\DateTimePicker;
use Filament\\Forms\\Components\\Select;
use Filament\\Forms\\Components\\Textarea;
use Filament\\Forms\\Components\\TextInput;
use Filament\\Forms\\Components\\Toggle;
use Filament\\Schemas\\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Başlık')->required()->maxLength(160),
            Textarea::make('description')->label('Açıklama')->required()->columnSpanFull(),
            TextInput::make('target_url')->label('Hedef bağlantı')->url()->required()->columnSpanFull(),
            TextInput::make('button_text')->label('Buton yazısı')->required()->maxLength(40),
            DateTimePicker::make('starts_at')->label('Başlangıç')->required(),
            DateTimePicker::make('ends_at')->label('Bitiş')->required(),
            Select::make('status')->label('Yayın durumu')->options([
                'pending' => 'Onay bekliyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', 'paused' => 'Durduruldu',
            ])->required(),
            Select::make('billing_type')->label('Reklam türü')->options(['free' => 'Ücretsiz', 'paid' => 'Ücretli'])->required()->live(),
            Select::make('payment_status')->label('Ödeme durumu')->options([
                'pending' => 'Ödeme bekliyor', 'paid' => 'Ödeme alındı', 'failed' => 'Ödeme başarısız',
            ])->required(),
            TextInput::make('total_budget')->label('Toplam bütçe')->numeric()->required(),
            TextInput::make('daily_budget')->label('Günlük bütçe')->numeric()->required(),
            TextInput::make('spent_amount')->label('Harcanan tutar')->numeric()->required(),
            Textarea::make('rejection_reason')->label('Ret nedeni'),
            Toggle::make('free_admin_ad')->label('Ücretsiz yönetici reklamı')->dehydrated(false)
                ->helperText('Ücretsiz seçilirse ödeme kontrolü aranmaz.'),
        ]);
    }
}

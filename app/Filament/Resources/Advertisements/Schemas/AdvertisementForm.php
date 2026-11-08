<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->label('Reklamveren')->relationship('user', 'username')->searchable()->preload()->required(),
            TextInput::make('title')->label('Başlık')->required()->maxLength(160),
            Textarea::make('description')->label('Açıklama')->required()->columnSpanFull(),
            TextInput::make('image_path')->label('Görsel dosya yolu')->maxLength(2048),
            TextInput::make('video_url')->label('Video bağlantısı')->url()->maxLength(2048),
            TextInput::make('target_url')->label('Hedef bağlantı')->url()->required()->columnSpanFull(),
            TextInput::make('button_text')->label('Buton yazısı')->required()->maxLength(40),
            DateTimePicker::make('starts_at')->label('Başlangıç')->timezone('Europe/Istanbul')->required(),
            DateTimePicker::make('ends_at')->label('Bitiş')->timezone('Europe/Istanbul')->required(),
            Select::make('status')->label('Yayın durumu')->default('pending')->options([
                'pending' => 'Onay bekliyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', 'paused' => 'Durduruldu',
            ])->required(),
            Select::make('billing_type')->label('Reklam türü')->options(['free' => 'Ücretsiz', 'paid' => 'Ücretli'])->default('free')->required()->live(),
            Select::make('payment_status')->label('Ödeme durumu')->options([
                'pending' => 'Ödeme bekliyor', 'paid' => 'Ödeme alındı', 'failed' => 'Ödeme başarısız',
            ])->default('paid')->required(),
            TextInput::make('total_budget')->label('Toplam bütçe')->numeric()->default(0)->required(),
            TextInput::make('daily_budget')->label('Günlük bütçe')->numeric()->default(0)->required(),
            TextInput::make('spent_amount')->label('Harcanan tutar')->numeric()->default(0)->required(),
            Textarea::make('rejection_reason')->label('Ret nedeni'),
        ]);
    }
}

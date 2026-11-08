<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Reklam içeriği')
                ->description('Reklamın sahibi, başlığı ve ziyaretçilerin göreceği metin.')
                ->icon('heroicon-o-megaphone')
                ->columns(2)
                ->schema([
                    Select::make('user_id')->label('Reklamveren')->relationship('user', 'username')->searchable()->preload()->required(),
                    TextInput::make('title')->label('Başlık')->required()->maxLength(160)->columnSpan(1),
                    Textarea::make('description')->label('Açıklama')->rows(5)->required()->maxLength(2000)->columnSpanFull(),
                    FileUpload::make('image_path')->label('Reklam görseli')->image()->disk('public')->directory('advertisements')->visibility('public')->imageEditor()->maxSize(5120),
                    TextInput::make('video_url')->label('Video bağlantısı')->url()->maxLength(2048),
                    TextInput::make('target_url')->label('Harici hedef bağlantı')->url()->maxLength(2048)->helperText('Boş bırakılırsa reklamın ayrıntı sayfası açılır.'),
                    TextInput::make('button_text')->label('Buton yazısı')->default('Daha fazla bilgi al')->maxLength(40),
                ]),
            Section::make('Yayın planı')
                ->description('Reklamın yayınlanacağı tarih aralığı ve onay durumu.')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([
                    DateTimePicker::make('starts_at')->label('Yayın başlangıcı')->timezone('Europe/Istanbul')->required(),
                    DateTimePicker::make('ends_at')->label('Yayın bitişi')->timezone('Europe/Istanbul')->required()->after('starts_at'),
                    Select::make('status')->label('Yayın durumu')->options([
                        'pending' => 'Onay bekliyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', 'paused' => 'Durduruldu',
                    ])->required()->native(false),
                    Textarea::make('rejection_reason')->label('Ret nedeni')->rows(2)->columnSpanFull(),
                ]),
            Section::make('Ödeme ve bütçe')
                ->description('Ücretlendirme, tahsilat ve reklam harcaması.')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->schema([
                    Select::make('billing_type')->label('Reklam türü')->options(['free' => 'Ücretsiz', 'paid' => 'Ücretli'])->required()->native(false),
                    Select::make('payment_status')->label('Ödeme durumu')->options([
                        'pending' => 'Ödeme bekliyor', 'paid' => 'Ödeme alındı', 'failed' => 'Ödeme başarısız',
                    ])->required()->native(false),
                    TextInput::make('total_budget')->label('Toplam bütçe (₺)')->numeric()->prefix('₺')->required(),
                    TextInput::make('daily_budget')->label('Günlük bütçe (₺)')->numeric()->prefix('₺')->required(),
                    TextInput::make('spent_amount')->label('Harcanan tutar (₺)')->numeric()->prefix('₺')->required(),
                ]),
        ]);
    }
}

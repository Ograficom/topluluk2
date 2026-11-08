<?php

namespace App\\Filament\\Resources\\Advertisements\\Schemas;

use Filament\\Forms\\Components\\DateTimePicker;
use Filament\\Forms\\Components\\Select;
use Filament\\Forms\\Components\\Textarea;
use Filament\\Forms\\Components\\TextInput;
use Filament\\Schemas\\Components\\Section;
use Filament\\Schemas\\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Reklam içeriği')
                ->description('Reklamın sahibi, başlığı ve ziyaretçilere gösterilecek açıklama.')
                ->icon('heroicon-o-megaphone')
                ->columns(2)
                ->schema([
                    Select::make('user_id')
                        ->label('Reklamveren')
                        ->relationship('user', 'username')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('title')
                        ->label('Başlık')
                        ->required()
                        ->maxLength(160),
                    Textarea::make('description')
                        ->label('Açıklama')
                        ->rows(6)
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('image_path')
                        ->label('Görsel dosya yolu')
                        ->helperText('public disk üzerindeki dosya yolu.')
                        ->maxLength(2048),
                    TextInput::make('video_url')
                        ->label('Video bağlantısı')
                        ->url()
                        ->maxLength(2048),
                ]),
            Section::make('Yönlendirme')
                ->description('Ziyaretçinin reklam ayrıntısından ulaşabileceği isteğe bağlı dış bağlantı.')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->columns(2)
                ->schema([
                    TextInput::make('target_url')
                        ->label('Hedef bağlantı')
                        ->url()
                        ->helperText('Boş bırakılırsa reklam yalnızca kendi ayrıntı sayfasını gösterir.')
                        ->columnSpanFull(),
                    TextInput::make('button_text')
                        ->label('Buton yazısı')
                        ->default('Daha fazla bilgi al')
                        ->maxLength(40),
                ]),
            Section::make('Yayın ve onay')
                ->description('Yayın tarihlerini ve reklamın moderasyon durumunu yönetin.')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->label('Başlangıç')
                        ->timezone('Europe/Istanbul')
                        ->seconds(false)
                        ->required(),
                    DateTimePicker::make('ends_at')
                        ->label('Bitiş')
                        ->timezone('Europe/Istanbul')
                        ->seconds(false)
                        ->after('starts_at')
                        ->required(),
                    Select::make('status')
                        ->label('Yayın durumu')
                        ->options([
                            'pending' => 'Onay bekliyor',
                            'approved' => 'Onaylandı',
                            'rejected' => 'Reddedildi',
                            'paused' => 'Durduruldu',
                        ])
                        ->native(false)
                        ->required(),
                    Textarea::make('rejection_reason')
                        ->label('Ret nedeni')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make('Bütçe ve ödeme')
                ->description('Ücretlendirme, ödeme onayı ve harcama bilgileri.')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->schema([
                    Select::make('billing_type')
                        ->label('Reklam türü')
                        ->options(['free' => 'Ücretsiz', 'paid' => 'Ücretli'])
                        ->native(false)
                        ->default('free')
                        ->required(),
                    Select::make('payment_status')
                        ->label('Ödeme durumu')
                        ->options([
                            'pending' => 'Ödeme bekliyor',
                            'paid' => 'Ödeme alındı',
                            'failed' => 'Ödeme başarısız',
                        ])
                        ->native(false)
                        ->default('paid')
                        ->required(),
                    TextInput::make('total_budget')
                        ->label('Toplam bütçe (₺)')
                        ->numeric()
                        ->prefix('₺')
                        ->default(0)
                        ->required(),
                    TextInput::make('daily_budget')
                        ->label('Günlük bütçe (₺)')
                        ->numeric()
                        ->prefix('₺')
                        ->default(0)
                        ->required(),
                    TextInput::make('spent_amount')
                        ->label('Harcanan tutar (₺)')
                        ->numeric()
                        ->prefix('₺')
                        ->default(0)
                        ->required(),
                ]),
        ]);
    }
}

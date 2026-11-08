<?php

namespace App\\Filament\\Pages;

use App\\Models\\SiteSetting;
use Filament\\Forms\\Components\\TextInput;
use Filament\\Notifications\\Notification;
use Filament\\Pages\\Page;
use Filament\\Schemas\\Components\\Section;
use Filament\\Schemas\\Schema;
use Filament\\Support\\Icons\\Heroicon;

class AdvertisementPricing extends Page
{
    protected static string|\\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'Reklam fiyatlandırması';
    protected static ?string $title = 'Reklam fiyatlandırması';
    protected static ?int $navigationSort = 5;
    protected static string|\\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.advertisement-pricing';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'advertisement_daily_price' => SiteSetting::current()?->advertisement_daily_price ?? 200,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Günlük reklam ücreti')
                    ->description('Reklam başvuru formundaki toplam ücret, seçilen yayın süresi ile bu günlük fiyat çarpılarak hesaplanır.')
                    ->schema([
                        TextInput::make('advertisement_daily_price')
                            ->label('1 günlük reklam ücreti (₺)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1000000)
                            ->step(0.01)
                            ->required()
                            ->suffix('₺')
                            ->helperText('Örnek: 200 TL/gün. 7 günlük reklamın ücreti otomatik olarak 1.400 TL olur.')
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SiteSetting::query()->firstOrNew(['id' => 1]);
        $settings->advertisement_daily_price = $data['advertisement_daily_price'];
        $settings->save();

        Notification::make()
            ->title('Reklam fiyatı kaydedildi.')
            ->success()
            ->send();
    }
}

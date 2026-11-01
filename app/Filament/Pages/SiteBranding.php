<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteBranding extends Page
{
    protected static ?string $navigationLabel = 'Marka ayarları';

    protected static ?string $title = 'Site markası';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.site-branding';

    public ?array $data = [];

    public ?string $currentLogoUrl = null;

    public function mount(): void
    {
        $settings = SiteSetting::current();
        $this->currentLogoUrl = $settings?->brandLogoUrl();

        $this->form->fill([
            'brand_name' => $settings?->brand_name ?? SiteSetting::DEFAULT_BRAND_NAME,
            'footer_year' => $settings?->footer_year ?? now()->year,
            'instagram_url' => $settings?->instagram_url,
            'telegram_url' => $settings?->telegram_url,
            'threads_url' => $settings?->threads_url,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('brand_name')
                    ->label('Marka adı')
                    ->required()
                    ->maxLength(80),
                TextInput::make('footer_year')
                    ->label('Telif yılı')
                    ->numeric()
                    ->integer()
                    ->minValue(1900)
                    ->maxValue(9999)
                    ->required(),
                TextInput::make('instagram_url')
                    ->label('Instagram bağlantısı')
                    ->url()
                    ->maxLength(2048)
                    ->nullable(),
                TextInput::make('telegram_url')
                    ->label('Telegram bağlantısı')
                    ->url()
                    ->maxLength(2048)
                    ->nullable(),
                TextInput::make('threads_url')
                    ->label('Threads bağlantısı')
                    ->url()
                    ->maxLength(2048)
                    ->nullable(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SiteSetting::current() ?? new SiteSetting;

        $settings->id = 1;
        $settings->brand_name = $data['brand_name'];
        $settings->footer_year = $data['footer_year'];
        $settings->instagram_url = filled($data['instagram_url'] ?? null) ? $data['instagram_url'] : null;
        $settings->telegram_url = filled($data['telegram_url'] ?? null) ? $data['telegram_url'] : null;
        $settings->threads_url = filled($data['threads_url'] ?? null) ? $data['threads_url'] : null;
        $settings->save();

        $this->currentLogoUrl = $settings->brandLogoUrl();

        Notification::make()
            ->title('Marka ayarları kaydedildi.')
            ->success()
            ->send();
    }
}

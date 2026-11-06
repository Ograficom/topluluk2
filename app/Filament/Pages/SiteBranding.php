<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteBranding extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    protected static ?string $navigationLabel = 'Marka ayarları';

    protected static ?string $title = 'Site markası';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.site-branding';

    public ?array $data = [];

    public ?string $currentLogoUrl = null;

    public ?string $currentFaviconUrl = null;

    public function mount(): void
    {
        $settings = SiteSetting::current();
        $this->currentLogoUrl = $settings?->brandLogoUrl();
        $this->currentFaviconUrl = $settings?->faviconUrl();

        $this->form->fill([
            'brand_name' => $settings?->brand_name ?? SiteSetting::DEFAULT_BRAND_NAME,
            'footer_year' => $settings?->footer_year ?? now()->year,
            'instagram_url' => $settings?->instagram_url,
            'telegram_url' => $settings?->telegram_url,
            'threads_url' => $settings?->threads_url,
            'google_login_enabled' => $settings?->google_login_enabled ?? false,
            'google_client_id' => $settings?->google_client_id,
            'google_client_secret' => null,
            'yandex_login_enabled' => $settings?->yandex_login_enabled ?? false,
            'yandex_client_id' => $settings?->yandex_client_id,
            'yandex_client_secret' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Marka kimliği')
                    ->description('Sitenin adı ve alt bilgisinde görünecek yıl.')
                    ->schema([
                        TextInput::make('brand_name')
                            ->label('Marka adı')
                            ->required()
                            ->maxLength(80)
                            ->columnSpanFull(),
                        TextInput::make('footer_year')
                            ->label('Telif yılı')
                            ->numeric()
                            ->integer()
                            ->minValue(1900)
                            ->maxValue(9999)
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Google ile giriş')
                    ->description('Google OAuth uygulama bilgileri. Geri dönüş URL: '.route('oauth.callback', ['provider' => 'google']))
                    ->schema([
                        Toggle::make('google_login_enabled')->label('Google ile girişi etkinleştir'),
                        TextInput::make('google_client_id')->label('OAuth Client ID')->maxLength(255)->nullable(),
                        TextInput::make('google_client_secret')->label('OAuth Client Secret')->password()->revealable()->nullable()->helperText('Kayıtlı anahtar güvenlik için gösterilmez. Değiştirmeyecekseniz boş bırakın.'),
                    ])->columns(2),
                Section::make('Yandex ile giriş')
                    ->description('Yandex OAuth uygulama bilgileri. Geri dönüş URL: '.route('oauth.callback', ['provider' => 'yandex']))
                    ->schema([
                        Toggle::make('yandex_login_enabled')->label('Yandex ile girişi etkinleştir'),
                        TextInput::make('yandex_client_id')->label('OAuth Client ID')->maxLength(255)->nullable(),
                        TextInput::make('yandex_client_secret')->label('OAuth Client Secret')->password()->revealable()->nullable()->helperText('Kayıtlı anahtar güvenlik için gösterilmez. Değiştirmeyecekseniz boş bırakın.'),
                    ])->columns(2),
                Section::make('Sosyal bağlantılar')
                    ->description('Boş bıraktığınız bağlantılar sitede gösterilmez.')
                    ->schema([
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->maxLength(2048)
                            ->nullable(),
                        TextInput::make('telegram_url')
                            ->label('Telegram')
                            ->url()
                            ->maxLength(2048)
                            ->nullable(),
                        TextInput::make('threads_url')
                            ->label('Threads')
                            ->url()
                            ->maxLength(2048)
                            ->nullable(),
                    ])
                    ->columns(2),
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
        $settings->google_login_enabled = (bool) ($data['google_login_enabled'] ?? false);
        $settings->google_client_id = filled($data['google_client_id'] ?? null) ? $data['google_client_id'] : null;
        if (filled($data['google_client_secret'] ?? null)) $settings->google_client_secret = $data['google_client_secret'];
        $settings->yandex_login_enabled = (bool) ($data['yandex_login_enabled'] ?? false);
        $settings->yandex_client_id = filled($data['yandex_client_id'] ?? null) ? $data['yandex_client_id'] : null;
        if (filled($data['yandex_client_secret'] ?? null)) $settings->yandex_client_secret = $data['yandex_client_secret'];
        if ($settings->google_login_enabled && (! filled($settings->google_client_id) || ! filled($settings->google_client_secret))) { Notification::make()->title('Google girişi için Client ID ve Secret gerekli.')->danger()->send(); return; }
        if ($settings->yandex_login_enabled && (! filled($settings->yandex_client_id) || ! filled($settings->yandex_client_secret))) { Notification::make()->title('Yandex girişi için Client ID ve Secret gerekli.')->danger()->send(); return; }
        $settings->save();

        $this->currentLogoUrl = $settings->brandLogoUrl();
        $this->currentFaviconUrl = $settings->faviconUrl();

        Notification::make()
            ->title('Marka ayarları kaydedildi.')
            ->success()
            ->send();
    }
}

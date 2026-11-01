<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class SiteBranding extends Page
{
    protected static ?string $navigationLabel = 'Marka ayarları';

    protected static ?string $title = 'Site markası';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.site-branding';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->form->fill([
            'brand_name' => $settings?->brand_name ?? SiteSetting::DEFAULT_BRAND_NAME,
            'brand_logo_path' => $settings?->brand_logo_path,
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
                FileUpload::make('brand_logo_path')
                    ->label('Logo simgesi')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('site-branding')
                    ->visibility('public')
                    ->imagePreviewHeight('80')
                    ->helperText('PNG, JPG veya WebP; en fazla 2 MB. Boş bırakırsanız varsayılan simge kullanılır.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SiteSetting::current() ?? new SiteSetting;
        $previousLogo = $settings->brand_logo_path;

        $settings->id = 1;
        $settings->brand_name = $data['brand_name'];
        $settings->brand_logo_path = $data['brand_logo_path'] ?? null;
        $settings->save();

        if (filled($previousLogo) && $previousLogo !== $settings->brand_logo_path) {
            Storage::disk('public')->delete($previousLogo);
        }

        Notification::make()
            ->title('Marka ayarları kaydedildi.')
            ->success()
            ->send();
    }
}

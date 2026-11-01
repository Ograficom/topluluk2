<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\SvgSanitizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                FileUpload::make('brand_logo_path')
                    ->label('Logo dosyası')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('site-branding')
                    ->visibility('public')
                    ->imagePreviewHeight('80')
                    ->saveUploadedFileUsing(function (FileUpload $component, TemporaryUploadedFile $file): ?string {
                        if ($file->getMimeType() !== 'image/svg+xml') {
                            return $component->saveUploadedFile($file);
                        }

                        try {
                            $contents = app(SvgSanitizer::class)->sanitize($file->getContent());
                        } catch (\InvalidArgumentException) {
                            throw ValidationException::withMessages([
                                'data.brand_logo_path' => 'SVG dosyası geçersiz veya güvenli biçimde işlenemedi.',
                            ]);
                        }

                        $path = trim($component->getDirectory().'/'.Str::uuid().'.svg', '/');
                        $component->getDisk()->put($path, $contents);
                        $component->getDisk()->setVisibility($path, 'public');

                        return $path;
                    })
                    ->helperText('SVG, PNG, JPG veya WebP; en fazla 2 MB. Boş bırakırsanız varsayılan simge kullanılır.'),
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
        $settings->footer_year = $data['footer_year'];
        $settings->instagram_url = filled($data['instagram_url'] ?? null) ? $data['instagram_url'] : null;
        $settings->telegram_url = filled($data['telegram_url'] ?? null) ? $data['telegram_url'] : null;
        $settings->threads_url = filled($data['threads_url'] ?? null) ? $data['threads_url'] : null;
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

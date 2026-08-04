<?php

namespace App\Filament\Pages;

use App\Models\ThemeSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThemeExportImport extends Page
{
    protected string $view = 'filament.pages.theme-export-import';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'Tema Indir / Yukle';
    protected static ?int $navigationSort = 25;

    /** ThemeSetting fields that store a path on the "public" disk rather than a plain value. */
    private const FILE_FIELDS = [
        'header_logo_image',
        'font_heading_file',
        'font_body_file',
        'font_button_file',
        'font_nav_file',
        'font_code_file',
        'custom_css_file',
    ];

    public function getSettings(): ThemeSetting
    {
        return ThemeSetting::current();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Temayi Indir')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(fn () => $this->exportTheme()),

            Action::make('import')
                ->label('Tema Yukle')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->schema([
                    FileUpload::make('file')
                        ->label('Tema dosyasi (.json)')
                        ->acceptedFileTypes(['application/json', 'text/plain', '.json'])
                        ->required()
                        ->disk('local')
                        ->directory('theme-imports')
                        ->visibility('private'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Temayi ice aktar')
                ->modalDescription('Bu islem sitenin su anki gorunum ayarlarinin (renkler, fontlar, ozel CSS) tamamen uzerine yazilacak. Devam etmeden once mevcut temani "Temayi Indir" ile yedeklemen onerilir.')
                ->modalSubmitActionLabel('Ice aktar ve uygula')
                ->action(function (array $data): void {
                    $this->importTheme((string) $data['file']);
                }),
        ];
    }

    /**
     * Bundles every ThemeSetting field - including uploaded font/CSS/logo files, embedded
     * as base64 - into a single portable JSON file so the current look can be backed up or
     * moved to another Ografi install.
     */
    private function exportTheme()
    {
        $settings = ThemeSetting::current();
        $data = Arr::only($settings->toArray(), $settings->getFillable());

        $files = [];
        foreach (self::FILE_FIELDS as $field) {
            $path = $data[$field] ?? null;
            unset($data[$field]);

            if (! $path || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $files[$field] = [
                'filename' => basename((string) $path),
                'mime' => Storage::disk('public')->mimeType($path) ?: 'application/octet-stream',
                'content_base64' => base64_encode(Storage::disk('public')->get($path)),
            ];
        }

        $payload = [
            'ografi_theme_export' => true,
            'version' => 1,
            'exported_at' => now()->toAtomString(),
            'site' => config('app.url'),
            'settings' => $data,
            'files' => $files,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'ografi-tema-' . now()->format('Y-m-d-His') . '.json';

        return response()->streamDownload(
            fn () => print ($json === false ? '{}' : $json),
            $filename,
            ['Content-Type' => 'application/json'],
        );
    }

    private function importTheme(string $uploadedPath): void
    {
        if ($uploadedPath === '' || ! Storage::disk('local')->exists($uploadedPath)) {
            $this->notifyImportFailure('Yuklenen dosya bulunamadi.');

            return;
        }

        $raw = Storage::disk('local')->get($uploadedPath);
        Storage::disk('local')->delete($uploadedPath);

        $payload = json_decode((string) $raw, true);

        if (
            ! is_array($payload)
            || ($payload['ografi_theme_export'] ?? false) !== true
            || ! is_array($payload['settings'] ?? null)
        ) {
            $this->notifyImportFailure('Dosya gecerli bir Ografi tema paketi degil.');

            return;
        }

        $settings = ThemeSetting::current();
        $allowedFields = $settings->getFillable();

        $incoming = Arr::except(Arr::only($payload['settings'], $allowedFields), self::FILE_FIELDS);
        // Defensive: a hand-edited/corrupted export could carry an array/object for what
        // should be a plain string, number or boolean column - drop anything that isn't.
        $incoming = array_filter($incoming, fn ($value) => is_scalar($value) || $value === null);

        foreach ((array) ($payload['files'] ?? []) as $field => $file) {
            if (! in_array($field, self::FILE_FIELDS, true) || ! in_array($field, $allowedFields, true)) {
                continue;
            }

            if (! is_array($file) || ! is_string($file['content_base64'] ?? null) || ! is_string($file['filename'] ?? null)) {
                continue;
            }

            $decoded = base64_decode($file['content_base64'], true);
            if ($decoded === false) {
                continue;
            }

            $extension = pathinfo($file['filename'], PATHINFO_EXTENSION) ?: 'bin';
            $directory = match ($field) {
                'custom_css_file' => 'custom-theme',
                'header_logo_image' => 'theme',
                default => 'fonts',
            };
            $storedPath = $directory . '/' . Str::random(20) . '.' . $extension;

            Storage::disk('public')->put($storedPath, $decoded);
            $incoming[$field] = $storedPath;
        }

        $settings->update($incoming);
        Cache::forget('theme-appearance-css');

        Notification::make()
            ->title('Tema ice aktarildi')
            ->body('Yeni gorunum ayarlari siteye uygulandi.')
            ->success()
            ->send();
    }

    private function notifyImportFailure(string $message): void
    {
        Notification::make()
            ->title('Tema yuklenemedi')
            ->body($message)
            ->danger()
            ->send();
    }
}

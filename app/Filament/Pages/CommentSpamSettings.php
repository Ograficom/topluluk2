<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CommentSpamSettings extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'Spam yorum filtresi';
    protected static ?string $title = 'Spam yorum filtresi';
    protected static ?int $navigationSort = 6;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected string $view = 'filament.pages.comment-spam-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->form->fill([
            'comment_spam_enabled' => $settings?->comment_spam_enabled ?? true,
            'comment_spam_words' => implode("\n", $settings?->comment_spam_words ?? []),
            'comment_spam_max_links' => $settings?->comment_spam_max_links ?? 2,
            'comment_spam_rate_limit' => $settings?->comment_spam_rate_limit ?? 4,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Otomatik spam denetimi')
                ->description('Şüpheli yorumlar silinmez; yönetim panelindeki yorum listesinde spam olarak işaretlenir ve ziyaretçilere gösterilmez.')
                ->schema([
                    Toggle::make('comment_spam_enabled')
                        ->label('Spam filtresini etkinleştir')
                        ->default(true)
                        ->columnSpanFull(),
                    Textarea::make('comment_spam_words')
                        ->label('Yasaklı kelimeler')
                        ->placeholder("Her satıra bir kelime veya ifade yazın\nörnek ifade\nreklam kelimesi")
                        ->rows(12)
                        ->helperText('Her satıra bir kelime veya ifade. Noktalama ve harf varyasyonları normalize edilerek kontrol edilir.')
                        ->columnSpanFull(),
                    TextInput::make('comment_spam_max_links')
                        ->label('İzin verilen en fazla bağlantı')
                        ->numeric()->minValue(0)->maxValue(20)->required(),
                    TextInput::make('comment_spam_rate_limit')
                        ->label('Dakikada izin verilen yorum')
                        ->numeric()->minValue(1)->maxValue(30)->required()
                        ->helperText('Bu sınır aşıldığında yeni yorum spam incelemesine alınır.'),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SiteSetting::current() ?? new SiteSetting();
        $settings->id = 1;
        $words = preg_split('/\R/u', (string) ($data['comment_spam_words'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $settings->fill([
            'comment_spam_enabled' => (bool) ($data['comment_spam_enabled'] ?? false),
            'comment_spam_words' => collect($words)->map(fn ($word) => trim($word))->filter()->unique()->values()->all(),
            'comment_spam_max_links' => (int) $data['comment_spam_max_links'],
            'comment_spam_rate_limit' => (int) $data['comment_spam_rate_limit'],
        ]);
        $settings->save();

        Notification::make()->title('Spam yorum filtresi kaydedildi.')->success()->send();
    }
}

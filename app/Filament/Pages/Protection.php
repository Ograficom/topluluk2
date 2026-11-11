<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Protection extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = null;
    protected static ?string $navigationLabel = 'Koruma';
    protected static ?string $title = 'Koruma';
    protected static ?int $navigationSort = 1;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected string $view = 'filament.pages.protection';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->form->fill([
            'comment_spam_enabled' => $settings?->comment_spam_enabled ?? true,
            'comment_spam_words' => implode("\n", $settings?->comment_spam_words ?? []),
            'comment_spam_max_links' => $settings?->comment_spam_max_links ?? 2,
            'comment_spam_rate_limit' => $settings?->comment_spam_rate_limit ?? 4,
            'post_spam_enabled' => $settings?->post_spam_enabled ?? true,
            'post_spam_words' => implode("\n", $settings?->post_spam_words ?? []),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Koruma ayarları')
                ->tabs([
                    Tab::make('Yorum koruması')
                        ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->schema([
                            Section::make('Yorum spam filtresi')
                                ->description('Yasaklı kelime ve ifadeleri içeren yorumlar otomatik olarak incelemeye alınır.')
                                ->schema([
                                    Toggle::make('comment_spam_enabled')
                                        ->label('Yorum spam filtresini etkinleştir')
                                        ->default(true)
                                        ->inline(false),
                                    Textarea::make('comment_spam_words')
                                        ->label('Yasaklı kelimeler ve ifadeler')
                                        ->placeholder("Her satıra bir kelime veya ifade yazın")
                                        ->helperText('Örnek: reklam, bahis, istenmeyen bağlantılar. Birden fazla kelime içeren ifadeler de ekleyebilirsiniz.')
                                        ->rows(9)
                                        ->columnSpanFull(),
                                ]),
                            Section::make('Yorum gönderim sınırları')
                                ->description('Kısa sürede yapılan yoğun yorum gönderimlerini ve bağlantı spamini sınırlar.')
                                ->schema([
                                    \\Filament\\Forms\\Components\\TextInput::make('comment_spam_max_links')
                                        ->label('İzin verilen en fazla bağlantı')
                                        ->numeric()->minValue(0)->maxValue(20)->required(),
                                    \\Filament\\Forms\\Components\\TextInput::make('comment_spam_rate_limit')
                                        ->label('Dakikada izin verilen yorum')
                                        ->numeric()->minValue(1)->maxValue(30)->required(),
                                ])->columns(2),
                        ]),
                    Tab::make('Post koruması')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->schema([
                            Section::make('Post spam filtresi')
                                ->description('Post başlığı, açıklaması ve içeriği yasaklı kelimelere göre kontrol edilir. Eşleşen postlar yönetici onayına kadar yayına çıkmaz.')
                                ->schema([
                                    Toggle::make('post_spam_enabled')
                                        ->label('Post spam filtresini etkinleştir')
                                        ->default(true)
                                        ->inline(false),
                                    Textarea::make('post_spam_words')
                                        ->label('Postlarda yasaklı kelimeler')
                                        ->placeholder("Her satıra bir kelime veya ifade yazın")
                                        ->helperText('Bu liste yalnızca yeni postların başlık, açıklama ve içeriklerinde uygulanır.')
                                        ->rows(9)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('Spam listeleri')
                        ->icon(Heroicon::OutlinedQueueList)
                        ->schema([
                            Section::make('İnceleme listeleri')
                                ->description('Filtreye takılan içerikleri buradan açıp kontrol edebilir, hatalı işaretlenenleri onaylayabilirsiniz.')
                                ->schema([
                                    \\Filament\\Forms\\Components\\Placeholder::make('comment_spam_list_link')
                                        ->label('Yorumlar')
                                        ->content(new \\Illuminate\\Support\\HtmlString('<a class="fi-link fi-size-md" href="'.url('/admin/comments?tableFilters[is_spam][value]=1').'">Spam yorum listesini aç →</a>')),
                                    \\Filament\\Forms\\Components\\Placeholder::make('post_spam_list_link')
                                        ->label('Postlar')
                                        ->content(new \\Illuminate\\Support\\HtmlString('<a class="fi-link fi-size-md" href="'.url('/admin/posts?tableFilters[is_spam][value]=1').'">Spam post listesini aç →</a>')),
                                ]),
                        ]),
                ])
                ->contained(false),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SiteSetting::current() ?? new SiteSetting();
        $settings->id = 1;

        $toWords = fn (string $value): array => collect(preg_split('/\R/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->map(fn ($word) => trim($word))->filter()->unique()->values()->all();

        $settings->fill([
            'comment_spam_enabled' => (bool) ($data['comment_spam_enabled'] ?? false),
            'comment_spam_words' => $toWords((string) ($data['comment_spam_words'] ?? '')),
            'comment_spam_max_links' => (int) $data['comment_spam_max_links'],
            'comment_spam_rate_limit' => (int) $data['comment_spam_rate_limit'],
            'post_spam_enabled' => (bool) ($data['post_spam_enabled'] ?? false),
            'post_spam_words' => $toWords((string) ($data['post_spam_words'] ?? '')),
        ]);
        $settings->save();

        Notification::make()->title('Koruma ayarları kaydedildi.')->success()->send();
    }
}

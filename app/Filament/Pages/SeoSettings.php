<?php

namespace App\Filament\Pages;

use App\Models\SeoPageSetting;
use App\Models\SiteSetting;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SeoSettings extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'SEO ayarları';
    protected static ?string $title = 'Google ve site SEO yönetimi';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.seo-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->form->fill([
            'home_seo_title' => $settings?->home_seo_title,
            'home_seo_description' => $settings?->home_seo_description,
            'home_seo_og_image' => $settings?->home_seo_og_image,
            'home_seo_index' => $settings?->home_seo_index ?? true,
            'google_site_verification' => $settings?->google_site_verification,
            'seo_default_title' => $settings?->seo_default_title,
            'seo_default_description' => $settings?->seo_default_description,
            'seo_og_image' => $settings?->seo_og_image,
            'pages' => SeoPageSetting::query()
                ->orderBy('route_name')
                ->get()
                ->map(fn (SeoPageSetting $page) => $page->only([
                    'route_name', 'title', 'description', 'canonical_url', 'og_image', 'robots_index',
                ]))
                ->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Google Search Console')
                ->description('Search Console mülkünüzü HTML meta etiketiyle doğrulamak için Google’ın verdiği yalnızca doğrulama kodunu girin. Tam meta etiketini veya URL’yi yapıştırmayın.')
                ->schema([
                    TextInput::make('google_site_verification')
                        ->label('Google site doğrulama kodu')
                        ->placeholder('Google Search Console doğrulama kodu')
                        ->maxLength(255)
                        ->helperText('Kaydedildiğinde tüm herkese açık sayfaların HTML head alanına google-site-verification etiketi eklenir.')
                        ->columnSpanFull(),
                ]),

            Section::make('Ana sayfa Google görünümü')
                ->description('Ana sayfanın Google sonuçlarında kullanabileceği başlık ve açıklama. Google, arama sorgusuna göre başlığı veya açıklama metnini kendisi değiştirebilir.')
                ->schema([
                    TextInput::make('home_seo_title')
                        ->label('Ana sayfa SEO başlığı')
                        ->maxLength(255)
                        ->placeholder('OgraFi – Fikirlerin ve toplulukların buluşma noktası')
                        ->helperText('Kısa, özgün ve sayfanın içeriğini doğru anlatan bir başlık yazın. Site adı otomatik sona eklenir; burada tekrar yazmayın.')
                        ->columnSpanFull(),
                    Textarea::make('home_seo_description')
                        ->label('Ana sayfa meta açıklaması')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('OgraFi topluluğunda teknoloji, gündem ve ilgi alanlarını keşfedin; yazılar paylaşın ve tartışmalara katılın.')
                        ->helperText('Sayfayı özetleyen doğal bir açıklama yazın. Google bu metni her aramada göstermek zorunda değildir.')
                        ->columnSpanFull(),
                    Toggle::make('home_seo_index')
                        ->label('Ana sayfanın Google tarafından indekslenmesine izin ver')
                        ->default(true)
                        ->helperText('Normalde açık kalmalıdır. Kapatılırsa ana sayfaya noindex yönergesi gönderilir.'),
                ])->columns(2),

            Section::make('Ana sayfa ve sosyal paylaşım görseli')
                ->description('Bu görsel Google sıralama ayarı değildir; bağlantı Facebook, X, WhatsApp ve benzeri platformlarda paylaşıldığında kullanılan Open Graph görselidir. Önerilen oran 1.91:1, örneğin 1200 × 630 px.')
                ->schema([
                    TextInput::make('home_seo_og_image')
                        ->label('Ana sayfa paylaşım görseli URL')
                        ->url()
                        ->maxLength(2048)
                        ->placeholder('https://ografi.com/storage/seo/ana-sayfa.jpg')
                        ->helperText('Boş bırakılırsa genel paylaşım görseli kullanılır.')
                        ->columnSpanFull(),
                ]),

            Section::make('Diğer herkese açık sayfalar')
                ->description('Buradan liste ve sabit rotaları ayrı ayrı yönetin. Yazılar, kanallar, statik sayfalar ve kullanıcı profilleri kendi düzenleme ekranlarındaki SEO alanlarını kullanır. Özel hesap sayfaları noindex kalır.')
                ->schema([
                    Repeater::make('pages')
                        ->label('Sayfa SEO kayıtları')
                        ->schema([
                            Select::make('route_name')
                                ->label('Sayfa')
                                ->options([
                                    'channels.index' => 'Kanallar listesi (/channels)',
                                    'members.index' => 'Üye listesi (/members)',
                                    'posts.latest' => 'Son yazılar (/last-posts)',
                                    'comments.latest' => 'Son yorumlar (/last-comments)',
                                    'tags.index' => 'Etiketler (/tags)',
                                    'tags.show' => 'Etiket detayları (/tags/{tag})',
                                    'contacts' => 'İletişim (/contacts)',
                                    'stats.index' => 'İstatistikler (/stats.html)',
                                ])
                                ->required()
                                ->searchable()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            TextInput::make('title')
                                ->label('SEO başlığı')
                                ->maxLength(255)
                                ->helperText('Boşsa sayfanın kendi başlığı kullanılır.'),
                            Textarea::make('description')
                                ->label('Meta açıklaması')
                                ->rows(2)
                                ->maxLength(500)
                                ->columnSpanFull(),
                            TextInput::make('canonical_url')
                                ->label('Canonical URL')
                                ->url()
                                ->maxLength(2048)
                                ->placeholder('Boşsa geçerli sayfanın kendi URL’si kullanılır.')
                                ->columnSpanFull(),
                            TextInput::make('og_image')
                                ->label('Sosyal paylaşım görseli URL')
                                ->url()
                                ->maxLength(2048)
                                ->columnSpanFull(),
                            Toggle::make('robots_index')
                                ->label('Google indekslemesine izin ver')
                                ->default(true),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Sayfa SEO kaydı ekle')
                        ->reorderable(false)
                        ->columnSpanFull(),
                ]),

            Section::make('Genel SEO varsayılanları')
                ->description('Yalnızca kendi SEO başlığı veya açıklaması bulunmayan herkese açık sayfalarda yedek değer olarak kullanılır. Her sayfada özgün başlık ve açıklama tercih edilir.')
                ->schema([
                    TextInput::make('seo_default_title')
                        ->label('Varsayılan SEO başlığı')
                        ->maxLength(255)
                        ->placeholder('OgraFi – Topluluk ve paylaşım platformu')
                        ->columnSpanFull(),
                    Textarea::make('seo_default_description')
                        ->label('Varsayılan meta açıklaması')
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                    TextInput::make('seo_og_image')
                        ->label('Varsayılan sosyal paylaşım görseli URL')
                        ->url()
                        ->maxLength(2048)
                        ->placeholder('https://ografi.com/storage/seo/varsayilan.jpg')
                        ->helperText('Önerilen boyut 1200 × 630 px. Sayfaya özel görsel yoksa kullanılır.')
                        ->columnSpanFull(),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SiteSetting::current() ?? new SiteSetting();
        $settings->id = 1;
        $settings->fill([
            'google_site_verification' => filled($data['google_site_verification'] ?? null)
                ? trim($data['google_site_verification'])
                : null,
            'home_seo_title' => filled($data['home_seo_title'] ?? null) ? trim($data['home_seo_title']) : null,
            'home_seo_description' => filled($data['home_seo_description'] ?? null) ? trim($data['home_seo_description']) : null,
            'home_seo_og_image' => filled($data['home_seo_og_image'] ?? null) ? trim($data['home_seo_og_image']) : null,
            'home_seo_index' => (bool) ($data['home_seo_index'] ?? false),
            'seo_default_title' => filled($data['seo_default_title'] ?? null) ? trim($data['seo_default_title']) : null,
            'seo_default_description' => filled($data['seo_default_description'] ?? null) ? trim($data['seo_default_description']) : null,
            'seo_og_image' => filled($data['seo_og_image'] ?? null) ? trim($data['seo_og_image']) : null,
        ]);
        $settings->save();

        $pages = collect($data['pages'] ?? [])
            ->filter(fn (array $page): bool => filled($page['route_name'] ?? null))
            ->unique('route_name')
            ->values();

        SeoPageSetting::query()->whereNotIn('route_name', $pages->pluck('route_name'))->delete();

        foreach ($pages as $page) {
            SeoPageSetting::query()->updateOrCreate(
                ['route_name' => $page['route_name']],
                [
                    'title' => filled($page['title'] ?? null) ? trim($page['title']) : null,
                    'description' => filled($page['description'] ?? null) ? trim($page['description']) : null,
                    'canonical_url' => filled($page['canonical_url'] ?? null) ? trim($page['canonical_url']) : null,
                    'og_image' => filled($page['og_image'] ?? null) ? trim($page['og_image']) : null,
                    'robots_index' => (bool) ($page['robots_index'] ?? false),
                ],
            );
        }

        Notification::make()
            ->title('SEO ayarları kaydedildi.')
            ->success()
            ->send();
    }
}

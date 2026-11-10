<?php

namespace App\Filament\Pages;

use App\Models\SantralPageSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SantralPage extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Site yönetimi';
    protected static ?string $navigationLabel = 'Santral sayfası';
    protected static ?string $title = 'OgraFi Santral sayfası';
    protected static ?int $navigationSort = 20;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected string $view = 'filament.pages.santral-page';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SantralPageSetting::current();

        $this->form->fill([
            'eyebrow' => $settings->eyebrow,
            'seo_title' => $settings->seo_title,
            'seo_description' => $settings->seo_description,
            'seo_tags' => $settings->seo_tags ?: [],
            'featured_image' => $settings->featured_image,
            'hero_title' => $settings->hero_title,
            'hero_description' => $settings->hero_description,
            'primary_button_text' => $settings->primary_button_text,
            'activation_button_text' => $settings->activation_button_text,
            'hero_note' => $settings->hero_note,
            'features_title' => $settings->features_title,
            'features' => $settings->features ?: [
                ['icon' => 'channels', 'title' => 'Bulut santral hizmeti', 'description' => 'Bulut santral altyapısını biz sağlıyor, hat ve dahili ayarlarınızı kurup yönetiyoruz.'],
                ['icon' => 'list', 'title' => 'IVR ve profesyonel karşılama', 'description' => 'İşletmenize özel sesli karşılama ve tuşlama menülerini hazırlıyoruz.'],
                ['icon' => 'megaphone', 'title' => '0850 ve şehir numaraları', 'description' => 'Numara talepleriniz için başvuru ve operatör bağlantısını sağlıyoruz.'],
                ['icon' => 'message', 'title' => 'WhatsApp Business entegrasyonu', 'description' => 'WhatsApp Business bağlantısını iş akışlarınıza göre yapılandırıyoruz.'],
                ['icon' => 'security', 'title' => 'SMS ve OTP entegrasyonu', 'description' => 'SMS ve OTP bağlantılarını kuruyor, test ediyor ve devreye alıyoruz.'],
                ['icon' => 'bolt', 'title' => 'CRM, API ve Webhook bağlantıları', 'description' => 'Santrali CRM ve özel yazılımlarınıza bağlıyoruz.'],
            ],
            'steps_title' => $settings->steps_title,
            'packages_title' => $settings->packages_title,
            'packages_description' => $settings->packages_description,
            'packages' => $settings->packages ?: [
                ['name' => 'Başlangıç', 'price' => 0, 'period' => 'aylık', 'description' => 'Temel telefon trafiği ve çağrı yönlendirme ihtiyaçları için.', 'features' => "Bulut santral\nTemel karşılama\nÇağrı yönlendirme\nTeknik destek", 'featured' => false],
                ['name' => 'İşletme', 'price' => 0, 'period' => 'aylık', 'description' => 'Günlük çağrı trafiği olan işletmeler için kapsamlı paket.', 'features' => "Tüm Başlangıç özellikleri\nIVR sesli yanıt menüsü\nBirden fazla dahili\nÇağrı raporları\nEntegrasyon desteği", 'featured' => true],
                ['name' => 'Profesyonel', 'price' => 0, 'period' => 'aylık', 'description' => 'Yoğun çağrı ve özel entegrasyon ihtiyacı olan ekipler için.', 'features' => "Tüm İşletme özellikleri\nGelişmiş çağrı akışları\nCRM / API entegrasyonu\nÖzel yapılandırma\nÖncelikli destek", 'featured' => false],
            ],
            'steps' => $settings->steps ?: [
                ['title' => 'İhtiyacınızı belirleyelim', 'description' => 'Hat, kullanıcı ve entegrasyon ihtiyaçlarınızı netleştirelim.'],
                ['title' => 'Sisteminizi hazırlayalım', 'description' => 'Numara, karşılama ve yönlendirme ayarlarını yapalım.'],
                ['title' => 'Devreye alıp destek verelim', 'description' => 'Test edip kullanıma açalım ve teknik destek sağlayalım.'],
            ],
            'bottom_title' => $settings->bottom_title,
            'bottom_description' => $settings->bottom_description,
            'slides' => $settings->slides ?: [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Üst alan ve butonlar')
                    ->description('Santral sayfasının ilk görünen bölümünü ve aktivasyon penceresini yönetin.')
                    ->schema([
                        TextInput::make('eyebrow')->label('Üst küçük başlık')->required()->maxLength(100),
                        TextInput::make('hero_title')->label('Ana başlık')->required()->maxLength(180)->columnSpanFull(),
                        Textarea::make('hero_description')->label('Açıklama')->rows(3)->columnSpanFull(),
                        TextInput::make('primary_button_text')->label('İletişim butonu')->required()->maxLength(100),
                        TextInput::make('activation_button_text')->label('Entegrasyon butonu')->required()->maxLength(100),
                        Textarea::make('hero_note')->label('Buton altı kısa not')->rows(2)->columnSpanFull(),
                    ])->columns(2),
                Section::make('SEO ve öne çıkan görsel')
                    ->description('Google arama sonucu, sosyal medya paylaşımı ve sayfanın öne çıkan görselini buradan yönetin.')
                    ->schema([
                        TextInput::make('seo_title')->label('SEO başlığı')->maxLength(180)->helperText('Önerilen uzunluk: 50–60 karakter. Boş bırakılırsa ana başlık kullanılır.')->columnSpanFull(),
                        Textarea::make('seo_description')->label('Meta açıklaması')->rows(3)->maxLength(320)->helperText('Önerilen uzunluk: 140–160 karakter. Sayfanın hizmetini ve faydasını net anlatın.')->columnSpanFull(),
                        TagsInput::make('seo_tags')->label('SEO etiketleri / anahtar kelimeler')->placeholder('Bulut santral, Asterisk, IVR...')->separator(',')->columnSpanFull(),
                        FileUpload::make('featured_image')->label('Öne çıkan görsel (Open Graph)')->image()->disk('public')->directory('santral/seo')->visibility('public')->imageEditor()->helperText('Google ve sosyal medya paylaşımlarında kullanılır. 1200 × 630 px görsel tercih edin.')->columnSpanFull(),
                    ])->columns(2),
                Section::make('Sayfa slaytları')
                    ->description('Görsel, başlık ve açıklama ekleyin. Slaytlar santral sayfasında otomatik kayan bir alanda gösterilir.')
                    ->schema([
                        Repeater::make('slides')
                            ->label('Slaytlar')
                            ->schema([
                                FileUpload::make('image')->label('Slayt görseli')->image()->disk('public')->directory('santral/slides')->visibility('public')->imageEditor()->required(),
                                TextInput::make('title')->label('Slayt başlığı')->maxLength(160)->required(),
                                Textarea::make('description')->label('Slayt açıklaması')->rows(2),
                                TextInput::make('link')->label('İsteğe bağlı bağlantı')->url()->maxLength(2048),
                            ])->columns(2)->defaultItems(0)->reorderable()->collapsible()->columnSpanFull(),
                    ]),
                Section::make('Hizmet alanı')
                    ->schema([
                        TextInput::make('features_title')->label('Bölüm başlığı')->required()->maxLength(160),
                        Repeater::make('features')
                            ->label('Hizmet kartları')
                            ->schema([
                                Select::make('icon')->label('İkon')->options([
                                    'channels' => 'Bulut santral',
                                    'list' => 'IVR / Karşılama',
                                    'megaphone' => 'Telefon numaraları',
                                    'message' => 'WhatsApp Business',
                                    'security' => 'SMS / OTP',
                                    'bolt' => 'CRM / API',
                                ])->required()->native(false),
                                TextInput::make('title')->label('Başlık')->required()->maxLength(120),
                                Textarea::make('description')->label('Açıklama')->rows(3)->required(),
                            ])->columns(2)->defaultItems(0)->reorderable()->collapsible()->columnSpanFull(),
                    ]),
                Section::make('Fiyatlandırma paketleri')
                    ->description('Üç santral paketinin adını, aylık ücretini ve özelliklerini buradan yönetin. Fiyatı 0 bırakırsanız sayfada Teklif alın gösterilir.')
                    ->schema([
                        TextInput::make('packages_title')->label('Paket alanı başlığı')->required()->maxLength(180),
                        Textarea::make('packages_description')->label('Paket alanı açıklaması')->rows(2)->columnSpanFull(),
                        Repeater::make('packages')->label('Santral paketleri')->minItems(3)->maxItems(3)->schema([
                            Select::make('icon')->label('Paket ikonu')->options([
                                'channels' => 'Santral',
                                'bolt' => 'Performans',
                                'security' => 'Güvenlik',
                                'list' => 'Özellikler',
                                'megaphone' => 'İletişim',
                            ])->default('channels')->required()->native(false),
                            TextInput::make('name')->label('Paket adı')->required()->maxLength(80),
                            TextInput::make('price')->label('Fiyat (TL)')->numeric()->minValue(0)->default(0)->required(),
                            TextInput::make('period')->label('Ücret dönemi')->default('aylık')->maxLength(30),
                            Textarea::make('description')->label('Kısa açıklama')->rows(2)->required(),
                            Textarea::make('features')->label('Özellikler (her satıra bir özellik)')->rows(5)->required()->columnSpanFull(),
                            \Filament\Forms\Components\Toggle::make('featured')->label('Öne çıkarılan paket'),
                        ])->columns(2)->defaultItems(3)->reorderable(false)->collapsible()->columnSpanFull(),
                    ]),
                Section::make('Nasıl çalışır?')
                    ->schema([
                        TextInput::make('steps_title')->label('Bölüm başlığı')->required()->maxLength(160),
                        Repeater::make('steps')
                            ->label('Adımlar')
                            ->schema([
                                TextInput::make('title')->label('Adım başlığı')->required()->maxLength(120),
                                Textarea::make('description')->label('Açıklama')->rows(2)->required(),
                            ])->columns(2)->defaultItems(0)->reorderable()->collapsible()->columnSpanFull(),
                    ]),
                Section::make('Alt çağrı alanı')
                    ->schema([
                        TextInput::make('bottom_title')->label('Başlık')->required()->maxLength(180),
                        Textarea::make('bottom_description')->label('Açıklama')->rows(3)->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SantralPageSetting::current();
        $settings->fill($data)->save();

        Notification::make()
            ->title('Santral sayfası güncellendi.')
            ->success()
            ->send();
    }
}

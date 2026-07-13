<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageBuilderResource\Pages;
use App\Models\PageBuilder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Schemas\Components\Utilities\Get;

class PageBuilderResource extends Resource
{
    protected static ?string $model = PageBuilder::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Site';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationLabel = 'Sayfa Olusturucu';
    protected static ?string $modelLabel = 'Sayfa';
    protected static ?string $pluralModelLabel = 'Sayfalar';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                Select::make('preset_key')
                    ->label('Hazır sayfa seç')
                    ->options(self::presetOptions())
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('key', $state))
                    ->dehydrated(false),
                TextInput::make('key')
                    ->label('Sayfa anahtarı')
                    ->helperText('Route adı (örn: blog.index) veya özel anahtar.')
                    ->required()
                    ->columnSpan(1),
                TextInput::make('title')
                    ->label('Başlık')
                    ->columnSpan(1),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->columnSpan(1),
            ]),

            Repeater::make('sections')
                ->label('Sayfa bölümleri')
                ->reorderable()
                ->collapsible()
                ->defaultItems(0)
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('title')
                            ->label('Bölüm başlığı (panel)')
                            ->helperText('Sadece yönetimde görünür.')
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Bölüm tipi')
                            ->options([
                                'hero' => 'Vitrin',
                                'text' => 'Metin',
                                'cards' => 'Kartlar',
                                'stats' => 'İstatistik',
                                'cta' => 'Cagri',
                                'image' => 'Görsel',
                                'divider' => 'Ayraç',
                                'modal' => 'Buton Popup',
                                'html' => 'Özel HTML',
                            ])
                            ->required()
                            ->reactive(),
                        TextInput::make('anchor')
                            ->label('Ankor ID')
                            ->helperText('Sayfada #ankor olarak kullanılabilir.'),
                        Toggle::make('full_width')
                            ->label('Tam genişlik')
                            ->default(false),
                        Select::make('radius')
                            ->label('Köşe')
                            ->options([
                                'none' => 'Kare',
                                'sm' => 'Küçük',
                                'md' => 'Orta',
                                'lg' => 'Büyük',
                                'xl' => 'Ekstra',
                            ])
                            ->default('lg'),
                        Select::make('shadow')
                            ->label('Gölge')
                            ->options([
                                'none' => 'Yok',
                                'sm' => 'Hafif',
                                'md' => 'Orta',
                                'lg' => 'Yoğun',
                            ])
                            ->default('none'),
                        Select::make('padding')
                            ->label('İç boşluk')
                            ->options([
                                'sm' => 'Sıkı',
                                'md' => 'Normal',
                                'lg' => 'Geniş',
                                'xl' => 'Daha geniş',
                            ])
                            ->default('lg'),
                        ColorPicker::make('bg_color')->label('Arka plan'),
                        ColorPicker::make('text_color')->label('Yazı rengi'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('heading')
                            ->label('Başlık')
                            ->visible(fn (Get $get) => in_array($get('type'), ['hero', 'text', 'cta'], true)),
                        Textarea::make('subheading')
                            ->label('Alt başlık')
                            ->rows(3)
                            ->visible(fn (Get $get) => $get('type') === 'hero'),
                        Textarea::make('body')
                            ->label('Metin')
                            ->rows(4)
                            ->visible(fn (Get $get) => in_array($get('type'), ['text', 'cta', 'modal'], true)),
                        TextInput::make('button_text')
                            ->label('Buton yazısı')
                            ->visible(fn (Get $get) => in_array($get('type'), ['hero', 'cta', 'modal'], true)),
                        TextInput::make('button_url')
                            ->label('Buton link')
                            ->visible(fn (Get $get) => in_array($get('type'), ['hero', 'cta'], true)),
                        FileUpload::make('image')
                            ->label('Görsel')
                            ->disk('public')
                            ->directory('page-builder')
                            ->image()
                            ->imageEditor()
                            ->visible(fn (Get $get) => in_array($get('type'), ['hero', 'image'], true)),
                        TextInput::make('image_alt')
                            ->label('Görsel alt')
                            ->visible(fn (Get $get) => in_array($get('type'), ['hero', 'image'], true)),
                        Textarea::make('caption')
                            ->label('Görsel açıklama')
                            ->rows(2)
                            ->visible(fn (Get $get) => $get('type') === 'image'),
                        TextInput::make('label')
                            ->label('Ayraç yazısı')
                            ->visible(fn (Get $get) => $get('type') === 'divider'),
                        TextInput::make('modal_title')
                            ->label('Popup Başlık')
                            ->visible(fn (Get $get) => $get('type') === 'modal'),
                        TextInput::make('confirm_text')
                            ->label('Onay Buton Yazısı')
                            ->visible(fn (Get $get) => $get('type') === 'modal'),
                        TextInput::make('cancel_text')
                            ->label('İptal Buton Yazısı')
                            ->visible(fn (Get $get) => $get('type') === 'modal'),
                        Select::make('variant')
                            ->label('Popup Stil')
                            ->options([
                                'info' => 'Bilgi',
                                'success' => 'Basarili',
                                'warning' => 'Uyari',
                                'danger' => 'Tehlike',
                            ])
                            ->default('info')
                            ->visible(fn (Get $get) => $get('type') === 'modal'),
                        Textarea::make('html')
                            ->label('Özel HTML')
                            ->rows(8)
                            ->visible(fn (Get $get) => $get('type') === 'html'),
                    ])->columns(2),

                    Repeater::make('items')
                        ->label('Ögeler')
                        ->visible(fn (Get $get) => in_array($get('type'), ['cards', 'stats'], true))
                        ->dehydrated(fn (Get $get) => in_array($get('type'), ['cards', 'stats'], true))
                        ->schema([
                            TextInput::make('title')->label('Başlık'),
                            Textarea::make('text')->label('Metin')->rows(2),
                            TextInput::make('icon')->label('İkon/Emoji'),
                            TextInput::make('value')->label('Değer (istatistik)'),
                            TextInput::make('label')->label('Etiket (istatistik)'),
                        ])->columns(2),

                    Select::make('columns')
                        ->label('Kart kolon')
                        ->options([
                            2 => '2',
                            3 => '3',
                            4 => '4',
                        ])
                        ->default(3)
                        ->visible(fn (Get $get) => $get('type') === 'cards')
                        ->dehydrated(fn (Get $get) => $get('type') === 'cards'),
                ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('Anahtar')->searchable(),
                TextColumn::make('title')->label('Başlık')->searchable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('updated_at')->label('Güncellendi')->since()->sortable(),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageBuilders::route('/'),
            'create' => Pages\CreatePageBuilder::route('/create'),
            'edit' => Pages\EditPageBuilder::route('/{record}/edit'),
        ];
    }

    private static function presetOptions(): array
    {
        $options = (array) config('page-builder.keys', []);

        if (!empty($options)) {
            return $options;
        }

        return [
            'home' => 'Anasayfa',
            'discover' => 'Keşfet',
            'blog.index' => 'Blog Anasayfa',
            'blog.posts' => 'Blog Gonderileri',
            'blog.popular' => 'Popüler',
            'blog.categories' => 'Kategoriler',
            'blog.tags' => 'Etiketler',
            'blog.post' => 'Gonderi Detayi',
            'pages.show' => 'Sayfa (sayfa/{slug})',
            'users.show' => 'Profil (u/{username})',
            'profile.show' => 'Profil Ayar',
            'messages.index' => 'Mesajlar',
            'notifications.index' => 'Bildirimler',
            'search' => 'Arama',
            'dashboard' => 'Panel',
            'settings.index' => 'Ayarlar',
            'install.requirements' => 'Kurulum: Gereksinimler',
            'install.database' => 'Kurulum: Veritabanı',
            'install.admin' => 'Kurulum: Admin',
            'install.finished' => 'Kurulum: Tamamlandı',
        ];
    }
}












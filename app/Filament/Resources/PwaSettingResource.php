<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PwaSettingResource\Pages;
use App\Models\PwaSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PwaSettingResource extends Resource
{
    protected static ?string $model = PwaSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationLabel = 'PWA / TWA';

    protected static ?string $modelLabel = 'PWA Ayari';

    protected static ?string $pluralModelLabel = 'PWA Ayarlari';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Placeholder::make('general_heading')->label('Genel'),
            Grid::make(3)->schema([
                Toggle::make('is_enabled')->label('PWA aktif'),
                Toggle::make('install_banner_enabled')->label('Indir banneri aktif'),
                Toggle::make('twa_enabled')->label('TWA aktif'),
            ]),

            Placeholder::make('manifest_heading')->label('Manifest temel bilgiler'),
            Grid::make(2)->schema([
                TextInput::make('app_name')
                    ->label('Uygulama adi')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('short_name')
                    ->label('Kisa ad')
                    ->maxLength(100)
                    ->required(),
                Textarea::make('description')
                    ->label('Aciklama')
                    ->rows(3)
                    ->maxLength(500),
            ]),

            Placeholder::make('display_heading')->label('Gorunum'),
            Grid::make(3)->schema([
                TextInput::make('start_url')
                    ->label('Start URL')
                    ->default('/')
                    ->maxLength(255),
                TextInput::make('scope')
                    ->label('Scope')
                    ->default('/')
                    ->maxLength(255),
                Select::make('display')
                    ->label('Display')
                    ->options([
                        'standalone' => 'standalone',
                        'fullscreen' => 'fullscreen',
                        'minimal-ui' => 'minimal-ui',
                        'browser' => 'browser',
                    ])
                    ->default('standalone'),
                Select::make('orientation')
                    ->label('Orientation')
                    ->options([
                        'portrait' => 'portrait',
                        'landscape' => 'landscape',
                        'any' => 'any',
                    ])
                    ->default('portrait'),
                TextInput::make('lang')
                    ->label('Lang')
                    ->default('tr')
                    ->maxLength(10),
                Select::make('dir')
                    ->label('Direction')
                    ->options([
                        'ltr' => 'ltr',
                        'rtl' => 'rtl',
                        'auto' => 'auto',
                    ])
                    ->default('ltr'),
            ]),

            Placeholder::make('colors_heading')->label('Renkler'),
            Grid::make(2)->schema([
                TextInput::make('theme_color')
                    ->label('Theme color')
                    ->maxLength(20)
                    ->default('#111827'),
                TextInput::make('background_color')
                    ->label('Background color')
                    ->maxLength(20)
                    ->default('#ffffff'),
            ]),

            Placeholder::make('icons_heading')->label('Ikonlar'),
            Grid::make(2)->schema([
                FileUpload::make('icon_192')
                    ->label('Icon 192x192')
                    ->disk('public')
                    ->directory('pwa/icons')
                    ->visibility('public')
                    ->image()
                    ->imagePreviewHeight('120')
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/svg+xml']),
                FileUpload::make('icon_512')
                    ->label('Icon 512x512')
                    ->disk('public')
                    ->directory('pwa/icons')
                    ->visibility('public')
                    ->image()
                    ->imagePreviewHeight('120')
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/svg+xml']),
                FileUpload::make('icon_maskable_192')
                    ->label('Maskable 192x192')
                    ->disk('public')
                    ->directory('pwa/icons')
                    ->visibility('public')
                    ->image()
                    ->imagePreviewHeight('120')
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/svg+xml']),
                FileUpload::make('icon_maskable_512')
                    ->label('Maskable 512x512')
                    ->disk('public')
                    ->directory('pwa/icons')
                    ->visibility('public')
                    ->image()
                    ->imagePreviewHeight('120')
                    ->acceptedFileTypes(['image/png', 'image/webp', 'image/svg+xml']),
            ]),

            Placeholder::make('screenshots_heading')->label('Ekran goruntuleri'),
            Repeater::make('screenshots')
                ->label('Screenshots')
                ->schema([
                    FileUpload::make('image')
                        ->label('Gorsel')
                        ->disk('public')
                        ->directory('pwa/screenshots')
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('120')
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp']),
                    TextInput::make('label')
                        ->label('Baslik')
                        ->maxLength(100),
                    Select::make('form_factor')
                        ->label('Form factor')
                        ->options([
                            'narrow' => 'narrow',
                            'wide' => 'wide',
                        ])
                        ->default('narrow'),
                ])
                ->columns(3)
                ->default([]),

            Placeholder::make('categories_heading')->label('Kategoriler'),
            TagsInput::make('categories')
                ->label('Categories')
                ->separator(',')
                ->helperText('Virgulle ayirabilirsiniz.'),

            Placeholder::make('shortcuts_heading')->label('Kisayollar'),
            Repeater::make('shortcuts')
                ->label('Shortcuts')
                ->schema([
                    TextInput::make('name')->label('Ad')->required()->maxLength(100),
                    TextInput::make('short_name')->label('Kisa ad')->maxLength(100),
                    TextInput::make('url')->label('URL')->required()->maxLength(255),
                    TextInput::make('description')->label('Aciklama')->maxLength(255),
                    FileUpload::make('icon')
                        ->label('Icon')
                        ->disk('public')
                        ->directory('pwa/shortcuts')
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('80')
                        ->acceptedFileTypes(['image/png', 'image/webp', 'image/svg+xml']),
                ])
                ->columns(2)
                ->default([]),

            Placeholder::make('banner_heading')->label('Indir banneri'),
            Grid::make(2)->schema([
                TextInput::make('install_banner_title')
                    ->label('Baslik')
                    ->maxLength(120),
                Textarea::make('install_banner_description')
                    ->label('Aciklama')
                    ->rows(2)
                    ->maxLength(500),
                TextInput::make('install_banner_button_label')
                    ->label('Buton yazisi')
                    ->maxLength(40),
            ]),

            Placeholder::make('login_heading')->label('Giris ekrani'),
            FileUpload::make('login_hero_image')
                ->label('Giris ust gorsel')
                ->disk('public')
                ->directory('pwa/login')
                ->visibility('public')
                ->image()
                ->imagePreviewHeight('160')
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp']),

            Placeholder::make('twa_heading')->label('TWA ayarlari'),
            Grid::make(2)->schema([
                TextInput::make('twa_package_id')
                    ->label('TWA package id')
                    ->maxLength(255),
                TextInput::make('twa_fallback_url')
                    ->label('TWA fallback URL')
                    ->maxLength(255),
                TagsInput::make('twa_sha256_cert_fingerprints')
                    ->label('SHA256 fingerprints')
                    ->separator(',')
                    ->helperText('Asset Links fingerprints icin virgulle ayirabilirsiniz.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_enabled')->label('PWA')->boolean(),
                IconColumn::make('install_banner_enabled')->label('Banner')->boolean(),
                IconColumn::make('twa_enabled')->label('TWA')->boolean(),
                TextColumn::make('app_name')->label('Uygulama adi'),
                TextColumn::make('short_name')->label('Kisa ad'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPwaSettings::route('/'),
            'edit' => Pages\EditPwaSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }
}












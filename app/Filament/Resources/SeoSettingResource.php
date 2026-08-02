<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoSettingResource\Pages;
use App\Models\SeoSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SeoSettingResource extends Resource
{
    protected static ?string $model = SeoSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Sayfalar ve SEO';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'SEO';

    protected static ?string $modelLabel = 'SEO Ayari';

    protected static ?string $pluralModelLabel = 'SEO Ayarlari';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Arama Motoru Optimizasyonu')
                ->description('Arama motoru optimizasyonu, bir web sitesine veya web sayfasina arama motorlarindan gelen trafigin kalitesini ve miktarini artirma surecidir. Bir sayfanin kendi SEO ayarlari yoksa buradaki degerler yedek (fallback) olarak kullanilir.')
                ->schema([
                    TextInput::make('site_meta_title')
                        ->label('Site meta basligi')
                        ->required()
                        ->maxLength(65)
                        ->helperText('Onerilen uzunluk: 50-60 karakter.'),
                    Textarea::make('site_meta_description')
                        ->label('Site meta aciklamasi')
                        ->rows(3)
                        ->maxLength(160)
                        ->helperText('Onerilen uzunluk: 140-160 karakter.'),
                    TextInput::make('site_meta_keywords')
                        ->label('Site meta anahtar kelimeleri')
                        ->maxLength(255)
                        ->helperText('Virgulle ayirin. Google siralamada kullanmiyor ama bazi arama motorlari hala okuyor.'),
                ]),

            Section::make('Open Graph Meta Etiketleri')
                ->description('Open Graph protokolu, sosyal aglarda ve anlik mesajlasma uygulamalarinda bir siteye verilen baglantilarin turunu belirleyen bir isaretleme dilidir. Bu mikro isaretleme sayesinde, gonderiye dogru gorsel ve kisa bir aciklama iceren belirtilen metin eklenir; bu da gonderiyi daha cekici hale getirir, baglam odakli reklamcilikta reklam gibi gorunur ve daha fazla dikkat ceker.')
                ->schema([
                    TextInput::make('og_site_name')
                        ->label('OG site adi')
                        ->maxLength(255),
                    TextInput::make('og_default_title')
                        ->label('Orijinal baslik')
                        ->maxLength(255)
                        ->helperText('Kendi OG basligi olmayan sayfalarda kullanilir.'),
                    Textarea::make('og_default_description')
                        ->label('OG aciklamasi')
                        ->rows(3)
                        ->maxLength(300),
                    TextInput::make('og_url')
                        ->label("OG URL'si")
                        ->url()
                        ->maxLength(255),
                    Select::make('og_type')
                        ->label('OG tipi')
                        ->options([
                            'website' => 'website',
                            'article' => 'article',
                            'profile' => 'profile',
                        ])
                        ->default('website')
                        ->required(),
                    FileUpload::make('og_default_image')
                        ->label('Orijinal gorsel')
                        ->disk('public')
                        ->directory('seo')
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('160')
                        ->helperText('Onerilen boyut: 1200x630px. Kendi gorseli olmayan sayfalarda kullanilir.')
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp']),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site_meta_title')->label('Site meta basligi'),
                TextColumn::make('og_site_name')->label('OG site adi'),
                TextColumn::make('updated_at')->label('Guncellendi')->since(),
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
            'index' => Pages\ListSeoSettings::route('/'),
            'edit' => Pages\EditSeoSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }
}

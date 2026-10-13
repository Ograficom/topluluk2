<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Martin6363\FilamentSmartSeo\Forms\Components\SeoSection;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Sayfalar ve SEO';

    protected static ?string $navigationLabel = 'Sayfalar';

    protected static ?string $pluralModelLabel = 'Sayfalar';

    protected static ?string $modelLabel = 'Sayfa';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Baslik')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $state, callable $set, callable $get) {
                            if ($get('slug')) {
                                return;
                            }

                            $set('slug', Str::slug($state));
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Page::class, 'slug', ignoreRecord: true),
                ]),
                Section::make('Tasarım ayarları')
                    ->description('Bu ayarlar yalnızca bu statik sayfanın görünümünü etkiler.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('layout_width')
                            ->label('İçerik genişliği')
                            ->options(['contained' => 'Standart', 'wide' => 'Geniş', 'full' => 'Tam genişlik'])
                            ->default('contained')
                            ->required(),
                        Forms\Components\Select::make('content_background')
                            ->label('İçerik arka planı')
                            ->placeholder('Tema varsayılanı')
                            ->options(['#ffffff' => 'Beyaz', '#f8fafc' => 'Açık gri', '#f1f5f9' => 'Gri', '#eff6ff' => 'Açık mavi'])
                            ->nullable(),
                        Forms\Components\Select::make('content_text_color')
                            ->label('Metin rengi')
                            ->placeholder('Tema varsayılanı')
                            ->options(['#111827' => 'Koyu', '#374151' => 'Koyu gri', '#1d4ed8' => 'Mavi'])
                            ->nullable(),
                        Forms\Components\Select::make('content_radius')
                            ->label('Köşe stili')
                            ->options(['none' => 'Keskin', 'soft' => 'Hafif yuvarlak', 'rounded' => 'Yuvarlak'])
                            ->default('rounded')
                            ->required(),
                        Forms\Components\TextInput::make('content_padding')
                            ->label('İç boşluk (px)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(64)
                            ->step(4)
                            ->default(24)
                            ->required(),
                        Forms\Components\Textarea::make('custom_css')
                            ->label('Özel CSS')
                            ->rows(8)
                            ->maxLength(12000)
                            ->helperText('Yalnızca bu sayfanın içeriğini hedefleyen CSS yazın. <style> etiketi eklemeyin.')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
                Forms\Components\RichEditor::make('content')
                    ->label('Icerik')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'strike',
                        'underline',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'link',
                        'codeBlock',
                    ])
                    ->fileAttachmentsDisk(config('filesystems.default', 'public'))
                    ->fileAttachmentsVisibility('public'),
                Section::make('SEO')
                    ->description('Arama motorlarinda ve paylasimlarda gorunecek bilgiler. Bos birakilirsa sayfa basligi kullanilir.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta baslik')
                            ->maxLength(65)
                            ->helperText('Bos birakilirsa sayfa basligi kullanilir. Onerilen: 50-60 karakter.'),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta aciklama')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Arama sonuclarinda basligin altinda gorunur. 140-160 karakter onerilir.'),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Anahtar kelimeler')
                            ->maxLength(255)
                            ->helperText('Virgulle ayirin (opsiyonel).'),
                        Grid::make(2)->schema([
                            Forms\Components\FileUpload::make('og_image')
                                ->label('Paylasim gorseli (opsiyonel)')
                                ->disk('public')
                                ->directory('seo')
                                ->visibility('public')
                                ->image()
                                ->imagePreviewHeight('120')
                                ->helperText('Sosyal medyada paylasilirken gorunecek gorsel. Onerilen: 1200x630px.')
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp']),
                            Forms\Components\Toggle::make('noindex')
                                ->label('Arama motorlarindan gizle (noindex)')
                                ->helperText('Acilirsa bu sayfa Google gibi arama motorlarinin dizinine eklenmez.')
                                ->inline(false),
                        ]),
                    ]),
                SeoSection::make('Akilli SEO (AI)')
                    ->description('Doldurulursa yukaridaki Meta baslik/aciklamanin yerine kullanilir. AI ile otomatik doldurmak icin sag ustteki isik simgesine tiklayin (Gemini API anahtari gerektirir).')
                    ->sourceTitleField('title')
                    ->sourceDescriptionField('content')
                    ->collapsible()
                    ->collapsed(),
                Grid::make(2)->schema([
                    Forms\Components\Toggle::make('is_published')
                        ->label('Yayinda')
                        ->inline(false),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Yayin tarihi')
                        ->seconds(false)
                        ->native(false)
                        ->helperText('Bos birakilirsa hemen yayinlanir.')
                        ->default(now()),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Baslik')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Yayinda')
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Yayin')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Guncellendi')
                    ->since(),
            ])
            ->filters([])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('design')
                    ->label('Tasarım')
                    ->icon('heroicon-o-swatch')
                    ->url(fn (Page $record): string => static::getUrl('design', ['record' => $record])),
                Actions\Action::make('view_public')
                    ->label('Goruntule')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record) => route('pages.show', $record->slug))
                    ->visible(fn (Page $record) => $record->is_published),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
            'design' => Pages\DesignPage::route('/{record}/design'),
        ];
    }
}













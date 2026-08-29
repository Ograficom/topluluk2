<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\PostResource\RelationManagers\ReactionsRelationManager;
use App\Models\Post;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Str;
use Martin6363\FilamentSmartSeo\Forms\Components\SeoSection;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Blog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                TextInput::make('title')
                    ->label('Baslik')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($set, ?string $state): void {
                        if (filled($state)) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'posts', column: 'slug', ignoreRecord: true),
            ]),
            Select::make('author_id')
                ->label('Yazar')
                ->relationship('author', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => auth()->id())
                ->nullable(),
            Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            Select::make('tags')
                ->label('Etiketler')
                ->multiple()
                ->relationship('tags', 'name')
                ->preload()
                ->searchable()
                ->columnSpanFull(),
            Textarea::make('excerpt')
                ->label('Ozet')
                ->rows(3)
                ->columnSpanFull(),
            FileUpload::make('featured_image')
                ->label('One cikan gorsel')
                ->image()
                ->directory('featured-images')
                ->disk('public')
                ->visibility('public')
                ->imagePreviewHeight('220')
                ->panelLayout('integrated')
                ->maxSize(5120)
                ->fetchFileInformation(false)
                ->columnSpanFull(),
            Grid::make(2)->schema([
                TextInput::make('image_license_url')
                    ->label('Gorsel lisans URL')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('image_acquire_url')
                    ->label('Lisans alma sayfasi')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('image_credit_text')
                    ->label('Gorsel kredi metni')
                    ->maxLength(255),
                TextInput::make('image_creator_name')
                    ->label('Gorsel olusturan')
                    ->maxLength(255),
                TextInput::make('image_copyright_notice')
                    ->label('Telif bildirimi')
                    ->maxLength(255),
            ])->columnSpanFull(),
            Hidden::make('content')
                ->required()
                ->extraAttributes(['data-editor-content' => '1']),
            Hidden::make('content_json')
                ->extraAttributes(['data-editor-json' => '1']),
            ViewField::make('editorjs_post')
                ->label('Icerik')
                ->view('filament.forms.editorjs-post')
                ->columnSpanFull(),
            Section::make('SEO')
                ->description('Arama motorlarinda ve paylasimlarda gorunecek bilgiler. Bos birakilirsa basliktan/icerikten otomatik uretilir.')
                ->collapsible()
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('meta_title')
                            ->label('Meta baslik')
                            ->maxLength(65)
                            ->helperText('Onerilen uzunluk: 50-60 karakter.')
                            ->columnSpan(1),
                        Textarea::make('meta_description')
                            ->label('Meta aciklama')
                            ->rows(2)
                            ->maxLength(160)
                            ->helperText('Onerilen uzunluk: 140-160 karakter.')
                            ->columnSpan(2),
                    ]),
                    Textarea::make('meta_keywords')
                        ->label('Meta anahtar kelimeler')
                        ->rows(2)
                        ->maxLength(255)
                        ->helperText('Virgulle ayirin (opsiyonel).')
                        ->columnSpanFull(),
                    Grid::make(2)->schema([
                        FileUpload::make('og_image')
                            ->label('Paylasim gorseli (opsiyonel)')
                            ->disk('public')
                            ->directory('seo')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('120')
                            ->helperText('Bos birakilirsa gonderi icindeki ilk gorsel kullanilir. Onerilen: 1200x630px.')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp']),
                        Toggle::make('noindex')
                            ->label('Arama motorlarindan gizle (noindex)')
                            ->helperText('Acilirsa bu gonderi Google gibi arama motorlarinin dizinine eklenmez.')
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
                Toggle::make('is_published')
                    ->label('Yayinla')
                    ->inline(false),
                DateTimePicker::make('published_at')
                    ->label('Yayin tarihi')
                    ->seconds(false),
            ]),
            Textarea::make('edited_reason')
                ->label('Duzenleme nedeni')
                ->rows(3)
                ->helperText('Kalem ikonundaki duzenleme kutusunda gorunur.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Baslik')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('reactions_count')
                    ->label('Tepkiler')
                    ->counts('reactions')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('author.name')
                    ->label('Yazar')
                    ->toggleable(),
                TagsColumn::make('tags.name')
                    ->label('Etiketler')
                    ->limit(3),
                TextColumn::make('publication_status')
                    ->label('Durum')
                    ->state(fn (Post $record): string => $record->isPublishedNow()
                        ? 'Yayında'
                        : ($record->is_published ? 'Zamanlandı' : 'Taslak'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Yayında' => 'success',
                        'Zamanlandı' => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('published_at')
                    ->label('Yayin tarihi')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Olusturulma')
                    ->dateTime()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('is_published')
                    ->label('Yayin durumu')
                    ->trueLabel('Yayinda')
                    ->falseLabel('Taslak'),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('toggle_publish')
                        ->label(fn (Post $record) => $record->is_published ? 'Yayından Kaldır' : 'Yayınla')
                        ->icon(fn (Post $record) => $record->is_published ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn (Post $record) => $record->is_published ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->action(function (Post $record): void {
                            $record->is_published = ! $record->is_published;
                            if ($record->is_published && ! $record->published_at) {
                                $record->published_at = now();
                            }
                            $record->save();
                        }),
                    Actions\ViewAction::make()
                        ->label('Görüntüle')
                        ->icon('heroicon-o-eye'),
                    Actions\EditAction::make()
                        ->label('Düzenle')
                        ->icon('heroicon-o-pencil-square'),
                    Actions\DeleteAction::make()
                        ->label('Sil')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ]),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            ReactionsRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'excerpt'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getRecordSubNavigation(\Filament\Resources\Pages\Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewPost::class,
            Pages\EditPost::class,
        ]);
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Blog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-folder';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                TextInput::make('name')
                    ->label('Kategori adi')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'categories', column: 'name', ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (callable $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'categories', column: 'slug', ignoreRecord: true),
            ]),
            Textarea::make('description')
                ->label('Aciklama')
                ->columnSpanFull(),
            Grid::make(2)->schema([
                FileUpload::make('profile_image')
                    ->label('Profil Resmi')
                    ->image()
                    ->directory('categories/profile')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->maxSize(10240)
                    ->helperText('Kategori avatar: kare veya yuvarlak ikon yukleyin.'),
                FileUpload::make('cover_image')
                    ->label('Kapak Resmi')
                    ->image()
                    ->directory('categories/cover')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->maxSize(15360)
                    ->helperText('Listeleme kartlarinda arka plan olarak kullanilir.'),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_image')
                    ->label('Gorsel')
                    ->circular()
                    ->defaultImageUrl(fn (Category $record) => $record->cover_image_url),
                TextColumn::make('name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Aciklama')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('posts_count')
                    ->label('Gonderi Sayisi')
                    ->counts('posts'),
                TextColumn::make('created_at')
                    ->label('Olusturulma')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug'];
    }
}












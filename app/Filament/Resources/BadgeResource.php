<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Models\Badge;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';

    protected static string | \UnitEnum | null $navigationGroup = 'Kullanicilar';

    protected static ?string $navigationLabel = 'Rozetler';

    protected static ?string $pluralModelLabel = 'Rozetler';

    protected static ?string $modelLabel = 'Rozet';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Rozet adi')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(120),
                Forms\Components\TextInput::make('min_points')
                    ->label('Minimum puan')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Siralama')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\ColorPicker::make('color')
                    ->label('Renk')
                    ->default('#9ca3af'),
                Forms\Components\TextInput::make('icon')
                    ->label('Heroicon adi')
                    ->maxLength(120)
                    ->placeholder('heroicon-m-check-badge')
                    ->helperText('Heroicon kullanmak isterseniz adini girin. Ornek: heroicon-m-check-badge'),
                Forms\Components\FileUpload::make('icon_svg_path')
                    ->label('SVG ikon')
                    ->directory('badge-icons')
                    ->disk('public')
                    ->acceptedFileTypes(['image/svg+xml'])
                    ->helperText('SVG yuklerseniz profil tarafinda heroicon yerine bu ikon gosterilir.'),
                Forms\Components\Select::make('eligible_profile_type')
                    ->label('Profil turu')
                    ->options([
                        'person' => 'Kisi',
                        'organization' => 'Kurulus',
                    ])
                    ->placeholder('Tum profiller')
                    ->helperText('Secerseniz bu rozet sadece o profil turundeki hesaplara verilir.'),
                Forms\Components\Toggle::make('requires_verified')
                    ->label('Onayli hesap gerekli')
                    ->default(false)
                    ->helperText('Aciksa rozet sadece onayli hesaplara verilir.'),
            ]),
            Forms\Components\Textarea::make('description')
                ->label('Aciklama')
                ->rows(2)
                ->maxLength(255),
            Forms\Components\Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Rozet')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('icon')->label('Heroicon')->toggleable(),
                Tables\Columns\TextColumn::make('icon_svg_path')->label('SVG')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('eligible_profile_type')
                    ->label('Profil Turu')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'person' => 'Kisi',
                        'organization' => 'Kurulus',
                        default => 'Tum',
                    })
                    ->toggleable(),
                Tables\Columns\IconColumn::make('requires_verified')->label('Onayli')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('min_points')->label('Min Puan')->numeric()->sortable(),
                Tables\Columns\ColorColumn::make('color')->label('Renk'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Sira')->numeric()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}

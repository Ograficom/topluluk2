<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Kullanicilar';

    protected static ?string $navigationLabel = 'Kullanicilar';

    protected static ?string $pluralModelLabel = 'Kullanicilar';

    protected static ?string $modelLabel = 'Kullanici';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                Forms\Components\FileUpload::make('profile_photo_path')
                    ->label('Profil foto')
                    ->image()
                    ->imageEditor()
                    ->directory('profile-photos')
                    ->disk('public')
                    ->previewable(true),
                Forms\Components\FileUpload::make('cover_photo_path')
                    ->label('Kapak foto')
                    ->image()
                    ->imageEditor()
                    ->directory('cover-photos')
                    ->disk('public')
                    ->previewable(true),
            ]),
            Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Isim')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->options(User::roleOptions())
                    ->default(User::ROLE_WRITER)
                    ->required()
                    ->live()
                    ->helperText(fn (Get $get) => User::roleDescriptions()[User::normalizeRoleValue($get('role'))] ?? null),
                Forms\Components\Select::make('profile_type')
                    ->label('Profil turu')
                    ->options([
                        'person' => 'Kisi',
                        'organization' => 'Kurulus',
                    ])
                    ->default('person')
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->label('Parola')
                    ->password()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                    ->required(fn (string $context) => $context === 'create')
                    ->maxLength(255),
            ]),
            Forms\Components\Placeholder::make('role_rules')
                ->label('Rol kurali')
                ->content(function (Get $get): string {
                    $labels = User::roleBaseRestrictionLabels($get('role'));

                    return empty($labels)
                        ? 'Bu rolde sabit bir kisit yok.'
                        : 'Bu rolde sabit kisitlar: ' . implode(', ', $labels) . '.';
                }),
            Forms\Components\Placeholder::make('restriction_info')
                ->label('Ek kisitlamalar')
                ->content('Asagidaki togglelar role ek olarak ekstra yasak uygular. Banned rolu secilirse tum etkileşimler otomatik kapanir.'),
            Grid::make(2)
                ->schema([
                    Forms\Components\Toggle::make('block_messages')->label('Mesaj engeli'),
                    Forms\Components\Toggle::make('block_posts')->label('Gonderi engeli'),
                    Forms\Components\Toggle::make('block_categories')->label('Kategori engeli'),
                    Forms\Components\Toggle::make('block_tags')->label('Etiket engeli'),
                    Forms\Components\Toggle::make('block_comments')->label('Yorum engeli'),
                    Forms\Components\Toggle::make('block_reactions')->label('Reaksiyon engeli'),
                ])
                ->visible(fn (Get $get) => User::normalizeRoleValue($get('role')) !== User::ROLE_BANNED),
            Forms\Components\Placeholder::make('banned_notice')
                ->label('Banned modu')
                ->content('Banned rolunde admin paneli, mesaj, gonderi, kategori, etiket, yorum ve reaksiyon yetkileri otomatik olarak kapanir.')
                ->visible(fn (Get $get) => User::normalizeRoleValue($get('role')) === User::ROLE_BANNED),
            Forms\Components\Textarea::make('bio')
                ->label('Bio')
                ->rows(3),
            Grid::make(2)->schema([
                Forms\Components\TextInput::make('social_x')
                    ->label('X')
                    ->maxLength(255),
                Forms\Components\TextInput::make('social_instagram')
                    ->label('Instagram')
                    ->maxLength(255),
                Forms\Components\TextInput::make('social_whatsapp')
                    ->label('WhatsApp')
                    ->maxLength(255),
                Forms\Components\TextInput::make('social_tiktok')
                    ->label('TikTok')
                    ->maxLength(255),
                Forms\Components\TextInput::make('social_facebook')
                    ->label('Facebook')
                    ->maxLength(255),
                Forms\Components\TextInput::make('website_url')
                    ->label('Website')
                    ->url()
                    ->maxLength(255),
            ]),
            Grid::make(2)->schema([
                Forms\Components\DateTimePicker::make('joined_at')
                    ->label('Katilma tarihi'),
                Forms\Components\Toggle::make('is_verified')
                    ->label('Onayli hesap'),
                Forms\Components\TextInput::make('badge_points')
                    ->label('Rozet puani')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('Kullanici bu puana gore birden fazla rozet kazanabilir.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Isim')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Rol')
                    ->formatStateUsing(fn (?string $state) => User::roleOptions()[User::normalizeRoleValue($state)] ?? 'Yazar')
                    ->color(fn (?string $state) => match (User::normalizeRoleValue($state)) {
                        User::ROLE_ADMIN => 'success',
                        User::ROLE_EDITOR => 'warning',
                        User::ROLE_WRITER => 'gray',
                        User::ROLE_BANNED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('restriction_summary')
                    ->label('Kisitlar')
                    ->state(fn (User $record) => $record->blockedAbilityLabels()
                        ? implode(', ', $record->blockedAbilityLabels())
                        : 'Yok')
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Onayli')
                    ->boolean(),
                Tables\Columns\TextColumn::make('badge_points')
                    ->label('Rozet Puani')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Katilma')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Olusturuldu')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(User::roleOptions()),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->disabled(fn (User $record) => $record->isLastAdmin())
                    ->tooltip(fn (User $record) => $record->isLastAdmin() ? 'Son admin kullanici silinemez.' : null),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false) && !$record->isLastAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function normalizeFormData(array $data): array
    {
        $data['role'] = User::normalizeRoleValue($data['role'] ?? null);

        foreach (User::manualRestrictionFields() as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        if ($data['role'] === User::ROLE_BANNED) {
            foreach (User::manualRestrictionFields() as $field) {
                $data[$field] = true;
            }
        }

        return $data;
    }

    public static function ensureAdminIntegrity(?User $record, array $data): void
    {
        $targetRole = User::normalizeRoleValue($data['role'] ?? $record?->role);

        if (
            $record
            && $record->isAdmin()
            && $targetRole !== User::ROLE_ADMIN
            && $record->isLastAdmin()
        ) {
            throw ValidationException::withMessages([
                'role' => 'Sistemde en az bir admin kalmalidir.',
            ]);
        }
    }
}

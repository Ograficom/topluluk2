<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReactionTypeResource\Pages;
use App\Models\ReactionType;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReactionTypeResource extends Resource
{
    protected static ?string $model = ReactionType::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Blog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-face-smile';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('label')
                ->label('Ad')
                ->required()
                ->maxLength(100),
            TextInput::make('short_code')
                ->label('Kisa Kod')
                ->required()
                ->unique(table: 'reaction_types', column: 'short_code', ignoreRecord: true)
                ->maxLength(50)
                ->helperText('Orn: like, clap, wow. Frontend bu kodla istek gonderir.'),
            TextInput::make('emoji')
                ->label('Emoji')
                ->maxLength(16)
                ->helperText('Tek bir emoji veya Unicode karakter.'),
            FileUpload::make('gif_url')
                ->label('GIF/Resim')
                ->directory('reaction-types')
                ->disk('public')
                ->image()
                ->visibility('public')
                ->preserveFilenames()
                ->acceptedFileTypes(['image/gif', 'image/png', 'image/jpeg', 'image/webp'])
                ->maxSize(10240)
                ->helperText('GIF veya gorsel yukleyin; public diskte saklanir.'),
            Select::make('moderation_status')
                ->label('Moderasyon')
                ->options(ReactionType::moderationStatusOptions())
                ->default(ReactionType::STATUS_APPROVED)
                ->required()
                ->helperText('Uye onerileri Beklemede gelir. Onaylanmayan tepkiler aktif olamaz.'),
            Textarea::make('moderation_note')
                ->label('Moderasyon Notu')
                ->rows(3)
                ->maxLength(1000)
                ->helperText('Reddetme nedeni veya yonetici notu. Uye kendi dashboard sayfasinda gorebilir.'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true)
                ->helperText('Sadece Onaylandi durumundaki tepki aktif olabilir.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Ad')->sortable()->searchable(),
                TextColumn::make('short_code')->label('Kod')->sortable()->searchable(),
                TextColumn::make('emoji')->label('Emoji'),
                TextColumn::make('gif_url')->label('GIF')->limit(20),
                TextColumn::make('submittedBy.name')
                    ->label('Gonderen')
                    ->placeholder('Yonetici')
                    ->searchable(),
                TextColumn::make('moderation_status')
                    ->label('Moderasyon')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ReactionType::moderationStatusOptions()[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        ReactionType::STATUS_APPROVED => 'success',
                        ReactionType::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('reactions_count')->label('Toplam Tepki')->counts('reactions'),
            ])
            ->filters([
                SelectFilter::make('moderation_status')
                    ->label('Moderasyon')
                    ->options(ReactionType::moderationStatusOptions()),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Onayla')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ReactionType $record): bool => $record->moderation_status === ReactionType::STATUS_PENDING)
                    ->action(function (ReactionType $record): void {
                        $record->update([
                            'moderation_status' => ReactionType::STATUS_APPROVED,
                            'moderation_note' => null,
                            'is_active' => true,
                        ]);
                    }),
                Actions\Action::make('reject')
                    ->label('Reddet')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ReactionType $record): bool => $record->moderation_status === ReactionType::STATUS_PENDING)
                    ->action(function (ReactionType $record): void {
                        $record->update([
                            'moderation_status' => ReactionType::STATUS_REJECTED,
                            'is_active' => false,
                        ]);
                    }),
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
            'index' => Pages\ListReactionTypes::route('/'),
            'create' => Pages\CreateReactionType::route('/create'),
            'edit' => Pages\EditReactionType::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['label', 'short_code', 'emoji'];
    }
}

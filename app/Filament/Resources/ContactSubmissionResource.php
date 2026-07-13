<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactSubmissionResource\Pages;
use App\Models\ContactSubmission;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactSubmissionResource extends Resource
{
    protected static ?string $model = ContactSubmission::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Iletisim';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Iletisim Formlari';

    protected static ?string $pluralModelLabel = 'Iletisim Formlari';

    protected static ?string $modelLabel = 'Iletisim Formu';

    protected static bool $isGloballySearchable = false;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                TextInput::make('full_name')
                    ->label('Ad soyad')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('email')
                    ->label('E-posta')
                    ->disabled()
                    ->dehydrated(false),
            ]),
            Grid::make(2)->schema([
                TextInput::make('subject')
                    ->label('Konu')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->label('Durum')
                    ->options([
                        'new' => 'Yeni',
                        'reviewing' => 'Inceleniyor',
                        'replied' => 'Yanitlandi',
                        'archived' => 'Arsivlendi',
                    ])
                    ->required(),
            ]),
            Textarea::make('message')
                ->label('Mesaj')
                ->rows(8)
                ->disabled()
                ->dehydrated(false)
                ->columnSpanFull(),
            Grid::make(2)->schema([
                Toggle::make('consent_accepted')
                    ->label('Veri isleme onayi')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('ip_address')
                    ->label('IP adresi')
                    ->disabled()
                    ->dehydrated(false),
            ]),
            Grid::make(2)->schema([
                Placeholder::make('submitted_by')
                    ->label('Gonderen uye')
                    ->content(fn (?ContactSubmission $record) => $record?->user?->name ?? 'Misafir'),
                Placeholder::make('submitted_at')
                    ->label('Gonderim tarihi')
                    ->content(fn (?ContactSubmission $record) => $record?->created_at?->format('d.m.Y H:i') ?? '-'),
            ]),
            Textarea::make('admin_notes')
                ->label('Admin notlari')
                ->rows(5)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Ad soyad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('Konu')
                    ->searchable()
                    ->limit(60),
                IconColumn::make('read_at')
                    ->label('Okundu')
                    ->boolean()
                    ->getStateUsing(fn (ContactSubmission $record) => $record->read_at !== null),
                BadgeColumn::make('status')
                    ->label('Durum')
                    ->colors([
                        'warning' => 'new',
                        'info' => 'reviewing',
                        'success' => 'replied',
                        'gray' => 'archived',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'new' => 'Yeni',
                        'reviewing' => 'Inceleniyor',
                        'replied' => 'Yanitlandi',
                        'archived' => 'Arsivlendi',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'new' => 'Yeni',
                        'reviewing' => 'Inceleniyor',
                        'replied' => 'Yanitlandi',
                        'archived' => 'Arsivlendi',
                    ]),
                TernaryFilter::make('read_at')
                    ->label('Okundu')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('read_at'),
                        false: fn ($query) => $query->whereNull('read_at'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactSubmissions::route('/'),
            'edit' => Pages\EditContactSubmission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->whereNull('read_at')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}

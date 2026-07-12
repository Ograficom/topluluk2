<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycDocumentResource\Pages;
use App\Models\KycDocument;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KycDocumentResource extends Resource
{
    protected static ?string $model = KycDocument::class;

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function getNavigationGroup(): ?string
    {
        return __('Verification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('KYC Documents');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.username')
                    ->label(__('User'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('document_type')
                    ->label(__('Document Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(KycDocument::$documentTypes[$state] ?? $state)),

                Tables\Columns\TextColumn::make('document_number')
                    ->label(__('Document Number'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                        default => __('Pending'),
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Submitted'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('verified_by')
                    ->label(__('Reviewed By'))
                    ->formatStateUsing(fn ($record) => $record->verifiedBy?->username ?? '—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                    ]),
                Tables\Filters\SelectFilter::make('document_type')
                    ->label(__('Document Type'))
                    ->options(KycDocument::$documentTypes),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make(__('User Information'))
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('user.username')
                                    ->label(__('Username')),
                                Components\TextEntry::make('user.name')
                                    ->label(__('Name'))
                                    ->default('—'),
                                Components\TextEntry::make('user.email')
                                    ->label(__('Email')),
                            ]),
                    ]),

                Components\Section::make(__('Document Details'))
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('document_type')
                                    ->label(__('Document Type'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => __(KycDocument::$documentTypes[$state] ?? $state)),
                                Components\TextEntry::make('document_number')
                                    ->label(__('Document Number')),
                                Components\TextEntry::make('status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'approved' => __('Approved'),
                                        'rejected' => __('Rejected'),
                                        default => __('Pending'),
                                    }),
                            ]),
                    ]),

                Components\Section::make(__('Document Images'))
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\ImageEntry::make('document_front_path')
                                    ->label(__('Front Side'))
                                    ->disk(getCurrentDisk())
                                    ->width(400)
                                    ->height(300),
                                Components\ImageEntry::make('document_back_path')
                                    ->label(__('Back Side'))
                                    ->disk(getCurrentDisk())
                                    ->width(400)
                                    ->height(300)
                                    ->visible(fn ($record) => $record->document_back_path !== null),
                            ]),
                        Components\ImageEntry::make('selfie_path')
                            ->label(__('Selfie with Document'))
                            ->disk(getCurrentDisk())
                            ->width(400)
                            ->height(300)
                            ->visible(fn ($record) => $record->selfie_path !== null),
                    ]),

                Components\Section::make(__('Review Information'))
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('verifiedBy.username')
                                    ->label(__('Reviewed By'))
                                    ->default('—'),
                                Components\TextEntry::make('verified_at')
                                    ->label(__('Reviewed At'))
                                    ->dateTime()
                                    ->default('—'),
                                Components\TextEntry::make('admin_notes')
                                    ->label(__('Admin Notes'))
                                    ->default('—')
                                    ->visible(fn ($record) => $record->admin_notes !== null),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->status !== 'pending'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKycDocuments::route('/'),
            'view' => Pages\ViewKycDocument::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

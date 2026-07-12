<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activity Logs');
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
                    ->searchable()
                    ->default(__('System'))
                    ->url(fn ($record) => $record->user ? route('user.show', $record->user->username) : null, true),

                Tables\Columns\TextColumn::make('actionLabel')
                    ->label(__('Action'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($record) => $record->actionLabel()),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(80)
                    ->searchable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label(__('IP Address'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label(__('Action'))
                    ->options(fn () => ActivityLog::select('action')->distinct()->pluck('action', 'action')->map(fn ($a) => ActivityLog::actionLabelStatic($a))),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label(__('From')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label(__('Until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        \Filament\Infolists\Components\Section::make(__('Activity Log Details'))
                            ->schema([
                                \Filament\Infolists\Components\Grid::make(2)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('user.username')
                                            ->label(__('User'))
                                            ->default(__('System')),
                                        \Filament\Infolists\Components\TextEntry::make('actionLabel')
                                            ->label(__('Action'))
                                            ->badge()
                                            ->formatStateUsing(fn ($record) => $record->actionLabel()),
                                    ]),
                                \Filament\Infolists\Components\TextEntry::make('description')
                                    ->label(__('Description')),
                                \Filament\Infolists\Components\Grid::make(3)
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('ip_address')
                                            ->label(__('IP Address'))
                                            ->default('—'),
                                        \Filament\Infolists\Components\TextEntry::make('user_agent')
                                            ->label(__('User Agent'))
                                            ->limit(60)
                                            ->default('—'),
                                        \Filament\Infolists\Components\TextEntry::make('created_at')
                                            ->label(__('Date'))
                                            ->dateTime(),
                                    ]),
                            ]),
                    ]),
            ])
            ->bulkActions([])
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

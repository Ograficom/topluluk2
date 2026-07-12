<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function getNavigationGroup(): ?string
    {
        return __('Communication');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Conversations');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_names')
                    ->label(__('Participants'))
                    ->formatStateUsing(function ($record) {
                        return $record->users->pluck('username')->join(', ');
                    })
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('users', fn ($q) => $q->where('username', 'like', "%{$search}%"));
                    }),

                Tables\Columns\TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->default('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('messages_count')
                    ->label(__('Messages'))
                    ->counts('messages')
                    ->sortable(),

                Tables\Columns\TextColumn::make('latestMessage.created_at')
                    ->label(__('Last Message'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label(false)
                    ->size('md')
                    ->tooltip(__('Delete')),
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
                Components\Section::make(__('Conversation Details'))
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('subject')
                                    ->label(__('Subject'))
                                    ->default('—'),
                                Components\TextEntry::make('created_at')
                                    ->label(__('Created'))
                                    ->dateTime(),
                            ]),
                        Components\TextEntry::make('users_names')
                            ->label(__('Participants'))
                            ->formatStateUsing(fn ($record) => $record->users->pluck('username')->join(', ')),
                    ]),

                Components\Section::make(__('Messages'))
                    ->schema([
                        Components\RepeatableEntry::make('messages')
                            ->schema([
                                Components\TextEntry::make('user.username')
                                    ->label(__('From'))
                                    ->badge(),
                                Components\TextEntry::make('body')
                                    ->label(__('Message'))
                                    ->columnSpan(2),
                                Components\TextEntry::make('created_at')
                                    ->label(__('Sent'))
                                    ->dateTime(),
                                Components\TextEntry::make('read_at')
                                    ->label(__('Read'))
                                    ->dateTime()
                                    ->default(__('Not read')),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view' => Pages\ViewConversation::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

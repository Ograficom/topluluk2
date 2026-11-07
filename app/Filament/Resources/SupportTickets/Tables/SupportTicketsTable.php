<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Models\SupportTicket;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('No')->sortable(),
                TextColumn::make('subject')->label('Konu')->searchable()->limit(45)->wrap(),
                TextColumn::make('name')->label('Gönderen')->searchable()->sortable(),
                TextColumn::make('email')->label('E-posta')->searchable(),
                TextColumn::make('department')->label('Birim')->formatStateUsing(fn (string $state): string => match ($state) {
                    'support' => 'Teknik destek',
                    'editorial' => 'Editör ekibi',
                    'partnerships' => 'İş birlikleri',
                    default => $state,
                }),
                TextColumn::make('status')->label('Durum')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'new' => 'Yeni',
                    'reviewing' => 'İnceleniyor',
                    'replied' => 'Yanıtlandı',
                    'resolved' => 'Çözüldü',
                    default => $state,
                })->color(fn (string $state): string => match ($state) {
                    'new' => 'warning',
                    'reviewing' => 'info',
                    'replied' => 'success',
                    'resolved' => 'gray',
                    default => 'gray',
                }),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Durum')->options([
                    'new' => 'Yeni',
                    'reviewing' => 'İnceleniyor',
                    'replied' => 'Yanıtlandı',
                    'resolved' => 'Çözüldü',
                ]),
                SelectFilter::make('department')->label('Birim')->options([
                    'support' => 'Teknik destek',
                    'editorial' => 'Editör ekibi',
                    'partnerships' => 'İş birlikleri',
                ]),
            ])
            ->recordActions([
                EditAction::make()->label('İncele'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Reports\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->label('#'),
                TextColumn::make('reporter.name')
                    ->label('Sikayet eden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reportedUser.name')
                    ->label('Sikayet edilen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('topic')
                    ->label('Konu')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('show_username')
                    ->label('Kullanici adi goster')
                    ->boolean(),
                BadgeColumn::make('status')
                    ->label('Durum')
                    ->sortable()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_review',
                        'success' => 'resolved',
                        'danger' => 'dismissed',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Beklemede',
                        'in_review' => 'Incelemede',
                        'resolved' => 'Cozuldu',
                        'dismissed' => 'Kapandi',
                        default => $state,
                    }),
                TextColumn::make('created_at')->label('Tarih')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'pending' => 'Beklemede',
                        'in_review' => 'Incelemede',
                        'resolved' => 'Cozuldu',
                        'dismissed' => 'Kapandi',
                    ]),
                SelectFilter::make('topic')
                    ->label('Konu')
                    ->options([
                        'Taciz' => 'Taciz',
                        'Zorbalik' => 'Zorbalik',
                        'Dolandirici' => 'Dolandirici',
                        'Kimlik taklidi' => 'Kimlik taklidi',
                        'Casus veya supheli' => 'Casus veya supheli',
                        'Satici' => 'Satici',
                        'Istenmeyen' => 'Istenmeyen',
                        'Olasi aktivite' => 'Olasi aktivite',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}

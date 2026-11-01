<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Kullanıcı adı')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable(),
                TextColumn::make('verification_status')
                    ->label('Doğrulama')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'İnceleme bekliyor',
                        'approved' => 'Doğrulandı',
                        'rejected' => 'Reddedildi',
                        default => 'Doğrulanmadı',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_admin')
                    ->label('Yönetici')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('is_admin')
                    ->label('Yetki')
                    ->options([
                        1 => 'Yönetici',
                        0 => 'Üye',
                    ]),
                SelectFilter::make('verification_status')
                    ->label('Doğrulama')
                    ->options([
                        'unverified' => 'Doğrulanmadı',
                        'pending' => 'İnceleme bekliyor',
                        'approved' => 'Doğrulandı',
                        'rejected' => 'Reddedildi',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

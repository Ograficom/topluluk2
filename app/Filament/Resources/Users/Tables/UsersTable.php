<?php

namespace App\\Filament\\Resources\\Users\\Tables;

use App\\Models\\User;
use Filament\\Actions\\Action;
use Filament\\Actions\\BulkAction;
use Filament\\Actions\\BulkActionGroup;
use Filament\\Actions\\DeleteBulkAction;
use Filament\\Actions\\EditAction;
use Filament\\Notifications\\Notification;
use Filament\\Tables\\Columns\\IconColumn;
use Filament\\Tables\\Columns\\TextColumn;
use Filament\\Tables\\Filters\\SelectFilter;
use Filament\\Tables\\Table;
use Illuminate\\Database\\Eloquent\\Collection;

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
                TextColumn::make('email_verified_at')
                    ->label('E-posta onayı')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Onaylandı' : 'Onay bekliyor')
                    ->color(fn ($state): string => $state ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('verification_status')
                    ->label('Mavi tik')
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
                SelectFilter::make('email_verified_at')
                    ->label('E-posta onayı')
                    ->options([
                        'verified' => 'Onaylandı',
                        'unverified' => 'Onay bekliyor',
                    ])
                    ->query(fn ($query, array $data) => match ($data['value'] ?? null) {
                        'verified' => $query->whereNotNull('email_verified_at'),
                        'unverified' => $query->whereNull('email_verified_at'),
                        default => $query,
                    }),
                SelectFilter::make('verification_status')
                    ->label('Mavi tik')
                    ->options([
                        'unverified' => 'Doğrulanmadı',
                        'pending' => 'İnceleme bekliyor',
                        'approved' => 'Doğrulandı',
                        'rejected' => 'Reddedildi',
                    ]),
            ])
            ->recordActions([
                Action::make('verifyEmail')
                    ->label('E-postayı onayla')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('E-posta adresi onaylansın mı?')
                    ->modalDescription('Bu kullanıcı, e-posta bağlantısına tıklamış gibi doğrulanmış olarak işaretlenecek.')
                    ->visible(fn (User $record): bool => ! $record->hasVerifiedEmail())
                    ->action(function (User $record): void {
                        $record->forceFill(['email_verified_at' => now()])->save();

                        Notification::make()
                            ->title('E-posta adresi onaylandı')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('verifyEmails')
                        ->label('Seçilenlerin e-postasını onayla')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (User $record): void {
                                if (! $record->hasVerifiedEmail()) {
                                    $record->forceFill(['email_verified_at' => now()])->save();
                                }
                            });

                            Notification::make()
                                ->title('Seçilen kullanıcıların e-posta onayı güncellendi')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\AccountDeletionRequests\Tables;

use App\Models\AccountDeletionRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class AccountDeletionRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_name')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Kullanıcı adı')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable(),
                TextColumn::make('requested_at')
                    ->label('Talep tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Onay bekliyor',
                        'approved' => 'Kalıcı olarak silindi',
                        'rejected' => 'Reddedildi / hesap açıldı',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('processed_at')
                    ->label('İşlem tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Talep durumu')
                    ->options([
                        'pending' => 'Onay bekliyor',
                        'approved' => 'Kalıcı olarak silindi',
                        'rejected' => 'Reddedildi',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Kalıcı sil')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hesap kalıcı olarak silinsin mi?')
                    ->modalDescription('Kullanıcının hesabı ve ilişkili kayıtları veritabanından kalıcı olarak silinecek. Bu işlem geri alınamaz.')
                    ->visible(fn (AccountDeletionRequest $record): bool => $record->status === 'pending' && $record->user_id !== null)
                    ->action(function (AccountDeletionRequest $record): void {
                        DB::transaction(function () use ($record): void {
                            $user = User::withTrashed()->find($record->user_id);

                            if (! $user) {
                                throw new \RuntimeException('Silinecek kullanıcı kaydı bulunamadı.');
                            }

                            $user->forceDelete();

                            $record->update([
                                'user_id' => null,
                                'status' => 'approved',
                                'processed_by' => auth()->id(),
                                'processed_at' => now(),
                            ]);
                        });

                        Notification::make()
                            ->title('Hesap kalıcı olarak silindi')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reddet ve hesabı aç')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Silme talebi reddedilsin mi?')
                    ->modalDescription('Kullanıcının hesabı yeniden etkinleştirilir ve giriş yapabilir.')
                    ->visible(fn (AccountDeletionRequest $record): bool => $record->status === 'pending' && $record->user_id !== null)
                    ->action(function (AccountDeletionRequest $record): void {
                        DB::transaction(function () use ($record): void {
                            $user = User::withTrashed()->find($record->user_id);

                            if (! $user) {
                                throw new \RuntimeException('Geri açılacak kullanıcı kaydı bulunamadı.');
                            }

                            $user->restore();

                            $record->update([
                                'status' => 'rejected',
                                'processed_by' => auth()->id(),
                                'processed_at' => now(),
                            ]);
                        });

                        Notification::make()
                            ->title('Talep reddedildi, hesap yeniden açıldı')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('requested_at', 'desc');
    }
}

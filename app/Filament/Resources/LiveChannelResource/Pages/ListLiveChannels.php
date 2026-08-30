<?php

namespace App\Filament\Resources\LiveChannelResource\Pages;

use App\Filament\Resources\LiveChannelResource;
use App\Services\LiveChannelPlaylistService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLiveChannels extends ListRecords
{
    protected static string $resource = LiveChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncM3u')
                ->label('M3U listesini geri yükle')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function (): void {
                    $result = app(LiveChannelPlaylistService::class)
                        ->syncFromFile(public_path('streams/turkiye.m3u'));

                    Notification::make()
                        ->title('M3U listesi kontrol edildi')
                        ->body($result['created'] . ' eksik kanal eklendi, ' . $result['existing'] . ' mevcut kanal korundu.')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()->label('Kanal ekle'),
        ];
    }
}

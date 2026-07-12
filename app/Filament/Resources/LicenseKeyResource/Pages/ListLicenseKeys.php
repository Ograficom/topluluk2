<?php

namespace App\Filament\Resources\LicenseKeyResource\Pages;

use App\Filament\Resources\LicenseKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLicenseKeys extends ListRecords
{
    protected static string $resource = LicenseKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate')
                ->label(__('Generate New Key'))
                ->icon('heroicon-o-key')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription(__('A new license key will be generated. The plaintext key will only be shown once — copy it immediately.'))
                ->form([
                    \Filament\Forms\Components\TextInput::make('label')
                        ->label(__('Label'))
                        ->placeholder(__('e.g. Production server')),
                    \Filament\Forms\Components\TextInput::make('max_domains')
                        ->label(__('Max Domains'))
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(999),
                ])
                ->action(function (array $data): void {
                    $key = \App\Models\LicenseKey::generate($data['label'] ?? null, (int) ($data['max_domains'] ?? 1));

                    \Filament\Notifications\Notification::make()
                        ->title(__('License key generated'))
                        ->body($key)
                        ->success()
                        ->persistent()
                        ->send();
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources\KycDocumentResource\Pages;

use App\Filament\Resources\KycDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKycDocument extends ViewRecord
{
    protected static string $resource = KycDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label(__('Approve'))
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function () {
                    $this->getRecord()->update([
                        'status' => 'approved',
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                    ]);
                    $this->getRecord()->user->update(['kyc_status' => 'verified']);
                })
                ->visible(fn () => $this->getRecord()->status === 'pending'),

            Actions\Action::make('reject')
                ->label(__('Reject'))
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->form([
                    \Filament\Forms\Components\Textarea::make('admin_notes')
                        ->label(__('Reason for rejection'))
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data) {
                    $this->getRecord()->update([
                        'status' => 'rejected',
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                        'admin_notes' => $data['admin_notes'],
                    ]);
                    $this->getRecord()->user->update(['kyc_status' => 'rejected']);
                })
                ->visible(fn () => $this->getRecord()->status === 'pending'),
        ];
    }
}

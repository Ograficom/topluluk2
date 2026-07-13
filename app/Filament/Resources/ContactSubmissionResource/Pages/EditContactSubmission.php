<?php

namespace App\Filament\Resources\ContactSubmissionResource\Pages;

use App\Filament\Resources\ContactSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContactSubmission extends EditRecord
{
    protected static string $resource = ContactSubmissionResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->read_at === null) {
            $this->record->forceFill([
                'read_at' => now(),
            ])->save();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

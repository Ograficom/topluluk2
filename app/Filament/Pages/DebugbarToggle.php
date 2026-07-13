<?php

namespace App\Filament\Pages;

use Filament\Actions;
use Filament\Pages\Page;

class DebugbarToggle extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bug-ant';

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected string $view = 'filament.pages.debugbar-toggle';

    public function getTitle(): string
    {
        return 'Debugbar';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle')
                ->label($this->debugbarEnabled() ? 'Debugbar On' : 'Debugbar Off')
                ->icon('heroicon-o-power')
                ->color($this->debugbarEnabled() ? 'success' : 'danger')
                ->action(function () {
                    if ($this->debugbarEnabled()) {
                        session(['debugbar_disabled' => true]);
                        session()->flash('status', 'Debugbar kapatildi');
                    } else {
                        session()->forget('debugbar_disabled');
                        session()->flash('status', 'Debugbar acildi');
                    }

                    return redirect()->to(static::getUrl());
                })
                ->button()
                ->extraAttributes([
                    'class' => 'inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold',
                ]),
        ];
    }

    private function debugbarEnabled(): bool
    {
        return session('debugbar_disabled', false) === false;
    }
}






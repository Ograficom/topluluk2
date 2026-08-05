<?php

namespace App\Livewire;

use Asmit\FilaCalendar\Forms\Components\CalendarInput;
use Asmit\FilaCalendar\Support\CalendarMode;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Public-facing (non-admin) use of the fila-calendar package's CalendarInput field.
 * Lets a visitor pick a date range on /tarihe-gore and jump to the matching archive
 * listing. Built as a standalone Livewire component implementing HasForms, which is
 * Filament's documented way to use a form field outside an admin panel.
 */
class ArchiveDateRangePicker extends Component implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(?string $from = null, ?string $to = null): void
    {
        $this->form->fill([
            'range' => ($from && $to) ? ['start' => $from, 'end' => $to] : null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                CalendarInput::make('range')
                    ->label('Tarih araligi sec')
                    ->mode(CalendarMode::Range)
                    ->months(2)
                    ->calendarColumns(['default' => 1, 'md' => 2])
                    ->withToday()
                    ->maxDate(now()->toDateString()),
            ])
            ->statePath('data');
    }

    public function apply(): void
    {
        $range = $this->form->getState()['range'] ?? null;

        if (!is_array($range) || blank($range['start'] ?? null) || blank($range['end'] ?? null)) {
            $this->addError('data.range', 'Lutfen once bir tarih araligi secin.');

            return;
        }

        $this->redirect(route('blog.archive', [
            'from' => $range['start'],
            'to' => $range['end'],
        ]));
    }

    public function render(): View
    {
        return view('livewire.archive-date-range-picker');
    }
}

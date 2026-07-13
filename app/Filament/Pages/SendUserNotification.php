<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Notifications\AdminUserNotification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class SendUserNotification extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.send-user-notification';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bell';
    protected static string | \UnitEnum | null $navigationGroup = 'Bildirimler';
    protected static ?string $navigationLabel = 'Bildirim Gonder';

    public array $state = [];

    public function mount(): void
    {
        $this->form->fill([
            'send_to_all' => false,
        ]);
    }

    protected function getFormStatePath(): string
    {
        return 'state';
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Baslik')
                ->required()
                ->maxLength(120),
            Textarea::make('body')
                ->label('Mesaj')
                ->required()
                ->maxLength(2000)
                ->rows(5),
            TextInput::make('url')
                ->label('Link (opsiyonel)')
                ->url()
                ->maxLength(2048),
            Toggle::make('send_to_all')
                ->label('Tum kullanicilara gonder'),
            Select::make('recipients')
                ->label('Kullanicilar')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->all())
                ->required(fn (Get $get) => !$get('send_to_all'))
                ->disabled(fn (Get $get) => $get('send_to_all')),
        ];
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $sendToAll = (bool) ($data['send_to_all'] ?? false);
        $recipientIds = array_values(array_filter($data['recipients'] ?? []));

        if (!$sendToAll && empty($recipientIds)) {
            Notification::make()
                ->title('En az bir kullanici secmelisiniz')
                ->danger()
                ->send();
            return;
        }

        $title = trim((string) ($data['title'] ?? ''));
        $body = trim((string) ($data['body'] ?? ''));
        $url = trim((string) ($data['url'] ?? ''));
        $url = $url !== '' ? $url : null;

        $query = User::query();
        if (!$sendToAll) {
            $query->whereIn('id', $recipientIds);
        }

        $sentCount = 0;
        $query->chunkById(200, function ($users) use (&$sentCount, $title, $body, $url) {
            NotificationFacade::send($users, new AdminUserNotification($title, $body, $url));
            $sentCount += $users->count();
        });

        Notification::make()
            ->title('Bildirim gonderildi')
            ->body($sentCount . ' kullaniciya iletildi.')
            ->success()
            ->send();

        $this->form->fill([
            'title' => '',
            'body' => '',
            'url' => '',
            'send_to_all' => false,
            'recipients' => [],
        ]);
    }
}











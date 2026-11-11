<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class SiteActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title($this->title)
            ->body($this->message)
            ->actions([
                Action::make('view')
                    ->label('Görüntüle')
                    ->button()
                    ->url($this->url)
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}

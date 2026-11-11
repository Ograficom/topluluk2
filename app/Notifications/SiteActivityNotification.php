<?php

namespace App\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteActivityNotification extends Notification
{
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $url,
        public readonly ?string $sourceKey = null,
        public readonly ?string $category = null,
    ) {}

    public function via(object $notifiable): array
    {
        // Administrative notifications are always kept in the Filament database inbox.
        if ($this->category === null) {
            return ['database'];
        }

        $preferences = $notifiable->notification_preferences ?? [];
        $channels = [];

        $streamEnabled = (bool) data_get($preferences, 'stream.'.$this->category, true);

        $legacyEmailDefault = match ($this->category) {
            'replies' => (bool) ($notifiable->notify_replies ?? true),
            'posts' => (bool) ($notifiable->notify_posts ?? true),
            'announcements' => (bool) ($notifiable->notify_announcements ?? true),
            default => true,
        };

        $emailEnabled = (bool) data_get($preferences, 'email.'.$this->category, $legacyEmailDefault);
        $pushEnabled = (bool) data_get($preferences, 'push.'.$this->category, $this->category === 'security');

        // The database row powers the in-site stream and is also the durable push event record.
        if ($streamEnabled || $pushEnabled) {
            $channels[] = 'database';
        }

        if ($emailEnabled) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            ...FilamentNotification::make()
                ->title($this->title)
                ->body($this->message)
                ->actions([
                    Action::make('view')
                        ->label('Görüntüle')
                        ->button()
                        ->url($this->url)
                        ->markAsRead(),
                ])
                ->getDatabaseMessage(),
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'category' => $this->category,
            'source_key' => $this->sourceKey,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $target = str_starts_with($this->url, 'http')
            ? $this->url
            : url('/'.ltrim($this->url, '/'));

        return (new MailMessage)
            ->subject($this->title.' — OgraFi')
            ->greeting('Merhaba '.$notifiable->displayName().'!')
            ->line($this->message)
            ->action('Bildirimi görüntüle', $target)
            ->line('Bu e-postayı bildirim tercihlerin açık olduğu için aldın.');
    }
}

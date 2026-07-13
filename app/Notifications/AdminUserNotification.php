<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AdminUserNotification extends Notification
{
    public function __construct(
        private string $title,
        private string $body,
        private ?string $url = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'admin',
            'title' => Str::limit($this->title, 120),
            'body' => Str::limit($this->body, 2000),
            'url' => $this->url,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MessageReceivedNotification extends Notification
{
    public function __construct(private Message $message)
    {
        $this->message->loadMissing(['sender:id,name,username']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->message->sender;
        $senderName = $sender?->name ?? 'Bir kullanici';

        return [
            'type' => 'message_received',
            'title' => 'Yeni mesaj',
            'body' => $senderName . ': ' . Str::limit((string) $this->message->body, 140),
            'url' => $sender ? route('messages.show', $sender) : null,
            'message_id' => $this->message->id,
            'actor_id' => $sender?->id,
            'actor_name' => $senderName,
        ];
    }
}

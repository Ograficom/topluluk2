<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class UserFollowedNotification extends Notification
{
    public function __construct(private User $follower)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $followerName = $this->follower->name ?? 'Bir kullanici';

        return [
            'type' => 'user_followed',
            'title' => 'Yeni takipci',
            'body' => $followerName . ' seni takip etmeye basladi.',
            'url' => route('users.show', $this->follower),
            'actor_id' => $this->follower->id,
            'actor_name' => $followerName,
        ];
    }
}

<?php

namespace App\Notifications\Report;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $reporter, public User $reported, public string $reason)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reported_user',
            'reason' => $this->reason,
            'reporter' => [
                'name' => $this->reporter->name,
                'nickname' => $this->reporter->username,
                'avatar' => $this->reporter->getAvatar(),
            ],
            'reported' => [
                'name' => $this->reported->name,
                'nickname' => $this->reported->username,
                'avatar' => $this->reported->getAvatar(),
            ],
        ];
    }
}

<?php

namespace App\Notifications\Report;

use App\Models\Story;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoryReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user, public Story $story, public string $reason, public ?string $message)
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
            'type' => 'reported_story',
            'reason' => $this->reason,
            'message' => $this->message,
            'user' => [
                'name' => $this->user->name,
                'nickname' => $this->user->username,
                'avatar' => $this->user->getAvatar(),
            ],
            'story' => [
                'id' => $this->story->id,
                'title' => $this->story->title,
                'slug' => $this->story->slug,
            ],
        ];
    }
}

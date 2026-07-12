<?php

namespace App\Notifications\Comment;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentMentionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $user,
        public Comment $comment,
    ) {
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

    public function morphMap()
    {
        return [
            self::class => 'CommentMentionNotification',
        ];
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

            'type' => 'comment_mention',
            'user' => [
                'name' => $this->user->name,
                'nickname' => $this->user->username,
                'avatar' => $this->user->getAvatar(),
            ],
            'comment' => [
                'id' => $this->comment->id,
                'body' => $this->comment->comment,
            ],
            'story' => [
                'id' => $this->comment->commentable_id,
                'title' => $this->comment->commentable->title,
                'slug' => $this->comment->commentable->slug,
            ],
        ];
    }
}

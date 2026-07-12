<?php

namespace App\Notifications\Like;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $liker,
        public Comment $comment
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
            'type' => 'liked_comment',
            'liker' => [
                'name' => $this->liker->name,
                'nickname' => $this->liker->username,
                'avatar' => $this->liker->getAvatar(),
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

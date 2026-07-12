<?php

namespace App\Notifications\Comment;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Comment $comment,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('A comment was posted on your article')
            ->markdown('emails.comment-added', [
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'comment',
            'user' => [
                'name' => $this->comment->user->name,
                'nickname' => $this->comment->user->username,
                'avatar' => $this->comment->user->getAvatar(),
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

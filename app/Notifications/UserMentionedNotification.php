<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserMentionedNotification extends Notification
{
    public function __construct(
        private User $actor,
        private Post|Comment $subject,
    ) {
        if ($this->subject instanceof Comment) {
            $this->subject->loadMissing([
                'post:id,title,slug',
                'user:id,name,username',
            ]);
        } else {
            $this->subject->loadMissing([
                'author:id,name,username',
            ]);
        }
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actorName = $this->actor->name ?: ($this->actor->username ?: 'Bir kullanici');

        if ($this->subject instanceof Comment) {
            $post = $this->subject->post;

            return [
                'type' => 'user_mention',
                'title' => 'Bir yorumda senden bahsedildi',
                'body' => $actorName . ': ' . Str::limit((string) $this->subject->content, 140),
                'url' => $post ? route('blog.post', $post) . '#comment-' . $this->subject->id : null,
                'post_id' => $post?->id,
                'post_title' => $post?->title,
                'comment_id' => $this->subject->id,
                'actor_id' => $this->actor->id,
                'actor_name' => $actorName,
                'mention_context' => 'comment',
            ];
        }

        return [
            'type' => 'user_mention',
            'title' => 'Bir gonderide senden bahsedildi',
            'body' => $actorName . ': ' . Str::limit(strip_tags((string) $this->subject->content), 140),
            'url' => route('blog.post', $this->subject),
            'post_id' => $this->subject->id,
            'post_title' => $this->subject->title,
            'actor_id' => $this->actor->id,
            'actor_name' => $actorName,
            'mention_context' => 'post',
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PostCommentedNotification extends Notification
{
    public function __construct(private Comment $comment)
    {
        $this->comment->loadMissing(['post:id,title,slug', 'user:id,name,username']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $post = $this->comment->post;
        $author = $this->comment->user;
        $authorName = $author?->name ?? 'Bir kullanici';

        return [
            'type' => 'post_comment',
            'title' => 'Postuna yeni yorum',
            'body' => $authorName . ': ' . Str::limit((string) $this->comment->content, 140),
            'url' => $post ? route('blog.post', $post) : null,
            'post_id' => $post?->id,
            'post_title' => $post?->title,
            'comment_id' => $this->comment->id,
            'actor_id' => $author?->id,
            'actor_name' => $authorName,
        ];
    }
}

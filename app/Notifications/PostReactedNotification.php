<?php

namespace App\Notifications;

use App\Models\Reaction;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PostReactedNotification extends Notification
{
    public function __construct(private Reaction $reaction)
    {
        $this->reaction->loadMissing(['post:id,title,slug', 'user:id,name,username', 'type:id,label,emoji']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $post = $this->reaction->post;
        $author = $this->reaction->user;
        $authorName = $author?->name ?? 'Bir kullanici';
        $typeLabel = $this->reaction->type?->label ?? 'tepkisi';
        $emoji = $this->reaction->type?->emoji ?? '';
        $message = trim($emoji . ' ' . $typeLabel);

        return [
            'type' => 'post_reaction',
            'title' => 'Postuna yeni tepki',
            'body' => $authorName . ': ' . Str::limit($message, 140),
            'url' => $post ? route('blog.post', $post) : null,
            'post_id' => $post?->id,
            'post_title' => $post?->title,
            'reaction_id' => $this->reaction->id,
            'reaction_type' => $this->reaction->type?->label,
            'actor_id' => $author?->id,
            'actor_name' => $authorName,
        ];
    }
}

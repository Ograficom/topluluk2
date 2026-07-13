<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CategoryPostPublishedNotification extends Notification
{
    public function __construct(private Post $post)
    {
        $this->post->loadMissing(['category:id,name,slug', 'author:id,name,username']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $category = $this->post->category;
        $author = $this->post->author;
        $authorName = $author?->name ?? 'Bir kullanici';
        $categoryName = $category?->name ?? 'Kategori';

        return [
            'type' => 'category_post',
            'title' => 'Takip ettigin kategoride yeni post',
            'body' => $categoryName . ' kategorisinde ' . $authorName . ' yeni bir post paylasti: ' . Str::limit((string) $this->post->title, 120),
            'url' => route('blog.post', $this->post),
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'category_id' => $category?->id,
            'category_name' => $categoryName,
            'actor_id' => $author?->id,
            'actor_name' => $authorName,
        ];
    }
}

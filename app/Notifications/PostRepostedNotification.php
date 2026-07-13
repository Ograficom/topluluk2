<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PostRepostedNotification extends Notification
{
    public function __construct(
        private Post $originalPost,
        private Post $repost,
        private ?User $actor
    ) {
        $this->originalPost->loadMissing(['author:id,name,username']);
        $this->repost->loadMissing(['author:id,name,username']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actorName = $this->actor?->name ?? 'Bir kullanici';
        $repostedAt = $this->repost->created_at
            ? Carbon::parse($this->repost->created_at)->format('d.m.Y H:i')
            : Carbon::now()->format('d.m.Y H:i');
        $body = $actorName . ' • ' . $repostedAt . ' • ' . Str::limit((string) $this->repost->title, 80);

        return [
            'type' => 'post_repost',
            'title' => 'Postun yeniden paylasildi',
            'body' => $body,
            'url' => route('blog.post', $this->repost),
            'post_id' => $this->originalPost->id,
            'post_title' => $this->originalPost->title,
            'repost_id' => $this->repost->id,
            'repost_title' => $this->repost->title,
            'repost_url' => route('blog.post', $this->repost),
            'original_url' => route('blog.post', $this->originalPost),
            'actor_id' => $this->actor?->id,
            'actor_name' => $actorName,
            'reposted_at' => $this->repost->created_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
        ];
    }
}

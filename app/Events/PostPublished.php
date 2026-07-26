<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $postId,
        public string $publishedAt,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('posts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PostPublished';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->postId,
            'published_at' => $this->publishedAt,
        ];
    }

    public static function fromPost(Post $post): self
    {
        return new self(
            (int) $post->id,
            optional($post->published_at)->toIso8601String() ?? now()->toIso8601String(),
        );
    }
}

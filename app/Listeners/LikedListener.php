<?php

namespace App\Listeners;

use App\Events\Like\Liked;
use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use App\Notifications\Like\CommentLikedNotification;
use App\Notifications\Like\StoryLikedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LikedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Liked $event): void
    {
        if ($event->like->likeable_type === 'App\Models\Story') {
            $liker = User::find($event->like->user_id);
            $story = Story::find($event->like->likeable_id);

            $notifyEnabled = $story->author->notify_settings !== null && $story->author->notify_settings['liked'] !== false;

            if ($liker->id !== $story->user_id && $notifyEnabled) {
                $story->author->notify(new StoryLikedNotification($liker, $story));
            }
        }

        if ($event->like->likeable_type === 'App\Models\Comment') {
            $liker = User::find($event->like->user_id);
            $comment = Comment::find($event->like->likeable_id);

            $notifyEnabled = $comment->user->notify_settings !== null && $comment->user->notify_settings['liked'] !== false;

            if ($liker->id !== $comment->user_id && $notifyEnabled) {
                $comment->user->notify(new CommentLikedNotification($liker, $comment));
            }
        }
    }
}

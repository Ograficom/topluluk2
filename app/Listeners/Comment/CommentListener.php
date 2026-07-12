<?php

namespace App\Listeners\Comment;

use App\Events\Comment\CreatedComment;
use App\Models\Story;
use App\Models\User;
use App\Notifications\Comment\CommentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CommentListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CreatedComment $event): void
    {
        if ($event->comment->commentable_type === 'App\Models\Story') {
            $commenter = User::find($event->comment->user_id);
            $story = Story::find($event->comment->commentable_id);

            $notifyEnabled = $story->author->notify_settings !== null && $story->author->notify_settings['new_comments'] !== false;

            if ($commenter->id !== $story->user_id && $notifyEnabled) {
                $story->author->notify(new CommentNotification($event->comment));
            }
        }
    }
}

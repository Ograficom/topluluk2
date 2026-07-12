<?php

namespace App\Listeners\Comment;

use App\Events\Comment\CreatedCommentReply;
use App\Models\User;
use App\Notifications\Comment\CommentReplyNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CommentReplyListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CreatedCommentReply $event): void
    {
        if ($event->comment->commentable_type === 'App\Models\Story') {
            $user = User::find($event->comment->parentComment->user_id);

            $notifyEnabled = $user->notify_settings !== null && $user->notify_settings['replies_comments'] !== false;

            if ($event->comment->user_id !== $event->comment->parentComment->user_id && $notifyEnabled) {
                $user->notify(new CommentReplyNotification($event->comment));
            }
        }
    }
}

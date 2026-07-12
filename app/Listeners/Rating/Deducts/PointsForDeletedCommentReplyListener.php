<?php

namespace App\Listeners\Rating\Deducts;

use App\Events\Comment\DeletedCommentReply;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForDeletedCommentReplyListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(DeletedCommentReply $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled) {
            $user = User::find($event->comment->user_id);
            $amount = (int) config('points.deleted_comment_reply');
            $reason = 'deleted_comment_reply';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

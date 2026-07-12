<?php

namespace App\Listeners\Rating\Awards;

use App\Events\Comment\CreatedCommentReply;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForCreatedCommentReplyListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CreatedCommentReply $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled) {
            $user = User::find($event->comment->user_id);
            $amount = (int) config('points.created_comment_reply');
            $reason = 'created_comment_reply';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

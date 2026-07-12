<?php

namespace App\Listeners\Rating\Deducts;

use App\Events\Comment\DeletedComment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForDeletedCommentListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(DeletedComment $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled) {
            $user = User::find($event->comment->user_id);
            $amount = (int) config('points.deleted_comment');
            $reason = 'deleted_comment';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

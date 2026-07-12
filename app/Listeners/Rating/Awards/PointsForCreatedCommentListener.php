<?php

namespace App\Listeners\Rating\Awards;

use App\Events\Comment\CreatedComment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForCreatedCommentListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CreatedComment $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled) {
            $user = User::find($event->comment->user_id);
            $amount = (int) config('points.created_comment');
            $reason = 'created_comment';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

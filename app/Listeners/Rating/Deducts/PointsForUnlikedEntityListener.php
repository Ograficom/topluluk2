<?php

namespace App\Listeners\Rating\Deducts;

use App\Events\Like\Unliked;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForUnlikedEntityListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Unliked $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled && $event->like->likeable_type === 'App\Models\Story') {
            $user = User::find($event->like->user_id);
            $amount = config('points.unliked_post');
            $reason = 'unliked_post';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }

        if ($isRatingEnabled && $event->like->likeable_type === 'App\Models\Comment') {
            $user = User::find($event->like->user_id);
            $amount = config('points.unliked_comment');
            $reason = 'unliked_comment';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

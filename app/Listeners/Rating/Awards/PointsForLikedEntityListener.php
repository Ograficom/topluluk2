<?php

namespace App\Listeners\Rating\Awards;

use App\Events\Like\Liked;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForLikedEntityListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Liked $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled && $event->like->likeable_type === 'App\Models\Story') {
            $user = User::find($event->like->user_id);
            $amount = config('points.liked_post');
            $reason = 'liked_post';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }

        if ($isRatingEnabled && $event->like->likeable_type === 'App\Models\Comment') {
            $user = User::find($event->like->user_id);
            $amount = config('points.liked_comment');
            $reason = 'liked_comment';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

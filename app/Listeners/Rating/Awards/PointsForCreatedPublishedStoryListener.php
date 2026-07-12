<?php

namespace App\Listeners\Rating\Awards;

use App\Events\Story\CreatedPublishedStory;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForCreatedPublishedStoryListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CreatedPublishedStory $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled) {
            $user = User::find($event->story->user_id);
            $amount = (int) config('points.created_published_story');
            $reason = 'created_published_story';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

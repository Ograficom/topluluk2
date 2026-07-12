<?php

namespace App\Listeners\Rating\Deducts;

use App\Events\Story\DeletedPublishedStory;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForDeletedPublishedStoryListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(DeletedPublishedStory $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled) {
            $user = User::find($event->story->user_id);
            $amount = (int) config('points.deleted_published_story');
            $reason = 'deleted_published_story';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

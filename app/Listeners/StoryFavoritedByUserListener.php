<?php

namespace App\Listeners;

use App\Events\Favorite\Favorited;
use App\Models\Story;
use App\Models\User;
use App\Notifications\StoryFavoritedByUserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoryFavoritedByUserListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Favorited $event): void
    {
        if ($event->favorite->favoriteable_type === 'App\Models\Story') {
            $user = User::find($event->favorite->user_id);
            $story = Story::find($event->favorite->favoriteable_id);

            if ($user->id !== $story->user_id) {
                $story->user->notify(new StoryFavoritedByUserNotification($user, $story));
            }
        }
    }
}

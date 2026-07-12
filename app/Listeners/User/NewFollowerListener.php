<?php

namespace App\Listeners\User;

use App\Events\Follow\Followed;
use App\Models\User;
use App\Notifications\User\NewUserFollowNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NewFollowerListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Followed $event): void
    {
        if ($event->followable_type === 'App\Models\User') {
            $user = User::find($event->followable_id);
            $follower = User::find($event->follower_id);

            $isNotifyEnabled = $user->notify_settings !== null && $user->notify_settings['new_follower'] !== false;

            if ($isNotifyEnabled) {
                $user->notify(new NewUserFollowNotification($follower));
            }
        }
    }
}

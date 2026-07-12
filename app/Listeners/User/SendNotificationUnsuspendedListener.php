<?php

namespace App\Listeners\User;

use App\Events\Suspend\Unsuspended;
use App\Notifications\User\UnsuspendNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNotificationUnsuspendedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Unsuspended $unsuspended): void
    {
        $unsuspended->user->notify(new UnsuspendNotification($unsuspended->user));
    }
}

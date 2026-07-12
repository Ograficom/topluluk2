<?php

namespace App\Listeners\User;

use App\Events\Suspend\Suspended;
use App\Notifications\User\SuspendNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNotificationSuspendedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Suspended $suspended): void
    {
        $suspended->user->notify(new SuspendNotification($suspended->user));
    }
}

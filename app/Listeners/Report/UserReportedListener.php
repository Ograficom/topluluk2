<?php

namespace App\Listeners\Report;

use App\Events\Report\UserReported;
use App\Models\User;
use App\Notifications\Report\UserReportedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserReportedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserReported $reported): void
    {
        $userReporter = User::find($reported->reporterId);
        $userReported = User::find($reported->reportedId);

        $mods = User::role(['administrator', 'moderator'])->get();

        foreach ($mods as $mod) {
            $mod->notify(new UserReportedNotification($userReporter, $userReported, $reported->reason));
        }
    }
}

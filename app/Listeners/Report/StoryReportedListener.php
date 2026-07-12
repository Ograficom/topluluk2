<?php

namespace App\Listeners\Report;

use App\Events\Report\StoryReported;
use App\Models\Story;
use App\Models\User;
use App\Notifications\Report\StoryReportedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoryReportedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(StoryReported $reported): void
    {
        $user = User::find($reported->userId);
        $story = Story::find($reported->storyId);

        $mods = User::role(['administrator', 'moderator'])->get();

        foreach ($mods as $mod) {
            $mod->notify(new StoryReportedNotification($user, $story, $reported->reason, $reported->message));
        }
    }
}

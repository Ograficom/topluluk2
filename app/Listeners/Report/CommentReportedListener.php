<?php

namespace App\Listeners\Report;

use App\Events\Report\CommentReported;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\Report\CommentReportedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CommentReportedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CommentReported $reported): void
    {
        $user = User::find($reported->userId);
        $comment = Comment::find($reported->commentId);

        $mods = User::role(['administrator', 'moderator'])->get();

        foreach ($mods as $mod) {
            $mod->notify(new CommentReportedNotification($user, $comment, $reported->reason, $reported->message));
        }
    }
}

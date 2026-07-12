<?php

namespace App\Http\Controllers;

use App\Events\Report\CommentReported;
use App\Events\Report\StoryReported;
use App\Events\Report\UserReported;
use App\Models\ReportedComment;
use App\Models\ReportedStory;
use App\Models\ReportedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function story(Request $request)
    {
        // User must be logged in system
        if (auth()->guest()) {
            toast_warning(__('You must be logged in!'));

            return back();
        }

        $request->validate([
            'story_id' => ['required', 'integer', 'exists:stories,id'],
            'reason' => ['required', 'string'],
            'message' => ['sometimes', 'max:500'],
        ]);

        // Checks first if a user already sent
        if (ReportedStory::query()->where('user_id', auth()->id())->where('story_id', $request->story_id)->first()) {
            toast_warning(__('Your report is being reviewed!'));

            return back();
        } else {
            ReportedStory::create([
                'story_id' => $request->story_id,
                'user_id' => auth()->id(),
                'reason' => $request->reason,
                'message' => $request->message,
            ]);

            event(new StoryReported(auth()->id(), $request->story_id, $request->reason, $request->message));

            toast_success(__('Report sent successfully'));

            return back();
        }
    }

    public function comment(Request $request): RedirectResponse
    {
        // User must be logged in system
        if (auth()->guest()) {
            toast_warning(__('You must be logged in!'));

            return back();
        }

        $request->validate([
            'comment_id' => ['required', 'integer', 'exists:comments,id'],
            'story_id' => ['required', 'integer', 'exists:stories,id'],
            'reason' => ['required', 'string'],
            'message' => ['sometimes', 'max:500'],
        ]);

        // Checks first if a user already sent
        if (ReportedComment::query()->where('user_id', auth()->id())->where('comment_id', $request->comment_id)->first()) {
            toast_warning(__('Your report is being reviewed!'));

            return back();
        } else {
            ReportedComment::create([
                'story_id' => $request->story_id,
                'comment_id' => $request->comment_id,
                'user_id' => auth()->id(),
                'reason' => $request->reason,
                'message' => $request->message,
            ]);

            event(new CommentReported(auth()->id(), $request->comment_id, $request->reason, $request->message));

            toast_success(__('Report sent successfully'));

            return back();
        }
    }

    public function user(Request $request)
    {
        $request->validate([
            'reported_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        // User must be logged in system
        if (auth()->guest()) {
            toast_warning(__('You must be logged in!'));

            return back();
        }

        // Checks first if a user already sent
        if (ReportedUser::query()->where('reporter_id', auth()->id())->where('reported_id', $request->reported_id)->first()) {
            toast_warning(__('Your report is being reviewed!'));

            return back();
        } else {
            ReportedUser::create([
                'reporter_id' => auth()->id(),
                'reported_id' => $request->reported_id,
                'ip_address' => $request->ip(),
                'reason' => $request->reason,
            ]);

            event(new UserReported(auth()->id(), $request->reported_id, $request->reason));

            toast_success(__('Report sent successfully'));

            return back();
        }
    }
}

<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;

class PinStoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $story = Story::find($request->story_id);

        if (! $story) {
            return back();
        }

        if ($story->user_id !== auth()->user()->id) {
            return back();
        }

        if ($story->is_pinned === 0) {
            $story->is_pinned = true;
            $story->pinned_at = now();
            $story->timestamps = false;
            $story->save();
        } else {
            $story->is_pinned = false;
            $story->pinned_at = null;
            $story->timestamps = false;
            $story->save();
        }

        toast_success($story->is_pinned ? __('Story pinned') : __('Story unpinned'));

        return back();
    }
}

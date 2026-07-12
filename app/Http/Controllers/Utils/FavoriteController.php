<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggleFavoriteStory(Request $request)
    {
        $user = User::find($request->user_id);
        $story = Story::find($request->story_id);
        $user->toggleFavorite($story);
    }

    public function favoriteComment(Request $request)
    {
        $user = auth()->user();
        $comment = Comment::find($request->comment_id);
        $user->favorite($comment);

        return response()->json(['success' => true, 'message' => __('Comment added to bookmarks')], 200);
    }

    public function unfavoriteComment(Request $request)
    {
        $user = auth()->user();
        $comment = Comment::find($request->comment_id);
        $user->unfavorite($comment);

        return response()->json(['success' => true, 'message' => __('Comment removed from bookmarks')], 200);
    }
}

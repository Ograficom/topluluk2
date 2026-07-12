<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggleLike(Request $request)
    {
        $user = User::find($request->user_id);
        $story = Story::find($request->story_id);

        $user->toggleLike($story);
    }

    public function toggleLikeComment(Request $request)
    {
        $user = auth()->user();
        $comment = Comment::find($request->comment_id);

        $user->toggleLike($comment);

        $comment->loadCount('likers');

        return response()->json([
            'success' => true,
            'likers_count' => $comment->likers_count,
            'isLikedBy' => $comment->isLikedBy($user),
        ], 200);
    }
}

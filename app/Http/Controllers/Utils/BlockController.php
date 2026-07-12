<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\User;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function toggleBlock(Request $request)
    {
        $user = User::find($request->user);

        $blockingUser = User::find($request->blocking);

        $user->toggleBlock($blockingUser);

        if (Block::where('user_id', $user->id)->where('blocking_id', $blockingUser->id)->exists()) {
            $user->unfollow($blockingUser);
        }
    }

    public function unblock(Request $request)
    {
        $user = User::find($request->user);

        $blockingUser = User::find($request->blocking);

        $user->unblock($blockingUser);
    }
}

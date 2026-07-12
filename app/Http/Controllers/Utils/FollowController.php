<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function toggleFollowUser(Request $request)
    {
        $user = User::find($request->user);

        $following = User::find($request->following);
        $user->toggleFollow($following);
    }

    public function toggleFollowCommunity(Request $request)
    {
        $user = User::find($request->user);

        $following = Community::find($request->following);
        $user->toggleFollow($following);
    }
}

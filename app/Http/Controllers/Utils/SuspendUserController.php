<?php

namespace App\Http\Controllers\Utils;

use App\Events\Suspend\Suspended;
use App\Events\Suspend\Unsuspended;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SuspendUserController extends Controller
{
    public function suspend(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'suspended_until' => ['required', 'integer', 'min:1'],
            'suspended_reason' => ['required'],
        ]);

        $user = User::find($request->user_id);

        $user->update([
            'suspended_until' => Carbon::now()->addDays($request->suspended_until)->toDateTimeString(),
            'suspended_reason' => $request->suspended_reason,
        ]);

        event(new Suspended($user));
        toast_success('@'.$user->username.' '.__('suspended successfully'));
    }

    public function unsuspend(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::find($request->user_id);

        $user->update([
            'suspended_until' => null,
            'suspended_reason' => null,
        ]);

        event(new Unsuspended($user));
        toast_success('@'.$user->username.' '.__('unsuspended successfully'));
    }
}

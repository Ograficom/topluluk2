<?php

namespace App\Listeners\Rating\Awards;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForAuthUserListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Login $event)
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled) {
            $user = User::find($event->user->id);
            $amount = (int) config('points.login');
            $reason = 'login';

            $key = "last_login_date_{$user->id}";

            if (now()->format('Y-m-d') !== cache()->get($key)) {
                cache()->put($key, now()->format('Y-m-d'), now()->addDay());

                $user->addPoints($amount, $reason);
                $user->update(['rating' => $user->currentPoints()]);
            }
        }
    }
}

<?php

namespace App\Listeners\Rating\Awards;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PointsForRegisteredUserListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Registered $event): void
    {
        $isRatingEnabled = (bool) settings()->group('advanced')->get('rating_active');

        if ($isRatingEnabled) {
            $user = User::find($event->user->id);
            $amount = (int) config('points.registration');
            $reason = 'registration';

            $user->addPoints($amount, $reason);
            $user->update(['rating' => $user->currentPoints()]);
        }
    }
}

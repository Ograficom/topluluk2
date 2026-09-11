<?php

namespace App\Providers;

use App\Http\Controllers\SocialLoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LoginSecurityRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web', 'guest'])->group(function (): void {
            Route::get('/auth/device/verify', [SocialLoginController::class, 'showDeviceVerification'])
                ->name('social.device.verify');

            Route::post('/auth/device/verify', [SocialLoginController::class, 'verifyDevice'])
                ->middleware('throttle:10,1')
                ->name('social.device.verify.submit');
        });
    }
}

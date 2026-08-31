<?php

use App\Http\Controllers\DashboardReactionTypeController;
use Illuminate\Support\Facades\Route;

// Verified members can manage site reactions from their dashboard.
Route::middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function (): void {
    Route::get('/dashboard/reactions', [DashboardReactionTypeController::class, 'index'])
        ->name('dashboard.reactions');

    Route::post('/dashboard/reactions', [DashboardReactionTypeController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('dashboard.reactions.store');
});

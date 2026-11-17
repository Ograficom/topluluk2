<?php

use App\Http\Controllers\GoogleDriveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin/google-drive')->name('admin.google-drive.')->group(function (): void {
    Route::get('/', [GoogleDriveController::class, 'index'])->name('index');
    Route::get('/connect', [GoogleDriveController::class, 'redirect'])->name('connect');
    Route::delete('/disconnect', [GoogleDriveController::class, 'disconnect'])->name('disconnect');
});

Route::middleware(['web'])->get('/auth/google-drive/callback', [GoogleDriveController::class, 'callback'])
    ->name('google-drive.callback');

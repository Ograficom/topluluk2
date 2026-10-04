<?php

use App\Http\Controllers\BlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// EditorJS video endpoints are consumed by the authenticated web editor.
// Keep the web/session + CSRF middleware here because the editor uses the user's
// Laravel session and sends X-CSRF-TOKEN with each request.
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/editorjs/video-upload', [BlogController::class, 'editorJsVideo'])
        ->withoutMiddleware('throttle:api')
        ->name('api.blog.editorjs.video');
    Route::post('/editorjs/video-upload/init', [BlogController::class, 'editorJsVideoInit'])
        ->withoutMiddleware('throttle:api')
        ->name('api.blog.editorjs.video.init');
    Route::post('/editorjs/video-upload/chunk', [BlogController::class, 'editorJsVideoChunk'])
        ->withoutMiddleware('throttle:api')
        ->name('api.blog.editorjs.video.chunk');
    Route::post('/editorjs/video-upload/complete', [BlogController::class, 'editorJsVideoComplete'])
        ->withoutMiddleware('throttle:api')
        ->name('api.blog.editorjs.video.complete');
});

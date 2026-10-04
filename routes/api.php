<?php

use App\Http\Controllers\BlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// EditorJS video upload endpoint on API stack (no web/csrf precondition issues)
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

<?php

namespace App\Providers;

use App\Http\Controllers\PostVoteController;
use App\Http\Middleware\PostVoteAssetsMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PostVoteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', PostVoteAssetsMiddleware::class);

        Route::middleware('web')
            ->prefix('blog')
            ->group(function (): void {
                Route::get('/post-votes/summary', [PostVoteController::class, 'summaries'])
                    ->name('blog.post.votes.summary');

                Route::post('/posts/{post:slug}/vote', [PostVoteController::class, 'vote'])
                    ->middleware(['auth', 'throttle:60,1'])
                    ->name('blog.post.vote');
            });
    }
}

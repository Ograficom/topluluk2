<?php

namespace App\Providers;

use App\Http\Controllers\PostVoteController;
use App\Http\Middleware\PostVoteAssetsMiddleware;
use App\Models\Post;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class PostVoteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', PostVoteAssetsMiddleware::class);

        $syncVotePreference = function (Post $post): void {
            if ($this->app->runningInConsole() || ! $this->app->bound('request')) {
                return;
            }

            try {
                if (! Schema::hasColumn('posts', 'votes_enabled')) {
                    return;
                }

                $request = $this->app['request'];
                if (! $request->has('votes_enabled')) {
                    return;
                }

                $post->setAttribute('votes_enabled', $request->boolean('votes_enabled'));
            } catch (\Throwable $exception) {
                // Oylama tercihi opsiyoneldir; bu alan yüzünden post oluşturma/güncelleme
                // işlemi 500'e düşmemeli.
                report($exception);
            }
        };

        Post::creating($syncVotePreference);
        Post::updating($syncVotePreference);

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

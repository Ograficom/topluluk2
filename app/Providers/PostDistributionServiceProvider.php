<?php

namespace App\Providers;

use App\Http\Middleware\PostPresentationMiddleware;
use App\Models\Post;
use App\Observers\PostDistributionObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class PostDistributionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasColumns('posts', [
            'followers_only',
            'is_ai_product',
            'hide_from_feeds',
            'suppress_follower_notifications',
        ])) {
            return;
        }

        Post::addGlobalScope('post_distribution', function (Builder $query): void {
            $viewer = auth()->user();
            $authorColumn = $query->qualifyColumn('author_id');
            $followersOnlyColumn = $query->qualifyColumn('followers_only');

            if (! $viewer?->isAdmin()) {
                if (! $viewer) {
                    $query->where($followersOnlyColumn, false);
                } else {
                    $viewerId = (int) $viewer->id;
                    $query->where(function (Builder $visibility) use ($authorColumn, $followersOnlyColumn, $viewerId): void {
                        $visibility
                            ->where($followersOnlyColumn, false)
                            ->orWhere($authorColumn, $viewerId)
                            ->orWhereExists(function ($followers) use ($authorColumn, $viewerId): void {
                                $followers->selectRaw('1')
                                    ->from('follows as post_distribution_follows')
                                    ->where('post_distribution_follows.follower_id', $viewerId)
                                    ->whereColumn('post_distribution_follows.followed_id', $authorColumn);
                            });
                    });
                }
            }

            $routeName = (string) optional(request()->route())->getName();
            $isProfile = $routeName === 'users.show';
            $isDirectPostRoute = $routeName === 'blog.post' || str_starts_with($routeName, 'blog.post.');

            if (! $isProfile && ! $isDirectPostRoute) {
                $query->where($query->qualifyColumn('hide_from_feeds'), false);
            }
        });

        Post::observe(PostDistributionObserver::class);
        $this->app['router']->pushMiddlewareToGroup('web', PostPresentationMiddleware::class);
    }
}

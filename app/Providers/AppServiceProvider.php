<?php

namespace App\Providers;

use App\Models\Channel;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $viewer = auth()->user();

            $view->with([
                'navigationChannels' => Channel::query()->active()->orderBy('name')->limit(8)->get(),
                'sidebarAuthors' => User::query()
                    ->whereNotNull('username')
                    ->where('username', '<>', '')
                    ->withCount('posts')
                    ->orderByDesc('posts_count')
                    ->limit(5)
                    ->get(),
                'sidebarPosts' => Post::query()
                    ->published()
                    ->when(! $viewer?->show_adult_content, fn ($query) => $query->where('is_adult', false))
                    ->withCount('comments')
                    ->withCount([
                        'reactions as likes_count' => fn ($query) => $query->where('type', PostReaction::LIKE),
                    ])
                    ->orderByDesc('views')
                    ->limit(4)
                    ->get(),
                'sidebarComments' => Comment::query()
                    ->with(['user', 'post'])
                    ->whereHas('post', fn (Builder $query) => $query
                        ->published()
                        ->when(! $viewer?->show_adult_content, fn (Builder $query) => $query->where('is_adult', false))
                        ->when($viewer, fn (Builder $query) => $query
                            ->whereNotIn('user_id', $viewer->blockedUsers()->select('users.id'))
                            ->whereNotIn('user_id', $viewer->blockedByUsers()->select('users.id'))))
                    ->orderByDesc(Post::query()
                        ->selectRaw('count(*)')
                        ->whereColumn('posts.id', 'comments.post_id'))
                    ->latest('comments.created_at')
                    ->limit(5)
                    ->get(),
            ]);
        });
    }
}

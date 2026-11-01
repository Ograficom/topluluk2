<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(): View
    {
        $periods = [
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
        ];
        $postsByPeriod = $this->countByPeriod(Post::class, 'published_at', $periods, true);
        $usersByPeriod = $this->countByPeriod(User::class, 'created_at', $periods);
        $commentsByPeriod = $this->countByPeriod(Comment::class, 'created_at', $periods);
        $totalPosts = Post::query()->count();
        $publishedPosts = Post::query()->published()->count();

        return view('statistics.index', [
            'totalPosts' => $totalPosts,
            'publishedPosts' => $publishedPosts,
            'postsInModeration' => $totalPosts - $publishedPosts,
            'totalComments' => Comment::query()->count(),
            'totalUsers' => User::query()->count(),
            'postsByPeriod' => $postsByPeriod,
            'usersByPeriod' => $usersByPeriod,
            'commentsByPeriod' => $commentsByPeriod,
            'popularTags' => Post::query()
                ->published()
                ->whereNotNull('tags')
                ->pluck('tags')
                ->flatten()
                ->filter(fn ($tag) => is_string($tag) && filled($tag))
                ->unique()
                ->take(20)
                ->values(),
        ]);
    }

    /**
     * @param  class-string<Post|User|Comment>  $model
     * @param  array<string, Carbon>  $periods
     * @return array<string, int>
     */
    private function countByPeriod(string $model, string $column, array $periods, bool $publishedOnly = false): array
    {
        $counts = [];

        foreach ($periods as $period => $start) {
            $query = $model::query()
                ->whereNotNull($column)
                ->where($column, '>=', $start);

            if ($publishedOnly) {
                $query->where(fn (Builder $query) => $query
                    ->where('is_published', true)
                    ->where(fn (Builder $query) => $query
                        ->whereNull('published_at')
                        ->orWhere('published_at', '<=', now())));
            }

            $counts[$period] = $query->count();
        }

        return $counts;
    }
}

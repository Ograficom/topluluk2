<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeFeedInterleavedCardsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->routeIs('home')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html)
            || $html === ''
            || ! str_contains($html, 'home-feed-shell')
            || ! str_contains($html, 'ografi-filterable-post')
            || str_contains($html, 'data-og-feed-insert-assets')
        ) {
            return $response;
        }

        $viewerId = $request->user()?->id;

        $topPosts = Post::query()
            ->published()
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get([
                'id',
                'title',
                'slug',
                'featured_image',
                'views_count',
                'published_at',
                'created_at',
            ]);

        $feedCategories = Category::query()
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->take(5)
            ->get([
                'id',
                'name',
                'slug',
                'profile_image',
            ]);

        $feedSuggestedUsers = User::query()
            ->when($viewerId, fn ($query) => $query->whereKeyNot($viewerId))
            ->when($viewerId, function ($query) use ($viewerId) {
                $query->withExists([
                    'followers as is_followed_by_viewer' => fn ($inner) => $inner->where('users.id', $viewerId),
                ]);
            })
            ->withCount('followers')
            ->orderByDesc('is_verified')
            ->orderByDesc('followers_count')
            ->orderByDesc('id')
            ->take(5)
            ->get([
                'id',
                'name',
                'username',
                'profile_photo_path',
                'is_verified',
                'verification_badge',
                'verification_badge_svg',
            ]);

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 25;
        $feedGlobalStart = (($page - 1) * $perPage) + 1;

        try {
            $assets = view('partials.feed.interleaved-cards', [
                'topPosts' => $topPosts,
                'feedCategories' => $feedCategories,
                'feedSuggestedUsers' => $feedSuggestedUsers,
                'feedGlobalStart' => $feedGlobalStart,
            ])->render();
        } catch (\Throwable $exception) {
            report($exception);

            return $response;
        }

        $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
        $response->setContent($html);
        $response->headers->set('X-Ografi-Feed-Inserts', 'v1');

        return $response;
    }
}

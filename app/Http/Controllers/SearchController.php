<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\SearchSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $settings = SearchSetting::current();
        $query = trim($request->string('q'));

        $results = $this->buildResults($query, $settings);

        $meta = [
            'enabled' => (bool) $settings->is_enabled,
            'min_length' => $settings->min_query_length,
            'query' => $query,
            'too_short' => mb_strlen($query) < $settings->min_query_length,
        ];

        if ($request->expectsJson()) {
            return Response::json([
                'data' => $results,
                'meta' => $meta,
            ]);
        }

        return view('search.index', [
            'results' => $results,
            'query' => $query,
            'meta' => $meta,
        ]);
    }

    private function buildResults(string $query, SearchSetting $settings): array
    {
        $empty = [
            'posts' => [],
            'categories' => [],
            'tags' => [],
            'users' => [],
            'pages' => [],
        ];

        if (!$settings->is_enabled || $query === '' || mb_strlen($query) < $settings->min_query_length) {
            return $empty;
        }

        $limit = max(1, (int) $settings->limit_per_type);
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';

        $results = $empty;

        if ($settings->include_posts) {
            $postsQuery = Post::query()
                ->published()
                ->select(['id', 'title', 'slug', 'excerpt', 'content', 'category_id', 'author_id', 'views_count'])
                ->with([
                    'category:id,name,slug,profile_image',
                    'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                ])
                ->where(function ($q) use ($like, $settings) {
                    $q->where('title', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('meta_title', 'like', $like)
                        ->orWhere('meta_description', 'like', $like)
                        ->orWhere('meta_keywords', 'like', $like);

                    if ($settings->include_post_content) {
                        $q->orWhere('content', 'like', $like);
                    }

                    $q->orWhereHas('category', fn ($cat) => $cat->where('name', 'like', $like))
                        ->orWhereHas('author', fn ($author) => $author
                            ->where('name', 'like', $like)
                            ->orWhere('username', 'like', $like)
                        )
                        ->orWhereHas('tags', fn ($tag) => $tag->where('name', 'like', $like));
                })
                ->limit($limit);

            $results['posts'] = $postsQuery->get()->map(function (Post $post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'snippet' => Str::limit(strip_tags($post->excerpt ?? $post->content), 140),
                    'url' => route('blog.post', $post),
                    'category' => optional($post->category)->name,
                    'author' => optional($post->author)->name,
                    'author_avatar' => optional($post->author)->profile_photo_url,
                    'category_avatar' => optional($post->category)->profile_image_url ?? optional($post->category)->profile_image,
                    'views' => $post->views_count,
                ];
            });
        }

        if ($settings->include_categories) {
            $results['categories'] = Category::query()
                ->select(['id', 'name', 'slug', 'profile_image'])
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                })
                ->limit($limit)
                ->get()
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'title' => $category->name,
                    'url' => route('blog.category', $category),
                    'avatar' => $category->profile_image_url ?? $category->profile_image,
                ]);
        }

        if ($settings->include_tags) {
            $results['tags'] = Tag::query()
                ->select(['id', 'name', 'slug'])
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                })
                ->limit($limit)
                ->get()
                ->map(fn (Tag $tag) => [
                    'id' => $tag->id,
                    'title' => $tag->name,
                    'url' => route('blog.index', ['tag' => $tag->slug]),
                ]);
        }

        if ($settings->include_users) {
            $results['users'] = User::query()
                ->select(['id', 'name', 'username', 'profile_photo_path'])
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('username', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->limit($limit)
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'title' => $user->name,
                    'subtitle' => $user->username ? '@' . $user->username : null,
                    'username' => $user->username,
                    'url' => route('users.show', $user),
                    'avatar' => $user->profile_photo_url,
                ]);
        }

        $results['pages'] = Page::query()
            ->published()
            ->select(['id', 'title', 'slug', 'content'])
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('content', 'like', $like);
            })
            ->limit($limit)
            ->get()
            ->map(fn (Page $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'snippet' => Str::limit(strip_tags($page->content ?? ''), 140),
                'url' => route('pages.show.short', $page->slug),
            ]);

        return $results;
    }
}


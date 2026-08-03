<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Page;
use App\Models\Post;
use App\Models\SearchSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    private const TYPES = ['posts', 'categories', 'tags', 'users', 'comments', 'pages'];

    private const SORTS = ['relevance', 'newest', 'popular'];

    public function __invoke(Request $request)
    {
        $settings = SearchSetting::current();
        $query = trim($request->string('q'));

        $type = $request->string('type')->lower()->value();
        $type = in_array($type, self::TYPES, true) ? $type : 'all';

        $sort = $request->string('sort')->lower()->value();
        $sort = in_array($sort, self::SORTS, true) ? $sort : 'relevance';

        $nsfw = $request->boolean('nsfw');
        $ai = $request->boolean('ai');
        $offset = max(0, (int) $request->integer('offset'));

        $filters = compact('type', 'sort', 'nsfw', 'ai', 'offset');

        [$results, $hasMore] = $this->buildResults($query, $settings, $filters);

        $meta = [
            'enabled' => (bool) $settings->is_enabled,
            'min_length' => $settings->min_query_length,
            'query' => $query,
            'too_short' => $query !== '' && mb_strlen($query) < $settings->min_query_length,
            'type' => $type,
            'sort' => $sort,
            'nsfw' => $nsfw,
            'ai' => $ai,
            'offset' => $offset,
            'has_more' => $hasMore,
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

    /**
     * @return array{0: array<string, array>, 1: bool}
     */
    private function buildResults(string $query, SearchSetting $settings, array $filters): array
    {
        $empty = array_fill_keys(self::TYPES, []);

        if (!$settings->is_enabled || $query === '' || mb_strlen($query) < $settings->min_query_length) {
            return [$empty, false];
        }

        $type = $filters['type'];
        $isSingleType = $type !== 'all';
        $limit = $isSingleType ? 24 : max(1, (int) $settings->limit_per_type);
        $offset = $isSingleType ? $filters['offset'] : 0;

        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';
        $results = $empty;
        $hasMore = false;

        $wants = fn (string $key) => $isSingleType ? $type === $key : true;

        if ($wants('posts') && $settings->include_posts) {
            [$results['posts'], $moreFlag] = $this->fetchPosts($like, $settings, $filters, $limit, $offset);
            $hasMore = $hasMore || ($isSingleType && $moreFlag);
        }

        if ($wants('categories') && $settings->include_categories) {
            [$results['categories'], $moreFlag] = $this->fetchCategories($like, $filters, $limit, $offset);
            $hasMore = $hasMore || ($isSingleType && $moreFlag);
        }

        if ($wants('tags') && $settings->include_tags) {
            [$results['tags'], $moreFlag] = $this->fetchTags($like, $filters, $limit, $offset);
            $hasMore = $hasMore || ($isSingleType && $moreFlag);
        }

        if ($wants('users') && $settings->include_users) {
            [$results['users'], $moreFlag] = $this->fetchUsers($like, $filters, $limit, $offset);
            $hasMore = $hasMore || ($isSingleType && $moreFlag);
        }

        if ($wants('comments')) {
            [$results['comments'], $moreFlag] = $this->fetchComments($like, $filters, $limit, $offset);
            $hasMore = $hasMore || ($isSingleType && $moreFlag);
        }

        if ($wants('pages')) {
            [$results['pages'], $moreFlag] = $this->fetchPages($like, $filters, $limit, $offset);
            $hasMore = $hasMore || ($isSingleType && $moreFlag);
        }

        return [$results, $hasMore];
    }

    /**
     * Fetches limit+1 rows so callers can detect "more available" without a second COUNT query.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: bool}
     */
    private function paginateSlice(Builder $query, int $limit, int $offset): array
    {
        $rows = $query->offset($offset)->limit($limit + 1)->get();
        $hasMore = $rows->count() > $limit;

        return [$rows->take($limit), $hasMore];
    }

    private function fetchPosts(string $like, SearchSetting $settings, array $filters, int $limit, int $offset): array
    {
        $postsQuery = Post::query()
            ->published()
            ->select(['id', 'title', 'slug', 'excerpt', 'content', 'category_id', 'author_id', 'views_count', 'is_nsfw', 'created_at', 'published_at'])
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
            });

        if (!$filters['nsfw']) {
            $postsQuery->where('is_nsfw', false);
        }

        if ($filters['ai']) {
            $postsQuery->aiWritten();
        }

        $this->applySort($postsQuery, $filters['sort'], 'views_count', 'published_at');

        [$rows, $hasMore] = $this->paginateSlice($postsQuery, $limit, $offset);

        $mapped = $rows->map(fn (Post $post) => [
            'id' => $post->id,
            'title' => $post->title,
            'snippet' => Str::limit(strip_tags($post->excerpt ?? $post->content), 140),
            'url' => route('blog.post', $post),
            'category' => optional($post->category)->name,
            'author' => optional($post->author)->name,
            'author_avatar' => optional($post->author)->profile_photo_url,
            'category_avatar' => optional($post->category)->profile_image_url ?? optional($post->category)->profile_image,
            'views' => $post->views_count,
            'is_nsfw' => (bool) $post->is_nsfw,
            'published_at' => optional($post->published_at)->toIso8601String(),
        ]);

        return [$mapped, $hasMore];
    }

    private function fetchCategories(string $like, array $filters, int $limit, int $offset): array
    {
        $categoriesQuery = Category::query()
            ->select(['id', 'name', 'slug', 'profile_image'])
            ->withCount('posts')
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            });

        $this->applySort($categoriesQuery, $filters['sort'], 'posts_count', 'created_at');

        [$rows, $hasMore] = $this->paginateSlice($categoriesQuery, $limit, $offset);

        $mapped = $rows->map(fn (Category $category) => [
            'id' => $category->id,
            'title' => $category->name,
            'url' => route('blog.category', $category),
            'avatar' => $category->profile_image_url ?? $category->profile_image,
            'posts_count' => $category->posts_count,
        ]);

        return [$mapped, $hasMore];
    }

    private function fetchTags(string $like, array $filters, int $limit, int $offset): array
    {
        $tagsQuery = Tag::query()
            ->select(['id', 'name', 'slug'])
            ->withCount('posts')
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            });

        $this->applySort($tagsQuery, $filters['sort'], 'posts_count', 'created_at');

        [$rows, $hasMore] = $this->paginateSlice($tagsQuery, $limit, $offset);

        $mapped = $rows->map(fn (Tag $tag) => [
            'id' => $tag->id,
            'title' => $tag->name,
            'url' => route('blog.index', ['tag' => $tag->slug]),
            'posts_count' => $tag->posts_count,
        ]);

        return [$mapped, $hasMore];
    }

    private function fetchUsers(string $like, array $filters, int $limit, int $offset): array
    {
        $usersQuery = User::query()
            ->select(['id', 'name', 'username', 'profile_photo_path', 'created_at'])
            ->withCount('followers')
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('username', 'like', $like);
            });

        $this->applySort($usersQuery, $filters['sort'], 'followers_count', 'created_at');

        [$rows, $hasMore] = $this->paginateSlice($usersQuery, $limit, $offset);

        $mapped = $rows->map(fn (User $user) => [
            'id' => $user->id,
            'title' => $user->name,
            'subtitle' => $user->username ? '@' . $user->username : null,
            'username' => $user->username,
            'url' => route('users.show', $user),
            'avatar' => $user->profile_photo_url,
            'followers_count' => $user->followers_count,
        ]);

        return [$mapped, $hasMore];
    }

    private function fetchComments(string $like, array $filters, int $limit, int $offset): array
    {
        $commentsQuery = Comment::query()
            ->approved()
            ->select(['id', 'post_id', 'user_id', 'author_name', 'content', 'created_at'])
            ->with([
                'post:id,title,slug,is_nsfw',
                'user:id,name,username,profile_photo_path',
            ])
            ->whereHas('post', function ($q) use ($filters) {
                $q->published();
                if (!$filters['nsfw']) {
                    $q->where('is_nsfw', false);
                }
            })
            ->where('content', 'like', $like);

        $this->applySort($commentsQuery, $filters['sort'], null, 'created_at');

        [$rows, $hasMore] = $this->paginateSlice($commentsQuery, $limit, $offset);

        $mapped = $rows->filter(fn (Comment $comment) => $comment->post !== null)->map(fn (Comment $comment) => [
            'id' => $comment->id,
            'snippet' => Str::limit(strip_tags((string) $comment->content), 160),
            'author' => optional($comment->user)->name ?? $comment->author_name,
            'author_avatar' => optional($comment->user)->profile_photo_url,
            'post_title' => optional($comment->post)->title,
            'url' => $comment->post ? route('blog.post', $comment->post) . '#comment-' . $comment->id : null,
        ])->values();

        return [$mapped, $hasMore];
    }

    private function fetchPages(string $like, array $filters, int $limit, int $offset): array
    {
        $pagesQuery = Page::query()
            ->published()
            ->select(['id', 'title', 'slug', 'content', 'created_at'])
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('content', 'like', $like);
            });

        $this->applySort($pagesQuery, $filters['sort'], null, 'created_at');

        [$rows, $hasMore] = $this->paginateSlice($pagesQuery, $limit, $offset);

        $mapped = $rows->map(fn (Page $page) => [
            'id' => $page->id,
            'title' => $page->title,
            'snippet' => Str::limit(strip_tags($page->content ?? ''), 140),
            'url' => route('pages.show.short', $page->slug),
        ]);

        return [$mapped, $hasMore];
    }

    private function applySort(Builder $query, string $sort, ?string $popularColumn, string $newestColumn): void
    {
        if ($sort === 'newest') {
            $query->orderByDesc($newestColumn);

            return;
        }

        if ($sort === 'popular' && $popularColumn !== null) {
            $query->orderByDesc($popularColumn);

            return;
        }

        // 'relevance' (default): no explicit order, natural match order.
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\ReactionType;
use App\Models\Category;
use App\Models\Tag;
use App\Models\PostView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\RecaptchaSetting;
use App\Services\BadgePointService;
use App\Services\CommentModerationService;
use App\Services\MentionService;
use App\Services\RecaptchaV3Verifier;
use App\Services\PostCommentPreviewService;
use App\Services\PostLinkPreviewService;
use App\Services\SitemapManager;
use App\Notifications\PostCommentedNotification;
use App\Notifications\PostReactedNotification;
use App\Notifications\PostRepostedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function __construct(
        protected BadgePointService $badgePointService,
        protected CommentModerationService $commentModerationService,
        protected MentionService $mentionService,
    ) {
    }

    public function createCategory(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->isBlockedFrom('categories')) {
            return redirect()->route('blog.categories')
                ->with('status', 'Kategori islemleri yetkiniz kisitlandi.');
        }

        return view('blog.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->isBlockedFrom('categories')) {
            return redirect()->route('blog.categories')
                ->with('status', 'Kategori islemleri yetkiniz kisitlandi.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories', 'slug')],
            'description' => ['nullable', 'string', 'max:5000'],
            'profile_image' => ['nullable', 'image', 'max:10240'],
            'cover_image' => ['nullable', 'image', 'max:15360'],
        ]);

        $baseSlug = Str::slug((string) ($data['slug'] ?? $data['name']));
        if ($baseSlug === '') {
            $baseSlug = 'kategori';
        }

        $slug = $baseSlug;
        $counter = 2;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $category = Category::create([
            'created_by_user_id' => $user->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        $updates = [];
        if ($request->hasFile('profile_image')) {
            $updates['profile_image'] = $request->file('profile_image')->store('categories/profile', 'public');
        }
        if ($request->hasFile('cover_image')) {
            $updates['cover_image'] = $request->file('cover_image')->store('categories/cover', 'public');
        }
        if (!empty($updates)) {
            $category->update($updates);
        }

        $this->badgePointService->award($user, 'category_created');

        return redirect()
            ->route('blog.category', ['category' => $category->slug])
            ->with('status', 'Kategori olusturuldu.');
    }

    public function toggleCategoryJoin(Request $request, Category $category)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $existing = $category->followers()->whereKey($user->id)->exists();
        if ($existing) {
            $category->followers()->detach($user->id);
            $joined = false;
        } else {
            $category->followers()->attach($user->id);
            $joined = true;
        }

        if ($request->expectsJson()) {
            return Response::json([
                'joined' => $joined,
                'followers_count' => $category->followers()->count(),
            ]);
        }

        return back();
    }

    public function editCategory(Request $request, Category $category)
    {
        $user = $request->user();
        abort_unless($user, 403);
        if ($user->isBlockedFrom('categories')) {
            return redirect()->route('blog.categories')
                ->with('status', 'Kategori islemleri yetkiniz kisitlandi.');
        }
        if (empty($category->created_by_user_id)) {
            $category->forceFill(['created_by_user_id' => $user->id])->save();
        }
        abort_unless((int) $category->created_by_user_id === (int) $user->id, 403);

        return view('blog.categories.edit', [
            'category' => $category,
        ]);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $user = $request->user();
        abort_unless($user, 403);
        if ($user->isBlockedFrom('categories')) {
            return redirect()->route('blog.categories')
                ->with('status', 'Kategori islemleri yetkiniz kisitlandi.');
        }
        if (empty($category->created_by_user_id)) {
            $category->forceFill(['created_by_user_id' => $user->id])->save();
        }
        abort_unless((int) $category->created_by_user_id === (int) $user->id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'profile_image' => ['nullable', 'image', 'max:10240'],
            'cover_image' => ['nullable', 'image', 'max:15360'],
        ]);

        $updates = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ];

        $deleteIfLocal = function (?string $path): void {
            $path = trim((string) $path);
            if ($path === '') {
                return;
            }
            if (Str::startsWith($path, ['http://', 'https://', '//', '/storage/', 'storage/'])) {
                return;
            }
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        };

        if ($request->hasFile('profile_image')) {
            $deleteIfLocal($category->profile_image);
            $updates['profile_image'] = $request->file('profile_image')->store('categories/profile', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $deleteIfLocal($category->cover_image);
            $updates['cover_image'] = $request->file('cover_image')->store('categories/cover', 'public');
        }

        $category->update($updates);

        return redirect()
            ->route('blog.category', $category)
            ->with('status', 'Kategori güncellendi.');
    }

    public function destroyCategory(Request $request, Category $category)
    {
        $user = $request->user();
        abort_unless($user, 403);
        if ($user->isBlockedFrom('categories')) {
            return redirect()->route('blog.categories')
                ->with('status', 'Kategori islemleri yetkiniz kisitlandi.');
        }
        if (empty($category->created_by_user_id)) {
            $category->forceFill(['created_by_user_id' => $user->id])->save();
        }
        abort_unless((int) $category->created_by_user_id === (int) $user->id, 403);

        $category->delete();

        return redirect()
            ->route('blog.categories')
            ->with('status', 'Kategori silindi.');
    }

    public function giphySearch(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 18);
        $limit = max(1, min(30, $limit));

        if ($query === '') {
            return Response::json([
                'data' => [],
            ]);
        }

        $apiKey = (string) config('services.giphy.api_key');
        if ($apiKey === '') {
            return Response::json([
                'message' => 'GIPHY_API_KEY tanimli degil.',
                'data' => [],
            ], 422);
        }

        try {
            $response = Http::timeout(6)->get('https://api.giphy.com/v1/gifs/search', [
                'api_key' => $apiKey,
                'q' => $query,
                'limit' => $limit,
                'lang' => (string) config('services.giphy.lang', 'tr'),
                'rating' => (string) config('services.giphy.rating', 'pg-13'),
            ]);

            if (!$response->successful()) {
                return Response::json([
                    'message' => 'Giphy servisine ulasilamadi.',
                    'data' => [],
                ], 502);
            }

            $json = $response->json();
            $items = collect($json['data'] ?? [])
                ->map(function (array $item) {
                    $images = (array) ($item['images'] ?? []);
                    $fixed = (array) ($images['fixed_width'] ?? []);
                    $original = (array) ($images['original'] ?? []);

                    return [
                        'id' => (string) ($item['id'] ?? ''),
                        'title' => (string) ($item['title'] ?? ''),
                        'preview' => (string) ($fixed['url'] ?? $original['url'] ?? ''),
                        'url' => (string) ($original['url'] ?? $fixed['url'] ?? ''),
                        'width' => (int) ($original['width'] ?? 0),
                        'height' => (int) ($original['height'] ?? 0),
                    ];
                })
                ->filter(fn (array $item) => $item['url'] !== '' && $item['preview'] !== '')
                ->values()
                ->all();

            return Response::json([
                'data' => $items,
            ]);
        } catch (\Throwable $e) {
            return Response::json([
                'message' => 'Giphy servisine ulasilamadi.',
                'data' => [],
            ], 502);
        }
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $baseQuery = Post::query()
            ->published()
            ->with([
                'category:id,name,slug,profile_image,cover_image',
                'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                'tags:id,name,slug',
                'latestComment.user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            ])
            ->withCount(['comments', 'reactions']);

        if ($userId) {
            $baseQuery->withExists([
                'bookmarkers as is_bookmarked' => fn ($query) => $query->where('users.id', $userId),
            ]);
        }

        $filteredQuery = (clone $baseQuery)
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
            })
            ->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tags', fn ($q) => $q->where('slug', $request->string('tag')));
            });

        $posts = (clone $filteredQuery)
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->orderByDesc('published_at')
            ->paginate(25)
            ->withQueryString();

        $postsCollection = $posts->getCollection();

        $popularPosts = (clone $filteredQuery)
            ->orderByDesc('is_pinned')
            ->orderByDesc('reactions_count')
            ->orderByDesc('comments_count')
            ->orderByDesc('updated_at')
            ->orderByDesc('published_at')
            ->take(24)
            ->get();

        if ($request->expectsJson()) {
            return Response::json([
                'posts' => $posts,
                'popular_posts' => $popularPosts,
            ]);
        }

        $categories = Category::withCount('posts')->orderBy('name')->get();
        $tags = Tag::withCount('posts')->orderBy('name')->get();
        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        // Attach reaction summary counts (type -> count) for list view
        $reactionCountsByPost = \App\Models\Reaction::query()
            ->whereIn('post_id', $postsCollection->pluck('id'))
            ->whereNotNull('reaction_type_id')
            ->selectRaw('post_id, reaction_type_id, count(*) as cnt')
            ->groupBy('post_id', 'reaction_type_id')
            ->get()
            ->groupBy('post_id');

        $postsCollection->transform(function ($post) use ($reactionCountsByPost, $reactionTypes, $userId) {
            $post->reaction_counts = ($reactionCountsByPost[$post->id] ?? collect())
                ->mapWithKeys(fn ($row) => [$row->reaction_type_id => (int) $row->cnt]);
            $post->setRelation('reactionTypes', $reactionTypes);
            if (!$userId) {
                $post->setAttribute('is_bookmarked', false);
            }
            return $post;
        });

        $posts->setCollection($postsCollection);

        if (!$userId) {
            $popularPosts->each->setAttribute('is_bookmarked', false);
        }

        app(PostCommentPreviewService::class)->attachToPosts($postsCollection);
        app(PostCommentPreviewService::class)->attachToPosts($popularPosts);
        app(PostLinkPreviewService::class)->attachToPosts($postsCollection);
        app(PostLinkPreviewService::class)->attachToPosts($popularPosts);

        $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
        $popularComments = Comment::with(['user', 'post'])->whereNull('parent_id')->orderByDesc('id')->take(10)->get();

        return view('blog.index', [
            'posts' => $posts,
            'popularPosts' => $popularPosts,
            'categories' => $categories,
            'tags' => $tags,
            'reactionTypes' => $reactionTypes,
            'activeCategory' => $request->string('category')->toString(),
            'activeTag' => $request->string('tag')->toString(),
            'popularTags' => $popularTags,
            'popularComments' => $popularComments,
        ]);
    }

    public function popular(Request $request)
    {
        $userId = Auth::id();

        $postsQuery = Post::query()
            ->published()
            ->with([
                'category:id,name,slug,profile_image,cover_image',
                'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                'latestComment.user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            ])
            ->withCount(['comments', 'reactions'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('views_count')
            ->orderByDesc('published_at');

        if ($userId) {
            $postsQuery->withExists([
                'bookmarkers as is_bookmarked' => fn ($query) => $query->where('users.id', $userId),
            ]);
        }

        $posts = $postsQuery
            ->paginate(25)
            ->withQueryString();

        $postsCollection = $posts->getCollection();

        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        $reactionCountsByPost = \App\Models\Reaction::query()
            ->whereIn('post_id', $postsCollection->pluck('id'))
            ->whereNotNull('reaction_type_id')
            ->selectRaw('post_id, reaction_type_id, count(*) as cnt')
            ->groupBy('post_id', 'reaction_type_id')
            ->get()
            ->groupBy('post_id');

        $postsCollection->transform(function ($post) use ($reactionCountsByPost, $reactionTypes, $userId) {
            $post->reaction_counts = ($reactionCountsByPost[$post->id] ?? collect())
                ->mapWithKeys(fn ($row) => [$row->reaction_type_id => (int) $row->cnt]);
            $post->setRelation('reactionTypes', $reactionTypes);
            if (!$userId) {
                $post->setAttribute('is_bookmarked', false);
            }
            return $post;
        });

        $posts->setCollection($postsCollection);

        app(PostCommentPreviewService::class)->attachToPosts($postsCollection);
        app(PostLinkPreviewService::class)->attachToPosts($postsCollection);

        $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
        $popularComments = Comment::with(['user', 'post'])->whereNull('parent_id')->orderByDesc('id')->take(10)->get();

        return view('blog.filtre.popular', [
            'posts' => $posts,
            'reactionTypes' => $reactionTypes,
            'popularTags' => $popularTags,
            'popularComments' => $popularComments,
        ]);
    }

    public function bookmarks(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $posts = $user->bookmarks()
            ->with([
                'category:id,name,slug,profile_image,cover_image',
                'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                'latestComment.user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            ])
            ->withCount(['comments', 'reactions'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $postsCollection = $posts->getCollection();

        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        $reactionCountsByPost = \App\Models\Reaction::query()
            ->whereIn('post_id', $postsCollection->pluck('id'))
            ->whereNotNull('reaction_type_id')
            ->selectRaw('post_id, reaction_type_id, count(*) as cnt')
            ->groupBy('post_id', 'reaction_type_id')
            ->get()
            ->groupBy('post_id');

        $postsCollection->each(function ($post) use ($reactionCountsByPost, $reactionTypes) {
            $post->reaction_counts = ($reactionCountsByPost[$post->id] ?? collect())
                ->mapWithKeys(fn ($row) => [$row->reaction_type_id => (int) $row->cnt]);
            $post->setRelation('reactionTypes', $reactionTypes);
            $post->setAttribute('is_bookmarked', true);
        });

        $posts->setCollection($postsCollection);

        app(PostCommentPreviewService::class)->attachToPosts($postsCollection);
        app(PostLinkPreviewService::class)->attachToPosts($postsCollection);

        $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
        $popularComments = Comment::with(['user', 'post'])->whereNull('parent_id')->orderByDesc('id')->take(10)->get();

        return view('blog.bookmarks', [
            'posts' => $posts,
            'reactionTypes' => $reactionTypes,
            'popularTags' => $popularTags,
            'popularComments' => $popularComments,
        ]);
    }

    public function show(Request $request, Post $post)
    {
        abort_unless($this->isVisible($post), 404);

        $this->recordUniqueView($request, $post);

        $userId = Auth::id();
        if ($userId) {
            $post->loadExists([
                'bookmarkers as is_bookmarked' => fn ($query) => $query->where('users.id', $userId),
            ]);
        } else {
            $post->setAttribute('is_bookmarked', false);
        }

        $post->load([
            'category:id,name,slug,profile_image,cover_image',
            'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            'tags:id,name,slug',
        ]);

        $comments = Comment::query()
            ->where('post_id', $post->id)
            ->approved()
            ->with([
                'user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                'parent:id,user_id,author_name,content',
                'parent.user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            ])
            ->withCount([
                'reactions as likes_count' => fn (Builder $q) => $q->where('is_like', true),
                'reactions as dislikes_count' => fn (Builder $q) => $q->where('is_like', false),
            ])
            ->orderBy('created_at')
            ->get();
        $post->setRelation('comments', $comments);

        $reactionSummary = $this->buildReactionSummary($post);
        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        $pollBlocks = $this->buildPollBlocks($request, $post);

        if ($request->expectsJson()) {
            return Response::json([
                'post' => $post,
                'reaction_counts' => $reactionSummary,
            ]);
        }

        $categories = Category::withCount('posts')->orderBy('name')->get();
        $tags = Tag::withCount('posts')->orderBy('name')->get();
        
        $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
        $popularComments = Comment::with('user', 'post')->whereNull('parent_id')->orderByDesc('id')->take(10)->get();
        $recommendedPosts = Post::query()
            ->published()
            ->whereKeyNot($post->id)
            ->when($post->category_id, function (Builder $query) use ($post) {
                $query->where('category_id', $post->category_id);
            })
            ->with([
                'category:id,name,slug',
                'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            ])
            ->withCount(['comments', 'reactions'])
            ->latest('published_at')
            ->limit(4)
            ->get();

        $recommendedReactionCounts = \App\Models\Reaction::query()
            ->whereIn('post_id', $recommendedPosts->pluck('id'))
            ->whereNotNull('reaction_type_id')
            ->selectRaw('post_id, reaction_type_id, count(*) as cnt')
            ->groupBy('post_id', 'reaction_type_id')
            ->get()
            ->groupBy('post_id');

        $recommendedPosts->transform(function ($recommendedPost) use ($recommendedReactionCounts, $reactionTypes, $request) {
            $recommendedPost->reaction_counts = ($recommendedReactionCounts[$recommendedPost->id] ?? collect())
                ->mapWithKeys(fn ($row) => [$row->reaction_type_id => (int) $row->cnt]);
            $recommendedPost->setRelation('reactionTypes', $reactionTypes);

            if (!$request->user()) {
                $recommendedPost->setAttribute('is_bookmarked', false);
            }

            return $recommendedPost;
        });

        app(PostCommentPreviewService::class)->attachToPosts($recommendedPosts);
        app(PostLinkPreviewService::class)->attachToPost($post);
        app(PostLinkPreviewService::class)->attachToPosts($recommendedPosts);

        $post->content = $this->renderSocialEmbedsInHtml((string) ($post->content ?? ''));

        return view('blog.show', [
            'post' => $post,
            'reactionSummary' => $reactionSummary,
            'categories' => $categories,
            'tags' => $tags,
            'reactionTypes' => $reactionTypes,
            'isBookmarked' => (bool) $post->getAttribute('is_bookmarked'),
            'popularTags' => $popularTags,
            'popularComments' => $popularComments,
            'pollBlocks' => $pollBlocks,
            'recommendedPosts' => $recommendedPosts,
        ]);
    }

    public function recordView(Request $request, Post $post)
    {
        abort_unless($this->isVisible($post), 404);

        $created = $this->recordUniqueView($request, $post);
        $post->refresh();

        return Response::json([
            'count' => (int) ($post->views_count ?? 0),
            'created' => $created,
        ]);
    }

    public function viewers(Request $request, Post $post)
    {
        abort_unless($this->isVisible($post), 404);
        abort_unless(Auth::check(), 403);

        $limit = (int) $request->query('limit', 10);
        $limit = $limit > 0 ? min($limit, 30) : 10;

        $views = PostView::query()
            ->where('post_id', $post->id)
            ->whereNotNull('user_id')
            ->with('user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg')
            ->latest('id')
            ->take($limit)
            ->get();

        $users = $views->map(function ($view) {
            $user = $view->user;
            return [
                'id' => $user?->id,
                'name' => $user?->name,
                'username' => $user?->username,
                'avatar' => $user?->profile_photo_url ?? null,
            ];
        })->filter(fn ($row) => !empty($row['id']))->values();

        return Response::json([
            'count' => (int) ($post->views_count ?? 0),
            'users' => $users,
        ]);
    }

    private function recordUniqueView(Request $request, Post $post): bool
    {
        $userId = Auth::id();
        $ip = $request->ip();
        $viewKey = $userId ? ('u:' . $userId) : ('ip:' . ($ip ?: 'unknown'));

        $exists = PostView::query()
            ->where('post_id', $post->id)
            ->where('view_key', $viewKey)
            ->exists();

        if ($exists) {
            return false;
        }

        PostView::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'ip_address' => $userId ? null : $ip,
            'view_key' => $viewKey,
        ]);

        $post->increment('views_count');

        return true;
    }

    public function storeComment(Request $request, Post $post)
    {
        abort_unless($this->isVisible($post), 404);

        if (!Auth::check()) {
            abort(403);
        }

        $user = Auth::user();
        if ($user && $user->isBlockedFrom('comments')) {
            return back()->withErrors(['content' => 'Yorum yapma yetkiniz kisitlandi.']);
        }

        if ((bool) $post->comments_disabled) {
            return back()->withErrors(['content' => 'Bu gonderide yorumlar kapatildi.']);
        }

        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'content' => ['nullable', 'required_without:image', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'recaptcha_token' => ['nullable', 'string'],
        ]);

        $recaptcha = RecaptchaSetting::currentOrNull();
        if ($recaptcha && $recaptcha->isEnabledFor('comment')) {
            $token = (string) ($data['recaptcha_token'] ?? '');
            if ($token === '') {
                throw ValidationException::withMessages([
                    'recaptcha_token' => 'reCAPTCHA dogrulamasi gerekli.',
                ]);
            }

            $result = app(RecaptchaV3Verifier::class)->verify($token, 'comment', $request->ip());
            if (!($result['success'] ?? false)) {
                throw ValidationException::withMessages([
                    'recaptcha_token' => 'reCAPTCHA dogrulamasi basarisiz.',
                ]);
            }
        }

        $image = $data['image'] ?? null;
        unset($data['image'], $data['recaptcha_token']);

        $data['content'] = $this->commentModerationService->censor((string) ($data['content'] ?? ''));
        if ($image) {
            $imagePath = $image->store('comment-images', 'public');
            $data['content'] = trim($data['content'] . "\n[img:" . $imagePath . ']');
        }

        $data['user_id'] = Auth::id();
        $data['author_name'] = Auth::user()?->name;
        $data['author_email'] = Auth::user()?->email;

        if (!empty($data['parent_id'])) {
            $parent = Comment::query()->whereKey($data['parent_id'])->first();
            if (!$parent || (int) $parent->post_id !== (int) $post->id) {
                return back()->withErrors(['parent_id' => 'Yanıtlanacak yorum bulunamadı.']);
            }
            $data['parent_id'] = $parent->id;
        } else {
            $data['parent_id'] = null;
        }

        $comment = $post->comments()->create($data);
        $this->badgePointService->award($user, 'comment_created');

        $author = $post->author;
        if ($author && $author->id !== $user->id) {
            $author->notify(new PostCommentedNotification($comment));
        }

        $this->mentionService->notifyCommentMentions($comment);

        if ($request->expectsJson()) {
            return Response::json($comment, 201);
        }

        return back()->with('status', 'Yorumunuz alındı.');
    }

    public function reactions(Post $post): JsonResponse
    {
        abort_unless($this->isVisible($post), 404);

        return Response::json($this->buildReactionSummary($post));
    }

    public function storeReaction(Request $request, Post $post)
    {
        abort_unless($this->isVisible($post), 404);

        $user = $request->user();
        if ($user && $user->isBlockedFrom('reactions')) {
            if ($request->expectsJson()) {
                return Response::json(['message' => 'Tepki verme yetkiniz kisitlandi.'], 403);
            }

            return back()->withErrors(['reaction' => 'Tepki verme yetkiniz kisitlandi.']);
        }

        $data = $request->validate([
            'short_code' => ['nullable', 'string', 'max:50'],
            'reaction_type_id' => ['nullable', 'integer', 'exists:reaction_types,id'],
        ]);

        if (empty($data['short_code']) && empty($data['reaction_type_id'])) {
            return back()->withErrors(['reaction' => 'Tepki tipi bulunamadı.']);
        }

        $type = ReactionType::query()
            ->when(!empty($data['reaction_type_id']), fn ($q) => $q->where('id', $data['reaction_type_id']))
            ->when(empty($data['reaction_type_id']) && !empty($data['short_code']), fn ($q) => $q->where('short_code', $data['short_code']))
            ->where('is_active', true)
            ->firstOrFail();

        $fingerprint = (string) ($request->header('X-Reaction-Fingerprint') ?? ($request->ip() . '|' . $request->userAgent()));
        $fingerprintHash = $fingerprint ? sha1($fingerprint) : null;

        $userId = $request->user()?->id;

        // Bir kullanici/fingerprint ayni gonderiye tek tepki biraksin: ayni tipi secerse sil, farkli secerse guncelle.
        $existing = $post->reactions()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn ($q) => $q->whereNull('user_id')->where('fingerprint', $fingerprintHash))
            ->latest('id')
            ->first();

        // Hedef tipi icin varsa diger kayitlari sil; unique constraint ihlalini onler
        $post->reactions()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn ($q) => $q->whereNull('user_id')->where('fingerprint', $fingerprintHash))
            ->where('reaction_type_id', $type->id)
            ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
            ->delete();

        if ($existing && $existing->reaction_type_id === $type->id) {
            $existing->delete();
            if ($request->expectsJson()) {
                return Response::json(['removed' => true], 200);
            }
            return back()->with('status', 'Tepkin kaldirildi.');
        }

        $shouldNotify = (bool) $userId
            && (int) $post->author_id !== (int) $userId
            && (!$existing || (int) $existing->reaction_type_id !== (int) $type->id);

        if ($existing) {
            $existing->update([
                'reaction_type_id' => $type->id,
                'fingerprint' => $userId ? null : $fingerprintHash,
            ]);
            $reaction = $existing;
        } else {
            $reaction = $post->reactions()->create([
                'reaction_type_id' => $type->id,
                'user_id' => $userId,
                'fingerprint' => $userId ? null : $fingerprintHash,
            ]);
        }

        if ($shouldNotify) {
            $author = $post->author;
            if ($author) {
                $author->notify(new PostReactedNotification($reaction));
            }
        }

        if ($request->expectsJson()) {
            return Response::json($reaction->load('type'), 201);
        }

        return back()->with('status', 'Tepkin kaydedildi.');
    }

    public function votePoll(Request $request, Post $post): JsonResponse
    {
        abort_unless($this->isVisible($post), 404);

        $data = $request->validate([
            'block_id' => ['required', 'string', 'max:64'],
            'option_index' => ['required', 'integer', 'min:0'],
        ]);

        $poll = $this->findPollBlock($post, $data['block_id']);
        if (!$poll) {
            return Response::json(['message' => 'Anket bulunamadi.'], 404);
        }

        $options = $poll['options'];
        if (!array_key_exists($data['option_index'], $options)) {
            return Response::json(['message' => 'Secenek bulunamadi.'], 422);
        }

        if ($this->isPollExpired($post, $poll['duration_minutes'])) {
            return Response::json(['message' => 'Anket suresi doldu.'], 422);
        }

        $userId = $request->user()?->id;
        $deviceId = (string) ($request->cookie('device_id') ?? '');

        if (!$userId && $deviceId === '') {
            return Response::json(['message' => 'Cihaz kimligi bulunamadi.'], 403);
        }

        $query = DB::table('post_poll_votes')
            ->where('post_id', $post->id)
            ->where('block_id', $data['block_id']);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('device_id', $deviceId);
        }

        $existing = $query->first();

        if ($existing) {
            if ((int) $existing->option_index !== (int) $data['option_index']) {
                DB::table('post_poll_votes')
                    ->where('id', $existing->id)
                    ->update([
                        'option_index' => (int) $data['option_index'],
                        'updated_at' => now(),
                    ]);
            }
        } else {
            DB::table('post_poll_votes')->insert([
                'post_id' => $post->id,
                'block_id' => $data['block_id'],
                'option_index' => (int) $data['option_index'],
                'user_id' => $userId,
                'device_id' => $userId ? null : $deviceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $counts = DB::table('post_poll_votes')
            ->where('post_id', $post->id)
            ->where('block_id', $data['block_id'])
            ->selectRaw('option_index, count(*) as cnt')
            ->groupBy('option_index')
            ->pluck('cnt', 'option_index');

        $total = (int) $counts->sum();
        $percentages = [];
        foreach ($options as $idx => $label) {
            $count = (int) ($counts[$idx] ?? 0);
            $percentages[$idx] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        }

        return Response::json([
            'total' => $total,
            'counts' => $counts,
            'percentages' => $percentages,
        ]);
    }

    public function toggleBookmark(Request $request, Post $post)
    {
        abort_unless($this->isVisible($post), 404);

        $user = $request->user();
        $exists = $user->bookmarks()->where('post_id', $post->id)->exists();

        if ($exists) {
            $user->bookmarks()->detach($post->id);
            $message = 'Kaydedilenlerden cikarildi.';
            $status = 'removed';
        } else {
            $user->bookmarks()->attach($post->id);
            $message = 'Kaydedildi.';
            $status = 'added';
        }

        if ($request->expectsJson()) {
            return Response::json([
                'bookmarked' => !$exists,
                'status' => $status,
            ]);
        }

        return back()->with('status', $message);
    }

    public function togglePin(Request $request, Post $post)
    {
        abort_unless($this->isVisible($post), 404);

        $user = $request->user();
        abort_unless($user, 403);
        abort_unless((int) $post->author_id === (int) $user->id, 403);

        $wasPublishedBeforeUpdate = $post->isPublishedNow();

        $post->update([
            'is_pinned' => !$post->is_pinned,
        ]);

        return back()->with('status', $post->is_pinned ? 'Gonderi sabitlendi.' : 'Gonderi sabitten kaldirildi.');
    }

    public function destroy(Post $post)
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isVisible($post), 404);
        abort_unless(Auth::id() === $post->author_id, 403);

        $user = Auth::user();
        $post->delete();
        if ($user) {
            $this->badgePointService->award($user, 'post_deleted');
        }

        return redirect()->route('blog.index')->with('status', 'Yazi silindi.');
    }

    private function buildReactionSummary(Post $post): array
    {
        $types = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        $counts = $post->reactions()
            ->selectRaw('reaction_type_id, count(*) as count')
            ->groupBy('reaction_type_id')
            ->pluck('count', 'reaction_type_id');

        return $types->map(fn ($type) => [
            'short_code' => $type->short_code,
            'label' => $type->label,
            'emoji' => $type->emoji,
            'gif_url' => $type->gif_url,
            'count' => (int) ($counts[$type->id] ?? 0),
        ])->values()->all();
    }

    public function categories(Request $request)
    {
        $viewer = $request->user();
        $sort = in_array((string) $request->query('sort', 'relevance'), ['relevance', 'time', 'members'], true)
            ? (string) $request->query('sort', 'relevance')
            : 'relevance';

        $categories = Category::query()
            ->withCount(['posts', 'followers'])
            ->when($viewer, function ($query) use ($viewer) {
                $query->withExists([
                    'followers as is_joined' => fn ($inner) => $inner->where('users.id', $viewer->id),
                ]);
            })
            ->when($sort === 'members', fn ($query) => $query->orderByDesc('followers_count')->orderBy('name'))
            ->when($sort === 'time', fn ($query) => $query->latest())
            ->when($sort === 'relevance', fn ($query) => $query->orderByDesc('posts_count')->orderByDesc('followers_count')->orderBy('name'))
            ->paginate(20);

        if ($request->expectsJson()) {
            return Response::json($categories);
        }

        $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
        $popularComments = Comment::with(['user', 'post'])->whereNull('parent_id')->orderByDesc('id')->take(10)->get();

        return view('blog.categories', [
            'categories' => $categories,
            'popularTags' => $popularTags,
            'popularComments' => $popularComments,
            'sort' => $sort,
        ]);
    }

    public function category(Request $request, Category $category)
    {
        $userId = Auth::id();

        $categoryPostsCount = Post::query()
            ->published()
            ->whereBelongsTo($category, 'category')
            ->count();

        $categoryViews = Post::query()
            ->published()
            ->whereBelongsTo($category, 'category')
            ->sum('views_count');

        $followersCount = $category->followers()->count();
        $isCategoryJoined = $userId ? $category->followers()->whereKey($userId)->exists() : false;

        $baseQuery = Post::query()
            ->published()
            ->whereBelongsTo($category, 'category')
            ->with([
                'category:id,name,slug,profile_image,cover_image',
                'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                'tags:id,name,slug',
                'latestComment.user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            ])
            ->withCount(['comments', 'reactions']);

        if ($userId) {
            $baseQuery->withExists([
                'bookmarkers as is_bookmarked' => fn ($query) => $query->where('users.id', $userId),
            ]);
        }

        $filteredQuery = (clone $baseQuery)
            ->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tags', fn ($q) => $q->where('slug', $request->string('tag')));
            });

        $posts = (clone $filteredQuery)
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate(25)
            ->withQueryString();

        $postsCollection = $posts->getCollection();

        $popularPosts = (clone $filteredQuery)
            ->orderByDesc('is_pinned')
            ->orderByDesc('reactions_count')
            ->orderByDesc('comments_count')
            ->orderByDesc('published_at')
            ->take(24)
            ->get();

        if ($request->expectsJson()) {
            return Response::json([
                'posts' => $posts,
                'popular_posts' => $popularPosts,
                'category' => $category,
            ]);
        }

        $categories = Category::withCount('posts')->orderBy('name')->get();
        $tags = Tag::withCount('posts')->orderBy('name')->get();
        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        if (!$userId) {
            $postsCollection->each->setAttribute('is_bookmarked', false);
            $popularPosts->each->setAttribute('is_bookmarked', false);
        }

        app(PostCommentPreviewService::class)->attachToPosts($postsCollection);
        app(PostCommentPreviewService::class)->attachToPosts($popularPosts);
        app(PostLinkPreviewService::class)->attachToPosts($postsCollection);
        app(PostLinkPreviewService::class)->attachToPosts($popularPosts);

        $posts->setCollection($postsCollection);

        $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
        $popularComments = Comment::with(['user', 'post'])->whereNull('parent_id')->orderByDesc('id')->take(10)->get();

        return view('blog.index', [
            'posts' => $posts,
            'popularPosts' => $popularPosts,
            'categories' => $categories,
            'tags' => $tags,
            'reactionTypes' => $reactionTypes,
            'activeCategory' => $category->slug,
            'activeTag' => $request->string('tag')->toString(),
            'category' => $category,
            'categoryPostsCount' => $categoryPostsCount,
            'categoryViews' => $categoryViews,
            'followersCount' => $followersCount,
            'isCategoryJoined' => $isCategoryJoined,
            'popularTags' => $popularTags,
            'popularComments' => $popularComments,
        ]);
    }

    public function tags(Request $request)
    {
        $tags = Tag::withCount('posts')->orderBy('name')->paginate(50);

        if ($request->expectsJson()) {
            return Response::json($tags);
        }

        return view('blog.tags', ['tags' => $tags]);
    }

    private function isVisible(Post $post): bool
    {
        return $post->is_published && ($post->published_at === null || $post->published_at->isPast());
    }

    private function buildPollBlocks(Request $request, Post $post): array
    {
        $blocks = $post->content_json['blocks'] ?? [];
        $pollBlocks = collect($blocks)
            ->filter(fn ($block) => ($block['type'] ?? null) === 'poll')
            ->values();

        if ($pollBlocks->isEmpty()) {
            return [];
        }

        $voteCounts = DB::table('post_poll_votes')
            ->where('post_id', $post->id)
            ->selectRaw('block_id, option_index, count(*) as cnt')
            ->groupBy('block_id', 'option_index')
            ->get()
            ->groupBy('block_id');

        $userId = $request->user()?->id;
        $deviceId = (string) ($request->cookie('device_id') ?? '');

        $votesQuery = DB::table('post_poll_votes')
            ->where('post_id', $post->id);

        if ($userId) {
            $votesQuery->where('user_id', $userId);
        } elseif ($deviceId !== '') {
            $votesQuery->where('device_id', $deviceId);
        }

        $userVotes = $votesQuery->pluck('option_index', 'block_id');

        return $pollBlocks->map(function ($block) use ($post, $voteCounts, $userVotes) {
            $question = trim((string) ($block['data']['question'] ?? ''));
            $options = array_values(array_filter(
                array_map('trim', $block['data']['options'] ?? []),
                fn ($option) => $option !== ''
            ));
            $duration = (int) ($block['data']['duration_minutes'] ?? 0);
            $blockId = (string) ($block['id'] ?? '');

            if ($question === '' || count($options) < 2 || $blockId === '') {
                return null;
            }

            $countsByOption = $voteCounts->get($blockId, collect())->pluck('cnt', 'option_index');
            $total = (int) $countsByOption->sum();
            $percentages = [];
            foreach ($options as $idx => $label) {
                $count = (int) ($countsByOption[$idx] ?? 0);
                $percentages[$idx] = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            }

            return [
                'id' => $blockId,
                'question' => $question,
                'options' => $options,
                'duration_minutes' => max(0, $duration),
                'expired' => $this->isPollExpired($post, $duration),
                'total' => $total,
                'counts' => $countsByOption,
                'percentages' => $percentages,
                'user_vote' => $userVotes[$blockId] ?? null,
            ];
        })->filter()->values()->all();
    }

    public function create()
    {
        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return redirect()->route('blog.index')
                ->with('status', 'Post paylasma yetkiniz kisitlandi.');
        }

        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        return view('blog.create', compact('categories', 'tags', 'reactionTypes'));
    }

    public function repostCreate(Request $request, ?Post $post = null)
    {
        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return redirect()->route('blog.index')
                ->with('status', 'Post paylasma yetkiniz kisitlandi.');
        }

        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        $repostUrl = trim((string) $request->string('repost_url'));
        $repostTitle = trim((string) $request->string('repost_title'));
        $repostPost = null;
        if ($post) {
            $post->load(['author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg', 'category:id,name,slug,profile_image,cover_image']);
            $repostPost = $post;
            if ($repostUrl === '') {
                $repostUrl = route('blog.post', $post);
            }
            if ($repostTitle === '') {
                $repostTitle = (string) ($post->title ?? '');
            }
        }
        $repostContent = '';
        if ($repostTitle !== '' || $repostUrl !== '') {
            $repostContent = "Alinti:\n" . ($repostTitle !== '' ? $repostTitle : 'Baslik yok');
            if ($repostUrl !== '') {
                $repostContent .= "\n" . $repostUrl;
            }
        }

        return view('blog.repost-create', compact('categories', 'tags', 'reactionTypes', 'repostUrl', 'repostTitle', 'repostContent', 'repostPost'));
    }

    public function edit(Post $post)
    {
        $user = Auth::user();
        abort_unless($user, 403);
        if ($user->isBlockedFrom('posts')) {
            return redirect()->route('blog.index')
                ->with('status', 'Post paylasma yetkiniz kisitlandi.');
        }
        abort_unless((int) $post->author_id === (int) $user->id, 403);

        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        $post->load('tags');
        if (is_array($post->content_json)) {
            $post->content_json = $this->sanitizeEditorContentJson($post->content_json);
        }

        return view('blog.edit', compact('post', 'categories', 'tags', 'reactionTypes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return redirect()->route('blog.index')
                ->with('status', 'Post paylasma yetkiniz kisitlandi.');
        }

        if ($user && $user->isBlockedFrom('tags') && $request->filled('new_tags')) {
            return back()->withErrors(['new_tags' => 'Etiket ekleme yetkiniz kisitlandi.'])->withInput();
        }

        if (trim((string) $request->input('content_json')) === '') {
            $request->merge(['content_json' => null]);
        }

        $featuredImageRule = $request->hasFile('featured_image')
            ? ['nullable', 'image', 'max:5120']
            : ['nullable', 'string', 'max:2048'];

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'new_tags' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'featured_image' => $featuredImageRule,
            'image_license_url' => ['nullable', 'url', 'max:2048'],
            'image_acquire_url' => ['nullable', 'url', 'max:2048'],
            'image_credit_text' => ['nullable', 'string', 'max:255'],
            'image_creator_name' => ['nullable', 'string', 'max:255'],
            'image_copyright_notice' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'content_json' => ['nullable', 'json'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'is_pinned' => ['sometimes', 'boolean'],
            'comments_disabled' => ['boolean'],
            'is_nsfw' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'repost_post_id' => ['nullable', 'integer', 'exists:posts,id'],
            'repost_url' => ['nullable', 'string', 'max:2048'],
            'repost_title' => ['nullable', 'string', 'max:255'],
        ]);

        $baseSlug = $data['slug'] ?? Str::slug($data['title']);
        $slug = $this->uniqueSlug($baseSlug);
        $isPublished = (bool) ($data['is_published'] ?? false);
        $publishedAt = $isPublished
            ? (!empty($data['published_at']) ? Carbon::parse($data['published_at']) : now())
            : null;

        $contentJson = $data['content_json'] ?? null;
        $decodedContentJson = $contentJson ? json_decode($contentJson, true) : null;
        if (is_array($decodedContentJson)) {
            $decodedContentJson = $this->sanitizeEditorContentJson($decodedContentJson);
        }

        $featuredImage = null;
        if ($request->hasFile('featured_image')) {
            $featuredImage = $request->file('featured_image')->store('featured-images', 'public');
        } elseif (!empty($data['featured_image']) && is_string($data['featured_image'])) {
            $featuredImage = $data['featured_image'];
        }
        $post = Post::create([
            'title' => $data['title'],
            'slug' => $slug,
            'meta_title' => ($data['meta_title'] ?? null) ?: $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'author_id' => Auth::id(),
            'excerpt' => $data['excerpt'] ?? null,
            'featured_image' => $featuredImage,
            'image_license_url' => $data['image_license_url'] ?? null,
            'image_acquire_url' => $data['image_acquire_url'] ?? null,
            'image_credit_text' => $data['image_credit_text'] ?? null,
            'image_creator_name' => $data['image_creator_name'] ?? null,
            'image_copyright_notice' => $data['image_copyright_notice'] ?? null,
            'content' => $data['content'],
            'content_json' => $decodedContentJson,
            'is_published' => $isPublished,
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'comments_disabled' => (bool) ($data['comments_disabled'] ?? false),
            'is_nsfw' => (bool) ($data['is_nsfw'] ?? false),
            'published_at' => $publishedAt,
        ]);

        $tagIds = collect($data['tags'] ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $newTags = collect(preg_split('/[,\n]/', (string) ($data['new_tags'] ?? '')))
            ->map(fn ($name) => preg_replace('/\s+/', ' ', trim((string) $name)))
            ->map(fn ($name) => ltrim($name, '#'))
            ->filter();

        foreach ($newTags as $name) {
            $slugBase = Str::slug($name);
            if ($slugBase === '') {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['slug' => $slugBase],
                ['name' => $name, 'slug' => $slugBase]
            );
            $tagIds->push($tag->id);
        }

        if ($tagIds->isNotEmpty()) {
            $post->tags()->sync($tagIds->unique()->values()->all());
        }

        $repostPostId = $data['repost_post_id'] ?? null;
        if ($repostPostId) {
            $original = Post::query()->with('author:id,name,username')->find($repostPostId);
            $actorId = Auth::id();
            if ($original && $original->author_id && $actorId && (int) $original->author_id !== (int) $actorId) {
                $original->author?->notify(new PostRepostedNotification($original, $post, $request->user()));
            }
        }

        $this->mentionService->notifyPostMentions($post);

        if ($user && $post->isPublishedNow()) {
            $this->badgePointService->award($user, 'post_published');
        }

        return redirect()->route('blog.post', $post)->with('status', 'Yazı oluşturuldu');
    }

    public function update(Request $request, Post $post)
    {
        $user = Auth::user();
        abort_unless($user, 403);
        if ($user->isBlockedFrom('posts')) {
            return redirect()->route('blog.index')
                ->with('status', 'Post paylasma yetkiniz kisitlandi.');
        }
        abort_unless((int) $post->author_id === (int) $user->id, 403);

        if ($user->isBlockedFrom('tags') && $request->filled('new_tags')) {
            return back()->withErrors(['new_tags' => 'Etiket ekleme yetkiniz kisitlandi.'])->withInput();
        }

        $wasPublishedBeforeUpdate = $post->isPublishedNow();
        $previousPostContent = (string) ($post->content ?? '');

        if (trim((string) $request->input('content_json')) === '') {
            $request->merge(['content_json' => null]);
        }

        $featuredImageRule = $request->hasFile('featured_image')
            ? ['nullable', 'image', 'max:5120']
            : ['nullable', 'string', 'max:2048'];

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'new_tags' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'featured_image' => $featuredImageRule,
            'image_license_url' => ['nullable', 'url', 'max:2048'],
            'image_acquire_url' => ['nullable', 'url', 'max:2048'],
            'image_credit_text' => ['nullable', 'string', 'max:255'],
            'image_creator_name' => ['nullable', 'string', 'max:255'],
            'image_copyright_notice' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'content_json' => ['nullable', 'json'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'is_pinned' => ['sometimes', 'boolean'],
            'comments_disabled' => ['boolean'],
            'is_nsfw' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $baseSlug = $data['slug'] ?? '';
        if ($baseSlug === '') {
            $baseSlug = Str::slug($data['title']);
        }
        $slug = $baseSlug === $post->slug ? $post->slug : $this->uniqueSlug($baseSlug);
        $isPublished = (bool) ($data['is_published'] ?? false);
        $publishedAt = $isPublished
            ? (!empty($data['published_at']) ? Carbon::parse($data['published_at']) : ($post->published_at ?? now()))
            : null;

        $contentJson = $data['content_json'] ?? null;
        $decodedContentJson = $contentJson ? json_decode($contentJson, true) : null;
        if (is_array($decodedContentJson)) {
            $decodedContentJson = $this->sanitizeEditorContentJson($decodedContentJson);
        }

        $featuredImage = $post->featured_image;
        if ($request->hasFile('featured_image')) {
            $existingPath = trim((string) $post->featured_image);
            if ($existingPath !== '' && !Str::startsWith($existingPath, ['http://', 'https://', '//', '/storage/', 'storage/'])) {
                if (Storage::disk('public')->exists($existingPath)) {
                    Storage::disk('public')->delete($existingPath);
                }
            }
            $featuredImage = $request->file('featured_image')->store('featured-images', 'public');
        } elseif (!empty($data['featured_image']) && is_string($data['featured_image'])) {
            $featuredImage = $data['featured_image'];
        }

        $postAttributes = [
            'title' => $data['title'],
            'slug' => $slug,
            'meta_title' => ($data['meta_title'] ?? null) ?: $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'featured_image' => $featuredImage,
            'image_license_url' => $data['image_license_url'] ?? null,
            'image_acquire_url' => $data['image_acquire_url'] ?? null,
            'image_credit_text' => $data['image_credit_text'] ?? null,
            'image_creator_name' => $data['image_creator_name'] ?? null,
            'image_copyright_notice' => $data['image_copyright_notice'] ?? null,
            'content' => $data['content'],
            'content_json' => $decodedContentJson,
            'is_published' => $isPublished,
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'comments_disabled' => (bool) ($data['comments_disabled'] ?? false),
            'is_nsfw' => (bool) ($data['is_nsfw'] ?? false),
            'published_at' => $publishedAt,
        ];

        $post->fill($postAttributes);

        $meaningfulEditFields = [
            'title',
            'slug',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'category_id',
            'excerpt',
            'featured_image',
            'image_license_url',
            'image_acquire_url',
            'image_credit_text',
            'image_creator_name',
            'image_copyright_notice',
            'content',
            'content_json',
            'is_published',
            'comments_disabled',
            'is_nsfw',
            'published_at',
        ];

        if ($post->isDirty($meaningfulEditFields)) {
            $post->edited_at = now();
            $post->edited_reason = null;
        }

        $post->save();

        $tagIds = collect($data['tags'] ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $newTags = collect(preg_split('/[,\n]/', (string) ($data['new_tags'] ?? '')))
            ->map(fn ($name) => preg_replace('/\s+/', ' ', trim((string) $name)))
            ->map(fn ($name) => ltrim($name, '#'))
            ->filter();

        foreach ($newTags as $name) {
            $slugBase = Str::slug($name);
            if ($slugBase === '') {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['slug' => $slugBase],
                ['name' => $name, 'slug' => $slugBase]
            );
            $tagIds->push($tag->id);
        }

        $post->tags()->sync($tagIds->unique()->values()->all());

        $this->mentionService->notifyPostMentions($post, $wasPublishedBeforeUpdate ? $previousPostContent : null);

        if (!$wasPublishedBeforeUpdate && $post->isPublishedNow()) {
            $this->badgePointService->award($user, 'post_published');
        }

        return redirect()->route('blog.post', $post)->with('status', 'Yazi guncellendi.');
    }

    public function editorJsImage(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return Response::json(['message' => 'Post paylasma yetkiniz kisitlandi.'], 403);
        }

        $data = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $path = $data['image']->store('editorjs', 'public');
        $url = Storage::disk('public')->url($path);

        return Response::json([
            'success' => 1,
            'file' => [
                'url' => $url,
            ],
        ]);
    }

    public function editorJsVideo(Request $request): JsonResponse
    {
        $contentType = (string) $request->header('content-type', '');
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $maxVideoBytes = 5 * 1024 * 1024 * 1024; // 5GB

        logger()->info('editorJsVideo called', [
            'path' => $request->path(),
            'content_length' => $contentLength,
            'content_type' => $contentType,
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'has_file' => $request->hasFile('video'),
            'file_keys' => array_keys($request->allFiles()),
        ]);

        if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class)) {
            \Barryvdh\Debugbar\Facades\Debugbar::disable();
        }

        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return Response::json(['message' => 'Post paylasma yetkiniz kisitlandi.'], 403);
        }

        $file = $request->file('video')
            ?? $request->file('file')
            ?? $request->file('upload')
            ?? $this->firstUploadedFile($request->allFiles());

        // Legacy compatibility: some clients send raw binary with xhr.send(file)
        $isMultipart = str_contains(strtolower($contentType), 'multipart/form-data');
        if (!$file && $contentLength > 0 && !$isMultipart) {
            $mime = strtolower(trim(strtok($contentType, ';') ?: ''));
            $isRawVideo = str_starts_with($mime, 'video/') || $mime === 'application/octet-stream';

            if ($isRawVideo) {
                if ($contentLength > $maxVideoBytes) {
                    return Response::json([
                        'success' => 0,
                        'message' => 'Dosya boyutu limiti asiyor (5GB).',
                    ], 422);
                }

                $ext = match ($mime) {
                    'video/webm' => 'webm',
                    'video/quicktime' => 'mov',
                    'video/ogg' => 'ogv',
                    'video/x-matroska' => 'mkv',
                    'video/x-msvideo', 'video/avi' => 'avi',
                    'video/mpeg' => 'mpeg',
                    'video/3gpp' => '3gp',
                    'video/mp4' => 'mp4',
                    default => 'mp4',
                };

                $relativePath = 'editorjs/videos/' . Str::uuid() . '.' . $ext;
                $stream = fopen('php://input', 'rb');
                if (!$stream) {
                    return Response::json([
                        'success' => 0,
                        'message' => 'Video akisi okunamadi.',
                    ], 422);
                }

                $saved = Storage::disk('public')->put($relativePath, $stream);
                fclose($stream);

                if ($saved) {
                    return Response::json([
                        'success' => 1,
                        'file' => [
                            'url' => Storage::disk('public')->url($relativePath),
                        ],
                    ]);
                }

                return Response::json([
                    'success' => 0,
                    'message' => 'Video kaydedilemedi.',
                ], 500);
            }
        }

        if (!$file) {
            return Response::json([
                'success' => 0,
                'message' => $contentLength > 0
                    ? 'Video dosyasi alinamadi. Istek formati gecersiz olabilir.'
                    : 'Video dosyasi alinamadi.',
            ], 422);
        }

        if (!$file->isValid()) {
            return Response::json([
                'success' => 0,
                'message' => $file?->getErrorMessage() ?: 'Video yuklenemedi.',
            ], 422);
        }

        $size = (int) $file->getSize();
        if ($size <= 0 || $size > $maxVideoBytes) {
            return Response::json([
                'success' => 0,
                'message' => 'Dosya boyutu limiti asiyor (5GB).',
            ], 422);
        }

        $mime = strtolower((string) ($file->getMimeType() ?? ''));
        $ext = strtolower((string) ($file->getClientOriginalExtension() ?? ''));
        $allowedExt = ['mp4', 'webm', 'mov', 'ogv', 'mkv', 'avi', 'mpeg', 'mpg', '3gp', 'm4v'];

        if (($ext !== '' && !in_array($ext, $allowedExt, true)) || !$this->isAllowedVideoMime($mime)) {
            return Response::json([
                'success' => 0,
                'message' => 'Desteklenmeyen video formati.',
            ], 422);
        }

        try {
            $path = $file->store('editorjs/videos', 'public');
            $url = Storage::disk('public')->url($path);
        } catch (\Throwable $e) {
            report($e);
            return Response::json([
                'success' => 0,
                'message' => 'Sunucuya video kaydedilemedi. Lutfen tekrar deneyin.',
            ], 500);
        }

        return Response::json([
            'success' => 1,
            'file' => [
                'url' => $url,
            ],
        ]);
    }

    public function editorJsVideoInit(Request $request): JsonResponse
    {
        logger()->info('editorJsVideoInit called', [
            'path' => $request->path(),
            'content_length' => (int) $request->server('CONTENT_LENGTH', 0),
            'content_type' => (string) $request->header('content-type', ''),
        ]);

        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return Response::json(['message' => 'Post paylasma yetkiniz kisitlandi.'], 403);
        }

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:1024'],
                'mime' => ['nullable', 'string', 'max:255'],
                'size' => ['required', 'integer', 'min:1', 'max:5368709120'],
            ]);
        } catch (ValidationException $e) {
            logger()->warning('editorJsVideoInit validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->only(['name', 'mime', 'size']),
            ]);

            return Response::json([
                'success' => 0,
                'message' => collect($e->errors())->flatten()->first() ?: 'Gecersiz video metadata.',
                'errors' => $e->errors(),
            ], 422);
        }

        $mime = (string) ($data['mime'] ?? '');

        if (!$this->isAllowedVideoMime($mime)) {
            return Response::json([
                'success' => 0,
                'message' => 'Desteklenmeyen video formati.',
            ], 422);
        }

        $uploadId = (string) Str::uuid();
        $base = "editorjs-chunks/{$uploadId}";
        Storage::disk('local')->makeDirectory($base);
        Storage::disk('local')->put("{$base}/meta.json", json_encode([
            'name' => $data['name'],
            'mime' => $mime,
            'size' => (int) $data['size'],
            'created_at' => now()->toIso8601String(),
        ]));

        logger()->info('editorJsVideoInit prepared upload', [
            'upload_id' => $uploadId,
            'base' => Storage::disk('local')->path($base),
            'meta_exists' => Storage::disk('local')->exists("{$base}/meta.json"),
        ]);

        return Response::json([
            'success' => 1,
            'upload_id' => $uploadId,
        ]);
    }

    public function editorJsVideoChunk(Request $request): JsonResponse
    {
        logger()->info('editorJsVideoChunk called', [
            'path' => $request->path(),
            'content_length' => (int) $request->server('CONTENT_LENGTH', 0),
            'content_type' => (string) $request->header('content-type', ''),
        ]);

        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return Response::json(['message' => 'Post paylasma yetkiniz kisitlandi.'], 403);
        }

        try {
            $data = $request->validate([
                'upload_id' => ['required', 'string', 'max:100'],
                'index' => ['required', 'integer', 'min:0'],
                'total' => ['required', 'integer', 'min:1', 'max:10000'],
                'chunk' => ['required', 'file', 'max:2048'],
            ]);
        } catch (ValidationException $e) {
            logger()->warning('editorJsVideoChunk validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->only(['upload_id', 'index', 'total']),
            ]);

            return Response::json([
                'success' => 0,
                'message' => collect($e->errors())->flatten()->first() ?: 'Video parcasi gecersiz.',
                'errors' => $e->errors(),
            ], 422);
        }

        $rawUploadId = (string) $data['upload_id'];
        $uploadId = $this->normalizeUploadId($rawUploadId);
        $base = "editorjs-chunks/{$uploadId}";
        logger()->info('editorJsVideoChunk targeting upload', [
            'raw_upload_id' => $rawUploadId,
            'upload_id' => $uploadId,
            'index' => (int) $data['index'],
            'total' => (int) $data['total'],
            'base' => Storage::disk('local')->path($base),
            'meta_exists' => Storage::disk('local')->exists("{$base}/meta.json"),
        ]);

        if (!Storage::disk('local')->exists("{$base}/meta.json")) {
            return Response::json([
                'success' => 0,
                'message' => 'Gecersiz upload oturumu.',
            ], 422);
        }

        $partName = sprintf('%06d.part', (int) $data['index']);
        Storage::disk('local')->putFileAs($base, $data['chunk'], $partName);

        return Response::json(['success' => 1]);
    }

    public function editorJsVideoComplete(Request $request): JsonResponse
    {
        logger()->info('editorJsVideoComplete called', [
            'path' => $request->path(),
            'content_length' => (int) $request->server('CONTENT_LENGTH', 0),
            'content_type' => (string) $request->header('content-type', ''),
        ]);

        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return Response::json(['message' => 'Post paylasma yetkiniz kisitlandi.'], 403);
        }

        try {
            $data = $request->validate([
                'upload_id' => ['required', 'string', 'max:100'],
                'total' => ['required', 'integer', 'min:1', 'max:10000'],
                'name' => ['required', 'string', 'max:1024'],
                'mime' => ['nullable', 'string', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            logger()->warning('editorJsVideoComplete validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->only(['upload_id', 'total', 'name', 'mime']),
            ]);

            return Response::json([
                'success' => 0,
                'message' => collect($e->errors())->flatten()->first() ?: 'Video tamamlama verisi gecersiz.',
                'errors' => $e->errors(),
            ], 422);
        }

        $rawUploadId = (string) $data['upload_id'];
        $uploadId = $this->normalizeUploadId($rawUploadId);
        $candidateBases = [
            Storage::disk('local')->path('editorjs-chunks/' . $uploadId),
            storage_path('app/private/editorjs-chunks/' . $uploadId),
            storage_path('app/editorjs-chunks/' . $uploadId),
        ];

        // If incoming id contains wrapping characters/encoding noise, try wildcard fallback.
        if (!collect($candidateBases)->contains(fn (string $path) => is_dir($path))) {
            $chunkRoots = [
                Storage::disk('local')->path('editorjs-chunks'),
                storage_path('app/private/editorjs-chunks'),
                storage_path('app/editorjs-chunks'),
            ];

            foreach ($chunkRoots as $root) {
                if (!is_dir($root)) {
                    continue;
                }

                $matches = glob(rtrim($root, '\\/') . DIRECTORY_SEPARATOR . '*' . $uploadId . '*', GLOB_ONLYDIR) ?: [];
                foreach ($matches as $match) {
                    $candidateBases[] = $match;
                }
            }
        }

        logger()->info('editorJsVideoComplete resolving base', [
            'raw_upload_id' => $rawUploadId,
            'upload_id' => $uploadId,
            'candidates' => collect($candidateBases)
                ->map(fn (string $path) => ['path' => $path, 'exists' => is_dir($path)])
                ->all(),
        ]);

        $base = collect($candidateBases)->first(fn (string $path) => is_dir($path));

        if (!$base) {
            logger()->warning('editorJsVideoComplete base not found', [
                'upload_id' => $uploadId,
                'candidates' => $candidateBases,
            ]);

            return Response::json([
                'success' => 0,
                'message' => 'Upload verisi bulunamadi.',
            ], 422);
        }

        $ext = strtolower((string) pathinfo($data['name'], PATHINFO_EXTENSION));
        if ($ext === '') {
            $ext = match ((string) ($data['mime'] ?? '')) {
                'video/webm' => 'webm',
                'video/quicktime' => 'mov',
                'video/x-msvideo', 'video/avi' => 'avi',
                'video/ogg' => 'ogv',
                default => 'mp4',
            };
        }

        $finalRelPath = 'editorjs/videos/' . Str::uuid() . '.' . $ext;
        Storage::disk('public')->makeDirectory('editorjs/videos');
        $finalAbsPath = storage_path('app/public/' . $finalRelPath);
        $out = @fopen($finalAbsPath, 'wb');
        if ($out === false) {
            return Response::json([
                'success' => 0,
                'message' => 'Video dosyasi olusturulamadi.',
            ], 500);
        }

        try {
            for ($i = 0; $i < (int) $data['total']; $i++) {
                $part = $base . DIRECTORY_SEPARATOR . sprintf('%06d.part', $i);
                if (!is_file($part)) {
                    throw new \RuntimeException('Eksik chunk: ' . $i);
                }
                $in = fopen($part, 'rb');
                if ($in === false) {
                    throw new \RuntimeException('Chunk okunamadi: ' . $i);
                }
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
        } catch (\Throwable $e) {
            fclose($out);
            @unlink($finalAbsPath);
            return Response::json([
                'success' => 0,
                'message' => 'Video birlestirilemedi.',
            ], 500);
        }

        fclose($out);
        if (str_contains(str_replace('\\', '/', $base), str_replace('\\', '/', storage_path('app/private/editorjs-chunks/')))) {
            Storage::disk('local')->deleteDirectory('editorjs-chunks/' . $uploadId);
        } elseif (is_dir($base)) {
            \Illuminate\Support\Facades\File::deleteDirectory($base);
        }

        return Response::json([
            'success' => 1,
            'file' => [
                'url' => Storage::disk('public')->url($finalRelPath),
            ],
        ]);
    }

    private function normalizeUploadId(string $uploadId): string
    {
        $uploadId = trim(urldecode($uploadId));
        $uploadId = trim($uploadId, "\"'[](){} \t\n\r\0\x0B");

        if (preg_match('/([a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12})/i', $uploadId, $m)) {
            return strtolower($m[1]);
        }

        return strtolower((string) (preg_replace('/[^a-z0-9-]/i', '', $uploadId) ?? ''));
    }

    private function firstUploadedFile(array $files)
    {
        foreach ($files as $item) {
            if ($item instanceof \Illuminate\Http\UploadedFile) {
                return $item;
            }

            if (is_array($item)) {
                $found = $this->firstUploadedFile($item);
                if ($found instanceof \Illuminate\Http\UploadedFile) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function sanitizeEditorContentJson(array $payload): array
    {
        $blocks = $payload['blocks'] ?? [];
        if (!is_array($blocks)) {
            return ['blocks' => []];
        }

        $cleanBlocks = [];
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            if ($type === '') {
                continue;
            }

            $data = $block['data'] ?? [];
            if (!is_array($data)) {
                if (is_string($data)) {
                    $decoded = json_decode($data, true);
                    $data = is_array($decoded) ? $decoded : ['text' => $data];
                } else {
                    $data = [];
                }
            }

            if ($type === 'paragraph') {
                $text = $data['text'] ?? $data['content'] ?? $data['html'] ?? '';
                if (is_array($text)) {
                    $text = '';
                }
                $text = trim((string) $text);
                if ($text === '') {
                    continue;
                }

                $paragraphBlocks = $this->splitParagraphIntoTextAndSocialEmbedBlocks($text);
                if (!empty($paragraphBlocks)) {
                    foreach ($paragraphBlocks as $paragraphBlock) {
                        $cleanBlocks[] = $paragraphBlock;
                    }
                    continue;
                }

                $data = ['text' => $text];
            }

            if ($type === 'embed' || $type === 'socialEmbed') {
                $raw = trim((string) ($data['src'] ?? $data['embed'] ?? $data['source'] ?? ''));
                if ($raw !== '') {
                    $embedSrc = $this->buildSocialEmbedUrlFromUrl($raw) ?? $this->sanitizeSocialEmbedUrl($raw);
                    if ($embedSrc) {
                        $cleanBlocks[] = [
                            'type' => 'socialEmbed',
                            'data' => ['src' => $embedSrc],
                        ];
                        continue;
                    }
                }
            }

            if ($type === 'video') {
                $url = trim((string) (
                    $data['url']
                    ?? $data['src']
                    ?? ($data['file']['url'] ?? '')
                    ?? ($data['file']['src'] ?? '')
                ));
                if ($url === '') {
                    continue;
                }
                $data = array_merge($data, ['url' => $url]);
            }

            $cleanBlocks[] = [
                'type' => $type,
                'data' => $data,
            ];
        }

        $out = $payload;
        $out['blocks'] = $cleanBlocks;

        return $out;
    }

    private function splitParagraphIntoTextAndSocialEmbedBlocks(string $text): array
    {
        $lines = preg_split('/\R+/u', $text) ?: [];
        $blocks = [];
        $paragraphBuffer = [];
        $foundEmbed = false;

        $flushParagraphBuffer = function () use (&$blocks, &$paragraphBuffer): void {
            $paragraphText = trim(implode("\n", $paragraphBuffer));
            if ($paragraphText !== '') {
                $blocks[] = [
                    'type' => 'paragraph',
                    'data' => ['text' => $paragraphText],
                ];
            }
            $paragraphBuffer = [];
        };

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                $paragraphBuffer[] = '';
                continue;
            }

            $embedSrc = $this->buildSocialEmbedUrlFromUrl($line);
            if ($embedSrc) {
                $flushParagraphBuffer();
                $blocks[] = [
                    'type' => 'socialEmbed',
                    'data' => ['src' => $embedSrc],
                ];
                $foundEmbed = true;
                continue;
            }

            $paragraphBuffer[] = $line;
        }

        $flushParagraphBuffer();

        return $foundEmbed ? $blocks : [];
    }

    private function renderSocialEmbedsInHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $previousLibxmlState = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $wrappedHtml = '<?xml encoding="utf-8" ?><div id="social-embed-root">' . $html . '</div>';

        try {
            if (!$dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                return $html;
            }

            $container = $dom->getElementById('social-embed-root');
            if (!$container) {
                return $html;
            }

            $paragraphs = [];
            foreach ($container->getElementsByTagName('p') as $paragraph) {
                if ($paragraph instanceof \DOMElement) {
                    $paragraphs[] = $paragraph;
                }
            }

            $changed = false;
            foreach ($paragraphs as $paragraph) {
                $text = $this->domTextWithLineBreaks($paragraph);
                $lines = preg_split('/\R+/u', $text) ?: [];
                $items = [];
                $hasEmbed = false;

                foreach ($lines as $line) {
                    $line = trim((string) $line);
                    if ($line === '') {
                        continue;
                    }

                    $embedSrc = $this->buildSocialEmbedUrlFromUrl($line);
                    if ($embedSrc) {
                        $items[] = ['type' => 'embed', 'src' => $embedSrc];
                        $hasEmbed = true;
                    } else {
                        $items[] = ['type' => 'text', 'text' => $line];
                    }
                }

                if (!$hasEmbed) {
                    continue;
                }

                $fragment = $dom->createDocumentFragment();
                foreach ($items as $item) {
                    if (($item['type'] ?? '') === 'embed') {
                        $fragment->appendChild($this->createSocialEmbedNode($dom, (string) $item['src']));
                        continue;
                    }

                    $replacementParagraph = $dom->createElement('p');
                    $replacementParagraph->appendChild($dom->createTextNode((string) ($item['text'] ?? '')));
                    $fragment->appendChild($replacementParagraph);
                }

                $paragraph->parentNode?->replaceChild($fragment, $paragraph);
                $changed = true;
            }

            if (!$changed) {
                return $html;
            }

            $output = '';
            foreach ($container->childNodes as $childNode) {
                $output .= $dom->saveHTML($childNode);
            }

            return trim($output) !== '' ? $output : $html;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlState);
        }
    }

    private function domTextWithLineBreaks(\DOMNode $node): string
    {
        if ($node instanceof \DOMText) {
            return (string) $node->nodeValue;
        }

        if ($node instanceof \DOMElement && strtolower($node->tagName) === 'br') {
            return "\n";
        }

        $text = '';
        foreach ($node->childNodes as $childNode) {
            $text .= $this->domTextWithLineBreaks($childNode);
        }

        return $text;
    }

    private function createSocialEmbedNode(\DOMDocument $dom, string $src): \DOMElement
    {
        $safe = $this->sanitizeSocialEmbedUrl($src) ?: '';

        $outer = $dom->createElement('div');
        $outer->setAttribute('class', 'my-4');

        $inner = $dom->createElement('div');
        $inner->setAttribute('class', 'w-full overflow-hidden rounded-xl bg-black/5');
        $inner->setAttribute('style', $this->socialEmbedFrameStyle($safe));

        $iframe = $dom->createElement('iframe');
        $iframe->setAttribute('class', 'h-full w-full');
        $iframe->setAttribute('src', $safe);
        $iframe->setAttribute('loading', 'lazy');
        $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');

        $inner->appendChild($iframe);
        $outer->appendChild($inner);

        return $outer;
    }

    private function socialEmbedFrameStyle(string $src): string
    {
        $host = strtolower((string) (parse_url($src, PHP_URL_HOST) ?: ''));
        if (str_contains($host, 'tiktok.com') || str_contains($host, 'instagram.com')) {
            return 'aspect-ratio: 9 / 16; max-width: 425px; margin-left: auto; margin-right: auto;';
        }

        return 'aspect-ratio: 16 / 9;';
    }

    private function buildSocialEmbedUrlFromUrl(string $value): ?string
    {
        $value = $this->extractUrlFromEmbedInput($value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        }

        $parts = parse_url($value);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '/');
        $pathParts = array_values(array_filter(explode('/', trim($path, '/'))));
        parse_str((string) ($parts['query'] ?? ''), $query);

        // YouTube
        if ($host === 'youtu.be') {
            $id = $pathParts[0] ?? null;
            return $id ? $this->sanitizeSocialEmbedUrl('https://www.youtube.com/embed/' . rawurlencode($id)) : null;
        }
        if (str_ends_with($host, 'youtube.com') || str_ends_with($host, 'youtube-nocookie.com')) {
            $id = null;
            if (($pathParts[0] ?? '') === 'watch') {
                $id = (string) ($query['v'] ?? '');
            } elseif (($pathParts[0] ?? '') === 'shorts') {
                $id = $pathParts[1] ?? null;
            } elseif (($pathParts[0] ?? '') === 'embed') {
                $id = $pathParts[1] ?? null;
            }
            return $id ? $this->sanitizeSocialEmbedUrl('https://www.youtube.com/embed/' . rawurlencode($id)) : null;
        }

        // Instagram
        if (str_ends_with($host, 'instagram.com')) {
            $kind = $pathParts[0] ?? '';
            $code = $pathParts[1] ?? '';
            if (in_array($kind, ['p', 'reel', 'tv'], true) && $code !== '') {
                return $this->sanitizeSocialEmbedUrl("https://www.instagram.com/{$kind}/" . rawurlencode($code) . '/embed');
            }
        }

        // TikTok
        if (str_ends_with($host, 'tiktok.com')) {
            if (($pathParts[0] ?? '') === 'embed' && ($pathParts[1] ?? '') === 'v2' && !empty($pathParts[2])) {
                return $this->sanitizeSocialEmbedUrl('https://www.tiktok.com/embed/v2/' . rawurlencode((string) $pathParts[2]));
            }

            if (($pathParts[1] ?? '') === 'video' && !empty($pathParts[2])) {
                return $this->sanitizeSocialEmbedUrl('https://www.tiktok.com/embed/v2/' . rawurlencode((string) $pathParts[2]));
            }
        }

        // Vimeo
        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            $id = (($pathParts[0] ?? '') === 'video') ? ($pathParts[1] ?? null) : ($pathParts[0] ?? null);
            if ($id && preg_match('/^\d+$/', $id)) {
                return $this->sanitizeSocialEmbedUrl('https://player.vimeo.com/video/' . $id);
            }
        }

        // Dailymotion
        if (str_ends_with($host, 'dailymotion.com')) {
            $id = (($pathParts[0] ?? '') === 'video') ? ($pathParts[1] ?? null) : null;
            return $id ? $this->sanitizeSocialEmbedUrl('https://www.dailymotion.com/embed/video/' . rawurlencode($id)) : null;
        }
        if ($host === 'dai.ly') {
            $id = $pathParts[0] ?? null;
            return $id ? $this->sanitizeSocialEmbedUrl('https://www.dailymotion.com/embed/video/' . rawurlencode($id)) : null;
        }

        // Twitch
        $parentHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
        if (in_array($host, ['twitch.tv', 'www.twitch.tv'], true)) {
            if (($pathParts[0] ?? '') === 'videos' && !empty($pathParts[1])) {
                return $this->sanitizeSocialEmbedUrl('https://player.twitch.tv/?video=v' . rawurlencode((string) $pathParts[1]) . '&parent=' . rawurlencode((string) $parentHost));
            }
            if (count($pathParts) >= 3 && ($pathParts[1] ?? '') === 'clip' && !empty($pathParts[2])) {
                return $this->sanitizeSocialEmbedUrl('https://player.twitch.tv/?clip=' . rawurlencode((string) $pathParts[2]) . '&parent=' . rawurlencode((string) $parentHost));
            }
        }
        if ($host === 'clips.twitch.tv') {
            $clip = $pathParts[0] ?? null;
            return $clip ? $this->sanitizeSocialEmbedUrl('https://player.twitch.tv/?clip=' . rawurlencode((string) $clip) . '&parent=' . rawurlencode((string) $parentHost)) : null;
        }

        // Facebook
        if (str_ends_with($host, 'facebook.com') || str_ends_with($host, 'fb.watch')) {
            return $this->sanitizeSocialEmbedUrl('https://www.facebook.com/plugins/video.php?href=' . rawurlencode($value) . '&show_text=false');
        }

        // X / Twitter
        if (in_array($host, ['x.com', 'www.x.com', 'mobile.x.com', 'twitter.com', 'www.twitter.com', 'mobile.twitter.com'], true) || str_ends_with($host, '.x.com') || str_ends_with($host, '.twitter.com')) {
            if (in_array('status', $pathParts, true) || in_array('statuses', $pathParts, true)) {
                return $this->sanitizeSocialEmbedUrl('https://twitframe.com/show?url=' . rawurlencode($value));
            }
        }

        // Vine (legacy)
        if (str_ends_with($host, 'vine.co') && ($pathParts[0] ?? '') === 'v' && !empty($pathParts[1])) {
            return $this->sanitizeSocialEmbedUrl('https://vine.co/v/' . rawurlencode((string) $pathParts[1]) . '/embed/simple');
        }

        return null;
    }

    private function sanitizeSocialEmbedUrl(string $url): ?string
    {
        $url = $this->extractUrlFromEmbedInput($url);
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $allowed = [
            'www.youtube.com',
            'youtube.com',
            'www.youtube-nocookie.com',
            'youtube-nocookie.com',
            'www.instagram.com',
            'instagram.com',
            'www.tiktok.com',
            'tiktok.com',
            'player.vimeo.com',
            'www.dailymotion.com',
            'dailymotion.com',
            'player.twitch.tv',
            'www.facebook.com',
            'facebook.com',
            'twitframe.com',
            'vine.co',
        ];

        return in_array($host, $allowed, true) ? $url : null;
    }

    private function extractUrlFromEmbedInput(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/\bsrc\s*=\s*(?:"(https?:\/\/[^"]+)"|\'(https?:\/\/[^\']+)\'|(https?:\/\/[^\s>]+))/i', $value, $matches)) {
            return trim((string) collect([$matches[1] ?? '', $matches[2] ?? '', $matches[3] ?? ''])->first(fn ($match) => trim((string) $match) !== '', ''));
        }

        if (preg_match('/https?:\/\/[^\s"\'<>]+/i', $value, $matches)) {
            $url = trim($matches[0]);
            return $value === $url ? $url : $value;
        }

        return $value;
    }

    private function isAllowedVideoMime(string $mime): bool
    {
        $mime = strtolower(trim($mime));
        if ($mime === 'application/octet-stream' || $mime === '') {
            return true;
        }

        return in_array($mime, [
            'video/mp4',
            'video/webm',
            'video/quicktime',
            'video/ogg',
            'video/x-matroska',
            'video/x-msvideo',
            'video/avi',
            'video/mpeg',
            'video/3gpp',
        ], true);
    }

    public function editorJsSubtitle(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && $user->isBlockedFrom('posts')) {
            return Response::json(['message' => 'Post paylasma yetkiniz kisitlandi.'], 403);
        }

        $data = $request->validate([
            'subtitle' => ['nullable', 'file', 'mimetypes:text/vtt', 'max:5120'],
            'subtitle_content' => ['nullable', 'string'],
            'filename' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('subtitle')) {
            $path = $data['subtitle']->store('editorjs/subtitles', 'public');
        } else {
            $content = trim((string) ($data['subtitle_content'] ?? ''));
            if ($content === '') {
                return Response::json(['success' => 0], 422);
            }

            $baseName = Str::slug($data['filename'] ?? 'subtitle') ?: 'subtitle';
            $name = "{$baseName}-{$request->ip()}-" . time() . '.vtt';
            $path = "editorjs/subtitles/{$name}";
            Storage::disk('public')->put($path, $content);
        }

        $url = Storage::disk('public')->url($path);

        return Response::json([
            'success' => 1,
            'file' => [
                'url' => $url,
            ],
        ]);
    }

    public function editorJsLink(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $preview = app(PostLinkPreviewService::class)->previewForUrl((string) $data['url'], true);
        if (!$preview) {
            return Response::json(['success' => 0]);
        }

        $meta = array_filter([
            'title' => $preview['title'] ?? null,
            'description' => $preview['description'] ?? null,
            'site_name' => $preview['site_name'] ?? null,
            'image' => !empty($preview['image_url']) ? ['url' => $preview['image_url']] : null,
        ], fn ($value) => $value !== null && $value !== '');

        return Response::json([
            'success' => 1,
            'meta' => $meta,
            'link' => $preview['url'] ?? $data['url'],
        ]);
    }

    public function toggleCommentLike(Request $request, Comment $comment)
    {
        $post = $comment->post;
        abort_unless($post && $this->isVisible($post), 404);

        $user = $request->user();
        if ($user && $user->isBlockedFrom('reactions')) {
            return back()->withErrors(['comment' => 'Tepki verme yetkiniz kisitlandi.']);
        }

        $this->toggleCommentReaction($request, $comment, true);
        return back();
    }

    public function updateComment(Request $request, Comment $comment)
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        if ($user && $user->isBlockedFrom('comments')) {
            return back()->withErrors(['content' => 'Yorum guncelleme yetkiniz kisitlandi.']);
        }
        abort_unless((int) $comment->user_id === (int) Auth::id(), 403);

        $previousCommentContent = (string) ($comment->content ?? '');

        $data = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $data['content'] = $this->commentModerationService->censor((string) $data['content']);

        $comment->update([
            'content' => $data['content'],
        ]);

        $this->deleteRemovedCommentImages($previousCommentContent, $data['content']);
        $this->mentionService->notifyCommentMentions($comment, $previousCommentContent);

        if ($request->expectsJson()) {
            return Response::json([
                'updated_at' => optional($comment->updated_at)->toIso8601String(),
            ]);
        }

        return back()->with('status', 'Yorum güncellendi.');
    }

    public function destroyComment(Request $request, Comment $comment)
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        if ($user && $user->isBlockedFrom('comments')) {
            return back()->withErrors(['comment' => 'Yorum silme yetkiniz kisitlandi.']);
        }
        abort_unless((int) $comment->user_id === (int) Auth::id(), 403);

        $commentContent = (string) ($comment->content ?? '');
        $comment->delete();
        $this->deleteCommentImages($commentContent);

        if ($request->expectsJson()) {
            return Response::json(['deleted' => true]);
        }

        return back()->with('status', 'Yorum silindi.');
    }

    public function toggleCommentDislike(Request $request, Comment $comment)
    {
        $post = $comment->post;
        abort_unless($post && $this->isVisible($post), 404);

        $user = $request->user();
        if ($user && $user->isBlockedFrom('reactions')) {
            return back()->withErrors(['comment' => 'Tepki verme yetkiniz kisitlandi.']);
        }

        $this->toggleCommentReaction($request, $comment, false);
        return back();
    }

    private function deleteRemovedCommentImages(string $previousContent, string $currentContent): void
    {
        $removedPaths = array_diff(
            $this->commentImagePaths($previousContent),
            $this->commentImagePaths($currentContent),
        );

        Storage::disk('public')->delete($removedPaths);
    }

    private function deleteCommentImages(string $content): void
    {
        Storage::disk('public')->delete($this->commentImagePaths($content));
    }

    private function commentImagePaths(string $content): array
    {
        preg_match_all('/\[(?:img|image):([^\]\s]+)\]/iu', $content, $matches);

        return collect($matches[1] ?? [])
            ->filter(fn ($path) => Str::startsWith((string) $path, 'comment-images/'))
            ->unique()
            ->values()
            ->all();
    }

    private function toggleCommentReaction(Request $request, Comment $comment, bool $isLike): void
    {
        $userId = $request->user()?->id;
        $fingerprint = $this->commentFingerprint($request);

        $existing = CommentReaction::query()
            ->where('comment_id', $comment->id)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn ($q) => $q->whereNull('user_id')->where('fingerprint', $fingerprint))
            ->first();

        if ($existing && (bool) $existing->is_like === $isLike) {
            $existing->delete();
            return;
        }

        if ($existing) {
            $existing->update([
                'is_like' => $isLike,
            ]);
            return;
        }

        CommentReaction::create([
            'comment_id' => $comment->id,
            'user_id' => $userId,
            'fingerprint' => $userId ? null : $fingerprint,
            'is_like' => $isLike,
        ]);
    }

    private function commentFingerprint(Request $request): string
    {
        $deviceId = (string) ($request->cookie('device_id') ?? '');
        if ($deviceId !== '') {
            return sha1('device:' . $deviceId);
        }

        return sha1(($request->ip() ?? '') . '|' . ($request->userAgent() ?? ''));
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: Str::random(8);
        $original = $slug;
        $counter = 0;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $original . '-' . Str::random(4);
            $counter++;

            if ($counter > 10) {
                $slug = $original . '-' . Str::random(8);
                break;
            }
        }

        return $slug;
    }

    public function sitemapIndex(SitemapManager $manager)
    {
        return response()->view('xml.sitemap-index', [
            'items' => $manager->sitemapIndexItems(),
        ])->header('Content-Type', 'application/xml');
    }

    public function sitemapPosts(SitemapManager $manager)
    {
        return response()->view('xml.sitemaps', [
            'items' => $manager->postsEntries(),
        ])->header('Content-Type', 'application/xml');
    }

    public function sitemapCategories(SitemapManager $manager)
    {
        return response()->view('xml.sitemaps', [
            'items' => $manager->categoriesEntries(),
        ])->header('Content-Type', 'application/xml');
    }

    public function sitemapTags(SitemapManager $manager)
    {
        return response()->view('xml.sitemaps', [
            'items' => $manager->tagsEntries(),
        ])->header('Content-Type', 'application/xml');
    }

    public function sitemapPages(SitemapManager $manager)
    {
        return response()->view('xml.sitemaps', [
            'items' => $manager->pagesEntries(),
        ])->header('Content-Type', 'application/xml');
    }

    private function findPollBlock(Post $post, string $blockId): ?array
    {
        $blocks = $post->content_json['blocks'] ?? [];
        foreach ($blocks as $block) {
            if (($block['type'] ?? null) !== 'poll') {
                continue;
            }
            if (($block['id'] ?? '') === $blockId) {
                $question = trim((string) ($block['data']['question'] ?? ''));
                $options = array_values(array_filter(
                    array_map('trim', $block['data']['options'] ?? []),
                    fn ($option) => $option !== ''
                ));
                $duration = (int) ($block['data']['duration_minutes'] ?? 0);

                if ($question === '' || count($options) < 2) {
                    return null;
                }

                return [
                    'question' => $question,
                    'options' => $options,
                    'duration_minutes' => max(0, $duration),
                ];
            }
        }

        return null;
    }

    private function isPollExpired(Post $post, int $durationMinutes): bool
    {
        if ($durationMinutes <= 0) {
            return false;
        }

        $start = $post->published_at ?? $post->created_at ?? now();

        return $start->copy()->addMinutes($durationMinutes)->isPast();
    }
}


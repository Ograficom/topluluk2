<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\AdvertiseController;
use App\Http\Controllers\BorsaController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\CookiePolicyController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\MentionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PostReportController;
use App\Http\Middleware\RedirectIfInstalled;
use App\Services\BadgePointService;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\ReactionType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Middleware\ValidatePostSize;
use Illuminate\Validation\Rule;
use App\Http\Controllers\AiController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\EmailVerificationCodeController;

// E-posta istemcisindeki bağlantı, kullanıcının web oturumu olmasa da imzalı URL
// ve e-posta hash'i ile güvenle doğrulanabilmelidir.
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verify-code', EmailVerificationCodeController::class)
    ->middleware(['auth', 'throttle:10,1'])
    ->name('verification.code.verify');

Route::middleware(RedirectIfInstalled::class)->prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'saveDatabase'])->name('database.save');
    Route::get('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallController::class, 'saveAdmin'])->name('admin.save');
    Route::get('/finished', [InstallController::class, 'finished'])->name('finished');
});

Route::get('/auth/{provider}', [SocialLoginController::class, 'redirect'])
    ->whereIn('provider', ['google'])
    ->name('social.redirect');

Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])
    ->whereIn('provider', ['google'])
    ->name('social.callback');

Route::post('/auth/google/one-tap', [SocialLoginController::class, 'oneTap'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('social.google.one_tap');

Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/.well-known/assetlinks.json', [PwaController::class, 'assetLinks'])->name('pwa.assetlinks');
Route::post('/cookie-consent', [CookieConsentController::class, 'store'])->name('cookie-consent.store');

Route::get('/go', function (Request $request) {
    $targetUrl = trim((string) $request->query('url', ''));

    if ($targetUrl === '' || !filter_var($targetUrl, FILTER_VALIDATE_URL)) {
        abort(404);
    }

    $targetParts = parse_url($targetUrl);
    $targetScheme = strtolower((string) ($targetParts['scheme'] ?? ''));
    $targetHost = strtolower((string) ($targetParts['host'] ?? ''));
    $currentHost = strtolower((string) $request->getHost());

    if (!in_array($targetScheme, ['http', 'https'], true) || $targetHost === '') {
        abort(404);
    }

    if ($targetHost === $currentHost) {
        return redirect()->to($targetUrl);
    }

    return view('external-bridge', [
        'targetUrl' => $targetUrl,
        'targetHost' => $targetHost,
    ]);
})->name('external.bridge');

Route::get('/ads/frame/{slotKey}', function (string $slotKey) {
    abort_unless(preg_match('/\A[a-zA-Z0-9_-]+\z/', $slotKey) === 1, 404);

    $content = \App\Models\Snippet::render($slotKey);
    abort_if(trim($content) === '', 404);

    return response()
        ->view('partials.ads.frame', [
            'slotKey' => $slotKey,
            'content' => $content,
        ])
        ->header('X-Robots-Tag', 'noindex, nofollow');
})->name('ads.frame');

Route::get('/reklam-ver', [AdvertiseController::class, 'create'])->name('advertise.create');
Route::post('/reklam-ver', [AdvertiseController::class, 'store'])->name('advertise.store');
Route::get('/reklam-ver/{adOrder}/odeme', [AdvertiseController::class, 'payment'])->name('advertise.payment');

Route::get('/locale/{locale}', function (string $locale, Request $request) {
    $availableLocales = array_keys(config('app.available_locales', []));
    abort_unless($locale === 'auto' || in_array($locale, $availableLocales, true), 404);

    $request->session()->put('locale_preference', $locale);

    if ($locale === 'auto') {
        $request->session()->forget('locale');
    } else {
        $request->session()->put('locale', $locale);
    }

    if ($request->user()) {
        $request->user()->forceFill([
            'preferred_locale' => $locale,
        ])->save();
    }

    return redirect()->back();
})->name('locale.switch');

// Legacy auth URL aliases (avoid 404 from old mail/template links)
Route::redirect('/user/login', '/login', 301);
Route::redirect('/user/forgot-password', '/forgot-password', 301);
Route::redirect('/user/reset-password', '/forgot-password', 301);
Route::get('/cerez-politikasi', [CookiePolicyController::class, 'show'])->name('cookie.policy');
Route::view('/4040', 'errors.404')->name('errors.preview.4040');
Route::view('/template/home-like', 'templates.home-like')->name('templates.home-like');

Route::get('/Categorys', [BlogController::class, 'categories'])->name('blog.categories');
Route::middleware('auth')->group(function () {
    Route::get('/Categorys/create', [BlogController::class, 'createCategory'])->name('blog.category.create');
    Route::post('/Categorys', [BlogController::class, 'storeCategory'])->name('blog.category.store');
});
Route::get('/Categorys/{category:slug}', [BlogController::class, 'category'])->name('blog.category');

Route::post('/Categorys/{category:slug}/join', [BlogController::class, 'toggleCategoryJoin'])
    ->middleware('auth')
    ->name('blog.category.join');

Route::middleware('auth')->group(function () {
    Route::get('/Categorys/{category:slug}/edit', [BlogController::class, 'editCategory'])->name('blog.category.edit');
    Route::put('/Categorys/{category:slug}', [BlogController::class, 'updateCategory'])->name('blog.category.update');
    Route::delete('/Categorys/{category:slug}', [BlogController::class, 'destroyCategory'])->name('blog.category.destroy');
});

Route::get('/c', function () {
    return redirect()->route('blog.categories', [], 301);
});

Route::get('/c/{category:slug}', function (Category $category) {
    // IMPORTANT: slug bekleyen route'a model basmayalım, slug string gönderelim.
    return redirect()->route('blog.category', ['category' => $category->slug], 301);
})->name('blog.category.c-legacy');

$socialFeedHandler = function () {
    $posts = Post::query()
        ->published()
        ->with([
            'category:id,name,slug,profile_image,cover_image',
            'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            'tags:id,name,slug',
        ])
        ->withCount(['comments', 'reactions'])
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
        ->take(100)
        ->get();

    return view('pages.social-feed-export', [
        'posts' => $posts,
    ]);
};

Route::get('/', function () {
    $userId = \Illuminate\Support\Facades\Auth::id();

    $posts = Post::query()
        ->published()
        ->with([
            'category:id,name,slug,profile_image,cover_image',
            'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            'tags:id,name,slug',
            'latestComment.user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
        ])
        ->withCount(['comments', 'reactions'])
        ->when($userId, function ($query) use ($userId) {
            $query->withExists([
                'bookmarkers as is_bookmarked' => fn ($inner) => $inner->where('users.id', $userId),
            ]);
        })
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
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

    app(\App\Services\PostCommentPreviewService::class)->attachToPosts($postsCollection);
    app(\App\Services\PostLinkPreviewService::class)->attachToPosts($postsCollection);

    $communityCategories = Category::query()
        ->withCount('posts')
        ->orderByDesc('posts_count')
        ->orderBy('name')
        ->take(10)
        ->get(['id', 'name', 'slug', 'profile_image']);

    $suggestedUsers = User::query()
        ->when($userId, fn ($query) => $query->whereKeyNot($userId))
        ->when($userId, function ($query) use ($userId) {
            $query->withExists([
                'followers as is_followed_by_viewer' => fn ($inner) => $inner->where('users.id', $userId),
            ]);
        })
        ->withCount(['followers'])
        ->orderByDesc('is_verified')
        ->orderByDesc('followers_count')
        ->orderByDesc('id')
        ->take(8)
        ->get([
            'id',
            'name',
            'username',
            'profile_photo_path',
            'is_verified',
            'verification_badge',
            'verification_badge_svg',
        ]);

    $suggestedCommunities = Category::query()
        ->when($userId, function ($query) use ($userId) {
            $query->withExists([
                'followers as is_joined_by_viewer' => fn ($inner) => $inner->where('users.id', $userId),
            ]);
        })
        ->withCount(['followers', 'posts'])
        ->orderByDesc('followers_count')
        ->orderByDesc('posts_count')
        ->orderBy('name')
        ->take(8)
        ->get([
            'id',
            'name',
            'slug',
            'profile_image',
        ]);

    $popularComments = Comment::query()
        ->approved()
        ->whereNull('parent_id')
        ->with([
            'user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            'post:id,title,slug',
        ])
        ->latest('created_at')
        ->take(5)
        ->get(['id', 'post_id', 'user_id', 'author_name', 'content', 'created_at']);

    $popularTags = Tag::query()
        ->withCount('posts')
        ->orderByDesc('posts_count')
        ->orderBy('name')
        ->take(5)
        ->get(['id', 'name', 'slug']);

    return view('templates.home-like', [
        'templateTitle' => 'Ografi',
        'posts' => $posts,
        'reactionTypes' => $reactionTypes,
        'communityCategories' => $communityCategories,
        'suggestedUsers' => $suggestedUsers,
        'suggestedCommunities' => $suggestedCommunities,
        'popularComments' => $popularComments,
        'popularTags' => $popularTags,
    ]);
})->name('home');
Route::get('/social-feed', $socialFeedHandler)->name('social-feed');

Route::get('/discover', function () {
    $viewerId = \Illuminate\Support\Facades\Auth::id();

    $featuredUsers = User::query()
        ->when($viewerId, fn ($query) => $query->whereKeyNot($viewerId))
        ->withCount(['followers'])
        ->orderByDesc('is_verified')
        ->orderByDesc('followers_count')
        ->orderByDesc('id')
        ->take(2)
        ->get([
            'id',
            'name',
            'username',
            'profile_photo_path',
            'is_verified',
            'verification_badge',
            'verification_badge_svg',
        ]);

    $featuredCommunities = Category::query()
        ->withCount(['followers', 'posts'])
        ->orderByDesc('followers_count')
        ->orderByDesc('posts_count')
        ->orderBy('name')
        ->take(3)
        ->get([
            'id',
            'name',
            'slug',
            'profile_image',
        ]);

    $reactionTypes = ReactionType::query()
        ->where('is_active', true)
        ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

    $recommendedPosts = Post::query()
        ->published()
        ->with([
            'category:id,name,slug,profile_image,cover_image',
            'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
        ])
        ->withCount(['comments', 'reactions'])
        ->when($viewerId, function ($query) use ($viewerId) {
            $query->withExists([
                'bookmarkers as is_bookmarked' => fn ($inner) => $inner->where('users.id', $viewerId),
            ]);
        })
        ->orderByDesc('is_pinned')
        ->orderByDesc('reactions_count')
        ->latest('published_at')
        ->take(6)
        ->get();

    if (!$viewerId) {
        $recommendedPosts->each->setAttribute('is_bookmarked', false);
    }

    app(\App\Services\PostCommentPreviewService::class)->attachToPosts($recommendedPosts);
    app(\App\Services\PostLinkPreviewService::class)->attachToPosts($recommendedPosts);

    $popularTags = Tag::withCount('posts')->orderByDesc('posts_count')->take(10)->get();
    $popularComments = Comment::query()
        ->whereNull('parent_id')
        ->with([
            'user:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
            'post:id,title,slug',
        ])
        ->latest('created_at')
        ->take(10)
        ->get(['id', 'post_id', 'user_id', 'author_name', 'content', 'created_at']);

    return view('pages.discover', compact(
        'featuredUsers',
        'featuredCommunities',
        'recommendedPosts',
        'reactionTypes',
        'popularTags',
        'popularComments',
    ));
})->name('discover');

Route::get('/video', function () {
    $viewerId = \Illuminate\Support\Facades\Auth::id();

    $applyViewerContext = function (Builder $query) use ($viewerId) {
        return $query->when($viewerId, function ($inner) use ($viewerId) {
            $inner->withExists([
                'bookmarkers as is_bookmarked' => fn ($bookmarkQuery) => $bookmarkQuery->where('users.id', $viewerId),
            ]);
        });
    };

    $videoLikeConditions = function (Builder $query) {
        foreach ([
            '%<video%',
            '%youtube.com%',
            '%youtu.be%',
            '%youtube-nocookie.com%',
            '%tiktok.com%',
            '%vimeo.com%',
            '%player.vimeo.com%',
            '%dailymotion.com%',
            '%dai.ly%',
            '%fb.watch%',
            '%facebook.com%',
        ] as $pattern) {
            $query->orWhere('content', 'like', $pattern)
                ->orWhere('content_json', 'like', $pattern);
        }

        foreach ([
            '%"type":"video"%',
            '%"type":"embed"%',
            '%"type":"socialEmbed"%',
            '%"service":"youtube"%',
            '%"service":"tiktok"%',
            '%"service":"vimeo"%',
            '%"service":"dailymotion"%',
            '%"service":"facebook"%',
        ] as $pattern) {
            $query->orWhere('content_json', 'like', $pattern);
        }
    };

    $hasVideoPost = function (Post $post): bool {
        $content = (string) ($post->content ?? '');
        if ($content !== '' && preg_match('/<video\b|youtube(?:-nocookie)?\.com|youtu\.be|tiktok\.com|vimeo\.com|dailymotion\.com|dai\.ly|facebook\.com|fb\.watch/i', $content)) {
            return true;
        }

        $blocks = collect(is_array($post->content_json) ? ($post->content_json['blocks'] ?? []) : []);
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            if (in_array($type, ['video', 'embed', 'socialEmbed'], true)) {
                return true;
            }

            $candidates = [
                $data['src'] ?? null,
                $data['embed'] ?? null,
                $data['source'] ?? null,
                $data['url'] ?? null,
                data_get($data, 'file.url'),
                data_get($data, 'video.url'),
                data_get($data, 'meta.source'),
                $data['service'] ?? null,
            ];

            foreach ($candidates as $candidate) {
                $candidate = trim((string) $candidate);
                if ($candidate !== '' && preg_match('/youtube|youtu\.be|tiktok|vimeo|dailymotion|dai\.ly|facebook|fb\.watch|\.mp4\b|\.webm\b|\.mov\b/i', $candidate)) {
                    return true;
                }
            }
        }

        return false;
    };

    $baseVideoQuery = Post::query()
        ->published()
        ->with([
            'category:id,name,slug,profile_image,cover_image',
            'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
        ])
        ->withCount(['comments', 'reactions'])
        ->where(function (Builder $query) use ($videoLikeConditions) {
            $videoLikeConditions($query);
        })
        ->orderByDesc('is_pinned')
        ->orderByDesc('reactions_count')
        ->latest('published_at');

    $videoPosts = $applyViewerContext(clone $baseVideoQuery)
        ->take(18)
        ->get()
        ->filter($hasVideoPost)
        ->values();

    if ($videoPosts->isEmpty()) {
        $videoPosts = $applyViewerContext(
            Post::query()
                ->published()
                ->with([
                    'category:id,name,slug,profile_image,cover_image',
                    'author:id,name,username,profile_photo_path,is_verified,verification_badge,verification_badge_svg',
                ])
                ->withCount(['comments', 'reactions'])
                ->orderByDesc('is_pinned')
                ->orderByDesc('reactions_count')
                ->latest('published_at')
        )
            ->take(40)
            ->get()
            ->filter($hasVideoPost)
            ->take(12)
            ->values();
    }

    if (!$viewerId) {
        $videoPosts->each->setAttribute('is_bookmarked', false);
    }

    app(\App\Services\PostCommentPreviewService::class)->attachToPosts($videoPosts);

    return view('video_player', compact('videoPosts'));
})->name('video');

Route::get('/robots.txt', function () {
    return response(
        implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
            '',
        ]),
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8']
    );
});

Route::get('/sitemap', function () {
    return redirect('/sitemap.xml', 301);
});
Route::get('/sitemap.xml', [BlogController::class, 'sitemapIndex'])->name('sitemap.index');
Route::get('/posts.xml', [BlogController::class, 'sitemapPosts'])->name('sitemap.posts');
Route::get('/news-sitemap.xml', [BlogController::class, 'sitemapNews'])->name('sitemap.news');
Route::get('/categories.xml', [BlogController::class, 'sitemapCategories'])->name('sitemap.categories');
Route::get('/tags.xml', [BlogController::class, 'sitemapTags'])->name('sitemap.tags');
Route::get('/pages.xml', [BlogController::class, 'sitemapPages'])->name('sitemap.pages');

Route::get('/search', SearchController::class)->name('search');
Route::get('/contact', [ContactSubmissionController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactSubmissionController::class, 'store'])->name('contact.store');

Route::get('/borsa/ticker', [BorsaController::class, 'ticker'])->name('borsa.ticker');
Route::get('/markets/{market}', fn (string $market) => redirect('/'))->name('markets.show');

Route::get('/rss', [RssController::class, 'index'])->name('rss');
Route::get('/rss/feed', [RssController::class, 'index'])->name('rss.feed');
Route::get('/feed', [RssController::class, 'index'])->name('feed');

Route::prefix('blog')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/posts', [BlogController::class, 'index'])->name('blog.posts');
    Route::get('/popular', [BlogController::class, 'popular'])->name('blog.popular');

    Route::get('/posts/{post:slug}', function (Post $post) {
        return redirect()->route('blog.post', ['post' => $post->slug], 301);
    })->name('blog.post.legacy');
    Route::get('/posts/{post:slug}/reactions', [BlogController::class, 'reactions'])->name('blog.post.reactions');
    Route::post('/posts/{post:slug}/reactions', [BlogController::class, 'storeReaction'])->name('blog.post.reaction');
    Route::post('/posts/{post:slug}/view', [BlogController::class, 'recordView'])->name('blog.post.view');
    Route::get('/posts/{post:slug}/viewers', [BlogController::class, 'viewers'])
        ->middleware('auth')
        ->name('blog.post.viewers');
    Route::post('/posts/{post:slug}/poll/vote', [BlogController::class, 'votePoll'])->name('blog.post.poll.vote');

    Route::post('/comments/{comment}/like', [BlogController::class, 'toggleCommentLike'])->name('blog.comment.like');
    Route::post('/comments/{comment}/dislike', [BlogController::class, 'toggleCommentDislike'])->name('blog.comment.dislike');
    Route::put('/comments/{comment}', [BlogController::class, 'updateComment'])->name('blog.comment.update');
    Route::delete('/comments/{comment}', [BlogController::class, 'destroyComment'])->name('blog.comment.delete');

    Route::get('/categories', function () {
        return redirect()->route('blog.categories', [], 301);
    })->name('blog.categories.legacy');

    Route::get('/kategori/{category:slug}', function (Category $category) {
        // IMPORTANT: slug bekleyen route'a model basmayalım, slug string gönderelim.
        return redirect()->route('blog.category', ['category' => $category->slug], 301);
    })->name('blog.category.legacy');

    Route::get('/tags', [BlogController::class, 'tags'])->name('blog.tags');

    Route::middleware('auth')->group(function () {
        Route::get('/create', [BlogController::class, 'create'])->name('blog.create');
        Route::get('/repost/{post?}', [BlogController::class, 'repostCreate'])->name('blog.repost.create');
        Route::post('/create', [BlogController::class, 'store'])->name('blog.store');
        Route::get('/posts/{post:slug}/edit', [BlogController::class, 'edit'])->name('blog.post.edit');
        Route::put('/posts/{post:slug}', [BlogController::class, 'update'])->name('blog.post.update');
        Route::get('/posts/{post:slug}/report', [PostReportController::class, 'create'])->name('blog.post.report.form');
        Route::post('/posts/{post:slug}/report', [PostReportController::class, 'store'])->name('blog.post.report');
        Route::post('/posts/{post:slug}/pin', [BlogController::class, 'togglePin'])->name('blog.post.pin');
        Route::post('/posts/{post:slug}/comments', [BlogController::class, 'storeComment'])->name('blog.post.comment');
        Route::get('/giphy/search', [BlogController::class, 'giphySearch'])->name('blog.giphy.search');
        Route::post('/posts/{post:slug}/bookmark', [BlogController::class, 'toggleBookmark'])->name('blog.post.bookmark');
        Route::delete('/posts/{post:slug}', [BlogController::class, 'destroy'])->name('blog.post.destroy');
        Route::get('/bookmarks', [BlogController::class, 'bookmarks'])->name('blog.bookmarks');
    });

    Route::post('/editorjs/image', [BlogController::class, 'editorJsImage'])
        ->withoutMiddleware([ValidatePostSize::class])
        ->name('blog.editorjs.image');
    Route::post('/editorjs/video', [BlogController::class, 'editorJsVideo'])
        ->withoutMiddleware([ValidatePostSize::class])
        ->name('blog.editorjs.video');
    Route::post('/editorjs/subtitle', [BlogController::class, 'editorJsSubtitle'])
        ->withoutMiddleware([ValidatePostSize::class])
        ->name('blog.editorjs.subtitle');
    Route::get('/editorjs/link', [BlogController::class, 'editorJsLink'])->name('blog.editorjs.link');
});

// Create sayfasi icin ek URL aliaslari
Route::get('/bloglog/create', function () {
    return redirect()->route('blog.create');
})->name('bloglog.create');

Route::get('/tr/create', function () {
    return redirect()->route('blog.create');
})->name('tr.create');

Route::view('/p/sss', 'pages.sss')->name('pages.sss');
Route::get('/sayfa/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show.short');

Route::get('/u/{user:username}', [UserController::class, 'show'])->name('users.show');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/u/{user:username}/follow', [UserController::class, 'toggleFollow'])
    ->middleware('auth')
    ->name('users.follow');

Route::match(['post', 'get'], '/u/{user:username}/block', [UserController::class, 'toggleBlock'])
    ->middleware('auth')
    ->name('users.block');

Route::get('/u/{user:username}/report', [ReportController::class, 'create'])
    ->middleware('auth')
    ->name('users.report.form');

Route::post('/u/{user:username}/report', [ReportController::class, 'store'])
    ->middleware('auth')
    ->name('users.report');

Route::middleware('auth')->prefix('messages')->name('messages.')->group(function () {
    Route::get('/dropdown', [MessageController::class, 'dropdown'])->name('dropdown');
    Route::get('/', [MessageController::class, 'index'])->name('index');
    Route::get('/contacts', [MessageController::class, 'contacts'])->name('contacts');
    Route::get('/settings', [MessageController::class, 'settings'])->name('settings');
    Route::post('/settings', [MessageController::class, 'updateSettings'])->name('settings.update');
    Route::post('/{user:username}/pin', [MessageController::class, 'togglePin'])->name('pin');
    Route::post('/{user:username}/delete', [MessageController::class, 'deleteThread'])->name('delete');
    Route::post('/message/{message}/delete', [MessageController::class, 'deleteMessage'])->name('message.delete');
    Route::get('/{user:username}', [MessageController::class, 'show'])->name('show');
    Route::post('/{user:username}', [MessageController::class, 'store'])->name('store');
});

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationsController::class, 'index'])->name('index');
    Route::get('/dropdown', [NotificationsController::class, 'dropdown'])->name('dropdown');
    Route::get('/{notificationId}/read', [NotificationsController::class, 'read'])->name('read');
    Route::post('/mark-all', [NotificationsController::class, 'markAll'])->name('mark-all');
    Route::post('/{notificationId}/delete', [NotificationsController::class, 'destroy'])->name('delete');
    Route::post('/delete-all', [NotificationsController::class, 'destroyAll'])->name('delete-all');
});

Route::middleware('auth')->prefix('mentions')->name('mentions.')->group(function () {
    Route::get('/users', [MentionController::class, 'users'])->name('users');
});

Route::get('/kullanici/{userId}', function ($userId) {
    $user = \App\Models\User::findOrFail($userId);

    // IMPORTANT: users.show route'u {user:username} bekliyor, username gönderelim.
    return redirect()->route('users.show', ['user' => $user->username], 301);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.page');

    Route::get('/profile/edit', function () {
        return view('profile.show');
    })->name('profile.edit');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/dashboard/account', function () {
        return view('dashboard.account');
    })->name('dashboard.account');

    Route::put('/dashboard/account', function (Request $request) {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', 'account-updated');
    })->name('dashboard.account.update');

    Route::get('/dashboard/password', function () {
        return view('dashboard.password');
    })->name('dashboard.password');

    Route::put('/dashboard/password', function (Request $request) {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('status', 'password-updated');
    })->name('dashboard.password.update');

    Route::get('/dashboard/profile', function () {
        return view('dashboard.profile');
    })->name('dashboard.profile');

    Route::put('/dashboard/profile', function (Request $request) {
        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'education' => ['nullable', 'string', 'max:255'],
            'profile_type' => ['nullable', 'string', 'in:person,organization'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_x' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_tiktok' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->bio = $validated['bio'] ?? null;
        $user->location = $validated['location'] ?? null;
        $user->company = $validated['company'] ?? null;
        $user->education = $validated['education'] ?? null;
        if (array_key_exists('profile_type', $validated)) {
            $user->profile_type = $validated['profile_type'] ?: 'person';
        }
        $user->website_url = $validated['website_url'] ?? null;
        $user->social_facebook = $validated['social_facebook'] ?? null;
        $user->social_x = $validated['social_x'] ?? null;
        $user->social_instagram = $validated['social_instagram'] ?? null;
        $user->social_tiktok = $validated['social_tiktok'] ?? null;
        $user->social_youtube = $validated['social_youtube'] ?? null;
        $user->save();
        app(BadgePointService::class)->awardProfileCompletion($user->fresh());

        return back()->with('status', 'profile-updated');
    })->name('dashboard.profile.update');

    Route::get('/dashboard/preferences', function () {
        return view('dashboard.preferences');
    })->name('dashboard.preferences');

    Route::put('/dashboard/preferences', function (Request $request) {
        $validated = $request->validate([
            'show_mature' => ['nullable', 'boolean'],
            'blur_mature' => ['nullable', 'boolean'],
            'open_new_tab' => ['nullable', 'boolean'],
            'preferred_locale' => ['required', 'string', Rule::in([
                'auto',
                ...array_keys(config('app.available_locales', [])),
            ])],
        ]);

        $preferredLocale = $validated['preferred_locale'];

        session([
            'locale_preference' => $preferredLocale,
            'dashboard_preferences' => [
                'show_mature' => (bool) ($validated['show_mature'] ?? false),
                'blur_mature' => (bool) ($validated['blur_mature'] ?? false),
                'open_new_tab' => (bool) ($validated['open_new_tab'] ?? false),
                'preferred_locale' => $preferredLocale,
            ],
        ]);

        $request->user()->forceFill([
            'preferred_locale' => $preferredLocale,
        ])->save();

        return back()->with('status', 'preferences-updated');
    })->name('dashboard.preferences.update');

    Route::get('/dashboard/notifications', function () {
        return view('dashboard.notifications');
    })->name('dashboard.notifications');

    Route::put('/dashboard/notifications', function (Request $request) {
        $validated = $request->validate([
            'comments' => ['nullable', 'boolean'],
            'replies' => ['nullable', 'boolean'],
            'likes' => ['nullable', 'boolean'],
            'followers' => ['nullable', 'boolean'],
            'mentions' => ['nullable', 'boolean'],
        ]);

        session([
            'dashboard_notifications' => [
                'comments' => (bool) ($validated['comments'] ?? false),
                'replies' => (bool) ($validated['replies'] ?? false),
                'likes' => (bool) ($validated['likes'] ?? false),
                'followers' => (bool) ($validated['followers'] ?? false),
                'mentions' => (bool) ($validated['mentions'] ?? false),
            ],
        ]);

        return back()->with('status', 'notifications-updated');
    })->name('dashboard.notifications.update');

    Route::get('/dashboard/blocks', function () {
        return view('dashboard.blocks');
    })->name('dashboard.blocks');

    Route::get('/dashboard/badges', function () {
        $user = auth()->user();
        $badges = $user?->earnedBadgesCollection() ?? collect();
        $nextBadge = $user?->nextBadge();

        return view('dashboard.badges', [
            'badges' => $badges,
            'nextBadge' => $nextBadge,
            'badgePoints' => (int) ($user?->badge_points ?? 0),
        ]);
    })->name('dashboard.badges');

    Route::get('/dashboard/two-factor-authentication', function () {
        return view('dashboard.two-factor-authentication');
    })->name('dashboard.two-factor');

    Route::get('/dashboard/delete-account', function () {
        return view('dashboard.delete-account');
    })->name('dashboard.delete-account');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings.index');
});

Route::get('/tr/{post:slug}', [BlogController::class, 'show'])->name('blog.post');

Route::middleware(['auth', 'throttle:10,1'])->post('/ai/ask', [AiController::class, 'ask'])
    ->name('ai.ask');


Route::middleware(['auth'])->get('/ai', function () {
    return view('ai.index');
})->name('ai.index');

Route::middleware(['auth', 'throttle:10,1'])->post('/ai/ask', [AiController::class, 'ask'])
    ->name('ai.ask');

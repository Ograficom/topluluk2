<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\PostReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SiteBrandingController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\RssReaderController;
use App\Http\Controllers\SocialAuthController;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::get('/pwa/icon/{icon}', [\App\Http\Controllers\PwaIconController::class, 'show'])
    ->where('icon', '192|512|maskable-512')->name('pwa.icon');

Route::get('/manifest.webmanifest', function () {
    $settings = \App\Models\SiteSetting::current();
    abort_unless($settings?->pwa_enabled ?? true, 404);

    $icons = \App\Http\Controllers\PwaIconController::manifestIcons($settings);
    $shortcutIcon = $icons[0];

    $startUrl = $settings?->pwa_start_url ?: '/';
    if (! str_starts_with($startUrl, '/') || str_starts_with($startUrl, '//')) $startUrl = '/';

    $advanced = $settings?->pwa_advanced ?? [];
    $display = $settings?->pwa_display ?: 'standalone';
    $desktopModes = ($advanced['desktop_mode'] ?? 'overlay') === 'tabbed'
        ? ['tabbed', 'window-controls-overlay', 'standalone', 'minimal-ui']
        : ['window-controls-overlay', 'tabbed', 'standalone', 'minimal-ui'];

    $manifest = [
        'id' => '/',
        'name' => $settings?->pwa_name ?: 'OgraFi',
        'short_name' => $settings?->pwa_short_name ?: 'OgraFi',
        'description' => $settings?->pwa_description ?: 'OgraFi topluluk uygulaması',
        'lang' => 'tr-TR',
        'dir' => 'ltr',
        'start_url' => $startUrl,
        'scope' => '/',
        'display' => $settings?->pwa_display ?: 'standalone',
        'display_override' => $display === 'standalone' ? $desktopModes : [$display],
        'launch_handler' => ['client_mode' => ['navigate-existing', 'auto']],
        'edge_side_panel' => ['preferred_width' => 480],
        'tab_strip' => ['new_tab_button' => ['url' => '/last-posts']],
        'related_applications' => [
            ['platform' => 'play', 'id' => 'com.ografi.twa', 'url' => 'https://play.google.com/store/apps/details?id=com.ografi.twa'],
        ],
        'note_taking' => ['new_note_url' => '/pwa/notes'],
        'share_target' => [
            'action' => '/posts/create',
            'method' => 'GET',
            'enctype' => 'application/x-www-form-urlencoded',
            'params' => ['title' => 'share_title', 'text' => 'share_text', 'url' => 'share_url'],
        ],
        'file_handlers' => [
            ['action' => '/posts/create?open_files=1', 'accept' => [
                'image/jpeg' => ['.jpg', '.jpeg'],
                'image/png' => ['.png'],
                'image/webp' => ['.webp'],
                'image/gif' => ['.gif'],
            ]],
        ],
        'protocol_handlers' => [
            ['protocol' => 'web+ografi', 'url' => '/pwa/open?uri=%s'],
        ],
        'orientation' => $settings?->pwa_orientation ?: 'any',
        'background_color' => $settings?->pwa_background_color ?: '#f7f9fd',
        'theme_color' => $settings?->pwa_theme_color ?: '#2563eb',
        'categories' => ['social', 'news'],
        'icons' => $icons,
        'shortcuts' => [
            ['name' => 'Gönderi oluştur', 'short_name' => 'Yeni gönderi', 'description' => 'Yeni bir gönderi yaz', 'url' => '/posts/create', 'icons' => [$shortcutIcon]],
            ['name' => 'Bildirimler', 'short_name' => 'Bildirimler', 'description' => 'Bildirimlerini görüntüle', 'url' => '/notifications', 'icons' => [$shortcutIcon]],
            ['name' => 'Mesajlar', 'short_name' => 'Mesajlar', 'description' => 'Sohbetlerini aç', 'url' => '/messenger', 'icons' => [$shortcutIcon]],
            ['name' => 'Son gönderiler', 'short_name' => 'Son gönderiler', 'description' => 'Topluluktaki son gönderileri keşfet', 'url' => '/last-posts', 'icons' => [$shortcutIcon]],
        ],
        'widgets' => [
            [
                'name' => 'OgraFi Hızlı Erişim',
                'short_name' => 'OgraFi',
                'description' => 'Son gönderilere, kanallara ve yeni gönderi oluşturmaya hızlı eriş.',
                'tag' => 'ografi-quick-access',
                'template' => 'ografi-quick-access',
                'ms_ac_template' => '/widgets/quick-access.json',
                'data' => '/pwa/widget-data',
                'icons' => [$shortcutIcon],
                'update' => 3600,
                'type' => 'application/json',
                'auth' => false,
                'multiple' => false,
                'screenshots' => [
                    ['src' => '/widgets/quick-access-preview.png', 'sizes' => '320x320', 'type' => 'image/png', 'platform' => 'Windows', 'label' => 'OgraFi hızlı erişim widgetı'],
                ],
            ],
        ],
        'screenshots' => [
            [
                'src' => '/assets/img/pwa/feed-desktop.jpg',
                'sizes' => '960x540',
                'type' => 'image/jpeg',
                'form_factor' => 'wide',
                'label' => 'OgraFi ana akışı',
            ],
            [
                'src' => '/assets/img/pwa/channels-desktop.jpg',
                'sizes' => '960x540',
                'type' => 'image/jpeg',
                'form_factor' => 'wide',
                'label' => 'OgraFi kanalları',
            ],
            [
                'src' => '/assets/img/pwa/feed-mobile.jpg',
                'sizes' => '390x844',
                'type' => 'image/jpeg',
                'form_factor' => 'narrow',
                'label' => 'OgraFi mobil ana akışı',
            ],
        ],
        'prefer_related_applications' => false,
    ];
    if (filled($advanced['iarc_rating_id'] ?? null)) {
        $manifest['iarc_rating_id'] = $advanced['iarc_rating_id'];
    }
    $origins = \App\Support\PwaCapabilities::scopeOrigins($advanced['scope_origins'] ?? '');
    if ($origins !== []) {
        $manifest['scope_extensions'] = array_map(fn (string $origin) => ['type' => 'origin', 'origin' => $origin], $origins);
    }

    return response()->json($manifest)->header('Content-Type', 'application/manifest+json; charset=UTF-8')
      ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
})->name('pwa.manifest');

// Device-local notes and public widget data do not use authenticated content.
Route::view('/pwa/notes', 'pwa.notes')->name('pwa.notes');
Route::get('/pwa/widget-data', function () {
    $headlines = \App\Models\Post::query()->published()->whereNull('advertisement_id')
        ->where('is_adult', false)->where('is_spam', false)
        ->latest('published_at')->limit(3)->pluck('title')->map(fn ($title) => \Illuminate\Support\Str::limit(strip_tags($title), 100))->values();
    return response()->json([
        'headline' => 'Son gönderiler',
        'description' => 'Gerçekleşmiş Hayaller',
        'latest' => $headlines->map(fn ($title) => '• '.$title)->implode("\n") ?: 'Yeni gönderileri keşfet.',
    ])->header('Cache-Control', 'no-store');
})->name('pwa.widget-data');

// Registered links may open only known, same-origin pages.
Route::get('/pwa/open', function (\Illuminate\Http\Request $request) {
    $uri = $request->query('uri');
    abort_unless(is_string($uri) && strlen($uri) <= 2048, 400);
    $parts = parse_url($uri);
    abort_unless(is_array($parts) && ($parts['scheme'] ?? '') === 'web+ografi', 400);
    $target = match ($parts['host'] ?? '') {
        'home' => '/',
        'latest' => '/last-posts',
        'channels' => '/channels',
        'create' => '/posts/create',
        'notifications' => '/notifications',
        'messages' => '/messenger',
        default => null,
    };
    abort_unless($target !== null, 400);
    return redirect($target);
})->name('pwa.open');

Route::get('/offline', function () {
    return response()->view('pwa.offline', [
        'settings' => \App\Models\SiteSetting::current(),
    ])->header('Cache-Control', 'no-store');
})->name('pwa.offline');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/rss', [RssController::class, 'all'])->name('rss.all');
Route::get('/rss.xml', fn () => redirect()->route('rss.all', status: 301))->name('rss.xml');
Route::get('/feed/latest.xml', [RssController::class, 'latest'])->name('rss.latest');
Route::get('/feed/authors/{username}.xml', [RssController::class, 'author'])->name('rss.author');
Route::get('/feed/channels/{slug}.xml', [RssController::class, 'channel'])->name('rss.channel');
Route::get('/feed/tags/{slug}.xml', [RssController::class, 'tag'])->name('rss.tag');
Route::get('/robots.txt', function () {
    $content = "User-agent: *
";
    $content .= "Allow: /
";
    $content .= "Disallow: /admin
";
    $content .= "Disallow: /signin
";
    $content .= "Disallow: /signup
";
    $content .= "Sitemap: ".url('/sitemap.xml')."
";

    return response($content, 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
});

Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/index.html', [PostController::class, 'index'])->name('legacy.home');
Route::get('/channels.html', [ChannelController::class, 'index'])->name('legacy.channels');
Route::get('/channel/{channel:slug}', [ChannelController::class, 'show'])->name('legacy.channel');
Route::get('/last-posts.html', [CommunityController::class, 'latestPosts'])->name('legacy.last-posts');
Route::get('/last-comments.html', [CommunityController::class, 'latestComments'])->name('legacy.last-comments');
Route::get('/rating-users.html', [MemberController::class, 'index'])->name('legacy.rating-users');
Route::get('/favorites.html', fn () => redirect()->route('favorites.index'))->middleware('auth')->name('legacy.favorites');
Route::get('/messenger.html', fn () => redirect()->route('messenger.index'))->middleware('auth')->name('legacy.messenger');
Route::get('/dialog.html', fn () => redirect()->route('messenger.index'))->middleware('auth')->name('legacy.dialog');
Route::get('/lostpassword.html', fn () => redirect()->route('password.request'))->middleware('guest')->name('legacy.password');
Route::get('/contacts.html', [CommunityController::class, 'contacts'])->name('legacy.contacts');
Route::get('/page.html', fn () => redirect()->route('pages.show', 'about'))->name('legacy.page');
Route::get('/tagscloud.html', [CommunityController::class, 'tags'])->name('legacy.tags');
Route::get('/tags-catalog.html', [CommunityController::class, 'tags'])->name('legacy.tag-catalog');
Route::get('/subscriptions.html', fn () => redirect()->route('subscriptions.index'))->middleware('auth')->name('legacy.subscriptions');
Route::get('/stats.html', [StatisticsController::class, 'index'])->name('stats.index');

Route::get('/members', [MemberController::class, 'index'])->name('members.index');
Route::get('/gorevler', [TaskController::class, 'index'])->name('tasks.index');
Route::get('/site-branding/logo', [SiteBrandingController::class, 'logo'])->name('site-branding.logo');
Route::get('/site-branding/favicon', [SiteBrandingController::class, 'favicon'])->name('site-branding.favicon');
Route::get('/last-posts', [CommunityController::class, 'latestPosts'])->name('posts.latest');
Route::get('/last-comments', [CommunityController::class, 'latestComments'])->name('comments.latest');
Route::get('/tags', [CommunityController::class, 'tags'])->name('tags.index');
Route::get('/tags/{tag}', [CommunityController::class, 'tag'])->where('tag', '.*')->name('tags.show');
Route::get('/tags-catalog/{tag}', [CommunityController::class, 'tag'])->where('tag', '.*')->name('legacy.tag');
// Canonical public URL for dynamically managed site pages.
Route::get('/page/{slug}', [CommunityController::class, 'page'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('pages.show');

Route::get('/page/{slug}/feedback', [\App\Http\Controllers\SitePageFeedbackController::class, 'show'])
    ->where('slug', '[A-Za-z0-9_-]+')->middleware('throttle:60,1')->name('pages.feedback.show');
Route::post('/page/{slug}/feedback', [\App\Http\Controllers\SitePageFeedbackController::class, 'store'])
    ->where('slug', '[A-Za-z0-9_-]+')->middleware('throttle:15,1')->name('pages.feedback.store');

// Keep previously shared plural URLs working.
Route::get('/pages/{slug}', fn (string $slug) => redirect()->route('pages.show', ['slug' => $slug], 301))
    ->where('slug', '[A-Za-z0-9_-]+');
Route::get('/about', fn () => redirect()->route('pages.show', 'about'));
Route::get('/rules', fn () => redirect()->route('pages.show', 'rules'));
Route::get('/privacy', fn () => redirect()->route('pages.show', 'privacy'));
Route::get('/contacts', [CommunityController::class, 'contacts'])->name('contacts');
Route::get('/santral', function () {
    $santralSettings = \Illuminate\Support\Facades\Schema::hasTable('santral_page_settings')
        ? \App\Models\SantralPageSetting::query()->find(1)
        : null;

    return view('pages.santral', [
        'santralSettings' => $santralSettings,
    ]);
})->name('santral');
Route::post('/contacts', [CommunityController::class, 'sendContact'])->middleware('throttle:5,1')->name('contacts.send');

Route::get('/support-tickets/{supportTicket}/attachments/{index}/preview', function (SupportTicket $supportTicket, int $index) {
    abort_unless(auth()->user()?->is_admin, 403);

    $disk = Storage::disk('local');
    $path = $supportTicket->attachments[$index] ?? null;
    abort_unless($path && $disk->exists($path), 404);

    $mimeType = $disk->mimeType($path);
    abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true), 404);

    return response()->file($disk->path($path), [
        'Content-Type' => $mimeType,
        'X-Content-Type-Options' => 'nosniff',
        'Content-Disposition' => 'inline; filename="' . addslashes(basename($path)) . '"',
    ]);
})->middleware('auth')->whereNumber('index')->name('support-tickets.attachments.preview');

Route::get('/support-tickets/{supportTicket}/attachments/{index}', function (SupportTicket $supportTicket, int $index) {
    abort_unless(auth()->user()?->is_admin, 403);

    $path = $supportTicket->attachments[$index] ?? null;
    abort_unless($path && Storage::disk('local')->exists($path), 404);

    return Storage::disk('local')->download($path, basename($path));
})->middleware('auth')->whereNumber('index')->name('support-tickets.attachments.download');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->middleware('throttle:10,1')->name('oauth.callback');

Route::middleware('auth')->group(function (): void {
    // Users must be able to log out even when their email is not verified.
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');});

Route::middleware('guest')->group(function (): void {
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->middleware('throttle:10,1')->name('oauth.redirect');

    Route::get('/signin', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/signin.html', [AuthController::class, 'showLogin'])->name('legacy.signin');
    Route::post('/signin', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/username/check', [AuthController::class, 'checkUsername'])->middleware('throttle:30,1')->name('username.check');
    Route::get('/signup', [AuthController::class, 'showRegister'])->name('register');
    Route::get('/signup.html', [AuthController::class, 'showRegister'])->name('legacy.signup');
    Route::post('/signup', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/two-factor-challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactorChallenge'])->middleware('throttle:10,1')->name('two-factor.challenge.verify');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::post('/notifications/push-subscriptions', [NotificationController::class, 'storePushSubscription'])->middleware('throttle:10,1')->name('notifications.push-subscriptions.store');
    Route::delete('/notifications/push-subscriptions', [NotificationController::class, 'destroyPushSubscription'])->middleware('throttle:10,1')->name('notifications.push-subscriptions.destroy');
    Route::post('/gorevler/{taskKey}/katil', [TaskController::class, 'submit'])->middleware('throttle:10,1')->where('taskKey', '[A-Za-z0-9_-]{1,120}')->name('tasks.submit');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->middleware('throttle:30,1')->name('notifications.feed');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/unread-all', [NotificationController::class, 'markAllUnread'])->name('notifications.unread-all');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/{notification}/unread', [NotificationController::class, 'markUnread'])->name('notifications.mark-unread');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/presence/ping', function () {
        auth()->user()->forceFill(['last_seen_at' => now()])->saveQuietly();

        return response()->noContent();
    })->middleware('throttle:2,1')->name('presence.ping');

    Route::get('/following', [CommunityController::class, 'following'])->name('community.following');
    Route::post('/last-comments/moderate', [CommunityController::class, 'moderateLatestComments'])->name('comments.moderate');
    Route::get('/subscriptions', [CommunityController::class, 'subscriptions'])->name('subscriptions.index');
    Route::get('/favorites', [CommunityController::class, 'favorites'])->name('favorites.index');
    Route::post('/users/{target}/follow', [CommunityController::class, 'toggleFollow'])->middleware('throttle:30,1')->name('users.follow');
    Route::post('/channels/{channel:slug}/subscribe', [CommunityController::class, 'toggleChannelSubscription'])->middleware('throttle:30,1')->name('channels.subscribe');
    Route::post('/users/{recipient}/message', [CommunityController::class, 'startConversation'])->middleware('throttle:20,1')->name('messenger.start');

    Route::get('/messenger', [CommunityController::class, 'messenger'])->name('messenger.index');
    Route::get('/messenger/gifs/search', [CommunityController::class, 'searchGifs'])->middleware('throttle:30,1')->name('messenger.gifs.search');
    Route::get('/messenger/{conversation}/messages/{message}/attachment', [CommunityController::class, 'downloadMessageAttachment'])->name('messenger.messages.attachment');
    Route::post('/messenger/{conversation}/action', [CommunityController::class, 'conversationAction'])->middleware('throttle:30,1')->name('messenger.conversations.action');
    Route::get('/messenger/{conversation}', [CommunityController::class, 'showConversation'])->name('messenger.show');
    Route::post('/messenger/{conversation}/messages', [CommunityController::class, 'sendMessage'])->middleware('throttle:30,1')->name('messenger.messages.store');
    Route::patch('/messenger/{conversation}/messages/{message}', [CommunityController::class, 'updateMessage'])->middleware('throttle:30,1')->name('messenger.messages.update');
    Route::delete('/messenger/{conversation}/messages/{message}', [CommunityController::class, 'deleteMessage'])->middleware('throttle:30,1')->name('messenger.messages.delete');

    Route::post('/posts/{post:slug}/bookmark', [CommunityController::class, 'toggleBookmark'])->middleware('throttle:30,1')->name('posts.bookmarks.toggle');
    Route::post('/posts/{post:slug}/repost', [PostInteractionController::class, 'repost'])->middleware('throttle:20,1')->name('posts.reposts.toggle');
    Route::post('/posts/{post:slug}/report', [PostReportController::class, 'store'])->middleware('throttle:5,10')->name('posts.report');
    Route::get('/advertisements', [AdvertisementController::class, 'index'])->name('advertisements.index');
    Route::get('/advertisements/create', [AdvertisementController::class, 'create'])->name('advertisements.create');
    Route::get('/advertisements/{advertisement}/edit', [AdvertisementController::class, 'edit'])->name('advertisements.edit');
    Route::put('/advertisements/{advertisement}', [AdvertisementController::class, 'update'])->middleware('throttle:10,1')->name('advertisements.update');
    Route::post('/advertisements', [AdvertisementController::class, 'store'])->middleware('throttle:5,1')->name('advertisements.store');

    Route::get('/composer/tag-suggestions', [PostController::class, 'tagSuggestions'])->middleware('throttle:90,1')->name('posts.tag-suggestions');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->middleware('throttle:10,1')->name('posts.store');
    Route::get('/posts/{post:slug}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('/posts/{post:slug}', [PostController::class, 'update'])->name('posts.update');

    Route::get('/settings/social/{provider}/redirect', [SocialAuthController::class, 'linkRedirect'])->middleware('throttle:10,1')->name('oauth.link.redirect');
    Route::delete('/settings/social/{provider}', [SocialAuthController::class, 'unlink'])->middleware('throttle:10,1')->name('oauth.unlink');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::get('/settings/two-factor', fn () => redirect()->route('settings.edit', ['tab' => 'security']))->name('settings.two-factor');
    Route::get('/setting.html', [SettingsController::class, 'edit'])->name('settings.legacy');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::patch('/settings/username', [SettingsController::class, 'updateUsername'])->name('settings.username.update');
    Route::patch('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences.update');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::patch('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
    Route::post('/settings/two-factor', [SettingsController::class, 'startTwoFactor'])->name('settings.two-factor.start');
    Route::post('/settings/two-factor/confirm', [SettingsController::class, 'confirmTwoFactor'])->middleware('throttle:10,1')->name('settings.two-factor.confirm');
    Route::delete('/settings/two-factor', [SettingsController::class, 'disableTwoFactor'])->middleware('throttle:10,1')->name('settings.two-factor.disable');
    Route::post('/settings/verification', [SettingsController::class, 'requestVerification'])->name('settings.verification.request');
    Route::delete('/settings/verification', [SettingsController::class, 'cancelVerification'])->name('settings.verification.cancel');
    Route::delete('/settings/blocked-users/{blockedUser}', [SettingsController::class, 'unblockUser'])->name('settings.blocked-users.destroy');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])->name('settings.account.destroy');

    Route::post('/users/{blockedUser}/block', [SettingsController::class, 'blockUser'])->name('users.block');

    Route::post('/posts/{post:slug}/comments', [PostInteractionController::class, 'comment'])->middleware('throttle:10,1')->name('posts.comments.store');
    Route::patch('/posts/{post:slug}/comments/{comment}', [PostInteractionController::class, 'updateComment'])->middleware('throttle:10,1')->name('posts.comments.update');
    Route::post('/posts/{post:slug}/comments/moderate', [PostInteractionController::class, 'moderateComments'])->name('posts.comments.moderate');
    Route::post('/posts/{post:slug}/comments/{comment}/reactions/like', [PostInteractionController::class, 'reactToComment'])->middleware('throttle:30,1')->name('posts.comments.reactions.like');
    Route::post('/posts/{post:slug}/reactions/{type}', [PostInteractionController::class, 'react'])->whereIn('type', ['like', 'dislike'])->middleware('throttle:30,1')->name('posts.reactions.store');

    Route::post('/admin/site-branding/logo', [SiteBrandingController::class, 'updateLogo'])->middleware('throttle:10,1')->name('admin.site-branding.logo.update');
    Route::post('/admin/site-branding/favicon', [SiteBrandingController::class, 'updateFavicon'])->middleware('throttle:10,1')->name('admin.site-branding.favicon.update');
});

Route::post('/advertisements/{advertisement}/impression', [AdvertisementController::class, 'trackImpression'])->middleware('throttle:120,1')->name('advertisements.impression');
Route::get('/advertisements/{advertisement}', [AdvertisementController::class, 'show'])->name('advertisements.show');
Route::post('/advertisements/{advertisement}/read', [AdvertisementController::class, 'trackRead'])->middleware('throttle:10,1')->name('advertisements.read');
Route::get('/advertisements/{advertisement}/click', [AdvertisementController::class, 'click'])->middleware('throttle:60,1')->name('advertisements.click');
Route::get('/posts/{post:slug}/edit-history', [PostController::class, 'editHistory'])->middleware('throttle:60,1')->name('posts.edit-history');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
Route::get('/channels/create', [ChannelController::class, 'create'])->middleware('auth')->name('channels.create');
Route::get('/channels/{channel:slug}/edit', [ChannelController::class, 'edit'])->middleware('auth')->name('channels.edit');
Route::patch('/channels/{channel:slug}', [ChannelController::class, 'update'])->middleware(['auth', 'throttle:10,1'])->name('channels.update');
Route::post('/channels', [ChannelController::class, 'store'])->middleware(['auth', 'throttle:10,1'])->name('channels.store');
Route::get('/channels/{channel:slug}', [ChannelController::class, 'show'])->name('channels.show');
Route::get('/@{identifier}', [ProfileController::class, 'show'])->name('profiles.show');
Route::get('/users/{identifier}', [ProfileController::class, 'show'])->name('profiles.legacy');

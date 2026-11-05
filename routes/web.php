<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SiteBrandingController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

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
Route::get('/site-branding/logo', [SiteBrandingController::class, 'logo'])->name('site-branding.logo');
Route::get('/site-branding/favicon', [SiteBrandingController::class, 'favicon'])->name('site-branding.favicon');
Route::get('/last-posts', [CommunityController::class, 'latestPosts'])->name('posts.latest');
Route::get('/last-comments', [CommunityController::class, 'latestComments'])->name('comments.latest');
Route::get('/tags', [CommunityController::class, 'tags'])->name('tags.index');
Route::get('/tags/{tag}', [CommunityController::class, 'tag'])->where('tag', '.*')->name('tags.show');
Route::get('/tags-catalog/{tag}', [CommunityController::class, 'tag'])->where('tag', '.*')->name('legacy.tag');
Route::get('/pages/{slug}', [CommunityController::class, 'page'])->where('slug', '[A-Za-z0-9_-]+')->name('pages.show');
Route::get('/about', fn () => redirect()->route('pages.show', 'about'));
Route::get('/rules', fn () => redirect()->route('pages.show', 'rules'));
Route::get('/privacy', fn () => redirect()->route('pages.show', 'privacy'));
Route::get('/contacts', [CommunityController::class, 'contacts'])->name('contacts');
Route::post('/contacts', [CommunityController::class, 'sendContact'])->middleware('throttle:5,1')->name('contacts.send');

Route::middleware('guest')->group(function (): void {
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->middleware('throttle:10,1')->name('oauth.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->middleware('throttle:10,1')->name('oauth.callback');

    Route::get('/signin', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/signin.html', [AuthController::class, 'showLogin'])->name('legacy.signin');
    Route::post('/signin', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/signup', [AuthController::class, 'showRegister'])->name('register');
    Route::get('/signup.html', [AuthController::class, 'showRegister'])->name('legacy.signup');
    Route::post('/signup', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/following', [CommunityController::class, 'following'])->name('community.following');
    Route::post('/last-comments/moderate', [CommunityController::class, 'moderateLatestComments'])->name('comments.moderate');
    Route::get('/subscriptions', [CommunityController::class, 'subscriptions'])->name('subscriptions.index');
    Route::get('/favorites', [CommunityController::class, 'favorites'])->name('favorites.index');
    Route::post('/users/{target}/follow', [CommunityController::class, 'toggleFollow'])->middleware('throttle:30,1')->name('users.follow');
    Route::post('/channels/{channel:slug}/subscribe', [CommunityController::class, 'toggleChannelSubscription'])->middleware('throttle:30,1')->name('channels.subscribe');
    Route::post('/users/{recipient}/message', [CommunityController::class, 'startConversation'])->middleware('throttle:20,1')->name('messenger.start');

    Route::get('/messenger', [CommunityController::class, 'messenger'])->name('messenger.index');
    Route::get('/messenger/{conversation}', [CommunityController::class, 'showConversation'])->name('messenger.show');
    Route::post('/messenger/{conversation}/messages', [CommunityController::class, 'sendMessage'])->middleware('throttle:30,1')->name('messenger.messages.store');

    Route::post('/posts/{post:slug}/bookmark', [CommunityController::class, 'toggleBookmark'])->middleware('throttle:30,1')->name('posts.bookmarks.toggle');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->middleware('throttle:10,1')->name('posts.store');
    Route::get('/posts/{post:slug}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('/posts/{post:slug}', [PostController::class, 'update'])->name('posts.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::get('/setting.html', [SettingsController::class, 'edit'])->name('settings.legacy');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::patch('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences.update');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::patch('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
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

Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
Route::get('/channels/create', [ChannelController::class, 'create'])->middleware('auth')->name('channels.create');
Route::get('/channels/{channel:slug}/edit', [ChannelController::class, 'edit'])->middleware('auth')->name('channels.edit');
Route::patch('/channels/{channel:slug}', [ChannelController::class, 'update'])->middleware(['auth', 'throttle:10,1'])->name('channels.update');
Route::post('/channels', [ChannelController::class, 'store'])->middleware(['auth', 'throttle:10,1'])->name('channels.store');
Route::get('/channels/{channel:slug}', [ChannelController::class, 'show'])->name('channels.show');
Route::get('/users/{identifier}', [ProfileController::class, 'show'])->name('profiles.show');

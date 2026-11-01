<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SiteBrandingController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('posts.index');

// Repost HTML demo dosya adlariyla uyumlu eski adresler.
Route::get('/index.html', [PostController::class, 'index'])->name('legacy.home');
Route::get('/channels.html', [ChannelController::class, 'index'])->name('legacy.channels');
Route::get('/last-posts.html', [PostController::class, 'index'])->defaults('sort', 'latest')->name('legacy.last-posts');
Route::get('/rating-users.html', [MemberController::class, 'index'])->name('legacy.rating-users');

Route::get('/stats.html', [StatisticsController::class, 'index'])->name('stats.index');
Route::get('/members', [MemberController::class, 'index'])->name('members.index');
Route::middleware('auth')->group(function (): void {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('posts.store');
});
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
Route::get('/channels/create', [ChannelController::class, 'create'])->middleware('auth')->name('channels.create');
Route::post('/channels', [ChannelController::class, 'store'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('channels.store');
Route::get('/channels/{channel:slug}', [ChannelController::class, 'show'])->name('channels.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/signin', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/signin.html', [AuthController::class, 'showLogin'])->name('legacy.signin');
    Route::post('/signin', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.store');
    Route::get('/signup', [AuthController::class, 'showRegister'])->name('register');
    Route::get('/signup.html', [AuthController::class, 'showRegister'])->name('legacy.signup');
    Route::post('/signup', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::post('/admin/site-branding/logo', [SiteBrandingController::class, 'updateLogo'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('admin.site-branding.logo.update');
Route::post('/admin/site-branding/favicon', [SiteBrandingController::class, 'updateFavicon'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('admin.site-branding.favicon.update');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::get('/setting.html', [SettingsController::class, 'edit'])->name('settings.legacy');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->name('settings.profile.update');
    Route::patch('/settings/preferences', [SettingsController::class, 'updatePreferences'])
        ->name('settings.preferences.update');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('settings.password.update');
    Route::patch('/settings/email', [SettingsController::class, 'updateEmail'])
        ->name('settings.email.update');
    Route::post('/settings/verification', [SettingsController::class, 'requestVerification'])
        ->name('settings.verification.request');
    Route::delete('/settings/verification', [SettingsController::class, 'cancelVerification'])
        ->name('settings.verification.cancel');
    Route::delete('/settings/blocked-users/{blockedUser}', [SettingsController::class, 'unblockUser'])
        ->name('settings.blocked-users.destroy');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])
        ->name('settings.account.destroy');

    Route::post('/users/{blockedUser}/block', [SettingsController::class, 'blockUser'])
        ->name('users.block');

    Route::post('/posts/{post:slug}/comments', [PostInteractionController::class, 'comment'])
        ->middleware('throttle:10,1')
        ->name('posts.comments.store');
    Route::post('/posts/{post:slug}/comments/{comment}/reactions/like', [PostInteractionController::class, 'reactToComment'])
        ->middleware('throttle:30,1')
        ->name('posts.comments.reactions.like');
    Route::post('/posts/{post:slug}/reactions/{type}', [PostInteractionController::class, 'react'])
        ->whereIn('type', ['like', 'dislike'])
        ->middleware('throttle:30,1')
        ->name('posts.reactions.store');
});

Route::get('/users/{identifier}', [ProfileController::class, 'show'])
    ->name('profiles.show');

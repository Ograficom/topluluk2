<?php

namespace App\Providers;

use App\Models\Channel;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\SiteSetting;
use App\Models\MailSetting;
use App\Models\SitePage;
use App\Models\Tag;
use App\Observers\SitemapObserver;
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
        try {
            $mailSettings = MailSetting::query()->find(1);

            if ($mailSettings) {
                config([
                    'mail.default' => $mailSettings->enabled ? $mailSettings->mailer : 'log',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.scheme' => $mailSettings->scheme,
                    'mail.mailers.smtp.host' => $mailSettings->host,
                    'mail.mailers.smtp.port' => $mailSettings->port,
                    'mail.mailers.smtp.username' => $mailSettings->username,
                    'mail.mailers.smtp.password' => $mailSettings->password,
                    'mail.from.address' => $mailSettings->from_address,
                    'mail.from.name' => $mailSettings->from_name,
                ]);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        Post::observe(SitemapObserver::class);
        Channel::observe(SitemapObserver::class);
        Tag::observe(SitemapObserver::class);
        SitePage::observe(SitemapObserver::class);

        View::composer('layouts.app', function ($view): void {
            $viewer = auth()->user();
            $siteSettings = SiteSetting::current();

            $view->with([
                'brandName' => $siteSettings?->brand_name ?? SiteSetting::DEFAULT_BRAND_NAME,
                'brandLogoUrl' => $siteSettings?->brandLogoUrl(),
                'faviconUrl' => $siteSettings?->faviconUrl(),
                'faviconMimeType' => $siteSettings?->faviconMimeType(),
                'navigationChannels' => Channel::query()->active()->orderBy('name')->limit(8)->get(),
                'sidebarAuthors' => User::query()
                    ->whereNotNull('username')
                    ->where('username', '<>', '')
                    ->withCount('posts')
                    ->orderByDesc('posts_count')
                    ->limit(5)
                    ->get(),
                'popularTags' => Post::query()
                    ->published()
                    ->latest('published_at')
                    ->limit(200)
                    ->pluck('tags')
                    ->flatMap(fn ($tags) => $tags ?? [])
                    ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
                    ->countBy()
                    ->sortDesc()
                    ->take(8),
                'sidebarPosts' => Post::query()
                    ->published()
                    ->with('user')
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

        View::composer('layouts._footer', function ($view): void {
            $siteSettings = SiteSetting::current();

            $view->with([
                'brandName' => $siteSettings?->brand_name ?? SiteSetting::DEFAULT_BRAND_NAME,
                'footerYear' => $siteSettings?->footer_year ?? now()->year,
                'footerSocialLinks' => [
                    'instagram' => $siteSettings?->instagram_url,
                    'telegram' => $siteSettings?->telegram_url,
                    'threads' => $siteSettings?->threads_url,
                ],
            ]);
        });
    }
}

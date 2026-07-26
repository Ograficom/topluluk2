<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Message;
use App\Models\BorsaSetting;
use App\Services\BorsaService;
use App\Models\Tag;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $appUrl = config('app.url');
        if ($this->app->runningInConsole() && is_string($appUrl) && $appUrl !== '') {
            URL::forceRootUrl($appUrl);
            $scheme = parse_url($appUrl, PHP_URL_SCHEME);
            if (is_string($scheme) && $scheme !== '') {
                URL::forceScheme($scheme);
            }
        }

        View::share('availableLocales', config('app.available_locales', []));
        View::composer('*', function ($view) {
            $view->with('currentLocale', app()->getLocale());
        });

        Blade::directive('snippet', function ($expression) {
            return "<?php echo \\App\\Models\\Snippet::render({$expression}); ?>";
        });

        View::composer('partials.header', function ($view) {
            $authUser = auth()->user();

            // unreadNotifications metodu yoksa (Notifiable trait yoksa) patlamasın
            $unreadNotifications = 0;
            if ($authUser && method_exists($authUser, 'unreadNotifications')) {
                $unreadNotifications = $authUser->unreadNotifications()->count();
            }

            $unreadMessages = 0;
            if ($authUser) {
                $unreadMessages = Message::query()
                    ->where('recipient_id', $authUser->id)
                    ->whereNull('read_at')
                    ->where('deleted_by_recipient', false)
                    ->count();
            }

            $initials = '';
            if ($authUser) {
                $name = (string) ($authUser->name ?? 'U');
                $initials = Str::upper(Str::substr($name, 0, 2));
            }

            $themeHeader = null;
            try {
                $themeHeader = ThemeSetting::render('header');
            } catch (\Throwable $e) {
                $themeHeader = null;
            }

            $roleLabel = $authUser?->roleLabel() ?? 'Uye';

            $view->with(compact(
                'authUser',
                'unreadNotifications',
                'unreadMessages',
                'initials',
                'themeHeader',
                'roleLabel'
            ));
        });

        View::composer('partials.right', function ($view) {
            $data = $view->getData();
            $widgetSettings = ThemeSetting::currentOrNull();

            $commentsEnabled = $widgetSettings?->widget_comments_enabled ?? true;
            $commentsCount = $widgetSettings?->widget_comments_count ?? 10;
            $tagsEnabled = $widgetSettings?->widget_tags_enabled ?? true;
            $tagsCount = $widgetSettings?->widget_tags_count ?? 10;
            $trendingEnabled = $widgetSettings?->widget_trending_enabled ?? true;
            $trendingCount = $widgetSettings?->widget_trending_count ?? 5;

            $popularTags = $data['popularTags'] ?? null;
            $popularComments = $data['popularComments'] ?? null;

            if (!array_key_exists('popularTags', $data)) {
                $popularTags = $tagsEnabled
                    ? Tag::withCount('posts')
                        ->orderByDesc('posts_count')
                        ->take($tagsCount)
                        ->get()
                    : collect();
            }

            if (!array_key_exists('popularComments', $data)) {
                $popularComments = $commentsEnabled
                    ? Comment::with(['user', 'post'])
                        ->whereNull('parent_id')
                        ->orderByDesc('id')
                        ->take($commentsCount)
                        ->get()
                    : collect();
            }

            $mostViewedPosts = collect();
            $mostReactedPosts = collect();

            if ($trendingEnabled) {
                // "Populer gonderiler" gercekten O HAFTA one cikanlari
                // gostersin diye son 7 gune sinirlandi - onceden zaman
                // penceresi hic yoktu, bu yuzden cok eski/tek seferlik
                // viral bir gonderi surekli listeyi isgal edip o haftaki
                // gercekten populer gonderilerin hic gorunmemesine yol
                // aciyordu. Son 7 gunde yeterli veri yoksa (yeni/dusuk
                // trafikli site durumu), tum zamanlara geri donuluyor.
                $trendingSince = now()->subDays(7);

                $mostViewedPosts = \App\Models\Post::published()
                    ->where('published_at', '>=', $trendingSince)
                    ->orderByDesc('views_count')
                    ->take($trendingCount)
                    ->get();

                if ($mostViewedPosts->isEmpty()) {
                    $mostViewedPosts = \App\Models\Post::published()
                        ->orderByDesc('views_count')
                        ->take($trendingCount)
                        ->get();
                }

                $mostReactedPosts = \App\Models\Post::published()
                    ->where('published_at', '>=', $trendingSince)
                    ->withCount('reactions')
                    ->orderByDesc('reactions_count')
                    ->take($trendingCount)
                    ->get();

                if ($mostReactedPosts->isEmpty()) {
                    $mostReactedPosts = \App\Models\Post::published()
                        ->withCount('reactions')
                        ->orderByDesc('reactions_count')
                        ->take($trendingCount)
                        ->get();
                }
            }

            $view->with(compact(
                'popularTags',
                'popularComments',
                'commentsEnabled',
                'tagsEnabled',
                'trendingEnabled',
                'mostViewedPosts',
                'mostReactedPosts'
            ));
        });

        View::composer('partials.community-feed', function ($view) {
            $borsaTicker = app(BorsaService::class)->ticker();
            $borsaSetting = BorsaSetting::currentOrNull();
            $borsaRefresh = $borsaSetting ? max(10, (int) ($borsaSetting->cache_seconds ?? 60)) : 60;
            $view->with(compact('borsaTicker', 'borsaRefresh'));
        });
    }
}

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
            $popularTags = $data['popularTags'] ?? null;
            $popularComments = $data['popularComments'] ?? null;

            if (!array_key_exists('popularTags', $data)) {
                $popularTags = Tag::withCount('posts')
                    ->orderByDesc('posts_count')
                    ->take(10)
                    ->get();
            }

            if (!array_key_exists('popularComments', $data)) {
                $popularComments = Comment::with(['user', 'post'])
                    ->whereNull('parent_id')
                    ->orderByDesc('id')
                    ->take(10)
                    ->get();
            }

            $view->with(compact('popularTags', 'popularComments'));
        });

        View::composer('partials.community-feed', function ($view) {
            $borsaTicker = app(BorsaService::class)->ticker();
            $borsaSetting = BorsaSetting::currentOrNull();
            $borsaRefresh = $borsaSetting ? max(10, (int) ($borsaSetting->cache_seconds ?? 60)) : 60;
            $view->with(compact('borsaTicker', 'borsaRefresh'));
        });
    }
}

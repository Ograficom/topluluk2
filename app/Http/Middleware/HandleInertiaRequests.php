<?php

namespace App\Http\Middleware;

use App\Http\Resources\AdsResource;
use App\Http\Resources\CommunityStoryCardResource;
use App\Http\Resources\UserResource;
use App\Models\Ad;
use App\Models\Community;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if (! (new AppCheckInstalledMiddleware())->alreadyInstalled()) {
            // If the app is not installed, return a specific response
            return [
                'flash' => fn () => [
                    'toasts' => $request->session()->get('toasts'),
                    'db_alert' => $request->session()->get('db_alert'),
                ],
                'appearance' => fn () => config('alma.appearance'),
                'api_url' => fn () => Str::finish(url('/'), '/'),
                'api_key' => fn () => '',
                'license_key' => fn () => session()->get('license_key'),
                'translations' => function () {
                    return cache()->rememberForever('translations.'.app()->getLocale(), function () {
                        $phpTranslations = [];
                        $jsonTranslations = [];
                        if (File::exists(lang_path(app()->getLocale().'/'.'.php'))) {
                            $phpTranslations = include lang_path(app()->getLocale().'/'.'.php');
                        }

                        if (File::exists(lang_path(app()->getLocale().'.json'))) {
                            $jsonTranslations = json_decode(File::get(lang_path(app()->getLocale().'.json')), true, JSON_UNESCAPED_UNICODE);
                        }

                        return array_merge($phpTranslations, $jsonTranslations);
                    });
                },
                'ziggy' => fn () => [
                    ...(new Ziggy())->toArray(),
                    'location' => $request->url(),
                ],
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'check' => auth()->check(),
                'user' => fn () => $request->user()
                    ? UserResource::make($request->user())
                    : false,
                'unreaded_notifications' => auth()->check()
                    ? $request->user()->unreadNotifications()->count()
                    : null,
            ],
            'flash' => fn () => [
                'toasts' => $request->session()->get('toasts'),
                'scrollToComment' => $request->session()->get('scrollToComment'),
            ],
            'sidebar_communities' => fn () => cache()->remember('sidebar_communities', now()->addMinutes(30), function () {
                return CommunityStoryCardResource::collection(Community::active()->orderByFollowersCountDesc()->limit(10)->get());
            }),
            'sidebar_footer_menu' => fn () => cache()->remember('footerMenu', now()->addMinutes(60), function () {
                return Page::select(['title', 'slug'])->where('show_footer_menu', true)->get();
            }),
            'translations' => function () {
                return cache()->rememberForever('translations.'.app()->getLocale(), function () {
                    $phpTranslations = [];
                    $jsonTranslations = [];
                    if (File::exists(lang_path(app()->getLocale().'/'.'.php'))) {
                        $phpTranslations = include lang_path(app()->getLocale().'/'.'.php');
                    }

                    if (File::exists(lang_path(app()->getLocale().'.json'))) {
                        $jsonTranslations = json_decode(File::get(lang_path(app()->getLocale().'.json')), true, JSON_UNESCAPED_UNICODE);
                    }

                    return array_merge($phpTranslations, $jsonTranslations);
                });
            },
            'settings' => fn () => [
                'general' => function () {
                    $settings = settings()->group('general')->all(false);
                    $disk = getCurrentDisk();

                    foreach (['site_logo', 'site_logo_dark', 'site_favicon'] as $asset) {
                        if (! empty($settings[$asset])) {
                            $settings[$asset] = Storage::disk($disk)->url($settings[$asset]);
                        }
                    }

                    return $settings;
                },
                'advanced' => fn () => settings()->group('advanced')->all(false),
                'social_media_links' => fn () => settings()->group('social_media_links')->all(false),
            ],
            'recaptcha_site_key' => env('RECAPTCHA_SITE_KEY'),
            'current_locale' => fn () => app()->getLocale(),
            'appearance' => fn () => config('alma.appearance'),
            'cookie_active' => fn () => cache()->rememberForever('cookie_active', function () {
                return config('alma.cookie_active');
            }),
            'ads' => fn () => [
                'head_code' => fn () => cache()->remember('head_code', now()->addMinutes(5), function () {
                    return Ad::where('alias', 'head_code')->active()->first() ? AdsResource::make(Ad::where('alias', 'head_code')->active()->first()) : false;
                }),
                'feed_page_top' => fn () => cache()->remember('feed_page_top', now()->addMinutes(5), function () {
                    return Ad::where('alias', 'feed_page_top')->active()->first() ? AdsResource::make(Ad::where('alias', 'feed_page_top')->active()->first()) : false;
                }),
                'sidebar_sticky' => fn () => cache()->remember('sidebar_sticky', now()->addMinutes(5), function () {
                    return Ad::where('alias', 'sidebar_sticky')->active()->first() ? AdsResource::make(Ad::where('alias', 'sidebar_sticky')->active()->first()) : false;
                }),
                'post_page_top' => fn () => cache()->remember('post_page_top', now()->addMinutes(5), function () {
                    return Ad::where('alias', 'post_page_top')->active()->first() ? AdsResource::make(Ad::where('alias', 'post_page_top')->active()->first()) : false;
                }),
                'post_page_before_comments' => fn () => cache()->remember('post_page_before_comments', now()->addMinutes(5), function () {
                    return Ad::where('alias', 'post_page_before_comments')->active()->first() ? AdsResource::make(Ad::where('alias', 'post_page_before_comments')->active()->first()) : false;
                }),
                'post_page_after_comments' => fn () => cache()->remember('post_page_after_comments', now()->addMinutes(5), function () {
                    return Ad::where('alias', 'post_page_after_comments')->active()->first() ? AdsResource::make(Ad::where('alias', 'post_page_after_comments')->active()->first()) : false;
                }),
                'static_page_top' => fn () => cache()->remember('static_page_top', now()->addMinutes(5), function () {
                    return Ad::where('alias', 'static_page_top')->active()->first() ? AdsResource::make(Ad::where('alias', 'static_page_top')->active()->first()) : false;
                }),
                'static_page_bottom' => fn () => cache()->remember('static_page_bottom', now()->addMinutes(5), function () {
                    return Ad::where('alias', 'static_page_bottom')->active()->first() ? AdsResource::make(Ad::where('alias', 'static_page_bottom')->active()->first()) : false;
                }),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy())->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}

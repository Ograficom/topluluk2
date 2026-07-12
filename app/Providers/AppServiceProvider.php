<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        JsonResource::withoutWrapping();

        try {
            if (isAppInstalled()) {
                $site_language = settings()->group('general')->get('site_language', false) ?: 'tr';
            } else {
                $site_language = 'en';
            }
        } catch (\PDOException $e) {
            $site_language = 'en';
        }

        // Set locale for application
        app()->setLocale($site_language);
        setlocale(LC_TIME, $site_language.'_'.mb_strtoupper($site_language));

        Inertia::titleTemplate(fn ($title) => $title ? $title.' - '.env('APP_NAME') : env('APP_NAME'));
    }
}

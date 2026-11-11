<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AdminOverview;
use App\Models\SiteSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Navigation\NavigationGroup;
use Filament\View\PanelsRenderHook;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(fn (): string => (SiteSetting::current()?->brand_name ?? SiteSetting::DEFAULT_BRAND_NAME).' Yönetim')
            ->brandLogo(fn (): ?string => SiteSetting::current()?->brandLogoUrl())
            ->brandLogoHeight('36px')
            ->favicon(fn (): ?string => SiteSetting::current()?->faviconUrl() ?? asset('assets/img/meta/logo.svg'))
            ->login()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Ayarlar'),
                NavigationGroup::make()
                    ->label('Kullanıcı Yönetimi'),
            ])
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                fn (): string => match (true) {
                    request()->is('admin/content-management', 'admin/channels*', 'admin/comments*', 'admin/posts*', 'admin/tags*', 'admin/rss-sources*')
                        => view('filament.components.content-management-tabs')->render(),
                    request()->is('admin/settings-management', 'admin/site-branding', 'admin/mail-settings', 'admin/seo-settings', 'admin/sitemap-manager', 'admin/rss-manager')
                        => view('filament.components.settings-tabs')->render(),
                    default => '',
                },
            )
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AdminOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

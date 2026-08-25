<?php

namespace App\Providers\Filament;

use App\Filament\Resources\AdOrderResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use Gsferro\FilamentStatPlusEasy\FilamentStatPlusEasyPlugin;
use LaBoiteACode\FilamentDashboardWidgets\FilamentDashboardWidgetsPlugin;
use NoteBrainsLab\FilamentEmailTemplates\FilamentEmailTemplatesPlugin;
use Openplain\FilamentShadcnTheme\Color as ShadcnColor;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use WallaceMartinss\FilamentSecurity\FilamentSecurityPlugin;
use Wezlo\FilamentLookups\FilamentLookupsPlugin;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->default()
            ->path('admin')
            ->login()
            ->theme(new HtmlString('<link rel="stylesheet" href="' . asset('css/filament/filament/app.css') . '" data-navigate-track="reload" />'))
            ->colors([
                'primary' => ShadcnColor::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                AdOrderResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\OverviewStats::class,
                \App\Filament\Widgets\PostsPerMonthChart::class,
                \App\Filament\Widgets\UsersGrowthChart::class,
                \App\Filament\Widgets\CategoryCompositionWidget::class,
                \App\Filament\Widgets\MonthlyGoalWidget::class,
                \App\Filament\Widgets\RecentCommentsWidget::class,
                \App\Filament\Widgets\RecentActivityTimeline::class,
                \App\Filament\Widgets\PlatformSummaryWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Blog'),
                NavigationGroup::make('Sayfalar ve SEO'),
                NavigationGroup::make('Mesajlar'),
                NavigationGroup::make('Kullanicilar'),
                NavigationGroup::make('Moderasyon'),
                NavigationGroup::make('Ayarlar'),
            ])
            ->plugins([
                FilamentEmailTemplatesPlugin::make()
                    ->navigationGroup('Mesajlar'),
                FilamentSecurityPlugin::make(),
                FilamentDashboardWidgetsPlugin::make(),
                ResizedColumnPlugin::make(),
                FilamentStatPlusEasyPlugin::make(),
                FilamentSpatieLaravelHealthPlugin::make(),
                FilamentLookupsPlugin::make()
                    ->navigationGroup('Ayarlar'),
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

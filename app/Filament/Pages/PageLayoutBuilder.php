<?php

namespace App\Filament\Pages;

use App\Models\ThemeDesignRevision;
use App\Models\ThemeSetting;
use App\Services\PageLayoutCatalog;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PageLayoutBuilder extends Page
{
    protected string $view = 'filament.pages.page-layout-builder';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'Blade Surukle-Birak Editoru';

    protected static ?int $navigationSort = 24;

    public string $previewPath = '/';

    public string $activeLayoutKey = 'blade:home';

    /** @var array<string, mixed> */
    public array $layoutDraft = ['containers' => []];

    public bool $layoutDirty = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->loadInitialDraft();
    }

    public function updatedPreviewPath(string $value): void
    {
        $normalized = $this->normalizePreviewPath($value);

        if ($normalized !== $value) {
            $this->previewPath = $normalized;
        }

        // The iframe reports the actual route name after the destination is loaded.
        $this->layoutDirty = false;
    }

    /**
     * Route shortcuts are generated from the real Laravel router. The URL field
     * still accepts any same-origin path, including routes with route parameters.
     *
     * @return array<string, string>
     */
    public function pageShortcuts(): array
    {
        $routes = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $name = $route->getName();
            $uri = (string) $route->uri();

            if (!is_string($name) || $name === '' || !in_array('GET', $route->methods(), true)) {
                continue;
            }

            if (str_starts_with($name, 'filament.')
                || str_starts_with($name, 'api.')
                || str_starts_with($name, 'livewire.')
                || str_starts_with($name, 'install.')
                || str_contains($uri, '{')
                || preg_match('/(?:^|\/)(?:api|livewire|install|webhooks?)(?:\/|$)/i', $uri) === 1
                || preg_match('/\.(?:json|xml|rss|atom)$/i', $uri) === 1) {
                continue;
            }

            $path = '/' . ltrim($uri, '/');
            $label = Str::headline($name) . ' — ' . ($path === '/' ? '/' : $path);
            $routes[$path] = $label;
        }

        $routes['/'] = 'Ana sayfa — /';
        ksort($routes);

        return array_slice($routes, 0, 160, true);
    }

    public function previewUrl(): string
    {
        return $this->buildUrl($this->previewPath, true);
    }

    public function publicPreviewUrl(): string
    {
        return $this->buildUrl($this->previewPath, false);
    }

    /**
     * Receive a layout snapshot from the same-origin page preview. Selectors are
     * revalidated by PageLayoutCatalog before being kept in Livewire state.
     */
    public function syncUniversalDraft(string $pageKey, array $layout, bool $dirty = true): void
    {
        if (!str_starts_with($pageKey, 'blade:')) {
            return;
        }

        $routeName = substr($pageKey, 6);
        if ($routeName === '' || preg_match('/^[A-Za-z0-9_.:-]{1,190}$/', $routeName) !== 1) {
            return;
        }

        if (!$dirty && $pageKey === $this->activeLayoutKey && $this->layoutDirty) {
            // Don't replace an unsaved in-browser draft with the iframe's initial echo.
            return;
        }

        $this->activeLayoutKey = $pageKey;
        $this->layoutDraft = PageLayoutCatalog::normalizeUniversalLayout($layout);
        $this->layoutDirty = $dirty;
    }

    public function saveLayout(): void
    {
        if (!str_starts_with($this->activeLayoutKey, 'blade:')) {
            Notification::make()->title('Onizlemeden sayfa secilemedi')->danger()->send();

            return;
        }

        $settings = ThemeSetting::current();
        $storedLayouts = PageLayoutCatalog::normalizeAll($settings->page_layouts ?? []);
        $next = PageLayoutCatalog::normalizeUniversalLayout($this->layoutDraft);
        $current = $storedLayouts[$this->activeLayoutKey] ?? ['containers' => []];

        if ($next === $current) {
            $this->layoutDirty = false;
            Notification::make()
                ->title('Kaydedilecek bir degisiklik yok')
                ->info()
                ->send();

            return;
        }

        $this->recordRevision($settings, 'Sayfa duzeni kaydetmeden once');
        $storedLayouts[$this->activeLayoutKey] = $next;
        $settings->update(['page_layouts' => $storedLayouts]);
        $this->layoutDraft = $next;
        $this->layoutDirty = false;

        Notification::make()
            ->title('Blade sayfa duzeni kaydedildi')
            ->body('Surukleyerek yaptigin sira ve gizleme degisiklikleri bu Laravel rotasina uygulandi.')
            ->success()
            ->send();
    }

    public function clearCurrentLayout(): void
    {
        if (!str_starts_with($this->activeLayoutKey, 'blade:')) {
            return;
        }

        $settings = ThemeSetting::current();
        $storedLayouts = PageLayoutCatalog::normalizeAll($settings->page_layouts ?? []);

        if (array_key_exists($this->activeLayoutKey, $storedLayouts)) {
            $this->recordRevision($settings, 'Sayfa duzeni varsayilana donmeden once');
            unset($storedLayouts[$this->activeLayoutKey]);
            $settings->update(['page_layouts' => $storedLayouts]);
        }

        $this->layoutDraft = ['containers' => []];
        $this->layoutDirty = false;
        $this->dispatch('ografi-layout-reload-preview');

        Notification::make()
            ->title('Sayfa varsayilan Blade duzenine donduruldu')
            ->success()
            ->send();
    }

    public function revisions()
    {
        $settings = ThemeSetting::current();

        return ThemeDesignRevision::query()
            ->with('user:id,name')
            ->where('theme_setting_id', $settings->id)
            ->whereNotNull('page_layouts')
            ->where(function ($query): void {
                $query->where('label', 'like', 'Sayfa duzeni%')
                    ->orWhere('label', 'like', 'Surum geri yuklemeden once%')
                    ->orWhere('label', 'like', 'Tema paketi ice aktarilmadan once yedek');
            })
            ->latest('id')
            ->limit(10)
            ->get();
    }

    public function restoreRevision(int $revisionId): void
    {
        $settings = ThemeSetting::current();
        $revision = ThemeDesignRevision::query()
            ->where('theme_setting_id', $settings->id)
            ->whereNotNull('page_layouts')
            ->where(function ($query): void {
                $query->where('label', 'like', 'Sayfa duzeni%')
                    ->orWhere('label', 'like', 'Surum geri yuklemeden once%')
                    ->orWhere('label', 'Tema paketi ice aktarilmadan once yedek');
            })
            ->find($revisionId);

        if (!$revision || !is_array($revision->page_layouts)) {
            Notification::make()->title('Sayfa duzeni surumu bulunamadi')->danger()->send();

            return;
        }

        $this->recordRevision($settings, 'Surum geri yuklemeden once');
        $settings->update([
            'page_layouts' => PageLayoutCatalog::normalizeAll($revision->page_layouts),
        ]);

        $stored = PageLayoutCatalog::normalizeAll($settings->fresh()->page_layouts ?? []);
        $this->layoutDraft = $stored[$this->activeLayoutKey] ?? ['containers' => []];
        $this->layoutDirty = false;
        $this->dispatch('ografi-layout-reload-preview');

        Notification::make()
            ->title('Onceki sayfa duzeni geri yuklendi')
            ->success()
            ->send();
    }

    private function loadInitialDraft(): void
    {
        $settings = ThemeSetting::current();
        $stored = PageLayoutCatalog::normalizeAll($settings->page_layouts ?? []);
        $this->layoutDraft = $stored[$this->activeLayoutKey] ?? ['containers' => []];
    }

    private function normalizePreviewPath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '/';
        }

        if (!str_starts_with($path, '/')
            || str_starts_with($path, '//')
            || preg_match('/[\x00-\x20\\\\]/', $path) === 1
            || str_contains($path, '#')
            || preg_match('/^[a-z][a-z0-9+.-]*:/i', $path) === 1) {
            Notification::make()
                ->title('Yalnizca bu sitenin yolunu gir')
                ->body('Ornek: /, /discover, /blog/ veya /u/kullanici-adi')
                ->warning()
                ->send();

            return '/';
        }

        $parts = parse_url($path);
        if (!is_array($parts) || isset($parts['host']) || isset($parts['scheme'])) {
            return '/';
        }

        return $path;
    }

    private function buildUrl(string $path, bool $editorPreview): string
    {
        $path = $this->normalizePreviewPath($path);
        $parts = parse_url($path) ?: [];
        $targetPath = (string) ($parts['path'] ?? '/');
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);

        unset($query['ografi_layout_preview'], $query['ografi_layout_page']);

        if ($editorPreview) {
            $query['ografi_layout_preview'] = '1';
        }

        $url = url($targetPath);

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    private function recordRevision(ThemeSetting $settings, string $label): void
    {
        ThemeDesignRevision::query()->create([
            'theme_setting_id' => $settings->id,
            'user_id' => Auth::id(),
            'label' => $label . ' (' . $this->activeLayoutKey . ')',
            'component_styles' => is_array($settings->component_styles) ? $settings->component_styles : [],
            'page_layouts' => is_array($settings->page_layouts) ? $settings->page_layouts : [],
        ]);

        $keepIds = ThemeDesignRevision::query()
            ->where('theme_setting_id', $settings->id)
            ->latest('id')
            ->limit(30)
            ->pluck('id');

        ThemeDesignRevision::query()
            ->where('theme_setting_id', $settings->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}

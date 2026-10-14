<?php

namespace App\Filament\Pages;

use App\Models\ThemeDesignRevision;
use App\Models\ThemeSetting;
use App\Services\PageLayoutCatalog;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PageLayoutBuilder extends Page
{
    protected string $view = 'filament.pages.page-layout-builder';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'Surukle-Birak Sayfa Duzeni';

    protected static ?int $navigationSort = 24;

    public string $pageKey = 'home';

    /** @var list<string> */
    public array $order = [];

    /** @var list<string> */
    public array $hidden = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->loadDraft();
    }

    public function updatedPageKey(string $value): void
    {
        if (!array_key_exists($value, PageLayoutCatalog::definitions())) {
            $this->pageKey = 'home';
        }

        $this->loadDraft();
    }

    public function pageOptions(): array
    {
        return PageLayoutCatalog::pageOptions();
    }

    public function previewUrl(): string
    {
        return PageLayoutCatalog::layoutUrl($this->pageKey, preview: true);
    }

    public function blockItems(): array
    {
        $definition = PageLayoutCatalog::definition($this->pageKey);

        if (!$definition) {
            return [];
        }

        $items = [];

        foreach ($this->order as $id) {
            $block = $definition['blocks'][$id] ?? null;

            if (!$block) {
                continue;
            }

            $items[] = [
                'id' => $id,
                'label' => $block['label'],
                'hidden' => in_array($id, $this->hidden, true),
            ];
        }

        return $items;
    }

    public function setDraftOrder(array $order): void
    {
        $layout = PageLayoutCatalog::normalizeLayout($this->pageKey, [
            'order' => $order,
            'hidden' => $this->hidden,
        ]);

        $this->order = $layout['order'];
        $this->hidden = $layout['hidden'];
    }

    public function toggleBlock(string $blockId): void
    {
        $definition = PageLayoutCatalog::definition($this->pageKey);

        if (!$definition || !array_key_exists($blockId, $definition['blocks'])) {
            return;
        }

        if (in_array($blockId, $this->hidden, true)) {
            $this->hidden = array_values(array_filter($this->hidden, fn (string $id) => $id !== $blockId));
        } else {
            $this->hidden[] = $blockId;
        }

        $this->hidden = PageLayoutCatalog::normalizeLayout($this->pageKey, [
            'order' => $this->order,
            'hidden' => $this->hidden,
        ])['hidden'];
    }

    public function showAllBlocks(): void
    {
        $this->hidden = [];
    }

    public function restoreDefaultOrder(): void
    {
        $this->order = array_keys(PageLayoutCatalog::definition($this->pageKey)['blocks'] ?? []);
        $this->hidden = [];
    }

    public function saveLayout(): void
    {
        $settings = ThemeSetting::current();
        $storedLayouts = PageLayoutCatalog::normalizeAll($settings->page_layouts ?? []);
        $next = PageLayoutCatalog::normalizeLayout($this->pageKey, [
            'order' => $this->order,
            'hidden' => $this->hidden,
        ]);

        if (($storedLayouts[$this->pageKey] ?? null) === $next) {
            Notification::make()
                ->title('Kaydedilecek bir degisiklik yok')
                ->info()
                ->send();

            return;
        }

        $this->recordRevision($settings, 'Sayfa duzeni kaydetmeden once');
        $storedLayouts[$this->pageKey] = $next;
        $settings->update(['page_layouts' => $storedLayouts]);

        Notification::make()
            ->title('Sayfa duzeni kaydedildi')
            ->body('Bolumlerin yeni sirasi ve gorunurlugu secili Blade sayfasina uygulandi.')
            ->success()
            ->send();
    }

    public function applyDraftFromPreview(string $pageKey, array $order, array $hidden = []): void
    {
        if ($pageKey !== $this->pageKey) {
            return;
        }

        $layout = PageLayoutCatalog::normalizeLayout($this->pageKey, [
            'order' => $order,
            'hidden' => $hidden,
        ]);

        $this->order = $layout['order'];
        $this->hidden = $layout['hidden'];
    }

    public function revisions()
    {
        $settings = ThemeSetting::current();

        return ThemeDesignRevision::query()
            ->with('user:id,name')
            ->where('theme_setting_id', $settings->id)
            ->whereNotNull('page_layouts')
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
            ->find($revisionId);

        if (!$revision || !is_array($revision->page_layouts)) {
            Notification::make()->title('Sayfa duzeni surumu bulunamadi')->danger()->send();

            return;
        }

        $this->recordRevision($settings, 'Surum geri yuklemeden once');
        $settings->update([
            'page_layouts' => PageLayoutCatalog::normalizeAll($revision->page_layouts),
        ]);
        $this->loadDraft();

        Notification::make()
            ->title('Onceki sayfa duzeni geri yuklendi')
            ->success()
            ->send();
    }

    private function loadDraft(): void
    {
        $settings = ThemeSetting::current();
        $storedLayouts = PageLayoutCatalog::normalizeAll($settings->page_layouts ?? []);
        $layout = $storedLayouts[$this->pageKey]
            ?? PageLayoutCatalog::normalizeLayout($this->pageKey, []);

        $this->order = $layout['order'];
        $this->hidden = $layout['hidden'];
    }

    private function recordRevision(ThemeSetting $settings, string $label): void
    {
        ThemeDesignRevision::query()->create([
            'theme_setting_id' => $settings->id,
            'user_id' => Auth::id(),
            'label' => $label . ' (' . (PageLayoutCatalog::pageOptions()[$this->pageKey] ?? $this->pageKey) . ')',
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

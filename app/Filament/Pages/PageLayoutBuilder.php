<?php

namespace App\Filament\Pages;

use App\Models\ThemeDesignRevision;
use App\Models\ThemeSetting;
use App\Services\PageLayoutCatalog;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    /** @var list<array<string, mixed>> */
    public array $customBlocks = [];

    public string $newBlockType = 'text';

    public ?string $editingCustomBlockId = null;

    /** @var array<string, mixed> */
    public array $customBlockDraft = [];

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

        $customById = collect($this->customBlocks)->keyBy('id');
        $items = [];

        foreach ($this->order as $id) {
            $custom = $customById->get($id);
            $block = $definition['blocks'][$id] ?? null;

            if ($custom) {
                $items[] = [
                    'id' => $id,
                    'label' => $custom['label'] ?? 'Icerik blogu',
                    'type' => $custom['type'] ?? 'text',
                    'custom' => true,
                    'hidden' => in_array($id, $this->hidden, true),
                ];

                continue;
            }

            if (!$block) {
                continue;
            }

            $items[] = [
                'id' => $id,
                'label' => $block['label'],
                'type' => 'section',
                'custom' => false,
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
            'custom_blocks' => $this->customBlocks,
        ]);

        $this->order = $layout['order'];
        $this->hidden = $layout['hidden'];
    }

    public function toggleBlock(string $blockId): void
    {
        $definition = PageLayoutCatalog::definition($this->pageKey);

        $isFixedBlock = $definition && array_key_exists($blockId, $definition['blocks']);
        $isCustomBlock = collect($this->customBlocks)->contains('id', $blockId);

        if (!$isFixedBlock && !$isCustomBlock) {
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
            'custom_blocks' => $this->customBlocks,
        ])['hidden'];

        $this->dispatchDraftUpdated();
    }

    public function showAllBlocks(): void
    {
        $this->hidden = [];
        $this->dispatchDraftUpdated();
    }

    public function restoreDefaultOrder(): void
    {
        $this->order = [
            ...array_keys(PageLayoutCatalog::definition($this->pageKey)['blocks'] ?? []),
            ...array_column($this->customBlocks, 'id'),
        ];
        $this->hidden = [];
        $this->dispatchDraftUpdated();
    }

    public function addCustomBlock(): void
    {
        if (count($this->customBlocks) >= 40) {
            Notification::make()->title('Bir sayfada en fazla 40 ek blok olabilir')->warning()->send();

            return;
        }

        $id = 'custom_' . Str::uuid();
        $block = PageLayoutCatalog::defaultCustomBlock($id, $this->newBlockType);
        $this->customBlocks[] = $block;
        $this->order[] = $id;
        $this->editingCustomBlockId = $id;
        $this->customBlockDraft = $block;
        $this->dispatchDraftUpdated();
    }

    public function editCustomBlock(string $blockId): void
    {
        $block = collect($this->customBlocks)->firstWhere('id', $blockId);

        if (!$block) {
            return;
        }

        $this->editingCustomBlockId = $blockId;
        $this->customBlockDraft = $block;
    }

    public function applyCustomBlockDraft(): void
    {
        if (!$this->editingCustomBlockId) {
            return;
        }

        $draft = array_merge($this->customBlockDraft, ['id' => $this->editingCustomBlockId]);
        $normalized = PageLayoutCatalog::normalizeCustomBlocks([$draft]);

        if ($normalized === []) {
            Notification::make()->title('Blok ayarlari gecersiz')->danger()->send();

            return;
        }

        $this->customBlocks = collect($this->customBlocks)
            ->map(fn (array $block) => $block['id'] === $this->editingCustomBlockId ? $normalized[0] : $block)
            ->values()
            ->all();

        $this->customBlockDraft = $normalized[0];
        $this->dispatchDraftUpdated();

        Notification::make()
            ->title('Blok onizlemeye uygulandi')
            ->body('Degisiklikler sayfaya yayinlanmadi; kaydetmek icin Sayfa duzenini kaydet dugmesini kullan.')
            ->success()
            ->send();
    }

    public function removeCustomBlock(string $blockId): void
    {
        $this->customBlocks = array_values(array_filter(
            $this->customBlocks,
            fn (array $block) => $block['id'] !== $blockId,
        ));
        $this->order = array_values(array_filter($this->order, fn (string $id) => $id !== $blockId));
        $this->hidden = array_values(array_filter($this->hidden, fn (string $id) => $id !== $blockId));

        if ($this->editingCustomBlockId === $blockId) {
            $this->editingCustomBlockId = null;
            $this->customBlockDraft = [];
        }

        $this->dispatchDraftUpdated();
    }

    public function saveLayout(): void
    {
        $settings = ThemeSetting::current();
        $storedLayouts = PageLayoutCatalog::normalizeAll($settings->page_layouts ?? []);
        $next = PageLayoutCatalog::normalizeLayout($this->pageKey, [
            'order' => $this->order,
            'hidden' => $this->hidden,
            'custom_blocks' => $this->customBlocks,
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

    public function applyDraftFromPreview(string $pageKey, array $order, array $hidden = [], ?array $customBlocks = null): void
    {
        if ($pageKey !== $this->pageKey) {
            return;
        }

        if (is_array($customBlocks)) {
            $this->customBlocks = PageLayoutCatalog::normalizeCustomBlocks($customBlocks);
        }

        $layout = PageLayoutCatalog::normalizeLayout($this->pageKey, [
            'order' => $order,
            'hidden' => $hidden,
            'custom_blocks' => $this->customBlocks,
        ]);

        $this->order = $layout['order'];
        $this->hidden = $layout['hidden'];
        $this->customBlocks = $layout['custom_blocks'];
        $this->dispatchDraftUpdated();
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
                    ->orWhere('label', 'Tema paketi ice aktarilmadan once yedek');
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
        $this->customBlocks = $layout['custom_blocks'];
        $this->editingCustomBlockId = null;
        $this->customBlockDraft = [];
    }

    private function dispatchDraftUpdated(): void
    {
        $this->dispatch('ografi-layout-draft-updated');
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

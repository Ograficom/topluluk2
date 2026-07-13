<?php

namespace App\Filament\Pages;

use App\Services\SitemapManager;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SitemapSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.sitemap-settings';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rss';
    protected static string | \UnitEnum | null $navigationGroup = 'Site';
    protected static ?string $navigationLabel = 'Sitemap Ayarlari';

    protected ?SitemapManager $manager = null;

    public array $state = [];

    public function mount(): void
    {
        $this->form->fill($this->getSitemapManager()->getSettings());
    }

    protected function getFormStatePath(): string
    {
        return 'state';
    }

    protected function getFormSchema(): array
    {
        return [
            Toggle::make('include_posts')
                ->label('Post sayfalari'),
            Toggle::make('include_categories')
                ->label('Kategori sayfalari'),
            Toggle::make('include_tags')
                ->label('Etiket sayfalari'),
            Toggle::make('include_pages')
                ->label('Sayfa sayfalari'),
        ];
    }

    public function save(): void
    {
        $this->getSitemapManager()->saveSettings($this->form->getState());
        Notification::make()
            ->title('Sitemap guncellendi')
            ->success()
            ->send();
    }

    public function regenerate(): void
    {
        $this->getSitemapManager()->regenerate();
        Notification::make()
            ->title('Sitemap onbellegi temizlendi')
            ->success()
            ->send();
    }

    protected function getSitemapManager(): SitemapManager
    {
        return $this->manager ??= app(SitemapManager::class);
    }
}






<?php

namespace App\Filament\Pages;

use App\Models\RssSource;
use App\Services\ExternalRssReader;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class RssSources extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'İçerik';
    protected static ?string $navigationLabel = 'Dış RSS kaynakları';
    protected static ?string $title = 'Dış RSS kaynakları';
    protected static ?int $navigationSort = 6;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    protected string $view = 'filament.pages.rss-sources';

    public string $sourceName = '';
    public string $sourceUrl = '';

    public function addSource(ExternalRssReader $reader): void
    {
        $data = Validator::make([
            'name' => trim($this->sourceName),
            'url' => trim($this->sourceUrl),
        ], [
            'name' => ['required', 'string', 'max:160'],
            'url' => ['required', 'string', 'url', 'max:2048', 'regex:/^https?:\\/\\//i'],
        ], [
            'name.required' => 'Kaynak adı gerekli.',
            'url.url' => 'Geçerli bir RSS adresi girin.',
            'url.regex' => 'RSS adresi http:// veya https:// ile başlamalı.',
        ])->validate();

        $urlHash = hash('sha256', rtrim($data['url']));
        if (RssSource::query()->where('url_hash', $urlHash)->exists()) {
            $this->addError('sourceUrl', 'Bu RSS kaynağı zaten eklenmiş.');
            return;
        }

        $source = RssSource::query()->create([
            'name' => $data['name'],
            'url' => $data['url'],
            'url_hash' => $urlHash,
            'is_active' => true,
        ]);

        $this->sourceName = '';
        $this->sourceUrl = '';

        try {
            $count = $reader->fetchSource($source);
            Notification::make()
                ->title('RSS kaynağı eklendi')
                ->body($count.' içerik alındı.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()
                ->title('Kaynak kaydedildi ancak okunamadı')
                ->body('Adres kaydedildi. Kaynak adresini veya XML biçimini kontrol edin; ayrıntı kaynak durumunda gösteriliyor.')
                ->warning()
                ->persistent()
                ->send();
        }
    }

    public function refreshSource(int $sourceId, ExternalRssReader $reader): void
    {
        $source = RssSource::query()->findOrFail($sourceId);
        try {
            $count = $reader->fetchSource($source);
            Notification::make()->title('RSS kaynağı güncellendi')->body($count.' içerik işlendi.')->success()->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->title('RSS kaynağı okunamadı')->body('Kaynak durumundaki hata bilgisini kontrol edin.')->danger()->send();
        }
    }

    public function refreshAll(ExternalRssReader $reader): void
    {
        $result = $reader->fetchAll();
        Notification::make()
            ->title('RSS güncellemesi tamamlandı')
            ->body($result['sources'].' kaynak kontrol edildi, '.$result['items'].' içerik işlendi, '.$result['errors'].' hata oluştu.')
            ->color($result['errors'] ? 'warning' : 'success')
            ->send();
    }

    public function toggleSource(int $sourceId): void
    {
        $source = RssSource::query()->findOrFail($sourceId);
        $source->update(['is_active' => ! $source->is_active]);
        Notification::make()->title($source->is_active ? 'Kaynak etkinleştirildi.' : 'Kaynak duraklatıldı.')->success()->send();
    }

    public function deleteSource(int $sourceId): void
    {
        RssSource::query()->findOrFail($sourceId)->delete();
        Notification::make()->title('RSS kaynağı ve kayıtlı içerikleri silindi.')->success()->send();
    }

    public function sources()
    {
        return RssSource::query()->withCount('items')->orderBy('name')->get();
    }
}

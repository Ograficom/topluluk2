<?php

namespace App\Filament\Pages;

use App\Models\RssSource;
use App\Models\Channel;
use App\Models\User;
use App\Services\ExternalRssReader;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class RssSources extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationLabel = 'Dış RSS kaynakları';
    protected static ?string $title = 'Dış RSS kaynakları';
    protected static ?int $navigationSort = 6;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    protected string $view = 'filament.pages.rss-sources';

    public string $sourceName = '';
    public string $sourceUrl = '';
    public ?int $authorId = null;
    public ?int $channelId = null;
    public bool $autoPublish = true;
    public array $sourceSettings = [];

    public function mount(): void
    {
        RssSource::query()->get()->each(function (RssSource $source): void {
            $this->sourceSettings[$source->id] = [
                'author_id' => $source->author_id,
                'channel_id' => $source->channel_id,
                'auto_publish' => $source->auto_publish,
            ];
        });
    }

    public function authors(): array
    {
        return User::query()->orderBy('name')->get()->mapWithKeys(fn (User $user) => [$user->id => $user->displayName()])->all();
    }

    public function channels(): array
    {
        return Channel::query()->active()->orderBy('name')->pluck('name', 'id')->all();
    }

    public function saveSourceSettings(int $sourceId): void
    {
        $source = RssSource::query()->findOrFail($sourceId);
        $settings = $this->sourceSettings[$sourceId] ?? [];
        $data = Validator::make($settings, [
            'author_id' => ['required', 'integer', 'exists:users,id'],
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
            'auto_publish' => ['required', 'boolean'],
        ])->validate();
        $source->update($data);
        Notification::make()->title('RSS yayın ayarları kaydedildi.')->success()->send();
    }

    public function addSource(ExternalRssReader $reader): void
    {
        $data = Validator::make([
            'name' => trim($this->sourceName),
            'url' => trim($this->sourceUrl),
            'author_id' => $this->authorId,
            'channel_id' => $this->channelId,
        ], [
            'name' => ['required', 'string', 'max:160'],
            'url' => ['required', 'url', 'max:2048'],
            'author_id' => ['required', 'integer', 'exists:users,id'],
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
        ], [
            'name.required' => 'Kaynak adı gerekli.',
            'url.required' => 'RSS adresi gerekli.',
            'url.url' => 'Geçerli bir RSS adresi girin.',
            'url.max' => 'RSS adresi 2048 karakterden uzun olamaz.',
            'author_id.required' => 'Yayın yazarı seçin.',
            'channel_id.required' => 'Yayın kategorisi seçin.',
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
            'author_id' => $data['author_id'],
            'channel_id' => $data['channel_id'],
            'auto_publish' => $this->autoPublish,
            'is_active' => true,
        ]);

        $this->sourceName = '';
        $this->sourceUrl = '';
        $this->authorId = null;
        $this->channelId = null;
        $this->autoPublish = true;

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
                ->body('Adres kaydedildi. '.Str::limit($exception->getMessage(), 180))
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
            Notification::make()->title('RSS kaynağı okunamadı')->body(Str::limit($exception->getMessage(), 180))->danger()->send();
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

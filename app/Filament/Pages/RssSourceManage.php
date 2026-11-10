<?php

namespace App\Filament\Pages;

use App\Models\RssSource;
use App\Services\ExternalRssReader;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class RssSourceManage extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'RSS kaynağı ayarları';
    protected static ?string $title = 'RSS kaynağı ayarları';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;
    protected static ?string $slug = 'rss-sources/{record}/manage';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.rss-source-manage';

    public RssSource $source;
    public string $sourceUrl = '';
    public ?int $authorId = null;
    public ?int $channelId = null;
    public bool $autoPublish = false;

    public function mount(int|string $record): void
    {
        $this->source = RssSource::query()->withCount('items')->findOrFail($record);
        $this->sourceUrl = $this->source->url;
        $this->authorId = $this->source->author_id;
        $this->channelId = $this->source->channel_id;
        $this->autoPublish = (bool) $this->source->auto_publish;
    }

    public function authors(): array
    {
        return \App\Models\User::query()->orderBy('name')->get()
            ->mapWithKeys(fn ($user) => [$user->id => $user->displayName()])->all();
    }

    public function channels(): array
    {
        return \App\Models\Channel::query()->active()->orderBy('name')->pluck('name', 'id')->all();
    }

    public function saveSettings(): void
    {
        $data = Validator::make([
            'url' => trim($this->sourceUrl),
            'author_id' => $this->authorId,
            'channel_id' => $this->channelId,
            'auto_publish' => $this->autoPublish,
        ], [
            'url' => ['required', 'url', 'max:2048'],
            'author_id' => ['required', 'integer', 'exists:users,id'],
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
            'auto_publish' => ['required', 'boolean'],
        ], [
            'url.required' => 'RSS adresi gerekli.',
            'url.url' => 'Geçerli bir RSS adresi girin.',
            'url.max' => 'RSS adresi 2048 karakterden uzun olamaz.',
            'author_id.required' => 'Yayın yazarı seçmelisiniz.',
            'channel_id.required' => 'Kategori seçmelisiniz.',
        ])->validate();

        $urlHash = hash('sha256', rtrim($data['url']));
        $duplicate = RssSource::query()
            ->where('url_hash', $urlHash)
            ->whereKeyNot($this->source->getKey())
            ->exists();

        if ($duplicate) {
            $this->addError('sourceUrl', 'Bu RSS adresi başka bir kaynağa zaten eklenmiş.');
            return;
        }

        $this->resetErrorBag('sourceUrl');

        $this->source->update([
            'url' => $data['url'],
            'url_hash' => $urlHash,
            'author_id' => $data['author_id'],
            'channel_id' => $data['channel_id'],
            'auto_publish' => $data['auto_publish'],
        ]);
        $this->source->refresh();
        $this->sourceUrl = $this->source->url;

        Notification::make()->title('RSS kaynağı ayarları kaydedildi')->body('RSS adresi ve yayın ayarları güncellendi.')->success()->send();
    }

    public function refreshNow(ExternalRssReader $reader): void
    {
        try {
            $count = $reader->fetchSource($this->source->fresh());
            $this->source->refresh();
            Notification::make()->title('RSS kaynağı güncellendi')->body($count.' içerik işlendi.')->success()->send();
        } catch (Throwable $exception) {
            report($exception);
            $this->source->refresh();
            Notification::make()->title('RSS kaynağı okunamadı')->body(Str::limit($exception->getMessage(), 220))->danger()->persistent()->send();
        }
    }

    public function toggleSource(): void
    {
        $this->source->update(['is_active' => ! $this->source->is_active]);
        $this->source->refresh();
        Notification::make()->title($this->source->is_active ? 'Kaynak etkinleştirildi' : 'Kaynak duraklatıldı')->success()->send();
    }

    public function deleteSource(): mixed
    {
        $this->source->delete();
        Notification::make()->title('RSS kaynağı silindi')->success()->send();

        return redirect(RssSources::getUrl());
    }
}

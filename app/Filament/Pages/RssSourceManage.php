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
    public ?int $authorId = null;
    public ?int $channelId = null;
    public bool $autoPublish = false;

    public function mount(int|string $record): void
    {
        $this->source = RssSource::query()->withCount('items')->findOrFail($record);
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
            'author_id' => $this->authorId,
            'channel_id' => $this->channelId,
            'auto_publish' => $this->autoPublish,
        ], [
            'author_id' => ['required', 'integer', 'exists:users,id'],
            'channel_id' => ['required', 'integer', 'exists:channels,id'],
            'auto_publish' => ['required', 'boolean'],
        ], [
            'author_id.required' => 'Yayın yazarı seçmelisiniz.',
            'channel_id.required' => 'Kategori seçmelisiniz.',
        ])->validate();

        $this->source->update($data);
        $this->source->refresh();
        Notification::make()->title('Kaynak ayarları kaydedildi')->success()->send();
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

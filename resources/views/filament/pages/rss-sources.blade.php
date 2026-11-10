<x-filament-panels::page>
    <style>
        .rss-admin{display:grid;gap:18px;max-width:1080px}
        .rss-admin-card{overflow:hidden;border:1px solid #e5e7eb;border-radius:15px;background:#fff}
        .rss-admin-head{padding:18px 21px;border-bottom:1px solid #eef0f3}
        .rss-admin-head h3{margin:0;font-size:15px;font-weight:700;color:#111827}
        .rss-admin-head p{margin:5px 0 0;font-size:12px;line-height:1.6;color:#6b7280}
        .rss-admin-body{padding:20px}
        .rss-admin-form{display:grid;grid-template-columns:minmax(150px,.7fr) minmax(230px,1.2fr) minmax(150px,.8fr) minmax(150px,.8fr) auto;align-items:end;gap:14px}.rss-admin-publish-settings{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:12px;padding-top:12px;border-top:1px solid #eef0f3}.rss-admin-publish-settings select{min-width:150px;max-width:220px}.rss-admin-publish-settings label{display:flex;align-items:center;gap:6px;font-size:12px;color:#475569}.rss-admin-publish-settings .fi-btn{margin-left:auto}
        .rss-admin-list{display:grid}
        .rss-admin-row{display:flex;align-items:center;gap:14px;padding:16px 20px;border-bottom:1px solid #eef0f3}
        .rss-admin-row:last-child{border-bottom:0}
        .rss-admin-icon{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:11px;background:#eff6ff;color:#2563eb}
        .rss-admin-info{min-width:0;flex:1}
        .rss-admin-info strong{display:block;color:#111827;font-size:13px}
        .rss-admin-info a{display:block;overflow-wrap:anywhere;color:#64748b;font-size:11px;margin-top:3px}
        .rss-admin-meta{display:flex;flex-wrap:wrap;gap:7px;margin-top:5px;color:#64748b;font-size:11px}
        .rss-admin-status{display:inline-flex;align-items:center;gap:5px;border-radius:99px;padding:4px 8px;background:#ecfdf5;color:#047857;font-size:10px;font-weight:650}
        .rss-admin-status.is-off{background:#f3f4f6;color:#6b7280}
        .rss-admin-error{margin-top:6px;color:#b91c1c;font-size:11px;line-height:1.5}
        .rss-admin-actions{display:flex;align-items:center;gap:5px;flex:0 0 auto}
        @media(max-width:760px){.rss-admin-form{grid-template-columns:1fr}.rss-admin-row{align-items:flex-start;flex-wrap:wrap;padding:14px}.rss-admin-actions{width:100%;justify-content:flex-end}.rss-admin-body{padding:15px}.rss-admin-publish-settings{align-items:stretch;flex-direction:column}.rss-admin-publish-settings select{max-width:none;width:100%}.rss-admin-publish-settings .fi-btn{margin-left:0;width:100%}}
        .dark .rss-admin-card{background:#18181b;border-color:#3f3f46}.dark .rss-admin-head{border-color:#3f3f46}.dark .rss-admin-head h3,.dark .rss-admin-info strong{color:#fafafa}
    </style>

    <div class="rss-admin">
        <section class="rss-admin-card">
            <header class="rss-admin-head">
                <h3>Yeni RSS kaynağı ekle</h3>
                <p>RSS 2.0 veya Atom 1.0 adresini ekleyin. Yeni içerikler 5 dakikada bir kontrol edilir ve seçtiğiniz yazar/kategoriyle otomatik yayınlanır.</p>
            </header>
            <div class="rss-admin-body">
                <form wire:submit="addSource" class="rss-admin-form">
                    <div>
                        <label for="rssSourceName" class="fi-fo-field-wrp-label"><span class="text-sm font-medium">Kaynak adı</span></label>
                        <input id="rssSourceName" type="text" wire:model="sourceName" maxlength="160" placeholder="Örn. Teknoloji Haberleri" required class="fi-input w-full">
                        @error('sourceName')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="rssSourceUrl" class="fi-fo-field-wrp-label"><span class="text-sm font-medium">RSS adresi</span></label>
                        <input id="rssSourceUrl" type="url" wire:model="sourceUrl" maxlength="2048" placeholder="https://example.com/feed.xml" required class="fi-input w-full">
                        @error('sourceUrl')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="rssAuthor" class="fi-fo-field-wrp-label"><span class="text-sm font-medium">Yayın yazarı</span></label>
                        <select id="rssAuthor" wire:model="authorId" required class="fi-input w-full">
                            <option value="">Yazar seçin</option>
                            @foreach ($this->authors() as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                        @error('author_id')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="rssChannel" class="fi-fo-field-wrp-label"><span class="text-sm font-medium">Kategori</span></label>
                        <select id="rssChannel" wire:model="channelId" required class="fi-input w-full">
                            <option value="">Kategori seçin</option>
                            @foreach ($this->channels() as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                        </select>
                        @error('channel_id')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <x-filament::button type="submit" icon="heroicon-o-plus">Kaynağı ekle</x-filament::button>
                    <div style="grid-column:1/-1">
                        <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:#475569">
                            <input type="checkbox" wire:model="autoPublish" style="accent-color:#2563eb">
                            Yeni RSS içeriklerini otomatik olarak yayınla
                        </label>
                    </div>
                </form>
            </div>
        </section>

        <section class="rss-admin-card">
            <header class="rss-admin-head" style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                <div><h3>Eklenen kaynaklar</h3><p>Kaynakları etkinleştirin, güncelleyin veya kaldırın.</p></div>
                <x-filament::button color="gray" icon="heroicon-o-arrow-path" wire:click="refreshAll" wire:loading.attr="disabled">Tümünü güncelle</x-filament::button>
            </header>
            <div class="rss-admin-list">
                @forelse ($this->sources() as $source)
                    <div class="rss-admin-row" wire:key="rss-source-{{ $source->id }}">
                        <span class="rss-admin-icon"><x-filament::icon icon="heroicon-o-rss" /></span>
                        <div class="rss-admin-info">
                            <strong>{{ $source->name }}</strong>
                            <a href="{{ $source->url }}" target="_blank" rel="noopener noreferrer">{{ $source->url }}</a>
                            <div class="rss-admin-meta">
                                <span>{{ number_format($source->items_count) }} içerik</span>
                                <span>·</span>
                                <span>{{ $source->last_fetched_at?->format('d.m.Y H:i') ?? 'Henüz güncellenmedi' }}</span>
                                <span class="rss-admin-status {{ $source->is_active ? '' : 'is-off' }}">{{ $source->is_active ? 'Etkin' : 'Duraklatıldı' }}</span>
                                <span class="rss-admin-status {{ $source->auto_publish ? '' : 'is-off' }}">{{ $source->auto_publish ? 'Otomatik yayın açık' : 'Otomatik yayın kapalı' }}</span>
                            </div>
                            <div class="rss-admin-publish-settings">
                                <select class="fi-input" wire:model="sourceSettings.{{ $source->id }}.author_id" aria-label="Yayın yazarı">
                                    <option value="">Yazar seçin</option>
                                    @foreach ($this->authors() as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                                </select>
                                <select class="fi-input" wire:model="sourceSettings.{{ $source->id }}.channel_id" aria-label="Yayın kategorisi">
                                    <option value="">Kategori seçin</option>
                                    @foreach ($this->channels() as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                                </select>
                                <label><input type="checkbox" wire:model="sourceSettings.{{ $source->id }}.auto_publish" style="accent-color:#2563eb"> Otomatik yayınla</label>
                                <x-filament::button size="sm" color="gray" wire:click="saveSourceSettings({{ $source->id }})">Ayarları kaydet</x-filament::button>
                            </div>
                            @if ($source->last_error)
                                <p class="rss-admin-error">{{ $source->last_error }}</p>
                            @endif
                        </div>
                        <div class="rss-admin-actions">
                            <x-filament::icon-button icon="heroicon-o-arrow-path" color="gray" tooltip="Şimdi güncelle" wire:click="refreshSource({{ $source->id }})" />
                            <x-filament::icon-button icon="{{ $source->is_active ? 'heroicon-o-pause' : 'heroicon-o-play' }}" color="gray" tooltip="{{ $source->is_active ? 'Duraklat' : 'Etkinleştir' }}" wire:click="toggleSource({{ $source->id }})" />
                            <x-filament::icon-button icon="heroicon-o-trash" color="danger" tooltip="Kaynağı sil" wire:click="deleteSource({{ $source->id }})" wire:confirm="Bu RSS kaynağı ve kayıtlı içerikleri silinsin mi?" />
                        </div>
                    </div>
                @empty
                    <div style="padding:32px 20px;text-align:center;color:#6b7280;font-size:13px">Henüz RSS kaynağı eklenmedi.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <style>
        .rss-source-page{display:grid;gap:18px;max-width:1080px}
        .rss-source-panel{overflow:hidden;border:1px solid #e4e9f1;border-radius:15px;background:#fff}
        .rss-source-panel-head{padding:19px 22px;border-bottom:1px solid #edf0f5}
        .rss-source-panel-head h3{margin:0;color:#172033;font-size:15px;font-weight:750}
        .rss-source-panel-head p{margin:5px 0 0;color:#7b8798;font-size:12px;line-height:1.6}
        .rss-source-panel-body{padding:22px}
        .rss-source-url{overflow-wrap:anywhere;color:#64748b;font-size:12px}
        .rss-source-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
        .rss-source-stat{padding:15px;border:1px solid #edf0f5;border-radius:11px;background:#fbfcfe}
        .rss-source-stat span{display:block;color:#778397;font-size:11px}
        .rss-source-stat strong{display:block;margin-top:5px;color:#172033;font-size:18px;font-weight:750}
        .rss-source-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .rss-source-field label{display:block;margin-bottom:7px;color:#344054;font-size:12px;font-weight:650}
        .rss-source-field select{width:100%;min-height:44px;padding:9px 12px;border:1px solid #dfe5ee;border-radius:9px;background:#fff;color:#273449;font-size:13px}
        .rss-source-toggle{grid-column:1/-1;display:flex;align-items:center;gap:9px;padding:13px;border:1px solid #edf0f5;border-radius:10px;color:#475569;font-size:12px}
        .rss-source-toggle input{width:16px;height:16px;accent-color:#2563eb}
        .rss-source-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px}
        .rss-source-error{padding:13px 15px;border:1px solid #fecaca;border-radius:10px;background:#fff7f7;color:#991b1b;font-size:12px;line-height:1.6}
        .rss-source-item{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:14px 0;border-bottom:1px solid #f0f2f6}
        .rss-source-item:last-child{border-bottom:0}
        .rss-source-item strong{display:block;color:#172033;font-size:13px}
        .rss-source-item a{display:block;margin-top:4px;overflow-wrap:anywhere;color:#64748b;font-size:11px}
        .rss-source-item small{display:block;margin-top:5px;color:#98a2b3;font-size:10px}
        @media(max-width:640px){.rss-source-panel-body{padding:16px}.rss-source-stats{grid-template-columns:1fr 1fr}.rss-source-form{grid-template-columns:1fr}.rss-source-toggle{grid-column:auto}.rss-source-actions{align-items:stretch;flex-direction:column}.rss-source-actions>*{width:100%}}
        .dark .rss-source-panel{background:#18181b;border-color:#3f3f46}.dark .rss-source-panel-head{border-color:#3f3f46}.dark .rss-source-panel-head h3,.dark .rss-source-stat strong,.dark .rss-source-item strong{color:#fafafa}.dark .rss-source-stat,.dark .rss-source-toggle{background:#202027;border-color:#3f3f46;color:#d4d4d8}.dark .rss-source-field select{background:#18181b;border-color:#3f3f46;color:#f4f4f5}
    </style>

    <div class="rss-source-page">
        <section class="rss-source-panel">
            <div class="rss-source-panel-head">
                <h3>{{ $source->name }}</h3>
                <p class="rss-source-url"><a href="{{ $source->url }}" target="_blank" rel="noopener noreferrer">{{ $source->url }}</a></p>
            </div>
            <div class="rss-source-panel-body">
                <div class="rss-source-stats">
                    <div class="rss-source-stat"><span>Toplanan içerik</span><strong>{{ number_format($source->items_count) }}</strong></div>
                    <div class="rss-source-stat"><span>Son kontrol</span><strong style="font-size:14px">{{ $source->last_fetched_at?->format('d.m.Y H:i') ?? 'Henüz yok' }}</strong></div>
                    <div class="rss-source-stat"><span>Kaynak durumu</span><strong style="font-size:14px;color:{{ $source->is_active ? '#16804d' : '#9a6700' }}">{{ $source->is_active ? 'Etkin' : 'Duraklatıldı' }}</strong></div>
                </div>
            </div>
        </section>

        @if ($source->last_error)
            <div class="rss-source-error"><strong>Son RSS hatası:</strong> {{ $source->last_error }}</div>
        @endif

        <section class="rss-source-panel">
            <div class="rss-source-panel-head">
                <h3>Yayın ayarları</h3>
                <p>Bu kaynaktan gelen içeriklerin hangi hesap ve kategoriyle yayınlanacağını buradan belirleyin.</p>
            </div>
            <div class="rss-source-panel-body">
                <form wire:submit="saveSettings">
                    <div class="rss-source-form">
                        <div class="rss-source-field">
                            <label for="rss-source-author">Yayın yazarı</label>
                            <select id="rss-source-author" wire:model="authorId" required>
                                <option value="">Yazar seçin</option>
                                @foreach ($this->authors() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('authorId')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="rss-source-field">
                            <label for="rss-source-channel">Yayın kategorisi</label>
                            <select id="rss-source-channel" wire:model="channelId" required>
                                <option value="">Kategori seçin</option>
                                @foreach ($this->channels() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('channelId')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <label class="rss-source-toggle">
                            <input type="checkbox" wire:model="autoPublish">
                            RSS içeriklerini otomatik yayınla (5 dakikada bir kontrol edilir)
                        </label>
                    </div>
                    <div class="rss-source-actions">
                        <x-filament::button type="submit" icon="heroicon-o-check">Ayarları kaydet</x-filament::button>
                        <x-filament::button type="button" color="gray" icon="heroicon-o-arrow-path" wire:click="refreshNow" wire:loading.attr="disabled">Şimdi RSS kontrolü yap</x-filament::button>
                    </div>
                </form>
            </div>
        </section>

        <section class="rss-source-panel">
            <div class="rss-source-panel-head">
                <h3>Kaynak işlemleri</h3>
                <p>Kaynağı geçici olarak duraklatabilir veya tamamen kaldırabilirsiniz.</p>
            </div>
            <div class="rss-source-panel-body">
                <div class="rss-source-actions" style="margin-top:0">
                    <x-filament::button color="gray" icon="{{ $source->is_active ? 'heroicon-o-pause' : 'heroicon-o-play' }}" wire:click="toggleSource">{{ $source->is_active ? 'Kaynağı duraklat' : 'Kaynağı etkinleştir' }}</x-filament::button>
                    <x-filament::button color="danger" icon="heroicon-o-trash" wire:click="deleteSource" wire:confirm="Bu RSS kaynağı ve ona bağlı kayıtlı içerikler silinsin mi?">Kaynağı sil</x-filament::button>
                </div>
            </div>
        </section>

        <section class="rss-source-panel">
            <div class="rss-source-panel-head">
                <h3>Son alınan içerikler</h3>
                <p>Bu RSS kaynağından veritabanına kaydedilen son içerikler.</p>
            </div>
            <div class="rss-source-panel-body">
                @forelse ($source->items()->orderByDesc('published_at')->orderByDesc('id')->limit(10)->get() as $item)
                    <div class="rss-source-item">
                        <div>
                            <strong>{{ $item->title }}</strong>
                            <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer">{{ $item->link }}</a>
                            <small>{{ $item->published_at?->format('d.m.Y H:i') ?? 'Tarih belirtilmemiş' }}</small>
                        </div>
                        @if ($item->published_post_id)
                            <span class="rss-admin-status">Yayınlandı</span>
                        @else
                            <span class="rss-admin-status is-off">Bekliyor</span>
                        @endif
                    </div>
                @empty
                    <p style="color:#7b8798;font-size:12px">Bu kaynaktan henüz içerik alınmamış.</p>
                @endforelse
            </div>
        </section>

        <div>
            <x-filament::button color="gray" icon="heroicon-o-arrow-left" tag="a" href="{{ \App\Filament\Pages\RssSources::getUrl() }}">Tüm RSS kaynaklarına dön</x-filament::button>
        </div>
    </div>
</x-filament-panels::page>

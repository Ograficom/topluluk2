<x-filament-panels::page>
    <style>
        .sitemap-manager { display:grid; gap:24px; width:100%; max-width:1120px; margin-inline:auto; color:#111827; }
        .sitemap-manager * { box-sizing:border-box; }
        .sm-intro { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:24px; background:#fff; border:1px solid #e5e7eb; border-radius:16px; }
        .sm-intro-main { display:flex; align-items:flex-start; gap:15px; min-width:0; }
        .sm-icon { display:flex; align-items:center; justify-content:center; width:46px; height:46px; flex:0 0 46px; border-radius:12px; background:#eff6ff; color:#2563eb; }
        .sm-icon svg { width:23px; height:23px; }
        .sm-heading { margin:0; color:#111827; font-size:17px; font-weight:700; line-height:1.4; }
        .sm-muted { margin:5px 0 0; color:#6b7280; font-size:13px; line-height:1.65; }
        .sm-link { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:40px; padding:0 14px; border:1px solid #e5e7eb; border-radius:9px; color:#374151; background:#fff; font-size:13px; font-weight:600; text-decoration:none; white-space:nowrap; }
        .sm-link:hover { background:#f9fafb; }
        .sm-stats { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .sm-stat { padding:21px 23px; border:1px solid #e5e7eb; border-radius:15px; background:#fff; }
        .sm-stat-top { display:flex; align-items:center; justify-content:space-between; gap:12px; color:#6b7280; font-size:13px; font-weight:600; }
        .sm-stat-icon { display:flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:10px; background:#eff6ff; color:#2563eb; }
        .sm-stat-icon svg { width:19px; height:19px; }
        .sm-stat-value { margin:12px 0 0; color:#111827; font-size:29px; font-weight:750; letter-spacing:-.8px; line-height:1.2; }
        .sm-stat-help { margin:6px 0 0; color:#9ca3af; font-size:12px; }
        .sm-panel { overflow:hidden; border:1px solid #e5e7eb; border-radius:16px; background:#fff; }
        .sm-panel-head { padding:21px 25px; border-bottom:1px solid #f0f1f3; }
        .sm-panel-head h3 { margin:0; color:#111827; font-size:16px; font-weight:700; }
        .sm-panel-head p { margin:5px 0 0; color:#6b7280; font-size:13px; line-height:1.6; }
        .sm-form-body { padding:25px; }
        .sm-form-body .fi-section { border:0 !important; box-shadow:none !important; background:transparent !important; }
        .sm-form-body .fi-section-header { display:none; }
        .sm-form-actions { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:17px 25px; border-top:1px solid #f0f1f3; background:#fafafa; }
        .sm-actions { display:flex; align-items:center; flex-wrap:wrap; gap:9px; }
        .sm-address { padding:22px 24px; border:1px solid #e5e7eb; border-radius:15px; background:#fff; }
        .sm-address-head { display:flex; align-items:flex-start; gap:13px; }
        .sm-address-code { display:block; min-width:0; flex:1; padding:12px 13px; border:1px solid #e5e7eb; border-radius:9px; background:#f9fafb; color:#374151; font-size:13px; line-height:1.6; overflow-wrap:anywhere; }
        .sm-address-row { display:flex; align-items:center; gap:9px; margin-top:13px; }
        .sm-copy { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:42px; padding:0 13px; border:1px solid #e5e7eb; border-radius:9px; background:#fff; color:#374151; font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap; }
        .sm-copy:hover { background:#f9fafb; }
        .sm-note { display:flex; align-items:flex-start; gap:11px; padding:15px 17px; border:1px solid #bfdbfe; border-radius:12px; background:#eff6ff; color:#1e40af; }
        .sm-note svg { width:19px; height:19px; flex:0 0 19px; margin-top:2px; }
        .sm-note strong { display:block; margin-bottom:3px; font-size:13px; }
        .sm-note p { margin:0; color:#1e40af; font-size:12px; line-height:1.7; }
        @media(max-width:640px) {
            .sitemap-manager { gap:15px; }
            .sm-intro { align-items:stretch; flex-direction:column; padding:17px; gap:14px; }
            .sm-intro-main { gap:11px; }
            .sm-intro .sm-link { width:100%; }
            .sm-stats { gap:10px; }
            .sm-stat { padding:15px; }
            .sm-stat-value { font-size:24px; }
            .sm-stat-help { line-height:1.5; }
            .sm-panel-head { padding:18px; }
            .sm-form-body { padding:16px; }
            .sm-form-actions { align-items:stretch; flex-direction:column; padding:16px; }
            .sm-actions { display:grid; grid-template-columns:1fr; width:100%; }
            .sm-actions > * { width:100%; }
            .sm-address { padding:17px; }
            .sm-address-row { align-items:stretch; flex-direction:column; }
            .sm-copy { width:100%; }
        }
        @media(prefers-color-scheme:dark) {
            .sm-intro,.sm-stat,.sm-panel,.sm-address { background:#18181b; border-color:#3f3f46; }
            .sm-heading,.sm-stat-value,.sm-panel-head h3 { color:#fafafa; }
            .sm-muted,.sm-stat-top,.sm-stat-help,.sm-panel-head p { color:#a1a1aa; }
            .sm-panel-head,.sm-form-actions { border-color:#3f3f46; }
            .sm-form-actions { background:#202024; }
            .sm-address-code { background:#27272a; border-color:#3f3f46; color:#e4e4e7; }
            .sm-link,.sm-copy { background:#27272a; border-color:#3f3f46; color:#e4e4e7; }
            .sm-note { background:#172554; border-color:#1d4ed8; }
            .sm-note p { color:#bfdbfe; }
        }
    </style>

    <div class="sitemap-manager">
        <section class="sm-intro">
            <div class="sm-intro-main">
                <span class="sm-icon"><x-filament::icon icon="heroicon-o-globe-alt" /></span>
                <div>
                    <h2 class="sm-heading">XML Sitemap yönetimi</h2>
                    <p class="sm-muted">OgraFi içeriklerinin arama motorları tarafından keşfedilmesini sağlayan sitemap dosyasını buradan yönetin.</p>
                </div>
            </div>
            <a href="{{ $this->sitemapUrl() }}" target="_blank" rel="noopener" class="sm-link">
                <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="size-4" />
                Sitemap'i görüntüle
            </a>
        </section>

        <section class="sm-stats">
            <div class="sm-stat">
                <div class="sm-stat-top">
                    <span>Toplam URL</span>
                    <span class="sm-stat-icon"><x-filament::icon icon="heroicon-o-link" /></span>
                </div>
                <p class="sm-stat-value">{{ number_format($this->urlCount) }}</p>
                <p class="sm-stat-help">Sitemap dosyasına eklenen adres sayısı</p>
            </div>
            <div class="sm-stat">
                <div class="sm-stat-top">
                    <span>Son oluşturulma</span>
                    <span class="sm-stat-icon"><x-filament::icon icon="heroicon-o-clock" /></span>
                </div>
                <p class="sm-stat-value" style="font-size:22px">{{ $this->lastGeneratedAt ?? 'Henüz oluşturulmadı' }}</p>
                <p class="sm-stat-help">Sitemap dosyasının son yenilenme zamanı</p>
            </div>
        </section>

        <section class="sm-panel">
            <header class="sm-panel-head">
                <h3>Yayın ve içerik ayarları</h3>
                <p>Sitemap'in hangi içerikleri kapsayacağını belirleyin.</p>
            </header>
            <form wire:submit="save">
                <div class="sm-form-body">
                    {{ $this->form }}
                </div>
                <footer class="sm-form-actions">
                    <p class="sm-muted" style="margin:0">Değişikliklerin geçerli olması için ayarlarınızı kaydedin.</p>
                    <div class="sm-actions">
                        <x-filament::button type="submit" icon="heroicon-o-check">Ayarları kaydet</x-filament::button>
                        <x-filament::button type="button" color="gray" icon="heroicon-o-arrow-path" wire:click="rebuild" wire:loading.attr="disabled" wire:target="rebuild">
                            <span wire:loading.remove wire:target="rebuild">Şimdi yeniden oluştur</span>
                            <span wire:loading wire:target="rebuild">Oluşturuluyor...</span>
                        </x-filament::button>
                    </div>
                </footer>
            </form>
        </section>

        <section class="sm-address">
            <div class="sm-address-head">
                <span class="sm-icon" style="background:#f3f4f6;color:#4b5563"><x-filament::icon icon="heroicon-o-document-text" /></span>
                <div style="min-width:0;flex:1">
                    <h3 class="sm-heading" style="font-size:14px">Sitemap dosya adresi</h3>
                    <p class="sm-muted">Bu bağlantıyı Google Search Console ve diğer arama motorlarında kullanabilirsiniz.</p>
                    <div class="sm-address-row">
                        <code class="sm-address-code">{{ $this->sitemapUrl() }}</code>
                        <button type="button" class="sm-copy" x-on:click="window.navigator.clipboard.writeText(@js($this->sitemapUrl()))">
                            <x-filament::icon icon="heroicon-o-clipboard-document" class="size-4" />
                            Adresi kopyala
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <aside class="sm-note">
            <x-filament::icon icon="heroicon-o-information-circle" />
            <div>
                <strong>Otomatik güncelleme</strong>
                <p>Yazı, kanal, etiket veya sabit sayfa eklendiğinde, güncellendiğinde ya da silindiğinde sitemap önbelleği otomatik yenilenir. Laravel zamanlayıcısı da her 15 dakikada bir sitemap önbelleğini yeniler.</p>
            </div>
        </aside>
    </div>
</x-filament-panels::page>

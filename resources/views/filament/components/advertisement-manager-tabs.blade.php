<nav class="cm-tabs" aria-label="Reklam yönetimi bölümleri">
    <a href="{{ \\App\\Filament\\Pages\\AdvertisementManager::getUrl() }}" @class(['cm-tab', 'is-active' => request()->is('admin/advertisement-manager')])>
        <x-filament::icon icon="heroicon-o-squares-2x2" /><span>Genel görünüm</span>
    </a>
    <a href="{{ \\App\\Filament\\Resources\\Advertisements\\AdvertisementResource::getUrl() }}" @class(['cm-tab', 'is-active' => request()->is('admin/advertisements*')])>
        <x-filament::icon icon="heroicon-o-megaphone" /><span>Reklamlar</span>
    </a>
    <a href="{{ \\App\\Filament\\Pages\\AdvertisementPricing::getUrl() }}" @class(['cm-tab', 'is-active' => request()->is('admin/advertisement-pricing')])>
        <x-filament::icon icon="heroicon-o-banknotes" /><span>Reklam fiyatlandırması</span>
    </a>
</nav>
<style>
.cm-tabs{display:flex;align-items:center;gap:5px;width:100%;margin:24px 0 22px;padding:7px;overflow-x:auto;white-space:nowrap;scrollbar-width:thin;border:1px solid #e5e7eb;border-radius:14px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.06)}
.cm-tab{display:inline-flex;align-items:center;justify-content:center;gap:9px;flex:0 0 auto;min-height:42px;padding:0 14px;border-radius:10px;color:#64748b;font-size:14px;font-weight:500;text-decoration:none;transition:background-color .15s ease,color .15s ease}
.cm-tab svg{width:18px;height:18px;color:#94a3b8}
.cm-tab:hover{background:#f8fafc;color:#1d4ed8}
.cm-tab.is-active{background:#eff6ff;color:#155eef}
.cm-tab.is-active svg{color:#2563eb}
.dark .cm-tabs{border-color:#3f3f46;background:#18181b}
.dark .cm-tab{color:#a1a1aa}
.dark .cm-tab:hover{background:#27272a;color:#bfdbfe}
.dark .cm-tab.is-active{background:#1e3a5f;color:#bfdbfe}
.dark .cm-tab.is-active svg{color:#93c5fd}
@media(max-width:640px){.cm-tabs{gap:3px;padding:5px;margin:18px 0 16px;border-radius:12px}.cm-tab{min-height:40px;gap:7px;padding:0 11px;font-size:13px}}
</style>
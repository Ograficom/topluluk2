<x-filament-panels::page>
    <section class="ad-manager-welcome">
        <div class="ad-manager-icon"><x-filament::icon icon="heroicon-o-megaphone" /></div>
        <div>
            <p class="ad-manager-eyebrow">OGRAFI YÖNETİM MERKEZİ</p>
            <h2>Reklam yönetimi</h2>
            <p>Reklam başvurularını, yayın durumlarını ve fiyatlandırmayı tek merkezden yönetin.</p>
        </div>
    </section>
    <style>
        .ad-manager-welcome{display:flex;align-items:center;gap:16px;padding:22px 24px;border:1px solid #e2e8f0;border-radius:15px;background:linear-gradient(110deg,#fff 0%,#f4f8ff 100%)}
        .ad-manager-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:13px;background:#eaf2ff;color:#2563eb}
        .ad-manager-icon svg{width:24px;height:24px}
        .ad-manager-eyebrow{margin:0 0 4px;color:#2563eb;font-size:10px;font-weight:800;letter-spacing:.09em}
        .ad-manager-welcome h2{margin:0;color:#172033;font-size:18px;font-weight:800}
        .ad-manager-welcome p:last-child{margin:5px 0 0;color:#64748b;font-size:13px;line-height:1.6}
        .dark .ad-manager-welcome{border-color:#374151;background:linear-gradient(110deg,#171e29,#1b293d)}
        .dark .ad-manager-welcome h2{color:#f3f4f6}
        .dark .ad-manager-welcome p:last-child{color:#a1a1aa}
        @media(max-width:640px){.ad-manager-welcome{align-items:flex-start;padding:17px}.ad-manager-icon{flex-basis:42px;width:42px;height:42px}}
    </style>
</x-filament-panels::page>
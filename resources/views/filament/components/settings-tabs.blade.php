<div class="admin-page-heading"><h1>Ayarlar</h1></div>
<section class="protection-style-intro">
    <div class="protection-style-icon"><x-filament::icon icon="heroicon-o-adjustments-horizontal" /></div>
    <div>
        <h2>OgraFi site ayarları</h2>
        <p>Marka, e-posta, SEO, sitemap ve RSS yapılandırmalarını tek merkezden yönetin.</p>
    </div>
</section>

<nav class="cm-tabs" aria-label="Ayarlar bölümleri">
    <a href="{{ \App\Filament\Pages\SiteBranding::getUrl() }}" @class(['cm-tab', 'is-active' => request()->routeIs('filament.admin.pages.site-branding')])>
        <x-filament::icon icon="heroicon-o-cog-6-tooth" />
        <span>Marka ayarları</span>
    </a>
    <a href="{{ \App\Filament\Pages\MailSettings::getUrl() }}" @class(['cm-tab', 'is-active' => request()->routeIs('filament.admin.pages.mail-settings')])>
        <x-filament::icon icon="heroicon-o-envelope" />
        <span>E-posta / SMTP</span>
    </a>
    <a href="{{ \App\Filament\Pages\SeoSettings::getUrl() }}" @class(['cm-tab', 'is-active' => request()->routeIs('filament.admin.pages.seo-settings')])>
        <x-filament::icon icon="heroicon-o-magnifying-glass" />
        <span>SEO ayarları</span>
    </a>
    <a href="{{ \App\Filament\Pages\SitemapManager::getUrl() }}" @class(['cm-tab', 'is-active' => request()->routeIs('filament.admin.pages.sitemap-manager')])>
        <x-filament::icon icon="heroicon-o-squares-2x2" />
        <span>Sitemap yönetimi</span>
    </a>
    <a href="{{ \App\Filament\Pages\RssManager::getUrl() }}" @class(['cm-tab', 'is-active' => request()->routeIs('filament.admin.pages.rss-manager')])>
        <x-filament::icon icon="heroicon-o-rss" />
        <span>RSS yönetimi</span>
    </a>
</nav>

<style>
    .cm-tabs {
        display:flex; align-items:center; gap:5px; width:100%; margin:24px 0 22px;
        padding:7px; overflow-x:auto; white-space:nowrap; scrollbar-width:thin;
        border:1px solid #e5e7eb; border-radius:14px; background:#fff;
        box-shadow:0 1px 2px rgba(15,23,42,.06);
    }
    .cm-tab {
        display:inline-flex; align-items:center; justify-content:center; gap:9px;
        flex:0 0 auto; min-height:42px; padding:0 14px; border-radius:10px;
        color:#64748b; font-size:14px; font-weight:500; text-decoration:none;
        transition:background-color .15s ease,color .15s ease;
    }
    .cm-tab svg { width:18px; height:18px; color:#94a3b8; }
    .cm-tab:hover { background:#f8fafc; color:#1d4ed8; }
    .cm-tab.is-active { background:#eff6ff; color:#155eef; }
    .cm-tab.is-active svg { color:#2563eb; }
    .cm-welcome {
        display:flex; align-items:flex-start; gap:15px; padding:22px;
        border:1px solid #e5e7eb; border-radius:14px; background:#fff;
    }
    .cm-welcome-icon {
        display:grid; place-items:center; flex:0 0 42px; width:42px; height:42px;
        border-radius:11px; background:#eff6ff; color:#2563eb;
    }
    .cm-welcome-icon svg { width:21px; height:21px; }
    .cm-welcome h2 { margin:0; color:#111827; font-size:15px; font-weight:700; }
    .cm-welcome p { margin:5px 0 0; color:#64748b; font-size:13px; line-height:1.65; }
    .dark .cm-tabs { border-color:#3f3f46; background:#18181b; }
    .dark .cm-tab { color:#a1a1aa; }
    .dark .cm-tab:hover { background:#27272a; color:#bfdbfe; }
    .dark .cm-tab.is-active { background:#1e3a5f; color:#bfdbfe; }
    .dark .cm-tab.is-active svg { color:#93c5fd; }
    .dark .cm-welcome { border-color:#3f3f46; background:#18181b; }
    .dark .cm-welcome-icon { background:#1e3a5f; color:#93c5fd; }
    .dark .cm-welcome h2 { color:#fafafa; }
    .dark .cm-welcome p { color:#a1a1aa; }
    @media(max-width:640px) {
        .cm-tabs { gap:3px; padding:5px; margin:18px 0 16px; border-radius:12px; }
        .cm-tab { min-height:40px; gap:7px; padding:0 11px; font-size:13px; }
        .cm-welcome { padding:16px; }
    }

    .admin-page-heading { margin:0 0 18px; }
    .admin-page-heading h1 { margin:0; color:#111827; font-size:30px; font-weight:700; letter-spacing:-.035em; line-height:1.2; }
    .protection-style-intro { display:flex; align-items:flex-start; gap:14px; width:100%; margin:0 0 22px; padding:18px 20px; border:1px solid #dbeafe; border-radius:12px; background:#f8fbff; }
    .protection-style-icon { display:flex; align-items:center; justify-content:center; flex:0 0 42px; width:42px; height:42px; border:1px solid #dbeafe; border-radius:10px; background:#fff; color:#2563eb; }
    .protection-style-icon svg { width:21px; height:21px; }
    .protection-style-intro h2 { margin:0; color:#111827; font-size:15px; font-weight:650; }
    .protection-style-intro p { margin:4px 0 0; color:#64748b; font-size:13px; line-height:1.6; }
    .dark .admin-page-heading h1 { color:#f9fafb; }
    .dark .protection-style-intro { border-color:#1e3a5f; background:#111c2e; }
    .dark .protection-style-icon { border-color:#1e3a5f; background:#172554; color:#93c5fd; }
    .dark .protection-style-intro h2 { color:#f9fafb; }
    .dark .protection-style-intro p { color:#9ca3af; }
    @media(max-width:640px) {
        .admin-page-heading { margin-bottom:14px; }
        .admin-page-heading h1 { font-size:25px; }
        .protection-style-intro { padding:15px; gap:11px; }
    }
</style>
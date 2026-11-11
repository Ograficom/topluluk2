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
</style>
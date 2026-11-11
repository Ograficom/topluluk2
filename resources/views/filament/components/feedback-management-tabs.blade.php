<div class="admin-page-heading"><h1>Geri Bildirim Yönetimi</h1></div>

<nav class="cm-tabs" aria-label="Geri bildirim yönetimi bölümleri">
    <a href="{{ \App\Filament\Pages\FeedbackManagement::getUrl() }}" @class(['cm-tab', 'is-active' => request()->is('admin/feedback-management')])>
        <x-filament::icon icon="heroicon-o-chart-bar" />
        <span>Genel bakış</span>
    </a>
    <a href="{{ \App\Filament\Resources\PostReports\PostReportResource::getUrl('index') }}" @class(['cm-tab', 'is-active' => request()->is('admin/post-reports*')])>
        <x-filament::icon icon="heroicon-o-flag" />
        <span>Yazı şikayetleri</span>
    </a>
    <a href="{{ \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl('index') }}" @class(['cm-tab', 'is-active' => request()->is('admin/support-tickets*')])>
        <x-filament::icon icon="heroicon-o-lifebuoy" />
        <span>Destek ve geri bildirim</span>
    </a>
</nav>

<style>
    .admin-page-heading { margin:0 0 18px; }
    .admin-page-heading h1 { margin:0; color:#111827; font-size:30px; font-weight:700; letter-spacing:-.035em; line-height:1.2; }
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
    .dark .admin-page-heading h1 { color:#f9fafb; }
    .dark .cm-tabs { border-color:#3f3f46; background:#18181b; }
    .dark .cm-tab { color:#a1a1aa; }
    .dark .cm-tab:hover { background:#27272a; color:#bfdbfe; }
    .dark .cm-tab.is-active { background:#1e3a5f; color:#bfdbfe; }
    .dark .cm-tab.is-active svg { color:#93c5fd; }
    @media(max-width:640px) {
        .admin-page-heading { margin-bottom:14px; }
        .admin-page-heading h1 { font-size:25px; }
        .cm-tabs { gap:3px; padding:5px; margin:18px 0 16px; border-radius:12px; }
        .cm-tab { min-height:40px; gap:7px; padding:0 11px; font-size:13px; }
    }
</style>
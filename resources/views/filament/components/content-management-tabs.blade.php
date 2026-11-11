@if (request()->is('admin/posts*'))
    <div class="cm-posts-intro"><h1>Postlar</h1></div>
@elseif (request()->is('admin/channels*'))
    <div class="cm-posts-intro"><h1>Kanallar</h1></div>
@elseif (request()->is('admin/tags*'))
    <div class="cm-posts-intro"><h1>Etiketler</h1></div>
@elseif (request()->is('admin/comments*'))
    <div class="cm-posts-intro"><h1>Yorumlar</h1></div>
@elseif (request()->is('admin/rss-sources*'))
    <div class="cm-posts-intro"><h1>Dış Bağlantılar</h1></div>
@endif

<nav class="cm-tabs" aria-label="İçerik yönetimi bölümleri">
    <a href="{{ url('/admin/posts') }}" @class(['cm-tab', 'is-active' => request()->is('admin/posts*')])>
        <x-filament::icon icon="heroicon-o-document-text" />
        <span>Postlar</span>
    </a>
    <a href="{{ url('/admin/channels') }}" @class(['cm-tab', 'is-active' => request()->is('admin/channels*')])>
        <x-filament::icon icon="heroicon-o-rectangle-stack" />
        <span>Kanallar</span>
    </a>
    <a href="{{ url('/admin/tags') }}" @class(['cm-tab', 'is-active' => request()->is('admin/tags*')])>
        <x-filament::icon icon="heroicon-o-tag" />
        <span>Etiketler</span>
    </a>
    <a href="{{ url('/admin/comments') }}" @class(['cm-tab', 'is-active' => request()->is('admin/comments*')])>
        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" />
        <span>Yorumlar</span>
    </a>
    <a href="{{ url('/admin/rss-sources') }}" @class(['cm-tab', 'is-active' => request()->is('admin/rss-sources*')])>
        <x-filament::icon icon="heroicon-o-rss" />
        <span>Dış Bağlantılar</span>
    </a>
</nav>

<style>
    .cm-posts-intro { margin:0 0 22px; }
    .cm-posts-intro h1 { margin:0 0 18px; color:#111827; font-size:30px; font-weight:700; letter-spacing:-.035em; line-height:1.2; }
    .cm-posts-welcome {
        display:flex; align-items:flex-start; gap:14px; width:100%;
        padding:18px 20px; border:1px solid #dbeafe; border-radius:12px;
        background:#f8fbff;
    }
    .cm-posts-welcome-icon {
        display:flex; align-items:center; justify-content:center; flex:0 0 42px;
        width:42px; height:42px; border:1px solid #dbeafe; border-radius:10px;
        background:#fff; color:#2563eb;
    }
    .cm-posts-welcome-icon svg { width:21px; height:21px; }
    .cm-posts-welcome h2 { margin:0; color:#111827; font-size:15px; font-weight:650; }
    .cm-posts-welcome p { margin:4px 0 0; color:#64748b; font-size:13px; line-height:1.6; }
    body:has(.cm-posts-intro) .fi-header-heading { display:none !important; }
    .dark .cm-posts-intro h1 { color:#f9fafb; }
    .dark .cm-posts-welcome { border-color:#1e3a5f; background:#111c2e; }
    .dark .cm-posts-welcome-icon { border-color:#1e3a5f; background:#172554; color:#93c5fd; }
    .dark .cm-posts-welcome h2 { color:#f9fafb; }
    .dark .cm-posts-welcome p { color:#9ca3af; }
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
    .dark .cm-tabs,.dark .cm-welcome { border-color:#3f3f46; background:#18181b; }
    .dark .cm-tab { color:#a1a1aa; }
    .dark .cm-tab:hover { background:#27272a; color:#bfdbfe; }
    .dark .cm-tab.is-active { background:#1e3a5f; color:#bfdbfe; }
    .dark .cm-tab.is-active svg { color:#93c5fd; }
    .dark .cm-welcome-icon { background:#1e3a5f; color:#93c5fd; }
    .dark .cm-welcome h2 { color:#fafafa; }
    .dark .cm-welcome p { color:#a1a1aa; }
    @media(max-width:640px) {
        .cm-posts-intro { margin-bottom:16px; }
        .cm-posts-intro h1 { margin-bottom:14px; font-size:25px; }
        .cm-posts-welcome { padding:15px; gap:11px; }

        .cm-tabs { gap:3px; padding:5px; margin:18px 0 16px; border-radius:12px; }
        .cm-tab { min-height:40px; gap:7px; padding:0 11px; font-size:13px; }
        .cm-welcome { padding:16px; }
    }
</style>

<x-filament-panels::page>
    <style>
        .admin-page-heading { margin:0 0 18px; }
        .admin-page-heading h1 { margin:0; color:#111827; font-size:30px; font-weight:750; letter-spacing:-.035em; line-height:1.2; }
        .admin-page-intro { display:flex; align-items:center; gap:16px; min-height:118px; width:100%; margin:0 0 18px; padding:22px 24px; border:1px solid #dbe5f2; border-radius:15px; background:linear-gradient(110deg,#fff 0%,#f4f8ff 100%); }
        .admin-page-intro-icon { display:grid; place-items:center; flex:0 0 48px; width:48px; height:48px; border:1px solid #dbeafe; border-radius:13px; background:#eaf2ff; color:#2563eb; }
        .admin-page-intro-icon svg { width:24px; height:24px; }
        .admin-page-intro-content { min-width:0; }
        .admin-page-intro-eyebrow { margin:0 0 4px; color:#2563eb; font-size:10px; font-weight:800; letter-spacing:.09em; }
        .admin-page-intro h2 { margin:0; color:#172033; font-size:18px; font-weight:800; }
        .admin-page-intro p:last-child { margin:5px 0 0; color:#64748b; font-size:13px; line-height:1.6; }
        body:has(.admin-page-heading) .fi-header-heading { display:none !important; }
        .dark .admin-page-heading h1 { color:#f9fafb; }
        .dark .admin-page-intro { border-color:#303b4d; background:linear-gradient(110deg,#171e29 0%,#1a2637 100%); }
        .dark .admin-page-intro-icon { border-color:#34445b; background:#202c3e; color:#93c5fd; }
        .dark .admin-page-intro-eyebrow { color:#93c5fd; }
        .dark .admin-page-intro h2 { color:#f3f6fb; }
        .dark .admin-page-intro p:last-child { color:#a0aec0; }
        @media(max-width:640px) {
            .admin-page-heading { margin-bottom:14px; }
            .admin-page-heading h1 { font-size:25px; }
            .admin-page-intro { min-height:0; gap:12px; padding:16px; border-radius:13px; }
            .admin-page-intro-icon { flex-basis:44px; width:44px; height:44px; }
            .admin-page-intro-icon svg { width:22px; height:22px; }
            .admin-page-intro h2 { font-size:16px; }
            .admin-page-intro p:last-child { font-size:12px; }
        }
    </style>
    <style>
        .settings-landing {
            display:flex;
            align-items:center;
            gap:18px;
            min-height:132px;
            padding:26px 30px;
            border:1px solid #e0e8f3;
            border-radius:18px;
            background:linear-gradient(105deg,#ffffff 0%,#f8fbff 62%,#eef5ff 100%);
        }
        .settings-landing-icon {
            display:grid;
            place-items:center;
            flex:0 0 62px;
            width:62px;
            height:62px;
            border:1px solid #e2eaf6;
            border-radius:17px;
            background:#fff;
            color:#1769f5;
        }
        .settings-landing-icon svg { width:29px;height:29px; }
        .settings-landing-content { min-width:0; }
        .settings-landing-eyebrow {
            margin:0 0 5px;
            color:#1769f5;
            font-size:11px;
            font-weight:800;
            letter-spacing:.09em;
        }
        .settings-landing h2 {
            margin:0;
            color:#172033;
            font-size:22px;
            font-weight:800;
            letter-spacing:-.035em;
            line-height:1.25;
        }
        .settings-landing p {
            margin:7px 0 0;
            color:#718096;
            font-size:13px;
            line-height:1.65;
        }
        .dark .settings-landing {
            border-color:#303b4d;
            background:linear-gradient(105deg,#171e29 0%,#1a2637 100%);
        }
        .dark .settings-landing-icon {
            border-color:#34445b;
            background:#202c3e;
            color:#75a7ff;
        }
        .dark .settings-landing-eyebrow { color:#75a7ff; }
        .dark .settings-landing h2 { color:#f3f6fb; }
        .dark .settings-landing p { color:#a0aec0; }
        @media(max-width:640px) {
            .settings-landing { min-height:0; gap:14px; padding:19px; border-radius:14px; }
            .settings-landing-icon { flex-basis:50px;width:50px;height:50px;border-radius:14px; }
            .settings-landing-icon svg { width:24px;height:24px; }
            .settings-landing h2 { font-size:18px; }
            .settings-landing p { font-size:12px; }
        }
    </style>

    <div class="admin-page-heading"><h1>Ayarlar</h1></div>

    <section class="admin-page-intro settings-landing">
        <div class="admin-page-intro-icon settings-landing-icon">
            <x-filament::icon icon="heroicon-o-adjustments-horizontal" />
        </div>
        <div class="admin-page-intro-content settings-landing-content">
            <p class="admin-page-intro-eyebrow settings-landing-eyebrow">OGRAFI YÖNETİM MERKEZİ</p>
            <h2>Site ayarları</h2>
            <p>Marka, e-posta, SEO, sitemap ve RSS yapılandırmalarını tek merkezden yönetin.</p>
        </div>
    </section>
</x-filament-panels::page>
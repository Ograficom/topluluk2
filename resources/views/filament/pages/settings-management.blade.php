<x-filament-panels::page>
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

    <section class="settings-landing">
        <div class="settings-landing-icon">
            <x-filament::icon icon="heroicon-o-adjustments-horizontal" />
        </div>
        <div class="settings-landing-content">
            <p class="settings-landing-eyebrow">OGRAFI YÖNETİM MERKEZİ</p>
            <h2>Site ayarları</h2>
            <p>Marka, e-posta, SEO, sitemap ve RSS yapılandırmalarını tek merkezden yönetin.</p>
        </div>
    </section>
</x-filament-panels::page>